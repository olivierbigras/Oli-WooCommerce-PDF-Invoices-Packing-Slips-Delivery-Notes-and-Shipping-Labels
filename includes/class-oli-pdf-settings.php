<?php
/**
 * Settings management.
 *
 * @package Oli_PDF
 */

namespace Oli_PDF;

defined( 'ABSPATH' ) || exit;

/**
 * Settings class.
 */
class Settings {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Add menu.
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
	 * Register settings.
	 */
	public function register_settings() {
		$settings = $this->get_settings_fields();

		foreach ( $settings as $section => $section_data ) {
			$page = 'oli-pdf-settings-' . $section;

			add_settings_section(
				'oli_pdf_' . $section,
				$section_data['title'],
				'__return_false',
				$page
			);

			foreach ( $section_data['fields'] as $field_id => $field ) {
				$option_name = 'oli_pdf_' . $field_id;
				$field_type  = isset( $field['type'] ) ? $field['type'] : 'text';

				register_setting(
					'oli-pdf-settings-' . $section,
					$option_name,
					array(
						'type'              => 'string',
						'sanitize_callback' => function ( $value ) use ( $field_type ) {
							return Settings::sanitize_by_type( $value, $field_type );
						},
					)
				);

				add_settings_field(
					$option_name,
					$field['label'],
					array( $this, 'render_field' ),
					$page,
					'oli_pdf_' . $section,
					array_merge( $field, array( 'id' => $field_id ) )
				);
			}
		}
	}

	/**
	 * Get settings fields.
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
					'enable_credit_notes'         => array(
						'label' => __( 'Enable Credit Notes for Refunds', 'oli-pdf-invoices-packing-slips' ),
						'type'  => 'checkbox',
					),
					'credit_note_number_prefix'   => array(
						'label'   => __( 'Credit Note Number Prefix', 'oli-pdf-invoices-packing-slips' ),
						'type'    => 'text',
						'default' => 'CN-',
					),
					'credit_note_number_suffix'   => array(
						'label' => __( 'Credit Note Number Suffix', 'oli-pdf-invoices-packing-slips' ),
						'type'  => 'text',
					),
					'credit_note_number_padding'  => array(
						'label'   => __( 'Credit Note Number Padding', 'oli-pdf-invoices-packing-slips' ),
						'type'    => 'number',
						'default' => 4,
					),
					'credit_note_number_start'    => array(
						'label'   => __( 'Start Credit Note Number', 'oli-pdf-invoices-packing-slips' ),
						'type'    => 'number',
						'default' => 1,
					),
				),
			),
			'labels'   => array(
				'title'  => __( 'Shipping Label Settings', 'oli-pdf-invoices-packing-slips' ),
				'fields' => array(
					'label_size'          => array(
						'label'   => __( 'Label Size', 'oli-pdf-invoices-packing-slips' ),
						'type'    => 'select',
						'options' => array(
							'a4'     => 'A4 (210 x 297 mm)',
							'letter' => 'Letter (8.5 x 11 in)',
							'4x6'    => '4" x 6" (Shipping Label)',
							'4x4'    => '4" x 4" (Square Label)',
							'custom' => __( 'Custom Size', 'oli-pdf-invoices-packing-slips' ),
						),
						'default' => 'a4',
					),
					'label_custom_width'  => array(
						'label'   => __( 'Custom Width (mm)', 'oli-pdf-invoices-packing-slips' ),
						'type'    => 'number',
						'default' => 100,
					),
					'label_custom_height' => array(
						'label'   => __( 'Custom Height (mm)', 'oli-pdf-invoices-packing-slips' ),
						'type'    => 'number',
						'default' => 150,
					),
					'labels_per_sheet'    => array(
						'label'       => __( 'Labels Per Sheet', 'oli-pdf-invoices-packing-slips' ),
						'type'        => 'number',
						'default'     => 1,
						'description' => __( 'How many copies of a label to print on one sheet. Combined with columns and rows.', 'oli-pdf-invoices-packing-slips' ),
					),
					'label_columns'       => array(
						'label'   => __( 'Columns', 'oli-pdf-invoices-packing-slips' ),
						'type'    => 'number',
						'default' => 1,
					),
					'label_rows'          => array(
						'label'   => __( 'Rows', 'oli-pdf-invoices-packing-slips' ),
						'type'    => 'number',
						'default' => 1,
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
						'label'       => __( 'Company Logo URL', 'oli-pdf-invoices-packing-slips' ),
						'type'        => 'url',
						'description' => __( 'Use a Media Library URL on this site. Remote/CDN images are converted to a local copy when possible.', 'oli-pdf-invoices-packing-slips' ),
					),
				),
			),
			'template' => array(
				'title'  => __( 'Template Settings', 'oli-pdf-invoices-packing-slips' ),
				'fields' => array(
					'template_primary_color'   => array(
						'label'   => __( 'Primary Color', 'oli-pdf-invoices-packing-slips' ),
						'type'    => 'color',
						'default' => '#000000',
					),
					'template_secondary_color' => array(
						'label'   => __( 'Secondary Color', 'oli-pdf-invoices-packing-slips' ),
						'type'    => 'color',
						'default' => '#f5f5f5',
					),
					'show_sku'                 => array(
						'label' => __( 'Show SKU', 'oli-pdf-invoices-packing-slips' ),
						'type'  => 'checkbox',
					),
					'show_weight'              => array(
						'label' => __( 'Show Weight', 'oli-pdf-invoices-packing-slips' ),
						'type'  => 'checkbox',
					),
					'show_tax_breakdown'       => array(
						'label' => __( 'Show Tax Breakdown', 'oli-pdf-invoices-packing-slips' ),
						'type'  => 'checkbox',
					),
					'show_shipping'            => array(
						'label' => __( 'Show Shipping', 'oli-pdf-invoices-packing-slips' ),
						'type'  => 'checkbox',
					),
					'show_payment_method'      => array(
						'label' => __( 'Show Payment Method', 'oli-pdf-invoices-packing-slips' ),
						'type'  => 'checkbox',
					),
					'show_customer_note'       => array(
						'label' => __( 'Show Customer Note', 'oli-pdf-invoices-packing-slips' ),
						'type'  => 'checkbox',
					),
					'show_product_images'      => array(
						'label' => __( 'Show Product Images', 'oli-pdf-invoices-packing-slips' ),
						'type'  => 'checkbox',
					),
					'product_image_size'       => array(
						'label'   => __( 'Product Image Size (px)', 'oli-pdf-invoices-packing-slips' ),
						'type'    => 'number',
						'default' => 50,
					),
					'show_product_meta'        => array(
						'label' => __( 'Show Product Meta', 'oli-pdf-invoices-packing-slips' ),
						'type'  => 'checkbox',
					),
					'footer_text'              => array(
						'label' => __( 'Footer Text', 'oli-pdf-invoices-packing-slips' ),
						'type'  => 'textarea',
					),
				),
			),
		);
	}

	/**
	 * Get order statuses without the wc- prefix.
	 *
	 * @return array
	 */
	private function get_order_statuses() {
		if ( ! function_exists( 'wc_get_order_statuses' ) ) {
			return array(
				'processing' => __( 'Processing', 'oli-pdf-invoices-packing-slips' ),
				'completed'  => __( 'Completed', 'oli-pdf-invoices-packing-slips' ),
			);
		}

		$statuses = array();
		foreach ( wc_get_order_statuses() as $status_key => $label ) {
			$statuses[ str_replace( 'wc-', '', $status_key ) ] = $label;
		}

		return $statuses;
	}

