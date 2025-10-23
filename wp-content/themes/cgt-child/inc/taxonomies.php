<?php
/**
 * Custom taxonomies.
 *
 * @package CGT_Child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register custom taxonomies.
 */
function cgt_register_taxonomies() {
	$post_types = array( 'communiques_de_presse', 'dossiers_de_presse', 'tracts', 'articles_adherents', 'branch' );

	register_taxonomy(
		'branche',
		$post_types,
		array(
			'label'             => __( 'Branches', 'cgt' ),
			'labels'            => array(
				'name'          => __( 'Branches', 'cgt' ),
				'singular_name' => __( 'Branche', 'cgt' ),
			),
			'hierarchical'      => true,
			'show_in_rest'      => true,
			'show_admin_column' => true,
			'rewrite'           => array(
				'slug'       => 'branch',
				'with_front' => false,
			),
		)
	);

	register_taxonomy(
		'thematique',
		$post_types,
		array(
			'label'             => __( 'Thématiques', 'cgt' ),
			'show_in_rest'      => true,
			'hierarchical'      => false,
			'show_admin_column' => true,
			'rewrite'           => array(
				'slug'       => 'themes',
				'with_front' => false,
			),
		)
	);

	register_taxonomy(
		'zone_internationale',
		$post_types,
		array(
			'label'             => __( 'Zones internationales', 'cgt' ),
			'hierarchical'      => true,
			'show_in_rest'      => true,
			'show_admin_column' => true,
			'rewrite'           => array(
				'slug'       => 'international',
				'with_front' => false,
			),
		)
	);
}
add_action( 'init', 'cgt_register_taxonomies' );
