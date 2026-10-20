<?php
/**
 * Invoice and credit-note numbering.
 *
 * @package Oli_PDF
 */

namespace Oli_PDF;

defined( 'ABSPATH' ) || exit;

/**
 * Numbering class.
 */
class Numbering {

	/**
	 * Get next sequential number.
	 *
	 * @param string $option_key Option storing the current number.
	 * @param string $start_key  Option storing the start number.
	 * @return int
	 */
	public static function get_next_number( $option_key = 'oli_pdf_invoice_current_number', $start_key = 'oli_pdf_invoice_number_start' ) {
		$current = (int) get_option( $option_key, 0 );
		$start   = (int) get_option( $start_key, 1 );
		$next    = ( 0 === $current ) ? $start : ( $current + 1 );

		update_option( $option_key, $next );

		return $next;
	}

	/**
	 * Format a sequential number.
	 *
	 * @param int    $number      Number.
	 * @param string $prefix_key  Prefix option.
	 * @param string $suffix_key  Suffix option.
	 * @param string $padding_key Padding option.
	 * @return string
	 */
	public static function format_number( $number, $prefix_key = 'oli_pdf_invoice_number_prefix', $suffix_key = 'oli_pdf_invoice_number_suffix', $padding_key = 'oli_pdf_invoice_number_padding' ) {
		$prefix  = (string) get_option( $prefix_key, '' );
		$suffix  = (string) get_option( $suffix_key, '' );
		$padding = (int) get_option( $padding_key, 4 );

		return $prefix . str_pad( (string) $number, $padding, '0', STR_PAD_LEFT ) . $suffix;
	}

	/**
	 * Generate invoice number for order.
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

		/**
		 * Fires after an invoice number is generated.
		 *
		 * @param \WC_Order $order            Order.
		 * @param string    $formatted_number Invoice number.
		 */
		do_action( 'oli_pdf_invoice_generated', $order, $formatted_number );

		return $formatted_number;
	}

	/**
	 * Generate a credit note number for a refunded order.
	 *
	 * @param \WC_Order $order Order object.
	 * @return string|false
	 */
	public static function generate_credit_note_for_order( $order ) {
		if ( ! $order ) {
			return false;
		}

		if ( 'yes' !== get_option( 'oli_pdf_enable_credit_notes', 'yes' ) ) {
			return false;
		}

		$existing = $order->get_meta( '_oli_pdf_credit_note_number' );
		if ( $existing ) {
			return $existing;
		}

		$number           = self::get_next_number( 'oli_pdf_credit_note_current_number', 'oli_pdf_credit_note_number_start' );
		$formatted_number = self::format_number(
			$number,
			'oli_pdf_credit_note_number_prefix',
			'oli_pdf_credit_note_number_suffix',
			'oli_pdf_credit_note_number_padding'
		);

		$order->update_meta_data( '_oli_pdf_credit_note_number', $formatted_number );
		$order->update_meta_data( '_oli_pdf_credit_note_date', current_time( 'mysql' ) );
		$order->save();

		/**
		 * Fires after a credit note number is generated.
		 *
		 * @param \WC_Order $order            Order.
		 * @param string    $formatted_number Credit note number.
		 */
		do_action( 'oli_pdf_credit_note_generated', $order, $formatted_number );

		return $formatted_number;
	}

	/**
	 * Check if order should have an invoice.
	 *
	 * @param \WC_Order $order Order object.
	 * @return bool
	 */
	public static function should_generate_invoice( $order ) {
		$statuses = get_option( 'oli_pdf_invoice_generation_statuses', array( 'processing', 'completed' ) );

		if ( ! is_array( $statuses ) ) {
			$statuses = array( 'processing', 'completed' );
		}

		return in_array( $order->get_status(), $statuses, true );
	}

	/**
	 * Get invoice number for order.
	 *
	 * @param \WC_Order $order Order object.
	 * @return string
	 */
	public static function get_invoice_number( $order ) {
		return (string) $order->get_meta( '_oli_pdf_invoice_number' );
	}

	/**
	 * Get invoice date for order.
	 *
	 * @param \WC_Order $order Order object.
	 * @return string
	 */
	public static function get_invoice_date( $order ) {
		$date = $order->get_meta( '_oli_pdf_invoice_date' );
		return $date ? (string) $date : '';
	}

	/**
	 * Get credit note number.
	 *
	 * @param \WC_Order $order Order object.
	 * @return string
	 */
	public static function get_credit_note_number( $order ) {
		return (string) $order->get_meta( '_oli_pdf_credit_note_number' );
	}

	/**
	 * Get credit note date.
	 *
	 * @param \WC_Order $order Order object.
	 * @return string
	 */
	public static function get_credit_note_date( $order ) {
		$date = $order->get_meta( '_oli_pdf_credit_note_date' );
		return $date ? (string) $date : '';
	}
}
