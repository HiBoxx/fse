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
				'show_ui'             => false,
				'show_in_menu'        => false,
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
			'public'              => true,
			'publicly_queryable'  => true,
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
			'show_in_menu'       => false,
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
			'show_in_menu'        => false,
			'show_in_rest'        => false,
			'supports'            => array( 'title', 'editor', 'author' ),
			'capability_type'     => 'post',
			'map_meta_cap'        => true,
		)
	);

	register_post_type(
		'cgt_contact',
		array(
			'label'               => __( 'Messages', 'cgt' ),
			'labels'              => array(
				'name'          => __( 'Messages', 'cgt' ),
				'singular_name' => __( 'Message', 'cgt' ),
				'all_items'     => __( 'Messages reçus', 'cgt' ),
				'add_new'       => '',
				'add_new_item'  => '',
				'edit_item'     => __( 'Consulter le message', 'cgt' ),
				'not_found'     => __( 'Aucun message', 'cgt' ),
				'menu_name'     => __( 'Messages', 'cgt' ),
			),
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_rest'        => false,
			'has_archive'         => false,
			'supports'            => array( 'title' ),
			'menu_icon'           => 'dashicons-email',
			'menu_position'       => 26,
			'capability_type'     => 'post',
			'map_meta_cap'        => true,
		)
	);

	register_post_type(
		'cgt_agenda',
		array(
			'label'               => __( 'Événements fédéraux', 'cgt' ),
			'labels'              => array(
				'name'          => __( 'Événements fédéraux', 'cgt' ),
				'singular_name' => __( 'Événement fédéral', 'cgt' ),
				'menu_name'     => __( 'Événements', 'cgt' ),
				'add_new_item'  => __( 'Ajouter un événement', 'cgt' ),
				'all_items'     => __( 'Liste des événements', 'cgt' ),
			),
			'public'              => true,
			'publicly_queryable'  => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_rest'        => true,
			'has_archive'         => false,
			'supports'            => array( 'title', 'editor', 'thumbnail' ),
			'menu_icon'           => 'dashicons-calendar-alt',
			'menu_position'       => 27,
			'taxonomies'          => array( 'branche' ),
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

	$contact_meta = array(
		'cgt_submission_name'    => array( 'type' => 'string', 'sanitize' => 'sanitize_text_field' ),
		'cgt_submission_email'   => array( 'type' => 'string', 'sanitize' => 'sanitize_email' ),
		'cgt_submission_phone'   => array( 'type' => 'string', 'sanitize' => 'sanitize_text_field' ),
		'cgt_submission_subject' => array( 'type' => 'string', 'sanitize' => 'sanitize_text_field' ),
		'cgt_submission_message' => array( 'type' => 'string', 'sanitize' => 'wp_kses_post' ),
		'cgt_contact_type'       => array( 'type' => 'string', 'sanitize' => 'sanitize_text_field' ),
		'cgt_contact_response'   => array( 'type' => 'string', 'sanitize' => 'wp_kses_post' ),
		'cgt_contact_user'       => array( 'type' => 'integer', 'sanitize' => 'absint' ),
	);

	foreach ( $contact_meta as $meta_key => $args ) {
		register_post_meta(
			'cgt_contact',
			$meta_key,
			array(
				'type'              => $args['type'],
				'single'            => true,
				'sanitize_callback' => $args['sanitize'],
				'auth_callback'     => $auth_callback,
			)
		);
	}

	register_post_meta(
		'cgt_agenda',
		'cgt_event_date',
		array(
			'type'              => 'string',
			'single'            => true,
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
			'auth_callback'     => $auth_callback,
		)
	);

	register_post_meta(
		'cgt_agenda',
		'cgt_event_address',
		array(
			'type'              => 'string',
			'single'            => true,
			'sanitize_callback' => 'sanitize_textarea_field',
			'show_in_rest'      => true,
			'auth_callback'     => $auth_callback,
		)
	);

	register_post_meta(
		'cgt_agenda',
		'cgt_event_document',
		array(
			'type'              => 'integer',
			'single'            => true,
			'sanitize_callback' => 'absint',
			'show_in_rest'      => true,
			'auth_callback'     => $auth_callback,
		)
	);
}
add_action( 'init', 'cgt_register_meta_fields' );

/**
 * Customize admin list columns for contact messages.
 *
 * @param array $columns Existing columns.
 * @return array
 */
function cgt_contact_columns( $columns ) {
	$new_columns = array(
		'cb'      => isset( $columns['cb'] ) ? $columns['cb'] : '<input type="checkbox" />',
		'title'   => __( 'Titre', 'cgt' ),
		'type'    => __( 'Type', 'cgt' ),
		'email'   => __( 'Email', 'cgt' ),
		'phone'   => __( 'Téléphone', 'cgt' ),
		'subject' => __( 'Sujet', 'cgt' ),
		'date'    => isset( $columns['date'] ) ? $columns['date'] : __( 'Date', 'cgt' ),
	);

	return $new_columns;
}
add_filter( 'manage_cgt_contact_posts_columns', 'cgt_contact_columns' );

/**
 * Populate custom columns for contact messages.
 *
 * @param string $column Column name.
 * @param int    $post_id Current post ID.
 */
function cgt_contact_custom_column( $column, $post_id ) {
	switch ( $column ) {
		case 'type':
			$type = get_post_meta( $post_id, 'cgt_contact_type', true );
			if ( 'question' === $type ) {
				esc_html_e( 'Question adhérent', 'cgt' );
			} else {
				esc_html_e( 'Contact', 'cgt' );
			}
			break;
		case 'email':
			$email = get_post_meta( $post_id, 'cgt_submission_email', true );
			echo $email ? '<a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a>' : '—';
			break;
		case 'phone':
			$phone = get_post_meta( $post_id, 'cgt_submission_phone', true );
			echo $phone ? esc_html( $phone ) : '—';
			break;
		case 'subject':
			$subject = get_post_meta( $post_id, 'cgt_submission_subject', true );
			echo $subject ? esc_html( $subject ) : '—';
			break;
	}
}
add_action( 'manage_cgt_contact_posts_custom_column', 'cgt_contact_custom_column', 10, 2 );

/**
 * Admin columns for agenda CPT.
 */
function cgt_agenda_columns( $columns ) {
	$new = array();
	foreach ( $columns as $key => $label ) {
		$new[ $key ] = $label;
		if ( 'title' === $key ) {
			$new['event_date'] = __( 'Date', 'cgt' );
			$new['event_branch'] = __( 'Branche', 'cgt' );
		}
	}

	return $new;
}
add_filter( 'manage_cgt_agenda_posts_columns', 'cgt_agenda_columns' );

function cgt_agenda_custom_column( $column, $post_id ) {
	switch ( $column ) {
		case 'event_date':
			$date = get_post_meta( $post_id, 'cgt_event_date', true );
			echo $date ? esc_html( wp_date( 'd/m/Y H:i', strtotime( $date ) ) ) : '—';
			break;
		case 'event_branch':
			$terms = get_the_terms( $post_id, 'branche' );
			if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
				echo esc_html( implode( ', ', wp_list_pluck( $terms, 'name' ) ) );
			} else {
				echo '—';
			}
			break;
	}
}
add_action( 'manage_cgt_agenda_posts_custom_column', 'cgt_agenda_custom_column', 10, 2 );

