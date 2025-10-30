<?php
/**
 * Custom Dashboards for CGT Roles
 * Tableaux de bord personnalisés pour Administration, Gestionnaire, Assistante
 *
 * @package CGT_Child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Créer les 3 rôles personnalisés au chargement du thème
 */
add_action( 'after_setup_theme', 'cgt_create_custom_roles' );

function cgt_create_custom_roles() {
	// Vérifier si les rôles existent déjà
	if ( get_role( 'cgt_administration' ) && get_role( 'cgt_gestionnaire' ) && get_role( 'cgt_assistante' ) ) {
		return;
	}

	// Rôle 1 : Administration
	// Accès : Adhérents, Adhésions, Section bancaire
	$administration_caps = array(
		'read'                   => true,
		// Capacités adhésions
		'edit_posts'             => true,
		'edit_published_posts'   => true,
		'delete_posts'           => true,
		'delete_published_posts' => true,
		// Accès au tableau de bord
		'access_cgt_admin_dashboard' => true,
	);

	add_role( 'cgt_administration', 'CGT Administration', $administration_caps );

	// Rôle 2 : Gestionnaire
	// Accès : Publier des contenus (articles, tracts, pétitions, événements)
	$gestionnaire_caps = array(
		'read'                   => true,
		'edit_posts'             => true,
		'publish_posts'          => true,
		'upload_files'           => true,
		// Accès au tableau de bord gestionnaire
		'access_cgt_gestionnaire_dashboard' => true,
	);

	add_role( 'cgt_gestionnaire', 'CGT Gestionnaire', $gestionnaire_caps );

	// Rôle 3 : Assistante
	// Accès : Lecture seule adhérents + export PDF
	$assistante_caps = array(
		'read' => true,
		// Accès au tableau de bord assistante (lecture seule)
		'access_cgt_assistante_dashboard' => true,
	);

	add_role( 'cgt_assistante', 'CGT Assistante', $assistante_caps );
}

/**
 * Créer automatiquement les comptes utilisateurs s'ils n'existent pas
 */
add_action( 'after_setup_theme', 'cgt_create_default_users', 20 );

function cgt_create_default_users() {
	// Compte 1 : administration
	if ( ! username_exists( 'administration' ) && ! email_exists( 'administration@cgt-local.fr' ) ) {
		$user_id = wp_create_user( 'administration', 'admin123', 'administration@cgt-local.fr' );
		if ( ! is_wp_error( $user_id ) ) {
			$user = get_user_by( 'id', $user_id );
			$user->remove_role( 'subscriber' );
			$user->add_role( 'cgt_administration' );
		}
	}

	// Compte 2 : gestionnaire
	if ( ! username_exists( 'gestionnaire' ) && ! email_exists( 'gestionnaire@cgt-local.fr' ) ) {
		$user_id = wp_create_user( 'gestionnaire', 'gestion123', 'gestionnaire@cgt-local.fr' );
		if ( ! is_wp_error( $user_id ) ) {
			$user = get_user_by( 'id', $user_id );
			$user->remove_role( 'subscriber' );
			$user->add_role( 'cgt_gestionnaire' );
		}
	}

	// Compte 3 : assistante
	if ( ! username_exists( 'assistante' ) && ! email_exists( 'assistante@cgt-local.fr' ) ) {
		$user_id = wp_create_user( 'assistante', 'assist123', 'assistante@cgt-local.fr' );
		if ( ! is_wp_error( $user_id ) ) {
			$user = get_user_by( 'id', $user_id );
			$user->remove_role( 'subscriber' );
			$user->add_role( 'cgt_assistante' );
		}
	}
}

/**
 * Ajouter les pages de menu pour chaque rôle
 */
add_action( 'admin_menu', 'cgt_register_custom_dashboards' );

