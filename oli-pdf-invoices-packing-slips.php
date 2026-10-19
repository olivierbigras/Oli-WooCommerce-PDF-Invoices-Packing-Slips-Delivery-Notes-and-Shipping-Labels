<?php
/**
 * Plugin Name: Oli PDF Invoices, Packing Slips, Delivery Notes and Shipping Labels for WooCommerce
 * Plugin URI: https://olivierbigras.com/
 * Description: Generate and print PDF invoices, packing slips, delivery notes, and shipping labels for WooCommerce orders. Customize templates, auto-attach to emails, and manage sequential invoice numbering.
 * Version: 0.1.0
 * Author: Olivier Bigras
 * Author URI: https://olivierbigras.com/
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: oli-pdf-invoices-packing-slips
 * Domain Path: /languages
 * Requires at least: 6.5
 * Tested up to: 7.1
 * Requires PHP: 7.4
 * WC requires at least: 9.0
 * WC tested up to: 11.0
 *
 * @package Oli_PDF
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'OLI_PDF_VERSION' ) ) {
	define( 'OLI_PDF_VERSION', '0.1.0' );
}

if ( ! defined( 'OLI_PDF_PLUGIN_FILE' ) ) {
	define( 'OLI_PDF_PLUGIN_FILE', __FILE__ );
}

if ( ! defined( 'OLI_PDF_PLUGIN_DIR' ) ) {
	define( 'OLI_PDF_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'OLI_PDF_PLUGIN_URL' ) ) {
	define( 'OLI_PDF_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

require_once OLI_PDF_PLUGIN_DIR . 'vendor/autoload.php';

/**
 * Main plugin class
 */
final class Oli_PDF_Invoices {

	/**
	 * Single instance
	 *
	 * @var Oli_PDF_Invoices
	 */
	private static $instance = null;

	/**
	 * Get instance
	 *
	 * @return Oli_PDF_Invoices
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 */
	private function __construct() {
		add_action( 'plugins_loaded', array( $this, 'init' ) );
		register_activation_hook( __FILE__, array( $this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );
	}

	/**
	 * Initialize plugin
	 */
	public function init() {
		if ( ! $this->check_dependencies() ) {
			return;
		}

		load_plugin_textdomain( 'oli-pdf-invoices-packing-slips', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

		$this->includes();
		$this->init_hooks();
	}

	/**
	 * Check dependencies
	 *
	 * @return bool
	 */
	private function check_dependencies() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			add_action( 'admin_notices', array( $this, 'woocommerce_missing_notice' ) );
			return false;
		}

		if ( version_compare( WC_VERSION, '9.0', '<' ) ) {
			add_action( 'admin_notices', array( $this, 'woocommerce_version_notice' ) );
			return false;
		}

		return true;
	}

	/**
	 * Include required files
	 */
	private function includes() {
		require_once OLI_PDF_PLUGIN_DIR . 'includes/class-oli-pdf-settings.php';
		require_once OLI_PDF_PLUGIN_DIR . 'includes/class-oli-pdf-generator.php';
		require_once OLI_PDF_PLUGIN_DIR . 'includes/class-oli-pdf-numbering.php';
		require_once OLI_PDF_PLUGIN_DIR . 'includes/class-oli-pdf-admin.php';
		require_once OLI_PDF_PLUGIN_DIR . 'includes/class-oli-pdf-emails.php';
		require_once OLI_PDF_PLUGIN_DIR . 'includes/class-oli-pdf-my-account.php';
		require_once OLI_PDF_PLUGIN_DIR . 'includes/class-oli-pdf-migration.php';
		require_once OLI_PDF_PLUGIN_DIR . 'includes/class-oli-pdf-bulk-actions.php';
	}

	/**
	 * Initialize hooks
	 */
	private function init_hooks() {
		new Oli_PDF\Settings();
		new Oli_PDF\Admin();
		new Oli_PDF\Emails();
		new Oli_PDF\My_Account();
		new Oli_PDF\Bulk_Actions();
	}

	/**
	 * Activation hook
	 */
	public function activate() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			deactivate_plugins( plugin_basename( __FILE__ ) );
			wp_die( esc_html__( 'This plugin requires WooCommerce to be installed and active.', 'oli-pdf-invoices-packing-slips' ) );
		}

		$this->create_tables();
		$this->set_default_options();

		flush_rewrite_rules();
	}

	/**
	 * Deactivation hook
	 */
	public function deactivate() {
		flush_rewrite_rules();
	}

	/**
	 * Create database tables
	 */
	private function create_tables() {
		// Future: store invoice numbers in custom table for better performance
	}

	/**
	 * Set default options
	 */
	private function set_default_options() {
		$defaults = array(
			'invoice_number_prefix'      => '',
			'invoice_number_suffix'      => '',
			'invoice_number_padding'     => 4,
			'invoice_number_start'       => 1,
			'invoice_generation_statuses' => array( 'processing', 'completed' ),
			'invoice_email_attach'       => array( 'customer_invoice', 'customer_completed_order' ),
			'show_sku'                   => 'yes',
			'show_weight'                => 'yes',
			'show_tax_breakdown'         => 'yes',
			'show_shipping'              => 'yes',
			'show_payment_method'        => 'yes',
			'show_customer_note'         => 'yes',
			'show_product_images'        => 'no',
			'show_product_meta'          => 'yes',
			'company_name'               => '',
			'company_address'            => '',
			'vat_number'                 => '',
			'tax_number'                 => '',
			'footer_text'                => '',
			'logo_url'                   => '',
		);

		foreach ( $defaults as $key => $value ) {
			if ( false === get_option( 'oli_pdf_' . $key ) ) {
				add_option( 'oli_pdf_' . $key, $value );
			}
		}
	}

	/**
	 * WooCommerce missing notice
	 */
	public function woocommerce_missing_notice() {
		?>
		<div class="notice notice-error">
			<p><?php esc_html_e( 'Oli PDF Invoices requires WooCommerce to be installed and active.', 'oli-pdf-invoices-packing-slips' ); ?></p>
		</div>
		<?php
	}

	/**
	 * WooCommerce version notice
	 */
	public function woocommerce_version_notice() {
		?>
		<div class="notice notice-error">
			<p><?php esc_html_e( 'Oli PDF Invoices requires WooCommerce 9.0 or higher.', 'oli-pdf-invoices-packing-slips' ); ?></p>
		</div>
		<?php
	}
}

/**
 * Initialize plugin
 */
function oli_pdf_invoices() {
	return Oli_PDF_Invoices::instance();
}

oli_pdf_invoices();
