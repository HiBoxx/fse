<?php
/**
 * Template Name: Page de Connexion CGT
 * Description: Page de connexion personnalisée avec bloc de connexion et bloc d'adhésion
 */

$login_page_url        = get_permalink();
$current_action        = isset( $_GET['action'] ) ? sanitize_key( wp_unslash( $_GET['action'] ) ) : 'login';
$login_state           = isset( $_GET['login'] ) ? sanitize_key( wp_unslash( $_GET['login'] ) ) : '';
$logged_out            = isset( $_GET['loggedout'] ) && 'true' === sanitize_key( wp_unslash( $_GET['loggedout'] ) );
$checkemail_state      = isset( $_GET['checkemail'] ) ? sanitize_key( wp_unslash( $_GET['checkemail'] ) ) : '';
$account_request_state = isset( $_GET['account_request'] ) ? sanitize_key( wp_unslash( $_GET['account_request'] ) ) : '';
$requested_redirect = isset( $_GET['redirect_to'] ) ? esc_url_raw( wp_unslash( $_GET['redirect_to'] ) ) : '';

$login_messages = array();
if ( 'failed' === $login_state ) {
	$login_messages[] = array(
		'type' => 'error',
		'text' => __( 'Identifiants invalides. Vérifiez votre email et votre mot de passe.', 'cgt' ),
	);
}

if ( 'empty' === $login_state ) {
	$login_messages[] = array(
		'type' => 'error',
		'text' => __( 'Merci de renseigner votre identifiant et votre mot de passe.', 'cgt' ),
	);
}

if ( $logged_out ) {
	$login_messages[] = array(
		'type' => 'success',
		'text' => __( 'Vous êtes bien déconnecté·e.', 'cgt' ),
	);
}

if ( 'confirm' === $checkemail_state && 'lostpassword' !== $current_action ) {
	$login_messages[] = array(
		'type' => 'info',
		'text' => __( 'Un lien de réinitialisation vient de vous être envoyé par email.', 'cgt' ),
	);
}

$account_request_feedback = null;
$account_request_messages = array(
	'success'         => array(
		'type' => 'success',
		'text' => __( 'Votre compte a été créé et vous êtes maintenant connecté·e.', 'cgt' ),
	),
	'username_exists' => array(
		'type' => 'error',
		'text' => __( 'Cet identifiant est déjà utilisé. Merci d’en choisir un autre.', 'cgt' ),
	),
	'email_exists'    => array(
		'type' => 'error',
		'text' => __( 'Un compte existe déjà avec cet email.', 'cgt' ),
	),
	'password_mismatch' => array(
		'type' => 'error',
		'text' => __( 'Les mots de passe ne correspondent pas.', 'cgt' ),
	),
	'missing_fields'  => array(
		'type' => 'error',
		'text' => __( 'Merci de renseigner tous les champs obligatoires.', 'cgt' ),
	),
	'invalid_email'   => array(
		'type' => 'error',
		'text' => __( 'L’adresse email n’est pas valide.', 'cgt' ),
	),
	'invalid_branch'  => array(
		'type' => 'error',
		'text' => __( 'Merci de sélectionner une branche valide.', 'cgt' ),
	),
	'rate_limited'    => array(
		'type' => 'error',
		'text' => __( 'Vous avez effectué trop de tentatives. Veuillez réessayer dans quelques minutes.', 'cgt' ),
	),
	'terms_missing'   => array(
		'type' => 'error',
		'text' => __( 'Merci de confirmer l’acceptation des CGU et des mentions légales.', 'cgt' ),
	),
	'general_error'   => array(
		'type' => 'error',
		'text' => __( 'Une erreur est survenue lors de la création du compte.', 'cgt' ),
	),
);

if ( $account_request_state && isset( $account_request_messages[ $account_request_state ] ) ) {
	$account_request_feedback = $account_request_messages[ $account_request_state ];
	$login_messages[]         = $account_request_feedback;
}

$lost_messages = array();
if ( 'confirm' === $checkemail_state ) {
	$lost_messages[] = array(
		'type' => 'success',
		'text' => __( 'Un lien de réinitialisation vient de vous être envoyé par email.', 'cgt' ),
	);
}

