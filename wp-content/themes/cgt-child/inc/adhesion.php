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
 * Renders the adhésion dashboard page.
 */
function cgt_render_adhesion_admin_page() {
	if ( ! current_user_can( 'manage_cgt_adhesions' ) ) {
		wp_die( esc_html__( 'Vous n’avez pas les droits suffisants pour accéder à cette page.', 'cgt' ) );
	}

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
		<h1><?php esc_html_e( 'Tableau des adhésions', 'cgt' ); ?></h1>
		<p><?php esc_html_e( 'Téléchargez les fiches adhérents au format PDF ou contactez-les directement.', 'cgt' ); ?></p>
		<table class="widefat fixed striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Nom', 'cgt' ); ?></th>
					<th><?php esc_html_e( 'Prénom', 'cgt' ); ?></th>
					<th><?php esc_html_e( 'Téléphone', 'cgt' ); ?></th>
					<th><?php esc_html_e( 'Email', 'cgt' ); ?></th>
					<th><?php esc_html_e( 'Entreprise', 'cgt' ); ?></th>
					<th><?php esc_html_e( 'PDF', 'cgt' ); ?></th>
				</tr>
			</thead>
			<tbody>
			<?php if ( $query->have_posts() ) : ?>
				<?php
				while ( $query->have_posts() ) :
					$query->the_post();
					$post_id   = get_the_ID();
					$nom       = get_post_meta( $post_id, '_adhesion_nom', true );
					$prenom    = get_post_meta( $post_id, '_adhesion_prenom', true );
					$tel       = get_post_meta( $post_id, '_adhesion_tel', true );
					$email     = get_post_meta( $post_id, '_adhesion_email', true );
					$entreprise = get_post_meta( $post_id, '_adhesion_entreprise_nom', true );
					$download_url = wp_nonce_url(
						admin_url( 'admin-post.php?action=cgt_download_adhesion_pdf&post_id=' . $post_id ),
						'cgt_download_pdf_' . $post_id
					);
					?>
					<tr>
						<td><?php echo esc_html( $nom ); ?></td>
						<td><?php echo esc_html( $prenom ); ?></td>
						<td><?php echo esc_html( $tel ); ?></td>
						<td><a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a></td>
						<td><?php echo esc_html( $entreprise ); ?></td>
						<td><a class="button" href="<?php echo esc_url( $download_url ); ?>"><?php esc_html_e( 'Télécharger le PDF', 'cgt' ); ?></a></td>
					</tr>
				<?php endwhile; ?>
			<?php else : ?>
				<tr>
					<td colspan="6"><?php esc_html_e( 'Aucune adhésion enregistrée pour le moment.', 'cgt' ); ?></td>
				</tr>
			<?php endif; ?>
			</tbody>
		</table>
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
