<?php
/**
 * Custom Post Types.
 *
 * @package CGT_Child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register custom post types.
 */
function cgt_register_cpts() {
	$common_args = array(
		'supports'            => array( 'title', 'editor', 'excerpt', 'revisions', 'author', 'thumbnail' ),
		'show_in_rest'        => true,
		'public'              => true,
		'show_in_nav_menus'   => true,
		'show_in_admin_bar'   => true,
		'map_meta_cap'        => true,
		'has_archive'         => true,
		'hierarchical'        => false,
		'delete_with_user'    => false,
		'publicly_queryable'  => true,
	);

	register_post_type(
		'communiques_de_presse',
		array_merge(
			$common_args,
			array(
				'label'               => __( 'Communiqués de presse', 'cgt' ),
				'labels'              => array(
					'name'          => __( 'Communiqués de presse', 'cgt' ),
					'singular_name' => __( 'Communiqué de presse', 'cgt' ),
					'add_new'       => __( 'Ajouter un communiqué', 'cgt' ),
					'add_new_item'  => __( 'Ajouter un nouveau communiqué', 'cgt' ),
					'edit_item'     => __( 'Modifier le communiqué', 'cgt' ),
					'new_item'      => __( 'Nouveau communiqué', 'cgt' ),
					'all_items'     => __( 'Tous les communiqués', 'cgt' ),
				),
				'rewrite'             => array(
					'slug'       => 'communiques',
					'with_front' => false,
				),
				'menu_icon'           => 'dashicons-megaphone',
				'capability_type'     => 'post',
			)
		)
	);

	register_post_type(
		'dossiers_de_presse',
		array_merge(
			$common_args,
			array(
				'label'               => __( 'Dossiers de presse', 'cgt' ),
				'labels'              => array(
					'name'          => __( 'Dossiers de presse', 'cgt' ),
					'singular_name' => __( 'Dossier de presse', 'cgt' ),
				),
				'rewrite'             => array(
					'slug'       => 'dossiers',
					'with_front' => false,
				),
				'menu_icon'           => 'dashicons-portfolio',
			)
		)
	);

	register_post_type(
		'tracts',
		array_merge(
			$common_args,
			array(
				'label'               => __( 'Tracts', 'cgt' ),
				'labels'              => array(
					'name'          => __( 'Tracts', 'cgt' ),
					'singular_name' => __( 'Tract', 'cgt' ),
				),
				'rewrite'             => array(
					'slug'       => 'tracts',
					'with_front' => false,
				),
				'menu_icon'           => 'dashicons-media-document',
			)
		)
	);

	register_post_type(
		'articles_adherents',
		array(
			'label'               => __( 'Articles adhérents', 'cgt' ),
			'labels'              => array(
				'name'          => __( 'Articles adhérents', 'cgt' ),
				'singular_name' => __( 'Article adhérent', 'cgt' ),
				'add_new'       => __( 'Ajouter', 'cgt' ),
				'add_new_item'  => __( 'Ajouter un article adhérent', 'cgt' ),
			),
			'public'              => false,
			'publicly_queryable'  => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_rest'        => true,
			'has_archive'         => false,
			'supports'            => array( 'title', 'editor', 'excerpt', 'revisions', 'author' ),
			'capability_type'     => array( 'article_adherent', 'articles_adherents' ),
			'map_meta_cap'        => true,
			'menu_icon'           => 'dashicons-lock',
		)
	);

	register_post_type(
		'branch',
		array(
			'label'               => __( 'Branches', 'cgt' ),
			'labels'              => array(
				'name'          => __( 'Branches', 'cgt' ),
				'singular_name' => __( 'Branche', 'cgt' ),
			),
			'public'             => true,
			'show_ui'            => true,
			'show_in_rest'       => true,
			'has_archive'        => true,
			'supports'           => array( 'title', 'editor', 'excerpt', 'thumbnail' ),
			'rewrite'            => array(
				'slug'       => 'branches',
				'with_front' => false,
			),
			'menu_icon'          => 'dashicons-networking',
		)
	);

	register_post_type(
		'cgt_question',
		array(
			'label'               => __( 'Questions adhérents', 'cgt' ),
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => 'edit.php?post_type=articles_adherents',
			'show_in_rest'        => false,
			'supports'            => array( 'title', 'editor', 'author' ),
			'capability_type'     => 'post',
			'map_meta_cap'        => true,
		)
	);
}
add_action( 'init', 'cgt_register_cpts' );

/**
 * Register custom meta fields.
 */
function cgt_register_meta_fields() {
	$auth_callback = static function () {
		return current_user_can( 'edit_posts' );
	};

	register_post_meta(
		'communiques_de_presse',
		'cgt_chapo',
		array(
			'type'              => 'string',
			'single'            => true,
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
			'auth_callback'     => $auth_callback,
		)
	);

	register_post_meta(
		'communiques_de_presse',
		'cgt_porteur',
		array(
			'type'              => 'string',
			'single'            => true,
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
			'auth_callback'     => $auth_callback,
		)
	);

	register_post_meta(
		'communiques_de_presse',
		'cgt_embargo',
		array(
			'type'              => 'string',
			'single'            => true,
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
			'auth_callback'     => $auth_callback,
		)
	);

	register_post_meta(
		'tracts',
		'cgt_fichier_pdf',
		array(
			'type'              => 'string',
			'single'            => true,
			'sanitize_callback' => 'esc_url_raw',
			'show_in_rest'      => true,
			'auth_callback'     => $auth_callback,
		)
	);

	register_post_meta(
		'tracts',
		'cgt_visibilite',
		array(
			'type'              => 'string',
			'single'            => true,
			'default'           => 'public',
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
			'auth_callback'     => $auth_callback,
		)
	);
}
add_action( 'init', 'cgt_register_meta_fields' );

/**
 * Force private status for member articles.
 *
 * @param array $data    Post data.
 * @param array $postarr Original post array.
 * @return array
 */
function cgt_force_private_articles( $data, $postarr ) {
	if ( isset( $postarr['post_type'] ) && 'articles_adherents' === $postarr['post_type'] ) {
		$data['post_status'] = 'private';
	}

	return $data;
}
add_filter( 'wp_insert_post_data', 'cgt_force_private_articles', 10, 2 );
