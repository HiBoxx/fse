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
 * ✅ Bloquer l'accès au back office WordPress pour les gestionnaires
 * Les gestionnaires sont redirigés vers leur espace dédié
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
 * ✅ Masquer la barre d'administration WordPress pour les gestionnaires
 */
add_action( 'after_setup_theme', 'cgt_hide_admin_bar_for_gestionnaire' );

function cgt_hide_admin_bar_for_gestionnaire() {
	$user = wp_get_current_user();
	if ( $user && in_array( 'gestionnaire', (array) $user->roles, true ) ) {
		show_admin_bar( false );
	}
}

/**
 * ✅ Rediriger les gestionnaires vers leur espace après connexion
 */
add_filter( 'login_redirect', 'cgt_gestionnaire_login_redirect', 10, 3 );

function cgt_gestionnaire_login_redirect( $redirect_to, $request, $user ) {
	// Vérifier si c'est un gestionnaire
	if ( isset( $user->roles ) && is_array( $user->roles ) && in_array( 'gestionnaire', $user->roles, true ) ) {
		// Trouver la page gestionnaire
		$gestionnaire_page = get_page_by_path( 'gestionnaire' );
		if ( $gestionnaire_page ) {
			return get_permalink( $gestionnaire_page );
		}
	}
	return $redirect_to;
}

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
 * Remove unused default roles to keep only administrator and adherent.
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