if ( 'invalidkey' === $checkemail_state ) {
	$lost_messages[] = array(
		'type' => 'error',
		'text' => __( 'Le lien de réinitialisation est invalide ou a expiré.', 'cgt' ),
	);
}

$back_to_login_url = remove_query_arg(
	array( 'action', 'checkemail', 'login', 'loggedout', 'reauth' ),
	$login_page_url
);

if ( $requested_redirect ) {
	$back_to_login_url = add_query_arg( 'redirect_to', rawurlencode( $requested_redirect ), $back_to_login_url );
}

$branch_terms = get_terms(
	array(
		'taxonomy'   => 'branche',
		'hide_empty' => false,
		'orderby'    => 'name',
		'order'      => 'ASC',
	)
);

if ( is_wp_error( $branch_terms ) ) {
	$branch_terms = array();
}

$cgt_legal_links = array(
	'cgu'     => array(
		'slugs' => array( 'cgu', 'conditions-generales', 'conditions-generales-d-utilisation' ),
		'title' => 'Conditions générales d’utilisation',
	),
	'legal'   => array(
		'slugs' => array( 'mentions-legales', 'mentions-legales-et-confidentialite' ),
		'title' => 'Mentions légales',
	),
	'privacy' => array(
		'slugs' => array( 'politique-de-confidentialite', 'politique-de-confidentialite-cgt' ),
		'title' => 'Politique de confidentialité',
	),
);

$cgu_url      = home_url();
$mentions_url = home_url();
$privacy_url  = home_url();

foreach ( $cgt_legal_links as $key => $data ) {
	$page_url = '';

	foreach ( $data['slugs'] as $slug ) {
		$page = get_page_by_path( $slug );
		if ( $page && 'publish' === get_post_status( $page ) ) {
			$page_url = get_permalink( $page );
			break;
		}
	}

	if ( ! $page_url ) {
		$page = get_page_by_title( $data['title'] );
		if ( $page && 'page' === get_post_type( $page ) && 'publish' === get_post_status( $page ) ) {
			$page_url = get_permalink( $page );
		}
	}

	if ( 'cgu' === $key ) {
		$cgu_url = $page_url ? $page_url : home_url();
	} elseif ( 'legal' === $key ) {
		$mentions_url = $page_url ? $page_url : home_url();
	} elseif ( 'privacy' === $key ) {
		$privacy_url = $page_url ? $page_url : home_url();
	}
}

$redirect_target = '';
if ( $requested_redirect ) {
	$decoded_redirect = rawurldecode( $requested_redirect );
	$redirect_target  = wp_validate_redirect( $decoded_redirect, home_url( '/espace-adherent' ) );
}

if ( is_user_logged_in() && 'success' === $account_request_state && $redirect_target ) {
	wp_safe_redirect( $redirect_target );
	exit;
}

get_header();
?>

