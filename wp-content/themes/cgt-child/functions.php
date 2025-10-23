<?php
/**
 * Functions and definitions for the CGT child theme.
 *
 * @package CGT_Child
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'CGT_CHILD_VERSION' ) ) {
	define( 'CGT_CHILD_VERSION', '1.0.0' );
}

/**
 * Load inc files.
 */
$cgt_inc_files = array(
	'cpt.php',
	'taxonomies.php',
	'roles.php',
	'shortcodes.php',
	'seo.php',
	'templating.php',
	'security.php',
	'adhesion.php',
);

foreach ( $cgt_inc_files as $cgt_inc_file ) {
	$path = get_stylesheet_directory() . '/inc/' . $cgt_inc_file;
	if ( file_exists( $path ) ) {
		require_once $path;
	}
}

/**
 * Setup theme supports and menus.
 */
add_action(
	'after_setup_theme',
	function () {
		load_child_theme_textdomain( 'cgt', get_stylesheet_directory() . '/languages' );

		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support(
			'html5',
			array(
				'search-form',
				'comment-form',
				'comment-list',
				'gallery',
				'caption',
				'script',
				'style',
			)
		);
		add_theme_support( 'editor-styles' );
		add_editor_style( 'assets/css/cgt.css' );

		register_nav_menus(
			array(
				'primary' => __( 'Menu principal', 'cgt' ),
				'footer'  => __( 'Menu pied de page', 'cgt' ),
			)
		);
	}
);

/**
 * Enqueue styles and scripts.
 */
add_action(
	'wp_enqueue_scripts',
	function () {
		$parent_version = wp_get_theme( get_template() )->get( 'Version' );
		wp_enqueue_style(
			'cgt-parent',
			get_template_directory_uri() . '/style.css',
			array(),
			$parent_version
		);

		wp_enqueue_style(
			'cgt-child',
			get_stylesheet_directory_uri() . '/assets/css/cgt.css',
			array( 'cgt-parent' ),
			CGT_CHILD_VERSION
		);

		wp_enqueue_script(
			'cgt',
			get_stylesheet_directory_uri() . '/assets/js/cgt.js',
			array(),
			CGT_CHILD_VERSION,
			true
		);

		// Charger les styles et scripts de la page de connexion
		if ( is_page_template( 'page-connexion.php' ) ) {
			wp_enqueue_style(
				'cgt-connexion',
				get_stylesheet_directory_uri() . '/assets/css/connexion.css',
				array( 'cgt-child' ),
				CGT_CHILD_VERSION
			);

			wp_enqueue_script(
				'cgt-connexion',
				get_stylesheet_directory_uri() . '/assets/js/connexion.js',
				array(),
				CGT_CHILD_VERSION,
				true
			);
		}
	}
);

/**
 * Register pattern category.
 */
add_action(
	'init',
	function () {
		if ( function_exists( 'register_block_pattern_category' ) ) {
			register_block_pattern_category(
				'cgt',
				array(
					'label' => __( 'CGT', 'cgt' ),
				)
			);
		}
	}
);

/**
 * Run activation tasks when theme is switched.
 */
add_action( 'after_switch_theme', 'cgt_after_switch_theme' );
