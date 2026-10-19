<?php
/**
 * Email attachments
 *
 * @package Oli_PDF
 */

namespace Oli_PDF;

defined( 'ABSPATH' ) || exit;

/**
 * Emails class
 */
class Emails {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_filter( 'woocommerce_email_attachments', array( $this, 'attach_pdf' ), 10, 3 );
	}

	/**
	 * Attach PDF to emails
	 *
	 * @param array  $attachments Attachments.
	 * @param string $email_id Email ID.
	 * @param object $order Order object.
	 * @return array
	 */
	public function attach_pdf( $attachments, $email_id, $order ) {
		if ( ! is_object( $order ) || ! is_a( $order, 'WC_Order' ) ) {
			return $attachments;
		}

		$attach_to = get_option( 'oli_pdf_invoice_email_attach', array( 'customer_invoice', 'customer_completed_order' ) );
		
		if ( ! is_array( $attach_to ) ) {
			$attach_to = array();
		}

		if ( ! in_array( $email_id, $attach_to, true ) ) {
			return $attachments;
		}

		$invoice_number = Numbering::get_invoice_number( $order );
		if ( ! $invoice_number ) {
			if ( Numbering::should_generate_invoice( $order ) ) {
				Numbering::generate_for_order( $order );
			} else {
				return $attachments;
			}
		}

		$pdf = Generator::get_pdf_for_email( $order->get_id(), 'invoice' );
		if ( ! $pdf ) {
			return $attachments;
		}

		$upload_dir = wp_upload_dir();
		$temp_dir   = trailingslashit( $upload_dir['basedir'] ) . 'oli-pdf-temp/';

		if ( ! file_exists( $temp_dir ) ) {
			wp_mkdir_p( $temp_dir );
		}

		$filename = 'invoice-' . $order->get_order_number() . '.pdf';
		$filepath = $temp_dir . $filename;

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		file_put_contents( $filepath, $pdf );

		if ( file_exists( $filepath ) ) {
			$attachments[] = $filepath;
		}

		add_action( 'woocommerce_email_after_order_table', array( $this, 'cleanup_temp_files' ), 10, 1 );

		return $attachments;
	}

	/**
	 * Cleanup temp files
	 *
	 * @param \WC_Order $order Order object.
	 */
	public function cleanup_temp_files( $order ) {
		$upload_dir = wp_upload_dir();
		$temp_dir   = trailingslashit( $upload_dir['basedir'] ) . 'oli-pdf-temp/';

		if ( ! is_dir( $temp_dir ) ) {
			return;
		}

		$files = glob( $temp_dir . '*.pdf' );
		foreach ( $files as $file ) {
			if ( is_file( $file ) && time() - filemtime( $file ) > 3600 ) {
				// phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink
				unlink( $file );
			}
		}
	}
}