	/**
	 * Get email types.
	 *
	 * @return array
	 */
	private function get_email_types() {
		return array(
			'customer_invoice'          => __( 'Customer Invoice', 'oli-pdf-invoices-packing-slips' ),
			'customer_completed_order'  => __( 'Completed Order', 'oli-pdf-invoices-packing-slips' ),
			'customer_processing_order' => __( 'Processing Order', 'oli-pdf-invoices-packing-slips' ),
			'customer_on_hold_order'    => __( 'Order On Hold', 'oli-pdf-invoices-packing-slips' ),
			'customer_refunded_order'   => __( 'Refunded Order', 'oli-pdf-invoices-packing-slips' ),
		);
	}

	/**
	 * Render settings page.
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- tab is a read-only navigation key.
		$current_tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'invoice';
		$allowed     = array( 'invoice', 'labels', 'company', 'template', 'migration' );
		if ( ! in_array( $current_tab, $allowed, true ) ) {
			$current_tab = 'invoice';
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- verified below when action is present.
		if ( isset( $_GET['action'] ) && 'migrate' === $_GET['action'] ) {
			check_admin_referer( 'oli_pdf_migrate' );
			$migration = new Migration();
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- nonce checked above.
			$result = $migration->migrate( isset( $_GET['dry_run'] ) );
			echo '<div class="notice notice-success"><p>' . esc_html( $result['message'] ) . '</p></div>';
		}

		$tabs = array(
			'invoice'   => __( 'Invoice', 'oli-pdf-invoices-packing-slips' ),
			'labels'    => __( 'Labels', 'oli-pdf-invoices-packing-slips' ),
			'company'   => __( 'Company', 'oli-pdf-invoices-packing-slips' ),
			'template'  => __( 'Template', 'oli-pdf-invoices-packing-slips' ),
			'migration' => __( 'Migration', 'oli-pdf-invoices-packing-slips' ),
		);
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<?php settings_errors(); ?>
			<nav class="nav-tab-wrapper">
				<?php foreach ( $tabs as $tab_id => $tab_label ) : ?>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=oli-pdf-settings&tab=' . $tab_id ) ); ?>" class="nav-tab <?php echo $current_tab === $tab_id ? 'nav-tab-active' : ''; ?>">
						<?php echo esc_html( $tab_label ); ?>
					</a>
				<?php endforeach; ?>
			</nav>
			<?php if ( 'migration' === $current_tab ) : ?>
				<?php $this->render_migration_tab(); ?>
			<?php else : ?>
				<form method="post" action="options.php">
					<?php
					settings_fields( 'oli-pdf-settings-' . $current_tab );
					do_settings_sections( 'oli-pdf-settings-' . $current_tab );
					submit_button();
					?>
				</form>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Render migration tab.
	 */
	private function render_migration_tab() {
		$dry_run_url = wp_nonce_url( admin_url( 'admin.php?page=oli-pdf-settings&tab=migration&action=migrate&dry_run=1' ), 'oli_pdf_migrate' );
		$run_url     = wp_nonce_url( admin_url( 'admin.php?page=oli-pdf-settings&tab=migration&action=migrate' ), 'oli_pdf_migrate' );
		?>
		<h2><?php esc_html_e( 'Migrate from WebToffee Plugin', 'oli-pdf-invoices-packing-slips' ); ?></h2>
		<p><?php esc_html_e( 'Migrate settings and invoice numbers from the WebToffee Print Invoices plugin (print-invoices-packing-slip-labels-for-woocommerce 5.x). Existing WebToffee data is never deleted.', 'oli-pdf-invoices-packing-slips' ); ?></p>
		<p>
			<a href="<?php echo esc_url( $dry_run_url ); ?>" class="button"><?php esc_html_e( 'Dry Run (Preview)', 'oli-pdf-invoices-packing-slips' ); ?></a>
			<a href="<?php echo esc_url( $run_url ); ?>" class="button button-primary"><?php esc_html_e( 'Run Migration', 'oli-pdf-invoices-packing-slips' ); ?></a>
		</p>
		<?php
	}

