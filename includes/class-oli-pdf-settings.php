<?php
/**
 * Settings management
 *
 * @package Oli_PDF
 */

namespace Oli_PDF;

defined( 'ABSPATH' ) || exit;

/**
 * Settings class
 */
class Settings {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Add menu
	 */
	public function add_menu() {
		add_submenu_page(
			'woocommerce',
			__( 'PDF Documents', 'oli-pdf-invoices-packing-slips' ),
			__( 'PDF Documents', 'oli-pdf-invoices-packing-slips' ),
			'manage_woocommerce',
			'oli-pdf-settings',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Register settings
	 */
	public function register_settings() {
		$settings = $this->get_settings_fields();

		foreach ( $settings as $section => $fields ) {
			add_settings_section(
				'oli_pdf_' . $section,
				$fields['title'],
				null,
				'oli-pdf-settings'
			);

			foreach ( $fields['fields'] as $field_id => $field ) {
				register_setting(
					'oli-pdf-settings',
					'oli_pdf_' . $field_id,
					array(
						'sanitize_callback' => array( $this, 'sanitize_field' ),
					)
				);

				add_settings_field(
					'oli_pdf_' . $field_id,
					$field['label'],
					array( $this, 'render_field' ),
					'oli-pdf-settings',
					'oli_pdf_' . $section,
					array_merge( $field, array( 'id' => $field_id ) )
				);
			}
		}
	}

	/**
	 * Get settings fields
	 *
	 * @return array
	 */
	private function get_settings_fields() {
		return array(
			'invoice'  => array(
				'title'  => __( 'Invoice Settings', 'oli-pdf-invoices-packing-slips' ),
				'fields' => array(
					'invoice_number_prefix'       => array(
						'label' => __( 'Invoice Number Prefix', 'oli-pdf-invoices-packing-slips' ),
						'type'  => 'text',
					),
					'invoice_number_suffix'       => array(
						'label' => __( 'Invoice Number Suffix', 'oli-pdf-invoices-packing-slips' ),
						'type'  => 'text',
					),
					'invoice_number_padding'      => array(
						'label'   => __( 'Invoice Number Padding', 'oli-pdf-invoices-packing-slips' ),
						'type'    => 'number',
						'default' => 4,
					),
					'invoice_number_start'        => array(
						'label'   => __( 'Start Invoice Number', 'oli-pdf-invoices-packing-slips' ),
						'type'    => 'number',
						'default' => 1,
					),
					'invoice_generation_statuses' => array(
						'label'   => __( 'Generate Invoice on Order Status', 'oli-pdf-invoices-packing-slips' ),
						'type'    => 'multiselect',
						'options' => $this->get_order_statuses(),
						'default' => array( 'processing', 'completed' ),
					),
					'invoice_email_attach'        => array(
						'label'   => __( 'Attach Invoice to Emails', 'oli-pdf-invoices-packing-slips' ),
						'type'    => 'multiselect',
						'options' => $this->get_email_types(),
						'default' => array( 'customer_invoice', 'customer_completed_order' ),
					),
				),
			),
			'company'  => array(
				'title'  => __( 'Company Information', 'oli-pdf-invoices-packing-slips' ),
				'fields' => array(
					'company_name'    => array(
						'label' => __( 'Company Name', 'oli-pdf-invoices-packing-slips' ),
						'type'  => 'text',
					),
					'company_address' => array(
						'label' => __( 'Company Address', 'oli-pdf-invoices-packing-slips' ),
						'type'  => 'textarea',
					),
					'vat_number'      => array(
						'label' => __( 'VAT Number', 'oli-pdf-invoices-packing-slips' ),
						'type'  => 'text',
					),
					'tax_number'      => array(
						'label' => __( 'Tax Number', 'oli-pdf-invoices-packing-slips' ),
						'type'  => 'text',
					),
					'logo_url'        => array(
						'label' => __( 'Company Logo URL', 'oli-pdf-invoices-packing-slips' ),
						'type'  => 'url',
					),
				),
			),
			'template' => array(
				'title'  => __( 'Template Settings', 'oli-pdf-invoices-packing-slips' ),
				'fields' => array(
					'show_sku'            => array(
						'label' => __( 'Show SKU', 'oli-pdf-invoices-packing-slips' ),
						'type'  => 'checkbox',
					),
					'show_weight'         => array(
						'label' => __( 'Show Weight', 'oli-pdf-invoices-packing-slips' ),
						'type'  => 'checkbox',
					),
					'show_tax_breakdown'  => array(
						'label' => __( 'Show Tax Breakdown', 'oli-pdf-invoices-packing-slips' ),
						'type'  => 'checkbox',
					),
					'show_shipping'       => array(
						'label' => __( 'Show Shipping', 'oli-pdf-invoices-packing-slips' ),
						'type'  => 'checkbox',
					),
					'show_payment_method' => array(
						'label' => __( 'Show Payment Method', 'oli-pdf-invoices-packing-slips' ),
						'type'  => 'checkbox',
					),
					'show_customer_note'  => array(
						'label' => __( 'Show Customer Note', 'oli-pdf-invoices-packing-slips' ),
						'type'  => 'checkbox',
					),
					'show_product_images' => array(
						'label' => __( 'Show Product Images', 'oli-pdf-invoices-packing-slips' ),
						'type'  => 'checkbox',
					),
					'show_product_meta'   => array(
						'label' => __( 'Show Product Meta', 'oli-pdf-invoices-packing-slips' ),
						'type'  => 'checkbox',
					),
					'footer_text'         => array(
						'label' => __( 'Footer Text', 'oli-pdf-invoices-packing-slips' ),
						'type'  => 'textarea',
					),
				),
			),
		);
	}

	/**
	 * Get order statuses
	 *
	 * @return array
	 */
	private function get_order_statuses() {
		$statuses = wc_get_order_statuses();
		return array_map( 'wc_get_order_status_name', array_keys( $statuses ) );
	}

	/**
	 * Get email types
	 *
	 * @return array
	 */
	private function get_email_types() {
		return array(
			'customer_invoice'          => __( 'Customer Invoice', 'oli-pdf-invoices-packing-slips' ),
			'customer_completed_order'  => __( 'Completed Order', 'oli-pdf-invoices-packing-slips' ),
			'customer_processing_order' => __( 'Processing Order', 'oli-pdf-invoices-packing-slips' ),
			'customer_on_hold_order'    => __( 'Order On Hold', 'oli-pdf-invoices-packing-slips' ),
		);
	}

	/**
	 * Render settings page
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		if ( isset( $_GET['action'] ) && 'migrate' === $_GET['action'] ) {
			check_admin_referer( 'oli_pdf_migrate' );
			$migration = new Migration();
			$result    = $migration->migrate( isset( $_GET['dry_run'] ) );
			?>
			<div class="notice notice-success">
				<p><?php echo esc_html( $result['message'] ); ?></p>
			</div>
			<?php
		}
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

			<?php settings_errors(); ?>

			<nav class="nav-tab-wrapper">
				<a href="?page=oli-pdf-settings&tab=invoice" class="nav-tab <?php echo ! isset( $_GET['tab'] ) || 'invoice' === $_GET['tab'] ? 'nav-tab-active' : ''; ?>">
					<?php esc_html_e( 'Invoice', 'oli-pdf-invoices-packing-slips' ); ?>
				</a>
				<a href="?page=oli-pdf-settings&tab=company" class="nav-tab <?php echo isset( $_GET['tab'] ) && 'company' === $_GET['tab'] ? 'nav-tab-active' : ''; ?>">
					<?php esc_html_e( 'Company', 'oli-pdf-invoices-packing-slips' ); ?>
				</a>
				<a href="?page=oli-pdf-settings&tab=template" class="nav-tab <?php echo isset( $_GET['tab'] ) && 'template' === $_GET['tab'] ? 'nav-tab-active' : ''; ?>">
					<?php esc_html_e( 'Template', 'oli-pdf-invoices-packing-slips' ); ?>
				</a>
				<a href="?page=oli-pdf-settings&tab=migration" class="nav-tab <?php echo isset( $_GET['tab'] ) && 'migration' === $_GET['tab'] ? 'nav-tab-active' : ''; ?>">
					<?php esc_html_e( 'Migration', 'oli-pdf-invoices-packing-slips' ); ?>
				</a>
			</nav>

			<form method="post" action="options.php">
				<?php
				settings_fields( 'oli-pdf-settings' );
				$current_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'invoice';

				if ( 'migration' === $current_tab ) {
					$this->render_migration_tab();
				} else {
					do_settings_sections( 'oli-pdf-settings' );
					submit_button();
				}
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Render migration tab
	 */
	private function render_migration_tab() {
		?>
		<h2><?php esc_html_e( 'Migrate from WebToffee Plugin', 'oli-pdf-invoices-packing-slips' ); ?></h2>
		<p><?php esc_html_e( 'Migrate settings and invoice numbers from the WebToffee Print Invoices plugin.', 'oli-pdf-invoices-packing-slips' ); ?></p>
		
		<p>
			<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=oli-pdf-settings&tab=migration&action=migrate&dry_run=1' ), 'oli_pdf_migrate' ) ); ?>" class="button">
				<?php esc_html_e( 'Dry Run (Preview)', 'oli-pdf-invoices-packing-slips' ); ?>
			</a>
			<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=oli-pdf-settings&tab=migration&action=migrate' ), 'oli_pdf_migrate' ) ); ?>" class="button button-primary">
				<?php esc_html_e( 'Run Migration', 'oli-pdf-invoices-packing-slips' ); ?>
			</a>
		</p>
		<?php
	}

	/**
	 * Render field
	 *
	 * @param array $args Field arguments.
	 */
	public function render_field( $args ) {
		$value = get_option( 'oli_pdf_' . $args['id'], $args['default'] ?? '' );

		switch ( $args['type'] ) {
			case 'text':
			case 'url':
			case 'number':
				printf(
					'<input type="%s" name="oli_pdf_%s" id="oli_pdf_%s" value="%s" class="regular-text" />',
					esc_attr( $args['type'] ),
					esc_attr( $args['id'] ),
					esc_attr( $args['id'] ),
					esc_attr( $value )
				);
				break;

			case 'textarea':
				printf(
					'<textarea name="oli_pdf_%s" id="oli_pdf_%s" rows="5" class="large-text">%s</textarea>',
					esc_attr( $args['id'] ),
					esc_attr( $args['id'] ),
					esc_textarea( $value )
				);
				break;

			case 'checkbox':
				printf(
					'<input type="checkbox" name="oli_pdf_%s" id="oli_pdf_%s" value="yes" %s />',
					esc_attr( $args['id'] ),
					esc_attr( $args['id'] ),
					checked( $value, 'yes', false )
				);
				break;

			case 'multiselect':
				if ( ! is_array( $value ) ) {
					$value = array();
				}
				printf(
					'<select name="oli_pdf_%s[]" id="oli_pdf_%s" multiple class="regular-text" style="height:150px;">',
					esc_attr( $args['id'] ),
					esc_attr( $args['id'] )
				);
				foreach ( $args['options'] as $key => $label ) {
					printf(
						'<option value="%s" %s>%s</option>',
						esc_attr( $key ),
						selected( in_array( $key, $value, true ), true, false ),
						esc_html( $label )
					);
				}
				echo '</select>';
				break;
		}

		if ( ! empty( $args['description'] ) ) {
			printf( '<p class="description">%s</p>', esc_html( $args['description'] ) );
		}
	}

	/**
	 * Sanitize field
	 *
	 * @param mixed $value Field value.
	 * @return mixed
	 */
	public function sanitize_field( $value ) {
		if ( is_array( $value ) ) {
			return array_map( 'sanitize_text_field', $value );
		}
		return sanitize_text_field( $value );
	}

	/**
	 * Enqueue assets
	 *
	 * @param string $hook Hook suffix.
	 */
	public function enqueue_assets( $hook ) {
		if ( 'woocommerce_page_oli-pdf-settings' !== $hook ) {
			return;
		}

		wp_enqueue_media();
	}
}
