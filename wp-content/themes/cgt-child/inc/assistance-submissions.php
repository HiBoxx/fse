<?php
/**
 * Assistance submissions handlers (articles & tracts).
 *
 * @package CGT_Child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Common helper to build assistance redirect URL.
 *
 * @param array $args Query args.
 * @return string
 */
function cgt_assistance_redirect_url( $args = array() ) {
	$assistance_page = get_page_by_path( 'assistance' );
	$base_url        = $assistance_page ? get_permalink( $assistance_page ) : home_url( '/' );

	if ( empty( $args ) ) {
		return $base_url;
	}

	return add_query_arg( $args, $base_url );
}

/**
 * Ensure the current user is allowed to submit via assistance space.
 */
function cgt_assistance_assert_permissions() {
	if ( ! is_user_logged_in() ) {
		wp_die( esc_html__( 'Vous devez être connecté.', 'cgt' ) );
	}

	$user = wp_get_current_user();
	if ( ! $user || ! in_array( 'assistance', (array) $user->roles, true ) ) {
		wp_die( esc_html__( 'Vous ne disposez pas des permissions nécessaires.', 'cgt' ) );
	}
}

/**
 * Handle assistance article submission.
 */
function cgt_assistance_handle_article_submission() {
	cgt_assistance_assert_permissions();

	if ( ! isset( $_POST['cgt_assistance_article_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['cgt_assistance_article_nonce'] ), 'cgt_assistance_article' ) ) {
		wp_safe_redirect( cgt_assistance_redirect_url( array( 'section' => 'articles', 'status' => 'nonce' ) ) );
		exit;
	}

	$title       = isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '';
	$content     = isset( $_POST['description'] ) ? wp_kses_post( wp_unslash( $_POST['description'] ) ) : '';
	$excerpt     = isset( $_POST['excerpt'] ) ? sanitize_textarea_field( wp_unslash( $_POST['excerpt'] ) ) : '';
	$branch_id   = isset( $_POST['branch'] ) ? absint( $_POST['branch'] ) : 0;
	$keywords    = isset( $_POST['keywords'] ) ? sanitize_text_field( wp_unslash( $_POST['keywords'] ) ) : '';
	$sources     = isset( $_POST['sources'] ) ? sanitize_textarea_field( wp_unslash( $_POST['sources'] ) ) : '';

	if ( empty( $title ) || empty( $content ) ) {
		wp_safe_redirect( cgt_assistance_redirect_url( array( 'section' => 'articles', 'status' => 'missing' ) ) );
		exit;
	}

	$post_id = wp_insert_post(
		array(
			'post_type'    => 'post',
			'post_status'  => 'draft',
			'post_author'  => get_current_user_id(),
			'post_title'   => $title,
			'post_content' => $content,
			'post_excerpt' => $excerpt,
		),
		true
	);

	if ( is_wp_error( $post_id ) ) {
		wp_safe_redirect( cgt_assistance_redirect_url( array( 'section' => 'articles', 'status' => 'error' ) ) );
		exit;
	}

	if ( $branch_id ) {
		wp_set_post_terms( $post_id, array( $branch_id ), 'branche', false );
	}

	if ( $keywords ) {
		$tags = array_filter(
			array_map(
				'trim',
				explode( ',', $keywords )
			)
		);
		if ( ! empty( $tags ) ) {
			wp_set_post_terms( $post_id, $tags, 'post_tag', false );
		}
	}

	if ( $sources ) {
		update_post_meta( $post_id, '_cgt_sources', $sources );
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	if ( ! empty( $_FILES['featured_image']['name'] ) ) {
		$attachment_id = media_handle_upload( 'featured_image', $post_id );
		if ( ! is_wp_error( $attachment_id ) ) {
			set_post_thumbnail( $post_id, $attachment_id );
		}
	}

	if ( ! empty( $_FILES['pdf_file']['name'] ) ) {
		$pdf_id = media_handle_upload( 'pdf_file', $post_id );
		if ( ! is_wp_error( $pdf_id ) ) {
			update_post_meta( $post_id, '_cgt_attached_pdf', $pdf_id );
		}
	}

	cgt_assistance_notify_admin(
		array(
			'type'    => 'article',
			'post_id' => $post_id,
			'title'   => $title,
		)
	);

	wp_safe_redirect( cgt_assistance_redirect_url( array( 'section' => 'articles', 'status' => 'success' ) ) );
	exit;
}
add_action( 'admin_post_cgt_assistance_article_submit', 'cgt_assistance_handle_article_submission' );

/**
 * Handle assistance tract submission.
 */
function cgt_assistance_handle_tract_submission() {
	cgt_assistance_assert_permissions();

	if ( ! isset( $_POST['cgt_assistance_tract_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['cgt_assistance_tract_nonce'] ), 'cgt_assistance_tract' ) ) {
		wp_safe_redirect( cgt_assistance_redirect_url( array( 'section' => 'tracts', 'status' => 'nonce' ) ) );
		exit;
	}

	$title       = isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '';
	$content     = isset( $_POST['description'] ) ? wp_kses_post( wp_unslash( $_POST['description'] ) ) : '';
	$excerpt     = isset( $_POST['excerpt'] ) ? sanitize_textarea_field( wp_unslash( $_POST['excerpt'] ) ) : '';
	$branch_id   = isset( $_POST['branch'] ) ? absint( $_POST['branch'] ) : 0;
	$keywords    = isset( $_POST['keywords'] ) ? sanitize_text_field( wp_unslash( $_POST['keywords'] ) ) : '';
	$sources     = isset( $_POST['sources'] ) ? sanitize_textarea_field( wp_unslash( $_POST['sources'] ) ) : '';

	if ( empty( $title ) || empty( $content ) ) {
		wp_safe_redirect( cgt_assistance_redirect_url( array( 'section' => 'tracts', 'status' => 'missing' ) ) );
		exit;
	}

	$post_id = wp_insert_post(
		array(
			'post_type'    => 'tracts',
			'post_status'  => 'draft',
			'post_author'  => get_current_user_id(),
			'post_title'   => $title,
			'post_content' => $content,
			'post_excerpt' => $excerpt,
		),
		true
	);

	if ( is_wp_error( $post_id ) ) {
		wp_safe_redirect( cgt_assistance_redirect_url( array( 'section' => 'tracts', 'status' => 'error' ) ) );
		exit;
	}

	if ( $branch_id ) {
		wp_set_post_terms( $post_id, array( $branch_id ), 'branche', false );
	}

	if ( $keywords ) {
		$tags = array_filter(
			array_map(
				'trim',
				explode( ',', $keywords )
			)
		);
		if ( ! empty( $tags ) ) {
			wp_set_post_terms( $post_id, $tags, 'post_tag', false );
		}
	}

	if ( $sources ) {
		update_post_meta( $post_id, '_cgt_sources', $sources );
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	if ( ! empty( $_FILES['featured_image']['name'] ) ) {
		$attachment_id = media_handle_upload( 'featured_image', $post_id );
		if ( ! is_wp_error( $attachment_id ) ) {
			set_post_thumbnail( $post_id, $attachment_id );
		}
	}

	if ( ! empty( $_FILES['pdf_file']['name'] ) ) {
		$pdf_id = media_handle_upload( 'pdf_file', $post_id );
		if ( ! is_wp_error( $pdf_id ) ) {
			update_post_meta( $post_id, 'cgt_fichier_pdf', wp_get_attachment_url( $pdf_id ) );
		}
	}

	cgt_assistance_notify_admin(
		array(
			'type'    => 'tract',
			'post_id' => $post_id,
			'title'   => $title,
		)
	);

	wp_safe_redirect( cgt_assistance_redirect_url( array( 'section' => 'tracts', 'status' => 'success' ) ) );
	exit;
}
add_action( 'admin_post_cgt_assistance_tract_submit', 'cgt_assistance_handle_tract_submission' );

/**
 * Handle assistance event submission.
 */
function cgt_assistance_handle_event_submission() {
	cgt_assistance_assert_permissions();

	if ( ! isset( $_POST['cgt_assistance_event_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['cgt_assistance_event_nonce'] ), 'cgt_assistance_event' ) ) {
		wp_safe_redirect( cgt_assistance_redirect_url( array( 'section' => 'events', 'status' => 'nonce' ) ) );
		exit;
	}

	$title       = isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '';
	$content     = isset( $_POST['description'] ) ? wp_kses_post( wp_unslash( $_POST['description'] ) ) : '';
	$excerpt     = isset( $_POST['excerpt'] ) ? sanitize_textarea_field( wp_unslash( $_POST['excerpt'] ) ) : '';
	$event_date  = isset( $_POST['event_date'] ) ? sanitize_text_field( wp_unslash( $_POST['event_date'] ) ) : '';
	$event_addr  = isset( $_POST['event_address'] ) ? sanitize_textarea_field( wp_unslash( $_POST['event_address'] ) ) : '';
	$branch_id   = isset( $_POST['branch'] ) ? absint( $_POST['branch'] ) : 0;
	$keywords    = isset( $_POST['keywords'] ) ? sanitize_text_field( wp_unslash( $_POST['keywords'] ) ) : '';
	$sources     = isset( $_POST['sources'] ) ? sanitize_textarea_field( wp_unslash( $_POST['sources'] ) ) : '';

	if ( empty( $title ) || empty( $content ) || empty( $event_date ) ) {
		wp_safe_redirect( cgt_assistance_redirect_url( array( 'section' => 'events', 'status' => 'missing' ) ) );
		exit;
	}

	$post_id = wp_insert_post(
		array(
			'post_type'    => 'cgt_agenda',
			'post_status'  => 'draft',
			'post_author'  => get_current_user_id(),
			'post_title'   => $title,
			'post_content' => $content,
			'post_excerpt' => $excerpt,
		),
		true
	);

	if ( is_wp_error( $post_id ) ) {
		wp_safe_redirect( cgt_assistance_redirect_url( array( 'section' => 'events', 'status' => 'error' ) ) );
		exit;
	}

	$timestamp = strtotime( $event_date );
	if ( $timestamp ) {
		update_post_meta( $post_id, 'cgt_event_date', gmdate( 'Y-m-d H:i:s', $timestamp ) );
	}

	if ( $event_addr ) {
		update_post_meta( $post_id, 'cgt_event_address', $event_addr );
	}

	if ( $branch_id ) {
		wp_set_post_terms( $post_id, array( $branch_id ), 'branche', false );
	}

	if ( $keywords ) {
		$tags = array_filter( array_map( 'trim', explode( ',', $keywords ) ) );
		if ( ! empty( $tags ) ) {
			wp_set_post_terms( $post_id, $tags, 'post_tag', false );
		}
	}

	if ( $sources ) {
		update_post_meta( $post_id, '_cgt_sources', $sources );
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	if ( ! empty( $_FILES['featured_image']['name'] ) ) {
		$attachment_id = media_handle_upload( 'featured_image', $post_id );
		if ( ! is_wp_error( $attachment_id ) ) {
			set_post_thumbnail( $post_id, $attachment_id );
		}
	}

	if ( ! empty( $_FILES['pdf_file']['name'] ) ) {
		$pdf_id = media_handle_upload( 'pdf_file', $post_id );
		if ( ! is_wp_error( $pdf_id ) ) {
			update_post_meta( $post_id, '_cgt_attached_pdf', $pdf_id );
		}
	}

	cgt_assistance_notify_admin(
		array(
			'type'    => 'event',
			'post_id' => $post_id,
			'title'   => $title,
		)
	);

	wp_safe_redirect( cgt_assistance_redirect_url( array( 'section' => 'events', 'status' => 'success' ) ) );
	exit;
}
add_action( 'admin_post_cgt_assistance_event_submit', 'cgt_assistance_handle_event_submission' );
/**
 * Notify site admin when a new submission is created.
 *
 * @param array $args Notification args.
 */
function cgt_assistance_notify_admin( $args ) {
	$type    = isset( $args['type'] ) ? $args['type'] : 'article';
	$post_id = isset( $args['post_id'] ) ? (int) $args['post_id'] : 0;
	$title   = isset( $args['title'] ) ? $args['title'] : __( '(Sans titre)', 'cgt' );

	$admin_email = get_option( 'admin_email' );
	if ( ! $admin_email ) {
		return;
	}

	$post_edit_link = $post_id ? get_edit_post_link( $post_id, '' ) : '';

	$label = in_array( $type, array( 'post', 'article' ), true ) ? __( 'article', 'cgt' ) : __( 'tract', 'cgt' );

	$subject = sprintf(
		/* translators: %s: content type label */
		__( 'Nouvelle soumission %s via l’espace assistance', 'cgt' ),
		$label
	);

	$message = sprintf(
		"%s\n\n%s: %s\n\n%s",
		__( 'Une nouvelle soumission est en attente de validation.', 'cgt' ),
		__( 'Titre', 'cgt' ),
		$title,
		$post_edit_link ? sprintf( __( 'Consulter: %s', 'cgt' ), $post_edit_link ) : ''
	);

	wp_mail( $admin_email, $subject, $message );
}
