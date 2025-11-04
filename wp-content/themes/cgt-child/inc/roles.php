<?php
/**
 * Roles and permissions.
 *
 * @package CGT_Child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Ensure the adherent role exists with proper capabilities.
 */
function cgt_register_adherent_role() {
	$role = get_role( 'adherent' );

	if ( ! $role ) {
		$role = add_role(
			'adherent',
			__( 'Adhérent', 'cgt' ),
			array(
				'read' => true,
			)
		);
	}

	if ( $role ) {
		$role->add_cap( 'read_private_cgt' );
		$role->add_cap( 'read_private_articles_adherents' );
		$role->add_cap( 'read_private_cgt_agendas' );
		$role->add_cap( 'read_private_posts' );
	}

	$article_caps = array(
		'edit_article_adherent',
		'read_article_adherent',
		'delete_article_adherent',
		'edit_articles_adherents',
		'edit_others_articles_adherents',
		'publish_articles_adherents',
		'read_private_articles_adherents',
		'edit_private_articles_adherents',
		'edit_published_articles_adherents',
		'delete_articles_adherents',
		'delete_others_articles_adherents',
		'delete_private_articles_adherents',
		'delete_published_articles_adherents',
	);

	$adhesion_caps = array( 'manage_cgt_adhesions' );

	// Grant capabilities to administrators only.
	$admin_role = get_role( 'administrator' );
	if ( $admin_role ) {
		$admin_role->add_cap( 'read_private_cgt' );
		$admin_role->add_cap( 'read_private_articles_adherents' );
		$admin_role->add_cap( 'read_private_cgt_agendas' );
		$admin_role->add_cap( 'read_private_posts' );
		foreach ( $article_caps as $cap ) {
			$admin_role->add_cap( $cap );
		}
		foreach ( $adhesion_caps as $cap ) {
			$admin_role->add_cap( $cap );
		}
	}
}

/**
 * ✅ NOUVEAU : Créer le rôle gestionnaire pour l'espace adhésions
 * Accès uniquement à la page gestionnaire (pas au back office WordPress)
 */
function cgt_register_gestionnaire_role() {
	$role = get_role( 'gestionnaire' );

	// Créer le rôle s'il n'existe pas
	if ( ! $role ) {
		$role = add_role(
			'gestionnaire',
			__( 'Gestionnaire', 'cgt' ),
			array(
				'read'                   => true,
				'view_cgt_adhesions'     => true,
				'download_cgt_adhesions' => true,
			)
		);
	}

	// S'assurer que les capacités sont présentes
	if ( $role ) {
		$role->add_cap( 'view_cgt_adhesions' );
		$role->add_cap( 'download_cgt_adhesions' );
	}
}
add_action( 'init', 'cgt_register_gestionnaire_role' );

/**
 * ✅ NOUVEAU : Créer le rôle assistance pour l'espace de gestion des contenus
 * Accès uniquement à la page assistance (pas au back office WordPress)
 */
function cgt_register_assistance_role() {
	$role = get_role( 'assistance' );

	// Créer le rôle s'il n'existe pas
	if ( ! $role ) {
		$capabilities = array(
			// Capacités de base
			'read'                   => true,
			'upload_files'           => true,

			// Articles (posts)
			'edit_posts'             => true,
			'edit_others_posts'      => true,
			'edit_published_posts'   => true,
			'publish_posts'          => true,
			'delete_posts'           => true,
			'delete_published_posts' => true,

			// Tracts (utilise les caps de post par défaut)
			'edit_tracts'            => true,
			'edit_others_tracts'     => true,
			'edit_published_tracts'  => true,
			'publish_tracts'         => true,
			'delete_tracts'          => true,

			// Événements (cgt_agenda)
			'edit_cgt_agendas'       => true,
			'edit_others_cgt_agendas' => true,
			'edit_published_cgt_agendas' => true,
			'publish_cgt_agendas'    => true,
			'delete_cgt_agendas'     => true,

			// Messages (cgt_contact)
			'edit_cgt_contacts'      => true,
			'edit_others_cgt_contacts' => true,
			'delete_cgt_contacts'    => true,

			// Adhésions
			'view_cgt_adhesions'     => true,
			'download_cgt_adhesions' => true,
			'edit_cgt_adhesions'     => true,

			// Taxonomies
			'manage_categories'      => true,
			'edit_categories'        => true,
			'delete_categories'      => true,
			'assign_categories'      => true,
		);

		$role = add_role(
			'assistance',
			__( 'Assistance', 'cgt' ),
			$capabilities
		);
	}

	// S'assurer que toutes les capacités sont présentes
	if ( $role ) {
		$caps = array(
			'read', 'upload_files',
			'edit_posts', 'edit_others_posts', 'edit_published_posts', 'publish_posts', 'delete_posts', 'delete_published_posts',
			'edit_tracts', 'edit_others_tracts', 'edit_published_tracts', 'publish_tracts', 'delete_tracts',
			'edit_cgt_agendas', 'edit_others_cgt_agendas', 'edit_published_cgt_agendas', 'publish_cgt_agendas', 'delete_cgt_agendas',
			'edit_cgt_contacts', 'edit_others_cgt_contacts', 'delete_cgt_contacts',
			'view_cgt_adhesions', 'download_cgt_adhesions', 'edit_cgt_adhesions',
			'manage_categories', 'edit_categories', 'delete_categories', 'assign_categories',
		);

		foreach ( $caps as $cap ) {
			$role->add_cap( $cap );
		}
	}
}
add_action( 'init', 'cgt_register_assistance_role' );

