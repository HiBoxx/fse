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

/**
 * Handler: Bank form submission
 */
add_action( 'admin_post_cgt_bank_submit', 'cgt_handle_bank_submit' );
add_action( 'admin_post_nopriv_cgt_bank_submit', 'cgt_handle_bank_submit' );

function cgt_handle_bank_submit() {
	if ( ! isset( $_POST['cgt_bank_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['cgt_bank_nonce'] ), 'cgt_bank_submit' ) ) {
		wp_die( 'Erreur de sécurité.' );
	}

	$nom    = isset( $_POST['bank_nom'] ) ? sanitize_text_field( wp_unslash( $_POST['bank_nom'] ) ) : '';
	$prenom = isset( $_POST['bank_prenom'] ) ? sanitize_text_field( wp_unslash( $_POST['bank_prenom'] ) ) : '';
	$rib    = isset( $_POST['bank_rib'] ) ? sanitize_text_field( wp_unslash( $_POST['bank_rib'] ) ) : '';

	if ( empty( $nom ) || empty( $prenom ) || empty( $rib ) ) {
		wp_safe_redirect( add_query_arg( 'bank_error', '1', wp_get_referer() ) );
		exit;
	}

	// Create mandat post
	$post_id = wp_insert_post(
		array(
			'post_title'  => sprintf( 'Mandat SEPA - %s %s', $prenom, $nom ),
			'post_type'   => 'cgt_mandat',
			'post_status' => 'publish',
		)
	);

	if ( $post_id && ! is_wp_error( $post_id ) ) {
		update_post_meta( $post_id, '_mandat_nom', $nom );
		update_post_meta( $post_id, '_mandat_prenom', $prenom );
		update_post_meta( $post_id, '_mandat_rib', $rib );

		// Handle file upload
		if ( ! empty( $_FILES['bank_pdf']['name'] ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/media.php';
			require_once ABSPATH . 'wp-admin/includes/image.php';

			$attachment_id = media_handle_upload( 'bank_pdf', $post_id );
			if ( ! is_wp_error( $attachment_id ) ) {
				update_post_meta( $post_id, '_mandat_pdf_id', $attachment_id );
			}
		}
	}

	wp_safe_redirect( add_query_arg( 'bank_success', '1', wp_get_referer() ) );
	exit;
}

/**
 * Handler: Publish content form
 */
add_action( 'admin_post_cgt_publish_content', 'cgt_handle_publish_content' );
add_action( 'admin_post_nopriv_cgt_publish_content', 'cgt_handle_publish_content' );

function cgt_handle_publish_content() {
	if ( ! isset( $_POST['cgt_publish_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['cgt_publish_nonce'] ), 'cgt_publish_content' ) ) {
		wp_die( 'Erreur de sécurité.' );
	}

	$type       = isset( $_POST['content_type'] ) ? sanitize_key( $_POST['content_type'] ) : '';
	$title      = isset( $_POST['content_title'] ) ? sanitize_text_field( wp_unslash( $_POST['content_title'] ) ) : '';
	$content    = isset( $_POST['content_content'] ) ? wp_kses_post( wp_unslash( $_POST['content_content'] ) ) : '';
	$visibility = isset( $_POST['content_visibility'] ) ? sanitize_key( $_POST['content_visibility'] ) : 'public';

	if ( empty( $type ) || empty( $title ) || empty( $content ) ) {
		wp_safe_redirect( add_query_arg( 'published', 'error', wp_get_referer() ) );
		exit;
	}

	// Map type to post_type
	$post_type_map = array(
		'article'   => 'post',
		'tract'     => 'tracts',
		'petition'  => 'cgt_petition',
		'evenement' => 'cgt_agenda',
	);

	$post_type = isset( $post_type_map[ $type ] ) ? $post_type_map[ $type ] : 'post';

	// Create post
	$post_id = wp_insert_post(
		array(
			'post_type'    => $post_type,
			'post_title'   => $title,
			'post_content' => $content,
			'post_status'  => 'prive' === $visibility ? 'private' : 'publish',
			'post_author'  => get_current_user_id(),
		)
	);

	if ( $post_id && ! is_wp_error( $post_id ) ) {
		if ( 'tracts' === $post_type && 'prive' === $visibility ) {
			update_post_meta( $post_id, 'cgt_visibilite', 'prive' );
		}

		wp_safe_redirect( add_query_arg( 'published', 'success', wp_get_referer() ) );
		exit;
	}

	wp_safe_redirect( add_query_arg( 'published', 'error', wp_get_referer() ) );
	exit;
}

/**
 * Register mandat CPT
 */
add_action( 'init', 'cgt_register_mandat_cpt' );

function cgt_register_mandat_cpt() {
	register_post_type(
		'cgt_mandat',
		array(
			'labels'              => array(
				'name'          => 'Mandats SEPA',
				'singular_name' => 'Mandat SEPA',
			),
			'public'              => false,
			'show_ui'             => false,
			'show_in_menu'        => false,
			'capability_type'     => 'post',
			'supports'            => array( 'title' ),
			'has_archive'         => false,
		)
	);
}

/**
 * Load custom spaces CSS
 */
add_action( 'wp_enqueue_scripts', 'cgt_enqueue_custom_spaces_css' );

function cgt_enqueue_custom_spaces_css() {
	if ( is_page_template( 'templates/page-espace-administration.php' ) ||
	     is_page_template( 'templates/page-espace-gestionnaire.php' ) ||
	     is_page_template( 'templates/page-espace-assistante.php' ) ) {
		wp_enqueue_style(
			'cgt-custom-spaces',
			get_stylesheet_directory_uri() . '/assets/css/custom-spaces.css',
			array(),
			CGT_CHILD_VERSION
		);
	}
}
