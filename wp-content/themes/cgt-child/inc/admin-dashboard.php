<?php
/**
 * Admin dashboard helpers.
 *
 * @package CGT_Child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Enregistre les sous-menus personnalisés dans "Articles adhérents".
 */
add_action( 'admin_menu', 'cgt_register_private_content_submenus', 20 );
function cgt_register_private_content_submenus() {
    if ( ! current_user_can( 'edit_posts' ) ) {
        return;
    }

    global $submenu;

    $primary_slug = 'edit.php?post_type=articles_adherents';

	// Ajouter les sous-menus personnalisés
	// Note: WordPress ajoute automatiquement "Tous les articles adhérents" comme premier sous-menu

	add_submenu_page(
		$primary_slug,
		__( 'Créer un article privé', 'cgt' ),
		__( 'Créer un article privé', 'cgt' ),
        'edit_posts',
        'cgt-add-private-article',
        'cgt_render_add_private_article_page'
    );

    add_submenu_page(
        $primary_slug,
        __( 'Créer un tract privé', 'cgt' ),
        __( 'Créer un tract privé', 'cgt' ),
        'edit_posts',
		'cgt-add-private-tract',
		'cgt_render_add_private_tract_page'
	);

	add_submenu_page(
		$primary_slug,
		__( 'Tracts privés', 'cgt' ),
		__( 'Tracts privés', 'cgt' ),
		'edit_posts',
		'edit.php?post_type=tracts&cgt_private=1'
    );

	// Note: Branches et Classes sont ajoutés automatiquement par WordPress via les taxonomies
}

/**
 * Supprimer les doublons et réorganiser les sous-menus
 */
add_action( 'admin_menu', 'cgt_cleanup_articles_adherents_submenu', 999 );
function cgt_cleanup_articles_adherents_submenu() {
	global $submenu;

	$primary_slug = 'edit.php?post_type=articles_adherents';

	if ( ! isset( $submenu[ $primary_slug ] ) ) {
		return;
	}

	// Parcourir et identifier les éléments à garder
	$new_submenu = array();
	$seen_branches = false;
	$seen_classes = false;

	foreach ( $submenu[ $primary_slug ] as $item ) {
		$title = $item[0];
		$slug = $item[2];

		// Garder uniquement le premier "Branches"
		if ( stripos( $title, 'branche' ) !== false ) {
			if ( ! $seen_branches ) {
				$new_submenu[] = $item;
				$seen_branches = true;
			}
			continue;
		}

		// Garder uniquement le premier "Classes" (ou thématique)
		if ( stripos( $title, 'classe' ) !== false || stripos( $title, 'thematique' ) !== false || stripos( $title, 'thématique' ) !== false ) {
			if ( ! $seen_classes ) {
				$new_submenu[] = $item;
				$seen_classes = true;
			}
			continue;
		}

		// Garder tous les autres éléments
		$new_submenu[] = $item;
	}

	$submenu[ $primary_slug ] = $new_submenu;
}

// Fonctions de redirection supprimées - remplacées par des liens directs dans les sous-menus
// pour éviter l'erreur "headers already sent"

/**
 * Définit l'ordre personnalisé du menu principal de l'administration.
 */
add_filter( 'custom_menu_order', '__return_true' );
add_filter( 'menu_order', 'cgt_custom_menu_order' );
function cgt_custom_menu_order( $menu_order ) {
	if ( ! is_array( $menu_order ) ) {
		return $menu_order;
	}

	$desired_order = array(
		'index.php',                                // Tableau de bord.
		'edit.php',                                 // Articles.
		'edit.php?post_type=tracts',                // Tracts.
		'edit.php?post_type=page',                  // Pages.
		'edit.php?post_type=cgt_petition',          // Pétitions.
		'edit.php?post_type=cgt_pdf_library',       // Bibliothèque.
		'upload.php',                               // Médias.
		'cgt-newsletter',                           // Liste de diffusion.
		'edit.php?post_type=cgt_ad',                // Ads (si présent).
		'edit.php?post_type=articles_adherents',    // Articles adhérents.
		'edit.php?post_type=cgt_contact',           // Messages.
		'edit.php?post_type=cgt_agenda',            // Événements.
		'edit.php?post_type=cgt_adhesion',          // Adhérents.
		'themes.php',                               // Apparence.
		'plugins.php',                              // Extensions.
		'tools.php',                                // Outils.
		'options-general.php',                      // Réglages.
	);

	$ordered = array();

	foreach ( $desired_order as $slug ) {
		$key = array_search( $slug, $menu_order, true );
		if ( false !== $key ) {
			$ordered[] = $menu_order[ $key ];
			unset( $menu_order[ $key ] );
		}
	}

	return array_merge( $ordered, $menu_order );
}

/**
 * Filtre la liste des tracts pour n'afficher que les contenus privés lorsque demandé.
 */
add_action( 'load-edit.php', 'cgt_maybe_filter_private_tracts_list' );
function cgt_maybe_filter_private_tracts_list() {
	$screen = get_current_screen();
	if ( ! $screen || 'edit-tracts' !== $screen->id ) {
		return;
	}

	if ( isset( $_GET['cgt_private'] ) && '1' === $_GET['cgt_private'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		add_filter( 'pre_get_posts', 'cgt_filter_admin_private_tracts' );
	}
}

function cgt_filter_admin_private_tracts( $query ) {
	if ( ! is_admin() || ! $query->is_main_query() ) {
		return $query;
	}

	$meta_query = (array) $query->get( 'meta_query', array() );
	$meta_query[] = array(
		'key'   => 'cgt_visibilite',
		'value' => 'prive',
	);

	$query->set( 'post_status', array( 'private' ) );
	$query->set( 'meta_query', $meta_query );

	return $query;
}

/**
 * Gestion du formulaire "Ma branche" pour l'espace adhérent.
 */
add_action( 'admin_post_cgt_select_user_branch', 'cgt_handle_user_branch_selection' );
add_action( 'admin_post_nopriv_cgt_select_user_branch', 'cgt_handle_user_branch_selection' );

function cgt_handle_user_branch_selection() {
	if ( ! is_user_logged_in() ) {
	wp_die( esc_html__( 'Vous devez être connecté.', 'cgt' ) );
	}

	check_admin_referer( 'cgt_select_branch', 'cgt_branch_nonce' );

	$user_id   = get_current_user_id();
	$branch_id = isset( $_POST['user_branch'] ) ? absint( $_POST['user_branch'] ) : 0;

	if ( ! $branch_id ) {
		wp_safe_redirect( home_url( '/espace-adherent' ) );
		exit;
	}

	$branch_term = get_term( $branch_id, 'branche' );
	if ( ! $branch_term || is_wp_error( $branch_term ) ) {
		wp_safe_redirect( home_url( '/espace-adherent' ) );
		exit;
	}

	update_user_meta( $user_id, 'cgt_user_branch', $branch_id );

	wp_safe_redirect( home_url( '/espace-adherent' ) );
	exit;
}
