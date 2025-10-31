<?php
/**
 * Gestion des adhésions CGT
 *
 * @package CGT_Child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Traitement du formulaire d'adhésion
 */
add_action( 'admin_post_nopriv_cgt_adhesion_submit', 'cgt_adhesion_submit_handler' );
add_action( 'admin_post_cgt_adhesion_submit', 'cgt_adhesion_submit_handler' );

function cgt_adhesion_submit_handler() {
    // Vérification du nonce
    if ( ! isset( $_POST['adhesion_nonce'] ) || ! wp_verify_nonce( $_POST['adhesion_nonce'], 'cgt_adhesion_nonce' ) ) {
        wp_die( 'Erreur de sécurité. Veuillez réessayer.' );
    }

    // Vérification du honeypot (anti-spam)
    if ( ! cgt_check_honeypot( 'cgt_hp_adhesion' ) ) {
        cgt_log_spam_attempt( 'adhesion', $_POST );
        wp_die( 'Erreur de validation. Veuillez réessayer.' );
    }

    // Vérification du rate limiting (3 soumissions max par heure)
    if ( ! cgt_check_rate_limit( 'adhesion', 3, 3600 ) ) {
        wp_die( 'Vous avez atteint la limite de soumissions. Veuillez réessayer plus tard.' );
    }

    // Récupération et sanitization des données
    $data = array(
        // Informations personnelles
        'nom'               => sanitize_text_field( $_POST['nom'] ?? '' ),
        'prenom'            => sanitize_text_field( $_POST['prenom'] ?? '' ),
        'sexe'              => sanitize_text_field( $_POST['sexe'] ?? '' ),
        'date_naissance'    => sanitize_text_field( $_POST['date_naissance'] ?? '' ),
        'nationalite'       => sanitize_text_field( $_POST['nationalite'] ?? '' ),
        'adresse'           => sanitize_text_field( $_POST['adresse'] ?? '' ),
        'code_postal'       => sanitize_text_field( $_POST['code_postal'] ?? '' ),
        'ville'             => sanitize_text_field( $_POST['ville'] ?? '' ),
        'tel'               => sanitize_text_field( $_POST['tel'] ?? '' ),
        'email'             => sanitize_email( $_POST['email'] ?? '' ),
        'statut'            => sanitize_text_field( $_POST['statut'] ?? '' ),
        'categorie'         => sanitize_text_field( $_POST['categorie'] ?? '' ),

        // Informations entreprise
        'entreprise_nom'               => sanitize_text_field( $_POST['entreprise_nom'] ?? '' ),
        'entreprise_siret'             => sanitize_text_field( $_POST['entreprise_siret'] ?? '' ),
        'appartient_groupe'            => sanitize_text_field( $_POST['appartient_groupe'] ?? '' ),
        'entreprise_adresse'           => sanitize_text_field( $_POST['entreprise_adresse'] ?? '' ),
        'entreprise_code_postal'       => sanitize_text_field( $_POST['entreprise_code_postal'] ?? '' ),
        'entreprise_ville'             => sanitize_text_field( $_POST['entreprise_ville'] ?? '' ),
        'entreprise_tel'               => sanitize_text_field( $_POST['entreprise_tel'] ?? '' ),
        'entreprise_email'             => sanitize_email( $_POST['entreprise_email'] ?? '' ),
        'secteur'                      => sanitize_text_field( $_POST['secteur'] ?? '' ),
        'code_ape_naf'                 => sanitize_text_field( $_POST['code_ape_naf'] ?? '' ),
        'convention_collective'        => sanitize_text_field( $_POST['convention_collective'] ?? '' ),
        'effectif'                     => sanitize_text_field( $_POST['effectif'] ?? '' ),
        'union_locale'                 => sanitize_text_field( $_POST['union_locale'] ?? '' ),
        'union_departementale'         => sanitize_text_field( $_POST['union_departementale'] ?? '' ),
    );

    // Validation des champs obligatoires
    $required_fields = array( 'nom', 'prenom', 'sexe', 'date_naissance', 'nationalite', 'adresse', 'code_postal', 'ville', 'tel', 'email', 'statut', 'categorie', 'entreprise_nom' );
    $errors = array();

    foreach ( $required_fields as $field ) {
        if ( empty( $data[ $field ] ) ) {
            $errors[] = "Le champ {$field} est obligatoire.";
        }
    }

    // Validation email
    if ( ! is_email( $data['email'] ) ) {
        $errors[] = 'L\'adresse email n\'est pas valide.';
    }

    // Validation téléphone (10 chiffres)
    if ( ! preg_match( '/^[0-9]{10}$/', $data['tel'] ) ) {
        $errors[] = 'Le numéro de téléphone doit contenir 10 chiffres.';
    }

    // Validation code postal (5 chiffres)
    if ( ! preg_match( '/^[0-9]{5}$/', $data['code_postal'] ) ) {
        $errors[] = 'Le code postal doit contenir 5 chiffres.';
    }

    // Si des erreurs, rediriger avec message
    if ( ! empty( $errors ) ) {
        $redirect_url = add_query_arg(
            array(
                'adhesion' => 'error',
                'errors'   => urlencode( implode( '|', $errors ) ),
            ),
            wp_get_referer()
        );
        wp_safe_redirect( $redirect_url );
        exit;
    }

    // Créer un post de type 'adhesion' (custom post type)
    $post_data = array(
        'post_title'   => sprintf( 'Adhésion - %s %s', $data['prenom'], $data['nom'] ),
        'post_type'    => 'cgt_adhesion',
        'post_status'  => 'pending', // En attente de validation
        'post_content' => cgt_format_adhesion_content( $data ),
    );

    $post_id = wp_insert_post( $post_data );

    if ( is_wp_error( $post_id ) ) {
        $redirect_url = add_query_arg( 'adhesion', 'error', wp_get_referer() );
        wp_safe_redirect( $redirect_url );
        exit;
    }

    // Enregistrer les données dans les meta fields
    foreach ( $data as $key => $value ) {
        update_post_meta( $post_id, '_adhesion_' . $key, $value );
    }

    // Enregistrer la date de soumission
    update_post_meta( $post_id, '_adhesion_date_soumission', current_time( 'mysql' ) );

    // Envoyer un email de notification à l'administrateur
    cgt_send_adhesion_notification( $post_id, $data );

    // Envoyer un email de confirmation au demandeur
    cgt_send_adhesion_confirmation( $data );

    // Redirection avec message de succès
    $redirect_url = add_query_arg( 'adhesion', 'success', wp_get_referer() );
    wp_safe_redirect( $redirect_url );
    exit;
}

