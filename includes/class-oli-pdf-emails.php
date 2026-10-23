<?php
/**
 * Email attachments.
 *
 * @package Oli_PDF
 */

namespace Oli_PDF;

defined( 'ABSPATH' ) || exit;

/**
 * Emails class.
 */
class Emails {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_filter( 'woocommerce_email_attachments', array( $this, 'attach_pdf' ), 10, 3 );
	}

	/**
	 * Attach PDF to emails.
	 *
	 * @param array  $attachments Attachments.
	 * @param string $email_id    Email ID.
	 * @param object $order       Order object.
	 * @return array
	 */
	public function attach_pdf( $attachments, $email_id, $order ) {
		if ( ! is_object( $order ) || ! is_a( $order, 'WC_Order' ) ) {
			return $attachments;
		}

		if ( 'customer_refunded_order' === $email_id && 'yes' === get_option( 'oli_pdf_enable_credit_notes', 'yes' ) ) {
			Numbering::generate_credit_note_for_order( $order );
			$path = $this->write_temp_pdf( $order, 'credit-note' );
			if ( $path ) {
				$attachments[] = $path;
			}
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
			if ( ! Numbering::should_generate_invoice( $order ) ) {
				return $attachments;
			}
			Numbering::generate_for_order( $order );
		}

		$path = $this->write_temp_pdf( $order, 'invoice' );
		if ( $path ) {
			$attachments[] = $path;
		}

		add_action( 'woocommerce_email_after_order_table', array( $this, 'cleanup_temp_files' ), 10, 1 );

		return $attachments;
	}

	/**
	 * Write a temporary PDF file.
	 *
	 * @param \WC_Order $order Order.
	 * @param string    $type  Document type.
	 * @return string
	 */
	private function write_temp_pdf( $order, $type ) {
		$pdf = Generator::get_pdf_for_email( $order->get_id(), $type );
		if ( ! $pdf ) {
			return '';
		}

		$temp_dir = $this->get_temp_dir();
		if ( ! $temp_dir ) {
			return '';
		}

		$filename = $type . '-' . $order->get_order_number() . '.pdf';
		$filepath = $temp_dir . $filename;

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents -- temporary PDF for mailer.
		file_put_contents( $filepath, $pdf );

		return file_exists( $filepath ) ? $filepath : '';
	}

	/**
	 * Get temp directory.
	 *
	 * @return string
	 */
	private function get_temp_dir() {
		$upload_dir = wp_upload_dir();
		$temp_dir   = trailingslashit( $upload_dir['basedir'] ) . 'oli-pdf-temp/';

		if ( ! file_exists( $temp_dir ) ) {
			wp_mkdir_p( $temp_dir );
		}

		return file_exists( $temp_dir ) ? $temp_dir : '';
	}

	/**
	 * Cleanup temp files older than one hour.
	 *
	 * @param \WC_Order $order Order object.
	 */
	public function cleanup_temp_files( $order ) {
		unset( $order );

		$upload_dir = wp_upload_dir();
		$temp_dir   = trailingslashit( $upload_dir['basedir'] ) . 'oli-pdf-temp/';

		if ( ! is_dir( $temp_dir ) ) {
			return;
		}

		$files = glob( $temp_dir . '*.pdf' );
		if ( ! $files ) {
			return;
		}

		foreach ( $files as $file ) {
			if ( is_file( $file ) && ( time() - filemtime( $file ) ) > 3600 ) {
				wp_delete_file( $file );
			}
		}
	}
}