/**
 * ✅ Bloquer l'accès au back office WordPress pour les gestionnaires et assistance
 * Ils sont redirigés vers leurs espaces dédiés
 */
add_action( 'admin_init', 'cgt_block_custom_roles_admin_access' );

function cgt_block_custom_roles_admin_access() {
	// Ne pas bloquer les requêtes AJAX
	if ( wp_doing_ajax() ) {
		return;
	}

	$user = wp_get_current_user();
	if ( ! $user ) {
		return;
	}

	// Bloquer gestionnaires
	if ( in_array( 'gestionnaire', (array) $user->roles, true ) ) {
		$gestionnaire_page = get_page_by_path( 'gestionnaire' );
		$redirect_url      = $gestionnaire_page ? get_permalink( $gestionnaire_page ) : home_url();
		wp_safe_redirect( $redirect_url );
		exit;
	}

	// Bloquer assistance
	if ( in_array( 'assistance', (array) $user->roles, true ) ) {
		$assistance_page = get_page_by_path( 'assistance' );
		$redirect_url    = $assistance_page ? get_permalink( $assistance_page ) : home_url();
		wp_safe_redirect( $redirect_url );
		exit;
	}
}

/**
 * ✅ DEPRECATED - Gardé pour compatibilité
 */
add_action( 'admin_init', 'cgt_block_gestionnaire_admin_access' );

function cgt_block_gestionnaire_admin_access() {
	// Ne pas bloquer les requêtes AJAX
	if ( wp_doing_ajax() ) {
		return;
	}

	// Vérifier si l'utilisateur est un gestionnaire
	$user = wp_get_current_user();
	if ( ! $user || ! in_array( 'gestionnaire', (array) $user->roles, true ) ) {
		return;
	}

	// Trouver la page gestionnaire
	$gestionnaire_page = get_page_by_path( 'gestionnaire' );
	$redirect_url      = $gestionnaire_page ? get_permalink( $gestionnaire_page ) : home_url();

	// Rediriger vers l'espace gestionnaire
	wp_safe_redirect( $redirect_url );
	exit;
}

/**
 * ✅ Masquer la barre d'administration WordPress pour les gestionnaires et assistance
 */
add_action( 'after_setup_theme', 'cgt_hide_admin_bar_for_custom_roles' );

function cgt_hide_admin_bar_for_custom_roles() {
	$user = wp_get_current_user();
	if ( ! $user ) {
		return;
	}

	$roles_to_hide = array( 'gestionnaire', 'assistance' );
	$user_roles    = (array) $user->roles;

	foreach ( $roles_to_hide as $role ) {
		if ( in_array( $role, $user_roles, true ) ) {
			show_admin_bar( false );
			break;
		}
	}
}

/**
 * ✅ Rediriger les gestionnaires et assistance vers leurs espaces après connexion
 * Priorité 5 pour s'exécuter AVANT cgt_login_redirect (priorité 10)
 */
add_filter( 'login_redirect', 'cgt_custom_roles_login_redirect', 5, 3 );

