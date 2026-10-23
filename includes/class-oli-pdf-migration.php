<?php
/**
 * Migration from WebToffee Print Invoices (5.x).
 *
 * Confirmed against print-invoices-packing-slip-labels-for-woocommerce 5.0.3:
 * - Main settings option: Wf_Woocommerce_Packing_List
 * - Invoice module option: wf_woocommerce_packing_list_invoice
 * - Current number key: woocommerce_wf_Current_Invoice_number (inside invoice option)
 * - Order invoice number meta: wf_invoice_number
 * - Order invoice date meta: _wf_invoice_date (unix timestamp)
 *
 * @package Oli_PDF
 */

namespace Oli_PDF;

defined( 'ABSPATH' ) || exit;

/**
 * Migration class.
 */
class Migration {

	/**
	 * WebToffee 5.x main settings option (WF_PKLIST_SETTINGS_FIELD).
	 */
	const WEBTOFFEE_MAIN_OPTION = 'Wf_Woocommerce_Packing_List';

	/**
	 * Older/docs option name kept as a fallback.
	 */
	const WEBTOFFEE_MAIN_OPTION_LEGACY = 'wf_woocommerce_packing_list';

	/**
	 * WebToffee 5.x invoice module option (WF_PKLIST_POST_TYPE + '_invoice').
	 */
	const WEBTOFFEE_INVOICE_OPTION = 'wf_woocommerce_packing_list_invoice';

	/**
	 * Invoice number stored on the order (no leading underscore).
	 */
	const WEBTOFFEE_ORDER_INVOICE_NUMBER = 'wf_invoice_number';

	/**
	 * Legacy invoice number meta.
	 */
	const WEBTOFFEE_ORDER_INVOICE_NUMBER_LEGACY = '_wf_invoice_number';

	/**
	 * Invoice date stored on the order (leading underscore, unix timestamp).
	 */
	const WEBTOFFEE_ORDER_INVOICE_DATE = '_wf_invoice_date';

	/**
	 * Legacy invoice date meta (no underscore).
	 */
	const WEBTOFFEE_ORDER_INVOICE_DATE_LEGACY = 'wf_invoice_date';

	/**
	 * Current invoice counter key inside the invoice option array.
	 */
	const WEBTOFFEE_CURRENT_NUMBER_KEY = 'woocommerce_wf_Current_Invoice_number';

	/**
	 * Documented WebToffee 5.x keys used by this plugin.
	 *
	 * @return array
	 */
	public static function get_webtoffee_keys() {
		return array(
			'main_option'                 => self::WEBTOFFEE_MAIN_OPTION,
			'main_option_legacy'          => self::WEBTOFFEE_MAIN_OPTION_LEGACY,
			'invoice_option'              => self::WEBTOFFEE_INVOICE_OPTION,
			'order_invoice_number'        => self::WEBTOFFEE_ORDER_INVOICE_NUMBER,
			'order_invoice_number_legacy' => self::WEBTOFFEE_ORDER_INVOICE_NUMBER_LEGACY,
			'order_invoice_date'          => self::WEBTOFFEE_ORDER_INVOICE_DATE,
			'order_invoice_date_legacy'   => self::WEBTOFFEE_ORDER_INVOICE_DATE_LEGACY,
			'current_number_key'          => self::WEBTOFFEE_CURRENT_NUMBER_KEY,
			'settings_map'                => self::get_settings_map(),
		);
	}

	/**
	 * Map Oli option keys to WebToffee invoice-module keys.
	 *
	 * @return array
	 */
	public static function get_invoice_settings_map() {
		return array(
			'invoice_number_prefix'  => 'woocommerce_wf_invoice_number_prefix',
			'invoice_number_suffix'  => 'woocommerce_wf_invoice_number_postfix',
			'invoice_number_padding' => 'woocommerce_wf_invoice_padding_number',
			'invoice_number_start'   => 'woocommerce_wf_invoice_start_number',
		);
	}

	/**
	 * Map Oli option keys to WebToffee main-settings keys.
	 *
	 * @return array
	 */
	public static function get_settings_map() {
		return array(
			'company_name'     => 'woocommerce_wf_packinglist_sender_name',
			'company_name_alt' => 'woocommerce_wf_packinglist_companyname',
			'address_line1'    => 'woocommerce_wf_packinglist_sender_address_line1',
			'address_line2'    => 'woocommerce_wf_packinglist_sender_address_line2',
			'address_city'     => 'woocommerce_wf_packinglist_sender_city',
			'address_postcode' => 'woocommerce_wf_packinglist_sender_postalcode',
			'vat_number'       => 'woocommerce_wf_packinglist_sender_vat',
			'footer_text'      => 'woocommerce_wf_packinglist_footer',
			'logo_url'         => 'woocommerce_wf_packinglist_logo',
		);
	}

