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

if ( ! function_exists( 'cgt_child_upload_url' ) ) {
	function cgt_child_upload_url( $relative_path ) {
		$uploads = wp_get_upload_dir();
		if ( empty( $uploads['baseurl'] ) ) {
			return '';
		}

		return trailingslashit( $uploads['baseurl'] ) . ltrim( $relative_path, '/' );
	}
}

if ( ! defined( 'CGT_CHILD_DEFAULT_LOGO_URL' ) ) {
	define( 'CGT_CHILD_DEFAULT_LOGO_URL', esc_url_raw( cgt_child_upload_url( '2025/10/logo2.png' ) ) );
}

if ( ! defined( 'CGT_CHILD_DEFAULT_FAVICON_URL' ) ) {
	define( 'CGT_CHILD_DEFAULT_FAVICON_URL', esc_url_raw( cgt_child_upload_url( '2025/10/telechargement.png' ) ) );
}

if ( ! defined( 'CGT_CHILD_DEFAULT_IMAGE_URL' ) ) {
	define( 'CGT_CHILD_DEFAULT_IMAGE_URL', esc_url_raw( cgt_child_upload_url( '2025/11/ChatGPT-Image-4-nov.-2025-10_49_55.png' ) ) );
}

if ( ! defined( 'CGT_CHILD_DEFAULT_TRACT_IMAGE_URL' ) ) {
	define( 'CGT_CHILD_DEFAULT_TRACT_IMAGE_URL', esc_url_raw( cgt_child_upload_url( '2025/11/ChatGPT-Image-4-nov.-2025-11_00_16.png' ) ) );
}

// Charge l'autoloader Dompdf si disponible.
$cgt_child_dompdf_autoloaders = array(
	get_stylesheet_directory() . '/vendor/dompdf/autoload.inc.php',
	get_stylesheet_directory() . '/vendor/dompdf/src/Autoloader.php',
);

foreach ( $cgt_child_dompdf_autoloaders as $dompdf_loader ) {
	if ( file_exists( $dompdf_loader ) ) {
		require_once $dompdf_loader;
		if ( class_exists( '\Dompdf\Autoloader' ) ) {
			\Dompdf\Autoloader::register();
		}
		break;
	}
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
	'account-request.php',
	'rate-limiting.php',
	'pdf-validation.php',
	'autoload-audit.php',
	'media-library-modern.php',
	'admin-dashboard.php',
	'agenda.php',
	'brevo.php',
	'optimizations.php',
	'home-slider.php',
	'newsletter.php',
	'library-sync.php',
	'admin-post-creation.php',
	'admin-menu-reorganization.php',
	'content-reset.php',
	'enquetes.php',
	'assistance-submissions.php',
);

foreach ( $cgt_inc_files as $cgt_inc_file ) {
	$path = get_stylesheet_directory() . '/inc/' . $cgt_inc_file;
	if ( file_exists( $path ) ) {
		require_once $path;
	}
}

/**
 * Retourne l'URL du logo fédéral configuré.
 *
 * @return string
 */
function cgt_child_get_logo_url() {
	$logo_url = get_theme_mod( 'cgt_logo_url', CGT_CHILD_DEFAULT_LOGO_URL );

	/**
	 * Filtre l'URL du logo fédéral.
	 *
	 * @param string $logo_url URL du logo.
	 */
	$logo_url = apply_filters( 'cgt_child_logo_url', $logo_url );

	return esc_url_raw( $logo_url );
}

/**
 * Retourne l'URL du favicon fédéral configuré.
 *
 * @return string
 */
function cgt_child_get_favicon_url() {
	$favicon_url = get_theme_mod( 'cgt_favicon_url', CGT_CHILD_DEFAULT_FAVICON_URL );

	/**
	 * Filtre l'URL du favicon fédéral.
	 *
	 * @param string $favicon_url URL du favicon.
	 */
	$favicon_url = apply_filters( 'cgt_child_favicon_url', $favicon_url );

	return esc_url_raw( $favicon_url );
}

/**
 * Injecte le favicon fédéral dans l'ensemble des interfaces publiques et privées.
 */
