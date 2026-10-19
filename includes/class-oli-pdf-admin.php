<?php
/**
 * Admin integration
 *
 * @package Oli_PDF
 */

namespace Oli_PDF;

defined( 'ABSPATH' ) || exit;

/**
 * Admin class
 */
class Admin {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'woocommerce_order_status_changed', array( $this, 'maybe_generate_invoice' ), 10, 3 );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		add_action( 'admin_init', array( $this, 'handle_pdf_actions' ) );
		add_filter( 'woocommerce_admin_order_actions', array( $this, 'add_order_actions' ), 10, 2 );
	}

	/**
	 * Maybe generate invoice on status change
	 *
	 * @param int    $order_id Order ID.
	 * @param string $old_status Old status.
	 * @param string $new_status New status.
	 */
	public function maybe_generate_invoice( $order_id, $old_status, $new_status ) {
		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return;
		}

		if ( Numbering::should_generate_invoice( $order ) ) {
			Numbering::generate_for_order( $order );
		}
	}

	/**
	 * Add meta box
	 */
	public function add_meta_box() {
		$screen = wc_get_container()->get( \Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController::class )->custom_orders_table_usage_is_enabled()
			? wc_get_page_screen_id( 'shop-order' )
			: 'shop_order';

		add_meta_box(
			'oli-pdf-documents',
			__( 'PDF Documents', 'oli-pdf-invoices-packing-slips' ),
			array( $this, 'render_meta_box' ),
			$screen,
			'side',
			'default'
		);
	}

	/**
	 * Render meta box
	 *
	 * @param \WP_Post|\WC_Order $post_or_order Post or Order object.
	 */
	public function render_meta_box( $post_or_order ) {
		$order = $post_or_order instanceof \WC_Order ? $post_or_order : wc_get_order( $post_or_order->ID );
		if ( ! $order ) {
			return;
		}

		$invoice_number = Numbering::get_invoice_number( $order );
		$invoice_date   = Numbering::get_invoice_date( $order );

		?>
		<div class="oli-pdf-actions">
			<?php if ( $invoice_number ) : ?>
				<p>
					<strong><?php esc_html_e( 'Invoice Number:', 'oli-pdf-invoices-packing-slips' ); ?></strong>
					<?php echo esc_html( $invoice_number ); ?><br>
					<strong><?php esc_html_e( 'Invoice Date:', 'oli-pdf-invoices-packing-slips' ); ?></strong>
					<?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $invoice_date ) ) ); ?>
				</p>
			<?php endif; ?>

			<?php foreach ( Generator::get_document_types() as $type ) : ?>
				<?php if ( 'invoice' === $type && ! $invoice_number ) : ?>
					<p>
						<em><?php esc_html_e( 'Invoice not generated yet', 'oli-pdf-invoices-packing-slips' ); ?></em>
					</p>
				<?php else : ?>
					<p>
						<a href="<?php echo esc_url( $this->get_pdf_url( $order->get_id(), $type, 'view' ) ); ?>" class="button" target="_blank">
							<?php
							/* translators: %s: document type */
							printf( esc_html__( 'View %s', 'oli-pdf-invoices-packing-slips' ), esc_html( ucfirst( str_replace( '-', ' ', $type ) ) ) );
							?>
						</a>
						<a href="<?php echo esc_url( $this->get_pdf_url( $order->get_id(), $type, 'download' ) ); ?>" class="button">
							<?php
							/* translators: %s: document type */
							printf( esc_html__( 'Download %s', 'oli-pdf-invoices-packing-slips' ), esc_html( ucfirst( str_replace( '-', ' ', $type ) ) ) );
							?>
						</a>
					</p>
				<?php endif; ?>
			<?php endforeach; ?>
		</div>
		<?php
	}

	/**
	 * Get PDF URL
	 *
	 * @param int    $order_id Order ID.
	 * @param string $type Document type.
	 * @param string $action Action.
	 * @return string
	 */
	private function get_pdf_url( $order_id, $type, $action = 'download' ) {
		return wp_nonce_url(
			add_query_arg(
				array(
					'oli_pdf_action' => 'generate',
					'order_id'       => $order_id,
					'type'           => $type,
					'action'         => $action,
				),
				admin_url( 'admin.php' )
			),
			'oli_pdf_generate'
		);
	}

	/**
	 * Handle PDF actions
	 */
	public function handle_pdf_actions() {
		if ( ! isset( $_GET['oli_pdf_action'] ) ) {
			return;
		}

		check_admin_referer( 'oli_pdf_generate' );

		$action   = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : 'download';
		$order_id = isset( $_GET['order_id'] ) ? absint( $_GET['order_id'] ) : 0;
		$type     = isset( $_GET['type'] ) ? sanitize_text_field( wp_unslash( $_GET['type'] ) ) : 'invoice';

		if ( ! $order_id || ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'Invalid request.', 'oli-pdf-invoices-packing-slips' ) );
		}

		Generator::generate( $order_id, $type, $action );
	}

	/**
	 * Add order actions
	 *
	 * @param array     $actions Actions.
	 * @param \WC_Order $order Order object.
	 * @return array
	 */
	public function add_order_actions( $actions, $order ) {
		$invoice_number = Numbering::get_invoice_number( $order );
		
		if ( $invoice_number ) {
			$actions['oli_pdf_invoice'] = array(
				'url'    => $this->get_pdf_url( $order->get_id(), 'invoice', 'download' ),
				'name'   => __( 'PDF Invoice', 'oli-pdf-invoices-packing-slips' ),
				'action' => 'oli_pdf_invoice',
			);
		}

		return $actions;
	}
}
