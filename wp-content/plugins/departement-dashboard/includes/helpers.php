<?php

namespace CGT\Dashboard;

defined( 'ABSPATH' ) || exit;

/**
 * Simple view renderer.
 *
 * @param string $template Template slug.
 * @param array  $context  Data to expose.
 */
function render( $template, $context = array() ) {
	$file = trailingslashit( CGT_DD_PATH . 'templates' ) . $template . '.php';
	if ( ! file_exists( $file ) ) {
		status_header( 404 );
		wp_die( esc_html__( 'Template introuvable.', 'departement-dashboard' ) );
	}

	$context = apply_filters( 'cgt_dd_view_context', $context, $template );
	extract( $context, EXTR_SKIP ); // phpcs:ignore WordPress.PHP.DontExtract
	require $file;
}

/**
 * Ensure mandats directory exists.
 *
 * @return string
 */
function ensure_mandat_dir() {
	$upload_dir = wp_upload_dir();
	$mandat_dir = trailingslashit( $upload_dir['basedir'] ) . 'mandats';

	if ( ! file_exists( $mandat_dir ) ) {
		wp_mkdir_p( $mandat_dir );
	}

	return $mandat_dir;
}

/**
 * Array helper: sanitize text array.
 *
 * @param array $data Array of strings.
 * @return array
 */
function sanitize_text_array( $data ) {
	return array_map(
		static function( $value ) {
			return sanitize_text_field( wp_unslash( $value ) );
		},
		(array) $data
	);
}