function cgt_register_custom_dashboards() {
	$current_user = wp_get_current_user();

	// Tableau de bord Administration
	if ( in_array( 'cgt_administration', $current_user->roles, true ) || current_user_can( 'manage_options' ) ) {
		add_menu_page(
			__( 'Tableau de bord Administration', 'cgt' ),
			__( 'Administration', 'cgt' ),
			'access_cgt_admin_dashboard',
			'cgt-dashboard-administration',
			'cgt_render_administration_dashboard',
			'dashicons-businessman',
			2
		);

		// Sous-menu : Adhérents
		add_submenu_page(
			'cgt-dashboard-administration',
			__( 'Adhérents', 'cgt' ),
			__( 'Adhérents', 'cgt' ),
			'access_cgt_admin_dashboard',
			'edit.php?post_type=cgt_adhesion'
		);

		// Sous-menu : Section Banque
		add_submenu_page(
			'cgt-dashboard-administration',
			__( 'Banque', 'cgt' ),
			__( 'Banque', 'cgt' ),
			'access_cgt_admin_dashboard',
			'cgt-banque',
			'cgt_render_banque_page'
		);
	}

	// Tableau de bord Gestionnaire
	if ( in_array( 'cgt_gestionnaire', $current_user->roles, true ) || current_user_can( 'manage_options' ) ) {
		add_menu_page(
			__( 'Tableau de bord Gestionnaire', 'cgt' ),
			__( 'Gestionnaire', 'cgt' ),
			'access_cgt_gestionnaire_dashboard',
			'cgt-dashboard-gestionnaire',
			'cgt_render_gestionnaire_dashboard',
			'dashicons-edit',
			2
		);

		// Sous-menu : Publier un contenu
		add_submenu_page(
			'cgt-dashboard-gestionnaire',
			__( 'Publier un contenu', 'cgt' ),
			__( 'Publier un contenu', 'cgt' ),
			'access_cgt_gestionnaire_dashboard',
			'cgt-publier-contenu',
			'cgt_render_publish_content_page'
		);
	}

	// Tableau de bord Assistante
	if ( in_array( 'cgt_assistante', $current_user->roles, true ) || current_user_can( 'manage_options' ) ) {
		add_menu_page(
			__( 'Tableau de bord Assistante', 'cgt' ),
			__( 'Assistante', 'cgt' ),
			'access_cgt_assistante_dashboard',
			'cgt-dashboard-assistante',
			'cgt_render_assistante_dashboard',
			'dashicons-visibility',
			2
		);
	}
}

/**
 * Masquer les menus WordPress par défaut pour les rôles personnalisés
 */
add_action( 'admin_menu', 'cgt_hide_default_menus_for_custom_roles', 999 );

function cgt_hide_default_menus_for_custom_roles() {
	$current_user = wp_get_current_user();
	$roles        = $current_user->roles;

	// Pour tous les rôles personnalisés, masquer les menus par défaut
	if ( in_array( 'cgt_administration', $roles, true ) || in_array( 'cgt_gestionnaire', $roles, true ) || in_array( 'cgt_assistante', $roles, true ) ) {
		remove_menu_page( 'index.php' );                  // Tableau de bord
		remove_menu_page( 'edit.php' );                   // Articles
		remove_menu_page( 'upload.php' );                 // Médias
		remove_menu_page( 'edit.php?post_type=page' );    // Pages
		remove_menu_page( 'edit-comments.php' );          // Commentaires
		remove_menu_page( 'themes.php' );                 // Apparence
		remove_menu_page( 'plugins.php' );                // Extensions
		remove_menu_page( 'users.php' );                  // Utilisateurs
		remove_menu_page( 'tools.php' );                  // Outils
		remove_menu_page( 'options-general.php' );        // Réglages
		remove_menu_page( 'edit.php?post_type=tracts' );
		remove_menu_page( 'edit.php?post_type=articles_adherents' );
	}

	// Pour Administration, garder uniquement l'accès aux adhésions
	if ( in_array( 'cgt_administration', $roles, true ) && ! current_user_can( 'manage_options' ) ) {
		// Tout est déjà masqué ci-dessus, sauf ce qu'on ajoute dans notre menu
	}

	// Pour Gestionnaire, tout est masqué sauf son menu
	if ( in_array( 'cgt_gestionnaire', $roles, true ) && ! current_user_can( 'manage_options' ) ) {
		// Tout est déjà masqué
	}

	// Pour Assistante, tout est masqué sauf son menu
	if ( in_array( 'cgt_assistante', $roles, true ) && ! current_user_can( 'manage_options' ) ) {
		// Tout est déjà masqué
	}
}

/**
 * Rediriger les utilisateurs vers leur tableau de bord personnalisé après connexion
 */
add_filter( 'login_redirect', 'cgt_custom_login_redirect', 10, 3 );

function cgt_custom_login_redirect( $redirect_to, $request, $user ) {
	if ( isset( $user->roles ) && is_array( $user->roles ) ) {
		if ( in_array( 'cgt_administration', $user->roles, true ) ) {
			return admin_url( 'admin.php?page=cgt-dashboard-administration' );
		} elseif ( in_array( 'cgt_gestionnaire', $user->roles, true ) ) {
			return admin_url( 'admin.php?page=cgt-dashboard-gestionnaire' );
		} elseif ( in_array( 'cgt_assistante', $user->roles, true ) ) {
			return admin_url( 'admin.php?page=cgt-dashboard-assistante' );
		}
	}

	return $redirect_to;
}

/**
 * Bloquer l'accès à wp-admin pour les utilisateurs non autorisés (sauf ajax)
 */
add_action( 'admin_init', 'cgt_restrict_admin_access' );

