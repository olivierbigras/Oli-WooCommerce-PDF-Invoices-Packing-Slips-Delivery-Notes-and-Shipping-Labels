<?php
/**
 * Credit note template for refunds.
 *
 * Override: yourtheme/oli-pdf-templates/credit-note.php
 *
 * @package Oli_PDF
 * @var WC_Order $order
 */

defined( 'ABSPATH' ) || exit;

$credit_number   = $order->get_meta( '_oli_pdf_credit_note_number' );
$credit_date     = $order->get_meta( '_oli_pdf_credit_note_date' );
$invoice_number  = $order->get_meta( '_oli_pdf_invoice_number' );
$company_name    = get_option( 'oli_pdf_company_name', '' );
$company_address = get_option( 'oli_pdf_company_address', '' );
$vat_number      = get_option( 'oli_pdf_vat_number', '' );
$footer_text     = get_option( 'oli_pdf_footer_text', '' );
$show_sku        = 'yes' === get_option( 'oli_pdf_show_sku', 'yes' );
$show_images     = 'yes' === get_option( 'oli_pdf_show_product_images', 'no' );
$logo_src        = \Oli_PDF\Generator::get_logo_src();
$refunds         = $order->get_refunds();
$refund_total    = $order->get_total_refunded();
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<title><?php esc_html_e( 'Credit Note', 'oli-pdf-invoices-packing-slips' ); ?></title>
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
				<?php echo wp_kses_post( nl2br( $company_address ) ); ?><br>
			<?php endif; ?>
			<?php if ( $vat_number ) : ?>
				<?php esc_html_e( 'VAT:', 'oli-pdf-invoices-packing-slips' ); ?> <?php echo esc_html( $vat_number ); ?>
			<?php endif; ?>
		</div>
	</div>

	<div class="oli-pdf-title"><?php esc_html_e( 'CREDIT NOTE', 'oli-pdf-invoices-packing-slips' ); ?></div>

	<div class="oli-pdf-details">
		<table>
			<tr>
				<td>
					<strong><?php esc_html_e( 'Bill To:', 'oli-pdf-invoices-packing-slips' ); ?></strong><br>
					<?php echo wp_kses_post( $order->get_formatted_billing_address() ); ?>
				</td>
				<td class="right">
					<strong><?php esc_html_e( 'Credit Note Number:', 'oli-pdf-invoices-packing-slips' ); ?></strong> <?php echo esc_html( $credit_number ); ?><br>
					<strong><?php esc_html_e( 'Credit Note Date:', 'oli-pdf-invoices-packing-slips' ); ?></strong> <?php echo esc_html( $credit_date ? date_i18n( get_option( 'date_format' ), strtotime( $credit_date ) ) : '' ); ?><br>
					<?php if ( $invoice_number ) : ?>
						<strong><?php esc_html_e( 'Original Invoice:', 'oli-pdf-invoices-packing-slips' ); ?></strong> <?php echo esc_html( $invoice_number ); ?><br>
					<?php endif; ?>
					<strong><?php esc_html_e( 'Order Number:', 'oli-pdf-invoices-packing-slips' ); ?></strong> <?php echo esc_html( $order->get_order_number() ); ?>
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
				<th class="text-right"><?php esc_html_e( 'Qty refunded', 'oli-pdf-invoices-packing-slips' ); ?></th>
				<th class="text-right"><?php esc_html_e( 'Amount', 'oli-pdf-invoices-packing-slips' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php
			$has_rows = false;
			foreach ( $refunds as $refund ) :
				foreach ( $refund->get_items() as $item ) :
					$has_rows = true;
					$product  = $item->get_product();
					?>
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
						<td class="text-right"><?php echo esc_html( absint( $item->get_quantity() ) ); ?></td>
						<td class="text-right"><?php echo wp_kses_post( wc_price( abs( (float) $item->get_total() ), array( 'currency' => $order->get_currency() ) ) ); ?></td>
					</tr>
					<?php
				endforeach;
			endforeach;
			if ( ! $has_rows ) :
				?>
				<tr>
					<td colspan="5"><?php esc_html_e( 'Refund recorded against this order.', 'oli-pdf-invoices-packing-slips' ); ?></td>
				</tr>
			<?php endif; ?>
		</tbody>
	</table>

	<table class="oli-pdf-totals">
		<tr class="grand-total">
			<td><?php esc_html_e( 'Total credited:', 'oli-pdf-invoices-packing-slips' ); ?></td>
			<td><?php echo wp_kses_post( wc_price( $refund_total, array( 'currency' => $order->get_currency() ) ) ); ?></td>
		</tr>
	</table>

	<?php if ( $footer_text ) : ?>
		<div class="oli-pdf-footer"><?php echo wp_kses_post( nl2br( $footer_text ) ); ?></div>
	<?php endif; ?>
</body>
</html>
