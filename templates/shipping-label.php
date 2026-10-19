<?php
/**
 * Shipping Label Template
 *
 * This template can be overridden by copying it to yourtheme/oli-pdf-templates/shipping-label.php
 *
 * @package Oli_PDF
 * @var WC_Order $order
 */

defined( 'ABSPATH' ) || exit;

$company_name    = get_option( 'oli_pdf_company_name', '' );
$company_address = get_option( 'oli_pdf_company_address', '' );

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<title><?php esc_html_e( 'Shipping Label', 'oli-pdf-invoices-packing-slips' ); ?></title>
	<style>
		body {
			font-family: 'DejaVu Sans', sans-serif;
			font-size: 14px;
			line-height: 1.4;
			color: #000;
			margin: 0;
			padding: 20px;
		}
		.label {
			border: 2px solid #000;
			padding: 20px;
			width: 400px;
		}
		.section {
			margin-bottom: 15px;
			padding: 10px;
			border: 1px solid #333;
		}
		.section-title {
			font-weight: bold;
			font-size: 12px;
			margin-bottom: 5px;
			text-transform: uppercase;
		}
		.address {
			font-size: 16px;
			font-weight: bold;
		}
		.order-info {
			font-size: 12px;
		}
	</style>
</head>
<body>
	<div class="label">
		<div class="section">
			<div class="section-title"><?php esc_html_e( 'From:', 'oli-pdf-invoices-packing-slips' ); ?></div>
			<div>
				<?php if ( $company_name ) : ?>
					<strong><?php echo esc_html( $company_name ); ?></strong><br>
				<?php endif; ?>
				<?php if ( $company_address ) : ?>
					<?php echo wp_kses_post( nl2br( $company_address ) ); ?>
				<?php endif; ?>
			</div>
		</div>

		<div class="section">
			<div class="section-title"><?php esc_html_e( 'To:', 'oli-pdf-invoices-packing-slips' ); ?></div>
			<div class="address">
				<?php echo wp_kses_post( $order->get_formatted_shipping_address() ); ?>
			</div>
		</div>

		<div class="section order-info">
			<strong><?php esc_html_e( 'Order:', 'oli-pdf-invoices-packing-slips' ); ?></strong> #<?php echo esc_html( $order->get_order_number() ); ?><br>
			<strong><?php esc_html_e( 'Date:', 'oli-pdf-invoices-packing-slips' ); ?></strong> <?php echo esc_html( $order->get_date_created()->date_i18n( get_option( 'date_format' ) ) ); ?>
		</div>
	</div>
</body>
</html>
