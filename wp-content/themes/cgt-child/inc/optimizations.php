<?php
/**
 * Optimisations de performance et améliorations diverses
 *
 * @package CGT_Child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Ajouter lazy loading aux images
 */
add_filter( 'wp_get_attachment_image_attributes', 'cgt_add_lazy_loading_to_images', 10, 1 );
function cgt_add_lazy_loading_to_images( $attr ) {
	if ( ! isset( $attr['loading'] ) ) {
		$attr['loading'] = 'lazy';
	}
	if ( ! isset( $attr['decoding'] ) ) {
		$attr['decoding'] = 'async';
	}
	return $attr;
}

/**
 * Ajouter le preconnect pour les fonts et ressources externes
 */
add_action( 'wp_head', 'cgt_add_resource_hints', 2 );
function cgt_add_resource_hints() {
	echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
	echo '<link rel="dns-prefetch" href="https://fonts.gstatic.com">' . "\n";
}

/**
 * Cache pour les queries coûteuses
 */
function cgt_get_cached_posts( $args, $cache_key, $expiration = 3600 ) {
	$cached = get_transient( $cache_key );

	if ( false !== $cached ) {
		return $cached;
	}

	$query = new WP_Query( $args );
	$posts = $query->posts;

	set_transient( $cache_key, $posts, $expiration );

	return $posts;
}

/**
 * Invalider le cache lors de la publication d'un post
 */
add_action( 'save_post', 'cgt_clear_post_cache', 10, 1 );
function cgt_clear_post_cache( $post_id ) {
	if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
		return;
	}

	$post_type = get_post_type( $post_id );

	// Nettoyer les caches pertinents
	delete_transient( 'cgt_latest_communiques' );
	delete_transient( 'cgt_latest_tracts' );
	delete_transient( 'cgt_latest_posts' );
	delete_transient( 'cgt_branches_list' );

	// Nettoyer le cache spécifique au type de post
	delete_transient( 'cgt_latest_' . $post_type );
}

/**
 * Optimisation des queries front page
 */
add_action( 'pre_get_posts', 'cgt_optimize_queries' );
function cgt_optimize_queries( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}

	// Limiter le nombre de posts par défaut
	if ( is_home() || is_archive() ) {
		$query->set( 'posts_per_page', 12 );

		// Optimiser avec des champs uniquement nécessaires
		$query->set( 'update_post_meta_cache', false );
		$query->set( 'update_post_term_cache', false );
		$query->set( 'no_found_rows', true );
	}
}

/**
 * Désactiver les embeds WordPress (performance)
 */
add_action( 'init', 'cgt_disable_embeds' );
function cgt_disable_embeds() {
	// Désactiver l'embed discovery
	remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
	remove_action( 'wp_head', 'wp_oembed_add_host_js' );

	// Désactiver les endpoints REST
	add_filter( 'embed_oembed_discover', '__return_false' );

	// Nettoyer les scripts inutiles
	add_filter( 'tiny_mce_plugins', 'cgt_disable_embeds_tiny_mce_plugin' );
	add_filter( 'rewrite_rules_array', 'cgt_disable_embeds_rewrites' );
}

function cgt_disable_embeds_tiny_mce_plugin( $plugins ) {
	return array_diff( $plugins, array( 'wpembed' ) );
}

function cgt_disable_embeds_rewrites( $rules ) {
	foreach ( $rules as $rule => $rewrite ) {
		if ( false !== strpos( $rewrite, 'embed=true' ) ) {
			unset( $rules[ $rule ] );
		}
	}
	return $rules;
}

/**
 * Optimiser les requêtes de termes (taxonomies)
 */
add_filter( 'get_terms', 'cgt_cache_taxonomy_terms', 10, 4 );
function cgt_cache_taxonomy_terms( $terms, $taxonomies, $args, $term_query ) {
	// Ne pas cacher si on demande explicitement un rafraîchissement
	if ( isset( $args['cache'] ) && false === $args['cache'] ) {
		return $terms;
	}

	return $terms;
}

/**
 * Ajouter les attributs width et height aux images pour éviter le CLS
 */
add_filter( 'the_content', 'cgt_add_image_dimensions' );
function cgt_add_image_dimensions( $content ) {
	if ( ! preg_match_all( '/<img[^>]+>/', $content, $images ) ) {
		return $content;
	}

	foreach ( $images[0] as $image ) {
		// Si l'image a déjà width et height, on passe
		if ( strpos( $image, 'width=' ) !== false && strpos( $image, 'height=' ) !== false ) {
			continue;
		}

		// Extraire le src
		if ( ! preg_match( '/src=["\'](.*?)["\']/', $image, $src_match ) ) {
			continue;
		}

		$src = $src_match[1];
		$attachment_id = attachment_url_to_postid( $src );

		if ( $attachment_id ) {
			$metadata = wp_get_attachment_metadata( $attachment_id );
			if ( isset( $metadata['width'] ) && isset( $metadata['height'] ) ) {
				$new_image = str_replace( '<img', '<img width="' . $metadata['width'] . '" height="' . $metadata['height'] . '"', $image );
				$content = str_replace( $image, $new_image, $content );
			}
		}
	}

	return $content;
}

/**
 * Optimisation du cache navigateur
 */
