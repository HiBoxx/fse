<?php
/**
 * Helper functions for the Enquêtes module.
 *
 * @package CGT_Child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Retrieve the URL of the Enquêtes page.
 *
 * @return string
 */
function cgt_get_enquetes_page_url() {
	static $cached = null;

	if ( null !== $cached ) {
		return $cached;
	}

	$page = get_page_by_path( 'enquetes' );
	if ( $page instanceof WP_Post ) {
		$cached = get_permalink( $page );
		return $cached;
	}

	$by_template = get_posts(
		array(
			'post_type'      => 'page',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'no_found_rows'  => true,
			'meta_key'       => '_wp_page_template',
			'meta_value'     => 'templates/page-enquetes.php',
			'fields'         => 'ids',
		)
	);

	if ( ! empty( $by_template ) ) {
		$cached = get_permalink( $by_template[0] );
		return $cached;
	}

	$cached = '';
	return $cached;
}

/**
 * Check if the current user may answer an enquête.
 *
 * @return bool
 */
function cgt_enquete_user_can_participate() {
	if ( ! is_user_logged_in() ) {
		return false;
	}

	if ( function_exists( 'cgt_user_can_read_private' ) && cgt_user_can_read_private() ) {
		return true;
	}

	$user = wp_get_current_user();
	if ( ! $user instanceof WP_User ) {
		return false;
	}

	$allowed_roles = array( 'administrator', 'gestionnaire', 'assistance' );
	foreach ( $allowed_roles as $role ) {
		if ( in_array( $role, (array) $user->roles, true ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Normalize questionnaire data coming from the assistance form.
 *
 * @param array $raw_questions Raw questions array.
 * @return array
 */
function cgt_normalize_enquete_questions_input( $raw_questions ) {
	$normalized = array();

	if ( empty( $raw_questions ) || ! is_array( $raw_questions ) ) {
		return $normalized;
	}

	foreach ( $raw_questions as $index => $question ) {
		$label = isset( $question['label'] ) ? sanitize_text_field( wp_unslash( $question['label'] ) ) : '';

		$options_input = isset( $question['options'] ) ? (array) $question['options'] : array();
		$options       = array();

		foreach ( $options_input as $option_index => $option_label ) {
			$option_label = sanitize_text_field( wp_unslash( $option_label ) );
			if ( '' === $option_label ) {
				continue;
			}

			$value = sanitize_title( $option_label );
			if ( '' === $value ) {
				$value = sprintf( 'opt-%d-%d', (int) $index, (int) $option_index );
			}

			$options[] = array(
				'label' => $option_label,
				'value' => $value,
			);
		}

		if ( '' === $label || count( $options ) < 2 ) {
			continue;
		}

		$key = sanitize_title( $label );
		if ( '' === $key ) {
			$key = sprintf( 'question-%d', (int) $index );
		}

		$normalized[] = array(
			'label'   => $label,
			'key'     => $key,
			'options' => $options,
		);
	}

	return $normalized;
}

/**
 * Return decoded questionnaire from post meta.
 *
 * @param int $enquete_id Post ID.
 * @return array
 */
function cgt_get_enquete_questions( $enquete_id ) {
	$questions_raw = get_post_meta( $enquete_id, 'cgt_enquete_questions', true );
	if ( empty( $questions_raw ) ) {
		return array();
	}

	$decoded = json_decode( $questions_raw, true );
	if ( ! is_array( $decoded ) ) {
		return array();
	}

	$normalized = array();
	foreach ( $decoded as $question ) {
		if ( empty( $question['label'] ) || empty( $question['key'] ) || empty( $question['options'] ) ) {
			continue;
		}

		$options = array();
		foreach ( (array) $question['options'] as $option ) {
			if ( empty( $option['label'] ) || empty( $option['value'] ) ) {
				continue;
			}

			$options[] = array(
				'label' => sanitize_text_field( $option['label'] ),
				'value' => sanitize_title( $option['value'] ),
			);
		}

		if ( count( $options ) < 2 ) {
			continue;
		}

		$normalized[] = array(
			'label'   => sanitize_text_field( $question['label'] ),
			'key'     => sanitize_title( $question['key'] ),
			'options' => $options,
		);
	}

	return $normalized;
}

/**
 * Count responses for an enquête.
 *
 * @param int $enquete_id Post ID.
 * @return int
 */
function cgt_get_enquete_response_count( $enquete_id ) {
	$cached = get_post_meta( $enquete_id, 'cgt_enquete_total_responses', true );
	if ( '' !== $cached && null !== $cached ) {
		return absint( $cached );
	}

	$count = new WP_Query(
		array(
			'post_type'      => 'cgt_enquete_response',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'meta_key'       => 'cgt_enquete_id',
			'meta_value'     => (int) $enquete_id,
		)
	);

	return (int) $count->found_posts;
}

/**
 * Determine whether a user already answered an enquête.
 *
 * @param int $enquete_id Enquête ID.
 * @param int $user_id    User ID.
 * @return bool
 */
function cgt_user_has_answered_enquete( $enquete_id, $user_id = 0 ) {
	$enquete_id = (int) $enquete_id;
	if ( $enquete_id <= 0 ) {
		return false;
	}

	$user_id = $user_id ? (int) $user_id : get_current_user_id();
	if ( $user_id <= 0 ) {
		return false;
	}

	$cache_key = sprintf( 'enquete_%d_user_%d', $enquete_id, $user_id );
	$cached    = wp_cache_get( $cache_key, 'cgt_enquetes' );
	if ( false !== $cached ) {
		return (bool) $cached;
	}

	$existing = get_posts(
		array(
			'post_type'      => 'cgt_enquete_response',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'no_found_rows'  => true,
			'fields'         => 'ids',
			'meta_query'     => array(
				array(
					'key'   => 'cgt_enquete_id',
					'value' => $enquete_id,
				),
				array(
					'key'   => 'cgt_enquete_user_id',
					'value' => $user_id,
				),
			),
		)
	);

	$has_answered = ! empty( $existing );
	wp_cache_set( $cache_key, $has_answered ? 1 : 0, 'cgt_enquetes', HOUR_IN_SECONDS );

	return $has_answered;
}

/**
 * Decode stored response data.
 *
 * @param int $response_id Post ID.
 * @return array
 */
function cgt_get_enquete_response_payload( $response_id ) {
	$data = get_post_meta( $response_id, 'cgt_enquete_answers', true );
	if ( empty( $data ) ) {
		return array();
	}

	$decoded = json_decode( $data, true );
	return is_array( $decoded ) ? $decoded : array();
}

/**
 * Handle front-end participation.
 */
function cgt_handle_enquete_response_submission() {
	$redirect_base = cgt_get_enquetes_page_url();
	if ( ! $redirect_base ) {
		$redirect_base = home_url( '/' );
	}

	$enquete_id = isset( $_POST['enquete_id'] ) ? absint( $_POST['enquete_id'] ) : 0;
	$redirect   = add_query_arg(
		array(
			'enquete' => $enquete_id ? $enquete_id : '',
		),
		$redirect_base
	);

	if ( ! isset( $_POST['cgt_enquete_response_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['cgt_enquete_response_nonce'] ), 'cgt_enquete_response' ) ) {
		wp_safe_redirect( add_query_arg( 'enquete_status', 'nonce', $redirect ) );
		exit;
	}

	if ( ! is_user_logged_in() ) {
		$login_url = cgt_get_login_page_url();
		if ( ! $login_url ) {
			$login_url = wp_login_url( $redirect );
		}
		wp_safe_redirect( add_query_arg( 'redirect_to', rawurlencode( $redirect ), $login_url ) );
		exit;
	}

	if ( ! cgt_enquete_user_can_participate() ) {
		wp_safe_redirect( add_query_arg( 'enquete_status', 'forbidden', $redirect ) );
		exit;
	}

	if ( ! $enquete_id || 'cgt_enquete' !== get_post_type( $enquete_id ) ) {
		wp_safe_redirect( add_query_arg( 'enquete_status', 'missing', $redirect ) );
		exit;
	}

	$questions = cgt_get_enquete_questions( $enquete_id );
	if ( empty( $questions ) ) {
		wp_safe_redirect( add_query_arg( 'enquete_status', 'missing', $redirect ) );
		exit;
	}

	$user_id = get_current_user_id();
	if ( cgt_user_has_answered_enquete( $enquete_id, $user_id ) ) {
		wp_safe_redirect( add_query_arg( 'enquete_status', 'duplicate', $redirect ) . '#enquete-' . $enquete_id );
		exit;
	}

	$responses_raw = isset( $_POST['responses'] ) ? (array) wp_unslash( $_POST['responses'] ) : array();
	$answers       = array();

	foreach ( $questions as $question ) {
		$key = $question['key'];
		if ( empty( $responses_raw[ $key ] ) ) {
			wp_safe_redirect( add_query_arg( 'enquete_status', 'missing', $redirect ) . '#enquete-' . $enquete_id );
			exit;
		}

		$value     = sanitize_title( $responses_raw[ $key ] );
		$selection = null;
		foreach ( $question['options'] as $option ) {
			if ( $option['value'] === $value ) {
				$selection = $option;
				break;
			}
		}

		if ( ! $selection ) {
			wp_safe_redirect( add_query_arg( 'enquete_status', 'error', $redirect ) . '#enquete-' . $enquete_id );
			exit;
		}

		$answers[] = array(
			'question' => $question['label'],
			'value'    => $selection['value'],
			'label'    => $selection['label'],
		);
	}

	$response_id = wp_insert_post(
		array(
			'post_type'    => 'cgt_enquete_response',
			'post_status'  => 'publish',
			'post_author'  => $user_id,
			'post_title'   => sprintf(
				/* translators: %s: survey title. */
				__( 'Réponse à l’enquête « %s »', 'cgt' ),
				get_the_title( $enquete_id )
			),
			'post_content' => wp_json_encode( $answers ),
		),
		true
	);

	if ( is_wp_error( $response_id ) ) {
		wp_safe_redirect( add_query_arg( 'enquete_status', 'error', $redirect ) . '#enquete-' . $enquete_id );
		exit;
	}

	update_post_meta( $response_id, 'cgt_enquete_id', $enquete_id );
	update_post_meta( $response_id, 'cgt_enquete_user_id', $user_id );
	update_post_meta( $response_id, 'cgt_enquete_answers', wp_json_encode( $answers ) );

	$user_branch = get_user_meta( $user_id, 'cgt_user_branch', true );
	if ( $user_branch ) {
		update_post_meta( $response_id, 'cgt_enquete_branch', sanitize_text_field( $user_branch ) );
	}

	$current_total = (int) get_post_meta( $enquete_id, 'cgt_enquete_total_responses', true );
	update_post_meta( $enquete_id, 'cgt_enquete_total_responses', $current_total + 1 );
	update_post_meta( $enquete_id, 'cgt_enquete_last_response', current_time( 'mysql' ) );

	wp_safe_redirect( add_query_arg( 'enquete_status', 'success', $redirect ) . '#enquete-' . $enquete_id );
	exit;
}
add_action( 'admin_post_cgt_enquete_response', 'cgt_handle_enquete_response_submission' );
add_action( 'admin_post_nopriv_cgt_enquete_response', 'cgt_handle_enquete_response_submission' );
