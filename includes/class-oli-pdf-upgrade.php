<?php
/**
 * Version upgrades and option-key migration.
 *
 * @package Oli_PDF
 */

namespace Oli_PDF;

defined( 'ABSPATH' ) || exit;

/**
 * Upgrade class.
 */
class Upgrade {

	/**
	 * Option that stores the last migrated plugin version.
	 */
	const VERSION_OPTION = 'oli_pdf_db_version';

	/**
	 * Legacy option prefixes copied then deleted on upgrade.
	 *
	 * Sites that ran 0.1.0–0.1.1 already use oli_pdf_*. This map is for any
	 * earlier or forked keys so a folder/slug rename does not drop settings.
	 *
	 * @return array<string,string> Old option name => new option name.
	 */
	public static function get_legacy_option_map() {
		$suffixes = array(
			'invoice_number_prefix',
			'invoice_number_suffix',
			'invoice_number_padding',
			'invoice_number_start',
			'invoice_current_number',
			'invoice_generation_statuses',
			'invoice_email_attach',
			'enable_credit_notes',
			'credit_note_number_prefix',
			'credit_note_number_suffix',
			'credit_note_number_padding',
			'credit_note_number_start',
			'credit_note_current_number',
			'label_size',
			'label_custom_width',
			'label_custom_height',
			'labels_per_sheet',
			'label_columns',
			'label_rows',
			'show_sku',
			'show_weight',
			'show_tax_breakdown',
			'show_shipping',
			'show_payment_method',
			'show_customer_note',
			'show_product_images',
			'product_image_size',
			'show_product_meta',
			'template_primary_color',
			'template_secondary_color',
			'company_name',
			'company_address',
			'vat_number',
			'tax_number',
			'footer_text',
			'logo_url',
		);

		$map = array();
		foreach ( $suffixes as $suffix ) {
			$map[ 'olipdf_' . $suffix ] = 'oli_pdf_' . $suffix;
		}

		return $map;
	}

	/**
	 * Run pending upgrades once per version.
	 */
	public static function maybe_upgrade() {
		$installed = get_option( self::VERSION_OPTION, '0' );
		if ( version_compare( (string) $installed, OLI_PDF_VERSION, '>=' ) ) {
			return;
		}

		self::migrate_legacy_options();
		update_option( self::VERSION_OPTION, OLI_PDF_VERSION );
	}

	/**
	 * Copy old options onto oli_pdf_* keys, then delete the old keys.
	 *
	 * @return array<string,mixed> Copied values keyed by the new option name.
	 */
	public static function migrate_legacy_options() {
		$copied = array();

		foreach ( self::get_legacy_option_map() as $old_name => $new_name ) {
			$old_value = get_option( $old_name, false );
			if ( false === $old_value ) {
				continue;
			}

			if ( false === get_option( $new_name, false ) ) {
				add_option( $new_name, $old_value );
				$copied[ $new_name ] = $old_value;
			}

			delete_option( $old_name );
		}

		return $copied;
	}
}
