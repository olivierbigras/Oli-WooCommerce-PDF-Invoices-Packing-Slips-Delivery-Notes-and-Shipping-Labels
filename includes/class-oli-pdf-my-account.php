<?php
/**
 * My Account integration.
 *
 * @package Oli_PDF
 */

namespace Oli_PDF;

defined( 'ABSPATH' ) || exit;

/**
 * My_Account class.
 */
class My_Account {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_filter( 'woocommerce_my_account_my_orders_actions', array( $this, 'add_order_actions' ), 10, 2 );
		add_action( 'init', array( $this, 'handle_download' ) );
	}

	/**
	 * Add order actions.
	 *
	 * @param array     $actions Actions.
	 * @param \WC_Order $order   Order object.
	 * @return array
	 */
	public function add_order_actions( $actions, $order ) {
		if ( Numbering::get_invoice_number( $order ) ) {
			$actions['oli_pdf_invoice'] = array(
				'url'  => $this->get_download_url( $order->get_id(), 'invoice' ),
				'name' => __( 'Download Invoice', 'oli-pdf-invoices-packing-slips' ),
			);
		}

		if ( Numbering::get_credit_note_number( $order ) ) {
			$actions['oli_pdf_credit_note'] = array(
				'url'  => $this->get_download_url( $order->get_id(), 'credit-note' ),
				'name' => __( 'Download Credit Note', 'oli-pdf-invoices-packing-slips' ),
			);
		}

		return $actions;
	}

	/**
	 * Get download URL.
	 *
	 * @param int    $order_id Order ID.
	 * @param string $type     Document type.
	 * @return string
	 */
	private function get_download_url( $order_id, $type ) {
		$order = wc_get_order( $order_id );
		$token = wp_hash( 'oli_pdf_' . $order_id . $order->get_order_key() );

		return add_query_arg(
			array(
				'oli_pdf_download' => $type,
				'order_id'         => $order_id,
				'token'            => $token,
			),
			home_url()
		);
	}

	/**
	 * Handle download.
	 */
	public function handle_download() {
		if ( ! isset( $_GET['oli_pdf_download'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- token is verified in Generator.
			return;
		}

		$order_id = isset( $_GET['order_id'] ) ? absint( wp_unslash( $_GET['order_id'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$type     = isset( $_GET['oli_pdf_download'] ) ? sanitize_text_field( wp_unslash( $_GET['oli_pdf_download'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( ! $order_id || ! $type ) {
			return;
		}

		Generator::generate( $order_id, $type, 'download' );
	}
}