add_action( 'send_headers', 'cgt_add_cache_headers' );
function cgt_add_cache_headers() {
	if ( is_admin() || is_user_logged_in() ) {
		return;
	}

	// Cache agressif pour les assets statiques
	if ( preg_match( '/\.(jpg|jpeg|png|gif|webp|svg|css|js|woff|woff2|ttf|eot)$/i', $_SERVER['REQUEST_URI'] ?? '' ) ) {
		header( 'Cache-Control: public, max-age=31536000, immutable' );
		header( 'Expires: ' . gmdate( 'D, d M Y H:i:s', time() + 31536000 ) . ' GMT' );
	}
}

/**
 * Désactiver les heartbeats (économie ressources)
 */
add_action( 'init', 'cgt_disable_heartbeat', 1 );
function cgt_disable_heartbeat() {
	// Désactiver complètement le heartbeat sur le front-end
	if ( ! is_admin() ) {
		wp_deregister_script( 'heartbeat' );
	}
}

/**
 * Limiter les révisions de posts
 */
if ( ! defined( 'WP_POST_REVISIONS' ) ) {
	define( 'WP_POST_REVISIONS', 5 );
}

/**
 * Augmenter l'intervalle d'autosave
 */
if ( ! defined( 'AUTOSAVE_INTERVAL' ) ) {
	define( 'AUTOSAVE_INTERVAL', 300 ); // 5 minutes
}

/**
 * Désactiver l'éditeur de fichiers WordPress (sécurité)
 */
if ( ! defined( 'DISALLOW_FILE_EDIT' ) ) {
	define( 'DISALLOW_FILE_EDIT', true );
}

/**
 * Optimisation de la barre d'admin
 */
add_action( 'wp_before_admin_bar_render', 'cgt_remove_admin_bar_links' );
function cgt_remove_admin_bar_links() {
	global $wp_admin_bar;

	// Supprimer les liens inutiles
	$wp_admin_bar->remove_menu( 'wp-logo' );
	$wp_admin_bar->remove_menu( 'about' );
	$wp_admin_bar->remove_menu( 'wporg' );
	$wp_admin_bar->remove_menu( 'documentation' );
	$wp_admin_bar->remove_menu( 'support-forums' );
	$wp_admin_bar->remove_menu( 'feedback' );
}

/**
 * Nettoyage automatique des données temporaires
 */
add_action( 'cgt_daily_cleanup', 'cgt_cleanup_transients' );
function cgt_cleanup_transients() {
	global $wpdb;

	// Supprimer les transients expirés
	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM $wpdb->options WHERE option_name LIKE %s AND option_value < %d",
			'_transient_timeout_%',
			time()
		)
	);

	// Supprimer les transients orphelins
	$wpdb->query(
		"DELETE a, b FROM $wpdb->options a, $wpdb->options b
		WHERE a.option_name LIKE '_transient_%'
		AND a.option_name NOT LIKE '_transient_timeout_%'
		AND b.option_name = CONCAT('_transient_timeout_', SUBSTRING(a.option_name, 12))
		AND b.option_value < UNIX_TIMESTAMP()"
	);
}

/**
 * Optimisation des meta queries
 */
add_filter( 'posts_clauses', 'cgt_optimize_meta_queries', 10, 2 );
function cgt_optimize_meta_queries( $clauses, $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return $clauses;
	}

	// Optimiser les meta queries pour éviter les jointures multiples
	// Ceci est une optimisation avancée, à adapter selon les besoins

	return $clauses;
}

/**
 * Ajouter un skip link pour l'accessibilité
 */
add_action( 'wp_body_open', 'cgt_add_skip_link' );
function cgt_add_skip_link() {
	echo '<a href="#primary" class="skip-link">' . esc_html__( 'Aller au contenu principal', 'cgt' ) . '</a>' . "\n";
}

/**
 * Amélioration de la sécurité des headers
 */
add_action( 'send_headers', 'cgt_add_security_headers' );
function cgt_add_security_headers() {
	// Protection XSS
	header( 'X-XSS-Protection: 1; mode=block' );

	// Protection contre le clickjacking
	header( 'X-Frame-Options: SAMEORIGIN' );

	// Protection MIME
	header( 'X-Content-Type-Options: nosniff' );

	// Referrer policy
	header( 'Referrer-Policy: strict-origin-when-cross-origin' );

	// Permissions policy
	header( 'Permissions-Policy: geolocation=(), microphone=(), camera=()' );
}

/**
 * Optimisation de la recherche
 */
add_filter( 'pre_get_posts', 'cgt_optimize_search' );
function cgt_optimize_search( $query ) {
	if ( ! is_admin() && $query->is_main_query() && $query->is_search() ) {
		// Limiter à certains post types
		$query->set( 'post_type', array( 'post', 'page', 'communiques_de_presse', 'tracts' ) );

		// Optimiser la query
		$query->set( 'posts_per_page', 20 );
		$query->set( 'no_found_rows', false ); // On a besoin de la pagination
	}

	return $query;
}

/**
 * Compression GZIP (si pas déjà actif)
 */
if ( ! ini_get( 'zlib.output_compression' ) && 'ob_gzhandler' !== ini_get( 'output_handler' ) ) {
	if ( extension_loaded( 'zlib' ) && ! headers_sent() ) {
		ob_start( 'ob_gzhandler' );
	}
}
