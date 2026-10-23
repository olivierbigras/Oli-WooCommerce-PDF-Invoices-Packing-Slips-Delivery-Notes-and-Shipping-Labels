<?php
/**
 * Plugin Name: Oli PDF Invoices & Packing Slips for WooCommerce
 * Plugin URI: https://olivierbigras.com/
 * Description: Generate and print PDF invoices, packing slips, delivery notes, shipping labels, and credit notes for WooCommerce orders. Customize templates, auto-attach to emails, and manage sequential invoice numbering.
 * Version: 0.1.3
 * Author: Olivier Bigras
 * Author URI: https://olivierbigras.com/
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: oli-pdf-invoices-packing-slips
 * Domain Path: /languages
 * Requires at least: 6.5
 * Tested up to: 7.1
 * Requires PHP: 7.4
 * Requires Plugins: woocommerce
 * WC requires at least: 9.0
 * WC tested up to: 11.0
 *
 * @package Oli_PDF
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'OLI_PDF_VERSION' ) ) {
	define( 'OLI_PDF_VERSION', '0.1.3' );
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

$oli_pdf_autoload = OLI_PDF_PLUGIN_DIR . 'vendor/autoload.php';
if ( file_exists( $oli_pdf_autoload ) ) {
	require_once $oli_pdf_autoload;
}

/**
 * Main plugin class.
 */
final class Oli_PDF_Invoices {

	/**
	 * Single instance.
	 *
	 * @var Oli_PDF_Invoices|null
	 */
	private static $instance = null;

	/**
	 * Get instance.
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
	 * Constructor.
	 */
	private function __construct() {
		add_action( 'before_woocommerce_init', array( $this, 'declare_hpos_compatibility' ) );
		add_action( 'plugins_loaded', array( $this, 'init' ) );
		register_activation_hook( __FILE__, array( $this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );
	}

	/**
	 * Declare HPOS compatibility.
	 */
	public function declare_hpos_compatibility() {
		if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', OLI_PDF_PLUGIN_FILE, true );
		}
	}

	/**
	 * Initialize plugin.
	 */
	public function init() {
		if ( ! $this->check_dependencies() ) {
			return;
		}

		load_plugin_textdomain( 'oli-pdf-invoices-packing-slips', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

		$this->includes();
		Oli_PDF\Upgrade::maybe_upgrade();
		$this->init_hooks();
	}

	/**
	 * Check dependencies.
	 *
	 * @return bool
	 */
	private function check_dependencies() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			add_action( 'admin_notices', array( $this, 'woocommerce_missing_notice' ) );
			return false;
		}