	/**
	 * Render field.
	 *
	 * @param array $args Field arguments.
	 */
	public function render_field( $args ) {
		$default = isset( $args['default'] ) ? $args['default'] : '';
		$value   = get_option( 'oli_pdf_' . $args['id'], $default );
		$id      = $args['id'];

		switch ( $args['type'] ) {
			case 'text':
			case 'url':
			case 'number':
				printf(
					'<input type="%1$s" name="oli_pdf_%2$s" id="oli_pdf_%2$s" value="%3$s" class="regular-text" />',
					esc_attr( $args['type'] ),
					esc_attr( $id ),
					esc_attr( $value )
				);
				break;

			case 'textarea':
				printf(
					'<textarea name="oli_pdf_%1$s" id="oli_pdf_%1$s" rows="5" class="large-text">%2$s</textarea>',
					esc_attr( $id ),
					esc_textarea( $value )
				);
				break;

			case 'checkbox':
				printf(
					'<input type="hidden" name="oli_pdf_%1$s" value="no" />',
					esc_attr( $id )
				);
				printf(
					'<input type="checkbox" name="oli_pdf_%1$s" id="oli_pdf_%1$s" value="yes" %2$s />',
					esc_attr( $id ),
					checked( $value, 'yes', false )
				);
				break;

			case 'color':
				printf(
					'<input type="text" name="oli_pdf_%1$s" id="oli_pdf_%1$s" value="%2$s" class="oli-pdf-color-field" data-default-color="%3$s" />',
					esc_attr( $id ),
					esc_attr( $value ),
					esc_attr( $default )
				);
				break;

			case 'select':
				printf( '<select name="oli_pdf_%1$s" id="oli_pdf_%1$s">', esc_attr( $id ) );
				foreach ( $args['options'] as $key => $label ) {
					printf(
						'<option value="%1$s" %2$s>%3$s</option>',
						esc_attr( $key ),
						selected( $value, $key, false ),
						esc_html( $label )
					);
				}
				echo '</select>';
				break;

			case 'multiselect':
				if ( ! is_array( $value ) ) {
					$value = array();
				}
				printf(
					'<select name="oli_pdf_%1$s[]" id="oli_pdf_%1$s" multiple class="regular-text" style="height:150px;">',
					esc_attr( $id )
				);
				foreach ( $args['options'] as $key => $label ) {
					printf(
						'<option value="%1$s" %2$s>%3$s</option>',
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
	 * Sanitize a field by type.
	 *
	 * @param mixed  $value Field value.
	 * @param string $type  Field type.
	 * @return mixed
	 */
	public static function sanitize_by_type( $value, $type ) {
		switch ( $type ) {
			case 'url':
				return esc_url_raw( $value );
			case 'number':
				return absint( $value );
			case 'textarea':
				return sanitize_textarea_field( $value );
			case 'color':
				$color = sanitize_hex_color( $value );
				return $color ? $color : '#000000';
			case 'checkbox':
				return 'yes' === $value ? 'yes' : 'no';
			case 'multiselect':
				if ( ! is_array( $value ) ) {
					return array();
				}
				return array_map( 'sanitize_text_field', $value );
			default:
				return sanitize_text_field( $value );
		}
	}

	/**
	 * Enqueue assets.
	 *
	 * @param string $hook Hook suffix.
	 */
	public function enqueue_assets( $hook ) {
		if ( 'woocommerce_page_oli-pdf-settings' !== $hook ) {
			return;
		}

		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script(
			'oli-pdf-admin',
			OLI_PDF_PLUGIN_URL . 'assets/js/admin.js',
			array( 'jquery', 'wp-color-picker' ),
			OLI_PDF_VERSION,
			true
		);
	}
}
