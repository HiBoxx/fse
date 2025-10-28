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
	$post_types_all = array( 'post', 'communiques_de_presse', 'dossiers_de_presse', 'tracts', 'articles_adherents', 'branch' );
	$post_types_classes = array( 'post', 'articles_adherents' );

	register_taxonomy(
		'branche',
		$post_types_all,
		array(
			'label'             => __( 'Branches', 'cgt' ),
			'labels'            => array(
				'name'          => __( 'Branches', 'cgt' ),
				'singular_name' => __( 'Branche', 'cgt' ),
			),
			'hierarchical'      => true,
			'show_in_rest'      => true,
			'show_admin_column' => true,
			'show_in_menu'      => 'edit.php',
			'rewrite'           => array(
				'slug'       => 'branch',
				'with_front' => false,
			),
		)
	);

	register_taxonomy(
		'thematique',
		$post_types_classes,
		array(
			'label'             => __( 'Classes', 'cgt' ),
			'labels'            => array(
				'name'          => __( 'Classes', 'cgt' ),
				'singular_name' => __( 'Classe', 'cgt' ),
				'search_items'  => __( 'Rechercher une classe', 'cgt' ),
				'all_items'     => __( 'Toutes les classes', 'cgt' ),
				'edit_item'     => __( 'Modifier la classe', 'cgt' ),
				'update_item'   => __( 'Mettre à jour la classe', 'cgt' ),
				'add_new_item'  => __( 'Ajouter une nouvelle classe', 'cgt' ),
				'new_item_name' => __( 'Nouvelle classe', 'cgt' ),
				'menu_name'     => __( 'Classes', 'cgt' ),
			),
			'hierarchical'      => true,
			'show_in_rest'      => true,
			'show_admin_column' => true,
			'rewrite'           => array(
				'slug'       => 'classes',
				'with_front' => false,
			),
		)
	);

	register_taxonomy(
		'zone_internationale',
		$post_types_all,
		array(
			'label'             => __( 'Zones internationales', 'cgt' ),
			'hierarchical'      => true,
			'show_in_rest'      => true,
			'show_admin_column' => false,
			'show_ui'           => false,
			'rewrite'           => array(
				'slug'       => 'international',
				'with_front' => false,
			),
		)
	);
}
add_action( 'init', 'cgt_register_taxonomies' );
