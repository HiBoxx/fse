<?php
/**
 * Template Name: Fédération CGT
 *
 * Fédération page template.
 *
 * @package CGT_Child
 */

get_header();
?>

<main id="primary" class="site-main federation-page">
	<!-- Hero Header -->
	<div class="federation-hero">
		<div class="container">
			<div class="federation-hero__content">
				<div class="federation-hero__badge">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
						<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
						<circle cx="9" cy="7" r="4"></circle>
						<path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
						<path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
					</svg>
					<span><?php esc_html_e( 'Qui sommes-nous ?', 'cgt' ); ?></span>
				</div>
				<h1 class="federation-hero__title"><?php esc_html_e( 'La Fédération CGT des Sociétés d\'Études', 'cgt' ); ?></h1>
				<p class="federation-hero__description"><?php esc_html_e( 'Un syndicat de proximité au service des salarié·es des bureaux d\'études, du conseil et de l\'expertise depuis plus de 50 ans.', 'cgt' ); ?></p>
			</div>
		</div>
	</div>

	<div class="container federation-container">
		<!-- Story Section -->
		<section class="federation-story">
			<div class="federation-story__image">
		<img
			src="<?php echo esc_url( 'https://fsetud-cgt.fr/wp-content/uploads/2025/10/federation.png' ); ?>"
					alt="<?php esc_attr_e( 'Militants de la Fédération CGT des Sociétés d\'Études', 'cgt' ); ?>"
					loading="lazy"
				>
			</div>
			<div class="federation-story__content">
				<div class="federation-story__header">
					<div class="federation-story__icon">
						<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
							<path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
							<path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
						</svg>
					</div>
					<h2 class="federation-story__title"><?php esc_html_e( 'Notre histoire', 'cgt' ); ?></h2>
				</div>
				<p><?php esc_html_e( 'Depuis plus de cinquante ans, la CGT des Sociétés d\'Études organise, accompagne et défend les salarié·es des bureaux d\'études, cabinets d\'expertise, sociétés de conseil, structures d\'ingénierie, organismes d\'enquête et d\'architecture.', 'cgt' ); ?></p>
				<p><?php esc_html_e( 'Présente sur tout le territoire, la fédération agit pour sécuriser les parcours professionnels, améliorer les conventions collectives et faire progresser les droits individuels et collectifs.', 'cgt' ); ?></p>
				<p><?php esc_html_e( 'Qu\'il s\'agisse de salaires, de conditions de travail ou de lutte contre la précarité, nous construisons des actions concrètes avec et pour les équipes syndicales afin de faire vivre la solidarité CGT dans chaque entreprise.', 'cgt' ); ?></p>
			</div>
		</section>

		<!-- Stats Section -->
		<section class="federation-stats">
			<div class="federation-stat-card">
				<div class="federation-stat-card__icon">
					<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
						<rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
						<line x1="16" y1="2" x2="16" y2="6"></line>
						<line x1="8" y1="2" x2="8" y2="6"></line>
						<line x1="3" y1="10" x2="21" y2="10"></line>
					</svg>
				</div>
				<div class="federation-stat-card__content">
					<div class="federation-stat-card__value">50+</div>
					<div class="federation-stat-card__label"><?php esc_html_e( 'Années d\'expérience', 'cgt' ); ?></div>
				</div>
			</div>

			<div class="federation-stat-card">
				<div class="federation-stat-card__icon">
					<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
						<path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
					</svg>
				</div>
				<div class="federation-stat-card__content">
					<div class="federation-stat-card__value">25+</div>
					<div class="federation-stat-card__label"><?php esc_html_e( 'Branches couvertes', 'cgt' ); ?></div>
				</div>
			</div>

			<div class="federation-stat-card">
				<div class="federation-stat-card__icon">
					<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
						<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
						<circle cx="9" cy="7" r="4"></circle>
						<path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
						<path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
					</svg>
				</div>
				<div class="federation-stat-card__content">
					<div class="federation-stat-card__value">1000+</div>
					<div class="federation-stat-card__label"><?php esc_html_e( 'Adhérents actifs', 'cgt' ); ?></div>
				</div>
			</div>
		</section>

		<!-- Branches Section -->
		<section class="federation-branches-section">
			<div class="federation-branches-section__header">
				<div class="federation-branches-section__icon">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
						<path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
					</svg>
				</div>
				<div>
					<h2 class="federation-branches-section__title"><?php esc_html_e( 'Nos branches et champs d\'intervention', 'cgt' ); ?></h2>
					<p class="federation-branches-section__subtitle"><?php esc_html_e( 'Nous couvrons l\'ensemble des secteurs des sociétés d\'études', 'cgt' ); ?></p>
				</div>
			</div>

			<div class="federation-branches-grid">
				<!-- Bureaux d'études -->
				<div class="federation-branch-card">
					<div class="federation-branch-card__header">
						<div class="federation-branch-card__icon">
							<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
								<polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
							</svg>
						</div>
						<h3 class="federation-branch-card__title"><?php esc_html_e( 'Bureaux d\'études techniques', 'cgt' ); ?></h3>
					</div>
					<ul class="federation-branch-card__list">
						<li><?php esc_html_e( 'Associations agréées qualité de l\'air', 'cgt' ); ?></li>
						<li><?php esc_html_e( 'Conseil Architecture & Urbanisme', 'cgt' ); ?></li>
						<li><?php esc_html_e( 'Études et conseil', 'cgt' ); ?></li>
						<li><?php esc_html_e( 'Foires et salons', 'cgt' ); ?></li>
						<li><?php esc_html_e( 'Formation professionnelle', 'cgt' ); ?></li>
						<li><?php esc_html_e( 'Informatique & ingénierie', 'cgt' ); ?></li>
						<li><?php esc_html_e( 'Organismes de contrôle', 'cgt' ); ?></li>
						<li><?php esc_html_e( 'Sondage', 'cgt' ); ?></li>
					</ul>
				</div>

				<!-- Officines judiciaires -->
				<div class="federation-branch-card">
					<div class="federation-branch-card__header">
						<div class="federation-branch-card__icon">
							<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
								<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
								<polyline points="14 2 14 8 20 8"></polyline>
							</svg>
						</div>
						<h3 class="federation-branch-card__title"><?php esc_html_e( 'Officines judiciaires', 'cgt' ); ?></h3>
					</div>
					<ul class="federation-branch-card__list">
						<li><?php esc_html_e( 'Avocats et personnels de cabinets', 'cgt' ); ?></li>
						<li><?php esc_html_e( 'Commissaires de justice', 'cgt' ); ?></li>
						<li><?php esc_html_e( 'Notariat', 'cgt' ); ?></li>
						<li><?php esc_html_e( 'Professions réglementées', 'cgt' ); ?></li>
						<li><?php esc_html_e( 'Administrateurs judiciaires', 'cgt' ); ?></li>
						<li><?php esc_html_e( 'Greffes tribunaux de commerce', 'cgt' ); ?></li>
					</ul>
				</div>

				<!-- Expertises -->
				<div class="federation-branch-card">
					<div class="federation-branch-card__header">
						<div class="federation-branch-card__icon">
							<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
								<circle cx="11" cy="11" r="8"></circle>
								<path d="m21 21-4.35-4.35"></path>
							</svg>
						</div>
						<h3 class="federation-branch-card__title"><?php esc_html_e( 'Expertises spécialisées', 'cgt' ); ?></h3>
					</div>
					<ul class="federation-branch-card__list">
						<li><?php esc_html_e( 'Coopératives d\'activité', 'cgt' ); ?></li>
						<li><?php esc_html_e( 'Expertises industrielles', 'cgt' ); ?></li>
						<li><?php esc_html_e( 'Experts automobiles', 'cgt' ); ?></li>
						<li><?php esc_html_e( 'Experts comptables', 'cgt' ); ?></li>
						<li><?php esc_html_e( 'Gestion comptable', 'cgt' ); ?></li>
						<li><?php esc_html_e( 'Développement économique', 'cgt' ); ?></li>
						<li><?php esc_html_e( 'Portage salarial', 'cgt' ); ?></li>
					</ul>
				</div>

				<!-- Prestataires -->
				<div class="federation-branch-card">
					<div class="federation-branch-card__header">
						<div class="federation-branch-card__icon">
							<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
								<path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path>
								<line x1="7" y1="7" x2="7.01" y2="7"></line>
							</svg>
						</div>
						<h3 class="federation-branch-card__title"><?php esc_html_e( 'Prestataires de services', 'cgt' ); ?></h3>
					</div>
					<ul class="federation-branch-card__list">
						<li><?php esc_html_e( 'Accueil en entreprise', 'cgt' ); ?></li>
						<li><?php esc_html_e( 'Animations commerciales', 'cgt' ); ?></li>
						<li><?php esc_html_e( 'Centres d\'appels', 'cgt' ); ?></li>
						<li><?php esc_html_e( 'Enquête civile', 'cgt' ); ?></li>
						<li><?php esc_html_e( 'Location de bureaux', 'cgt' ); ?></li>
						<li><?php esc_html_e( 'Traduction', 'cgt' ); ?></li>
						<li><?php esc_html_e( 'SNAII', 'cgt' ); ?></li>
					</ul>
				</div>
			</div>
		</section>

		<!-- Actions Section -->
		<section class="federation-actions-section">
			<div class="federation-actions-section__header">
				<div class="federation-actions-section__icon">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
						<polyline points="20 6 9 17 4 12"></polyline>
					</svg>
				</div>
				<div>
					<h2 class="federation-actions-section__title"><?php esc_html_e( 'Ce que nous faisons pour vous', 'cgt' ); ?></h2>
					<p class="federation-actions-section__subtitle"><?php esc_html_e( 'Des actions concrètes au quotidien pour améliorer vos conditions de travail', 'cgt' ); ?></p>
				</div>
			</div>

			<div class="federation-actions-grid">
				<div class="federation-action-card">
					<div class="federation-action-card__icon">
						<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
							<path d="M22 10v6M2 10l10-5 10 5-10 5z"></path>
							<path d="M6 12v5c3 3 9 3 12 0v-5"></path>
						</svg>
					</div>
					<h3 class="federation-action-card__title"><?php esc_html_e( 'Accompagnement juridique', 'cgt' ); ?></h3>
					<p class="federation-action-card__desc"><?php esc_html_e( 'Défense individuelle et collective, négociation d\'accords et suivi des dossiers.', 'cgt' ); ?></p>
				</div>

				<div class="federation-action-card">
					<div class="federation-action-card__icon">
						<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
							<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
							<circle cx="9" cy="7" r="4"></circle>
							<path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
							<path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
						</svg>
					</div>
					<h3 class="federation-action-card__title"><?php esc_html_e( 'Formation syndicale', 'cgt' ); ?></h3>
					<p class="federation-action-card__desc"><?php esc_html_e( 'Formation des équipes, partage d\'outils et ressources pour structurer les collectifs.', 'cgt' ); ?></p>
				</div>

				<div class="federation-action-card">
					<div class="federation-action-card__icon">
						<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
							<line x1="12" y1="1" x2="12" y2="23"></line>
							<path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
						</svg>
					</div>
					<h3 class="federation-action-card__title"><?php esc_html_e( 'Conventions collectives', 'cgt' ); ?></h3>
					<p class="federation-action-card__desc"><?php esc_html_e( 'Veille sur les conventions, revalorisation des salaires et amélioration des conditions.', 'cgt' ); ?></p>
				</div>

				<div class="federation-action-card">
					<div class="federation-action-card__icon">
						<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
							<path d="M12 20h9"></path>
							<path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
						</svg>
					</div>
					<h3 class="federation-action-card__title"><?php esc_html_e( 'Actions de solidarité', 'cgt' ); ?></h3>
					<p class="federation-action-card__desc"><?php esc_html_e( 'Campagnes nationales pour faire progresser les droits dans toutes les branches.', 'cgt' ); ?></p>
				</div>
			</div>
		</section>
	</div>

	<!-- CTA Section -->
	<section class="federation-cta-section">
		<div class="container">
			<div class="federation-cta">
				<div class="federation-cta__content">
					<h2 class="federation-cta__title"><?php esc_html_e( 'Envie de nous rejoindre ?', 'cgt' ); ?></h2>
					<p class="federation-cta__desc"><?php esc_html_e( 'Adhérez pour bénéficier d\'un accompagnement personnalisé et participer à la construction d\'un syndicalisme offensif. Vous pouvez également nous contacter pour échanger avec une équipe CGT près de chez vous.', 'cgt' ); ?></p>
				</div>
				<div class="federation-cta__actions">
					<a class="btn btn-white" href="<?php echo esc_url( home_url( '/contact' ) ); ?>">
						<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
							<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
						</svg>
						<?php esc_html_e( 'Nous contacter', 'cgt' ); ?>
					</a>
					<a class="btn btn-outline-white" href="<?php echo esc_url( home_url( '/connexion' ) ); ?>">
						<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
							<path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
							<circle cx="8.5" cy="7" r="4"></circle>
							<polyline points="17 11 19 13 23 9"></polyline>
						</svg>
						<?php esc_html_e( 'Devenir adhérent·e', 'cgt' ); ?>
					</a>
				</div>
			</div>
		</div>
	</section>
</main>

<?php
get_footer();