function cgt_custom_roles_login_redirect( $redirect_to, $request, $user ) {
	if ( ! isset( $user->roles ) || ! is_array( $user->roles ) ) {
		return $redirect_to;
	}

	// Redirection gestionnaires
	if ( in_array( 'gestionnaire', $user->roles, true ) ) {
		$gestionnaire_page = get_page_by_path( 'gestionnaire' );
		if ( $gestionnaire_page ) {
			return get_permalink( $gestionnaire_page );
		}
	}

	// Redirection assistance
	if ( in_array( 'assistance', $user->roles, true ) ) {
		$assistance_page = get_page_by_path( 'assistance' );
		if ( $assistance_page ) {
			return get_permalink( $assistance_page );
		}
	}

	return $redirect_to;
}

/**
 * Redirect gestionnaire / assistance users away from espace adhérent frontend.
 */
add_action(
	'template_redirect',
	function () {
		if ( ! is_user_logged_in() || ! is_page( 'espace-adherent' ) ) {
			return;
		}

		$user = wp_get_current_user();
		if ( ! $user || empty( $user->roles ) ) {
			return;
		}

		if ( in_array( 'gestionnaire', (array) $user->roles, true ) ) {
			$gestionnaire_page = get_page_by_path( 'gestionnaire' );
			if ( $gestionnaire_page ) {
				wp_safe_redirect( get_permalink( $gestionnaire_page ) );
				exit;
			}
		}

		if ( in_array( 'assistance', (array) $user->roles, true ) ) {
			$assistance_page = get_page_by_path( 'assistance' );
			if ( $assistance_page ) {
				wp_safe_redirect( get_permalink( $assistance_page ) );
				exit;
			}
		}
	}
);

/**
 * ✅ Créer automatiquement la page gestionnaire
 * S'exécute une seule fois lors de l'activation du thème
 */
add_action( 'after_switch_theme', 'cgt_create_gestionnaire_page' );
add_action( 'init', 'cgt_create_gestionnaire_page' );

function cgt_create_gestionnaire_page() {
	// Vérifier si déjà créée
	if ( get_option( 'cgt_gestionnaire_page_created', false ) ) {
		return;
	}

	// Vérifier si la page existe déjà
	$existing_page = get_page_by_path( 'gestionnaire' );
	if ( $existing_page ) {
		update_option( 'cgt_gestionnaire_page_created', true, false );
		return;
	}

	// Créer la page
	$page_id = wp_insert_post(
		array(
			'post_title'     => 'Espace Gestionnaire',
			'post_name'      => 'gestionnaire',
			'post_status'    => 'publish',
			'post_type'      => 'page',
			'post_content'   => '',
			'comment_status' => 'closed',
			'ping_status'    => 'closed',
		)
	);

	if ( ! is_wp_error( $page_id ) ) {
		// Assigner le template
		update_post_meta( $page_id, '_wp_page_template', 'page-gestionnaire.php' );
		update_option( 'cgt_gestionnaire_page_created', true, false );
		error_log( 'CGT: Page gestionnaire créée avec succès (ID: ' . $page_id . ')' );
	} else {
		error_log( 'CGT: Erreur lors de la création de la page gestionnaire: ' . $page_id->get_error_message() );
	}
}

/**
 * ✅ Créer automatiquement le compte gestionnaire par défaut
 * S'exécute une seule fois lors de l'activation du thème
 */
add_action( 'after_switch_theme', 'cgt_create_default_gestionnaire_account' );
add_action( 'init', 'cgt_create_default_gestionnaire_account' );

function cgt_create_default_gestionnaire_account() {
	// Vérifier si déjà créé
	if ( get_option( 'cgt_gestionnaire_account_created', false ) ) {
		return;
	}

	// Vérifier si l'utilisateur existe déjà
	$existing_user = get_user_by( 'login', 'gestionnaire' );
	if ( $existing_user ) {
		// S'assurer que l'utilisateur a le bon rôle
		$user = new WP_User( $existing_user->ID );
		if ( ! in_array( 'gestionnaire', (array) $user->roles, true ) ) {
			$user->set_role( 'gestionnaire' );
		}
		// Marquer comme créé
		update_option( 'cgt_gestionnaire_account_created', true, false );
		return;
	}

	// Créer le compte gestionnaire
	$user_id = wp_create_user(
		'gestionnaire',
		'gestion123',
		'gestionnaire@cgt-fsetud.local'
	);

	if ( ! is_wp_error( $user_id ) ) {
		// Assigner le rôle gestionnaire
		$user = new WP_User( $user_id );
		$user->set_role( 'gestionnaire' );

		// Mettre à jour les métadonnées
		wp_update_user(
			array(
				'ID'           => $user_id,
				'display_name' => 'Gestionnaire CGT',
				'first_name'   => 'Gestionnaire',
				'last_name'    => 'CGT',
			)
		);

		// Marquer comme créé
		update_option( 'cgt_gestionnaire_account_created', true, false );

		error_log( 'CGT: Compte gestionnaire créé avec succès (ID: ' . $user_id . ')' );
	} else {
		error_log( 'CGT: Erreur lors de la création du compte gestionnaire: ' . $user_id->get_error_message() );
	}
}

