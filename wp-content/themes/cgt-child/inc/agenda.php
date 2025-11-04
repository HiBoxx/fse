<?php
/**
 * Admin helpers for agenda events.
 *
 * @package CGT_Child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Add meta boxes for agenda events.
 */
function cgt_agenda_register_metaboxes() {
	add_meta_box(
		'cgt-agenda-details',
		__( 'Détails de l’événement', 'cgt' ),
		'cgt_agenda_render_metabox',
		'cgt_agenda',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes_cgt_agenda', 'cgt_agenda_register_metaboxes' );

/**
 * Enqueue custom admin styles for agenda screens.
 */
function cgt_agenda_enqueue_admin_assets( $hook_suffix ) {
	$screen = get_current_screen();
	if ( ! $screen || 'cgt_agenda' !== $screen->post_type ) {
		return;
	}

	wp_enqueue_style(
		'cgt-admin-agenda',
		get_stylesheet_directory_uri() . '/assets/css/admin-agenda.css',
		array(),
		CGT_CHILD_VERSION
	);
}
add_action( 'admin_enqueue_scripts', 'cgt_agenda_enqueue_admin_assets' );

/**
 * Custom hero area on agenda edit screens.
 *
 * @param WP_Post $post Post object.
 */
function cgt_agenda_edit_form_intro( $post ) {
	if ( ! $post || 'cgt_agenda' !== $post->post_type ) {
		return;
	}

	?>
	<div class="cgt-agenda-hero">
		<div>
			<h1><?php esc_html_e( 'Nouvel événement / Édition', 'cgt' ); ?></h1>
			<p><?php esc_html_e( 'Publiez un rendez-vous syndical clair et complet : date, lieu, informations pratiques et documents associés.', 'cgt' ); ?></p>
		</div>
		<div class="hero-icon">🗓️</div>
	</div>
	<?php
}
add_action( 'edit_form_top', 'cgt_agenda_edit_form_intro' );

/**
 * Customize the default title placeholder for agenda events.
 *
 * @param string $placeholder Default placeholder text.
 *
 * @return string
 */
function cgt_agenda_title_placeholder( $placeholder ) {
	$screen = get_current_screen();
	if ( $screen && 'cgt_agenda' === $screen->post_type ) {
		$placeholder = __( 'Titre de l’événement (ex. Assemblée générale des ingénieur·es)', 'cgt' );
	}

	return $placeholder;
}
add_filter( 'enter_title_here', 'cgt_agenda_title_placeholder' );

/**
 * Render agenda metabox.
 *
 * @param WP_Post $post Current post.
 */
function cgt_agenda_render_metabox( $post ) {
	wp_nonce_field( 'cgt_save_agenda', 'cgt_agenda_nonce' );

	$date_value     = get_post_meta( $post->ID, 'cgt_event_date', true );
	$address_value  = get_post_meta( $post->ID, 'cgt_event_address', true );
	$document_value = get_post_meta( $post->ID, 'cgt_event_document', true );

	// Prepare value for datetime-local input.
	$datetime_attr = '';
	if ( ! empty( $date_value ) ) {
		$timestamp = strtotime( $date_value );
		if ( $timestamp ) {
			$datetime_attr = gmdate( 'Y-m-d\TH:i', $timestamp );
		}
	}

	$current_doc_url = '';
	if ( $document_value ) {
		$current_doc_url = wp_get_attachment_url( $document_value );
	}
	?>
	<div class="cgt-agenda-meta">
		<div class="cgt-agenda-meta__field cgt-agenda-meta__field--half">
			<label for="cgt_event_date">
				<span class="cgt-agenda-meta__label"><?php esc_html_e( 'Date et heure', 'cgt' ); ?></span>
			</label>
			<input
				type="datetime-local"
				id="cgt_event_date"
				name="cgt_event_date"
				value="<?php echo esc_attr( $datetime_attr ); ?>"
				class="cgt-agenda-meta__input"
			>
			<p class="cgt-agenda-meta__hint"><?php esc_html_e( 'Sélectionnez la date et l’heure de l’événement.', 'cgt' ); ?></p>
		</div>

		<div class="cgt-agenda-meta__field">
			<label for="cgt_event_address">
				<span class="cgt-agenda-meta__label"><?php esc_html_e( 'Adresse / Informations pratiques', 'cgt' ); ?></span>
			</label>
			<textarea
				id="cgt_event_address"
				name="cgt_event_address"
				rows="4"
				class="cgt-agenda-meta__textarea"
				placeholder="<?php esc_attr_e( 'Salle, adresse complète, modalités d’accès…', 'cgt' ); ?>"
			><?php echo esc_textarea( $address_value ); ?></textarea>
			<p class="cgt-agenda-meta__hint"><?php esc_html_e( 'Lieu de l’événement (adresse, salle, indications).', 'cgt' ); ?></p>
		</div>

		<div class="cgt-agenda-meta__field">
			<span class="cgt-agenda-meta__label"><?php esc_html_e( 'Document associé (PDF ou image)', 'cgt' ); ?></span>

			<div class="cgt-agenda-meta__upload">
				<label class="cgt-agenda-upload__button" for="cgt_event_document">
					<span class="dashicons dashicons-upload"></span>
					<?php esc_html_e( 'Téléverser un fichier', 'cgt' ); ?>
				</label>
				<input type="file" id="cgt_event_document" name="cgt_event_document" accept=".pdf,image/*" class="cgt-agenda-upload__input">

				<?php if ( $current_doc_url ) : ?>
					<a class="cgt-agenda-upload__current" href="<?php echo esc_url( $current_doc_url ); ?>" target="_blank" rel="noopener">
						<span class="dashicons dashicons-media-default"></span>
						<?php esc_html_e( 'Voir le document actuel', 'cgt' ); ?>
					</a>
				<?php endif; ?>
			</div>

			<?php if ( $document_value ) : ?>
				<label class="cgt-agenda-meta__checkbox">
					<input type="checkbox" name="cgt_event_document_remove" value="1">
					<span><?php esc_html_e( 'Supprimer le document associé', 'cgt' ); ?></span>
				</label>
			<?php endif; ?>

			<p class="cgt-agenda-meta__hint"><?php esc_html_e( 'Formats acceptés : PDF, JPG, PNG. Taille recommandée &lt; 10 Mo.', 'cgt' ); ?></p>
		</div>
	</div>
	<?php
}

/**
 * Save agenda meta.
 *
 * @param int $post_id Post ID.
 */
function cgt_agenda_save_post( $post_id ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! isset( $_POST['cgt_agenda_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['cgt_agenda_nonce'] ), 'cgt_save_agenda' ) ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	// Date.
	if ( isset( $_POST['cgt_event_date'] ) ) {
		$raw_date = sanitize_text_field( wp_unslash( $_POST['cgt_event_date'] ) );
		if ( $raw_date ) {
			$timestamp = strtotime( $raw_date );
			if ( $timestamp ) {
				update_post_meta( $post_id, 'cgt_event_date', gmdate( 'Y-m-d H:i:s', $timestamp ) );
			}
		} else {
			delete_post_meta( $post_id, 'cgt_event_date' );
		}
	}

	// Address.
	if ( isset( $_POST['cgt_event_address'] ) ) {
		$address = sanitize_textarea_field( wp_unslash( $_POST['cgt_event_address'] ) );
		if ( $address ) {
			update_post_meta( $post_id, 'cgt_event_address', $address );
		} else {
			delete_post_meta( $post_id, 'cgt_event_address' );
		}
	}

	// Document upload.
	if ( ! empty( $_FILES['cgt_event_document']['name'] ) ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		$attachment_id = media_handle_upload( 'cgt_event_document', $post_id );
		if ( ! is_wp_error( $attachment_id ) ) {
			update_post_meta( $post_id, 'cgt_event_document', $attachment_id );
		}
	} elseif ( isset( $_POST['cgt_event_document_remove'] ) ) {
		delete_post_meta( $post_id, 'cgt_event_document' );
	}
}
add_action( 'save_post_cgt_agenda', 'cgt_agenda_save_post' );

/**
 * Migration helper: convert legacy private agenda events to public.
 */
function cgt_agenda_migrate_private_events() {
	if ( get_option( 'cgt_agenda_public_migration_done' ) ) {
		return;
	}

	if ( ! current_user_can( 'publish_posts' ) ) {
		return;
	}

	$private_events = get_posts(
		array(
			'post_type'      => 'cgt_agenda',
			'post_status'    => 'private',
			'fields'         => 'ids',
			'posts_per_page' => -1,
			'no_found_rows'  => true,
		)
	);

	if ( $private_events ) {
		foreach ( $private_events as $event_id ) {
			wp_update_post(
				array(
					'ID'          => $event_id,
					'post_status' => 'publish',
				)
			);
		}
	}

	update_option( 'cgt_agenda_public_migration_done', 1 );
}
add_action( 'admin_init', 'cgt_agenda_migrate_private_events' );
