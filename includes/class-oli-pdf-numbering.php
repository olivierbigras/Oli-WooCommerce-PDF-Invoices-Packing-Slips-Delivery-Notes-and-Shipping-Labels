<?php
/**
 * Invoice numbering
 *
 * @package Oli_PDF
 */

namespace Oli_PDF;

defined( 'ABSPATH' ) || exit;

/**
 * Numbering class
 */
class Numbering {

	/**
	 * Get next invoice number
	 *
	 * @return int
	 */
	public static function get_next_number() {
		$current = get_option( 'oli_pdf_invoice_current_number', 0 );
		$start   = get_option( 'oli_pdf_invoice_number_start', 1 );

		if ( 0 === $current ) {
			$next = $start;
		} else {
			$next = $current + 1;
		}

		update_option( 'oli_pdf_invoice_current_number', $next );

		return $next;
	}

	/**
	 * Format invoice number
	 *
	 * @param int $number Invoice number.
	 * @return string
	 */
	public static function format_number( $number ) {
		$prefix  = get_option( 'oli_pdf_invoice_number_prefix', '' );
		$suffix  = get_option( 'oli_pdf_invoice_number_suffix', '' );
		$padding = get_option( 'oli_pdf_invoice_number_padding', 4 );

		$formatted = str_pad( $number, (int) $padding, '0', STR_PAD_LEFT );

		return $prefix . $formatted . $suffix;
	}

	/**
	 * Generate invoice number for order
	 *
	 * @param \WC_Order $order Order object.
	 * @return string|false
	 */
	public static function generate_for_order( $order ) {
		if ( ! $order ) {
			return false;
		}

		$existing = $order->get_meta( '_oli_pdf_invoice_number' );
		if ( $existing ) {
			return $existing;
		}

		$number           = self::get_next_number();
		$formatted_number = self::format_number( $number );

		$order->update_meta_data( '_oli_pdf_invoice_number', $formatted_number );
		$order->update_meta_data( '_oli_pdf_invoice_date', current_time( 'mysql' ) );
		$order->save();

		do_action( 'oli_pdf_invoice_generated', $order, $formatted_number );

		return $formatted_number;
	}

	/**
	 * Check if order should have invoice
	 *
	 * @param \WC_Order $order Order object.
	 * @return bool
	 */
	public static function should_generate_invoice( $order ) {
		$statuses = get_option( 'oli_pdf_invoice_generation_statuses', array( 'processing', 'completed' ) );
		
		if ( ! is_array( $statuses ) ) {
			$statuses = array( 'processing', 'completed' );
		}

		$order_status = $order->get_status();

		return in_array( $order_status, $statuses, true );
	}

	/**
	 * Get invoice number for order
	 *
	 * @param \WC_Order $order Order object.
	 * @return string
	 */
	public static function get_invoice_number( $order ) {
		return $order->get_meta( '_oli_pdf_invoice_number' );
	}

	/**
	 * Get invoice date for order
	 *
	 * @param \WC_Order $order Order object.
	 * @return string
	 */
	public static function get_invoice_date( $order ) {
		$date = $order->get_meta( '_oli_pdf_invoice_date' );
		return $date ? $date : '';
	}
}