function cgt_child_print_favicon() {
	$favicon_url = cgt_child_get_favicon_url();

	if ( empty( $favicon_url ) ) {
		return;
	}

	printf(
		'<link rel="icon" href="%1$s" sizes="32x32" />' . PHP_EOL .
		'<link rel="apple-touch-icon" href="%1$s" />' . PHP_EOL,
		esc_url( $favicon_url )
	);
}

add_action( 'wp_head', 'cgt_child_print_favicon', 1 );
add_action( 'admin_head', 'cgt_child_print_favicon', 1 );
add_action( 'login_head', 'cgt_child_print_favicon', 1 );

/**
 * Retourne l'URL de l'image de remplacement.
 *
 * @return string
 */
function cgt_child_get_default_image_url() {
	$default_image = get_theme_mod( 'cgt_default_image_url', CGT_CHILD_DEFAULT_IMAGE_URL );

	/**
	 * Filtre l'URL de l'image de remplacement.
	 *
	 * @param string $default_image URL de l'image.
	 */
	$default_image = apply_filters( 'cgt_child_default_image_url', $default_image );

	return esc_url_raw( $default_image );
}

/**
 * Retourne l'URL de l'image de remplacement pour les tracts.
 *
 * @return string
 */
function cgt_child_get_default_tract_image_url() {
	$default_image = get_theme_mod( 'cgt_default_tract_image_url', CGT_CHILD_DEFAULT_TRACT_IMAGE_URL );

	/**
	 * Filtre l'URL de l'image de remplacement pour les tracts.
	 *
	 * @param string $default_image URL de l'image.
	 */
	$default_image = apply_filters( 'cgt_child_default_tract_image_url', $default_image );

	return esc_url_raw( $default_image );
}

/**
 * Récupère le HTML de l'image mise en avant ou de l'image de remplacement.
 *
 * @param int         $post_id Post ID.
 * @param string|int  $size    Image size.
 * @param array       $attr    Attributes.
 * @return string
 */
function cgt_child_get_post_thumbnail_html( $post_id = 0, $size = 'medium', $attr = array() ) {
	$post_id = $post_id ? absint( $post_id ) : get_the_ID();

	if ( has_post_thumbnail( $post_id ) ) {
		return get_the_post_thumbnail( $post_id, $size, $attr );
	}

	$post_type = get_post_type( $post_id );
	$fallback  = ( 'tracts' === $post_type )
		? cgt_child_get_default_tract_image_url()
		: cgt_child_get_default_image_url();

	if ( empty( $fallback ) ) {
		return '';
	}

	$attr = wp_parse_args(
		$attr,
		array(
			'alt'     => get_the_title( $post_id ),
			'loading' => 'lazy',
		)
	);

	$attributes = '';
	foreach ( $attr as $key => $value ) {
		if ( '' === $value || false === $value ) {
			continue;
		}
		$attributes .= sprintf( ' %s="%s"', esc_attr( $key ), esc_attr( $value ) );
	}

	return sprintf( '<img src="%1$s"%2$s />', esc_url( $fallback ), $attributes );
}

add_action(
	'init',
	function () {
		if ( 'FSETUD' !== get_option( 'blogname' ) ) {
			update_option( 'blogname', 'FSETUD' );
		}
	}
);

// Clear cached taxonomies when terms change.
add_action( 'edited_term', 'cgt_child_flush_term_cache' );
add_action( 'created_term', 'cgt_child_flush_term_cache' );
add_action( 'delete_term', 'cgt_child_flush_term_cache' );

