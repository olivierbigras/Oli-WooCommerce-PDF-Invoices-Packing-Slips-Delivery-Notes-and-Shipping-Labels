<?php
/**
 * Invoice template.
 *
 * Override: yourtheme/oli-pdf-templates/invoice.php
 *
 * @package Oli_PDF
 * @var WC_Order $order
 */

defined( 'ABSPATH' ) || exit;

( static function ( $order ) {
	$invoice_number  = $order->get_meta( '_oli_pdf_invoice_number' );
	$invoice_date    = $order->get_meta( '_oli_pdf_invoice_date' );
	$company_name    = get_option( 'oli_pdf_company_name', '' );
	$company_address = get_option( 'oli_pdf_company_address', '' );
	$vat_number      = get_option( 'oli_pdf_vat_number', '' );
	$tax_number      = get_option( 'oli_pdf_tax_number', '' );
	$footer_text     = get_option( 'oli_pdf_footer_text', '' );
	$show_sku        = 'yes' === get_option( 'oli_pdf_show_sku', 'yes' );
	$show_tax        = 'yes' === get_option( 'oli_pdf_show_tax_breakdown', 'yes' );
	$show_shipping   = 'yes' === get_option( 'oli_pdf_show_shipping', 'yes' );
	$show_payment    = 'yes' === get_option( 'oli_pdf_show_payment_method', 'yes' );
	$show_note       = 'yes' === get_option( 'oli_pdf_show_customer_note', 'yes' );
	$show_images     = 'yes' === get_option( 'oli_pdf_show_product_images', 'no' );
	$show_meta       = 'yes' === get_option( 'oli_pdf_show_product_meta', 'yes' );
	$logo_src        = \Oli_PDF\Generator::get_logo_src();
	?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<title><?php esc_html_e( 'Invoice', 'oli-pdf-invoices-packing-slips' ); ?></title>
</head>
<body class="oli-pdf-document">
	<div class="oli-pdf-header">
		<?php if ( $logo_src ) : ?>
			<img src="<?php echo esc_attr( $logo_src ); ?>" alt="<?php echo esc_attr( $company_name ); ?>" class="oli-pdf-logo" width="200" height="80">
		<?php endif; ?>
		<div class="oli-pdf-company-info">
			<?php if ( $company_name ) : ?>
				<strong><?php echo esc_html( $company_name ); ?></strong><br>
			<?php endif; ?>
			<?php if ( $company_address ) : ?>
				<?php echo wp_kses_post( nl2br( $company_address ) ); ?><br>
			<?php endif; ?>
			<?php if ( $vat_number ) : ?>
				<?php esc_html_e( 'VAT:', 'oli-pdf-invoices-packing-slips' ); ?> <?php echo esc_html( $vat_number ); ?><br>
			<?php endif; ?>
			<?php if ( $tax_number ) : ?>
				<?php esc_html_e( 'Tax:', 'oli-pdf-invoices-packing-slips' ); ?> <?php echo esc_html( $tax_number ); ?><br>
			<?php endif; ?>
		</div>
	</div>

	<div class="oli-pdf-title"><?php esc_html_e( 'INVOICE', 'oli-pdf-invoices-packing-slips' ); ?></div>

	<div class="oli-pdf-details">
		<table>
			<tr>
				<td class="left">
					<strong><?php esc_html_e( 'Bill To:', 'oli-pdf-invoices-packing-slips' ); ?></strong><br>
					<?php echo wp_kses_post( $order->get_formatted_billing_address() ); ?>
					<?php if ( $order->get_billing_email() ) : ?>
						<br><?php echo esc_html( $order->get_billing_email() ); ?>
					<?php endif; ?>
					<?php if ( $order->get_billing_phone() ) : ?>
						<br><?php echo esc_html( $order->get_billing_phone() ); ?>
					<?php endif; ?>
				</td>
				<td class="right">
					<strong><?php esc_html_e( 'Invoice Number:', 'oli-pdf-invoices-packing-slips' ); ?></strong> <?php echo esc_html( $invoice_number ); ?><br>
					<strong><?php esc_html_e( 'Invoice Date:', 'oli-pdf-invoices-packing-slips' ); ?></strong> <?php echo esc_html( $invoice_date ? date_i18n( get_option( 'date_format' ), strtotime( $invoice_date ) ) : '' ); ?><br>
					<strong><?php esc_html_e( 'Order Number:', 'oli-pdf-invoices-packing-slips' ); ?></strong> <?php echo esc_html( $order->get_order_number() ); ?><br>
					<strong><?php esc_html_e( 'Order Date:', 'oli-pdf-invoices-packing-slips' ); ?></strong> <?php echo esc_html( $order->get_date_created() ? $order->get_date_created()->date_i18n( get_option( 'date_format' ) ) : '' ); ?>
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
				<th class="text-right"><?php esc_html_e( 'Quantity', 'oli-pdf-invoices-packing-slips' ); ?></th>
				<th class="text-right"><?php esc_html_e( 'Price', 'oli-pdf-invoices-packing-slips' ); ?></th>
				<th class="text-right"><?php esc_html_e( 'Total', 'oli-pdf-invoices-packing-slips' ); ?></th>
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
					<td>
						<?php echo esc_html( $item->get_name() ); ?>
						<?php if ( $show_meta && $item->get_meta_data() ) : ?>
							<br>
							<small>
								<?php
								foreach ( $item->get_meta_data() as $meta ) {
									if ( 0 === strpos( $meta->key, '_' ) ) {
										continue;
									}
									echo esc_html( $meta->display_key ) . ': ' . esc_html( wp_strip_all_tags( (string) $meta->display_value ) ) . '<br>';
								}
								?>
							</small>
						<?php endif; ?>
					</td>
					<?php if ( $show_sku ) : ?>
						<td><?php echo esc_html( $product ? (string) $product->get_sku() : '' ); ?></td>
					<?php endif; ?>
					<td class="text-right"><?php echo esc_html( $item->get_quantity() ); ?></td>
					<td class="text-right"><?php echo wp_kses_post( wc_price( $order->get_item_subtotal( $item, false, true ), array( 'currency' => $order->get_currency() ) ) ); ?></td>
					<td class="text-right"><?php echo wp_kses_post( wc_price( $order->get_line_subtotal( $item, false, true ), array( 'currency' => $order->get_currency() ) ) ); ?></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<table class="oli-pdf-totals">
		<tr>
			<td class="total-label"><?php esc_html_e( 'Subtotal:', 'oli-pdf-invoices-packing-slips' ); ?></td>
			<td><?php echo wp_kses_post( wc_price( $order->get_subtotal(), array( 'currency' => $order->get_currency() ) ) ); ?></td>
		</tr>
		<?php if ( $show_shipping && $order->get_shipping_total() > 0 ) : ?>
			<tr>
				<td class="total-label"><?php esc_html_e( 'Shipping:', 'oli-pdf-invoices-packing-slips' ); ?></td>
				<td><?php echo wp_kses_post( wc_price( $order->get_shipping_total(), array( 'currency' => $order->get_currency() ) ) ); ?></td>
			</tr>
		<?php endif; ?>
		<?php if ( $show_tax ) : ?>
			<?php foreach ( $order->get_tax_totals() as $tax ) : ?>
				<tr>
					<td class="total-label"><?php echo esc_html( $tax->label ); ?>:</td>
					<td><?php echo wp_kses_post( wc_price( $tax->amount, array( 'currency' => $order->get_currency() ) ) ); ?></td>
				</tr>
			<?php endforeach; ?>
		<?php elseif ( $order->get_total_tax() > 0 ) : ?>
			<tr>
				<td class="total-label"><?php esc_html_e( 'Tax:', 'oli-pdf-invoices-packing-slips' ); ?></td>
				<td><?php echo wp_kses_post( wc_price( $order->get_total_tax(), array( 'currency' => $order->get_currency() ) ) ); ?></td>
			</tr>
		<?php endif; ?>
		<tr class="grand-total">
			<td class="total-label"><?php esc_html_e( 'Total:', 'oli-pdf-invoices-packing-slips' ); ?></td>
			<td><?php echo wp_kses_post( wc_price( $order->get_total(), array( 'currency' => $order->get_currency() ) ) ); ?></td>
		</tr>
	</table>

	<?php if ( $show_payment && $order->get_payment_method_title() ) : ?>
		<p><strong><?php esc_html_e( 'Payment Method:', 'oli-pdf-invoices-packing-slips' ); ?></strong> <?php echo esc_html( $order->get_payment_method_title() ); ?></p>
	<?php endif; ?>

	<?php if ( $show_note && $order->get_customer_note() ) : ?>
		<p><strong><?php esc_html_e( 'Customer Note:', 'oli-pdf-invoices-packing-slips' ); ?></strong><br><?php echo esc_html( $order->get_customer_note() ); ?></p>
	<?php endif; ?>

	<?php if ( $footer_text ) : ?>
		<div class="oli-pdf-footer"><?php echo wp_kses_post( nl2br( $footer_text ) ); ?></div>
	<?php endif; ?>
</body>
</html>
	<?php
} )( $order );