function cgt_restrict_admin_access() {
	// Ne pas bloquer les requêtes AJAX
	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		return;
	}

	$current_user = wp_get_current_user();
	$roles        = $current_user->roles;

	// Si l'utilisateur a un rôle personnalisé, vérifier qu'il accède à sa page
	if ( ! empty( $roles ) ) {
		$is_custom_role = in_array( 'cgt_administration', $roles, true ) ||
		                  in_array( 'cgt_gestionnaire', $roles, true ) ||
		                  in_array( 'cgt_assistante', $roles, true );

		// Si c'est un rôle personnalisé et qu'il n'a pas les droits d'admin
		if ( $is_custom_role && ! current_user_can( 'manage_options' ) ) {
			// Vérifier qu'il est sur sa propre page de dashboard
			$current_page = isset( $_GET['page'] ) ? sanitize_key( $_GET['page'] ) : '';

			$allowed_pages = array();
			if ( in_array( 'cgt_administration', $roles, true ) ) {
				$allowed_pages = array( 'cgt-dashboard-administration', 'cgt-banque' );
			} elseif ( in_array( 'cgt_gestionnaire', $roles, true ) ) {
				$allowed_pages = array( 'cgt-dashboard-gestionnaire', 'cgt-publier-contenu' );
			} elseif ( in_array( 'cgt_assistante', $roles, true ) ) {
				$allowed_pages = array( 'cgt-dashboard-assistante' );
			}

			// Si pas sur une page autorisée, rediriger
			if ( ! in_array( $current_page, $allowed_pages, true ) && ! strpos( $_SERVER['REQUEST_URI'], 'profile.php' ) ) {
				// Permettre l'accès au profil
				if ( ! strpos( $_SERVER['REQUEST_URI'], 'profile.php' ) && ! strpos( $_SERVER['REQUEST_URI'], 'admin-ajax.php' ) ) {
					wp_die(
						__( 'Vous n\'avez pas les permissions nécessaires pour accéder à cette page.', 'cgt' ),
						__( 'Accès refusé', 'cgt' ),
						array( 'response' => 403 )
					);
				}
			}
		}
	}
}

// ============================================
// RENDU DES TABLEAUX DE BORD
// ============================================

/**
 * Tableau de bord Administration
 */