/**
 * Formater le contenu de l'adhésion pour l'affichage dans l'admin
 */
function cgt_format_adhesion_content( $data ) {
    ob_start();
    ?>
    <h2>Informations personnelles</h2>
    <ul>
        <li><strong>Nom :</strong> <?php echo esc_html( $data['nom'] ); ?></li>
        <li><strong>Prénom :</strong> <?php echo esc_html( $data['prenom'] ); ?></li>
        <li><strong>Sexe :</strong> <?php echo esc_html( $data['sexe'] ); ?></li>
        <li><strong>Date de naissance :</strong> <?php echo esc_html( $data['date_naissance'] ); ?></li>
        <li><strong>Nationalité :</strong> <?php echo esc_html( $data['nationalite'] ); ?></li>
        <li><strong>Adresse :</strong> <?php echo esc_html( $data['adresse'] ); ?>, <?php echo esc_html( $data['code_postal'] ); ?> <?php echo esc_html( $data['ville'] ); ?></li>
        <li><strong>Téléphone :</strong> <?php echo esc_html( $data['tel'] ); ?></li>
        <li><strong>Email :</strong> <?php echo esc_html( $data['email'] ); ?></li>
        <li><strong>Statut :</strong> <?php echo esc_html( $data['statut'] ); ?></li>
        <li><strong>Catégorie :</strong> <?php echo esc_html( $data['categorie'] ); ?></li>
    </ul>

    <h2>Informations sur l'entreprise</h2>
    <ul>
        <li><strong>Nom de l'entreprise :</strong> <?php echo esc_html( $data['entreprise_nom'] ); ?></li>
        <?php if ( ! empty( $data['entreprise_siret'] ) ) : ?>
            <li><strong>N° Siret :</strong> <?php echo esc_html( $data['entreprise_siret'] ); ?></li>
        <?php endif; ?>
        <?php if ( ! empty( $data['appartient_groupe'] ) ) : ?>
            <li><strong>Appartient au groupe :</strong> <?php echo esc_html( $data['appartient_groupe'] ); ?></li>
        <?php endif; ?>
        <?php if ( ! empty( $data['entreprise_adresse'] ) ) : ?>
            <li><strong>Adresse :</strong> <?php echo esc_html( $data['entreprise_adresse'] ); ?>, <?php echo esc_html( $data['entreprise_code_postal'] ); ?> <?php echo esc_html( $data['entreprise_ville'] ); ?></li>
        <?php endif; ?>
        <?php if ( ! empty( $data['entreprise_tel'] ) ) : ?>
            <li><strong>Téléphone :</strong> <?php echo esc_html( $data['entreprise_tel'] ); ?></li>
        <?php endif; ?>
        <?php if ( ! empty( $data['entreprise_email'] ) ) : ?>
            <li><strong>Email :</strong> <?php echo esc_html( $data['entreprise_email'] ); ?></li>
        <?php endif; ?>
        <?php if ( ! empty( $data['secteur'] ) ) : ?>
            <li><strong>Secteur :</strong> <?php echo esc_html( $data['secteur'] ); ?></li>
        <?php endif; ?>
        <?php if ( ! empty( $data['code_ape_naf'] ) ) : ?>
            <li><strong>Code APE/NAF :</strong> <?php echo esc_html( $data['code_ape_naf'] ); ?></li>
        <?php endif; ?>
        <?php if ( ! empty( $data['convention_collective'] ) ) : ?>
            <li><strong>Convention collective :</strong> <?php echo esc_html( $data['convention_collective'] ); ?></li>
        <?php endif; ?>
        <?php if ( ! empty( $data['effectif'] ) ) : ?>
            <li><strong>Effectif :</strong> <?php echo esc_html( $data['effectif'] ); ?></li>
        <?php endif; ?>
        <?php if ( ! empty( $data['union_locale'] ) ) : ?>
            <li><strong>Union Locale :</strong> <?php echo esc_html( $data['union_locale'] ); ?></li>
        <?php endif; ?>
        <?php if ( ! empty( $data['union_departementale'] ) ) : ?>
            <li><strong>Union Départementale :</strong> <?php echo esc_html( $data['union_departementale'] ); ?></li>
        <?php endif; ?>
    </ul>
    <?php
    return ob_get_clean();
}

