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

( static function ( $order ) {
	$company_name = get_option( 'oli_pdf_company_name', '' );
	$show_sku     = 'yes' === get_option( 'oli_pdf_show_sku', 'yes' );
	?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<title><?php esc_html_e( 'Delivery Note', 'oli-pdf-invoices-packing-slips' ); ?></title>
</head>
<body class="oli-pdf-document">
	<div class="oli-pdf-header">
		<?php \Oli_PDF\Generator::render_logo_img( 'delivery-note', $company_name ); ?>
	</div>

	<div class="oli-pdf-title">
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

	<table class="oli-pdf-products">
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
						<td><?php echo esc_html( $product ? (string) $product->get_sku() : '' ); ?></td>
					<?php endif; ?>
					<td class="text-right"><?php echo esc_html( $item->get_quantity() ); ?></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<div class="oli-pdf-signature">
		<?php esc_html_e( 'Received by:', 'oli-pdf-invoices-packing-slips' ); ?>
	</div>
</body>
</html>
	<?php
} )( $order );