	/**
	 * Map WebToffee option arrays to Oli settings without writing to the database.
	 *
	 * @param array $main    Main settings array.
	 * @param array $invoice Invoice module settings array.
	 * @return array
	 */
	public static function map_webtoffee_settings( $main, $invoice ) {
		$mapped  = array();
		$main    = is_array( $main ) ? $main : array();
		$invoice = is_array( $invoice ) ? $invoice : array();

		foreach ( self::get_invoice_settings_map() as $oli_key => $wt_key ) {
			if ( isset( $invoice[ $wt_key ] ) && '' !== $invoice[ $wt_key ] && false !== $invoice[ $wt_key ] ) {
				$mapped[ $oli_key ] = $invoice[ $wt_key ];
			}
		}

		if ( isset( $invoice[ self::WEBTOFFEE_CURRENT_NUMBER_KEY ] ) && '' !== $invoice[ self::WEBTOFFEE_CURRENT_NUMBER_KEY ] ) {
			$mapped['invoice_current_number'] = (int) $invoice[ self::WEBTOFFEE_CURRENT_NUMBER_KEY ];
		}

		if ( ! empty( $invoice['woocommerce_wf_packinglist_logo'] ) ) {
			$mapped['logo_url'] = $invoice['woocommerce_wf_packinglist_logo'];
		} elseif ( ! empty( $main['woocommerce_wf_packinglist_logo'] ) ) {
			$mapped['logo_url'] = $main['woocommerce_wf_packinglist_logo'];
		}

		if ( ! empty( $main['woocommerce_wf_packinglist_sender_name'] ) ) {
			$mapped['company_name'] = $main['woocommerce_wf_packinglist_sender_name'];
		} elseif ( ! empty( $main['woocommerce_wf_packinglist_companyname'] ) ) {
			$mapped['company_name'] = $main['woocommerce_wf_packinglist_companyname'];
		}

		$address_parts = array();
		foreach ( array( 'woocommerce_wf_packinglist_sender_address_line1', 'woocommerce_wf_packinglist_sender_address_line2', 'woocommerce_wf_packinglist_sender_city', 'woocommerce_wf_packinglist_sender_postalcode' ) as $addr_key ) {
			if ( ! empty( $main[ $addr_key ] ) ) {
				$address_parts[] = $main[ $addr_key ];
			}
		}
		if ( $address_parts ) {
			$mapped['company_address'] = implode( "\n", $address_parts );
		}

		if ( ! empty( $main['woocommerce_wf_packinglist_sender_vat'] ) ) {
			$mapped['vat_number'] = $main['woocommerce_wf_packinglist_sender_vat'];
		}

		if ( ! empty( $main['woocommerce_wf_packinglist_footer'] ) ) {
			$mapped['footer_text'] = $main['woocommerce_wf_packinglist_footer'];
		}

		if ( ! empty( $invoice['woocommerce_wf_generate_for_orderstatus'] ) && is_array( $invoice['woocommerce_wf_generate_for_orderstatus'] ) ) {
			$statuses = array();
			foreach ( $invoice['woocommerce_wf_generate_for_orderstatus'] as $status ) {
				$statuses[] = str_replace( 'wc-', '', $status );
			}
			$mapped['invoice_generation_statuses'] = $statuses;
		}

		return $mapped;
	}

	/**
	 * Normalize a WebToffee invoice date to MySQL datetime.
	 *
	 * WebToffee 5.x stores `_wf_invoice_date` as a unix timestamp.
	 *
	 * @param mixed $raw Raw meta value.
	 * @return string
	 */
	public static function normalize_invoice_date( $raw ) {
		if ( '' === $raw || false === $raw || null === $raw ) {
			return '';
		}

		if ( is_numeric( $raw ) ) {
			return gmdate( 'Y-m-d H:i:s', (int) $raw );
		}

		$timestamp = strtotime( (string) $raw );
		if ( false === $timestamp ) {
			return '';
		}

		return gmdate( 'Y-m-d H:i:s', $timestamp );
	}

	/**
	 * Order meta keys that may hold a WebToffee invoice number.
	 *
	 * @return array
	 */
	public static function get_order_invoice_number_keys() {
		return array(
			self::WEBTOFFEE_ORDER_INVOICE_NUMBER,
			self::WEBTOFFEE_ORDER_INVOICE_NUMBER_LEGACY,
		);
	}

	/**
	 * Order meta keys that may hold a WebToffee invoice date.
	 *
	 * @return array
	 */
	public static function get_order_invoice_date_keys() {
		return array(
			self::WEBTOFFEE_ORDER_INVOICE_DATE,
			self::WEBTOFFEE_ORDER_INVOICE_DATE_LEGACY,
		);
	}

