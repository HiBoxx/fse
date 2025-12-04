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

        // Cotisation
        'cotisation_mensuelle'         => sanitize_text_field( $_POST['cotisation_mensuelle'] ?? '' ),
        'prelevement_mois'             => sanitize_text_field( $_POST['prelevement_mois'] ?? '' ),
        'prelevement_annee'            => sanitize_text_field( $_POST['prelevement_annee'] ?? '' ),
        'prelevement_montant'          => sanitize_text_field( $_POST['prelevement_montant'] ?? '' ),
        'banque_nom'                   => sanitize_text_field( $_POST['banque_nom'] ?? '' ),
        'banque_adresse'               => sanitize_text_field( $_POST['banque_adresse'] ?? '' ),
        'banque_code_postal'           => sanitize_text_field( $_POST['banque_code_postal'] ?? '' ),
        'banque_ville'                 => sanitize_text_field( $_POST['banque_ville'] ?? '' ),
        'rib'                          => sanitize_text_field( $_POST['rib'] ?? '' ),
        'signature'                    => sanitize_textarea_field( $_POST['signature'] ?? '' ),
    );

    // Validation des champs obligatoires
    $required_fields = array( 'nom', 'prenom', 'sexe', 'date_naissance', 'nationalite', 'adresse', 'code_postal', 'ville', 'tel', 'email', 'statut', 'categorie', 'entreprise_nom', 'cotisation_mensuelle', 'signature' );
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

    // ✅ PHASE 2 : Validation des longueurs maximales pour éviter les abus
    $field_lengths = array(
        'nom'                      => 100,
        'prenom'                   => 100,
        'sexe'                     => 20,
        'date_naissance'           => 10,
        'nationalite'              => 100,
        'adresse'                  => 255,
        'code_postal'              => 10,
        'ville'                    => 100,
        'tel'                      => 20,
        'email'                    => 254,
        'statut'                   => 100,
        'categorie'                => 100,
        'entreprise_nom'           => 255,
        'entreprise_siret'         => 14,
        'appartient_groupe'        => 255,
        'entreprise_adresse'       => 255,
        'entreprise_code_postal'   => 10,
        'entreprise_ville'         => 100,
        'entreprise_tel'           => 20,
        'entreprise_email'         => 254,
        'secteur'                  => 255,
        'code_ape_naf'             => 10,
        'convention_collective'    => 255,
        'effectif'                 => 100,
        'union_locale'             => 100,
        'union_departementale'     => 100,
        'cotisation_mensuelle'     => 20,
        'prelevement_mois'         => 2,
        'prelevement_annee'        => 4,
        'prelevement_montant'      => 20,
        'banque_nom'               => 255,
        'banque_adresse'           => 255,
        'banque_code_postal'       => 10,
        'banque_ville'             => 100,
        'rib'                      => 255,
    );

    foreach ( $field_lengths as $field => $max_length ) {
        if ( isset( $data[ $field ] ) && mb_strlen( $data[ $field ] ) > $max_length ) {
            $errors[] = sprintf(
                'Le champ %s est trop long (maximum %d caractères, %d fournis).',
                $field,
                $max_length,
                mb_strlen( $data[ $field ] )
            );
        }
    }

    // ✅ Validation spécifique SIRET (14 chiffres si fourni)
    if ( ! empty( $data['entreprise_siret'] ) && ! preg_match( '/^[0-9]{14}$/', $data['entreprise_siret'] ) ) {
        $errors[] = 'Le numéro SIRET doit contenir exactement 14 chiffres.';
    }

    // ✅ Validation email entreprise (si fourni)
    if ( ! empty( $data['entreprise_email'] ) && ! is_email( $data['entreprise_email'] ) ) {
        $errors[] = 'L\'adresse email de l\'entreprise n\'est pas valide.';
    }

    // ✅ Validation téléphone entreprise (10 chiffres si fourni)
    if ( ! empty( $data['entreprise_tel'] ) && ! preg_match( '/^[0-9]{10}$/', $data['entreprise_tel'] ) ) {
        $errors[] = 'Le numéro de téléphone de l\'entreprise doit contenir 10 chiffres.';
    }

    // ✅ Validation code postal entreprise (5 chiffres si fourni)
    if ( ! empty( $data['entreprise_code_postal'] ) && ! preg_match( '/^[0-9]{5}$/', $data['entreprise_code_postal'] ) ) {
        $errors[] = 'Le code postal de l\'entreprise doit contenir 5 chiffres.';
    }

    // ✅ Validation code postal banque (5 chiffres si fourni)
    if ( ! empty( $data['banque_code_postal'] ) && ! preg_match( '/^[0-9]{5}$/', $data['banque_code_postal'] ) ) {
        $errors[] = 'Le code postal de la banque doit contenir 5 chiffres.';
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

    <h2>Cotisation</h2>
    <ul>
        <?php if ( ! empty( $data['cotisation_mensuelle'] ) ) : ?>
            <li><strong>Montant cotisation mensuelle :</strong> <?php echo esc_html( $data['cotisation_mensuelle'] ); ?>€</li>
        <?php endif; ?>
        <?php if ( ! empty( $data['prelevement_mois'] ) && ! empty( $data['prelevement_annee'] ) ) : ?>
            <li><strong>Date du 1er prélèvement :</strong> 05/<?php echo esc_html( $data['prelevement_mois'] ); ?>/<?php echo esc_html( $data['prelevement_annee'] ); ?></li>
        <?php endif; ?>
        <?php if ( ! empty( $data['prelevement_montant'] ) ) : ?>
            <li><strong>Montant de prélèvement :</strong> <?php echo esc_html( $data['prelevement_montant'] ); ?>€</li>
        <?php endif; ?>
        <?php if ( ! empty( $data['banque_nom'] ) ) : ?>
            <li><strong>Nom de la banque :</strong> <?php echo esc_html( $data['banque_nom'] ); ?></li>
        <?php endif; ?>
        <?php if ( ! empty( $data['banque_adresse'] ) ) : ?>
            <li><strong>Adresse de la banque :</strong> <?php echo esc_html( $data['banque_adresse'] ); ?>, <?php echo esc_html( $data['banque_code_postal'] ); ?> <?php echo esc_html( $data['banque_ville'] ); ?></li>
        <?php endif; ?>
        <?php if ( ! empty( $data['rib'] ) ) : ?>
            <li><strong>RIB :</strong> <?php echo esc_html( $data['rib'] ); ?></li>
        <?php endif; ?>
        <?php if ( ! empty( $data['signature'] ) ) : ?>
            <li><strong>Signature :</strong> <br><img src="<?php echo esc_url( $data['signature'] ); ?>" alt="Signature" style="max-width: 400px; border: 1px solid #ddd; padding: 10px; margin-top: 5px;"></li>
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
		array(),
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

	if ( is_wp_error( $pdf ) ) {
		wp_die( esc_html( $pdf->get_error_message() ) );
	}

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
		'cotisation_mensuelle',
		'prelevement_mois',
		'prelevement_annee',
		'prelevement_montant',
		'banque_nom',
		'banque_adresse',
		'banque_code_postal',
		'banque_ville',
		'rib',
		'signature',
	);

	$data = array();
	foreach ( $fields as $field ) {
		$data[ $field ] = get_post_meta( $post_id, '_adhesion_' . $field, true );
	}

	$data['date_soumission'] = get_post_meta( $post_id, '_adhesion_date_soumission', true );

	return $data;
}