/**
 * Envoyer une notification à l'administrateur
 */
function cgt_send_adhesion_notification( $post_id, $data ) {
    $admin_email = get_option( 'admin_email' );
    $subject = sprintf( '[CGT] Nouvelle demande d\'adhésion - %s %s', $data['prenom'], $data['nom'] );

    $message = "Une nouvelle demande d'adhésion a été reçue.\n\n";
    $message .= "Nom : {$data['nom']}\n";
    $message .= "Prénom : {$data['prenom']}\n";
    $message .= "Email : {$data['email']}\n";
    $message .= "Téléphone : {$data['tel']}\n";
    $message .= "Entreprise : {$data['entreprise_nom']}\n\n";
    $message .= "Voir la demande complète dans l'administration :\n";
    $message .= admin_url( 'post.php?post=' . $post_id . '&action=edit' );

    $headers = array( 'Content-Type: text/plain; charset=UTF-8' );

    wp_mail( $admin_email, $subject, $message, $headers );

    // Envoyer aussi à l'email de contact CGT si différent
    $cgt_email = defined( 'CGT_ADMIN_EMAIL' ) ? CGT_ADMIN_EMAIL : 'admfsetud@cgt.fr';
    if ( $cgt_email !== $admin_email ) {
        wp_mail( $cgt_email, $subject, $message, $headers );
    }
}

/**
 * Envoyer un email de confirmation au demandeur
 */
function cgt_send_adhesion_confirmation( $data ) {
    $to = $data['email'];
    $subject = 'Confirmation de votre demande d\'adhésion CGT';

    $message = "Bonjour {$data['prenom']} {$data['nom']},\n\n";
    $message .= "Nous avons bien reçu votre demande d'adhésion à la CGT.\n\n";
    $message .= "Vos informations :\n";
    $message .= "- Nom : {$data['nom']}\n";
    $message .= "- Prénom : {$data['prenom']}\n";
    $message .= "- Email : {$data['email']}\n";
    $message .= "- Entreprise : {$data['entreprise_nom']}\n\n";
    $message .= "Un responsable vous contactera prochainement pour finaliser votre adhésion et vous informer sur les modalités de cotisation.\n\n";
    $cgt_contact_email = defined( 'CGT_ADMIN_EMAIL' ) ? CGT_ADMIN_EMAIL : 'admfsetud@cgt.fr';
    $message .= "Pour toute question, vous pouvez nous contacter à : {$cgt_contact_email}\n\n";
    $message .= "Cordialement,\n";
    $message .= "L'équipe CGT";

    $headers = array( 'Content-Type: text/plain; charset=UTF-8' );

    wp_mail( $to, $subject, $message, $headers );
}

/**
 * Enregistrer le custom post type pour les adhésions
 */
add_action( 'init', 'cgt_register_adhesion_cpt' );

