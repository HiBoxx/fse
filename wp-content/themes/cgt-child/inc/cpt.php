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

	register_post_type(
		'cgt_enquete',
		array(
			'label'               => __( 'Enquêtes', 'cgt' ),
			'labels'              => array(
				'name'          => __( 'Enquêtes', 'cgt' ),
				'singular_name' => __( 'Enquête', 'cgt' ),
				'add_new'       => __( 'Créer une enquête', 'cgt' ),
				'add_new_item'  => __( 'Ajouter une nouvelle enquête', 'cgt' ),
				'all_items'     => __( 'Toutes les enquêtes', 'cgt' ),
			),
			'public'              => true,
			'publicly_queryable'  => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_rest'        => true,
			'has_archive'         => true,
			'menu_icon'           => 'dashicons-chart-pie',
			'supports'            => array( 'title', 'editor', 'excerpt', 'thumbnail', 'author' ),
			'rewrite'             => array(
				'slug'       => 'enquetes',
				'with_front' => false,
			),
		)
	);

	register_post_type(
		'cgt_enquete_response',
		array(
			'label'               => __( 'Réponses d’enquête', 'cgt' ),
			'public'              => false,
			'show_ui'             => false,
			'show_in_menu'        => false,
			'show_in_rest'        => false,
			'capability_type'     => 'post',
			'supports'            => array( 'title', 'author' ),
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

	register_post_meta(
		'cgt_enquete',
		'cgt_enquete_questions',
		array(
			'type'              => 'string',
			'single'            => true,
			'sanitize_callback' => 'cgt_sanitize_enquete_meta_string',
			'show_in_rest'      => false,
			'auth_callback'     => $auth_callback,
		)
	);

	register_post_meta(
		'cgt_enquete',
		'cgt_enquete_pdf_id',
		array(
			'type'              => 'integer',
			'single'            => true,
			'sanitize_callback' => 'absint',
			'show_in_rest'      => true,
			'auth_callback'     => $auth_callback,
		)
	);

	register_post_meta(
		'cgt_enquete',
		'cgt_enquete_total_responses',
		array(
			'type'              => 'integer',
			'single'            => true,
			'default'           => 0,
			'sanitize_callback' => 'absint',
			'show_in_rest'      => true,
			'auth_callback'     => $auth_callback,
		)
	);

	register_post_meta(
		'cgt_enquete',
		'cgt_enquete_last_response',
		array(
			'type'              => 'string',
			'single'            => true,
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
			'auth_callback'     => $auth_callback,
		)
	);

	register_post_meta(
		'cgt_enquete_response',
		'cgt_enquete_id',
		array(
			'type'              => 'integer',
			'single'            => true,
			'sanitize_callback' => 'absint',
			'show_in_rest'      => false,
			'auth_callback'     => $auth_callback,
		)
	);

	register_post_meta(
		'cgt_enquete_response',
		'cgt_enquete_user_id',
		array(
			'type'              => 'integer',
			'single'            => true,
			'sanitize_callback' => 'absint',
			'show_in_rest'      => false,
			'auth_callback'     => $auth_callback,
		)
	);

	register_post_meta(
		'cgt_enquete_response',
		'cgt_enquete_answers',
		array(
			'type'              => 'string',
			'single'            => true,
			'sanitize_callback' => 'cgt_sanitize_enquete_meta_string',
			'show_in_rest'      => false,
			'auth_callback'     => $auth_callback,
		)
	);

	register_post_meta(
		'cgt_enquete_response',
		'cgt_enquete_branch',
		array(
			'type'              => 'string',
			'single'            => true,
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => false,
			'auth_callback'     => $auth_callback,
		)
	);
}
add_action( 'init', 'cgt_register_meta_fields' );

/**
 * Sanitize JSON meta stored as string for enquêtes.
 *
 * @param mixed $value Meta value.
 * @return string
 */
function cgt_sanitize_enquete_meta_string( $value ) {
	if ( is_array( $value ) || is_object( $value ) ) {
		$value = wp_json_encode( $value );
	}

	if ( ! is_string( $value ) ) {
		return '';
	}

	return wp_kses_post( $value );
}

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
