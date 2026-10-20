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
$primary         = \Oli_PDF\Generator::get_primary_color();
$secondary       = \Oli_PDF\Generator::get_secondary_color();
$logo_src        = \Oli_PDF\Generator::get_logo_src();
$image_size      = \Oli_PDF\Generator::get_product_image_size();
$refunds         = $order->get_refunds();
$refund_total    = $order->get_total_refunded();
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<title><?php esc_html_e( 'Credit Note', 'oli-pdf-invoices-packing-slips' ); ?></title>
	<style>
		body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; line-height: 1.5; color: #333; }
		.header { margin-bottom: 30px; overflow: hidden; }
		.logo { float: left; max-width: 200px; max-height: 100px; }
		.company-info { float: right; text-align: right; }
		.title { font-size: 24px; font-weight: bold; margin: 20px 0; clear: both; color: <?php echo esc_attr( $primary ); ?>; }
		.details table { width: 100%; }
		.details td { padding: 5px; vertical-align: top; }
		.products-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
		.products-table th { background: <?php echo esc_attr( $secondary ); ?>; color: <?php echo esc_attr( $primary ); ?>; padding: 10px; text-align: left; border-bottom: 2px solid <?php echo esc_attr( $primary ); ?>; }
		.products-table td { padding: 10px; border-bottom: 1px solid #eee; }
		.product-image { width: <?php echo (int) $image_size; ?>px; height: <?php echo (int) $image_size; ?>px; }
		.totals-table { width: 100%; }
		.totals-table td { padding: 5px; text-align: right; }
		.grand-total { font-size: 16px; font-weight: bold; border-top: 2px solid <?php echo esc_attr( $primary ); ?>; color: <?php echo esc_attr( $primary ); ?>; }
		.footer { margin-top: 40px; padding-top: 20px; border-top: 1px solid #ddd; text-align: center; font-size: 10px; }
		.text-right { text-align: right; }
	</style>
</head>
<body>
	<div class="header">
		<?php if ( $logo_src ) : ?>
			<img src="<?php echo esc_attr( $logo_src ); ?>" alt="<?php echo esc_attr( $company_name ); ?>" class="logo">
		<?php endif; ?>
		<div class="company-info">
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

	<div class="title"><?php esc_html_e( 'CREDIT NOTE', 'oli-pdf-invoices-packing-slips' ); ?></div>

	<div class="details">
		<table>
			<tr>
				<td>
					<strong><?php esc_html_e( 'Bill To:', 'oli-pdf-invoices-packing-slips' ); ?></strong><br>
					<?php echo wp_kses_post( $order->get_formatted_billing_address() ); ?>
				</td>
				<td style="text-align:right;">
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

	<table class="products-table">
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
									<img src="<?php echo esc_attr( $image_src ); ?>" class="product-image" alt="">
								<?php endif; ?>
							</td>
						<?php endif; ?>
						<td><?php echo esc_html( $item->get_name() ); ?></td>
						<?php if ( $show_sku ) : ?>
							<td><?php echo $product ? esc_html( $product->get_sku() ) : ''; ?></td>
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

	<table class="totals-table">
		<tr class="grand-total">
			<td><?php esc_html_e( 'Total credited:', 'oli-pdf-invoices-packing-slips' ); ?></td>
			<td><?php echo wp_kses_post( wc_price( $refund_total, array( 'currency' => $order->get_currency() ) ) ); ?></td>
		</tr>
	</table>

	<?php if ( $footer_text ) : ?>
		<div class="footer"><?php echo wp_kses_post( nl2br( $footer_text ) ); ?></div>
	<?php endif; ?>
</body>
</html>
