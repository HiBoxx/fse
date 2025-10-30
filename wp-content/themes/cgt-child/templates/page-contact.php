<?php
/**
 * Contact page template.
 *
 * @package CGT_Child
 */

get_header();
?>

<main id="primary" class="site-main contact-page">
	<!-- Hero Header -->
	<div class="contact-hero">
		<div class="container">
			<div class="contact-hero__content">
				<div class="contact-hero__badge">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
						<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
					</svg>
					<span><?php esc_html_e( 'Besoin d\'aide ?', 'cgt' ); ?></span>
				</div>
				<h1 class="contact-hero__title"><?php esc_html_e( 'Contactez-nous', 'cgt' ); ?></h1>
				<p class="contact-hero__description"><?php esc_html_e( 'Vous avez une question d\'ordre juridique ou souhaitez des précisions sur nos activités ? Nous sommes à votre écoute et nous vous répondrons dans les meilleurs délais.', 'cgt' ); ?></p>
			</div>
		</div>
	</div>

	<div class="container contact-container">
		<!-- Contact Methods Grid -->
		<div class="contact-methods">
			<div class="contact-method-card">
				<div class="contact-method-card__icon contact-method-card__icon--email">
					<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
						<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
						<polyline points="22,6 12,13 2,6"></polyline>
					</svg>
				</div>
				<h3 class="contact-method-card__title"><?php esc_html_e( 'Par Internet', 'cgt' ); ?></h3>
				<p class="contact-method-card__desc"><?php esc_html_e( 'Formulaire de contact en ligne, réponse sous 48h', 'cgt' ); ?></p>
			</div>

			<div class="contact-method-card">
				<div class="contact-method-card__icon contact-method-card__icon--phone">
					<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
						<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
					</svg>
				</div>
				<h3 class="contact-method-card__title"><?php esc_html_e( 'Par téléphone', 'cgt' ); ?></h3>
				<p class="contact-method-card__desc"><?php esc_html_e( 'Disponible du lundi au vendredi, 9h-17h', 'cgt' ); ?></p>
			</div>

			<div class="contact-method-card">
				<div class="contact-method-card__icon contact-method-card__icon--mail">
					<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
						<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
						<circle cx="12" cy="10" r="3"></circle>
					</svg>
				</div>
				<h3 class="contact-method-card__title"><?php esc_html_e( 'Par courrier', 'cgt' ); ?></h3>
				<p class="contact-method-card__desc"><?php esc_html_e( 'Adresse postale à Montreuil', 'cgt' ); ?></p>
			</div>
		</div>

		<!-- Main Content Sections -->
		<div class="contact-content">
			<!-- Contact Form Section -->
			<section class="contact-section contact-section--form">
				<div class="contact-section__header">
					<div class="contact-section__icon">
						<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
							<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
						</svg>
					</div>
					<div>
						<h2 class="contact-section__title"><?php esc_html_e( 'Envoyez-nous un message', 'cgt' ); ?></h2>
						<p class="contact-section__subtitle"><?php esc_html_e( 'Remplissez le formulaire ci-dessous et nous vous répondrons rapidement', 'cgt' ); ?></p>
					</div>
				</div>

				<div class="contact-form-wrapper">
					<?php echo do_shortcode( '[cgt_contact_form]' ); ?>
				</div>
			</section>

			<!-- Contact Info Section -->
			<aside class="contact-sidebar">
				<!-- Coordonnées -->
				<div class="contact-info-card">
					<h3 class="contact-info-card__title"><?php esc_html_e( 'Coordonnées', 'cgt' ); ?></h3>

					<div class="contact-info-item">
						<div class="contact-info-item__icon">
							<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
								<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
								<circle cx="12" cy="10" r="3"></circle>
							</svg>
						</div>
						<div class="contact-info-item__content">
							<strong><?php esc_html_e( 'Adresse', 'cgt' ); ?></strong>
							<p>
								<?php esc_html_e( 'Fédération CGT des Sociétés d\'Etudes', 'cgt' ); ?><br>
								<?php esc_html_e( '263, rue de Paris - Case 421', 'cgt' ); ?><br>
								<?php esc_html_e( '93514 Montreuil cedex', 'cgt' ); ?>
							</p>
						</div>
					</div>

					<div class="contact-info-item">
						<div class="contact-info-item__icon">
							<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
								<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
							</svg>
						</div>
						<div class="contact-info-item__content">
							<strong><?php esc_html_e( 'Téléphone', 'cgt' ); ?></strong>
							<p>
								<a href="tel:+33155828941">+33 1 55 82 89 41</a>
							</p>
						</div>
					</div>

					<div class="contact-info-item">
						<div class="contact-info-item__icon">
							<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
								<path d="M22 17H2a3 3 0 0 0 3-3V9a7 7 0 0 1 14 0v5a3 3 0 0 0 3 3zm-8.27 4a2 2 0 0 1-3.46 0"></path>
							</svg>
						</div>
						<div class="contact-info-item__content">
							<strong><?php esc_html_e( 'Fax', 'cgt' ); ?></strong>
							<p>+33 1 55 82 89 42</p>
						</div>
					</div>

					<div class="contact-info-item">
						<div class="contact-info-item__icon">
							<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
								<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
								<polyline points="22,6 12,13 2,6"></polyline>
							</svg>
						</div>
						<div class="contact-info-item__content">
							<strong><?php esc_html_e( 'Email', 'cgt' ); ?></strong>
							<p><a href="mailto:ccnprest@cgt.fr">ccnprest@cgt.fr</a></p>
						</div>
					</div>
				</div>

				<!-- Horaires -->
				<div class="contact-info-card contact-info-card--accent">
					<h3 class="contact-info-card__title"><?php esc_html_e( 'Horaires d\'ouverture', 'cgt' ); ?></h3>
					<div class="contact-hours">
						<div class="contact-hours__item">
							<span class="contact-hours__day"><?php esc_html_e( 'Lundi - Vendredi', 'cgt' ); ?></span>
							<span class="contact-hours__time"><?php esc_html_e( '9h00 - 17h00', 'cgt' ); ?></span>
						</div>
						<div class="contact-hours__item">
							<span class="contact-hours__day"><?php esc_html_e( 'Week-end', 'cgt' ); ?></span>
							<span class="contact-hours__time"><?php esc_html_e( 'Fermé', 'cgt' ); ?></span>
						</div>
					</div>
					<p class="contact-info-card__note">
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
							<circle cx="12" cy="12" r="10"></circle>
							<line x1="12" y1="16" x2="12" y2="12"></line>
							<line x1="12" y1="8" x2="12.01" y2="8"></line>
						</svg>
						<?php esc_html_e( 'Visite sur rendez-vous uniquement', 'cgt' ); ?>
					</p>
				</div>
			</aside>
		</div>

		<!-- Map Section -->
		<section class="contact-map-section">
			<div class="contact-map-section__header">
				<div class="contact-map-section__icon">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
						<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
						<circle cx="12" cy="10" r="3"></circle>
					</svg>
				</div>
				<div>
					<h2 class="contact-map-section__title"><?php esc_html_e( 'Venez nous rencontrer', 'cgt' ); ?></h2>
					<p class="contact-map-section__subtitle"><?php esc_html_e( 'Notre siège est situé à Montreuil, à quelques mètres de Paris, Porte de Montreuil', 'cgt' ); ?></p>
				</div>
			</div>

			<div class="contact-map-wrapper">
				<iframe
					class="contact-map"
					src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d10500.790245258937!2d2.416309!3d48.854443!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x47e67281e14c7b45%3A0x92c098e3186757a9!2s263%20Rue%20de%20Paris%2C%2093100%20Montreuil!5e0!3m2!1sfr!2sfr!4v1644042953753!5m2!1sfr!2sfr"
					width="100%"
					height="450"
					style="border:0;"
					allowfullscreen=""
					loading="lazy"
					referrerpolicy="no-referrer-when-downgrade">
				</iframe>
			</div>

			<!-- Access Info -->
			<div class="contact-access">
				<h3 class="contact-access__title"><?php esc_html_e( 'Comment nous rejoindre ?', 'cgt' ); ?></h3>
				<div class="contact-access-grid">
					<div class="contact-access-item">
						<div class="contact-access-item__icon">
							<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
								<circle cx="12" cy="12" r="10"></circle>
								<path d="M12 6v6l4 2"></path>
							</svg>
						</div>
						<div class="contact-access-item__content">
							<strong><?php esc_html_e( 'En métro', 'cgt' ); ?></strong>
							<p><?php esc_html_e( 'Porte-de-Montreuil ou Robespierre (Ligne 9)', 'cgt' ); ?></p>
						</div>
					</div>

					<div class="contact-access-item">
						<div class="contact-access-item__icon">
							<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
								<rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
								<path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
							</svg>
						</div>
						<div class="contact-access-item__content">
							<strong><?php esc_html_e( 'En bus/Tram', 'cgt' ); ?></strong>
							<p><?php esc_html_e( 'Lignes 57, 215, 351, Tram 3b (Porte-de-Montreuil), 318 (Etienne-Marcel)', 'cgt' ); ?></p>
						</div>
					</div>

					<div class="contact-access-item">
						<div class="contact-access-item__icon">
							<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
								<path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"></path>
								<circle cx="7" cy="17" r="2"></circle>
								<path d="M9 17h6"></path>
								<circle cx="17" cy="17" r="2"></circle>
							</svg>
						</div>
						<div class="contact-access-item__content">
							<strong><?php esc_html_e( 'En voiture', 'cgt' ); ?></strong>
							<p><?php esc_html_e( 'Périphérique, sortie Porte-de-Montreuil', 'cgt' ); ?></p>
						</div>
					</div>
				</div>
			</div>
		</section>
	</div>
</main>

<?php
get_footer();
