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

	// Grant capability to editors & admins.
	foreach ( array( 'administrator', 'editor' ) as $role_key ) {
		$role_object = get_role( $role_key );
		if ( $role_object ) {
			$role_object->add_cap( 'read_private_cgt' );
			$role_object->add_cap( 'read_private_articles_adherents' );
			$role_object->add_cap( 'read_private_cgt_agendas' );
			foreach ( $article_caps as $cap ) {
				$role_object->add_cap( $cap );
			}

			// Seuls les administrateurs gèrent les adhésions.
			if ( 'administrator' === $role_key ) {
				foreach ( $adhesion_caps as $cap ) {
					$role_object->add_cap( $cap );
				}
			}
		}
	}
}

add_action(
	'init',
	function () {
	$role            = get_role( 'adherent' );
	$needs_reapply   = false;
	$required_caps   = array( 'edit_article_adherent', 'manage_cgt_adhesions' );

		if ( ! $role || ! $role->has_cap( 'read_private_cgt' ) ) {
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
