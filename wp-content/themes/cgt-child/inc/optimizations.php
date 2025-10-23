<?php
/**
 * Front-end optimisations.
 *
 * @package CGT_Child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Remove emoji scripts/styles.
 */
function cgt_disable_emojis() {
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
	add_filter(
		'tiny_mce_plugins',
		function ( $plugins ) {
			return is_array( $plugins ) ? array_diff( $plugins, array( 'wpemoji' ) ) : array();
		}
	);
}
add_action( 'init', 'cgt_disable_emojis' );

/**
 * Remove oEmbed and REST discovery links if not needed.
 */
function cgt_cleanup_head() {
	remove_action( 'wp_head', 'wp_shortlink_wp_head', 10 );
	remove_action( 'wp_head', 'rest_output_link_wp_head', 10 );
	remove_action( 'template_redirect', 'rest_output_link_header', 11 );
	remove_action( 'wp_head', 'wp_oembed_add_discovery_links', 10 );
	remove_action( 'wp_head', 'wlwmanifest_link' );
	remove_action( 'wp_head', 'rsd_link' );
}
add_action( 'init', 'cgt_cleanup_head' );

/**
 * Dequeue block library styles on front-end (we rely on theme styles).
 */
function cgt_dequeue_block_library() {
	if ( ! is_admin() ) {
		wp_dequeue_style( 'wp-block-library' );
		wp_dequeue_style( 'wp-block-library-theme' );
		wp_dequeue_style( 'classic-theme-styles' );
	}
}
add_action( 'wp_enqueue_scripts', 'cgt_dequeue_block_library', 999 );

/**
 * Remove query strings from static resources for better caching.
 *
 * @param string $src Source URL.
 *
 * @return string
 */
function cgt_remove_asset_query_strings( $src ) {
	if ( strpos( $src, '?ver=' ) ) {
		$src = remove_query_arg( 'ver', $src );
	}
	return $src;
}
add_filter( 'script_loader_src', 'cgt_remove_asset_query_strings', 15 );
add_filter( 'style_loader_src', 'cgt_remove_asset_query_strings', 15 );

/**
 * Force lazy loading on standard images if missing.
 *
 * @param string $value Current loading value.
 *
 * @return string
 */
function cgt_force_lazy_images( $value ) {
	return $value ?: 'lazy';
}
add_filter( 'wp_lazy_loading_enabled', '__return_true' );
add_filter( 'wp_lazy_loading_enabled', '__return_true', 10, 2 );
add_filter( 'wp_img_tag_add_loading_attr', 'cgt_force_lazy_images' );
add_filter( 'wp_get_attachment_image_attributes', function ( $attr ) {
	if ( empty( $attr['loading'] ) ) {
		$attr['loading'] = 'lazy';
	}
	return $attr;
} );

/**
 * Minimise heartbeat frequency on front-end.
 */
function cgt_optimize_heartbeat( $settings ) {
	if ( ! is_admin() ) {
		$settings['interval'] = 60;
	}
	return $settings;
}
add_filter( 'heartbeat_settings', 'cgt_optimize_heartbeat' );