/**
 * ✅ Créer automatiquement la page assistance
 * S'exécute une seule fois lors de l'activation du thème
 */
add_action( 'after_switch_theme', 'cgt_create_assistance_page' );
add_action( 'init', 'cgt_create_assistance_page' );

function cgt_create_assistance_page() {
	// Vérifier si déjà créée
	if ( get_option( 'cgt_assistance_page_created', false ) ) {
		return;
	}

	// Vérifier si la page existe déjà
	$existing_page = get_page_by_path( 'assistance' );
	if ( $existing_page ) {
		update_option( 'cgt_assistance_page_created', true, false );
		return;
	}

	// Créer la page
	$page_id = wp_insert_post(
		array(
			'post_title'     => 'Espace Assistance',
			'post_name'      => 'assistance',
			'post_status'    => 'publish',
			'post_type'      => 'page',
			'post_content'   => '',
			'comment_status' => 'closed',
			'ping_status'    => 'closed',
		)
	);

	if ( ! is_wp_error( $page_id ) ) {
		// Assigner le template
		update_post_meta( $page_id, '_wp_page_template', 'page-assistance.php' );
		update_option( 'cgt_assistance_page_created', true, false );
		error_log( 'CGT: Page assistance créée avec succès (ID: ' . $page_id . ')' );
	} else {
		error_log( 'CGT: Erreur lors de la création de la page assistance: ' . $page_id->get_error_message() );
	}
}

/**
 * ✅ Créer automatiquement le compte assistance par défaut
 * S'exécute une seule fois lors de l'activation du thème
 */
add_action( 'after_switch_theme', 'cgt_create_default_assistance_account' );
add_action( 'init', 'cgt_create_default_assistance_account' );

function cgt_create_default_assistance_account() {
	// Vérifier si déjà créé
	if ( get_option( 'cgt_assistance_account_created', false ) ) {
		return;
	}

	// Vérifier si l'utilisateur existe déjà
	$existing_user = get_user_by( 'login', 'assistance' );
	if ( $existing_user ) {
		// S'assurer que l'utilisateur a le bon rôle
		$user = new WP_User( $existing_user->ID );
		if ( ! in_array( 'assistance', (array) $user->roles, true ) ) {
			$user->set_role( 'assistance' );
		}
		// Marquer comme créé
		update_option( 'cgt_assistance_account_created', true, false );
		return;
	}

	// Créer le compte assistance
	$user_id = wp_create_user(
		'assistance',
		'assist123',
		'assistance@cgt-fsetud.local'
	);

	if ( ! is_wp_error( $user_id ) ) {
		// Assigner le rôle assistance
		$user = new WP_User( $user_id );
		$user->set_role( 'assistance' );

		// Mettre à jour les métadonnées
		wp_update_user(
			array(
				'ID'           => $user_id,
				'display_name' => 'Assistance CGT',
				'first_name'   => 'Assistance',
				'last_name'    => 'CGT',
			)
		);

		// Marquer comme créé
		update_option( 'cgt_assistance_account_created', true, false );

		error_log( 'CGT: Compte assistance créé avec succès (ID: ' . $user_id . ')' );
	} else {
		error_log( 'CGT: Erreur lors de la création du compte assistance: ' . $user_id->get_error_message() );
	}
}

