<?php
/**
 * Standalone tests for option migration and wordpress.org guardrails.
 *
 * @package Oli_PDF
 */

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

if ( ! defined( 'OLI_PDF_PLUGIN_DIR' ) ) {
	define( 'OLI_PDF_PLUGIN_DIR', dirname( __DIR__ ) . '/' );
}

if ( ! defined( 'OLI_PDF_VERSION' ) ) {
	define( 'OLI_PDF_VERSION', '0.1.3' );
}

$oli_pdf_options = array();

if ( ! function_exists( 'get_option' ) ) {
	/**
	 * @param string $name    Option name.
	 * @param mixed  $default Default.
	 * @return mixed
	 */
	function get_option( $name, $default = false ) {
		global $oli_pdf_options;
		return array_key_exists( $name, $oli_pdf_options ) ? $oli_pdf_options[ $name ] : $default;
	}
}

if ( ! function_exists( 'add_option' ) ) {
	/**
	 * @param string $name  Option name.
	 * @param mixed  $value Value.
	 * @return bool
	 */
	function add_option( $name, $value ) {
		global $oli_pdf_options;
		if ( array_key_exists( $name, $oli_pdf_options ) ) {
			return false;
		}
		$oli_pdf_options[ $name ] = $value;
		return true;
	}
}

if ( ! function_exists( 'update_option' ) ) {
	/**
	 * @param string $name  Option name.
	 * @param mixed  $value Value.
	 * @return bool
	 */
	function update_option( $name, $value ) {
		global $oli_pdf_options;
		$oli_pdf_options[ $name ] = $value;
		return true;
	}
}

if ( ! function_exists( 'delete_option' ) ) {
	/**
	 * @param string $name Option name.
	 * @return bool
	 */
	function delete_option( $name ) {
		global $oli_pdf_options;
		unset( $oli_pdf_options[ $name ] );
		return true;
	}
}

require_once dirname( __DIR__ ) . '/includes/class-oli-pdf-upgrade.php';
require_once dirname( __DIR__ ) . '/includes/class-oli-pdf-generator.php';

use Oli_PDF\Upgrade;
use Oli_PDF\Generator;

$failures = 0;

/**
 * Assert helper.
 *
 * @param bool   $condition Condition.
 * @param string $message   Message.
 */
function oli_pdf_guard_assert( $condition, $message ) {
	global $failures;
	if ( $condition ) {
		echo "PASS  $message\n";
		return;
	}
	++$failures;
	echo "FAIL  $message\n";
}

$oli_pdf_options = array(
	'olipdf_company_name' => 'Legacy Co',
	'olipdf_logo_url'     => 'https://example.com/old-logo.png',
	'oli_pdf_vat_number'  => 'KEEP-ME',
);

$copied = Upgrade::migrate_legacy_options();

oli_pdf_guard_assert( 'Legacy Co' === get_option( 'oli_pdf_company_name' ), 'Copies olipdf_company_name onto oli_pdf_company_name' );
oli_pdf_guard_assert( 'https://example.com/old-logo.png' === get_option( 'oli_pdf_logo_url' ), 'Copies olipdf_logo_url onto oli_pdf_logo_url' );
oli_pdf_guard_assert( 'KEEP-ME' === get_option( 'oli_pdf_vat_number' ), 'Leaves an already-present oli_pdf_ option untouched' );
oli_pdf_guard_assert( false === get_option( 'olipdf_company_name', false ), 'Deletes the old olipdf_company_name key after copy' );
oli_pdf_guard_assert( false === get_option( 'olipdf_logo_url', false ), 'Deletes the old olipdf_logo_url key after copy' );
oli_pdf_guard_assert( isset( $copied['oli_pdf_company_name'] ), 'Reports copied company_name' );

$oli_pdf_options = array(
	'olipdf_company_name' => 'Should not overwrite',
	'oli_pdf_company_name' => 'Current name',
);

Upgrade::migrate_legacy_options();
oli_pdf_guard_assert( 'Current name' === get_option( 'oli_pdf_company_name' ), 'Does not overwrite an existing new option' );
oli_pdf_guard_assert( false === get_option( 'olipdf_company_name', false ), 'Still deletes the leftover old key' );