/**
 * Register a read-only metabox to display contact details.
 */
function cgt_contact_register_metabox() {
	add_meta_box(
		'cgt-contact-details',
		__( 'Détails du message', 'cgt' ),
		'cgt_contact_render_metabox',
		'cgt_contact',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes_cgt_contact', 'cgt_contact_register_metabox' );

/**
 * Render the contact details metabox.
 *
 * @param WP_Post $post Current post object.
 */
function cgt_contact_render_metabox( $post ) {
	$name      = get_post_meta( $post->ID, 'cgt_submission_name', true );
	$email     = get_post_meta( $post->ID, 'cgt_submission_email', true );
	$phone     = get_post_meta( $post->ID, 'cgt_submission_phone', true );
	$subject   = get_post_meta( $post->ID, 'cgt_submission_subject', true );
	$message   = get_post_meta( $post->ID, 'cgt_submission_message', true );
	$type      = get_post_meta( $post->ID, 'cgt_contact_type', true );
	$response  = get_post_meta( $post->ID, 'cgt_contact_response', true );
	$user_id   = (int) get_post_meta( $post->ID, 'cgt_contact_user', true );
	$user_label = '—';

	if ( $user_id ) {
		$user = get_user_by( 'id', $user_id );
		if ( $user ) {
			$user_label = sprintf(
				'%1$s (%2$s)',
				esc_html( $user->display_name ),
				esc_html( $user->user_email )
			);
		}
	}

	wp_nonce_field( 'cgt_contact_response', 'cgt_contact_response_nonce' );
	?>
	<table class="form-table cgt-contact-details-table">
		<tbody>
			<tr>
				<th><?php esc_html_e( 'Nom et prénom', 'cgt' ); ?></th>
				<td><?php echo $name ? esc_html( $name ) : '—'; ?></td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Type', 'cgt' ); ?></th>
				<td>
					<?php
					if ( 'question' === $type ) {
						esc_html_e( 'Question adhérent', 'cgt' );
					} else {
						esc_html_e( 'Contact classique', 'cgt' );
					}
					?>
				</td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Email', 'cgt' ); ?></th>
				<td>
					<?php if ( $email ) : ?>
						<a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a>
					<?php else : ?>
						—
					<?php endif; ?>
				</td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Téléphone', 'cgt' ); ?></th>
				<td><?php echo $phone ? esc_html( $phone ) : '—'; ?></td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Sujet', 'cgt' ); ?></th>
				<td><?php echo $subject ? esc_html( $subject ) : '—'; ?></td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Message', 'cgt' ); ?></th>
				<td>
					<div class="cgt-contact-message">
						<?php echo $message ? wpautop( wp_kses_post( $message ) ) : '—'; ?>
					</div>
				</td>
			</tr>
			<?php if ( $user_id ) : ?>
				<tr>
					<th><?php esc_html_e( 'Adhérent associé', 'cgt' ); ?></th>
					<td><?php echo $user_label; ?></td>
				</tr>
			<?php endif; ?>
			<?php if ( 'question' === $type ) : ?>
				<tr>
					<th><?php esc_html_e( 'Réponse à transmettre', 'cgt' ); ?></th>
					<td>
						<textarea name="cgt_contact_response" rows="6" class="widefat"><?php echo $response ? esc_textarea( $response ) : ''; ?></textarea>
						<p class="description"><?php esc_html_e( 'Saisissez votre réponse pour l’adhérent. Elle sera visible dans son espace personnel.', 'cgt' ); ?></p>
					</td>
				</tr>
			<?php endif; ?>
		</tbody>
	</table>
	<?php
}

/**
 * Save contact response metadata.
 *
 * @param int $post_id Post ID.
 */
function cgt_contact_save_post( $post_id ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! isset( $_POST['cgt_contact_response_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['cgt_contact_response_nonce'] ), 'cgt_contact_response' ) ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$type = get_post_meta( $post_id, 'cgt_contact_type', true );
	if ( 'question' !== $type ) {
		return;
	}

	$response = isset( $_POST['cgt_contact_response'] ) ? wp_kses_post( wp_unslash( $_POST['cgt_contact_response'] ) ) : '';
	update_post_meta( $post_id, 'cgt_contact_response', $response );

	if ( $response && 'publish' !== get_post_status( $post_id ) ) {
		remove_action( 'save_post_cgt_contact', 'cgt_contact_save_post' );
		wp_update_post(
			array(
				'ID'          => $post_id,
				'post_status' => 'publish',
			)
		);
		add_action( 'save_post_cgt_contact', 'cgt_contact_save_post' );
	}
}
add_action( 'save_post_cgt_contact', 'cgt_contact_save_post' );

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

/**
 * Register Nos Experts Custom Post Type
 */
function cgt_register_experts_cpt() {
	register_post_type(
		'cgt_expert',
		array(
			'label'               => __( 'Nos Experts', 'cgt' ),
			'labels'              => array(
				'name'               => __( 'Nos Experts', 'cgt' ),
				'singular_name'      => __( 'Expert', 'cgt' ),
				'add_new'            => __( 'Ajouter un expert', 'cgt' ),
				'add_new_item'       => __( 'Ajouter un nouvel expert', 'cgt' ),
				'edit_item'          => __( 'Modifier l\'expert', 'cgt' ),
				'new_item'           => __( 'Nouvel expert', 'cgt' ),
				'view_item'          => __( 'Voir l\'expert', 'cgt' ),
				'search_items'       => __( 'Rechercher un expert', 'cgt' ),
				'not_found'          => __( 'Aucun expert trouvé', 'cgt' ),
				'not_found_in_trash' => __( 'Aucun expert dans la corbeille', 'cgt' ),
				'all_items'          => __( 'Tous les experts', 'cgt' ),
			),
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'menu_icon'           => 'dashicons-businessman',
			'menu_position'       => 25,
			'supports'            => array( 'title', 'thumbnail' ),
			'show_in_rest'        => true,
			'has_archive'         => false,
			'publicly_queryable'  => true,
			'capability_type'     => 'post',
			'map_meta_cap'        => true,
			'rewrite'             => array(
				'slug'       => 'expert',
				'with_front' => false,
			),
		)
	);
}
add_action( 'init', 'cgt_register_experts_cpt' );

/**
 * Add meta boxes for expert fields
 */
function cgt_expert_meta_boxes() {
	add_meta_box(
		'cgt_expert_details',
		__( 'Informations de l\'expert', 'cgt' ),
		'cgt_expert_meta_box_callback',
		'cgt_expert',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'cgt_expert_meta_boxes' );

/**
 * Display expert meta box
 */
function cgt_expert_meta_box_callback( $post ) {
	wp_nonce_field( 'cgt_expert_meta_box', 'cgt_expert_meta_box_nonce' );

	$prenom      = get_post_meta( $post->ID, '_expert_prenom', true );
	$telephone   = get_post_meta( $post->ID, '_expert_telephone', true );
	$email       = get_post_meta( $post->ID, '_expert_email', true );
	$adresse     = get_post_meta( $post->ID, '_expert_adresse', true );
	$description = get_post_meta( $post->ID, '_expert_description', true );
	$mots_cles   = get_post_meta( $post->ID, '_expert_mots_cles', true );
	?>
	<style>
		.cgt-expert-field { margin-bottom: 20px; }
		.cgt-expert-field label { display: block; font-weight: 600; margin-bottom: 5px; }
		.cgt-expert-field input[type="text"],
		.cgt-expert-field input[type="email"],
		.cgt-expert-field input[type="tel"],
		.cgt-expert-field textarea { width: 100%; padding: 8px; }
		.cgt-expert-field textarea { min-height: 100px; }
		.cgt-expert-field small { color: #666; font-style: italic; }
	</style>

	<div class="cgt-expert-field">
		<label for="expert_prenom"><?php esc_html_e( 'Prénom', 'cgt' ); ?> <span style="color: red;">*</span></label>
		<input type="text" id="expert_prenom" name="expert_prenom" value="<?php echo esc_attr( $prenom ); ?>" required>
	</div>

	<div class="cgt-expert-field">
		<label for="expert_telephone"><?php esc_html_e( 'Numéro de téléphone', 'cgt' ); ?></label>
		<input type="tel" id="expert_telephone" name="expert_telephone" value="<?php echo esc_attr( $telephone ); ?>" placeholder="Ex: 01 23 45 67 89">
	</div>

	<div class="cgt-expert-field">
		<label for="expert_email"><?php esc_html_e( 'Email', 'cgt' ); ?></label>
		<input type="email" id="expert_email" name="expert_email" value="<?php echo esc_attr( $email ); ?>" placeholder="expert@exemple.fr">
	</div>

	<div class="cgt-expert-field">
		<label for="expert_adresse"><?php esc_html_e( 'Adresse', 'cgt' ); ?></label>
		<input type="text" id="expert_adresse" name="expert_adresse" value="<?php echo esc_attr( $adresse ); ?>" placeholder="123 rue de la République, 75001 Paris">
	</div>

	<div class="cgt-expert-field">
		<label for="expert_description"><?php esc_html_e( 'Description', 'cgt' ); ?></label>
		<textarea id="expert_description" name="expert_description" placeholder="Décrivez l'expertise et l'expérience de cette personne..."><?php echo esc_textarea( $description ); ?></textarea>
	</div>

	<div class="cgt-expert-field">
		<label for="expert_mots_cles"><?php esc_html_e( 'Mots-clés de recherche', 'cgt' ); ?></label>
		<input type="text" id="expert_mots_cles" name="expert_mots_cles" value="<?php echo esc_attr( $mots_cles ); ?>" placeholder="droit du travail, licenciement, accident...">
		<small><?php esc_html_e( 'Séparez les mots-clés par des virgules', 'cgt' ); ?></small>
	</div>

	<p><strong><?php esc_html_e( 'Note:', 'cgt' ); ?></strong> <?php esc_html_e( 'Le nom de famille est le titre de l\'article. L\'expertise se sélectionne dans la colonne de droite "Expertise".', 'cgt' ); ?></p>
	<p><strong><?php esc_html_e( 'Image:', 'cgt' ); ?></strong> <?php esc_html_e( 'Utilisez "Image à la une" dans la colonne de droite. Si aucune image n\'est définie, une image grise par défaut sera affichée.', 'cgt' ); ?></p>
	<?php
}

/**
 * Save expert meta data
 */
function cgt_save_expert_meta( $post_id ) {
	// Check nonce
	if ( ! isset( $_POST['cgt_expert_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['cgt_expert_meta_box_nonce'], 'cgt_expert_meta_box' ) ) {
		return;
	}

	// Check autosave
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	// Check permissions
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	// Save fields
	$fields = array(
		'expert_prenom'      => '_expert_prenom',
		'expert_telephone'   => '_expert_telephone',
		'expert_email'       => '_expert_email',
		'expert_adresse'     => '_expert_adresse',
		'expert_description' => '_expert_description',
		'expert_mots_cles'   => '_expert_mots_cles',
	);

	foreach ( $fields as $field => $meta_key ) {
		if ( isset( $_POST[ $field ] ) ) {
			if ( 'expert_description' === $field ) {
				update_post_meta( $post_id, $meta_key, sanitize_textarea_field( wp_unslash( $_POST[ $field ] ) ) );
			} elseif ( 'expert_email' === $field ) {
				update_post_meta( $post_id, $meta_key, sanitize_email( wp_unslash( $_POST[ $field ] ) ) );
			} else {
				update_post_meta( $post_id, $meta_key, sanitize_text_field( wp_unslash( $_POST[ $field ] ) ) );
			}
		}
	}
}
add_action( 'save_post_cgt_expert', 'cgt_save_expert_meta' );

/**
 * Customize admin columns for experts
 */
function cgt_expert_admin_columns( $columns ) {
	$new_columns = array();
	$new_columns['cb']         = $columns['cb'];
	$new_columns['thumbnail']  = __( 'Photo', 'cgt' );
	$new_columns['title']      = __( 'Nom', 'cgt' );
	$new_columns['prenom']     = __( 'Prénom', 'cgt' );
	$new_columns['expertise']  = __( 'Expertise', 'cgt' );
	$new_columns['telephone']  = __( 'Téléphone', 'cgt' );
	$new_columns['email']      = __( 'Email', 'cgt' );
	$new_columns['date']       = $columns['date'];
	return $new_columns;
}
add_filter( 'manage_cgt_expert_posts_columns', 'cgt_expert_admin_columns' );

/**
 * Populate custom admin columns
 */
function cgt_expert_admin_column_content( $column, $post_id ) {
	switch ( $column ) {
		case 'thumbnail':
			if ( has_post_thumbnail( $post_id ) ) {
				echo get_the_post_thumbnail( $post_id, array( 50, 50 ) );
			} else {
				echo '<div style="width:50px;height:50px;background:#ddd;"></div>';
			}
			break;
		case 'prenom':
			echo esc_html( get_post_meta( $post_id, '_expert_prenom', true ) );
			break;
		case 'expertise':
			$terms = get_the_terms( $post_id, 'expertise' );
			if ( $terms && ! is_wp_error( $terms ) ) {
				$expertise_names = array();
				foreach ( $terms as $term ) {
					$expertise_names[] = $term->name;
				}
				echo esc_html( implode( ', ', $expertise_names ) );
			}
			break;
		case 'telephone':
			echo esc_html( get_post_meta( $post_id, '_expert_telephone', true ) );
			break;
		case 'email':
			$email = get_post_meta( $post_id, '_expert_email', true );
			if ( $email ) {
				echo '<a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a>';
			}
			break;
	}
}
add_action( 'manage_cgt_expert_posts_custom_column', 'cgt_expert_admin_column_content', 10, 2 );

/**
 * Register Enquêtes (Surveys) Custom Post Type
 */
function cgt_register_enquetes_cpt() {
	register_post_type(
		'cgt_enquete',
		array(
			'label'               => __( 'Enquêtes', 'cgt' ),
			'labels'              => array(
				'name'               => __( 'Enquêtes', 'cgt' ),
				'singular_name'      => __( 'Enquête', 'cgt' ),
				'add_new'            => __( 'Ajouter une enquête', 'cgt' ),
				'add_new_item'       => __( 'Ajouter une nouvelle enquête', 'cgt' ),
				'edit_item'          => __( 'Modifier l\'enquête', 'cgt' ),
				'all_items'          => __( 'Toutes les enquêtes', 'cgt' ),
				'view_item'          => __( 'Voir l\'enquête', 'cgt' ),
				'search_items'       => __( 'Rechercher une enquête', 'cgt' ),
				'not_found'          => __( 'Aucune enquête trouvée', 'cgt' ),
			),
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'menu_icon'           => 'dashicons-chart-bar',
			'menu_position'       => 26,
			'supports'            => array( 'title', 'editor' ),
			'show_in_rest'        => true,
			'has_archive'         => true,
			'publicly_queryable'  => true,
			'capability_type'     => 'post',
			'map_meta_cap'        => true,
			'rewrite'             => array(
				'slug'       => 'enquetes',
				'with_front' => false,
			),
		)
	);
}
add_action( 'init', 'cgt_register_enquetes_cpt' );

/**
 * Flush rewrite rules when enquetes CPT is registered
 */
function cgt_enquetes_flush_rewrite_rules() {
	$flag = get_option( 'cgt_enquetes_flush_rewrite_rules_flag' );
	if ( $flag ) {
		flush_rewrite_rules();
		delete_option( 'cgt_enquetes_flush_rewrite_rules_flag' );
	}
}
add_action( 'init', 'cgt_enquetes_flush_rewrite_rules', 20 );

/**
 * Set flag to flush rewrite rules
 */
function cgt_enquetes_set_flush_flag() {
	update_option( 'cgt_enquetes_flush_rewrite_rules_flag', true );
}
add_action( 'after_switch_theme', 'cgt_enquetes_set_flush_flag' );

/**
 * Add meta box for survey questions
 */
function cgt_enquete_add_meta_boxes() {
	add_meta_box(
		'cgt_enquete_questions',
		__( 'Questions de l\'enquête', 'cgt' ),
		'cgt_enquete_questions_meta_box_callback',
		'cgt_enquete',
		'normal',
		'high'
	);

	add_meta_box(
		'cgt_enquete_settings',
		__( 'Paramètres', 'cgt' ),
		'cgt_enquete_settings_meta_box_callback',
		'cgt_enquete',
		'side',
		'default'
	);

	add_meta_box(
		'cgt_enquete_results',
		__( 'Résultats', 'cgt' ),
		'cgt_enquete_results_meta_box_callback',
		'cgt_enquete',
		'side',
		'default'
	);
}
add_action( 'add_meta_boxes', 'cgt_enquete_add_meta_boxes' );

/**
 * Questions meta box callback
 */
function cgt_enquete_questions_meta_box_callback( $post ) {
	wp_nonce_field( 'cgt_enquete_questions_meta_box', 'cgt_enquete_questions_meta_box_nonce' );

	$questions = get_post_meta( $post->ID, '_enquete_questions', true );
	if ( ! is_array( $questions ) ) {
		$questions = array();
	}
	?>
	<div class="cgt-enquete-questions-wrapper">
		<style>
			.cgt-enquete-questions-wrapper {
				padding: 15px;
				background: white;
			}
			.cgt-enquete-intro {
				background: linear-gradient(135deg, #e30613 0%, #b00510 100%);
				color: white;
				padding: 20px;
				border-radius: 8px;
				margin-bottom: 20px;
				box-shadow: 0 2px 8px rgba(227, 6, 19, 0.2);
			}
			.cgt-enquete-intro h3 {
				margin: 0 0 10px;
				font-size: 18px;
				color: white;
			}
			.cgt-enquete-intro p {
				margin: 0;
				font-size: 14px;
				opacity: 0.95;
			}
			.cgt-enquete-question {
				background: #f9f9f9;
				border: 2px solid #e5e7eb;
				padding: 20px;
				margin-bottom: 20px;
				border-radius: 8px;
				transition: all 0.2s;
				box-shadow: 0 1px 3px rgba(0,0,0,0.05);
			}
			.cgt-enquete-question:hover {
				border-color: #e30613;
				box-shadow: 0 4px 12px rgba(0,0,0,0.1);
			}
			.cgt-enquete-question-header {
				display: flex;
				justify-content: space-between;
				align-items: center;
				margin-bottom: 15px;
				padding-bottom: 10px;
				border-bottom: 2px solid #e5e7eb;
			}
			.cgt-enquete-question-title {
				font-weight: 600;
				font-size: 15px;
				color: #e30613;
				text-transform: uppercase;
				letter-spacing: 0.5px;
			}
			.cgt-enquete-question input[type="text"] {
				width: 100%;
				padding: 10px 12px;
				margin-bottom: 15px;
				border: 2px solid #e5e7eb;
				border-radius: 6px;
				font-size: 14px;
				transition: border-color 0.2s;
			}
			.cgt-enquete-question input[type="text"]:focus {
				outline: none;
				border-color: #e30613;
				box-shadow: 0 0 0 3px rgba(227, 6, 19, 0.1);
			}
			.cgt-enquete-option {
				display: flex;
				gap: 10px;
				margin-bottom: 10px;
				align-items: center;
			}
			.cgt-enquete-option input {
				flex: 1;
				padding: 8px 10px;
				border: 1px solid #e5e7eb;
				border-radius: 6px;
				transition: border-color 0.2s;
			}
			.cgt-enquete-option input:focus {
				outline: none;
				border-color: #e30613;
			}
			.cgt-enquete-options {
				margin-left: 20px;
				margin-top: 15px;
				background: white;
				padding: 15px;
				border-radius: 6px;
				border: 1px solid #e5e7eb;
			}
			.cgt-enquete-options strong {
				display: block;
				margin-bottom: 10px;
				color: #374151;
				font-size: 13px;
			}
			.btn-add-option, .btn-remove-option, .btn-remove-question {
				padding: 8px 16px;
				background: #2271b1;
				color: white;
				border: none;
				border-radius: 6px;
				cursor: pointer;
				font-size: 13px;
				font-weight: 500;
				transition: all 0.2s;
				box-shadow: 0 1px 2px rgba(0,0,0,0.1);
			}
			.btn-add-option:hover {
				background: #135e96;
				transform: translateY(-1px);
				box-shadow: 0 4px 6px rgba(0,0,0,0.15);
			}
			.btn-remove-question {
				background: #dc3545;
			}
			.btn-remove-question:hover {
				background: #c82333;
			}
			.btn-remove-option {
				background: #dc3545;
				padding: 6px 12px;
				font-size: 16px;
				line-height: 1;
			}
			.btn-remove-option:hover {
				background: #c82333;
			}
			.btn-add-question {
				padding: 12px 24px;
				background: linear-gradient(135deg, #00a32a 0%, #008a24 100%);
				color: white;
				border: none;
				border-radius: 8px;
				cursor: pointer;
				font-size: 15px;
				font-weight: 600;
				margin-top: 15px;
				width: 100%;
				display: flex;
				align-items: center;
				justify-content: center;
				gap: 8px;
				transition: all 0.2s;
				box-shadow: 0 2px 8px rgba(0, 163, 42, 0.3);
			}
			.btn-add-question:hover {
				transform: translateY(-2px);
				box-shadow: 0 4px 12px rgba(0, 163, 42, 0.4);
			}
			.btn-add-question::before {
				content: '+';
				font-size: 20px;
				font-weight: bold;
			}
			.cgt-enquete-empty-state {
				text-align: center;
				padding: 40px 20px;
				background: #f9fafb;
				border: 2px dashed #d1d5db;
				border-radius: 8px;
				margin-bottom: 20px;
			}
			.cgt-enquete-empty-state svg {
				color: #9ca3af;
				margin-bottom: 15px;
			}
			.cgt-enquete-empty-state p {
				color: #6b7280;
				font-size: 14px;
				margin: 0;
			}
		</style>

		<!-- Introduction Banner -->
		<?php if ( empty( $questions ) ) : ?>
			<div class="cgt-enquete-intro">
				<h3>✨ Créez votre première enquête</h3>
				<p>Ajoutez des questions avec plusieurs options de réponse. Les utilisateurs pourront voter et vous verrez les résultats en temps réel.</p>
			</div>
			<div class="cgt-enquete-empty-state">
				<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
					<path d="M9 11H3v2h6m1 0a1 1 0 001 1h2a1 1 0 001-1m-4 0V9a1 1 0 011-1h2a1 1 0 011 1v2m0 0h6v2h-6"></path>
					<path d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
				</svg>
				<p>Aucune question ajoutée. Cliquez sur le bouton ci-dessous pour commencer.</p>
			</div>
		<?php endif; ?>

		<div id="enquete-questions-container">
			<?php if ( ! empty( $questions ) ) : ?>
				<?php foreach ( $questions as $index => $question ) : ?>
					<div class="cgt-enquete-question" data-index="<?php echo esc_attr( $index ); ?>">
						<div class="cgt-enquete-question-header">
							<span class="cgt-enquete-question-title"><?php echo esc_html( sprintf( __( 'Question %d', 'cgt' ), $index + 1 ) ); ?></span>
							<button type="button" class="btn-remove-question" onclick="removeQuestion(this)"><?php esc_html_e( 'Supprimer', 'cgt' ); ?></button>
						</div>
						<input type="text" name="enquete_questions[<?php echo esc_attr( $index ); ?>][question]" value="<?php echo esc_attr( $question['question'] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'Tapez votre question ici...', 'cgt' ); ?>" required>

						<div class="cgt-enquete-options">
							<strong><?php esc_html_e( 'Options de réponse :', 'cgt' ); ?></strong>
							<div class="options-list">
								<?php if ( ! empty( $question['options'] ) ) : ?>
									<?php foreach ( $question['options'] as $opt_index => $option ) : ?>
										<div class="cgt-enquete-option">
											<input type="text" name="enquete_questions[<?php echo esc_attr( $index ); ?>][options][]" value="<?php echo esc_attr( $option ); ?>" placeholder="<?php esc_attr_e( 'Option de réponse', 'cgt' ); ?>" required>
											<button type="button" class="btn-remove-option" onclick="removeOption(this)">×</button>
										</div>
									<?php endforeach; ?>
								<?php endif; ?>
							</div>
							<button type="button" class="btn-add-option" onclick="addOption(this)"><?php esc_html_e( '+ Ajouter une option', 'cgt' ); ?></button>
						</div>
					</div>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>

		<button type="button" class="btn-add-question" onclick="addQuestion()"><?php esc_html_e( '+ Ajouter une question', 'cgt' ); ?></button>
	</div>

	<script>
		let questionIndex = <?php echo count( $questions ); ?>;

		function addQuestion() {
			const container = document.getElementById('enquete-questions-container');
			const questionDiv = document.createElement('div');
			questionDiv.className = 'cgt-enquete-question';
			questionDiv.setAttribute('data-index', questionIndex);

			questionDiv.innerHTML = `
				<div class="cgt-enquete-question-header">
					<span class="cgt-enquete-question-title"><?php esc_html_e( 'Question', 'cgt' ); ?> ${questionIndex + 1}</span>
					<button type="button" class="btn-remove-question" onclick="removeQuestion(this)"><?php esc_html_e( 'Supprimer', 'cgt' ); ?></button>
				</div>
				<input type="text" name="enquete_questions[${questionIndex}][question]" placeholder="<?php esc_attr_e( 'Tapez votre question ici...', 'cgt' ); ?>" required>
				<div class="cgt-enquete-options">
					<strong><?php esc_html_e( 'Options de réponse :', 'cgt' ); ?></strong>
					<div class="options-list">
						<div class="cgt-enquete-option">
							<input type="text" name="enquete_questions[${questionIndex}][options][]" placeholder="<?php esc_attr_e( 'Option de réponse', 'cgt' ); ?>" required>
							<button type="button" class="btn-remove-option" onclick="removeOption(this)">×</button>
						</div>
					</div>
					<button type="button" class="btn-add-option" onclick="addOption(this)"><?php esc_html_e( '+ Ajouter une option', 'cgt' ); ?></button>
				</div>
			`;

			container.appendChild(questionDiv);
			questionIndex++;
		}

		function removeQuestion(button) {
			if (confirm('<?php esc_html_e( 'Êtes-vous sûr de vouloir supprimer cette question ?', 'cgt' ); ?>')) {
				button.closest('.cgt-enquete-question').remove();
			}
		}

		function addOption(button) {
			const optionsList = button.previousElementSibling;
			const questionDiv = button.closest('.cgt-enquete-question');
			const qIndex = questionDiv.getAttribute('data-index');

			const optionDiv = document.createElement('div');
			optionDiv.className = 'cgt-enquete-option';
			optionDiv.innerHTML = `
				<input type="text" name="enquete_questions[${qIndex}][options][]" placeholder="<?php esc_attr_e( 'Option de réponse', 'cgt' ); ?>" required>
				<button type="button" class="btn-remove-option" onclick="removeOption(this)">×</button>
			`;

			optionsList.appendChild(optionDiv);
		}

		function removeOption(button) {
			const optionsList = button.closest('.options-list');
			if (optionsList.children.length > 1) {
				button.closest('.cgt-enquete-option').remove();
			} else {
				alert('<?php esc_html_e( 'Une question doit avoir au moins une option de réponse.', 'cgt' ); ?>');
			}
		}
	</script>
	<?php
}

/**
 * Settings meta box callback
 */
function cgt_enquete_settings_meta_box_callback( $post ) {
	wp_nonce_field( 'cgt_enquete_settings_meta_box', 'cgt_enquete_settings_meta_box_nonce' );

	$date_fin = get_post_meta( $post->ID, '_enquete_date_fin', true );
	$actif = get_post_meta( $post->ID, '_enquete_active', true );
	$multiple_votes = get_post_meta( $post->ID, '_enquete_multiple_votes', true );
	?>
	<style>
		.cgt-settings-box {
			padding: 15px;
		}
		.cgt-settings-box label {
			display: flex;
			align-items: center;
			gap: 8px;
			padding: 12px;
			background: #f9fafb;
			border-radius: 6px;
			margin-bottom: 10px;
			cursor: pointer;
			transition: background 0.2s;
		}
		.cgt-settings-box label:hover {
			background: #f3f4f6;
		}
		.cgt-settings-box input[type="checkbox"] {
			width: 18px;
			height: 18px;
			cursor: pointer;
		}
		.cgt-settings-box .date-field {
			margin-top: 10px;
			padding: 12px;
			background: #f9fafb;
			border-radius: 6px;
		}
		.cgt-settings-box .date-field label {
			display: block;
			margin-bottom: 8px;
			font-weight: 600;
			color: #374151;
		}
		.cgt-settings-box input[type="date"] {
			width: 100%;
			padding: 8px;
			border: 2px solid #e5e7eb;
			border-radius: 6px;
			font-size: 14px;
		}
		.cgt-settings-box input[type="date"]:focus {
			outline: none;
			border-color: #e30613;
		}
		.cgt-view-public-btn {
			display: block;
			width: 100%;
			padding: 12px;
			background: linear-gradient(135deg, #e30613 0%, #b00510 100%);
			color: white;
			text-align: center;
			text-decoration: none;
			border-radius: 6px;
			font-weight: 600;
			margin-top: 15px;
			transition: all 0.2s;
			box-shadow: 0 2px 8px rgba(227, 6, 19, 0.3);
		}
		.cgt-view-public-btn:hover {
			transform: translateY(-2px);
			box-shadow: 0 4px 12px rgba(227, 6, 19, 0.4);
			color: white;
		}
		.cgt-view-public-btn svg {
			display: inline-block;
			vertical-align: middle;
			margin-left: 5px;
		}
	</style>
	<div class="cgt-settings-box">
		<label>
			<input type="checkbox" name="enquete_active" value="1" <?php checked( $actif, '1' ); ?>>
			<strong><?php esc_html_e( 'Enquête active', 'cgt' ); ?></strong>
		</label>

		<label>
			<input type="checkbox" name="enquete_multiple_votes" value="1" <?php checked( $multiple_votes, '1' ); ?>>
			<strong><?php esc_html_e( 'Autoriser plusieurs votes', 'cgt' ); ?></strong>
		</label>

		<div class="date-field">
			<label for="enquete_date_fin"><?php esc_html_e( 'Date de fin (optionnel)', 'cgt' ); ?></label>
			<input type="date" id="enquete_date_fin" name="enquete_date_fin" value="<?php echo esc_attr( $date_fin ); ?>">
		</div>

		<?php if ( $post->post_status === 'publish' ) : ?>
			<a href="<?php echo esc_url( get_permalink( $post->ID ) ); ?>" class="cgt-view-public-btn" target="_blank">
				<?php esc_html_e( 'Voir l\'enquête publique', 'cgt' ); ?>
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
					<path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
					<polyline points="15 3 21 3 21 9"></polyline>
					<line x1="10" y1="14" x2="21" y2="3"></line>
				</svg>
			</a>
		<?php else : ?>
			<p style="text-align: center; color: #6b7280; font-size: 13px; margin-top: 15px; padding: 12px; background: #f9fafb; border-radius: 6px;">
				<?php esc_html_e( 'Publiez l\'enquête pour voir le lien public', 'cgt' ); ?>
			</p>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Results meta box callback
 */
function cgt_enquete_results_meta_box_callback( $post ) {
	$questions = get_post_meta( $post->ID, '_enquete_questions', true );
	$results = get_post_meta( $post->ID, '_enquete_results', true );

	if ( empty( $questions ) ) {
		echo '<p style="padding: 10px;">' . esc_html__( 'Aucune question définie.', 'cgt' ) . '</p>';
		return;
	}

	if ( ! is_array( $results ) ) {
		$results = array();
	}

	$total_votes = 0;
	foreach ( $results as $question_results ) {
		if ( is_array( $question_results ) ) {
			$total_votes += array_sum( $question_results );
		}
	}
	?>
	<div style="padding: 10px;">
		<p><strong><?php echo esc_html( sprintf( __( 'Total des votes : %d', 'cgt' ), $total_votes ) ); ?></strong></p>

		<?php foreach ( $questions as $q_index => $question ) : ?>
			<div style="margin-bottom: 15px; padding: 10px; background: #f9f9f9; border-radius: 4px;">
				<strong><?php echo esc_html( $question['question'] ); ?></strong>
				<?php if ( ! empty( $question['options'] ) ) : ?>
					<ul style="margin: 10px 0 0 0; padding: 0; list-style: none;">
						<?php foreach ( $question['options'] as $opt_index => $option ) : ?>
							<?php $votes = isset( $results[ $q_index ][ $opt_index ] ) ? (int) $results[ $q_index ][ $opt_index ] : 0; ?>
							<li style="margin: 5px 0; padding: 5px; background: white; border-left: 3px solid #2271b1;">
								<?php echo esc_html( $option ); ?>: <strong><?php echo esc_html( $votes ); ?></strong> vote<?php echo $votes > 1 ? 's' : ''; ?>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>

		<p style="margin-top: 15px;">
			<a href="<?php echo esc_url( get_permalink( $post->ID ) ); ?>" class="button" target="_blank"><?php esc_html_e( 'Voir l\'enquête publique', 'cgt' ); ?></a>
		</p>
	</div>
	<?php
}

/**
 * Save enquete meta boxes data
 */
function cgt_save_enquete_meta_boxes( $post_id ) {
	// Check nonces
	if ( ! isset( $_POST['cgt_enquete_questions_meta_box_nonce'] ) ||
	     ! wp_verify_nonce( $_POST['cgt_enquete_questions_meta_box_nonce'], 'cgt_enquete_questions_meta_box' ) ) {
		return;
	}

	if ( ! isset( $_POST['cgt_enquete_settings_meta_box_nonce'] ) ||
	     ! wp_verify_nonce( $_POST['cgt_enquete_settings_meta_box_nonce'], 'cgt_enquete_settings_meta_box' ) ) {
		return;
	}

	// Check autosave
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	// Check permissions
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	// Save questions
	if ( isset( $_POST['enquete_questions'] ) && is_array( $_POST['enquete_questions'] ) ) {
		$questions = array();
		foreach ( $_POST['enquete_questions'] as $question_data ) {
			if ( ! empty( $question_data['question'] ) && ! empty( $question_data['options'] ) ) {
				$questions[] = array(
					'question' => sanitize_text_field( $question_data['question'] ),
					'options'  => array_map( 'sanitize_text_field', $question_data['options'] ),
				);
			}
		}
		update_post_meta( $post_id, '_enquete_questions', $questions );
	}

	// Save settings
	update_post_meta( $post_id, '_enquete_active', isset( $_POST['enquete_active'] ) ? '1' : '0' );
	update_post_meta( $post_id, '_enquete_multiple_votes', isset( $_POST['enquete_multiple_votes'] ) ? '1' : '0' );

	if ( isset( $_POST['enquete_date_fin'] ) ) {
		update_post_meta( $post_id, '_enquete_date_fin', sanitize_text_field( $_POST['enquete_date_fin'] ) );
	}
}
add_action( 'save_post_cgt_enquete', 'cgt_save_enquete_meta_boxes' );

/**
 * Add custom admin columns for enquetes
 */
function cgt_enquete_admin_columns( $columns ) {
	$new_columns = array();
	$new_columns['cb']     = $columns['cb'];
	$new_columns['title']  = $columns['title'];
	$new_columns['active'] = __( 'Active', 'cgt' );
	$new_columns['votes']  = __( 'Votes', 'cgt' );
	$new_columns['date']   = $columns['date'];
	return $new_columns;
}
add_filter( 'manage_cgt_enquete_posts_columns', 'cgt_enquete_admin_columns' );

/**
 * Populate custom admin columns for enquetes
 */
function cgt_enquete_admin_column_content( $column, $post_id ) {
	switch ( $column ) {
		case 'active':
			$actif = get_post_meta( $post_id, '_enquete_active', true );
			if ( $actif === '1' ) {
				echo '<span style="color: #00a32a;">✓ ' . esc_html__( 'Active', 'cgt' ) . '</span>';
			} else {
				echo '<span style="color: #999;">✗ ' . esc_html__( 'Inactive', 'cgt' ) . '</span>';
			}
			break;
		case 'votes':
			$results = get_post_meta( $post_id, '_enquete_results', true );
			$total_votes = 0;
			if ( is_array( $results ) ) {
				foreach ( $results as $question_results ) {
					if ( is_array( $question_results ) ) {
						$total_votes += array_sum( $question_results );
					}
				}
			}
			echo '<strong>' . esc_html( $total_votes ) . '</strong>';
			break;
	}
}
add_action( 'manage_cgt_enquete_posts_custom_column', 'cgt_enquete_admin_column_content', 10, 2 );

/**
 * AJAX handler for survey votes
 */
function cgt_enquete_handle_vote() {
	// Check nonce
	if ( ! isset( $_POST['nonce'] ) || ! isset( $_POST['post_id'] ) ) {
		wp_send_json_error( array( 'message' => __( 'Données manquantes.', 'cgt' ) ) );
	}

	$post_id = absint( $_POST['post_id'] );

	if ( ! wp_verify_nonce( $_POST['nonce'], 'cgt_enquete_vote_' . $post_id ) ) {
		wp_send_json_error( array( 'message' => __( 'Erreur de sécurité.', 'cgt' ) ) );
	}

	// Check if enquete exists and is active
	$enquete = get_post( $post_id );
	if ( ! $enquete || $enquete->post_type !== 'cgt_enquete' ) {
		wp_send_json_error( array( 'message' => __( 'Enquête introuvable.', 'cgt' ) ) );
	}

	$actif = get_post_meta( $post_id, '_enquete_active', true );
	if ( $actif !== '1' ) {
		wp_send_json_error( array( 'message' => __( 'Cette enquête n\'est plus active.', 'cgt' ) ) );
	}

	// Check date limit
	$date_fin = get_post_meta( $post_id, '_enquete_date_fin', true );
	if ( $date_fin && strtotime( $date_fin ) < time() ) {
		wp_send_json_error( array( 'message' => __( 'Cette enquête est terminée.', 'cgt' ) ) );
	}

	// Check if multiple votes allowed
	$multiple_votes = get_post_meta( $post_id, '_enquete_multiple_votes', true );
	if ( $multiple_votes !== '1' ) {
		$vote_cookie_name = 'cgt_enquete_' . $post_id . '_voted';
		if ( isset( $_COOKIE[ $vote_cookie_name ] ) ) {
			wp_send_json_error( array( 'message' => __( 'Vous avez déjà voté pour cette enquête.', 'cgt' ) ) );
		}
	}

	// Get votes from request
	if ( ! isset( $_POST['votes'] ) || ! is_array( $_POST['votes'] ) ) {
		wp_send_json_error( array( 'message' => __( 'Aucune réponse fournie.', 'cgt' ) ) );
	}

	$votes = array_map( 'absint', $_POST['votes'] );

	// Get existing results
	$results = get_post_meta( $post_id, '_enquete_results', true );
	if ( ! is_array( $results ) ) {
		$results = array();
	}

	// Update results
	foreach ( $votes as $question_index => $option_index ) {
		$question_index = absint( $question_index );
		$option_index = absint( $option_index );

		if ( ! isset( $results[ $question_index ] ) ) {
			$results[ $question_index ] = array();
		}

		if ( ! isset( $results[ $question_index ][ $option_index ] ) ) {
			$results[ $question_index ][ $option_index ] = 0;
		}

		$results[ $question_index ][ $option_index ]++;
	}

	// Save updated results
	update_post_meta( $post_id, '_enquete_results', $results );

	// Set cookie to prevent multiple votes (if not allowed)
	if ( $multiple_votes !== '1' ) {
		setcookie(
			'cgt_enquete_' . $post_id . '_voted',
			'1',
			time() + ( 365 * 24 * 60 * 60 ), // 1 year
			COOKIEPATH,
			COOKIE_DOMAIN,
			is_ssl(),
			true // httponly
		);
	}

	wp_send_json_success(
		array(
			'message' => __( 'Merci pour votre participation !', 'cgt' ),
			'results' => $results,
		)
	);
}
add_action( 'wp_ajax_cgt_enquete_vote', 'cgt_enquete_handle_vote' );
add_action( 'wp_ajax_nopriv_cgt_enquete_vote', 'cgt_enquete_handle_vote' );

