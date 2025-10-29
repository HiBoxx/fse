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
			'public'              => false,
			'publicly_queryable'  => false,
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

	if ( isset( $postarr['post_type'] ) && 'cgt_agenda' === $postarr['post_type'] ) {
		$data['post_status'] = 'private';
	}

	return $data;
}
add_filter( 'wp_insert_post_data', 'cgt_force_private_articles', 10, 2 );
