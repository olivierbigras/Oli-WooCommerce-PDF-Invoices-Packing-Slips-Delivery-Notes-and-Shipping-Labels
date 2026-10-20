<?php
/**
 * PDF Generator.
 *
 * @package Oli_PDF
 */

namespace Oli_PDF;

use Dompdf\Dompdf;
use Dompdf\Options;

defined( 'ABSPATH' ) || exit;

/**
 * Generator class.
 */
class Generator {

	/**
	 * Document types.
	 *
	 * @var array
	 */
	private static $document_types = array(
		'invoice',
		'packing-slip',
		'delivery-note',
		'shipping-label',
		'credit-note',
	);

	/**
	 * Generate PDF.
	 *
	 * @param int    $order_id Order ID.
	 * @param string $type     Document type.
	 * @param string $action   Action (view, download, print).
	 * @return string|void
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

		if ( 'shipping-label' === $type ) {
			$html = self::get_shipping_label_sheet_html( array( $order ) );
		} else {
			$html = self::get_template_html( $order, $type );
		}

		$pdf      = self::generate_pdf( $html, $type );
		$filename = self::get_filename( $order, $type );

		if ( 'download' === $action ) {
			self::output_pdf( $pdf, $filename, 'D' );
		} elseif ( 'view' === $action ) {
			self::output_pdf( $pdf, $filename, 'I' );
		}

		return $pdf;
	}

	/**
	 * Check if PDF can be generated for order.
	 *
	 * @param \WC_Order $order Order object.
	 * @param string    $type  Document type.
	 * @return bool
	 */
	private static function can_generate_for_order( $order, $type ) {
		if ( current_user_can( 'manage_woocommerce' ) ) {
			return true;
		}

		if ( is_user_logged_in() && (int) $order->get_customer_id() === (int) get_current_user_id() ) {
			if ( 'invoice' === $type && self::has_invoice_number( $order ) ) {
				return true;
			}
			if ( 'credit-note' === $type && Numbering::get_credit_note_number( $order ) ) {
				return true;
			}
		}

		$token = isset( $_GET['token'] ) ? sanitize_text_field( wp_unslash( $_GET['token'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- token is a signed hash, not a form nonce.
		if ( $token && self::verify_token( $order, $token ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Verify token.
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
	 * Check if order has invoice number.
	 *
	 * @param \WC_Order $order Order object.
	 * @return bool
	 */
	private static function has_invoice_number( $order ) {
		return ! empty( $order->get_meta( '_oli_pdf_invoice_number' ) );
	}

	/**
	 * Get template HTML.
	 *
	 * @param \WC_Order $order Order object.
	 * @param string    $type  Document type.
	 * @return string
	 */
	public static function get_template_html( $order, $type ) {
		$template_name = $type . '.php';
		$template_path = 'oli-pdf-templates/';
		$template      = locate_template( $template_path . $template_name );

		if ( ! $template ) {
			$template = OLI_PDF_PLUGIN_DIR . 'templates/' . $template_name;
		}

		if ( ! file_exists( $template ) ) {
			return '';
		}

		ob_start();
		include $template;
		return (string) ob_get_clean();
	}

	/**
	 * Build a multi-label sheet for one or more orders.
	 *
	 * @param array $orders Order objects.
	 * @return string
	 */
	public static function get_shipping_label_sheet_html( $orders ) {
		$columns = max( 1, (int) get_option( 'oli_pdf_label_columns', 1 ) );
		$rows    = max( 1, (int) get_option( 'oli_pdf_label_rows', 1 ) );
		$copies  = max( 1, (int) get_option( 'oli_pdf_labels_per_sheet', 1 ) );
		$cells   = $columns * $rows;

		$labels = array();
		foreach ( $orders as $order ) {
			$label_html = self::get_template_html( $order, 'shipping-label' );
			$repeat     = min( $copies, $cells );
			for ( $i = 0; $i < $repeat; $i++ ) {
				$labels[] = $label_html;
				if ( count( $labels ) >= $cells ) {
					break 2;
				}
			}
		}

		while ( count( $labels ) < $cells ) {
			$labels[] = '';
		}

		$width  = ( 100 / $columns );
		$height = ( 100 / $rows );

		$html  = '<!DOCTYPE html><html><head><meta charset="utf-8"><style>';
		$html .= 'html,body{margin:0;padding:0;width:100%;height:100%;}';
		$html .= '.oli-pdf-sheet{width:100%;height:100%;border-collapse:collapse;}';
		$html .= '.oli-pdf-sheet td{width:' . esc_attr( $width ) . '%;height:' . esc_attr( $height ) . '%;vertical-align:top;overflow:hidden;border:1px dashed #ccc;padding:4px;}';
		$html .= '</style></head><body><table class="oli-pdf-sheet">';

		$index = 0;
		for ( $r = 0; $r < $rows; $r++ ) {
			$html .= '<tr>';
			for ( $c = 0; $c < $columns; $c++ ) {
				$html .= '<td>' . ( isset( $labels[ $index ] ) ? $labels[ $index ] : '' ) . '</td>';
				++$index;
			}
			$html .= '</tr>';
		}

		$html .= '</table></body></html>';

		return $html;
	}

	/**
	 * Generate PDF from HTML.
	 *
	 * @param string $html HTML content.
	 * @param string $type Document type.
	 * @return string
	 */
	public static function generate_pdf( $html, $type = 'invoice' ) {
		$upload = wp_upload_dir();
		$chroot = isset( $upload['basedir'] ) ? $upload['basedir'] : ABSPATH;

		$options = new Options();
		$options->set( 'isRemoteEnabled', false );
		$options->set( 'isHtml5ParserEnabled', true );
		$options->set( 'defaultFont', 'DejaVu Sans' );
		$options->set( 'chroot', $chroot );

		$dompdf = new Dompdf( $options );
		$dompdf->loadHtml( $html );

		$paper = self::get_paper( $type );
		$dompdf->setPaper( $paper['size'], $paper['orientation'] );
		$dompdf->render();

		return $dompdf->output();
	}

	/**
	 * Get paper size for a document type.
	 *
	 * @param string $type Document type.
	 * @return array
	 */
	public static function get_paper( $type ) {
		if ( 'shipping-label' !== $type ) {
			return array(
				'size'        => 'A4',
				'orientation' => 'portrait',
			);
		}

		$size    = get_option( 'oli_pdf_label_size', 'a4' );
		$columns = max( 1, (int) get_option( 'oli_pdf_label_columns', 1 ) );
		$rows    = max( 1, (int) get_option( 'oli_pdf_label_rows', 1 ) );

		if ( ( $columns * $rows ) > 1 && in_array( $size, array( '4x6', '4x4', 'custom' ), true ) ) {
			return array(
				'size'        => 'A4',
				'orientation' => 'portrait',
			);
		}

		switch ( $size ) {
			case 'letter':
				return array(
					'size'        => array( 0, 0, 8.5 * 72, 11 * 72 ),
					'orientation' => 'portrait',
				);
			case '4x6':
				return array(
					'size'        => array( 0, 0, 4 * 72, 6 * 72 ),
					'orientation' => 'portrait',
				);
			case '4x4':
				return array(
					'size'        => array( 0, 0, 4 * 72, 4 * 72 ),
					'orientation' => 'portrait',
				);
			case 'custom':
				$width  = (float) get_option( 'oli_pdf_label_custom_width', 100 ) * 72 / 25.4;
				$height = (float) get_option( 'oli_pdf_label_custom_height', 150 ) * 72 / 25.4;
				return array(
					'size'        => array( 0, 0, $width, $height ),
					'orientation' => 'portrait',
				);
			default:
				return array(
					'size'        => 'A4',
					'orientation' => 'portrait',
				);
		}
	}

	/**
	 * Output PDF.
	 *
	 * @param string $pdf      PDF content.
	 * @param string $filename Filename.
	 * @param string $mode     Output mode (D=download, I=inline).
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

		echo $pdf; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- binary PDF stream.
		exit;
	}

	/**
	 * Get filename.
	 *
	 * @param \WC_Order $order Order object.
	 * @param string    $type  Document type.
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

		if ( 'credit-note' === $type ) {
			$credit_number = $order->get_meta( '_oli_pdf_credit_note_number' );
			if ( $credit_number ) {
				$filename = 'credit-note-' . $credit_number;
			}
		}

		return sanitize_file_name( $filename ) . '.pdf';
	}

	/**
	 * Get PDF for email attachment.
	 *
	 * @param int    $order_id Order ID.
	 * @param string $type     Document type.
	 * @return string|false
	 */
	public static function get_pdf_for_email( $order_id, $type = 'invoice' ) {
		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return false;
		}

		if ( 'shipping-label' === $type ) {
			$html = self::get_shipping_label_sheet_html( array( $order ) );
		} else {
			$html = self::get_template_html( $order, $type );
		}

		return self::generate_pdf( $html, $type );
	}

	/**
	 * Get document types.
	 *
	 * @return array
	 */
	public static function get_document_types() {
		$types = self::$document_types;
		if ( 'yes' !== get_option( 'oli_pdf_enable_credit_notes', 'yes' ) ) {
			$types = array_values( array_diff( $types, array( 'credit-note' ) ) );
		}
		return $types;
	}

	/**
	 * Get primary template color.
	 *
	 * @return string
	 */
	public static function get_primary_color() {
		$color = sanitize_hex_color( get_option( 'oli_pdf_template_primary_color', '#000000' ) );
		return $color ? $color : '#000000';
	}

	/**
	 * Get secondary template color.
	 *
	 * @return string
	 */
	public static function get_secondary_color() {
		$color = sanitize_hex_color( get_option( 'oli_pdf_template_secondary_color', '#f5f5f5' ) );
		return $color ? $color : '#f5f5f5';
	}

	/**
	 * Convert a local image file to a data URI for Dompdf (no remote/CDN).
	 *
	 * @param string $path Absolute file path.
	 * @return string
	 */
	public static function file_to_data_uri( $path ) {
		if ( ! $path || ! file_exists( $path ) || ! is_readable( $path ) ) {
			return '';
		}

		$mime = wp_check_filetype( $path );
		$type = ! empty( $mime['type'] ) ? $mime['type'] : 'image/png';
		$data = file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local file for PDF embed.

		if ( false === $data ) {
			return '';
		}

		return 'data:' . $type . ';base64,' . base64_encode( $data );
	}

	/**
	 * Resolve a media URL or attachment ID to a local file path.
	 *
	 * @param string|int $url_or_id URL or attachment ID.
	 * @return string
	 */
	public static function resolve_local_image_path( $url_or_id ) {
		if ( is_numeric( $url_or_id ) ) {
			$path = get_attached_file( (int) $url_or_id );
			return $path ? $path : '';
		}

		$url = esc_url_raw( $url_or_id );
		if ( ! $url ) {
			return '';
		}

		$attachment_id = attachment_url_to_postid( $url );
		if ( $attachment_id ) {
			$path = get_attached_file( $attachment_id );
			if ( $path && file_exists( $path ) ) {
				return $path;
			}
		}

		$uploads = wp_upload_dir();
		if ( ! empty( $uploads['baseurl'] ) && 0 === strpos( $url, $uploads['baseurl'] ) ) {
			$relative = str_replace( $uploads['baseurl'], '', $url );
			$path     = trailingslashit( $uploads['basedir'] ) . ltrim( $relative, '/' );
			if ( file_exists( $path ) ) {
				return $path;
			}
		}

		return '';
	}

	/**
	 * Get company logo as a data URI.
	 *
	 * @return string
	 */
	public static function get_logo_src() {
		$url = get_option( 'oli_pdf_logo_url', '' );
		if ( ! $url ) {
			return '';
		}

		$path = self::resolve_local_image_path( $url );
		return $path ? self::file_to_data_uri( $path ) : '';
	}

	/**
	 * Get an optimized product image data URI.
	 *
	 * Uses a WP intermediate size close to the configured pixel size instead of
	 * embedding the original upload.
	 *
	 * @param \WC_Product|false $product Product.
	 * @return string
	 */
	public static function get_product_image_src( $product ) {
		if ( ! $product || ! is_object( $product ) ) {
			return '';
		}

		$image_id = $product->get_image_id();
		if ( ! $image_id ) {
			return '';
		}

		$size_px = max( 20, min( 200, (int) get_option( 'oli_pdf_product_image_size', 50 ) ) );
		$src     = wp_get_attachment_image_src( $image_id, array( $size_px, $size_px ) );

		if ( $src && ! empty( $src[0] ) ) {
			$path = self::resolve_local_image_path( $src[0] );
			if ( $path ) {
				return self::file_to_data_uri( $path );
			}
		}

		$original = get_attached_file( $image_id );
		return $original ? self::file_to_data_uri( $original ) : '';
	}

	/**
	 * Get configured product image pixel size.
	 *
	 * @return int
	 */
	public static function get_product_image_size() {
		return max( 20, min( 200, (int) get_option( 'oli_pdf_product_image_size', 50 ) ) );
	}
}
