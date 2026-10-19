<?php
/**
 * Packing Slip Template
 *
 * This template can be overridden by copying it to yourtheme/oli-pdf-templates/packing-slip.php
 *
 * @package Oli_PDF
 * @var WC_Order $order
 */

defined( 'ABSPATH' ) || exit;

$company_name   = get_option( 'oli_pdf_company_name', '' );
$company_address = get_option( 'oli_pdf_company_address', '' );
$logo_url       = get_option( 'oli_pdf_logo_url', '' );
$show_sku       = 'yes' === get_option( 'oli_pdf_show_sku', 'yes' );
$show_weight    = 'yes' === get_option( 'oli_pdf_show_weight', 'yes' );
$show_images    = 'yes' === get_option( 'oli_pdf_show_product_images', 'no' );

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<title><?php esc_html_e( 'Packing Slip', 'oli-pdf-invoices-packing-slips' ); ?></title>
	<style>
		body {
			font-family: 'DejaVu Sans', sans-serif;
			font-size: 12px;
			line-height: 1.5;
			color: #333;
		}
		.header {
			margin-bottom: 20px;
			overflow: hidden;
		}
		.logo {
			float: left;
			max-width: 150px;
			max-height: 80px;
		}
		.company-info {
			float: right;
			text-align: right;
		}
		.title {
			font-size: 20px;
			font-weight: bold;
			margin: 20px 0;
			clear: both;
		}
		.addresses {
			margin: 20px 0;
		}
		.addresses table {
			width: 100%;
		}
		.addresses td {
			width: 50%;
			padding: 10px;
			vertical-align: top;
		}
		.products-table {
			width: 100%;
			border-collapse: collapse;
			margin: 20px 0;
		}
		.products-table th {
			background: #f5f5f5;
			padding: 10px;
			text-align: left;
			border-bottom: 2px solid #ddd;
		}
		.products-table td {
			padding: 10px;
			border-bottom: 1px solid #eee;
		}
		.text-right {
			text-align: right;
		}
	</style>
</head>
<body>
	<div class="header">
		<?php if ( $logo_url ) : ?>
			<img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php echo esc_attr( $company_name ); ?>" class="logo">
		<?php endif; ?>
		
		<div class="company-info">
			<?php if ( $company_name ) : ?>
				<strong><?php echo esc_html( $company_name ); ?></strong><br>
			<?php endif; ?>
			<?php if ( $company_address ) : ?>
				<?php echo wp_kses_post( nl2br( $company_address ) ); ?>
			<?php endif; ?>
		</div>
	</div>

	<div class="title">
		<?php esc_html_e( 'PACKING SLIP', 'oli-pdf-invoices-packing-slips' ); ?>
	</div>

	<p>
		<strong><?php esc_html_e( 'Order Number:', 'oli-pdf-invoices-packing-slips' ); ?></strong> <?php echo esc_html( $order->get_order_number() ); ?><br>
		<strong><?php esc_html_e( 'Order Date:', 'oli-pdf-invoices-packing-slips' ); ?></strong> <?php echo esc_html( $order->get_date_created()->date_i18n( get_option( 'date_format' ) ) ); ?>
	</p>

	<div class="addresses">
		<table>
			<tr>
				<td>
					<strong><?php esc_html_e( 'Billing Address:', 'oli-pdf-invoices-packing-slips' ); ?></strong><br>
					<?php echo wp_kses_post( $order->get_formatted_billing_address() ); ?>
				</td>
				<td>
					<strong><?php esc_html_e( 'Shipping Address:', 'oli-pdf-invoices-packing-slips' ); ?></strong><br>
					<?php echo wp_kses_post( $order->get_formatted_shipping_address() ); ?>
				</td>
			</tr>
		</table>
	</div>

	<table class="products-table">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Product', 'oli-pdf-invoices-packing-slips' ); ?></th>
				<?php if ( $show_sku ) : ?>
					<th><?php esc_html_e( 'SKU', 'oli-pdf-invoices-packing-slips' ); ?></th>
				<?php endif; ?>
				<?php if ( $show_weight ) : ?>
					<th><?php esc_html_e( 'Weight', 'oli-pdf-invoices-packing-slips' ); ?></th>
				<?php endif; ?>
				<th class="text-right"><?php esc_html_e( 'Quantity', 'oli-pdf-invoices-packing-slips' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $order->get_items() as $item ) : ?>
				<?php $product = $item->get_product(); ?>
				<tr>
					<td><?php echo esc_html( $item->get_name() ); ?></td>
					<?php if ( $show_sku ) : ?>
						<td><?php echo $product ? esc_html( $product->get_sku() ) : ''; ?></td>
					<?php endif; ?>
					<?php if ( $show_weight ) : ?>
						<td><?php echo $product && $product->get_weight() ? esc_html( $product->get_weight() . ' ' . get_option( 'woocommerce_weight_unit' ) ) : ''; ?></td>
					<?php endif; ?>
					<td class="text-right"><?php echo esc_html( $item->get_quantity() ); ?></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<?php if ( $order->get_customer_note() ) : ?>
		<p>
			<strong><?php esc_html_e( 'Customer Note:', 'oli-pdf-invoices-packing-slips' ); ?></strong><br>
			<?php echo esc_html( $order->get_customer_note() ); ?>
		</p>
	<?php endif; ?>
</body>
</html>
