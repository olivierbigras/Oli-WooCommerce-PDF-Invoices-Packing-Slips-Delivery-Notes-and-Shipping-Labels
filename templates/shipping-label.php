<?php
/**
 * Shipping label inner markup (tiled onto a sheet by the generator).
 *
 * Override: yourtheme/oli-pdf-templates/shipping-label.php
 *
 * @package Oli_PDF
 * @var WC_Order $order
 */

defined( 'ABSPATH' ) || exit;

( static function ( $order ) {
	$company_name    = get_option( 'oli_pdf_company_name', '' );
	$company_address = get_option( 'oli_pdf_company_address', '' );
	?>
<div class="oli-pdf-label">
	<div class="oli-pdf-label-block">
		<div class="oli-pdf-label-heading"><?php esc_html_e( 'From:', 'oli-pdf-invoices-packing-slips' ); ?></div>
		<?php \Oli_PDF\Generator::render_logo_img( 'shipping-label', $company_name ); ?>
		<?php if ( $company_name ) : ?>
			<strong><?php echo esc_html( $company_name ); ?></strong><br>
		<?php endif; ?>
		<?php if ( $company_address ) : ?>
			<?php echo wp_kses_post( nl2br( $company_address ) ); ?>
		<?php endif; ?>
	</div>
	<div class="oli-pdf-label-block">
		<div class="oli-pdf-label-heading"><?php esc_html_e( 'To:', 'oli-pdf-invoices-packing-slips' ); ?></div>
		<div class="oli-pdf-label-destination">
			<?php echo wp_kses_post( $order->get_formatted_shipping_address() ? $order->get_formatted_shipping_address() : $order->get_formatted_billing_address() ); ?>
		</div>
	</div>
	<div class="oli-pdf-label-meta">
		<strong><?php esc_html_e( 'Order:', 'oli-pdf-invoices-packing-slips' ); ?></strong> #<?php echo esc_html( $order->get_order_number() ); ?><br>
		<strong><?php esc_html_e( 'Date:', 'oli-pdf-invoices-packing-slips' ); ?></strong> <?php echo esc_html( $order->get_date_created() ? $order->get_date_created()->date_i18n( get_option( 'date_format' ) ) : '' ); ?>
	</div>
</div>
	<?php
} )( $order );