add_action(
	'init',
	function () {
	$role            = get_role( 'adherent' );
	$needs_reapply   = false;
	$required_caps   = array( 'edit_article_adherent', 'manage_cgt_adhesions', 'read_private_posts' );

	if ( ! $role || ! $role->has_cap( 'read_private_cgt' ) || ! $role->has_cap( 'read_private_posts' ) ) {
			$needs_reapply = true;
		}

	$admin_role = get_role( 'administrator' );
	if ( $admin_role ) {
		foreach ( $required_caps as $required_cap ) {
			if ( ! $admin_role->has_cap( $required_cap ) ) {
				$needs_reapply = true;
				break;
			}
		}
	}

		if ( $needs_reapply ) {
			cgt_register_adherent_role();
		}
	}
);

/**
 * Remove unused default roles to keep only administrator, adherent, gestionnaire, and assistance.
 */
add_action(
	'after_setup_theme',
	function () {
		$roles_to_remove = array(
			'editor',
			'author',
			'contributor',
			'subscriber',
			'administration',
			'assistante',
		);

		foreach ( $roles_to_remove as $role_key ) {
			if ( get_role( $role_key ) ) {
				remove_role( $role_key );
			}
		}
	}
);

/**
 * Helper to know if a visitor can read private CGT content.
 *
 * @return bool
 */
function cgt_user_can_read_private() {
	return current_user_can( 'read_private_cgt' );
}

/**
 * Filter private tracts for non authorised users.
 *
 * @param WP_Query $query Query.
 */
function cgt_filter_private_content( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}

	if ( cgt_user_can_read_private() ) {
		return;
	}

	if ( $query->is_post_type_archive( 'tracts' ) || $query->is_tax( array( 'branche', 'thematique', 'zone_internationale' ) ) || $query->is_search() ) {
		$meta_query = (array) $query->get( 'meta_query', array() );

		// Afficher les tracts publics ET ceux sans visibilité définie
		$meta_query[] = array(
			'relation' => 'OR',
			array(
				'key'     => 'cgt_visibilite',
				'value'   => 'prive',
				'compare' => '!=',
			),
			array(
				'key'     => 'cgt_visibilite',
				'compare' => 'NOT EXISTS',
			),
		);

		$query->set( 'meta_query', $meta_query );
	}
}
add_action( 'pre_get_posts', 'cgt_filter_private_content' );

/**
 * Restrict access to private tracts and member-only content directly.
 */
function cgt_redirect_private_single() {
	// Ne s'exécute que sur le frontend, pas dans l'admin
	if ( is_admin() ) {
		return;
	}

	// Vérifier les tracts privés
	if ( is_singular( 'tracts' ) ) {
		$visibility = get_post_meta( get_queried_object_id(), 'cgt_visibilite', true );
		if ( 'prive' === $visibility && ! cgt_user_can_read_private() ) {
			// Afficher un message d'erreur avec lien de connexion
			$login_url = cgt_get_login_page_url() ? cgt_get_login_page_url() : wp_login_url( get_permalink() );
			$message = '<h1>Accès refusé</h1>';
			if ( ! is_user_logged_in() ) {
				$message .= '<p>Ce contenu est réservé aux adhérents de la CGT.</p>';
				$message .= '<p><a href="' . esc_url( $login_url ) . '" class="button button-primary">Se connecter</a></p>';
			} else {
				$message .= '<p>Votre compte n\'a pas les droits nécessaires pour accéder à ce contenu. Veuillez contacter un administrateur.</p>';
			}
			wp_die( $message, 'Accès refusé', array( 'response' => 403 ) );
		}
	}

	// Vérifier les articles réservés aux membres
	if ( is_singular( 'articles_adherents' ) ) {
		if ( ! cgt_user_can_read_private() ) {
			// Afficher un message d'erreur avec lien de connexion
			$login_url = cgt_get_login_page_url() ? cgt_get_login_page_url() : wp_login_url( get_permalink() );
			$message = '<h1>Accès refusé</h1>';
			if ( ! is_user_logged_in() ) {
				$message .= '<p>Ce contenu est réservé aux adhérents de la CGT.</p>';
				$message .= '<p><a href="' . esc_url( $login_url ) . '" class="button button-primary">Se connecter</a></p>';
			} else {
				$message .= '<p>Votre compte n\'a pas les droits nécessaires pour accéder à ce contenu. Veuillez contacter un administrateur.</p>';
			}
			wp_die( $message, 'Accès refusé', array( 'response' => 403 ) );
		}
	}
}
add_action( 'wp', 'cgt_redirect_private_single', 1 );
