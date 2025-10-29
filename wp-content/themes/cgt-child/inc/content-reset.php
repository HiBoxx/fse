<?php
/**
 * Purge unique des articles et tracts existants pour faciliter les tests locaux.
 *
 * @package CGT_Child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Supprime les contenus ciblés une seule fois.
 */
function cgt_maybe_purge_articles_and_tracts() {
	if ( is_admin() && defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		return;
	}

	if ( get_option( 'cgt_content_purge_20241024' ) ) {
		return;
	}

	$post_types = apply_filters(
		'cgt_content_purge_post_types',
		array(
			'post',
			'tracts',
			'articles_adherents',
		)
	);

	$deleted_counts = array();

	foreach ( $post_types as $type ) {
		$ids = get_posts(
			array(
				'post_type'        => $type,
				'post_status'      => 'any',
				'fields'           => 'ids',
				'nopaging'         => true,
				'suppress_filters' => true,
			)
		);

		$deleted_counts[ $type ] = 0;

		foreach ( $ids as $post_id ) {
			$result = wp_delete_post( $post_id, true );
			if ( $result instanceof WP_Post || true === $result ) {
				++$deleted_counts[ $type ];
			}
		}
	}

	update_option(
		'cgt_content_purge_20241024',
		array(
			'deleted'   => $deleted_counts,
			'timestamp' => current_time( 'mysql', true ),
		)
	);
}

add_action( 'init', 'cgt_maybe_purge_articles_and_tracts', 40 );