<main class="connexion-page">
    <div class="connexion-container">
        <div class="connexion-header">
            <h1>Bienvenue</h1>
            <p>Connectez-vous à votre espace ou devenez adhérent</p>
        </div>

		<div class="connexion-grid">
			<?php if ( 'lostpassword' === $current_action ) : ?>
			<div class="connexion-bloc connexion-login connexion-lostpassword">
				<div class="bloc-header">
					<svg class="bloc-icon" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
						<circle cx="12" cy="12" r="10"></circle>
						<path d="M12 8v4"></path>
						<path d="M11.95 16h.1"></path>
					</svg>
					<h2><?php esc_html_e( 'Mot de passe oublié', 'cgt' ); ?></h2>
				</div>
				<p class="bloc-description"><?php esc_html_e( 'Indiquez votre identifiant ou votre adresse email pour recevoir un lien de réinitialisation.', 'cgt' ); ?></p>

					<?php foreach ( $lost_messages as $message ) : ?>
						<div class="login-<?php echo esc_attr( $message['type'] ); ?>"><?php echo esc_html( $message['text'] ); ?></div>
					<?php endforeach; ?>

					<?php if ( 'confirm' !== $checkemail_state ) : ?>
					<form name="lostpasswordform" id="lostpasswordform" action="<?php echo esc_url( site_url( 'wp-login.php?action=lostpassword', 'login_post' ) ); ?>" method="post">
						<div class="form-group">
							<label for="user_login_lost"><?php esc_html_e( 'Identifiant ou email', 'cgt' ); ?></label>
							<input type="text" name="user_login" id="user_login_lost" class="form-control form-control--offset" required>
						</div>
						<?php wp_nonce_field( 'lost_password', '_wpnonce' ); ?>
						<?php if ( $requested_redirect ) : ?>
							<input type="hidden" name="redirect_to" value="<?php echo esc_attr( $requested_redirect ); ?>">
						<?php endif; ?>
						<button type="submit" class="btn btn-primary btn-block"><?php esc_html_e( 'Envoyer le lien de réinitialisation', 'cgt' ); ?></button>
					</form>
				<?php endif; ?>

				<div class="login-links">
					<a href="<?php echo esc_url( $back_to_login_url ); ?>" class="lost-password">&larr; <?php esc_html_e( 'Retour à la connexion', 'cgt' ); ?></a>
				</div>
			</div>
			<?php else : ?>
			<!-- BLOC 1: CONNEXION -->
			<div class="connexion-bloc connexion-login">
				<div class="bloc-header">
					<svg class="bloc-icon" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
						<path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
						<polyline points="10 17 15 12 10 7"></polyline>
						<line x1="15" y1="12" x2="3" y2="12"></line>
                    </svg>
                    <h2>Se connecter</h2>
                </div>

                <p class="bloc-description">Accédez à votre espace personnel</p>

				<?php foreach ( $login_messages as $message ) : ?>
					<div class="login-<?php echo esc_attr( $message['type'] ); ?>"><?php echo esc_html( $message['text'] ); ?></div>
				<?php endforeach; ?>

                <form name="loginform" id="loginform" action="<?php echo esc_url(site_url('wp-login.php', 'login_post')); ?>" method="post">
                    <div class="form-group">
                        <label for="user_login">Identifiant</label>
                        <input type="text" name="log" id="user_login" class="form-control" required autocomplete="username">
                    </div>

                    <div class="form-group">
                        <label for="user_pass">Mot de passe</label>
                        <input type="password" name="pwd" id="user_pass" class="form-control" required autocomplete="current-password">
                    </div>

                    <div class="form-group form-remember">
                        <label>
                            <input name="rememberme" type="checkbox" id="rememberme" value="forever">
                            <span>Se souvenir de moi</span>
                        </label>
                    </div>

                    <input type="hidden" name="redirect_to" value="<?php echo esc_url(home_url('/espace-adherent')); ?>">

					<button type="submit" name="wp-submit" id="wp-submit" class="btn btn-primary btn-block">
						Se connecter
					</button>
				</form>

				<div class="account-request">
					<button type="button" class="account-request__trigger" id="openAccountRequestModal">
						<?php esc_html_e( 'Je suis déjà adhérent·e mais je n’ai pas encore de compte', 'cgt' ); ?>
					</button>
				</div>

				<div class="login-links">
					<a href="<?php echo esc_url( add_query_arg( 'action', 'lostpassword', $login_page_url ) ); ?>" class="lost-password">
						<?php esc_html_e( 'Mot de passe oublié ?', 'cgt' ); ?>
					</a>
				</div>

                <div class="access-types">
                    <div class="access-type">
                        <strong>Accès Administrateur</strong>
                        <p>Pour les responsables CGT</p>
                    </div>
                    <div class="access-type">
                        <strong>Accès Adhérent</strong>
                        <p>Pour les membres syndiqués</p>
                    </div>
                </div>
            </div>

			<?php endif; ?>

			<!-- BLOC 2: ADHÉSION -->
			<div class="connexion-bloc connexion-adhesion">
                <div class="bloc-header">
                    <svg class="bloc-icon" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="8.5" cy="7" r="4"></circle>
                        <line x1="20" y1="8" x2="20" y2="14"></line>
                        <line x1="23" y1="11" x2="17" y2="11"></line>
                    </svg>
                    <h2>Devenir adhérent</h2>
                </div>

                <p class="bloc-description">Rejoignez la CGT et bénéficiez de nos services</p>

                <div class="adhesion-benefits">
                    <ul>
                        <li>
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                            Défense de vos droits au travail
                        </li>
                        <li>
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                            Accès à l'espace adhérent privé
                        </li>
                        <li>
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                            Accompagnement juridique
                        </li>
                        <li>
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                            Formation syndicale
                        </li>
                    </ul>
                </div>

                <div class="cotisation-info">
                    <div class="cotisation-icon">ℹ️</div>
                    <div class="cotisation-text">
                        <strong>Cotisations en ligne</strong>
                        <p>Les cotisations en ligne seront disponibles prochainement. Pour l'instant, veuillez contacter :</p>
                        <?php $cgt_email = defined( 'CGT_ADMIN_EMAIL' ) ? CGT_ADMIN_EMAIL : 'admfsetud@cgt.fr'; ?>
                        <a href="mailto:<?php echo esc_attr( $cgt_email ); ?>" class="email-link"><?php echo esc_html( $cgt_email ); ?></a>
                    </div>
                </div>

                <button type="button" id="btn-adhesion" class="btn btn-secondary btn-block">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="8.5" cy="7" r="4"></circle>
                        <line x1="20" y1="8" x2="20" y2="14"></line>
                        <line x1="23" y1="11" x2="17" y2="11"></line>
                    </svg>
                    Remplir le formulaire d'adhésion
                </button>
            </div>
        </div>
    </div>

    <!-- Styles spécifiques pour désactiver temporairement la section Cotisation -->
    <style>
        .section-disabled-note {
            margin-bottom: 1rem;
            padding: 12px 14px;
            background: #f6f6f6;
            border: 1px dashed #ccc;
            border-radius: 6px;
            color: #555;
            font-weight: 600;
        }
        .section-disabled-fields {
            opacity: 0.5;
        }
    </style>

    <!-- MODAL FORMULAIRE D'ADHÉSION -->
    <div id="adhesion-modal" class="adhesion-modal" style="display: none;">
        <div class="modal-overlay" id="modal-overlay"></div>
        <div class="modal-content">
            <div class="modal-header">
                <h2>Formulaire d'adhésion CGT</h2>
                <button type="button" class="modal-close" id="modal-close">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>

            <div class="modal-body">
                <?php if (isset($_GET['adhesion']) && $_GET['adhesion'] === 'success'): ?>
                    <div class="adhesion-success">
                        <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                        </svg>
                        <h3>Demande d'adhésion envoyée !</h3>
                        <p>Votre demande a été enregistrée avec succès. Nous vous contacterons prochainement à l'adresse email indiquée.</p>
                    </div>
                <?php else: ?>
                    <form id="adhesion-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" enctype="multipart/form-data">
                        <?php wp_nonce_field('cgt_adhesion_nonce', 'adhesion_nonce'); ?>
                        <input type="hidden" name="action" value="cgt_adhesion_submit">
                        <?php echo cgt_render_honeypot( 'cgt_hp_adhesion' ); ?>

                        <!-- SECTION 1: INFORMATIONS SYNDIQUÉ-E -->
                        <div class="form-section">
                            <h3 class="section-title">
                                <span class="section-number">1</span>
                                Informations syndiqué-e
                            </h3>

                            <div class="form-row form-row--gapped">
                                <div class="form-group col-6">
                                    <label for="nom">Nom <span class="required">*</span></label>
                                    <input type="text" name="nom" id="nom" class="form-control" required>
                                </div>
                                <div class="form-group col-6">
                                    <label for="prenom">Prénom <span class="required">*</span></label>
                                    <input type="text" name="prenom" id="prenom" class="form-control form-control--offset" required>
                                </div>
                            </div>

                            <div class="form-row form-row--gapped">
                                <div class="form-group col-4">
                                    <label for="sexe">Sexe <span class="required">*</span></label>
                                    <select name="sexe" id="sexe" class="form-control" required>
                                        <option value="">Sélectionner</option>
                                        <option value="Femme">Femme</option>
                                        <option value="Homme">Homme</option>
                                    </select>
                                </div>
                                <div class="form-group col-4">
                                    <label for="date_naissance">Date de naissance <span class="required">*</span></label>
                                    <input type="date" name="date_naissance" id="date_naissance" class="form-control" required>
                                </div>
                                <div class="form-group col-4">
                                    <label for="nationalite">Nationalité <span class="required">*</span></label>
                                    <select name="nationalite" id="nationalite" class="form-control form-control--offset" required>
                                        <option value="">Sélectionner</option>
                                        <option value="Française">Française</option>
                                        <option value="Autre">Autre</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="adresse">Adresse <span class="required">*</span></label>
                                <input type="text" name="adresse" id="adresse" class="form-control" required>
                            </div>

                            <div class="form-row form-row--gapped">
                                <div class="form-group col-4">
                                    <label for="code_postal">Code postal <span class="required">*</span></label>
                                    <input type="text" name="code_postal" id="code_postal" class="form-control" required pattern="[0-9]{5}">
                                </div>
                                <div class="form-group col-8">
                                    <label for="ville">Ville <span class="required">*</span></label>
                                    <input type="text" name="ville" id="ville" class="form-control form-control--offset" required>
                                </div>
                            </div>

                            <div class="form-row form-row--gapped">
                                <div class="form-group col-6">
                                    <label for="tel">Téléphone <span class="required">*</span></label>
                                    <input type="tel" name="tel" id="tel" class="form-control" required pattern="[0-9]{10}">
                                </div>
                                <div class="form-group col-6">
                                    <label for="email">Email <span class="required">*</span></label>
                                    <input type="email" name="email" id="email" class="form-control form-control--offset" required>
                                </div>
                            </div>

                            <div class="form-row form-row--gapped">
                                <div class="form-group col-6">
                                    <label for="statut">Statut <span class="required">*</span></label>
                                    <select name="statut" id="statut" class="form-control" required>
                                        <option value="">Sélectionner</option>
                                        <option value="CDI">CDI</option>
                                        <option value="CDD">CDD</option>
                                        <option value="Emploi jeune">Emploi jeune</option>
                                        <option value="Intérimaire">Intérimaire</option>
                                        <option value="Privé d'emploi">Privé d'emploi</option>
                                        <option value="Retraité">Retraité</option>
                                    </select>
                                </div>
                                <div class="form-group col-6">
                                    <label for="categorie">Catégorie <span class="required">*</span></label>
                                    <select name="categorie" id="categorie" class="form-control" required>
                                        <option value="">Sélectionner</option>
                                        <option value="Ouvrier">Ouvrier</option>
                                        <option value="Employé">Employé</option>
                                        <option value="Technicien">Technicien</option>
                                        <option value="Agent de maîtrise">Agent de maîtrise</option>
                                        <option value="Cadre">Cadre</option>
                                        <option value="Ingénieur">Ingénieur</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- SECTION 2: INFORMATIONS SUR L'ENTREPRISE -->
                        <div class="form-section">
                            <h3 class="section-title">
                                <span class="section-number">2</span>
                                Informations sur l'entreprise
                            </h3>

                            <div class="form-row form-row--gapped">
                                <div class="form-group col-8">
                                    <label for="entreprise_nom">Nom de l'entreprise <span class="required">*</span></label>
                                    <input type="text" name="entreprise_nom" id="entreprise_nom" class="form-control" required>
                                </div>
                                <div class="form-group col-4">
                                    <label for="entreprise_siret">N° Siret</label>
                                    <input type="text" name="entreprise_siret" id="entreprise_siret" class="form-control" pattern="[0-9]{14}">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="appartient_groupe">Appartient au groupe</label>
                                <input type="text" name="appartient_groupe" id="appartient_groupe" class="form-control" placeholder="Si applicable">
                            </div>

                            <div class="form-group">
                                <label for="entreprise_adresse">Adresse de l'entreprise</label>
                                <input type="text" name="entreprise_adresse" id="entreprise_adresse" class="form-control">
                            </div>

                            <div class="form-row form-row--gapped">
                                <div class="form-group col-4">
                                    <label for="entreprise_code_postal">Code postal</label>
                                    <input type="text" name="entreprise_code_postal" id="entreprise_code_postal" class="form-control" pattern="[0-9]{5}">
                                </div>
                                <div class="form-group col-8">
                                    <label for="entreprise_ville">Ville</label>
                                    <input type="text" name="entreprise_ville" id="entreprise_ville" class="form-control">
                                </div>
                            </div>

                            <div class="form-row form-row--gapped">
                                <div class="form-group col-6">
                                    <label for="entreprise_tel">Téléphone</label>
                                    <input type="tel" name="entreprise_tel" id="entreprise_tel" class="form-control">
                                </div>
                                <div class="form-group col-6">
                                    <label for="entreprise_email">Email</label>
                                    <input type="email" name="entreprise_email" id="entreprise_email" class="form-control">
                                </div>
                            </div>

                            <div class="form-row form-row--gapped">
                                <div class="form-group col-6">
                                    <label for="secteur">Secteur</label>
                                    <input type="text" name="secteur" id="secteur" class="form-control">
                                </div>
                                <div class="form-group col-6">
                                    <label for="code_ape_naf">Code APE/NAF</label>
                                    <input type="text" name="code_ape_naf" id="code_ape_naf" class="form-control" pattern="[0-9]{4}[A-Z]">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="convention_collective">Convention collective</label>
                                <input type="text" name="convention_collective" id="convention_collective" class="form-control">
                            </div>

                            <div class="form-group">
                                <label for="effectif">Effectif</label>
                                <input type="text" name="effectif" id="effectif" class="form-control" placeholder="Nombre d'employés">
                            </div>

                            <div class="form-row form-row--gapped">
                                <div class="form-group col-6">
                                    <label for="union_locale">Union Locale</label>
                                    <input type="text" name="union_locale" id="union_locale" class="form-control">
                                </div>
                                <div class="form-group col-6">
                                    <label for="union_departementale">Union Départementale</label>
                                    <input type="text" name="union_departementale" id="union_departementale" class="form-control">
                                </div>
                            </div>
                        </div>

                        <!-- SECTION 3: COTISATION -->
                        <div class="form-section">
                            <h3 class="section-title">
                                <span class="section-number">3</span>
                                Cotisation
                            </h3>

                            <div class="section-disabled-note">
                                Les paiements en ligne seront bientôt disponibles. Cette section est temporairement désactivée.
                            </div>
                            <fieldset class="section-disabled-fields" disabled aria-disabled="true">
                                <div class="form-group">
                                    <label for="cotisation_mensuelle">Montant cotisation mensuelle</label>
                                    <div class="form-input-with-suffix">
                                        <input type="number" name="cotisation_mensuelle" id="cotisation_mensuelle" class="form-control" min="0" step="0.01">
                                        <span class="input-suffix">€</span>
                                    </div>
                                    <small class="form-help">La cotisation est calculée sur 1% du salaire mensuel</small>
                                </div>

                                <div class="form-subsection">
                                    <h4 class="subsection-title">En cas de prélèvement</h4>

                                    <div class="form-group">
                                        <label for="prelevement_date">Date du 1er prélèvement</label>
                                        <div class="date-input-group">
                                            <input type="text" class="form-control date-day" value="05" readonly>
                                            <span class="date-separator">/</span>
                                            <select name="prelevement_mois" id="prelevement_mois" class="form-control date-month">
                                                <option value="">Mois</option>
                                                <option value="01">Janvier</option>
                                                <option value="02">Février</option>
                                                <option value="03">Mars</option>
                                                <option value="04">Avril</option>
                                                <option value="05">Mai</option>
                                                <option value="06">Juin</option>
                                                <option value="07">Juillet</option>
                                                <option value="08">Août</option>
                                                <option value="09">Septembre</option>
                                                <option value="10">Octobre</option>
                                                <option value="11">Novembre</option>
                                                <option value="12">Décembre</option>
                                            </select>
                                            <span class="date-separator">/</span>
                                            <select name="prelevement_annee" id="prelevement_annee" class="form-control date-year">
                                                <option value="">Année</option>
                                                <?php
                                                $current_year = date('Y');
                                                for ($i = 0; $i <= 5; $i++) {
                                                    $year = $current_year + $i;
                                                    echo '<option value="' . esc_attr($year) . '">' . esc_html($year) . '</option>';
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="prelevement_montant">Montant de prélèvement</label>
                                        <div class="form-input-with-suffix">
                                            <input type="text" name="prelevement_montant" id="prelevement_montant" class="form-control" readonly>
                                            <span class="input-suffix">€</span>
                                        </div>
                                        <small class="form-help">Égale à 2 fois le montant de la cotisation mensuelle</small>
                                    </div>
                                </div>

                                <div class="form-subsection">
                                    <h4 class="subsection-title">Domiciliation bancaire</h4>

                                    <div class="form-group">
                                        <label for="banque_nom">Nom de la banque</label>
                                        <input type="text" name="banque_nom" id="banque_nom" class="form-control">
                                    </div>

                                    <div class="form-group">
                                        <label for="banque_adresse">Adresse postale</label>
                                        <input type="text" name="banque_adresse" id="banque_adresse" class="form-control">
                                    </div>

                                    <div class="form-row form-row--gapped">
                                        <div class="form-group col-4">
                                            <label for="banque_code_postal">Code postal</label>
                                            <input type="text" name="banque_code_postal" id="banque_code_postal" class="form-control" pattern="[0-9]{5}">
                                        </div>
                                        <div class="form-group col-8">
                                            <label for="banque_ville">Ville</label>
                                            <input type="text" name="banque_ville" id="banque_ville" class="form-control">
                                        </div>
                                    </div>
                                </div>
                            </fieldset>

                                <div class="form-group">
                                    <label for="rib">RIB</label>
                                    <input type="text" name="rib" id="rib" class="form-control" placeholder="Relevé d'Identité Bancaire">
                                </div>
                            </div>

                            <div class="form-subsection">
                                <h4 class="subsection-title">Signature</h4>
                                <div class="form-group">
                                    <label for="signature">Votre signature <span class="required">*</span></label>
                                    <div class="signature-pad-wrapper">
                                        <canvas id="signature-pad" class="signature-pad" width="600" height="200"></canvas>
                                        <div class="signature-actions">
                                            <button type="button" class="btn btn-sm btn-outline" id="clear-signature">Effacer</button>
                                        </div>
                                    </div>
                                    <input type="hidden" name="signature" id="signature" required>
                                    <small class="form-help">Dessinez votre signature avec votre souris ou votre doigt</small>
                                </div>
                            </div>
                        </div>

                        <!-- SECTION 4: VALIDATION -->
                        <div class="form-section">
                            <h3 class="section-title">
                                <span class="section-number">4</span>
                                Validation
                            </h3>

                            <div class="form-group form-checkbox">
                                <label>
                                    <input type="checkbox" name="accepte_conditions" id="accepte_conditions" required>
                                    <span>J'accepte que mes données soient traitées dans le cadre de mon adhésion à la CGT <span class="required">*</span></span>
                                </label>
                            </div>

                            <div class="form-group form-checkbox">
                                <label>
                                    <input type="checkbox" name="accepte_rgpd" id="accepte_rgpd" required>
                                    <span>J'ai pris connaissance de la politique de confidentialité (RGPD) <span class="required">*</span></span>
                                </label>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="button" class="btn btn-outline" id="btn-cancel">Annuler</button>
                            <button type="submit" class="btn btn-primary">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                                    <polyline points="17 21 17 13 7 13 7 21"></polyline>
                                    <polyline points="7 3 7 8 15 8"></polyline>
                                </svg>
                                Envoyer ma demande d'adhésion
                            </button>
                        </div>

                        <p class="form-note"><span class="required">*</span> Champs obligatoires</p>
                    </form>
                <?php endif; ?>
            </div>
        </div>
</div>
</main>

<?php
$account_request_state_attr = $account_request_state ? esc_attr( $account_request_state ) : '';
$account_request_type_attr  = $account_request_feedback ? esc_attr( $account_request_feedback['type'] ) : '';
$form_message_classes       = 'account-request-form__message';

if ( $account_request_feedback ) {
	$form_message_classes .= ' is-visible';
	if ( 'success' === $account_request_feedback['type'] ) {
		$form_message_classes .= ' is-success';
	}
}
?>

<div
	class="account-request-modal"
	id="accountRequestModal"
	aria-hidden="true"
	role="dialog"
	aria-modal="true"
	data-account-request-state="<?php echo $account_request_state_attr; ?>"
	data-account-request-type="<?php echo $account_request_type_attr; ?>"
>
	<div class="account-request-modal__overlay" data-account-request-close></div>
	<div class="account-request-modal__content" role="document">
		<button type="button" class="account-request-modal__close" id="closeAccountRequestModal" aria-label="<?php esc_attr_e( 'Fermer', 'cgt' ); ?>">
			<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
		</button>
		<div class="account-request-modal__header">
			<span class="account-request-modal__icon">👥</span>
			<h2><?php esc_html_e( 'Créer mon compte adhérent', 'cgt' ); ?></h2>
			<p><?php esc_html_e( 'Complétez ce formulaire pour accéder à l’espace adhérent en ligne.', 'cgt' ); ?></p>
		</div>
		<form class="account-request-form" id="accountRequestForm" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( 'cgt_account_request', 'cgt_account_request_nonce' ); ?>
			<input type="hidden" name="action" value="cgt_request_account">
			<?php echo cgt_render_honeypot( 'cgt_hp_account_request' ); ?>
			<div class="account-request-form__grid">
				<label class="account-request-form__field">
					<span><?php esc_html_e( 'Prénom', 'cgt' ); ?> <span class="required">*</span></span>
					<input type="text" name="prenom" required autocomplete="given-name">
				</label>
				<label class="account-request-form__field">
					<span><?php esc_html_e( 'Nom', 'cgt' ); ?> <span class="required">*</span></span>
					<input type="text" name="nom" required autocomplete="family-name">
				</label>
				<label class="account-request-form__field">
					<span><?php esc_html_e( 'N° d’adhérent', 'cgt' ); ?></span>
					<input type="text" name="numero_adherent" autocomplete="off">
				</label>
				<label class="account-request-form__field">
					<span><?php esc_html_e( 'Email', 'cgt' ); ?> <span class="required">*</span></span>
					<input type="email" name="email" required autocomplete="email">
				</label>
				<label class="account-request-form__field">
					<span><?php esc_html_e( 'Identifiant souhaité', 'cgt' ); ?> <span class="required">*</span></span>
					<input type="text" name="username" required autocomplete="username">
				</label>
				<label class="account-request-form__field">
					<span><?php esc_html_e( 'Mot de passe', 'cgt' ); ?> <span class="required">*</span></span>
					<input type="password" name="password" id="accountRequestPassword" required autocomplete="new-password" minlength="8">
				</label>
				<label class="account-request-form__field">
					<span><?php esc_html_e( 'Confirmer le mot de passe', 'cgt' ); ?> <span class="required">*</span></span>
					<input type="password" name="password_confirm" id="accountRequestPasswordConfirm" required autocomplete="new-password" minlength="8">
				</label>
				<label class="account-request-form__field account-request-form__field--full">
					<span><?php esc_html_e( 'Entreprise', 'cgt' ); ?></span>
					<input type="text" name="entreprise" autocomplete="organization">
				</label>
				<label class="account-request-form__field account-request-form__field--full">
					<span><?php esc_html_e( 'Branche', 'cgt' ); ?> <span class="required">*</span></span>
					<select name="branche" required>
						<option value=""><?php esc_html_e( '-- Choisir une branche --', 'cgt' ); ?></option>
						<?php if ( ! is_wp_error( $branch_terms ) ) : ?>
							<?php foreach ( $branch_terms as $branch_option ) : ?>
								<option value="<?php echo esc_attr( $branch_option->term_id ); ?>"><?php echo esc_html( $branch_option->name ); ?></option>
							<?php endforeach; ?>
						<?php endif; ?>
					</select>
				</label>
			</div>
			<label class="account-request-form__consent">
				<input type="checkbox" name="accept_terms" value="1" required>
				<span>
					<?php
					echo wp_kses(
						sprintf(
							/* translators: 1: legal notice link, 2: privacy policy link. */
							__( 'J’accepte les <a href="%1$s">mentions légales</a> et la <a href="%2$s">politique de confidentialité</a>.', 'cgt' ),
							esc_url( $mentions_url ),
							esc_url( $privacy_url )
						),
						array(
							'a' => array(
								'href'   => array(),
								'target' => array(),
								'rel'    => array(),
							),
						)
					);
					?>
					<span class="required">*</span>
				</span>
			</label>
			<div class="<?php echo esc_attr( $form_message_classes ); ?>" id="accountRequestMessage" aria-live="polite" role="status">
				<?php
				if ( $account_request_feedback ) {
					echo esc_html( $account_request_feedback['text'] );
				}
				?>
			</div>
			<button type="submit" class="btn btn-primary btn-block account-request-form__submit"><?php esc_html_e( 'Envoyer ma demande', 'cgt' ); ?></button>
		</form>
	</div>
</div>

<?php get_footer(); ?>
