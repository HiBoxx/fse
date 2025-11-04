<?php
/**
 * Gestion des demandes de création de compte adhérent.
 *
 * @package CGT_Child
 */

defined( 'ABSPATH' ) || exit;

add_action( 'admin_post_nopriv_cgt_request_account', 'cgt_handle_account_request' );
add_action( 'admin_post_cgt_request_account', 'cgt_handle_account_request' );

/**
 * Traite le formulaire de création de compte adhérent.
 */
function cgt_handle_account_request() {
	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ?? '' ) ) {
		cgt_redirect_account_request( 'general_error' );
	}

	if ( ! isset( $_POST['cgt_account_request_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['cgt_account_request_nonce'] ), 'cgt_account_request' ) ) {
		cgt_redirect_account_request( 'general_error' );
	}

	if ( ! cgt_check_honeypot( 'cgt_hp_account_request' ) ) {
		cgt_log_spam_attempt( 'account_request', $_POST );
		cgt_redirect_account_request( 'general_error' );
	}

	if ( ! cgt_check_rate_limit( 'account_request', 5, HOUR_IN_SECONDS ) ) {
		cgt_redirect_account_request( 'rate_limited' );
	}

	$posted = wp_unslash( $_POST );

	$first_name = isset( $posted['prenom'] ) ? sanitize_text_field( $posted['prenom'] ) : '';
	$last_name  = isset( $posted['nom'] ) ? sanitize_text_field( $posted['nom'] ) : '';
	$email      = isset( $posted['email'] ) ? sanitize_email( $posted['email'] ) : '';
	$username   = isset( $posted['username'] ) ? sanitize_user( $posted['username'], true ) : '';
	$password   = isset( $posted['password'] ) ? (string) $posted['password'] : '';
	$confirm    = isset( $posted['password_confirm'] ) ? (string) $posted['password_confirm'] : '';
	$company    = isset( $posted['entreprise'] ) ? sanitize_text_field( $posted['entreprise'] ) : '';
	$branch_id  = isset( $posted['branche'] ) ? absint( $posted['branche'] ) : 0;
	$terms_ok   = isset( $posted['accept_terms'] ) && '1' === $posted['accept_terms'];

	if ( empty( $first_name ) || empty( $last_name ) || empty( $email ) || empty( $username ) || empty( $password ) || empty( $confirm ) || empty( $branch_id ) ) {
		cgt_redirect_account_request( 'missing_fields' );
	}

	if ( ! $terms_ok ) {
		cgt_redirect_account_request( 'terms_missing' );
	}

	if ( ! is_email( $email ) ) {
		cgt_redirect_account_request( 'invalid_email' );
	}

	if ( $password !== $confirm ) {
		cgt_redirect_account_request( 'password_mismatch' );
	}

	if ( username_exists( $username ) ) {
		cgt_redirect_account_request( 'username_exists' );
	}

	if ( email_exists( $email ) ) {
		cgt_redirect_account_request( 'email_exists' );
	}

	$branch_term = get_term( $branch_id, 'branche' );
	if ( ! $branch_term || is_wp_error( $branch_term ) ) {
		cgt_redirect_account_request( 'invalid_branch' );
	}

	$user_id = wp_create_user( $username, $password, $email );
	if ( is_wp_error( $user_id ) ) {
		cgt_redirect_account_request( 'general_error' );
	}

	wp_update_user(
		array(
			'ID'           => $user_id,
			'first_name'   => $first_name,
			'last_name'    => $last_name,
			'display_name' => trim( $first_name . ' ' . $last_name ),
			'role'         => 'adherent',
		)
	);

	update_user_meta( $user_id, 'cgt_user_branch', $branch_id );

	if ( $company ) {
		update_user_meta( $user_id, 'cgt_user_company', $company );
	}

	/**
	 * Permet aux extensions de réagir à la création d'un compte adhérent.
	 *
	 * @param int   $user_id  Identifiant du nouvel utilisateur.
	 * @param array $posted   Données du formulaire sanitizées.
	 */
	do_action(
		'cgt_account_request_created',
		$user_id,
		array(
			'first_name' => $first_name,
			'last_name'  => $last_name,
			'email'      => $email,
			'username'   => $username,
			'company'    => $company,
			'branch_id'  => $branch_id,
		)
	);

	wp_set_current_user( $user_id );
	wp_set_auth_cookie( $user_id );

	$user = get_user_by( 'id', $user_id );
	if ( $user ) {
		do_action( 'wp_login', $user->user_login, $user );
	}

	$success_redirect = add_query_arg(
		array(
			'account_request' => 'success',
			'redirect_to'     => rawurlencode( home_url( '/espace-adherent' ) ),
		),
		cgt_account_request_redirect_base()
	);

	wp_safe_redirect( $success_redirect );
	exit;
}

/**
 * Redirige vers la page de connexion avec un état d'erreur donné.
 *
 * @param string $state Code d'état.
 */
function cgt_redirect_account_request( $state ) {
	$redirect = add_query_arg(
		array(
			'account_request' => sanitize_key( $state ),
		),
		cgt_account_request_redirect_base()
	);

	wp_safe_redirect( $redirect );
	exit;
}

/**
 * Détermine l'URL de redirection de base pour la page de connexion.
 *
 * @return string
 */
function cgt_account_request_redirect_base() {
	$login_url = cgt_get_login_page_url();

	if ( $login_url ) {
		return $login_url;
	}

	if ( wp_get_referer() ) {
		return wp_get_referer();
	}

	return home_url();
}