function cgt_register_adhesion_cpt() {
    $labels = array(
        'name'               => 'Adhésions',
        'singular_name'      => 'Adhésion',
        'menu_name'          => 'Adhérents',
        'add_new'            => 'Ajouter',
        'add_new_item'       => 'Ajouter une adhésion',
        'edit_item'          => 'Modifier l\'adhésion',
        'view_item'          => 'Voir l\'adhésion',
        'all_items'          => 'Toutes les adhésions',
        'search_items'       => 'Rechercher',
        'not_found'          => 'Aucune adhésion trouvée',
    );

    $args = array(
        'labels'              => $labels,
        'public'              => false,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'menu_icon'           => 'dashicons-groups',
        'capability_type'     => 'post',
        'capabilities'        => array(
            'create_posts' => 'do_not_allow',
        ),
        'map_meta_cap'        => true,
        'supports'            => array( 'title', 'editor' ),
        'has_archive'         => false,
        'rewrite'             => false,
        'query_var'           => false,
    );

    register_post_type( 'cgt_adhesion', $args );
}

/**
 * Personnaliser les colonnes dans l'admin
 */
add_filter( 'manage_cgt_adhesion_posts_columns', 'cgt_adhesion_columns' );

function cgt_adhesion_columns( $columns ) {
    $new_columns = array();
    $new_columns['cb'] = $columns['cb'];
    $new_columns['title'] = 'Adhérent';
    $new_columns['email'] = 'Email';
    $new_columns['tel'] = 'Téléphone';
    $new_columns['entreprise'] = 'Entreprise';
    $new_columns['date'] = 'Date de demande';
    return $new_columns;
}

add_action( 'manage_cgt_adhesion_posts_custom_column', 'cgt_adhesion_column_content', 10, 2 );

function cgt_adhesion_column_content( $column, $post_id ) {
    switch ( $column ) {
        case 'email':
            echo esc_html( get_post_meta( $post_id, '_adhesion_email', true ) );
            break;
        case 'tel':
            echo esc_html( get_post_meta( $post_id, '_adhesion_tel', true ) );
            break;
        case 'entreprise':
            echo esc_html( get_post_meta( $post_id, '_adhesion_entreprise_nom', true ) );
            break;
    }
}

/**
 * Register adhésion admin submenu dashboard.
 */
add_action( 'admin_menu', 'cgt_register_adhesion_admin_page' );

function cgt_register_adhesion_admin_page() {
	add_submenu_page(
		'edit.php?post_type=cgt_adhesion',
		__( 'Tableau des adhésions', 'cgt' ),
		__( 'Tableau des adhésions', 'cgt' ),
		'manage_cgt_adhesions',
		'cgt-adhesions-dashboard',
		'cgt_render_adhesion_admin_page'
	);
}

/**
 * Enqueue custom CSS for adhesions dashboard.
 */
add_action( 'admin_enqueue_scripts', 'cgt_enqueue_adhesion_admin_styles' );

function cgt_enqueue_adhesion_admin_styles( $hook ) {
	// Only load on our custom dashboard page
	if ( $hook !== 'cgt_adhesion_page_cgt-adhesions-dashboard' ) {
		return;
	}

	wp_enqueue_style(
		'cgt-admin-adhesions',
		get_stylesheet_directory_uri() . '/assets/css/admin-adhesions.css',
		array( 'cgt-admin-global' ), // Depend on global admin styles
		filemtime( get_stylesheet_directory() . '/assets/css/admin-adhesions.css' )
	);
}

/**
 * Renders the adhésion dashboard page.
 */
