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
	'rate-limiting.php',
	'admin-dashboard.php',
	'agenda.php',
	'optimizations.php',
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

/**
 * Helper to retrieve the custom login page URL.
 *
 * @return string
 */
function cgt_get_login_page_url() {
	static $cached_url = null;

	if ( null !== $cached_url ) {
		return $cached_url;
	}

	$login_page = get_page_by_path( 'connexion' );
	if ( $login_page && 'publish' === get_post_status( $login_page ) ) {
		$cached_url = get_permalink( $login_page );
		return $cached_url;
	}

	$by_template = get_posts(
		array(
			'post_type'      => 'page',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'no_found_rows'  => true,
			'fields'         => 'ids',
			'meta_key'       => '_wp_page_template',
			'meta_value'     => 'page-connexion.php',
		)
	);

	if ( ! empty( $by_template ) ) {
		$cached_url = get_permalink( $by_template[0] );
		return $cached_url;
	}

	$cached_url = '';
	return $cached_url;
}

/**
 * Redirect default wp-login.php access to the custom login page.
 */
function cgt_redirect_default_login() {
	$requested = $_SERVER['REQUEST_URI'] ?? '';
	if ( false === strpos( $requested, 'wp-login.php' ) ) {
		return;
	}

	if ( isset( $_SERVER['REQUEST_METHOD'] ) && 'POST' === strtoupper( $_SERVER['REQUEST_METHOD'] ) ) {
		return;
	}

	$action = isset( $_GET['action'] ) ? sanitize_key( wp_unslash( $_GET['action'] ) ) : '';
	if ( in_array(
		$action,
		array( 'logout', 'lostpassword', 'retrievepassword', 'rp', 'resetpass', 'confirm_admin_email', 'postpass' ),
		true
	) ) {
		return;
	}

	$login_page = cgt_get_login_page_url();
	if ( $login_page ) {
		$args = array();
		if ( ! empty( $_GET['redirect_to'] ) ) {
			$args['redirect_to'] = rawurlencode( wp_unslash( $_GET['redirect_to'] ) );
		}
		if ( ! empty( $_GET['reauth'] ) ) {
			$args['reauth'] = '1';
		}
		if ( $action ) {
			$args['action'] = $action;
		}
		if ( ! empty( $_GET['loggedout'] ) ) {
			$args['loggedout'] = 'true';
		}
		if ( ! empty( $_GET['checkemail'] ) ) {
			$args['checkemail'] = sanitize_key( wp_unslash( $_GET['checkemail'] ) );
		}

		if ( $args ) {
			$login_page = add_query_arg( $args, $login_page );
		}

		wp_safe_redirect( $login_page );
		exit;
	}
}
add_action( 'login_init', 'cgt_redirect_default_login', 1 );

/**
 * Filters login related URLs to point to the custom page.
 *
 * @param string $url     The URL to filter.
 * @param string $redirect Redirect destination.
 * @param bool   $force_reauth Whether re-authentication is being forced.
 *
 * @return string
 */
function cgt_filter_login_url( $url, $redirect, $force_reauth ) {
	$login_page = cgt_get_login_page_url();
	if ( ! $login_page ) {
		return $url;
	}

	$args = array();
	if ( ! empty( $redirect ) ) {
		$args['redirect_to'] = rawurlencode( $redirect );
	}
	if ( $force_reauth ) {
		$args['reauth'] = '1';
	}

	return $args ? add_query_arg( $args, $login_page ) : $login_page;
}
add_filter( 'login_url', 'cgt_filter_login_url', 10, 3 );

/**
 * Ensure logout url continues to use default behaviour but with redirect back to custom login page.
 *
 * @param string $logout_url Logout URL.
 * @param string $redirect   Redirect destination.
 *
 * @return string
 */
function cgt_filter_logout_url( $logout_url, $redirect ) {
 $login_page = cgt_get_login_page_url();
 if ( ! $login_page ) {
  return $logout_url;
 }

 return add_query_arg(
  'redirect_to',
  rawurlencode( $login_page ),
  $logout_url
 );
}
add_filter( 'logout_url', 'cgt_filter_logout_url', 10, 2 );