function cgt_render_administration_dashboard() {
	if ( ! current_user_can( 'access_cgt_admin_dashboard' ) && ! current_user_can( 'manage_options' ) ) {
		wp_die( __( 'Accès refusé.', 'cgt' ) );
	}

	// Statistiques
	$total_adhesions = wp_count_posts( 'cgt_adhesion' );
	$pending_count   = $total_adhesions->pending ?? 0;
	$published_count = $total_adhesions->publish ?? 0;
	$total_count     = $pending_count + $published_count;

	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Tableau de bord Administration', 'cgt' ); ?></h1>
		<p style="font-size: 16px;"><?php esc_html_e( 'Bienvenue dans votre espace de gestion des adhérents et adhésions.', 'cgt' ); ?></p>

		<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-top: 30px;">
			<!-- Card Adhésions -->
			<div style="background: white; border: 1px solid #c3c4c7; border-radius: 8px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
				<h2 style="margin-top: 0; font-size: 18px; color: #c8102e;">
					👥 <?php esc_html_e( 'Adhésions', 'cgt' ); ?>
				</h2>
				<p style="font-size: 32px; font-weight: 600; margin: 10px 0; color: #1d2939;"><?php echo esc_html( $total_count ); ?></p>
				<p style="color: #6b7280; margin-bottom: 15px;">
					<?php echo esc_html( sprintf( __( '%d en attente', 'cgt' ), $pending_count ) ); ?>
				</p>
				<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=cgt_adhesion' ) ); ?>" class="button button-primary">
					<?php esc_html_e( 'Gérer les adhésions', 'cgt' ); ?>
				</a>
			</div>

			<!-- Card Banque -->
			<div style="background: white; border: 1px solid #c3c4c7; border-radius: 8px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
				<h2 style="margin-top: 0; font-size: 18px; color: #c8102e;">
					💳 <?php esc_html_e( 'Section Banque', 'cgt' ); ?>
				</h2>
				<p><?php esc_html_e( 'Gérez les informations bancaires et les mandats SEPA.', 'cgt' ); ?></p>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=cgt-banque' ) ); ?>" class="button button-primary">
					<?php esc_html_e( 'Accéder', 'cgt' ); ?>
				</a>
			</div>

			<!-- Card Exports -->
			<div style="background: white; border: 1px solid #c3c4c7; border-radius: 8px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
				<h2 style="margin-top: 0; font-size: 18px; color: #c8102e;">
					📄 <?php esc_html_e( 'Exports', 'cgt' ); ?>
				</h2>
				<p><?php esc_html_e( 'Téléchargez les listes d\'adhérents au format PDF.', 'cgt' ); ?></p>
				<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=cgt_adhesion&page=cgt-adhesions-dashboard' ) ); ?>" class="button button-primary">
					<?php esc_html_e( 'Voir les exports', 'cgt' ); ?>
				</a>
			</div>
		</div>
	</div>

	<style>
		.button-primary {
			background: linear-gradient(135deg, #c8102e 0%, #a00d26 100%) !important;
			border-color: #c8102e !important;
		}
		.button-primary:hover {
			background: #a00d26 !important;
			border-color: #a00d26 !important;
		}
	</style>
	<?php
}

/**
 * Tableau de bord Gestionnaire
 */
function cgt_render_gestionnaire_dashboard() {
	if ( ! current_user_can( 'access_cgt_gestionnaire_dashboard' ) && ! current_user_can( 'manage_options' ) ) {
		wp_die( __( 'Accès refusé.', 'cgt' ) );
	}

	// Statistiques des publications de l'utilisateur
	$user_id = get_current_user_id();
	$args    = array(
		'author'         => $user_id,
		'post_type'      => array( 'post', 'tracts', 'cgt_petition', 'cgt_agenda' ),
		'post_status'    => 'publish',
		'posts_per_page' => -1,
	);
	$user_posts = new WP_Query( $args );
	$total_publications = $user_posts->found_posts;

	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Tableau de bord Gestionnaire', 'cgt' ); ?></h1>
		<p style="font-size: 16px;"><?php esc_html_e( 'Bienvenue dans votre espace de gestion de contenu.', 'cgt' ); ?></p>

		<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-top: 30px;">
			<!-- Card Publier -->
			<div style="background: white; border: 1px solid #c3c4c7; border-radius: 8px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
				<h2 style="margin-top: 0; font-size: 18px; color: #c8102e;">
					✍️ <?php esc_html_e( 'Publier un contenu', 'cgt' ); ?>
				</h2>
				<p><?php esc_html_e( 'Créez et publiez des articles, tracts, pétitions ou événements.', 'cgt' ); ?></p>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=cgt-publier-contenu' ) ); ?>" class="button button-primary button-hero">
					<?php esc_html_e( 'Publier maintenant', 'cgt' ); ?>
				</a>
			</div>

			<!-- Card Mes publications -->
			<div style="background: white; border: 1px solid #c3c4c7; border-radius: 8px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
				<h2 style="margin-top: 0; font-size: 18px; color: #c8102e;">
					📚 <?php esc_html_e( 'Mes publications', 'cgt' ); ?>
				</h2>
				<p style="font-size: 32px; font-weight: 600; margin: 10px 0; color: #1d2939;"><?php echo esc_html( $total_publications ); ?></p>
				<p style="color: #6b7280;"><?php esc_html_e( 'Publications totales', 'cgt' ); ?></p>
			</div>
		</div>
	</div>

	<style>
		.button-primary {
			background: linear-gradient(135deg, #c8102e 0%, #a00d26 100%) !important;
			border-color: #c8102e !important;
		}
		.button-primary:hover {
			background: #a00d26 !important;
			border-color: #a00d26 !important;
		}
	</style>
	<?php
}

/**
 * Tableau de bord Assistante
 */
function cgt_render_assistante_dashboard() {
	if ( ! current_user_can( 'access_cgt_assistante_dashboard' ) && ! current_user_can( 'manage_options' ) ) {
		wp_die( __( 'Accès refusé.', 'cgt' ) );
	}

	// Liste des adhésions
	$args  = array(
		'post_type'      => 'cgt_adhesion',
		'post_status'    => array( 'pending', 'publish', 'draft' ),
		'posts_per_page' => 50,
		'orderby'        => 'date',
		'order'          => 'DESC',
	);
	$query = new WP_Query( $args );

	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Tableau de bord Assistante', 'cgt' ); ?></h1>
		<p style="font-size: 16px;"><?php esc_html_e( 'Consultation des adhésions en lecture seule.', 'cgt' ); ?></p>

		<div style="margin-top: 30px;">
			<h2><?php esc_html_e( 'Liste des adhérents', 'cgt' ); ?></h2>
			<table class="widefat fixed striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Nom', 'cgt' ); ?></th>
						<th><?php esc_html_e( 'Prénom', 'cgt' ); ?></th>
						<th><?php esc_html_e( 'Date d\'adhésion', 'cgt' ); ?></th>
						<th><?php esc_html_e( 'Email', 'cgt' ); ?></th>
						<th><?php esc_html_e( 'Statut', 'cgt' ); ?></th>
						<th><?php esc_html_e( 'PDF', 'cgt' ); ?></th>
					</tr>
				</thead>
				<tbody>
				<?php if ( $query->have_posts() ) : ?>
					<?php
					while ( $query->have_posts() ) :
						$query->the_post();
						$post_id    = get_the_ID();
						$nom        = get_post_meta( $post_id, '_adhesion_nom', true );
						$prenom     = get_post_meta( $post_id, '_adhesion_prenom', true );
						$email      = get_post_meta( $post_id, '_adhesion_email', true );
						$date_adhesion = get_the_date( 'd/m/Y' );
						$status     = get_post_status();
						$status_label = $status === 'pending' ? 'En attente' : 'Validée';

						$download_url = wp_nonce_url(
							admin_url( 'admin-post.php?action=cgt_download_adhesion_pdf&post_id=' . $post_id ),
							'cgt_download_pdf_' . $post_id
						);
						?>
						<tr>
							<td><?php echo esc_html( $nom ); ?></td>
							<td><?php echo esc_html( $prenom ); ?></td>
							<td><?php echo esc_html( $date_adhesion ); ?></td>
							<td><?php echo esc_html( $email ); ?></td>
							<td><?php echo esc_html( $status_label ); ?></td>
							<td><a class="button" href="<?php echo esc_url( $download_url ); ?>"><?php esc_html_e( 'Télécharger', 'cgt' ); ?></a></td>
						</tr>
					<?php endwhile; ?>
				<?php else : ?>
					<tr>
						<td colspan="6"><?php esc_html_e( 'Aucune adhésion enregistrée.', 'cgt' ); ?></td>
					</tr>
				<?php endif; ?>
				</tbody>
			</table>
		</div>
	</div>

	<style>
		.widefat th {
			background: #f9fafb;
			font-weight: 600;
		}
		.button {
			background: #c8102e !important;
			color: white !important;
			border-color: #c8102e !important;
		}
		.button:hover {
			background: #a00d26 !important;
			border-color: #a00d26 !important;
		}
	</style>
	<?php
	wp_reset_postdata();
}

/**
 * Page Section Banque (pour Administration)
 */
function cgt_render_banque_page() {
	if ( ! current_user_can( 'access_cgt_admin_dashboard' ) && ! current_user_can( 'manage_options' ) ) {
		wp_die( __( 'Accès refusé.', 'cgt' ) );
	}

	$message = '';
	$errors  = array();

	// Traitement du formulaire
	if ( isset( $_POST['cgt_banque_submit'] ) ) {
		if ( ! isset( $_POST['cgt_banque_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['cgt_banque_nonce'] ), 'cgt_banque' ) ) {
			$errors[] = __( 'Erreur de sécurité.', 'cgt' );
		} else {
			$nom       = isset( $_POST['cgt_banque_nom'] ) ? sanitize_text_field( wp_unslash( $_POST['cgt_banque_nom'] ) ) : '';
			$prenom    = isset( $_POST['cgt_banque_prenom'] ) ? sanitize_text_field( wp_unslash( $_POST['cgt_banque_prenom'] ) ) : '';
			$rib       = isset( $_POST['cgt_banque_rib'] ) ? sanitize_text_field( wp_unslash( $_POST['cgt_banque_rib'] ) ) : '';
			$mandat_id = isset( $_POST['cgt_banque_mandat_id'] ) ? absint( $_POST['cgt_banque_mandat_id'] ) : 0;

			if ( empty( $nom ) || empty( $prenom ) || empty( $rib ) ) {
				$errors[] = __( 'Tous les champs sont obligatoires.', 'cgt' );
			}

			if ( empty( $errors ) ) {
				// Créer un dossier pour les mandats si nécessaire
				$upload_dir = wp_upload_dir();
				$mandats_dir = $upload_dir['basedir'] . '/mandats';
				if ( ! file_exists( $mandats_dir ) ) {
					wp_mkdir_p( $mandats_dir );
				}

				// Créer un post pour stocker les infos bancaires
				$post_data = array(
					'post_title'   => sprintf( 'Mandat SEPA - %s %s', $prenom, $nom ),
					'post_type'    => 'cgt_mandat',
					'post_status'  => 'publish',
					'post_content' => '',
				);

				$post_id = wp_insert_post( $post_data );

				if ( $post_id && ! is_wp_error( $post_id ) ) {
					update_post_meta( $post_id, '_mandat_nom', $nom );
					update_post_meta( $post_id, '_mandat_prenom', $prenom );
					update_post_meta( $post_id, '_mandat_rib', $rib );

					if ( $mandat_id ) {
						update_post_meta( $post_id, '_mandat_pdf_id', $mandat_id );
					}

					$message = __( 'Mandat enregistré avec succès !', 'cgt' );
				} else {
					$errors[] = __( 'Erreur lors de l\'enregistrement.', 'cgt' );
				}
			}
		}
	}

	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Section Banque', 'cgt' ); ?></h1>
		<p><?php esc_html_e( 'Enregistrez les informations bancaires et les mandats SEPA des adhérents.', 'cgt' ); ?></p>

		<?php if ( ! empty( $message ) ) : ?>
			<div class="notice notice-success is-dismissible">
				<p><?php echo esc_html( $message ); ?></p>
			</div>
		<?php endif; ?>

		<?php if ( ! empty( $errors ) ) : ?>
			<div class="notice notice-error is-dismissible">
				<ul>
					<?php foreach ( $errors as $error ) : ?>
						<li><?php echo esc_html( $error ); ?></li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>

		<div style="max-width: 800px; background: white; padding: 30px; border-radius: 8px; margin-top: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
			<h2><?php esc_html_e( 'Enregistrer un nouveau mandat', 'cgt' ); ?></h2>

			<form method="post" enctype="multipart/form-data">
				<?php wp_nonce_field( 'cgt_banque', 'cgt_banque_nonce' ); ?>

				<table class="form-table">
					<tr>
						<th scope="row">
							<label for="cgt_banque_nom"><?php esc_html_e( 'Nom', 'cgt' ); ?> <span class="required">*</span></label>
						</th>
						<td>
							<input type="text" name="cgt_banque_nom" id="cgt_banque_nom" class="regular-text" required>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="cgt_banque_prenom"><?php esc_html_e( 'Prénom', 'cgt' ); ?> <span class="required">*</span></label>
						</th>
						<td>
							<input type="text" name="cgt_banque_prenom" id="cgt_banque_prenom" class="regular-text" required>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="cgt_banque_rib"><?php esc_html_e( 'RIB / IBAN', 'cgt' ); ?> <span class="required">*</span></label>
						</th>
						<td>
							<input type="text" name="cgt_banque_rib" id="cgt_banque_rib" class="regular-text" required>
							<p class="description"><?php esc_html_e( 'Saisissez le RIB ou l\'IBAN complet.', 'cgt' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="cgt_banque_mandat"><?php esc_html_e( 'Mandat signé (PDF)', 'cgt' ); ?></label>
						</th>
						<td>
							<input type="hidden" name="cgt_banque_mandat_id" id="cgt_banque_mandat_id" value="">
							<div id="mandat-preview" style="margin-bottom: 10px;"></div>
							<button type="button" class="button" id="upload-mandat-button">
								<?php esc_html_e( 'Sélectionner un PDF', 'cgt' ); ?>
							</button>
							<button type="button" class="button" id="remove-mandat-button" style="display:none;">
								<?php esc_html_e( 'Supprimer', 'cgt' ); ?>
							</button>
							<p class="description"><?php esc_html_e( 'Téléchargez le mandat SEPA signé (max 10 MB).', 'cgt' ); ?></p>
						</td>
					</tr>
				</table>

				<p class="submit">
					<button type="submit" name="cgt_banque_submit" class="button button-primary button-hero">
						<?php esc_html_e( 'Enregistrer le mandat', 'cgt' ); ?>
					</button>
				</p>
			</form>
		</div>

		<!-- Liste des mandats existants -->
		<div style="max-width: 800px; background: white; padding: 30px; border-radius: 8px; margin-top: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
			<h2><?php esc_html_e( 'Mandats enregistrés', 'cgt' ); ?></h2>
			<?php
			$mandats = new WP_Query(
				array(
					'post_type'      => 'cgt_mandat',
					'post_status'    => 'publish',
					'posts_per_page' => 50,
					'orderby'        => 'date',
					'order'          => 'DESC',
				)
			);
			?>
			<?php if ( $mandats->have_posts() ) : ?>
				<table class="widefat fixed striped">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Nom', 'cgt' ); ?></th>
							<th><?php esc_html_e( 'Prénom', 'cgt' ); ?></th>
							<th><?php esc_html_e( 'RIB', 'cgt' ); ?></th>
							<th><?php esc_html_e( 'Mandat PDF', 'cgt' ); ?></th>
							<th><?php esc_html_e( 'Date', 'cgt' ); ?></th>
						</tr>
					</thead>
					<tbody>
					<?php
					while ( $mandats->have_posts() ) :
						$mandats->the_post();
						$post_id    = get_the_ID();
						$nom        = get_post_meta( $post_id, '_mandat_nom', true );
						$prenom     = get_post_meta( $post_id, '_mandat_prenom', true );
						$rib        = get_post_meta( $post_id, '_mandat_rib', true );
						$mandat_pdf = get_post_meta( $post_id, '_mandat_pdf_id', true );
						$pdf_url    = $mandat_pdf ? wp_get_attachment_url( $mandat_pdf ) : '';
						?>
						<tr>
							<td><?php echo esc_html( $nom ); ?></td>
							<td><?php echo esc_html( $prenom ); ?></td>
							<td><?php echo esc_html( $rib ); ?></td>
							<td>
								<?php if ( $pdf_url ) : ?>
									<a href="<?php echo esc_url( $pdf_url ); ?>" target="_blank" class="button button-small">
										<?php esc_html_e( 'Télécharger', 'cgt' ); ?>
									</a>
								<?php else : ?>
									<span style="color: #999;">—</span>
								<?php endif; ?>
							</td>
							<td><?php echo esc_html( get_the_date( 'd/m/Y' ) ); ?></td>
						</tr>
					<?php endwhile; ?>
					</tbody>
				</table>
			<?php else : ?>
				<p><?php esc_html_e( 'Aucun mandat enregistré.', 'cgt' ); ?></p>
			<?php endif; ?>
			<?php wp_reset_postdata(); ?>
		</div>
	</div>

	<script>
	jQuery(document).ready(function($) {
		var mandatUploader;
		$('#upload-mandat-button').on('click', function(e) {
			e.preventDefault();
			if (mandatUploader) {
				mandatUploader.open();
				return;
			}
			mandatUploader = wp.media({
				title: 'Choisir un PDF',
				button: { text: 'Sélectionner' },
				library: { type: 'application/pdf' },
				multiple: false
			});
			mandatUploader.on('select', function() {
				var attachment = mandatUploader.state().get('selection').first().toJSON();
				$('#cgt_banque_mandat_id').val(attachment.id);
				$('#mandat-preview').html('<p style="color: #059669; font-weight: 600;">📄 ' + attachment.filename + '</p>');
				$('#remove-mandat-button').show();
			});
			mandatUploader.open();
		});

		$('#remove-mandat-button').on('click', function(e) {
			e.preventDefault();
			$('#cgt_banque_mandat_id').val('');
			$('#mandat-preview').html('');
			$(this).hide();
		});
	});
	</script>

	<style>
		.button-primary {
			background: linear-gradient(135deg, #c8102e 0%, #a00d26 100%) !important;
			border-color: #c8102e !important;
		}
		.button-primary:hover {
			background: #a00d26 !important;
			border-color: #a00d26 !important;
		}
		.required {
			color: #c8102e;
		}
	</style>
	<?php
}

/**
 * Page Publier un contenu (pour Gestionnaire)
 */
function cgt_render_publish_content_page() {
	if ( ! current_user_can( 'access_cgt_gestionnaire_dashboard' ) && ! current_user_can( 'manage_options' ) ) {
		wp_die( __( 'Accès refusé.', 'cgt' ) );
	}

	$message = '';
	$errors  = array();

	// Traitement du formulaire
	if ( isset( $_POST['cgt_publish_content_submit'] ) ) {
		if ( ! isset( $_POST['cgt_publish_content_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['cgt_publish_content_nonce'] ), 'cgt_publish_content' ) ) {
			$errors[] = __( 'Erreur de sécurité.', 'cgt' );
		} else {
			$type       = isset( $_POST['cgt_content_type'] ) ? sanitize_key( $_POST['cgt_content_type'] ) : '';
			$title      = isset( $_POST['cgt_content_title'] ) ? sanitize_text_field( wp_unslash( $_POST['cgt_content_title'] ) ) : '';
			$content    = isset( $_POST['cgt_content_content'] ) ? wp_kses_post( wp_unslash( $_POST['cgt_content_content'] ) ) : '';
			$visibility = isset( $_POST['cgt_content_visibility'] ) ? sanitize_key( $_POST['cgt_content_visibility'] ) : 'public';

			if ( empty( $type ) || empty( $title ) || empty( $content ) ) {
				$errors[] = __( 'Tous les champs sont obligatoires.', 'cgt' );
			}

			// Mapper le type vers le post_type WordPress
			$post_type_map = array(
				'article'   => 'post',
				'tract'     => 'tracts',
				'petition'  => 'cgt_petition',
				'evenement' => 'cgt_agenda',
			);

			if ( ! isset( $post_type_map[ $type ] ) ) {
				$errors[] = __( 'Type de contenu invalide.', 'cgt' );
			}

			if ( empty( $errors ) ) {
				$post_status = ( 'prive' === $visibility ) ? 'private' : 'publish';
				$post_data   = array(
					'post_type'    => $post_type_map[ $type ],
					'post_title'   => $title,
					'post_content' => $content,
					'post_status'  => $post_status,
					'post_author'  => get_current_user_id(),
				);

				$post_id = wp_insert_post( $post_data );

				if ( $post_id && ! is_wp_error( $post_id ) ) {
					// Si tract et visibilité privée, ajouter le meta
					if ( 'tracts' === $post_type_map[ $type ] && 'prive' === $visibility ) {
						update_post_meta( $post_id, 'cgt_visibilite', 'prive' );
					}

					$message = sprintf(
						__( 'Contenu publié avec succès ! <a href="%s" target="_blank">Voir le contenu</a>', 'cgt' ),
						get_permalink( $post_id )
					);
				} else {
					$errors[] = __( 'Erreur lors de la publication.', 'cgt' );
				}
			}
		}
	}

	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Publier un contenu', 'cgt' ); ?></h1>
		<p><?php esc_html_e( 'Créez et publiez des articles, tracts, pétitions ou événements sur le site CGT.', 'cgt' ); ?></p>

		<?php if ( ! empty( $message ) ) : ?>
			<div class="notice notice-success is-dismissible">
				<p><?php echo wp_kses_post( $message ); ?></p>
			</div>
		<?php endif; ?>

		<?php if ( ! empty( $errors ) ) : ?>
			<div class="notice notice-error is-dismissible">
				<ul>
					<?php foreach ( $errors as $error ) : ?>
						<li><?php echo esc_html( $error ); ?></li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>

		<div style="max-width: 900px; background: white; padding: 30px; border-radius: 8px; margin-top: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
			<form method="post">
				<?php wp_nonce_field( 'cgt_publish_content', 'cgt_publish_content_nonce' ); ?>

				<table class="form-table">
					<tr>
						<th scope="row">
							<label for="cgt_content_type"><?php esc_html_e( 'Type de contenu', 'cgt' ); ?> <span class="required">*</span></label>
						</th>
						<td>
							<select name="cgt_content_type" id="cgt_content_type" class="regular-text" required>
								<option value=""><?php esc_html_e( '— Choisir un type —', 'cgt' ); ?></option>
								<option value="article"><?php esc_html_e( 'Article', 'cgt' ); ?></option>
								<option value="tract"><?php esc_html_e( 'Tract', 'cgt' ); ?></option>
								<option value="petition"><?php esc_html_e( 'Pétition', 'cgt' ); ?></option>
								<option value="evenement"><?php esc_html_e( 'Événement', 'cgt' ); ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="cgt_content_title"><?php esc_html_e( 'Titre', 'cgt' ); ?> <span class="required">*</span></label>
						</th>
						<td>
							<input type="text" name="cgt_content_title" id="cgt_content_title" class="regular-text" required>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="cgt_content_content"><?php esc_html_e( 'Contenu', 'cgt' ); ?> <span class="required">*</span></label>
						</th>
						<td>
							<?php
							wp_editor(
								'',
								'cgt_content_content',
								array(
									'textarea_name' => 'cgt_content_content',
									'media_buttons' => true,
									'textarea_rows' => 12,
									'teeny'         => false,
									'tinymce'       => true,
									'quicktags'     => true,
								)
							);
							?>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="cgt_content_visibility"><?php esc_html_e( 'Visibilité', 'cgt' ); ?></label>
						</th>
						<td>
							<label>
								<input type="radio" name="cgt_content_visibility" value="public" checked>
								<?php esc_html_e( 'Public', 'cgt' ); ?>
							</label>
							<br>
							<label>
								<input type="radio" name="cgt_content_visibility" value="prive">
								<?php esc_html_e( 'Privé (réservé aux adhérents)', 'cgt' ); ?>
							</label>
						</td>
					</tr>
				</table>

				<p class="submit">
					<button type="submit" name="cgt_publish_content_submit" class="button button-primary button-hero">
						<?php esc_html_e( 'Publier', 'cgt' ); ?>
					</button>
				</p>
			</form>
		</div>
	</div>

	<style>
		.button-primary {
			background: linear-gradient(135deg, #c8102e 0%, #a00d26 100%) !important;
			border-color: #c8102e !important;
		}
		.button-primary:hover {
			background: #a00d26 !important;
			border-color: #a00d26 !important;
		}
		.required {
			color: #c8102e;
		}
	</style>
	<?php
}

/**
 * Enregistrer le custom post type pour les mandats
 */
add_action( 'init', 'cgt_register_mandat_cpt' );

function cgt_register_mandat_cpt() {
	$labels = array(
		'name'          => 'Mandats SEPA',
		'singular_name' => 'Mandat SEPA',
	);

	$args = array(
		'labels'              => $labels,
		'public'              => false,
		'show_ui'             => false,
		'show_in_menu'        => false,
		'capability_type'     => 'post',
		'supports'            => array( 'title' ),
		'has_archive'         => false,
		'rewrite'             => false,
		'query_var'           => false,
	);

	register_post_type( 'cgt_mandat', $args );
}

/**
 * Charger les scripts WordPress media pour les pages personnalisées
 */
add_action( 'admin_enqueue_scripts', 'cgt_enqueue_custom_dashboard_scripts' );

function cgt_enqueue_custom_dashboard_scripts( $hook ) {
	// Charger uniquement sur nos pages personnalisées
	$allowed_pages = array(
		'toplevel_page_cgt-dashboard-administration',
		'toplevel_page_cgt-dashboard-gestionnaire',
		'toplevel_page_cgt-dashboard-assistante',
		'administration_page_cgt-banque',
		'gestionnaire_page_cgt-publier-contenu',
	);

	if ( in_array( $hook, $allowed_pages, true ) || strpos( $hook, 'cgt-dashboard' ) !== false || strpos( $hook, 'cgt-banque' ) !== false || strpos( $hook, 'cgt-publier-contenu' ) !== false ) {
		wp_enqueue_media();
		wp_enqueue_script( 'jquery' );
	}
}
