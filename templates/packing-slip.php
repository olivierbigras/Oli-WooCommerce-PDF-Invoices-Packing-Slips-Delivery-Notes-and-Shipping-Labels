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

$company_name    = get_option( 'oli_pdf_company_name', '' );
$company_address = get_option( 'oli_pdf_company_address', '' );
$show_sku        = 'yes' === get_option( 'oli_pdf_show_sku', 'yes' );
$show_weight     = 'yes' === get_option( 'oli_pdf_show_weight', 'yes' );
$show_images     = 'yes' === get_option( 'oli_pdf_show_product_images', 'no' );
$logo_src        = \Oli_PDF\Generator::get_logo_src();

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<title><?php esc_html_e( 'Packing Slip', 'oli-pdf-invoices-packing-slips' ); ?></title>
</head>
<body class="oli-pdf-document">
	<div class="oli-pdf-header">
		<?php if ( $logo_src ) : ?>
			<img src="<?php echo esc_attr( $logo_src ); ?>" alt="<?php echo esc_attr( $company_name ); ?>" class="oli-pdf-logo">
		<?php endif; ?>

		<div class="oli-pdf-company-info">
			<?php if ( $company_name ) : ?>
				<strong><?php echo esc_html( $company_name ); ?></strong><br>
			<?php endif; ?>
			<?php if ( $company_address ) : ?>
				<?php echo wp_kses_post( nl2br( $company_address ) ); ?>
			<?php endif; ?>
		</div>
	</div>

	<div class="oli-pdf-title">
		<?php esc_html_e( 'PACKING SLIP', 'oli-pdf-invoices-packing-slips' ); ?>
	</div>

	<p>
		<strong><?php esc_html_e( 'Order Number:', 'oli-pdf-invoices-packing-slips' ); ?></strong> <?php echo esc_html( $order->get_order_number() ); ?><br>
		<strong><?php esc_html_e( 'Order Date:', 'oli-pdf-invoices-packing-slips' ); ?></strong> <?php echo esc_html( $order->get_date_created()->date_i18n( get_option( 'date_format' ) ) ); ?>
	</p>

	<div class="oli-pdf-addresses">
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

	<table class="oli-pdf-products">
		<thead>
			<tr>
				<?php if ( $show_images ) : ?>
					<th><?php esc_html_e( 'Image', 'oli-pdf-invoices-packing-slips' ); ?></th>
				<?php endif; ?>
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
					<?php if ( $show_images ) : ?>
						<td>
							<?php
							$image_src = \Oli_PDF\Generator::get_product_image_src( $product );
							if ( $image_src ) :
								?>
								<img src="<?php echo esc_attr( $image_src ); ?>" class="oli-pdf-product-image" alt="">
							<?php endif; ?>
						</td>
					<?php endif; ?>
					<td><?php echo esc_html( $item->get_name() ); ?></td>
					<?php if ( $show_sku ) : ?>
						<td><?php echo esc_html( $product ? (string) $product->get_sku() : '' ); ?></td>
					<?php endif; ?>
					<?php if ( $show_weight ) : ?>
						<td><?php echo esc_html( ( $product && $product->get_weight() ) ? $product->get_weight() . ' ' . get_option( 'woocommerce_weight_unit' ) : '' ); ?></td>
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