/**
 * Filter register and lost password URLs to the custom page.
 */
function cgt_filter_register_url( $register_url ) {
	$login_page = cgt_get_login_page_url();
	if ( ! $login_page ) {
		return $register_url;
	}

	return add_query_arg( 'action', 'register', $login_page );
}
add_filter( 'register_url', 'cgt_filter_register_url' );

function cgt_filter_lostpassword_url( $lostpassword_url, $redirect ) {
	$login_page = cgt_get_login_page_url();
	if ( ! $login_page ) {
		return $lostpassword_url;
	}

	$args = array( 'action' => 'lostpassword' );
	if ( ! empty( $redirect ) ) {
		$args['redirect_to'] = rawurlencode( $redirect );
	}

	return add_query_arg( $args, $login_page );
}
add_filter( 'lostpassword_url', 'cgt_filter_lostpassword_url', 10, 2 );

/**
 * Redirect failed login attempts to the custom page with an error flag.
 *
 * @param string $username Entered username.
 */
function cgt_handle_login_failed( $username ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
	$login_page = cgt_get_login_page_url();
	if ( ! $login_page ) {
		return;
	}

	$login_page = add_query_arg( 'login', 'failed', $login_page );
	wp_safe_redirect( $login_page );
	exit;
}
add_action( 'wp_login_failed', 'cgt_handle_login_failed' );

/**
 * Handle empty credentials by redirecting back with a flag.
 *
 * @param null|WP_User|WP_Error $user     User.
 * @param string                $username Entered username.
 * @param string                $password Entered password.
 *
 * @return null|WP_User|WP_Error
 */
function cgt_handle_empty_login( $user, $username, $password ) {
	if ( ! empty( $username ) && ! empty( $password ) ) {
		return $user;
	}

	$login_page = cgt_get_login_page_url();
	if ( $login_page ) {
		$login_page = add_query_arg( 'login', 'empty', $login_page );
		wp_safe_redirect( $login_page );
		exit;
	}

	return $user;
}
add_filter( 'authenticate', 'cgt_handle_empty_login', 30, 3 );

/**
 * Redirect on successful login to espace adhérent by défaut.
 *
 * @param string           $redirect_to           Default redirect.
 * @param string           $requested_redirect_to Requested redirect.
 * @param WP_User|WP_Error $user                  User object.
 *
 * @return string
 */
function cgt_login_redirect( $redirect_to, $requested_redirect_to, $user ) {
	if ( is_wp_error( $user ) || ! $user ) {
		return $redirect_to;
	}

	if ( ! empty( $requested_redirect_to ) ) {
		return $requested_redirect_to;
	}

	return home_url( '/espace-adherent' );
}
add_filter( 'login_redirect', 'cgt_login_redirect', 10, 3 );

/**
 * Disable comments globally (front + back office).
 */
add_action( 'init', 'cgt_disable_comments_support', 20 );
function cgt_disable_comments_support() {
	foreach ( get_post_types() as $post_type ) {
		if ( post_type_supports( $post_type, 'comments' ) ) {
			remove_post_type_support( $post_type, 'comments' );
			remove_post_type_support( $post_type, 'trackbacks' );
		}
	}
}

add_filter( 'comments_open', '__return_false', 20, 2 );
add_filter( 'pings_open', '__return_false', 20, 2 );
add_filter( 'comments_array', '__return_empty_array', 20, 2 );

add_action( 'admin_menu', 'cgt_disable_comments_admin_menu' );
function cgt_disable_comments_admin_menu() {
	remove_menu_page( 'edit-comments.php' );
}

add_action( 'admin_init', 'cgt_disable_comments_admin_redirect' );
function cgt_disable_comments_admin_redirect() {
	global $pagenow;
	if ( 'edit-comments.php' === $pagenow ) {
		wp_safe_redirect( admin_url() );
		exit;
	}
}

add_action( 'wp_before_admin_bar_render', 'cgt_disable_comments_admin_bar' );
function cgt_disable_comments_admin_bar() {
	if ( is_admin_bar_showing() ) {
		global $wp_admin_bar;
		$wp_admin_bar->remove_menu( 'comments' );
	}
}
