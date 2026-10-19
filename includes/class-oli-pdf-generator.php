<?php
/**
 * PDF Generator
 *
 * @package Oli_PDF
 */

namespace Oli_PDF;

use Dompdf\Dompdf;
use Dompdf\Options;

defined( 'ABSPATH' ) || exit;

/**
 * Generator class
 */
class Generator {

	/**
	 * Document types
	 *
	 * @var array
	 */
	private static $document_types = array( 'invoice', 'packing-slip', 'delivery-note', 'shipping-label' );

	/**
	 * Generate PDF
	 *
	 * @param int    $order_id Order ID.
	 * @param string $type Document type.
	 * @param string $action Action (view, download, print).
	 * @return void
	 */
	public static function generate( $order_id, $type = 'invoice', $action = 'download' ) {
		if ( ! in_array( $type, self::$document_types, true ) ) {
			wp_die( esc_html__( 'Invalid document type.', 'oli-pdf-invoices-packing-slips' ) );
		}

		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			wp_die( esc_html__( 'Order not found.', 'oli-pdf-invoices-packing-slips' ) );
		}

		if ( ! self::can_generate_for_order( $order, $type ) ) {
			wp_die( esc_html__( 'You do not have permission to view this document.', 'oli-pdf-invoices-packing-slips' ) );
		}

		$html = self::get_template_html( $order, $type );
		$pdf  = self::generate_pdf( $html );

		$filename = self::get_filename( $order, $type );

		if ( 'download' === $action ) {
			self::output_pdf( $pdf, $filename, 'D' );
		} elseif ( 'view' === $action ) {
			self::output_pdf( $pdf, $filename, 'I' );
		} else {
			return $pdf;
		}
	}

	/**
	 * Check if PDF can be generated for order
	 *
	 * @param \WC_Order $order Order object.
	 * @param string    $type Document type.
	 * @return bool
	 */
	private static function can_generate_for_order( $order, $type ) {
		if ( current_user_can( 'manage_woocommerce' ) ) {
			return true;
		}

		if ( is_user_logged_in() && $order->get_customer_id() === get_current_user_id() ) {
			if ( 'invoice' === $type && self::has_invoice_number( $order ) ) {
				return true;
			}
		}

		$token = isset( $_GET['token'] ) ? sanitize_text_field( wp_unslash( $_GET['token'] ) ) : '';
		if ( $token && self::verify_token( $order, $token ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Verify token
	 *
	 * @param \WC_Order $order Order object.
	 * @param string    $token Token.
	 * @return bool
	 */
	private static function verify_token( $order, $token ) {
		$expected = wp_hash( 'oli_pdf_' . $order->get_id() . $order->get_order_key() );
		return hash_equals( $expected, $token );
	}

	/**
	 * Check if order has invoice number
	 *
	 * @param \WC_Order $order Order object.
	 * @return bool
	 */
	private static function has_invoice_number( $order ) {
		return ! empty( $order->get_meta( '_oli_pdf_invoice_number' ) );
	}

	/**
	 * Get template HTML
	 *
	 * @param \WC_Order $order Order object.
	 * @param string    $type Document type.
	 * @return string
	 */
	private static function get_template_html( $order, $type ) {
		$template_name = $type . '.php';
		$template_path = 'oli-pdf-templates/';

		// Allow theme override.
		$template = locate_template( $template_path . $template_name );

		if ( ! $template ) {
			$template = OLI_PDF_PLUGIN_DIR . 'templates/' . $template_name;
		}

		if ( ! file_exists( $template ) ) {
			return '';
		}

		ob_start();
		include $template;
		return ob_get_clean();
	}

	/**
	 * Generate PDF from HTML
	 *
	 * @param string $html HTML content.
	 * @return string PDF content.
	 */
	private static function generate_pdf( $html ) {
		$options = new Options();
		$options->set( 'isRemoteEnabled', false );
		$options->set( 'isHtml5ParserEnabled', true );
		$options->set( 'defaultFont', 'DejaVu Sans' );

		$dompdf = new Dompdf( $options );
		$dompdf->loadHtml( $html );
		$dompdf->setPaper( 'A4', 'portrait' );
		$dompdf->render();

		return $dompdf->output();
	}

	/**
	 * Output PDF
	 *
	 * @param string $pdf PDF content.
	 * @param string $filename Filename.
	 * @param string $mode Output mode (D=download, I=inline).
	 */
	private static function output_pdf( $pdf, $filename, $mode = 'D' ) {
		header( 'Content-Type: application/pdf' );
		
		if ( 'D' === $mode ) {
			header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		} else {
			header( 'Content-Disposition: inline; filename="' . $filename . '"' );
		}
		
		header( 'Cache-Control: private, max-age=0, must-revalidate' );
		header( 'Pragma: public' );
		header( 'Content-Length: ' . strlen( $pdf ) );

		echo $pdf; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		exit;
	}

	/**
	 * Get filename
	 *
	 * @param \WC_Order $order Order object.
	 * @param string    $type Document type.
	 * @return string
	 */
	private static function get_filename( $order, $type ) {
		$filename = $type . '-' . $order->get_order_number();

		if ( 'invoice' === $type ) {
			$invoice_number = $order->get_meta( '_oli_pdf_invoice_number' );
			if ( $invoice_number ) {
				$filename = 'invoice-' . $invoice_number;
			}
		}

		return sanitize_file_name( $filename ) . '.pdf';
	}

	/**
	 * Get PDF for email attachment
	 *
	 * @param int    $order_id Order ID.
	 * @param string $type Document type.
	 * @return string|false PDF content or false.
	 */
	public static function get_pdf_for_email( $order_id, $type = 'invoice' ) {
		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return false;
		}

		$html = self::get_template_html( $order, $type );
		return self::generate_pdf( $html );
	}

	/**
	 * Get document types
	 *
	 * @return array
	 */
	public static function get_document_types() {
		return self::$document_types;
	}
}
