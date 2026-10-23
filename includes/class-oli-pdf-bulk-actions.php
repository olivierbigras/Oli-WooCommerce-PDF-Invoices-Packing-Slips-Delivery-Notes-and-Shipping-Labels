<?php
/**
 * Bulk actions for orders.
 *
 * @package Oli_PDF
 */

namespace Oli_PDF;

defined( 'ABSPATH' ) || exit;

/**
 * Bulk_Actions class.
 */
class Bulk_Actions {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_filter( 'bulk_actions-woocommerce_page_wc-orders', array( $this, 'add_bulk_actions' ) );
		add_filter( 'bulk_actions-edit-shop_order', array( $this, 'add_bulk_actions' ) );
		add_filter( 'handle_bulk_actions-woocommerce_page_wc-orders', array( $this, 'handle_bulk_actions' ), 10, 3 );
		add_filter( 'handle_bulk_actions-edit-shop_order', array( $this, 'handle_bulk_actions' ), 10, 3 );
	}

	/**
	 * Add bulk actions.
	 *
	 * @param array $actions Actions.
	 * @return array
	 */
	public function add_bulk_actions( $actions ) {
		$actions['oli_pdf_download_invoices']        = __( 'Download Invoices (PDF)', 'oli-pdf-invoices-packing-slips' );
		$actions['oli_pdf_download_packing_slips']   = __( 'Download Packing Slips (PDF)', 'oli-pdf-invoices-packing-slips' );
		$actions['oli_pdf_download_delivery_notes']  = __( 'Download Delivery Notes (PDF)', 'oli-pdf-invoices-packing-slips' );
		$actions['oli_pdf_download_shipping_labels'] = __( 'Download Shipping Labels (PDF)', 'oli-pdf-invoices-packing-slips' );
		$actions['oli_pdf_download_credit_notes']    = __( 'Download Credit Notes (PDF)', 'oli-pdf-invoices-packing-slips' );

		return $actions;
	}

	/**
	 * Handle bulk actions.
	 *
	 * @param string $redirect_to Redirect URL.
	 * @param string $action      Action.
	 * @param array  $post_ids    Post IDs.
	 * @return string
	 */
	public function handle_bulk_actions( $redirect_to, $action, $post_ids ) {
		$type_map = array(
			'oli_pdf_download_invoices'        => 'invoice',
			'oli_pdf_download_packing_slips'   => 'packing-slip',
			'oli_pdf_download_delivery_notes'  => 'delivery-note',
			'oli_pdf_download_shipping_labels' => 'shipping-label',
			'oli_pdf_download_credit_notes'    => 'credit-note',
		);

		if ( ! isset( $type_map[ $action ] ) ) {
			return $redirect_to;
		}

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return $redirect_to;
		}

		$this->generate_bulk_pdf( $post_ids, $type_map[ $action ] );

		return $redirect_to;
	}

	/**
	 * Generate bulk PDF zip.
	 *
	 * @param array  $order_ids Order IDs.
	 * @param string $type      Document type.
	 */
	private function generate_bulk_pdf( $order_ids, $type ) {
		require_once ABSPATH . 'wp-admin/includes/class-pclzip.php';

		$upload_dir = wp_upload_dir();
		$temp_dir   = trailingslashit( $upload_dir['basedir'] ) . 'oli-pdf-temp/';

		if ( ! file_exists( $temp_dir ) ) {
			wp_mkdir_p( $temp_dir );
		}

		$zip_filename = 'bulk-' . $type . '-' . time() . '.zip';
		$zip_filepath = $temp_dir . $zip_filename;
		$archive      = new \PclZip( $zip_filepath );
		$files        = array();

		foreach ( $order_ids as $order_id ) {
			$order = wc_get_order( $order_id );
			if ( ! $order ) {
				continue;
			}

			if ( 'invoice' === $type ) {
				if ( ! Numbering::get_invoice_number( $order ) ) {
					if ( ! Numbering::should_generate_invoice( $order ) ) {
						continue;
					}
					Numbering::generate_for_order( $order );
				}
			}

			if ( 'credit-note' === $type && ! Numbering::get_credit_note_number( $order ) ) {
				continue;
			}

			$pdf = Generator::get_pdf_for_email( $order->get_id(), $type );
			if ( ! $pdf ) {
				continue;
			}

			$filename = $type . '-' . $order->get_order_number() . '.pdf';
			$filepath = $temp_dir . $filename;

			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents -- temporary bulk export.
			file_put_contents( $filepath, $pdf );

			if ( file_exists( $filepath ) ) {
				$files[] = $filepath;
			}
		}

		if ( empty( $files ) ) {
			wp_die( esc_html__( 'No PDF documents could be generated.', 'oli-pdf-invoices-packing-slips' ) );
		}

		$archive->create( $files, PCLZIP_OPT_REMOVE_PATH, $temp_dir );

		header( 'Content-Type: application/zip' );
		header( 'Content-Disposition: attachment; filename="' . $zip_filename . '"' );
		header( 'Content-Length: ' . filesize( $zip_filepath ) );

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_readfile -- streaming the generated zip.
		readfile( $zip_filepath );

		foreach ( $files as $file ) {
			wp_delete_file( $file );
		}
		wp_delete_file( $zip_filepath );

		exit;
	}
}