		if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '9.0', '<' ) ) {
			add_action( 'admin_notices', array( $this, 'woocommerce_version_notice' ) );
			return false;
		}

		if ( ! file_exists( OLI_PDF_PLUGIN_DIR . 'vendor/autoload.php' ) ) {
			add_action( 'admin_notices', array( $this, 'vendor_missing_notice' ) );
			return false;
		}

		return true;
	}

	/**
	 * Include required files.
	 */
	private function includes() {
		require_once OLI_PDF_PLUGIN_DIR . 'includes/class-oli-pdf-settings.php';
		require_once OLI_PDF_PLUGIN_DIR . 'includes/class-oli-pdf-generator.php';
		require_once OLI_PDF_PLUGIN_DIR . 'includes/class-oli-pdf-numbering.php';
		require_once OLI_PDF_PLUGIN_DIR . 'includes/class-oli-pdf-admin.php';
		require_once OLI_PDF_PLUGIN_DIR . 'includes/class-oli-pdf-emails.php';
		require_once OLI_PDF_PLUGIN_DIR . 'includes/class-oli-pdf-my-account.php';
		require_once OLI_PDF_PLUGIN_DIR . 'includes/class-oli-pdf-migration.php';
		require_once OLI_PDF_PLUGIN_DIR . 'includes/class-oli-pdf-upgrade.php';
		require_once OLI_PDF_PLUGIN_DIR . 'includes/class-oli-pdf-bulk-actions.php';
	}

	/**
	 * Initialize hooks.
	 */
	private function init_hooks() {
		new Oli_PDF\Settings();
		new Oli_PDF\Admin();
		new Oli_PDF\Emails();
		new Oli_PDF\My_Account();
		new Oli_PDF\Bulk_Actions();
	}

	/**
	 * Activation hook.
	 */
	public function activate() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			deactivate_plugins( plugin_basename( __FILE__ ) );
			wp_die( esc_html__( 'This plugin requires WooCommerce to be installed and active.', 'oli-pdf-invoices-packing-slips' ) );
		}

		$this->set_default_options();
		if ( ! class_exists( '\Oli_PDF\Upgrade' ) ) {
			require_once OLI_PDF_PLUGIN_DIR . 'includes/class-oli-pdf-upgrade.php';
		}
		Oli_PDF\Upgrade::maybe_upgrade();
		flush_rewrite_rules();
	}

	/**
	 * Deactivation hook.
	 */
	public function deactivate() {
		flush_rewrite_rules();
	}

	/**
	 * Set default options.
	 */
	private function set_default_options() {
		$defaults = array(
			'invoice_number_prefix'       => '',
			'invoice_number_suffix'       => '',
			'invoice_number_padding'      => 4,
			'invoice_number_start'        => 1,
			'invoice_generation_statuses' => array( 'processing', 'completed' ),
			'invoice_email_attach'        => array( 'customer_invoice', 'customer_completed_order' ),
			'enable_credit_notes'         => 'yes',
			'credit_note_number_prefix'   => 'CN-',
			'credit_note_number_suffix'   => '',
			'credit_note_number_padding'  => 4,
			'credit_note_number_start'    => 1,
			'label_size'                  => 'a4',
			'label_custom_width'          => 100,
			'label_custom_height'         => 150,
			'labels_per_sheet'            => 1,
			'label_columns'               => 1,
			'label_rows'                  => 1,
			'show_sku'                    => 'yes',
			'show_weight'                 => 'yes',
			'show_tax_breakdown'          => 'yes',
			'show_shipping'               => 'yes',
			'show_payment_method'         => 'yes',
			'show_customer_note'          => 'yes',
			'show_product_images'         => 'no',
			'product_image_size'          => 50,
			'show_product_meta'           => 'yes',
			'template_primary_color'      => '#000000',
			'template_secondary_color'    => '#f5f5f5',
			'company_name'                => '',
			'company_address'             => '',
			'vat_number'                  => '',
			'tax_number'                  => '',
			'footer_text'                 => '',
			'logo_url'                    => '',
		);

		foreach ( $defaults as $key => $value ) {
			if ( false === get_option( 'oli_pdf_' . $key ) ) {
				add_option( 'oli_pdf_' . $key, $value );
			}
		}
	}

	/**
	 * WooCommerce missing notice.
	 */
	public function woocommerce_missing_notice() {
		printf(
			'<div class="notice notice-error"><p>%s</p></div>',
			esc_html__( 'Oli PDF Invoices requires WooCommerce to be installed and active.', 'oli-pdf-invoices-packing-slips' )
		);
	}

	/**
	 * WooCommerce version notice.
	 */
	public function woocommerce_version_notice() {
		printf(
			'<div class="notice notice-error"><p>%s</p></div>',
			esc_html__( 'Oli PDF Invoices requires WooCommerce 9.0 or higher.', 'oli-pdf-invoices-packing-slips' )
		);
	}

	/**
	 * Vendor missing notice.
	 */
	public function vendor_missing_notice() {
		printf(
			'<div class="notice notice-error"><p>%s</p></div>',
			esc_html__( 'Oli PDF Invoices is missing Composer dependencies. Run composer install --no-dev or install the release zip that includes vendor/.', 'oli-pdf-invoices-packing-slips' )
		);
	}
}

/**
 * Initialize plugin.
 *
 * @return Oli_PDF_Invoices
 */
function oli_pdf_invoices() {
	return Oli_PDF_Invoices::instance();
}

oli_pdf_invoices();
