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

	register_taxonomy(
		'expertise',
		array( 'cgt_expert' ),
		array(
			'label'             => __( 'Expertises', 'cgt' ),
			'labels'            => array(
				'name'          => __( 'Expertises', 'cgt' ),
				'singular_name' => __( 'Expertise', 'cgt' ),
				'search_items'  => __( 'Rechercher une expertise', 'cgt' ),
				'all_items'     => __( 'Toutes les expertises', 'cgt' ),
				'edit_item'     => __( 'Modifier l\'expertise', 'cgt' ),
				'update_item'   => __( 'Mettre à jour l\'expertise', 'cgt' ),
				'add_new_item'  => __( 'Ajouter une nouvelle expertise', 'cgt' ),
				'new_item_name' => __( 'Nouvelle expertise', 'cgt' ),
				'menu_name'     => __( 'Expertises', 'cgt' ),
			),
			'hierarchical'      => true,
			'show_in_rest'      => true,
			'show_admin_column' => true,
			'show_ui'           => true,
			'rewrite'           => array(
				'slug'       => 'expertise',
				'with_front' => false,
			),
		)
	);
}
add_action( 'init', 'cgt_register_taxonomies' );

/**
 * Add default expertise terms
 */
function cgt_add_default_expertises() {
	if ( ! term_exists( 'Avocats', 'expertise' ) ) {
		wp_insert_term( 'Avocats', 'expertise', array( 'slug' => 'avocats' ) );
	}
	if ( ! term_exists( 'Juristes', 'expertise' ) ) {
		wp_insert_term( 'Juristes', 'expertise', array( 'slug' => 'juristes' ) );
	}
	if ( ! term_exists( 'Comptables', 'expertise' ) ) {
		wp_insert_term( 'Comptables', 'expertise', array( 'slug' => 'comptables' ) );
	}
	if ( ! term_exists( 'Expert en lois', 'expertise' ) ) {
		wp_insert_term( 'Expert en lois', 'expertise', array( 'slug' => 'expert-en-lois' ) );
	}
	if ( ! term_exists( 'Inspecteurs', 'expertise' ) ) {
		wp_insert_term( 'Inspecteurs', 'expertise', array( 'slug' => 'inspecteurs' ) );
	}
}
add_action( 'init', 'cgt_add_default_expertises', 20 );

/**
 * Add ID Brevo field to branche taxonomy
 */
function cgt_branche_add_form_fields() {
	?>
	<div class="form-field">
		<label for="branche_id_brevo"><?php esc_html_e( 'ID Brevo', 'cgt' ); ?></label>
		<input type="text" name="branche_id_brevo" id="branche_id_brevo" value="">
		<p class="description"><?php esc_html_e( 'Identifiant de la liste Brevo pour cette branche', 'cgt' ); ?></p>
	</div>
	<?php
}
add_action( 'branche_add_form_fields', 'cgt_branche_add_form_fields' );

/**
 * Add ID Brevo field to branche taxonomy edit form
 */
function cgt_branche_edit_form_fields( $term ) {
	$id_brevo = get_term_meta( $term->term_id, 'branche_id_brevo', true );
	?>
	<tr class="form-field">
		<th scope="row">
			<label for="branche_id_brevo"><?php esc_html_e( 'ID Brevo', 'cgt' ); ?></label>
		</th>
		<td>
			<input type="text" name="branche_id_brevo" id="branche_id_brevo" value="<?php echo esc_attr( $id_brevo ); ?>">
			<p class="description"><?php esc_html_e( 'Identifiant de la liste Brevo pour cette branche', 'cgt' ); ?></p>
		</td>
	</tr>
	<?php
}
add_action( 'branche_edit_form_fields', 'cgt_branche_edit_form_fields' );

/**
 * Save ID Brevo field for branche taxonomy
 */
function cgt_save_branche_meta( $term_id ) {
	if ( isset( $_POST['branche_id_brevo'] ) ) {
		update_term_meta( $term_id, 'branche_id_brevo', sanitize_text_field( wp_unslash( $_POST['branche_id_brevo'] ) ) );
	}
}
add_action( 'created_branche', 'cgt_save_branche_meta' );
add_action( 'edited_branche', 'cgt_save_branche_meta' );

/**
 * Add ID Brevo column to branche taxonomy admin table
 */
function cgt_branche_columns( $columns ) {
	$columns['branche_id_brevo'] = __( 'ID Brevo', 'cgt' );
	return $columns;
}
add_filter( 'manage_edit-branche_columns', 'cgt_branche_columns' );

/**
 * Display ID Brevo in branche taxonomy admin table
 */
function cgt_branche_column_content( $content, $column_name, $term_id ) {
	if ( 'branche_id_brevo' === $column_name ) {
		$id_brevo = get_term_meta( $term_id, 'branche_id_brevo', true );
		$content = $id_brevo ? esc_html( $id_brevo ) : '—';
	}
	return $content;
}
add_filter( 'manage_branche_custom_column', 'cgt_branche_column_content', 10, 3 );
