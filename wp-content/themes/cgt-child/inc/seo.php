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
 */
function cgt_maybe_output_sitemap() {
	if ( ! get_query_var( 'cgt_sitemap' ) ) {
		return;
	}

	$posts = get_posts(
		array(
			'post_type'      => array( 'page', 'post', 'communiques_de_presse', 'dossiers_de_presse', 'tracts', 'branch' ),
			'post_status'    => 'publish',
			'numberposts'    => 200,
			'orderby'        => 'modified',
			'order'          => 'DESC',
			'meta_query'     => array(
				array(
					'key'     => 'cgt_visibilite',
					'value'   => 'prive',
					'compare' => '!=',
				),
			),
		)
	);

	header( 'Content-Type: application/xml; charset=' . get_option( 'blog_charset' ) );
	echo '<?xml version="1.0" encoding="' . esc_attr( get_option( 'blog_charset' ) ) . "\"?>\n";
	echo "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
	foreach ( $posts as $post ) {
		printf(
			"<url>\n<loc>%s</loc>\n<lastmod>%s</lastmod>\n</url>\n",
			esc_url( get_permalink( $post ) ),
			esc_html( gmdate( 'Y-m-d\TH:i:s\Z', strtotime( $post->post_modified_gmt ) ) )
		);
	}
	echo "</urlset>";
	exit;
}
add_action( 'template_redirect', 'cgt_maybe_output_sitemap' );
