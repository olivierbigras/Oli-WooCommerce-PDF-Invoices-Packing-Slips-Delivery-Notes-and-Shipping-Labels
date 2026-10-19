<?php
/**
 * Migration from WebToffee plugin
 *
 * @package Oli_PDF
 */

namespace Oli_PDF;

defined( 'ABSPATH' ) || exit;

/**
 * Migration class
 */
class Migration {

	/**
	 * WebToffee option name
	 */
	const WEBTOFFEE_OPTION = 'wf_woocommerce_packing_list';

	/**
	 * Migrate data
	 *
	 * @param bool $dry_run Dry run mode.
	 * @return array
	 */
	public function migrate( $dry_run = false ) {
		$result = array(
			'success' => false,
			'message' => '',
			'data'    => array(),
		);

		$webtoffee_settings = get_option( self::WEBTOFFEE_OPTION );

		if ( ! $webtoffee_settings ) {
			$result['message'] = __( 'No WebToffee settings found. Nothing to migrate.', 'oli-pdf-invoices-packing-slips' );
			return $result;
		}

		$migrated_settings = $this->migrate_settings( $webtoffee_settings, $dry_run );
		$migrated_orders   = $this->migrate_order_data( $dry_run );

		$result['success'] = true;
		$result['message'] = sprintf(
			/* translators: 1: number of settings, 2: number of orders */
			__( 'Migration completed: %1$d settings migrated, %2$d orders migrated.', 'oli-pdf-invoices-packing-slips' ),
			count( $migrated_settings ),
			$migrated_orders
		);

		if ( $dry_run ) {
			$result['message'] = __( 'Dry run completed. No data was changed.', 'oli-pdf-invoices-packing-slips' ) . ' ' . $result['message'];
		}

		$result['data'] = array(
			'settings' => $migrated_settings,
			'orders'   => $migrated_orders,
		);

		return $result;
	}

	/**
	 * Migrate settings
	 *
	 * @param array $webtoffee_settings WebToffee settings.
	 * @param bool  $dry_run Dry run mode.
	 * @return array
	 */
	private function migrate_settings( $webtoffee_settings, $dry_run ) {
		$mapping = array(
			'invoice_number_prefix'  => 'woocommerce_wf_invoice_number_prefix',
			'invoice_number_suffix'  => 'woocommerce_wf_invoice_number_suffix',
			'invoice_number_padding' => 'woocommerce_wf_invoice_padding_number',
			'invoice_number_start'   => 'woocommerce_wf_invoice_start_number',
			'company_name'           => 'woocommerce_wf_packinglist_sender_name',
			'company_address'        => 'woocommerce_wf_packinglist_sender_address_line1',
			'vat_number'             => 'woocommerce_wf_packinglist_sender_vat',
			'footer_text'            => 'woocommerce_wf_packinglist_footer',
		);

		$migrated = array();

		foreach ( $mapping as $oli_key => $wt_key ) {
			if ( isset( $webtoffee_settings[ $wt_key ] ) && ! empty( $webtoffee_settings[ $wt_key ] ) ) {
				$value = $webtoffee_settings[ $wt_key ];

				if ( ! $dry_run ) {
					update_option( 'oli_pdf_' . $oli_key, $value );
				}

				$migrated[ $oli_key ] = $value;
			}
		}

		if ( isset( $webtoffee_settings['woocommerce_wf_packinglist_logo'] ) ) {
			$logo_url = $webtoffee_settings['woocommerce_wf_packinglist_logo'];
			if ( ! $dry_run ) {
				update_option( 'oli_pdf_logo_url', $logo_url );
			}
			$migrated['logo_url'] = $logo_url;
		}

		$current_number = get_option( 'woocommerce_wf_current_invoice_number', 0 );
		if ( $current_number && ! $dry_run ) {
			update_option( 'oli_pdf_invoice_current_number', $current_number );
			$migrated['current_invoice_number'] = $current_number;
		}

		return $migrated;
	}

	/**
	 * Migrate order data
	 *
	 * @param bool $dry_run Dry run mode.
	 * @return int
	 */
	private function migrate_order_data( $dry_run ) {
		global $wpdb;

		$order_ids = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT DISTINCT post_id FROM {$wpdb->postmeta} 
				WHERE meta_key = %s 
				AND meta_value != ''",
				'_wf_invoice_number'
			)
		);

		$migrated_count = 0;

		foreach ( $order_ids as $order_id ) {
			$order = wc_get_order( $order_id );
			if ( ! $order ) {
				continue;
			}

			$wt_invoice_number = $order->get_meta( '_wf_invoice_number' );
			$wt_invoice_date   = $order->get_meta( '_wf_invoice_date' );

			if ( $wt_invoice_number && ! $order->get_meta( '_oli_pdf_invoice_number' ) ) {
				if ( ! $dry_run ) {
					$order->update_meta_data( '_oli_pdf_invoice_number', $wt_invoice_number );
					
					if ( $wt_invoice_date ) {
						$order->update_meta_data( '_oli_pdf_invoice_date', $wt_invoice_date );
					} else {
						$order->update_meta_data( '_oli_pdf_invoice_date', $order->get_date_created()->date( 'Y-m-d H:i:s' ) );
					}
					
					$order->save();
				}

				$migrated_count++;
			}
		}

		return $migrated_count;
	}
}