function cgt_render_adhesion_admin_page() {
	if ( ! current_user_can( 'manage_cgt_adhesions' ) ) {
		wp_die( esc_html__( 'Vous n\'avez pas les droits suffisants pour accéder à cette page.', 'cgt' ) );
	}

	// Get statistics
	$total_adhesions = wp_count_posts( 'cgt_adhesion' );
	$total = $total_adhesions->pending + $total_adhesions->publish;
	$pending = $total_adhesions->pending;
	$approved = $total_adhesions->publish;

	// Query adhesions
	$args  = array(
		'post_type'      => 'cgt_adhesion',
		'post_status'    => array( 'pending', 'publish', 'draft' ),
		'posts_per_page' => 50,
		'orderby'        => 'date',
		'order'          => 'DESC',
	);
	$query = new WP_Query( $args );
	?>
	<div class="cgt-adhesions-dashboard">
		<!-- Header -->
		<div class="cgt-adhesions-header">
			<h1><?php esc_html_e( 'Gestion des Adhésions CGT', 'cgt' ); ?></h1>
			<p><?php esc_html_e( 'Gérez les demandes d\'adhésion, téléchargez les fiches et contactez les adhérents.', 'cgt' ); ?></p>
		</div>

		<!-- Statistics Cards -->
		<div class="cgt-stats-grid">
			<div class="cgt-stat-card">
				<div class="cgt-stat-label"><?php esc_html_e( 'Total Adhésions', 'cgt' ); ?></div>
				<div class="cgt-stat-value"><?php echo esc_html( $total ); ?></div>
			</div>
			<div class="cgt-stat-card">
				<div class="cgt-stat-label"><?php esc_html_e( 'En Attente', 'cgt' ); ?></div>
				<div class="cgt-stat-value"><?php echo esc_html( $pending ); ?></div>
			</div>
			<div class="cgt-stat-card">
				<div class="cgt-stat-label"><?php esc_html_e( 'Approuvées', 'cgt' ); ?></div>
				<div class="cgt-stat-value"><?php echo esc_html( $approved ); ?></div>
			</div>
			<div class="cgt-stat-card">
				<div class="cgt-stat-label"><?php esc_html_e( 'Ce Mois', 'cgt' ); ?></div>
				<div class="cgt-stat-value">
					<?php
					$args_month = array(
						'post_type'   => 'cgt_adhesion',
						'post_status' => array( 'pending', 'publish' ),
						'date_query'  => array(
							array(
								'year'  => date( 'Y' ),
								'month' => date( 'm' ),
							),
						),
					);
					$query_month = new WP_Query( $args_month );
					echo esc_html( $query_month->found_posts );
					wp_reset_postdata();
					?>
				</div>
			</div>
		</div>

		<!-- Filters (optional for future) -->
		<!--
		<div class="cgt-filters">
			<div class="cgt-filter-item">
				<label><?php esc_html_e( 'Recherche', 'cgt' ); ?></label>
				<input type="text" placeholder="Nom, prénom, email..." />
			</div>
			<div class="cgt-filter-item">
				<label><?php esc_html_e( 'Statut', 'cgt' ); ?></label>
				<select>
					<option value=""><?php esc_html_e( 'Tous', 'cgt' ); ?></option>
					<option value="pending"><?php esc_html_e( 'En attente', 'cgt' ); ?></option>
					<option value="publish"><?php esc_html_e( 'Approuvé', 'cgt' ); ?></option>
				</select>
			</div>
			<button class="cgt-filter-btn"><?php esc_html_e( 'Filtrer', 'cgt' ); ?></button>
		</div>
		-->

		<!-- Table Container -->
		<div class="cgt-table-container">
			<div class="cgt-table-header">
				<h2 class="cgt-table-title"><?php esc_html_e( 'Liste des Adhésions', 'cgt' ); ?></h2>
				<div class="cgt-table-actions">
					<!-- Future: Export button -->
				</div>
			</div>

			<?php if ( $query->have_posts() ) : ?>
				<table class="cgt-custom-table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Nom Complet', 'cgt' ); ?></th>
							<th><?php esc_html_e( 'Email', 'cgt' ); ?></th>
							<th><?php esc_html_e( 'Téléphone', 'cgt' ); ?></th>
							<th><?php esc_html_e( 'Entreprise', 'cgt' ); ?></th>
							<th><?php esc_html_e( 'Date', 'cgt' ); ?></th>
							<th><?php esc_html_e( 'Statut', 'cgt' ); ?></th>
							<th><?php esc_html_e( 'Actions', 'cgt' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php
						while ( $query->have_posts() ) :
							$query->the_post();
							$post_id    = get_the_ID();
							$nom        = get_post_meta( $post_id, '_adhesion_nom', true );
							$prenom     = get_post_meta( $post_id, '_adhesion_prenom', true );
							$tel        = get_post_meta( $post_id, '_adhesion_tel', true );
							$email      = get_post_meta( $post_id, '_adhesion_email', true );
							$entreprise = get_post_meta( $post_id, '_adhesion_entreprise_nom', true );
							$status     = get_post_status( $post_id );

							$download_url = wp_nonce_url(
								admin_url( 'admin-post.php?action=cgt_download_adhesion_pdf&post_id=' . $post_id ),
								'cgt_download_pdf_' . $post_id
							);
							$edit_url = admin_url( 'post.php?post=' . $post_id . '&action=edit' );

							// Status badge class
							$status_class = 'cgt-status-pending';
							$status_label = __( 'En attente', 'cgt' );
							if ( $status === 'publish' ) {
								$status_class = 'cgt-status-approved';
								$status_label = __( 'Approuvé', 'cgt' );
							}
							?>
							<tr>
								<td><strong><?php echo esc_html( $prenom . ' ' . $nom ); ?></strong></td>
								<td><a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a></td>
								<td><?php echo esc_html( $tel ); ?></td>
								<td><?php echo esc_html( $entreprise ); ?></td>
								<td><?php echo esc_html( get_the_date( 'd/m/Y' ) ); ?></td>
								<td><span class="cgt-status-badge <?php echo esc_attr( $status_class ); ?>"><?php echo esc_html( $status_label ); ?></span></td>
								<td>
									<div class="cgt-table-actions-cell">
										<a href="<?php echo esc_url( $edit_url ); ?>" class="cgt-action-btn cgt-action-btn-view">
											<?php esc_html_e( 'Voir', 'cgt' ); ?>
										</a>
										<a href="<?php echo esc_url( $download_url ); ?>" class="cgt-action-btn cgt-action-btn-pdf">
											<?php esc_html_e( 'PDF', 'cgt' ); ?>
										</a>
									</div>
								</td>
							</tr>
						<?php endwhile; ?>
					</tbody>
				</table>
			<?php else : ?>
				<div class="cgt-empty-state">
					<div class="cgt-empty-state-icon">📋</div>
					<h3><?php esc_html_e( 'Aucune adhésion pour le moment', 'cgt' ); ?></h3>
					<p><?php esc_html_e( 'Les nouvelles demandes d\'adhésion apparaîtront ici.', 'cgt' ); ?></p>
				</div>
			<?php endif; ?>
		</div>
	</div>
	<?php
	wp_reset_postdata();
}

/**
 * Handle PDF download.
 */
add_action( 'admin_post_cgt_download_adhesion_pdf', 'cgt_download_adhesion_pdf' );

function cgt_download_adhesion_pdf() {
	if ( ! current_user_can( 'manage_cgt_adhesions' ) ) {
		wp_die( esc_html__( 'Accès refusé.', 'cgt' ) );
	}

	$adhesion_id = isset( $_GET['post_id'] ) ? absint( $_GET['post_id'] ) : 0;
	if ( ! $adhesion_id || 'cgt_adhesion' !== get_post_type( $adhesion_id ) ) {
		wp_die( esc_html__( 'Adhésion introuvable.', 'cgt' ) );
	}

	check_admin_referer( 'cgt_download_pdf_' . $adhesion_id );

	$details = cgt_get_adhesion_details( $adhesion_id );
	$pdf     = cgt_generate_adhesion_pdf( get_the_title( $adhesion_id ), $details );

	$filename = sanitize_title( get_the_title( $adhesion_id ) ) . '.pdf';
	header( 'Content-Type: application/pdf' );
	header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
	header( 'Content-Length: ' . strlen( $pdf ) );
	set_time_limit( 0 );
	echo $pdf;
	exit;
}

/**
 * Retrieve adhesion details from meta.
 *
 * @param int $post_id Post ID.
 *
 * @return array
 */
function cgt_get_adhesion_details( $post_id ) {
	$fields = array(
		'nom',
		'prenom',
		'sexe',
		'date_naissance',
		'nationalite',
		'adresse',
		'code_postal',
		'ville',
		'tel',
		'email',
		'statut',
		'categorie',
		'entreprise_nom',
		'entreprise_siret',
		'appartient_groupe',
		'entreprise_adresse',
		'entreprise_code_postal',
		'entreprise_ville',
		'entreprise_tel',
		'entreprise_email',
		'secteur',
		'code_ape_naf',
		'convention_collective',
		'effectif',
		'union_locale',
		'union_departementale',
	);

	$data = array();
	foreach ( $fields as $field ) {
		$data[ $field ] = get_post_meta( $post_id, '_adhesion_' . $field, true );
	}

	$data['date_soumission'] = get_post_meta( $post_id, '_adhesion_date_soumission', true );

	return $data;
}

/**
 * Escape text for PDF.
 */
function cgt_pdf_escape_text( $text ) {
	$text = str_replace( array( '\\', '(', ')' ), array( '\\\\', '\\(', '\\)' ), $text );
	$text = preg_replace( '/\r?\n/', '\\n', $text );
	return $text;
}

/**
 * Generate professional PDF for adhesion data (1 page A4).
 *
 * @param string $title  Title.
 * @param array  $data   Data array.
 *
 * @return string
 */
function cgt_generate_adhesion_pdf( $title, $data ) {
	// PDF content stream with professional layout on 1 page A4 (595x842 points)
	$pdf_stream = '';

	// Header with red background
	$pdf_stream .= "q\n"; // Save graphics state
	$pdf_stream .= "0.784 0.063 0.078 rg\n"; // CGT Red color (#C8102E)
	$pdf_stream .= "0 802 595 40 re f\n"; // Red rectangle at top
	$pdf_stream .= "Q\n"; // Restore graphics state

	// White text for header
	$pdf_stream .= "BT\n";
	$pdf_stream .= "/F2 16 Tf\n"; // Bold, 16pt
	$pdf_stream .= "1 1 1 rg\n"; // White color
	$pdf_stream .= "50 817 Td\n"; // Position
	$pdf_stream .= '(' . cgt_pdf_escape_text( 'FICHE ADHESION CGT' ) . ") Tj\n";
	$pdf_stream .= "ET\n";

	// Date in header (right aligned)
	$pdf_stream .= "BT\n";
	$pdf_stream .= "/F1 10 Tf\n";
	$pdf_stream .= "1 1 1 rg\n";
	$pdf_stream .= "450 817 Td\n";
	$pdf_stream .= '(' . cgt_pdf_escape_text( date( 'd/m/Y' ) ) . ") Tj\n";
	$pdf_stream .= "ET\n";

	$y = 770; // Starting Y position

	// Title: Informations Personnelles
	$pdf_stream .= "q\n";
	$pdf_stream .= "0.784 0.063 0.078 rg\n"; // Red color
	$pdf_stream .= "BT /F2 11 Tf 50 {$y} Td (" . cgt_pdf_escape_text( 'INFORMATIONS PERSONNELLES' ) . ") Tj ET\n";
	$pdf_stream .= "Q\n";
	$y -= 5;

	// Line under title
	$pdf_stream .= "q 0.784 0.063 0.078 RG 2 w 50 {$y} m 545 {$y} l S Q\n";
	$y -= 20;

	// Personal info in 2 columns
	$left_col = 50;
	$right_col = 300;
	$line_height = 15;

	$personal_fields = array(
		array( 'Nom', $data['nom'] ?? '' ),
		array( 'Prénom', $data['prenom'] ?? '' ),
		array( 'Sexe', $data['sexe'] ?? '' ),
		array( 'Date de naissance', $data['date_naissance'] ?? '' ),
		array( 'Nationalité', $data['nationalite'] ?? '' ),
		array( 'Téléphone', $data['tel'] ?? '' ),
		array( 'Email', $data['email'] ?? '' ),
		array( 'Statut', $data['statut'] ?? '' ),
		array( 'Catégorie', $data['categorie'] ?? '' ),
	);

	$col = 0;
	foreach ( $personal_fields as $field ) {
		$x = ( $col === 0 ) ? $left_col : $right_col;
		// Label in black bold
		$pdf_stream .= "BT /F2 9 Tf 0 0 0 rg {$x} {$y} Td (" . cgt_pdf_escape_text( $field[0] . ' :' ) . ") Tj ET\n";
		// Value in black regular
		$pdf_stream .= "BT /F1 9 Tf 0 0 0 rg " . ( $x + 80 ) . " {$y} Td (" . cgt_pdf_escape_text( $field[1] ) . ") Tj ET\n";

		if ( $col === 1 ) {
			$y -= $line_height;
			$col = 0;
		} else {
			$col = 1;
		}
	}

	if ( $col === 1 ) {
		$y -= $line_height;
	}

	// Adresse (full width)
	$y -= 5;
	$pdf_stream .= "BT /F2 9 Tf 0 0 0 rg {$left_col} {$y} Td (" . cgt_pdf_escape_text( 'Adresse :' ) . ") Tj ET\n";
	$adresse_complete = trim( ( $data['adresse'] ?? '' ) . ', ' . ( $data['code_postal'] ?? '' ) . ' ' . ( $data['ville'] ?? '' ) );
	$pdf_stream .= "BT /F1 9 Tf 0 0 0 rg " . ( $left_col + 60 ) . " {$y} Td (" . cgt_pdf_escape_text( $adresse_complete ) . ") Tj ET\n";
	$y -= 25;

	// Title: Entreprise
	$pdf_stream .= "q\n";
	$pdf_stream .= "0.784 0.063 0.078 rg\n";
	$pdf_stream .= "BT /F2 11 Tf 50 {$y} Td (" . cgt_pdf_escape_text( 'INFORMATIONS ENTREPRISE' ) . ") Tj ET\n";
	$pdf_stream .= "Q\n";
	$y -= 5;
	$pdf_stream .= "q 0.784 0.063 0.078 RG 2 w 50 {$y} m 545 {$y} l S Q\n";
	$y -= 20;

	// Enterprise info in 2 columns
	$enterprise_fields = array(
		array( 'Nom', $data['entreprise_nom'] ?? '' ),
		array( 'SIRET', $data['entreprise_siret'] ?? '' ),
		array( 'Groupe', $data['appartient_groupe'] ?? '' ),
		array( 'Téléphone', $data['entreprise_tel'] ?? '' ),
		array( 'Email', $data['entreprise_email'] ?? '' ),
		array( 'Secteur', $data['secteur'] ?? '' ),
		array( 'Code APE/NAF', $data['code_ape_naf'] ?? '' ),
		array( 'Convention', $data['convention_collective'] ?? '' ),
		array( 'Effectif', $data['effectif'] ?? '' ),
		array( 'Union Locale', $data['union_locale'] ?? '' ),
		array( 'Union Dép.', $data['union_departementale'] ?? '' ),
	);

	$col = 0;
	foreach ( $enterprise_fields as $field ) {
		if ( empty( $field[1] ) ) {
			continue; // Skip empty fields
		}

		$x = ( $col === 0 ) ? $left_col : $right_col;
		// Label in black bold
		$pdf_stream .= "BT /F2 9 Tf 0 0 0 rg {$x} {$y} Td (" . cgt_pdf_escape_text( $field[0] . ' :' ) . ") Tj ET\n";

		// Truncate long text
		$value = $field[1];
		if ( strlen( $value ) > 30 ) {
			$value = substr( $value, 0, 27 ) . '...';
		}
		// Value in black regular
		$pdf_stream .= "BT /F1 9 Tf 0 0 0 rg " . ( $x + 80 ) . " {$y} Td (" . cgt_pdf_escape_text( $value ) . ") Tj ET\n";

		if ( $col === 1 ) {
			$y -= $line_height;
			$col = 0;
		} else {
			$col = 1;
		}
	}

	if ( $col === 1 ) {
		$y -= $line_height;
	}

	// Adresse entreprise (full width)
	if ( ! empty( $data['entreprise_adresse'] ) ) {
		$y -= 5;
		$pdf_stream .= "BT /F2 9 Tf 0 0 0 rg {$left_col} {$y} Td (" . cgt_pdf_escape_text( 'Adresse :' ) . ") Tj ET\n";
		$adresse_ent = trim( ( $data['entreprise_adresse'] ?? '' ) . ', ' . ( $data['entreprise_code_postal'] ?? '' ) . ' ' . ( $data['entreprise_ville'] ?? '' ) );
		$pdf_stream .= "BT /F1 9 Tf 0 0 0 rg " . ( $left_col + 60 ) . " {$y} Td (" . cgt_pdf_escape_text( $adresse_ent ) . ") Tj ET\n";
		$y -= 20;
	}

	// Footer with submission date
	$y = 60;
	$pdf_stream .= "q 0.9 0.9 0.9 rg 0 0 595 50 re f Q\n"; // Light gray footer
	$pdf_stream .= "BT /F1 8 Tf 0.4 0.4 0.4 rg 50 {$y} Td (" . cgt_pdf_escape_text( 'Soumis le : ' . ( $data['date_soumission'] ?? current_time( 'mysql' ) ) ) . ") Tj ET\n";

	// CGT contact in footer
	$pdf_stream .= "BT /F1 8 Tf 0.4 0.4 0.4 rg 350 {$y} Td (" . cgt_pdf_escape_text( 'CGT Fédération des Sociétés d\'Études' ) . ") Tj ET\n";

	$stream_length = strlen( $pdf_stream );

	$objects = array(
		'<< /Type /Catalog /Pages 2 0 R >>',
		'<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
		'<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Contents 4 0 R /Resources << /Font << /F1 5 0 R /F2 6 0 R >> >> >>',
		"<< /Length $stream_length >>\nstream\n$pdf_stream\nendstream",
		'<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
		'<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>',
	);

	$pdf    = "%PDF-1.4\n";
	$offset = array( 0 );
	foreach ( $objects as $index => $object ) {
		$offset[ $index + 1 ] = strlen( $pdf );
		$pdf                 .= ( $index + 1 ) . " 0 obj\n" . $object . "\nendobj\n";
	}

	$xref_pos = strlen( $pdf );
	$pdf     .= "xref\n0 " . ( count( $objects ) + 1 ) . "\n";
	$pdf     .= "0000000000 65535 f \n";
	for ( $i = 1; $i <= count( $objects ); $i++ ) {
		$pdf .= sprintf( '%010d 00000 n ', $offset[ $i ] ) . "\n";
	}

	$pdf .= "trailer\n<< /Size " . ( count( $objects ) + 1 ) . " /Root 1 0 R >>\nstartxref\n" . $xref_pos . "\n%%EOF";

	return $pdf;
}

/**
 * Render the adhesion PDF HTML template.
 *
 * @param array $data Adhesion data.
 * @return string
 */
function cgt_render_adhesion_pdf_template( $data ) {
	$template = locate_template( array( 'templates/pdf/adhesion-template.php' ) );

	if ( ! $template ) {
		return '';
	}

	ob_start();
	include $template;

	return ob_get_clean();
}