function cgt_child_flush_term_cache() {
    foreach ( array( 'cgt_classes_terms', 'cgt_branch_terms', 'cgt_category_terms' ) as $key ) {
        wp_cache_delete( $key );
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
		$parent       = wp_get_theme( get_template() );
		$parent_ver   = $parent ? $parent->get( 'Version' ) : null;

		wp_enqueue_style(
			'cgt-fonts',
			'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap',
			array(),
			null
		);

		wp_enqueue_style(
			'cgt-parent',
			get_template_directory_uri() . '/style.css',
			array( 'cgt-fonts' ),
			$parent_ver
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

		// Charger les styles et scripts de la page d'accueil
		if ( is_front_page() ) {
			wp_enqueue_style(
				'cgt-home-join',
				get_stylesheet_directory_uri() . '/assets/css/home-join-section.css',
				array( 'cgt-child' ),
				CGT_CHILD_VERSION
			);

			wp_enqueue_script(
				'cgt-home-slider',
				get_stylesheet_directory_uri() . '/assets/js/home-slider.js',
				array(),
				CGT_CHILD_VERSION,
				true
			);

			// Localize script for newsletter AJAX
			wp_localize_script(
				'cgt-home-slider',
				'cgtNewsletterData',
				array(
					'ajaxUrl' => admin_url( 'admin-ajax.php' ),
					'nonce'   => wp_create_nonce( 'cgt_newsletter_nonce' ),
				)
			);
		}

		// Charger les styles de la page archive des tracts et de la page d'accueil
		if ( is_post_type_archive( 'tracts' ) || is_tax( 'branche' ) || is_front_page() ) {
			wp_enqueue_style(
				'cgt-tracts-archive',
				get_stylesheet_directory_uri() . '/assets/css/tracts-archive.css',
				array( 'cgt-child' ),
				CGT_CHILD_VERSION
			);
		}

		// Charger les styles de la page Médiathèque/Bibliothèque
		if ( is_page( 'mediatheque' ) || is_page_template( 'templates/page-bibliotheque.php' ) ) {
			wp_enqueue_style(
				'cgt-library',
				get_stylesheet_directory_uri() . '/assets/css/library.css',
				array( 'cgt-child' ),
				CGT_CHILD_VERSION
			);
		}

		// Charger les styles de la page Contact
		if ( is_page( 'contact' ) || is_page_template( 'page-contact.php' ) || is_page_template( 'templates/page-contact.php' ) ) {
			wp_enqueue_style(
				'cgt-contact',
				get_stylesheet_directory_uri() . '/assets/css/contact.css',
				array( 'cgt-child' ),
				CGT_CHILD_VERSION
			);
		}

		// Charger les styles de la page Fédération
		if ( is_page( 'la-federation' ) || is_page_template( 'page-la-federation.php' ) || is_page_template( 'templates/page-federation.php' ) ) {
			wp_enqueue_style(
				'cgt-federation',
				get_stylesheet_directory_uri() . '/assets/css/federation.css',
				array( 'cgt-child' ),
				CGT_CHILD_VERSION
			);
		}

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

		// Charger les styles et scripts de la page Proposer un article
		global $post;
		if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'cgt_submit_article' ) ) {
			wp_enqueue_style(
				'cgt-submit-article',
				get_stylesheet_directory_uri() . '/assets/css/submit-article.css',
				array( 'cgt-child' ),
				CGT_CHILD_VERSION
			);

			wp_enqueue_script(
				'cgt-submit-article',
				get_stylesheet_directory_uri() . '/assets/js/submit-article.js',
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

	// ✅ Ne pas rediriger les gestionnaires et assistances (ils ont leurs propres espaces)
	if ( isset( $user->roles ) && is_array( $user->roles ) ) {
		if ( in_array( 'gestionnaire', $user->roles, true ) || in_array( 'assistance', $user->roles, true ) ) {
			return $redirect_to; // Laisser la fonction cgt_custom_roles_login_redirect gérer la redirection
		}
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

/**
 * Cleanup helper: remove all standard articles, tracts, and events once.
 */
add_action(
	'init',
	function () {
		if ( get_option( 'cgt_cleanup_articles_tracts_events_done' ) ) {
			return;
		}

		$post_types = array( 'post', 'tracts', 'cgt_agenda' );

		foreach ( $post_types as $post_type ) {
			$posts = get_posts(
				array(
					'post_type'      => $post_type,
					'post_status'    => 'any',
					'posts_per_page' => -1,
					'fields'         => 'ids',
				)
			);

			if ( empty( $posts ) ) {
				continue;
			}

			foreach ( $posts as $post_id ) {
				wp_delete_post( $post_id, true );
			}
		}

		update_option( 'cgt_cleanup_articles_tracts_events_done', current_time( 'mysql' ) );
	},
	20
);

/**
 * Rename legacy "Matériels" menu item to "Menu" in the primary navigation.
 */
add_filter(
	'wp_nav_menu_objects',
	function ( $items, $args ) {
		if ( empty( $items ) || ! isset( $args->theme_location ) || 'primary' !== $args->theme_location ) {
			return $items;
		}

		foreach ( $items as $item ) {
			if ( isset( $item->post_name ) && 'materiels' === $item->post_name ) {
				$item->title = __( 'Ressources', 'cgt' );
			} elseif ( isset( $item->title ) && 'Matériels' === $item->title ) {
				$item->title = __( 'Ressources', 'cgt' );
			} elseif ( isset( $item->title ) && 'Menu' === $item->title ) {
				$item->title = __( 'Ressources', 'cgt' );
			}
		}

		return $items;
	},
	10,
	2
);

/**
 * Enqueue modern styles and scripts for espace adhérent page.
 */
add_action( 'wp_enqueue_scripts', 'cgt_enqueue_espace_adherent_styles', 20 );
function cgt_enqueue_espace_adherent_styles() {
	if ( is_page_template( 'page-espace-adherent.php' ) || is_page( 'espace-adherent' ) ) {
		wp_enqueue_style(
			'cgt-espace-adherent',
			get_stylesheet_directory_uri() . '/assets/css/espace-adherent.css',
			array( 'cgt-child' ),
			CGT_CHILD_VERSION
		);

		wp_enqueue_script(
			'cgt-espace-adherent',
			get_stylesheet_directory_uri() . '/assets/js/espace-adherent.js',
			array(),
			CGT_CHILD_VERSION,
			true
		);
	}

	if ( is_page_template( 'templates/page-classes.php' ) ) {
		wp_enqueue_style(
			'cgt-classes',
			get_stylesheet_directory_uri() . '/assets/css/classes.css',
			array( 'cgt-child' ),
			CGT_CHILD_VERSION
		);
	}

	// Charger les styles de la page single événement
	if ( is_singular( 'cgt_agenda' ) ) {
		wp_enqueue_style(
			'cgt-single-event',
			get_stylesheet_directory_uri() . '/assets/css/single-event.css',
			array( 'cgt-child' ),
			CGT_CHILD_VERSION
		);
	}
}

/**
 * Admin: modernise Media Library appearance.
 */
add_action(
	'admin_enqueue_scripts',
	function ( $hook_suffix ) {
		if ( in_array( $hook_suffix, array( 'upload.php', 'media-new.php' ), true ) ) {
			wp_enqueue_style(
				'cgt-admin-media',
				get_stylesheet_directory_uri() . '/assets/css/admin-media.css',
				array(),
				CGT_CHILD_VERSION
			);
			wp_enqueue_script(
				'cgt-admin-media',
				get_stylesheet_directory_uri() . '/assets/js/admin-media.js',
				array(),
				CGT_CHILD_VERSION,
				true
			);
			wp_localize_script(
				'cgt-admin-media',
				'cgtMediaFilter',
				array(
					'i18nEmptyNotice' => __( 'Aucun média ne correspond à ce filtre.', 'cgt' ),
				)
			);
		}
	}
);

add_action(
	'load-upload.php',
	function () {
		if ( isset( $_GET['mode'] ) && 'grid' === $_GET['mode'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			wp_safe_redirect( remove_query_arg( 'mode' ) );
			exit;
		}
	}
);

/**
 * Add security headers to HTTP responses
 */
add_action( 'send_headers', 'cgt_add_security_headers' );
function cgt_add_security_headers() {
	// Ne pas ajouter les headers dans l'admin pour éviter les conflits
	if ( is_admin() ) {
		return;
	}

	// Protection contre le MIME type sniffing
	header( 'X-Content-Type-Options: nosniff' );

	// Protection contre le clickjacking
	header( 'X-Frame-Options: SAMEORIGIN' );

	// Protection XSS pour les anciens navigateurs
	header( 'X-XSS-Protection: 1; mode=block' );

	// Politique de referrer stricte
	header( 'Referrer-Policy: strict-origin-when-cross-origin' );

	// Permissions Policy (anciennement Feature Policy)
	header( 'Permissions-Policy: geolocation=(), microphone=(), camera=()' );
}
