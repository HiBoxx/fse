<?php
/**
 * SEO helpers.
 *
 * @package CGT_Child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Output meta robots for selected contexts.
 */
function cgt_meta_robots() {
	$should_noindex = false;

	if ( is_post_type_archive( 'communiques_de_presse' ) || is_post_type_archive( 'dossiers_de_presse' ) ) {
		$should_noindex = true;
	}

	if ( is_tax( array( 'branche', 'zone_internationale' ) ) ) {
		$should_noindex = true;
	}

	$should_noindex = apply_filters( 'cgt_noindex', $should_noindex );

	if ( $should_noindex ) {
		echo '<meta name="robots" content="noindex,follow" />' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
add_action( 'wp_head', 'cgt_meta_robots', 5 );

/**
 * Output basic OpenGraph tags.
 */
function cgt_opengraph_tags() {
	$title       = wp_get_document_title();
	$description = get_bloginfo( 'description', 'display' );
	$url         = home_url( add_query_arg( array(), $_SERVER['REQUEST_URI'] ?? '' ) );

	if ( is_singular() ) {
		global $post;
		$excerpt = has_excerpt( $post ) ? get_the_excerpt( $post ) : wp_trim_words( wp_strip_all_tags( $post->post_content ), 24, '…' );
		if ( $excerpt ) {
			$description = $excerpt;
		}
		$url = get_permalink( $post );
	}

	printf( '<meta property="og:title" content="%s" />' . "\n", esc_attr( $title ) );
	printf( '<meta property="og:description" content="%s" />' . "\n", esc_attr( $description ) );
	printf( '<meta property="og:url" content="%s" />' . "\n", esc_url( $url ) );
	printf( '<meta property="og:site_name" content="%s" />' . "\n", esc_attr( get_bloginfo( 'name' ) ) );
	echo '<meta name="twitter:card" content="summary_large_image" />' . "\n";
}
add_action( 'wp_head', 'cgt_opengraph_tags', 20 );

/**
 * Register sitemap rewrite.
 */
function cgt_register_sitemap_rewrite() {
	add_rewrite_rule( 'cgt-sitemap\.xml$', 'index.php?cgt_sitemap=1', 'top' );
}
add_action( 'init', 'cgt_register_sitemap_rewrite' );

/**
 * Add sitemap query var.
 *
 * @param array $vars Vars.
 * @return array
 */
function cgt_sitemap_query_vars( $vars ) {
	$vars[] = 'cgt_sitemap';
	return $vars;
}
add_filter( 'query_vars', 'cgt_sitemap_query_vars' );

/**
 * Output a lightweight sitemap when requested.
 * Supports pagination with 500 URLs per page.
 */
function cgt_maybe_output_sitemap() {
	if ( ! get_query_var( 'cgt_sitemap' ) ) {
		return;
	}

	// Limite Google : 50,000 URLs par sitemap, on utilise 500 pour la performance
	$posts_per_sitemap = 500;
	$page = isset( $_GET['page'] ) ? max( 1, absint( $_GET['page'] ) ) : 1;
	$offset = ( $page - 1 ) * $posts_per_sitemap;

	// Compter le total de posts pour savoir s'il faut paginer
	$total_posts = wp_count_posts();
	$total_public_posts = 0;
	$post_types = array( 'page', 'post', 'communiques_de_presse', 'dossiers_de_presse', 'tracts', 'branch' );

	foreach ( $post_types as $type ) {
		$counts = wp_count_posts( $type );
		if ( isset( $counts->publish ) ) {
			$total_public_posts += $counts->publish;
		}
	}

	// Récupérer les posts pour cette page
	$posts = get_posts(
		array(
			'post_type'      => $post_types,
			'post_status'    => 'publish',
			'numberposts'    => $posts_per_sitemap,
			'offset'         => $offset,
			'orderby'        => 'modified',
			'order'          => 'DESC',
			'meta_query'     => array(
				'relation' => 'OR',
				array(
					'key'     => 'cgt_visibilite',
					'value'   => 'prive',
					'compare' => '!=',
				),
				array(
					'key'     => 'cgt_visibilite',
					'compare' => 'NOT EXISTS',
				),
			),
		)
	);

	header( 'Content-Type: application/xml; charset=' . get_option( 'blog_charset' ) );
	echo '<?xml version="1.0" encoding="' . esc_attr( get_option( 'blog_charset' ) ) . "\"?>\n";

	// Si c'est la première page et qu'il y a plus de 500 posts, créer un index de sitemaps
	if ( 1 === $page && $total_public_posts > $posts_per_sitemap ) {
		echo '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
		$total_pages = ceil( $total_public_posts / $posts_per_sitemap );
		for ( $i = 1; $i <= $total_pages; $i++ ) {
			$sitemap_url = home_url( '/cgt-sitemap.xml?page=' . $i );
			printf(
				"<sitemap>\n<loc>%s</loc>\n<lastmod>%s</lastmod>\n</sitemap>\n",
				esc_url( $sitemap_url ),
				esc_html( gmdate( 'Y-m-d\TH:i:s\Z' ) )
			);
		}
		echo '</sitemapindex>';
	} else {
		// Sitemap classique avec les URLs
		echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

		// Ajouter la homepage en priorité
		if ( 1 === $page ) {
			printf(
				"<url>\n<loc>%s</loc>\n<lastmod>%s</lastmod>\n<priority>1.0</priority>\n</url>\n",
				esc_url( home_url( '/' ) ),
				esc_html( gmdate( 'Y-m-d\TH:i:s\Z' ) )
			);
		}

		// Ajouter les posts
		foreach ( $posts as $post ) {
			// Définir la priorité selon le type de contenu
			$priority = 0.5;
			if ( 'page' === $post->post_type ) {
				$priority = 0.8;
			} elseif ( in_array( $post->post_type, array( 'post', 'communiques_de_presse' ), true ) ) {
				$priority = 0.7;
			}

			printf(
				"<url>\n<loc>%s</loc>\n<lastmod>%s</lastmod>\n<priority>%.1f</priority>\n</url>\n",
				esc_url( get_permalink( $post ) ),
				esc_html( gmdate( 'Y-m-d\TH:i:s\Z', strtotime( $post->post_modified_gmt ) ) ),
				$priority
			);
		}
		echo '</urlset>';
	}
	exit;
}
add_action( 'template_redirect', 'cgt_maybe_output_sitemap' );
