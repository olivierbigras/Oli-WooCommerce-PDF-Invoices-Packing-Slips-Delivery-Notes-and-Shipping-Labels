<?php
/**
 * Delivery Note Template
 *
 * This template can be overridden by copying it to yourtheme/oli-pdf-templates/delivery-note.php
 *
 * @package Oli_PDF
 * @var WC_Order $order
 */

defined( 'ABSPATH' ) || exit;

$company_name    = get_option( 'oli_pdf_company_name', '' );
$company_address = get_option( 'oli_pdf_company_address', '' );
$show_sku        = 'yes' === get_option( 'oli_pdf_show_sku', 'yes' );
$primary         = \Oli_PDF\Generator::get_primary_color();
$secondary       = \Oli_PDF\Generator::get_secondary_color();
$logo_src        = \Oli_PDF\Generator::get_logo_src();

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<title><?php esc_html_e( 'Delivery Note', 'oli-pdf-invoices-packing-slips' ); ?></title>
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
			max-width: 150px;
			max-height: 80px;
		}
		.title {
			font-size: 20px;
			font-weight: bold;
			margin: 20px 0;
			color: <?php echo esc_attr( $primary ); ?>;
		}
		.products-table {
			width: 100%;
			border-collapse: collapse;
			margin: 20px 0;
		}
		.products-table th {
			background: <?php echo esc_attr( $secondary ); ?>;
			color: <?php echo esc_attr( $primary ); ?>;
			padding: 10px;
			text-align: left;
			border-bottom: 2px solid <?php echo esc_attr( $primary ); ?>;
		}
		.products-table td {
			padding: 10px;
			border-bottom: 1px solid #eee;
		}
		.signature {
			margin-top: 50px;
			border-top: 1px solid #333;
			width: 200px;
			padding-top: 10px;
		}
		.text-right {
			text-align: right;
		}
	</style>
</head>
<body>
	<div class="header">
		<?php if ( $logo_src ) : ?>
			<img src="<?php echo esc_attr( $logo_src ); ?>" alt="<?php echo esc_attr( $company_name ); ?>" class="logo">
		<?php endif; ?>
	</div>

	<div class="title">
		<?php esc_html_e( 'DELIVERY NOTE', 'oli-pdf-invoices-packing-slips' ); ?>
	</div>

	<p>
		<strong><?php esc_html_e( 'Order Number:', 'oli-pdf-invoices-packing-slips' ); ?></strong> <?php echo esc_html( $order->get_order_number() ); ?><br>
		<strong><?php esc_html_e( 'Date:', 'oli-pdf-invoices-packing-slips' ); ?></strong> <?php echo esc_html( current_time( get_option( 'date_format' ) ) ); ?>
	</p>

	<p>
		<strong><?php esc_html_e( 'Deliver To:', 'oli-pdf-invoices-packing-slips' ); ?></strong><br>
		<?php echo wp_kses_post( $order->get_formatted_shipping_address() ); ?>
	</p>

	<table class="products-table">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Product', 'oli-pdf-invoices-packing-slips' ); ?></th>
				<?php if ( $show_sku ) : ?>
					<th><?php esc_html_e( 'SKU', 'oli-pdf-invoices-packing-slips' ); ?></th>
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
					<td class="text-right"><?php echo esc_html( $item->get_quantity() ); ?></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<div class="signature">
		<?php esc_html_e( 'Received by:', 'oli-pdf-invoices-packing-slips' ); ?>
	</div>
</body>
</html>