$css_files = array(
	'admin.css',
	'documents.css',
	'invoice.css',
	'packing-slip.css',
	'delivery-note.css',
	'credit-note.css',
	'shipping-label.css',
	'shipping-label-sheet.css',
);
foreach ( $css_files as $css_file ) {
	oli_pdf_guard_assert( is_readable( dirname( __DIR__ ) . '/assets/css/' . $css_file ), 'CSS file exists: ' . $css_file );
}

oli_pdf_guard_assert( is_readable( dirname( __DIR__ ) . '/assets/js/admin.js' ), 'Admin JS file exists' );

$iterator = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( dirname( __DIR__ ) ) );
$blocked  = array();
foreach ( $iterator as $file ) {
	if ( ! $file->isFile() || 'php' !== strtolower( $file->getExtension() ) ) {
		continue;
	}
	$path = $file->getPathname();
	if ( false !== strpos( $path, '/vendor/' ) || false !== strpos( $path, '/tests/' ) || false !== strpos( $path, '/dist/' ) ) {
		continue;
	}
	$contents = file_get_contents( $path );
	if ( preg_match( '/<script[\s>]/i', $contents ) || preg_match( '/<style[\s>]/i', $contents ) ) {
		$blocked[] = $path;
	}
}
oli_pdf_guard_assert( array() === $blocked, 'No literal script/style tags in plugin PHP files' );

$style = Generator::style_element( 'body{color:red;}' );
oli_pdf_guard_assert( false !== strpos( $style, 'body{color:red;}' ), 'style_element wraps CSS' );
oli_pdf_guard_assert( 0 === strpos( $style, '<style' ), 'style_element emits a style element at runtime' );

$oli_pdf_main   = file_get_contents( dirname( __DIR__ ) . '/oli-pdf-invoices-packing-slips.php' );
$oli_pdf_readme = file_get_contents( dirname( __DIR__ ) . '/readme.txt' );
preg_match( '/^\s*\*\s*Tested up to:\s*(\S+)/m', $oli_pdf_main, $oli_pdf_header_tested );
preg_match( '/^Tested up to:\s*(\S+)/m', $oli_pdf_readme, $oli_pdf_readme_tested );
oli_pdf_guard_assert(
	isset( $oli_pdf_header_tested[1], $oli_pdf_readme_tested[1] ) && $oli_pdf_header_tested[1] === $oli_pdf_readme_tested[1],
	'Tested up to matches in the plugin header and readme.txt'
);
preg_match( '/^\s*\*\s*Version:\s*(\S+)/m', $oli_pdf_main, $oli_pdf_header_version );
preg_match( '/^Stable tag:\s*(\S+)/m', $oli_pdf_readme, $oli_pdf_stable );
oli_pdf_guard_assert(
	isset( $oli_pdf_header_version[1], $oli_pdf_stable[1] ) && $oli_pdf_header_version[1] === $oli_pdf_stable[1],
	'Plugin header Version matches readme.txt Stable tag'
);
oli_pdf_guard_assert( is_readable( dirname( __DIR__ ) . '/.distignore' ), '.distignore exists' );
oli_pdf_guard_assert( is_readable( dirname( __DIR__ ) . '/.gitattributes' ), '.gitattributes exists' );
$oli_pdf_gitattributes = file_get_contents( dirname( __DIR__ ) . '/.gitattributes' );
oli_pdf_guard_assert( false !== strpos( $oli_pdf_gitattributes, 'export-ignore' ), '.gitattributes uses export-ignore' );
$oli_pdf_distignore = file_get_contents( dirname( __DIR__ ) . '/.distignore' );
oli_pdf_guard_assert( ! preg_match( '/^composer\s*$/m', $oli_pdf_distignore ), '.distignore does not exclude a bare composer path (would drop vendor/composer)' );

if ( $failures > 0 ) {
	echo "\n$failures test(s) failed.\n";
	exit( 1 );
}

echo "\nAll upgrade and guardrail tests passed.\n";
exit( 0 );