/**
 * Generate adhesion PDF using Dompdf when available.
 *
 * @param string $title Title.
 * @param array  $data  Data array.
 *
 * @return string|WP_Error
 */
function cgt_generate_adhesion_pdf( $title, $data ) {
	if ( ! class_exists( '\Dompdf\Dompdf' ) ) {
		return new WP_Error(
			'cgt_missing_dompdf',
			__( 'La génération de PDF nécessite la bibliothèque Dompdf. Merci de vérifier son installation.', 'cgt' )
		);
	}

	if ( ! function_exists( 'cgt_render_adhesion_pdf_template' ) ) {
		return new WP_Error(
			'cgt_missing_pdf_template',
			__( 'Le gabarit de PDF est introuvable.', 'cgt' )
		);
	}

	$html = cgt_render_adhesion_pdf_template( $data );

	if ( empty( $html ) ) {
		return new WP_Error(
			'cgt_empty_pdf_html',
			__( 'Impossible de générer le PDF car le contenu est vide.', 'cgt' )
		);
	}

	try {
		$options = new \Dompdf\Options();
		$options->set( 'isRemoteEnabled', true );
		$options->set( 'isHtml5ParserEnabled', true );
		$options->setDefaultFont( 'DejaVu Sans' );

		$dompdf = new \Dompdf\Dompdf( $options );
		$dompdf->loadHtml( $html, 'UTF-8' );
		$dompdf->setPaper( 'A4', 'portrait' );
		$dompdf->render();
	} catch ( \Exception $e ) {
		return new WP_Error(
			'cgt_pdf_render_error',
			sprintf(
				/* translators: %s: error message */
				__( 'Une erreur est survenue lors de la génération du PDF : %s', 'cgt' ),
				$e->getMessage()
			)
		);
	}

	return $dompdf->output();
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

/**
 * ✅ Handle frontend PDF download for gestionnaires (non-admin users).
 * This allows gestionnaire role to download PDFs from the gestionnaire space.
 */
add_action( 'template_redirect', 'cgt_handle_frontend_adhesion_pdf_download' );

function cgt_handle_frontend_adhesion_pdf_download() {
	// Only process if download_adhesion parameter is present
	if ( ! isset( $_GET['download_adhesion'] ) ) {
		return;
	}

	// Check if user is logged in and has the download capability
	if ( ! is_user_logged_in() || ! current_user_can( 'download_cgt_adhesions' ) ) {
		wp_die( esc_html__( 'Accès refusé. Vous devez avoir les droits nécessaires pour télécharger ce document.', 'cgt' ) );
	}

	$adhesion_id = absint( $_GET['download_adhesion'] );

	// Validate adhesion ID
	if ( ! $adhesion_id || 'cgt_adhesion' !== get_post_type( $adhesion_id ) ) {
		wp_die( esc_html__( 'Adhésion introuvable.', 'cgt' ) );
	}

	// Get adhesion details
	$details = cgt_get_adhesion_details( $adhesion_id );
	$title   = get_the_title( $adhesion_id );

	// Generate PDF
	$pdf = cgt_generate_adhesion_pdf( $title, $details );

	if ( is_wp_error( $pdf ) ) {
		wp_die( esc_html( $pdf->get_error_message() ) );
	}

	// Send PDF to browser
	$filename = sanitize_title( $title ) . '.pdf';
	header( 'Content-Type: application/pdf' );
	header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
	header( 'Content-Length: ' . strlen( $pdf ) );
	header( 'Cache-Control: private, max-age=0, must-revalidate' );
	header( 'Pragma: public' );
	set_time_limit( 0 );
	echo $pdf;
	exit;
}