	/**
	 * Migrate data.
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

		$main    = $this->get_webtoffee_option( self::WEBTOFFEE_MAIN_OPTION );
		$legacy  = $this->get_webtoffee_option( self::WEBTOFFEE_MAIN_OPTION_LEGACY );
		$invoice = $this->get_webtoffee_option( self::WEBTOFFEE_INVOICE_OPTION );

		if ( ! $main && ! $legacy && ! $invoice ) {
			$result['message'] = __( 'No WebToffee settings found. Nothing to migrate.', 'oli-pdf-invoices-packing-slips' );
			return $result;
		}

		$mapped            = self::map_webtoffee_settings( $main ? $main : $legacy, $invoice ? $invoice : array() );
		$migrated_settings = $this->apply_mapped_settings( $mapped, $dry_run );
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
	 * Read a WebToffee option array.
	 *
	 * @param string $name Option name.
	 * @return array
	 */
	private function get_webtoffee_option( $name ) {
		$value = get_option( $name, array() );
		return is_array( $value ) ? $value : array();
	}

	/**
	 * Persist mapped settings.
	 *
	 * @param array $mapped  Mapped settings.
	 * @param bool  $dry_run Dry run.
	 * @return array
	 */
	private function apply_mapped_settings( $mapped, $dry_run ) {
		$written = array();

		foreach ( $mapped as $oli_key => $value ) {
			if ( ! $dry_run ) {
				if ( 'invoice_current_number' === $oli_key ) {
					update_option( 'oli_pdf_invoice_current_number', (int) $value );
				} else {
					update_option( 'oli_pdf_' . $oli_key, $value );
				}
			}
			$written[ $oli_key ] = $value;
		}

		return $written;
	}

	/**
	 * Migrate invoice numbers from orders.
	 *
	 * @param bool $dry_run Dry run mode.
	 * @return int
	 */
	private function migrate_order_data( $dry_run ) {
		$order_ids = $this->find_webtoffee_order_ids();
		$count     = 0;

		foreach ( $order_ids as $order_id ) {
			$order = wc_get_order( $order_id );
			if ( ! $order ) {
				continue;
			}

			$wt_number = $order->get_meta( self::WEBTOFFEE_ORDER_INVOICE_NUMBER );
			if ( ! $wt_number ) {
				$wt_number = $order->get_meta( self::WEBTOFFEE_ORDER_INVOICE_NUMBER_LEGACY );
			}

			if ( ! $wt_number || $order->get_meta( '_oli_pdf_invoice_number' ) ) {
				continue;
			}

			$wt_date = $order->get_meta( self::WEBTOFFEE_ORDER_INVOICE_DATE );
			if ( ! $wt_date ) {
				$wt_date = $order->get_meta( self::WEBTOFFEE_ORDER_INVOICE_DATE_LEGACY );
			}

			$normalized_date = self::normalize_invoice_date( $wt_date );
			if ( ! $normalized_date && $order->get_date_created() ) {
				$normalized_date = $order->get_date_created()->date( 'Y-m-d H:i:s' );
			}

			if ( ! $dry_run ) {
				$order->update_meta_data( '_oli_pdf_invoice_number', $wt_number );
				if ( $normalized_date ) {
					$order->update_meta_data( '_oli_pdf_invoice_date', $normalized_date );
				}
				$order->save();
			}

			++$count;
		}

		return $count;
	}

	/**
	 * Find orders that already have a WebToffee invoice number (posts + HPOS).
	 *
	 * @return array
	 */
	private function find_webtoffee_order_ids() {
		global $wpdb;

		$keys = self::get_order_invoice_number_keys();
		$ids  = array();

		foreach ( $keys as $key ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- one-time migration lookup.
			$post_ids = $wpdb->get_col(
				$wpdb->prepare(
					"SELECT DISTINCT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value != ''",
					$key
				)
			);
			$ids      = array_merge( $ids, is_array( $post_ids ) ? $post_ids : array() );

			$orders_meta = $wpdb->prefix . 'wc_orders_meta';
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- HPOS table may not exist.
			$table_exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $orders_meta ) );
			if ( $table_exists ) {
				// Table names cannot be placeholders in $wpdb->prepare().
				// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$hpos_ids = $wpdb->get_col(
					$wpdb->prepare(
						"SELECT DISTINCT order_id FROM {$orders_meta} WHERE meta_key = %s AND meta_value != ''",
						$key
					)
				);
				// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$ids = array_merge( $ids, is_array( $hpos_ids ) ? $hpos_ids : array() );
			}
		}

		return array_values( array_unique( array_map( 'absint', $ids ) ) );
	}
}
