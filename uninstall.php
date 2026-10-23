<?php
/**
 * Uninstall cleanup.
 *
 * @package Oli_PDF
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * Delete every option whose name starts with a prefix.
 *
 * @param string $prefix Option prefix.
 */
function oli_pdf_uninstall_delete_prefixed_options( $prefix ) {
	global $wpdb;

	if ( ! $prefix || ! isset( $wpdb ) ) {
		return;
	}

	$like = $wpdb->esc_like( $prefix ) . '%';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- uninstall bulk delete.
	$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", $like ) );

	if ( ! is_multisite() ) {
		return;
	}

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- uninstall bulk delete.
	$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->sitemeta} WHERE meta_key LIKE %s", $like ) );
}

/**
 * Remove the logo cache directory if it is inside uploads.
 */
function oli_pdf_uninstall_delete_logo_cache() {
	$uploads = wp_upload_dir();
	if ( empty( $uploads['basedir'] ) ) {
		return;
	}

	$dir       = trailingslashit( $uploads['basedir'] ) . 'oli-pdf-logos';
	$real_dir  = realpath( $dir );
	$real_root = realpath( $uploads['basedir'] );
	if ( ! $real_dir || ! $real_root || 'oli-pdf-logos' !== basename( $real_dir ) ) {
		return;
	}

	$root_prefix = rtrim( str_replace( '\\', '/', $real_root ), '/' );
	$dir_norm    = str_replace( '\\', '/', $real_dir );
	if ( 0 !== strpos( $dir_norm, $root_prefix . '/' ) ) {
		return;
	}

	$files = glob( trailingslashit( $real_dir ) . '*' );
	if ( $files ) {
		foreach ( $files as $file ) {
			if ( is_file( $file ) ) {
				wp_delete_file( $file );
			}
		}
	}

	rmdir( $real_dir ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_rmdir -- empty cache dir after file delete.
}

oli_pdf_uninstall_delete_prefixed_options( 'oli_pdf_' );
oli_pdf_uninstall_delete_prefixed_options( 'olipdf_' );
oli_pdf_uninstall_delete_logo_cache();
