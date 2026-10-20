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

$company_name    = get_option( 'oli_pdf_company_name', '' );
$company_address = get_option( 'oli_pdf_company_address', '' );
$primary         = \Oli_PDF\Generator::get_primary_color();
?>
<div class="oli-pdf-label" style="font-family:'DejaVu Sans',sans-serif;font-size:12px;color:#000;border:2px solid <?php echo esc_attr( $primary ); ?>;padding:10px;box-sizing:border-box;height:100%;">
	<div style="margin-bottom:10px;padding:8px;border:1px solid #333;">
		<div style="font-weight:bold;text-transform:uppercase;font-size:11px;color:<?php echo esc_attr( $primary ); ?>;"><?php esc_html_e( 'From:', 'oli-pdf-invoices-packing-slips' ); ?></div>
		<?php if ( $company_name ) : ?>
			<strong><?php echo esc_html( $company_name ); ?></strong><br>
		<?php endif; ?>
		<?php if ( $company_address ) : ?>
			<?php echo wp_kses_post( nl2br( $company_address ) ); ?>
		<?php endif; ?>
	</div>
	<div style="margin-bottom:10px;padding:8px;border:1px solid #333;">
		<div style="font-weight:bold;text-transform:uppercase;font-size:11px;color:<?php echo esc_attr( $primary ); ?>;"><?php esc_html_e( 'To:', 'oli-pdf-invoices-packing-slips' ); ?></div>
		<div style="font-size:14px;font-weight:bold;">
			<?php echo wp_kses_post( $order->get_formatted_shipping_address() ? $order->get_formatted_shipping_address() : $order->get_formatted_billing_address() ); ?>
		</div>
	</div>
	<div style="font-size:11px;">
		<strong><?php esc_html_e( 'Order:', 'oli-pdf-invoices-packing-slips' ); ?></strong> #<?php echo esc_html( $order->get_order_number() ); ?><br>
		<strong><?php esc_html_e( 'Date:', 'oli-pdf-invoices-packing-slips' ); ?></strong> <?php echo esc_html( $order->get_date_created() ? $order->get_date_created()->date_i18n( get_option( 'date_format' ) ) : '' ); ?>
	</div>
</div>
