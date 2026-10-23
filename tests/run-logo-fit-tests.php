<?php
/**
 * Logo fit / SVG dimension helpers (no WordPress runtime).
 *
 * @package Oli_PDF
 */

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
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

if ( $failures > 0 ) {
	echo "\n$failures logo-fit test(s) failed.\n";
	exit( 1 );
}

echo "\nAll logo-fit tests passed.\n";
exit( 0 );
