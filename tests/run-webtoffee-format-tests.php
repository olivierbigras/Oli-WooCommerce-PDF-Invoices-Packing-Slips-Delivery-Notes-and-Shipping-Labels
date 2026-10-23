<?php
/**
 * Standalone tests for the WebToffee 5.x data format used by migration.
 *
 * These tests do not require WordPress. They lock the option names and order
 * meta keys confirmed against print-invoices-packing-slip-labels-for-woocommerce 5.0.3.
 *
 * @package Oli_PDF
 */

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

require_once dirname( __DIR__ ) . '/includes/class-oli-pdf-migration.php';

use Oli_PDF\Migration;

$failures = 0;

/**
 * Assert helper.
 *
 * @param bool   $condition Condition.
 * @param string $message   Message.
 */
function oli_pdf_assert( $condition, $message ) {
	global $failures;
	if ( $condition ) {
		echo "PASS  $message\n";
		return;
	}
	++$failures;
	echo "FAIL  $message\n";
}

$keys = Migration::get_webtoffee_keys();

oli_pdf_assert( 'Wf_Woocommerce_Packing_List' === $keys['main_option'], 'Main option is Wf_Woocommerce_Packing_List (WF_PKLIST_SETTINGS_FIELD)' );
oli_pdf_assert( 'wf_woocommerce_packing_list' === $keys['main_option_legacy'], 'Legacy main option fallback is wf_woocommerce_packing_list' );
oli_pdf_assert( 'wf_woocommerce_packing_list_invoice' === $keys['invoice_option'], 'Invoice module option is wf_woocommerce_packing_list_invoice' );
oli_pdf_assert( 'wf_invoice_number' === $keys['order_invoice_number'], 'Order invoice number meta is wf_invoice_number (no underscore)' );
oli_pdf_assert( '_wf_invoice_number' === $keys['order_invoice_number_legacy'], 'Legacy order invoice number meta is _wf_invoice_number' );
oli_pdf_assert( '_wf_invoice_date' === $keys['order_invoice_date'], 'Order invoice date meta is _wf_invoice_date (underscore, unix timestamp)' );
oli_pdf_assert( 'wf_invoice_date' === $keys['order_invoice_date_legacy'], 'Legacy invoice date meta is wf_invoice_date' );
oli_pdf_assert( 'woocommerce_wf_Current_Invoice_number' === $keys['current_number_key'], 'Current number key uses capital C: woocommerce_wf_Current_Invoice_number' );

$invoice_map = Migration::get_invoice_settings_map();
oli_pdf_assert( 'woocommerce_wf_invoice_number_postfix' === $invoice_map['invoice_number_suffix'], 'Suffix maps from postfix, not suffix' );
oli_pdf_assert( 'woocommerce_wf_invoice_padding_number' === $invoice_map['invoice_number_padding'], 'Padding key is woocommerce_wf_invoice_padding_number' );
oli_pdf_assert( 'woocommerce_wf_invoice_start_number' === $invoice_map['invoice_number_start'], 'Start number key is woocommerce_wf_invoice_start_number' );
oli_pdf_assert( 'woocommerce_wf_invoice_number_prefix' === $invoice_map['invoice_number_prefix'], 'Prefix key is woocommerce_wf_invoice_number_prefix' );

$main = array(
	'woocommerce_wf_packinglist_sender_name'           => 'Test Company',
	'woocommerce_wf_packinglist_sender_address_line1'  => '123 Test Street',
	'woocommerce_wf_packinglist_sender_address_line2'  => 'Suite 4',
	'woocommerce_wf_packinglist_sender_city'           => 'Montreal',
	'woocommerce_wf_packinglist_sender_postalcode'     => 'H2X 1Y4',
	'woocommerce_wf_packinglist_sender_vat'            => 'VAT123456',
	'woocommerce_wf_packinglist_footer'                => 'Thank you',
	'woocommerce_wf_packinglist_logo'                  => 'https://example.com/logo.png',
);

$invoice = array(
	'woocommerce_wf_invoice_number_prefix'    => 'INV-',
	'woocommerce_wf_invoice_number_postfix'   => '-2024',
	'woocommerce_wf_invoice_padding_number'   => '5',
	'woocommerce_wf_invoice_start_number'     => '1000',
	'woocommerce_wf_Current_Invoice_number'   => 1050,
	'woocommerce_wf_generate_for_orderstatus' => array( 'wc-processing', 'wc-completed' ),
);

$mapped = Migration::map_webtoffee_settings( $main, $invoice );

oli_pdf_assert( 'INV-' === $mapped['invoice_number_prefix'], 'Maps invoice prefix' );
oli_pdf_assert( '-2024' === $mapped['invoice_number_suffix'], 'Maps postfix to suffix' );
oli_pdf_assert( '5' === $mapped['invoice_number_padding'], 'Maps padding' );
oli_pdf_assert( '1000' === $mapped['invoice_number_start'], 'Maps start number' );
oli_pdf_assert( 1050 === $mapped['invoice_current_number'], 'Maps current counter from invoice module option, not a standalone option' );
oli_pdf_assert( 'Test Company' === $mapped['company_name'], 'Maps sender name' );
oli_pdf_assert( false !== strpos( $mapped['company_address'], '123 Test Street' ), 'Maps address line 1' );
oli_pdf_assert( false !== strpos( $mapped['company_address'], 'Montreal' ), 'Maps city into address' );
oli_pdf_assert( 'VAT123456' === $mapped['vat_number'], 'Maps VAT' );
oli_pdf_assert( 'Thank you' === $mapped['footer_text'], 'Maps footer' );
oli_pdf_assert( 'https://example.com/logo.png' === $mapped['logo_url'], 'Maps logo' );
oli_pdf_assert( array( 'processing', 'completed' ) === $mapped['invoice_generation_statuses'], 'Strips wc- prefix from generate-for statuses' );

oli_pdf_assert( '2024-01-02 03:04:05' === Migration::normalize_invoice_date( 1704164645 ), 'Normalizes unix timestamp invoice date' );
oli_pdf_assert( '2024-01-01 12:00:00' === Migration::normalize_invoice_date( '2024-01-01 12:00:00' ), 'Keeps already-formatted dates' );
oli_pdf_assert( '' === Migration::normalize_invoice_date( '' ), 'Empty date stays empty' );

oli_pdf_assert( in_array( 'wf_invoice_number', Migration::get_order_invoice_number_keys(), true ), 'Looks up current invoice number meta' );
oli_pdf_assert( in_array( '_wf_invoice_number', Migration::get_order_invoice_number_keys(), true ), 'Looks up legacy invoice number meta' );
oli_pdf_assert( in_array( '_wf_invoice_date', Migration::get_order_invoice_date_keys(), true ), 'Looks up current invoice date meta' );

$empty = Migration::map_webtoffee_settings( array(), array() );
oli_pdf_assert( array() === $empty, 'Empty WebToffee bags map to an empty result' );

if ( $failures > 0 ) {
	echo "\n$failures test(s) failed.\n";
	exit( 1 );
}

echo "\nAll WebToffee format tests passed.\n";
exit( 0 );
