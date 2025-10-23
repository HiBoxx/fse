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
	<table class="form-table">
		<tbody>
			<tr>
				<th scope="row"><label for="cgt_event_date"><?php esc_html_e( 'Date et heure', 'cgt' ); ?></label></th>
				<td>
					<input type="datetime-local" id="cgt_event_date" name="cgt_event_date" value="<?php echo esc_attr( $datetime_attr ); ?>" class="regular-text">
					<p class="description"><?php esc_html_e( 'Sélectionnez la date et l’heure de l’événement.', 'cgt' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="cgt_event_address"><?php esc_html_e( 'Adresse', 'cgt' ); ?></label></th>
				<td>
					<textarea id="cgt_event_address" name="cgt_event_address" rows="3" class="large-text"><?php echo esc_textarea( $address_value ); ?></textarea>
					<p class="description"><?php esc_html_e( 'Lieu de l’événement (adresse, salle, indications).', 'cgt' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="cgt_event_document"><?php esc_html_e( 'Document (PDF ou image)', 'cgt' ); ?></label></th>
				<td>
					<?php if ( $current_doc_url ) : ?>
						<p><a href="<?php echo esc_url( $current_doc_url ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Document actuel', 'cgt' ); ?></a></p>
					<?php endif; ?>
					<input type="file" id="cgt_event_document" name="cgt_event_document" accept=".pdf,image/*">
					<?php if ( $document_value ) : ?>
						<p><label><input type="checkbox" name="cgt_event_document_remove" value="1"> <?php esc_html_e( 'Supprimer le document associé', 'cgt' ); ?></label></p>
					<?php endif; ?>
				</td>
			</tr>
		</tbody>
	</table>
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
