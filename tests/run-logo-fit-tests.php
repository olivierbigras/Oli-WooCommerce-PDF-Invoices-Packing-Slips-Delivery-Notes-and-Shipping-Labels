<?php
/**
 * Logo fit / SVG dimension helpers (no WordPress runtime).
 *
 * @package Oli_PDF
 */

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

if ( ! defined( 'OLI_PDF_PLUGIN_DIR' ) ) {
	define( 'OLI_PDF_PLUGIN_DIR', dirname( __DIR__ ) . '/' );
}

$oli_pdf_test_uploads = sys_get_temp_dir() . '/oli-pdf-path-test';

if ( ! function_exists( 'trailingslashit' ) ) {
	/**
	 * @param string $value Path.
	 * @return string
	 */
	function trailingslashit( $value ) {
		return rtrim( (string) $value, '/\\' ) . '/';
	}
}

if ( ! function_exists( 'esc_url_raw' ) ) {
	/**
	 * @param string $url URL.
	 * @return string
	 */
	function esc_url_raw( $url ) {
		return (string) $url;
	}
}

if ( ! function_exists( 'attachment_url_to_postid' ) ) {
	/**
	 * @param string $url URL.
	 * @return int
	 */
	function attachment_url_to_postid( $url ) {
		return 0;
	}
}

if ( ! function_exists( 'get_attached_file' ) ) {
	/**
	 * @param int $id Attachment ID.
	 * @return string
	 */
	function get_attached_file( $id ) {
		global $oli_pdf_test_uploads;
		if ( 2 === (int) $id ) {
			return dirname( $oli_pdf_test_uploads ) . '/wp-config.php';
		}
		if ( 1 === (int) $id ) {
			return $oli_pdf_test_uploads . '/logo.png';
		}
		return '';
	}
}

if ( ! function_exists( 'wp_upload_dir' ) ) {
	/**
	 * @return array
	 */
	function wp_upload_dir() {
		global $oli_pdf_test_uploads;
		return array(
			'basedir' => $oli_pdf_test_uploads,
			'baseurl' => 'https://example.test/wp-content/uploads',
		);
	}
}

$oli_pdf_autoload = dirname( __DIR__ ) . '/vendor/autoload.php';
if ( is_readable( $oli_pdf_autoload ) ) {
	require_once $oli_pdf_autoload;
}

require_once dirname( __DIR__ ) . '/includes/class-oli-pdf-generator.php';

$failures = 0;

/**
 * @param bool   $ok      Condition.
 * @param string $message Label.
 */
function oli_pdf_logo_assert( $ok, $message ) {
	global $failures;
	if ( $ok ) {
		echo "PASS  $message\n";
		return;
	}
	++$failures;
	echo "FAIL  $message\n";
}

$small = \Oli_PDF\Generator::fit_logo_box( 40, 20, 200, 80 );
oli_pdf_logo_assert( array( 40, 20 ) === $small, 'Small landscape logo is not upscaled' );

$wide = \Oli_PDF\Generator::fit_logo_box( 400, 80, 200, 80 );
oli_pdf_logo_assert( array( 200, 40 ) === $wide, 'Wide logo fits 200x80 by width and keeps ratio' );

$tall = \Oli_PDF\Generator::fit_logo_box( 80, 400, 150, 80 );
oli_pdf_logo_assert( array( 16, 80 ) === $tall, 'Portrait logo fits 150x80 by height and keeps ratio' );

$square = \Oli_PDF\Generator::fit_logo_box( 500, 500, 150, 80 );
oli_pdf_logo_assert( array( 80, 80 ) === $square, 'Square logo fits the shorter box edge' );

$giant = \Oli_PDF\Generator::fit_logo_box( 4000, 1600, 200, 80 );
oli_pdf_logo_assert( array( 200, 80 ) === $giant, 'Giant 2.5:1 logo fills the 200x80 invoice box' );

$attr = \Oli_PDF\Generator::read_svg_dimensions( '<svg width="300" height="100"></svg>' );
oli_pdf_logo_assert( array( 300.0, 100.0 ) === $attr, 'SVG width/height attributes' );

$view = \Oli_PDF\Generator::read_svg_dimensions( '<svg viewBox="0 0 120 90"></svg>' );
oli_pdf_logo_assert( array( 120.0, 90.0 ) === $view, 'SVG viewBox dimensions' );

$invoice_box = \Oli_PDF\Generator::get_logo_max_box( 'invoice' );
$slip_box    = \Oli_PDF\Generator::get_logo_max_box( 'packing-slip' );
$note_box    = \Oli_PDF\Generator::get_logo_max_box( 'delivery-note' );
$label_box   = \Oli_PDF\Generator::get_logo_max_box( 'shipping-label' );
oli_pdf_logo_assert( array( 200, 80 ) === $invoice_box, 'Invoice box is 200x80' );
oli_pdf_logo_assert( array( 150, 80 ) === $slip_box && $slip_box === $note_box && $slip_box === $label_box, 'Packing slip, delivery note, and label boxes are 150x80' );

$oli_pdf_path_root = dirname( $oli_pdf_test_uploads );
if ( ! is_dir( $oli_pdf_test_uploads ) ) {
	mkdir( $oli_pdf_test_uploads, 0777, true );
}
file_put_contents( $oli_pdf_path_root . '/wp-config.php', '<?php // secret' );
file_put_contents( $oli_pdf_test_uploads . '/logo.png', 'png' );
file_put_contents( $oli_pdf_test_uploads . '/evil.php', '<?php echo 1;' );

$forbidden = \Oli_PDF\Generator::resolve_local_image_path( 'https://example.test/wp-content/uploads/../../wp-config.php' );
oli_pdf_logo_assert( '' === $forbidden, 'Traversal uploads/../../wp-config.php is rejected' );

$php_in_uploads = \Oli_PDF\Generator::resolve_local_image_path( 'https://example.test/wp-content/uploads/evil.php' );
oli_pdf_logo_assert( '' === $php_in_uploads, 'Non-image extension in uploads is rejected' );

$outside_id = \Oli_PDF\Generator::resolve_local_image_path( 2 );
oli_pdf_logo_assert( '' === $outside_id, 'Attachment path outside uploads/plugin is rejected' );

$allowed = \Oli_PDF\Generator::resolve_local_image_path( 'https://example.test/wp-content/uploads/logo.png' );
oli_pdf_logo_assert( realpath( $oli_pdf_test_uploads . '/logo.png' ) === $allowed, 'Safe uploads PNG is accepted' );

if ( $failures > 0 ) {
	echo "\n$failures logo-fit test(s) failed.\n";
	exit( 1 );
}

echo "\nAll logo-fit tests passed.\n";
exit( 0 );
