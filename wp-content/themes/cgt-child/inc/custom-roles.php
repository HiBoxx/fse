<?php
/**
 * Custom Roles for CGT - Frontend Only (No wp-admin access)
 * 3 roles: Administration, Gestionnaire, Assistante
 *
 * @package CGT_Child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Create 3 custom roles
 */
add_action( 'after_setup_theme', 'cgt_create_custom_roles_frontend' );

function cgt_create_custom_roles_frontend() {
	if ( get_role( 'cgt_administration' ) && get_role( 'cgt_gestionnaire' ) && get_role( 'cgt_assistante' ) ) {
		return;
	}

	// Role 1: Administration - Manage members + bank info
	add_role(
		'cgt_administration',
		'CGT Administration',
		array(
			'read'                   => true,
			'cgt_manage_members'     => true,
			'cgt_manage_bank'        => true,
			'cgt_export_pdf'         => true,
		)
	);

	// Role 2: Gestionnaire - Publish content
	add_role(
		'cgt_gestionnaire',
		'CGT Gestionnaire',
		array(
			'read'                   => true,
			'edit_posts'             => true,
			'publish_posts'          => true,
			'upload_files'           => true,
			'cgt_publish_content'    => true,
		)
	);

	// Role 3: Assistante - Read-only access
	add_role(
		'cgt_assistante',
		'CGT Assistante',
		array(
			'read'                   => true,
			'cgt_view_members'       => true,
			'cgt_export_pdf'         => true,
		)
	);
}

/**
 * Create default user accounts
 */
add_action( 'after_setup_theme', 'cgt_create_custom_users', 20 );

function cgt_create_custom_users() {
	$users = array(
		array( 'login' => 'administration', 'password' => 'admin123', 'email' => 'administration@cgt-local.fr', 'role' => 'cgt_administration' ),
		array( 'login' => 'gestionnaire', 'password' => 'gestion123', 'email' => 'gestionnaire@cgt-local.fr', 'role' => 'cgt_gestionnaire' ),
		array( 'login' => 'assistante', 'password' => 'assist123', 'email' => 'assistante@cgt-local.fr', 'role' => 'cgt_assistante' ),
	);

	foreach ( $users as $user_data ) {
		if ( ! username_exists( $user_data['login'] ) && ! email_exists( $user_data['email'] ) ) {
			$user_id = wp_create_user( $user_data['login'], $user_data['password'], $user_data['email'] );
			if ( ! is_wp_error( $user_id ) ) {
				$user = get_user_by( 'id', $user_id );
				$user->remove_role( 'subscriber' );
				$user->add_role( $user_data['role'] );
			}
		}
	}
}

/**
 * Block wp-admin access for custom roles
 */
add_action( 'admin_init', 'cgt_block_admin_access_custom_roles' );

function cgt_block_admin_access_custom_roles() {
	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		return;
	}

	$current_user = wp_get_current_user();
	$roles        = $current_user->roles;

	$blocked_roles = array( 'cgt_administration', 'cgt_gestionnaire', 'cgt_assistante' );

	foreach ( $blocked_roles as $blocked_role ) {
		if ( in_array( $blocked_role, $roles, true ) ) {
			// Redirect to their frontend space
			$redirect_urls = array(
				'cgt_administration' => home_url( '/espace-administration/' ),
				'cgt_gestionnaire'   => home_url( '/espace-gestionnaire/' ),
				'cgt_assistante'     => home_url( '/espace-assistante/' ),
			);

			if ( isset( $redirect_urls[ $blocked_role ] ) ) {
				wp_safe_redirect( $redirect_urls[ $blocked_role ] );
				exit;
			}
		}
	}
}

/**
 * Redirect after login
 */
add_filter( 'login_redirect', 'cgt_custom_login_redirect_frontend', 10, 3 );

function cgt_custom_login_redirect_frontend( $redirect_to, $request, $user ) {
	if ( isset( $user->roles ) && is_array( $user->roles ) ) {
		if ( in_array( 'cgt_administration', $user->roles, true ) ) {
			return home_url( '/espace-administration/' );
		} elseif ( in_array( 'cgt_gestionnaire', $user->roles, true ) ) {
			return home_url( '/espace-gestionnaire/' );
		} elseif ( in_array( 'cgt_assistante', $user->roles, true ) ) {
			return home_url( '/espace-assistante/' );
		}
	}

	return $redirect_to;
}
