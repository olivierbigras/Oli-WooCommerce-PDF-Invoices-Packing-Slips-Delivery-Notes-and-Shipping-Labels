<?php
/**
 * Invoice Template
 *
 * This template can be overridden by copying it to yourtheme/oli-pdf-templates/invoice.php
 *
 * @package Oli_PDF
 * @var WC_Order $order
 */

defined( 'ABSPATH' ) || exit;

$invoice_number = $order->get_meta( '_oli_pdf_invoice_number' );
$invoice_date   = $order->get_meta( '_oli_pdf_invoice_date' );
$company_name   = get_option( 'oli_pdf_company_name', '' );
$company_address = get_option( 'oli_pdf_company_address', '' );
$vat_number     = get_option( 'oli_pdf_vat_number', '' );
$tax_number     = get_option( 'oli_pdf_tax_number', '' );
$logo_url       = get_option( 'oli_pdf_logo_url', '' );
$footer_text    = get_option( 'oli_pdf_footer_text', '' );
$show_sku       = 'yes' === get_option( 'oli_pdf_show_sku', 'yes' );
$show_weight    = 'yes' === get_option( 'oli_pdf_show_weight', 'yes' );
$show_tax       = 'yes' === get_option( 'oli_pdf_show_tax_breakdown', 'yes' );
$show_shipping  = 'yes' === get_option( 'oli_pdf_show_shipping', 'yes' );
$show_payment   = 'yes' === get_option( 'oli_pdf_show_payment_method', 'yes' );
$show_note      = 'yes' === get_option( 'oli_pdf_show_customer_note', 'yes' );
$show_images    = 'yes' === get_option( 'oli_pdf_show_product_images', 'no' );
$show_meta      = 'yes' === get_option( 'oli_pdf_show_product_meta', 'yes' );

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php esc_html_e( 'Invoice', 'oli-pdf-invoices-packing-slips' ); ?></title>
	<style>
		body {
			font-family: 'DejaVu Sans', sans-serif;
			font-size: 12px;
			line-height: 1.5;
			color: #333;
		}
		.header {
			margin-bottom: 30px;
			overflow: hidden;
		}
		.logo {
			float: left;
			max-width: 200px;
			max-height: 100px;
		}
		.company-info {
			float: right;
			text-align: right;
		}
		.invoice-title {
			font-size: 24px;
			font-weight: bold;
			margin: 20px 0;
			clear: both;
		}
		.invoice-details {
			margin-bottom: 20px;
		}
		.invoice-details table {
			width: 100%;
		}
		.invoice-details td {
			padding: 5px;
			vertical-align: top;
		}
		.invoice-details .left {
			width: 50%;
		}
		.invoice-details .right {
			width: 50%;
			text-align: right;
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
		.totals-table {
			width: 100%;
			margin-top: 20px;
		}
		.totals-table td {
			padding: 5px;
			text-align: right;
		}
		.totals-table .total-label {
			font-weight: bold;
		}
		.totals-table .grand-total {
			font-size: 16px;
			font-weight: bold;
			border-top: 2px solid #333;
			padding-top: 10px;
		}
		.footer {
			margin-top: 40px;
			padding-top: 20px;
			border-top: 1px solid #ddd;
			text-align: center;
			font-size: 10px;
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

	<div class="invoice-title">
		<?php esc_html_e( 'INVOICE', 'oli-pdf-invoices-packing-slips' ); ?>
	</div>

	<div class="invoice-details">
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
					<strong><?php esc_html_e( 'Invoice Date:', 'oli-pdf-invoices-packing-slips' ); ?></strong> <?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $invoice_date ) ) ); ?><br>
					<strong><?php esc_html_e( 'Order Number:', 'oli-pdf-invoices-packing-slips' ); ?></strong> <?php echo esc_html( $order->get_order_number() ); ?><br>
					<strong><?php esc_html_e( 'Order Date:', 'oli-pdf-invoices-packing-slips' ); ?></strong> <?php echo esc_html( $order->get_date_created()->date_i18n( get_option( 'date_format' ) ) ); ?>
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
				<th class="text-right"><?php esc_html_e( 'Quantity', 'oli-pdf-invoices-packing-slips' ); ?></th>
				<th class="text-right"><?php esc_html_e( 'Price', 'oli-pdf-invoices-packing-slips' ); ?></th>
				<th class="text-right"><?php esc_html_e( 'Total', 'oli-pdf-invoices-packing-slips' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $order->get_items() as $item ) : ?>
				<?php $product = $item->get_product(); ?>
				<tr>
					<td>
						<?php echo esc_html( $item->get_name() ); ?>
						<?php if ( $show_meta && $item->get_meta_data() ) : ?>
							<br>
							<small>
								<?php
								foreach ( $item->get_meta_data() as $meta ) {
									if ( strpos( $meta->key, '_' ) === 0 ) {
										continue;
									}
									echo esc_html( $meta->display_key ) . ': ' . esc_html( $meta->display_value ) . '<br>';
								}
								?>
							</small>
						<?php endif; ?>
					</td>
					<?php if ( $show_sku ) : ?>
						<td><?php echo $product ? esc_html( $product->get_sku() ) : ''; ?></td>
					<?php endif; ?>
					<td class="text-right"><?php echo esc_html( $item->get_quantity() ); ?></td>
					<td class="text-right"><?php echo wp_kses_post( wc_price( $order->get_item_subtotal( $item, false, true ), array( 'currency' => $order->get_currency() ) ) ); ?></td>
					<td class="text-right"><?php echo wp_kses_post( wc_price( $order->get_line_subtotal( $item, false, true ), array( 'currency' => $order->get_currency() ) ) ); ?></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<table class="totals-table">
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
		<?php else : ?>
			<?php if ( $order->get_total_tax() > 0 ) : ?>
				<tr>
					<td class="total-label"><?php esc_html_e( 'Tax:', 'oli-pdf-invoices-packing-slips' ); ?></td>
					<td><?php echo wp_kses_post( wc_price( $order->get_total_tax(), array( 'currency' => $order->get_currency() ) ) ); ?></td>
				</tr>
			<?php endif; ?>
		<?php endif; ?>

		<tr class="grand-total">
			<td class="total-label"><?php esc_html_e( 'Total:', 'oli-pdf-invoices-packing-slips' ); ?></td>
			<td><?php echo wp_kses_post( wc_price( $order->get_total(), array( 'currency' => $order->get_currency() ) ) ); ?></td>
		</tr>
	</table>

	<?php if ( $show_payment && $order->get_payment_method_title() ) : ?>
		<p>
			<strong><?php esc_html_e( 'Payment Method:', 'oli-pdf-invoices-packing-slips' ); ?></strong>
			<?php echo esc_html( $order->get_payment_method_title() ); ?>
		</p>
	<?php endif; ?>

	<?php if ( $show_note && $order->get_customer_note() ) : ?>
		<p>
			<strong><?php esc_html_e( 'Customer Note:', 'oli-pdf-invoices-packing-slips' ); ?></strong><br>
			<?php echo esc_html( $order->get_customer_note() ); ?>
		</p>
	<?php endif; ?>

	<?php if ( $footer_text ) : ?>
		<div class="footer">
			<?php echo wp_kses_post( nl2br( $footer_text ) ); ?>
		</div>
	<?php endif; ?>
</body>
</html>
