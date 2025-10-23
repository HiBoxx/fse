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
	}

	// Grant capability to editors & admins.
	foreach ( array( 'administrator', 'editor' ) as $role_key ) {
		$role_object = get_role( $role_key );
		if ( $role_object ) {
			$role_object->add_cap( 'read_private_cgt' );
			$role_object->add_cap( 'read_private_articles_adherents' );
		}
	}
}

add_action(
	'init',
	function () {
		$role = get_role( 'adherent' );
		if ( ! $role || ! $role->has_cap( 'read_private_cgt' ) ) {
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
		$meta_query   = (array) $query->get( 'meta_query', array() );
		$meta_query[] = array(
			'key'     => 'cgt_visibilite',
			'value'   => 'prive',
			'compare' => '!=',
		);
		$query->set( 'meta_query', $meta_query );
	}
}
add_action( 'pre_get_posts', 'cgt_filter_private_content' );

/**
 * Restrict access to private tracts and member-only content directly.
 */
function cgt_redirect_private_single() {
	// Vérifier les tracts privés
	if ( is_singular( 'tracts' ) ) {
		$visibility = get_post_meta( get_queried_object_id(), 'cgt_visibilite', true );
		if ( 'prive' === $visibility && ! cgt_user_can_read_private() ) {
			// Si l'utilisateur n'est pas connecté, rediriger vers la page de connexion
			if ( ! is_user_logged_in() ) {
				wp_safe_redirect( wp_login_url( get_permalink() ) );
				exit;
			}

			// Si l'utilisateur est connecté mais n'a pas les droits, afficher 403
			wp_die(
				'<h1>Accès refusé</h1><p>Ce contenu est réservé aux adhérents de la CGT. Veuillez vous connecter avec un compte adhérent valide.</p>',
				'Accès refusé',
				array( 'response' => 403 )
			);
		}
	}

	// Vérifier les articles réservés aux membres
	if ( is_singular( 'articles_adherents' ) ) {
		if ( ! cgt_user_can_read_private() ) {
			// Si l'utilisateur n'est pas connecté, rediriger vers la page de connexion
			if ( ! is_user_logged_in() ) {
				wp_safe_redirect( wp_login_url( get_permalink() ) );
				exit;
			}

			// Si l'utilisateur est connecté mais n'a pas les droits, afficher 403
			wp_die(
				'<h1>Accès refusé</h1><p>Ce contenu est réservé aux adhérents de la CGT. Veuillez vous connecter avec un compte adhérent valide.</p>',
				'Accès refusé',
				array( 'response' => 403 )
			);
		}
	}
}
add_action( 'template_redirect', 'cgt_redirect_private_single', 1 );
