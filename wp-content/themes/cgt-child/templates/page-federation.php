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
	<section class="page-hero">
		<div class="container">
			<h1 class="page-hero__title federation-page__title"><?php esc_html_e( 'La Fédération CGT des Sociétés d’Études', 'cgt' ); ?></h1>
			<p class="page-hero__intro federation-page__intro"><?php esc_html_e( 'Un syndicat de proximité pour les bureaux d’études, le conseil et l’expertise.', 'cgt' ); ?></p>
		</div>
	</section>

	<section class="federation-section federation-section--story">
		<div class="container federation-story">
			<div class="federation-story__media" aria-hidden="true">
				<span class="placeholder"></span>
			</div>
			<div class="federation-story__content">
				<h2><?php esc_html_e( 'Notre histoire', 'cgt' ); ?></h2>
				<p><?php esc_html_e( 'Depuis plus de cinquante ans, la CGT des Sociétés d’Études organise, accompagne et défend les salarié·es des bureaux d’études, cabinets d’expertise, sociétés de conseil, structures d’ingénierie, organismes d’enquête et d’architecture. Présente sur tout le territoire, la fédération agit pour sécuriser les parcours professionnels, améliorer les conventions collectives et faire progresser les droits individuels et collectifs.', 'cgt' ); ?></p>
				<p><?php esc_html_e( 'Qu’il s’agisse de salaires, de conditions de travail ou de lutte contre la précarité, nous construisons des actions concrètes avec et pour les équipes syndicales afin de faire vivre la solidarité CGT dans chaque entreprise.', 'cgt' ); ?></p>
			</div>
		</div>
	</section>

	<section class="federation-section federation-section--branches">
		<div class="container">
			<h2><?php esc_html_e( 'Nos branches et champs d’intervention', 'cgt' ); ?></h2>
			<p><?php esc_html_e( 'Nous couvrons l’ensemble des secteurs des sociétés d’études. Retrouvez ci-dessous les principales branches et domaines d’activité accompagnés par la fédération.', 'cgt' ); ?></p>
			<div class="federation-branches">
				<div class="federation-branches__column">
					<h3><?php esc_html_e( 'Bureaux d’études techniques et de conseil', 'cgt' ); ?></h3>
					<ul>
						<li><?php esc_html_e( 'Associations agréées de surveillance de la qualité de l’air', 'cgt' ); ?></li>
						<li><?php esc_html_e( 'Conseil d’Architecture d’Urbanisme et d’Environnement', 'cgt' ); ?></li>
						<li><?php esc_html_e( 'Études et conseil', 'cgt' ); ?></li>
						<li><?php esc_html_e( 'Foires et salons', 'cgt' ); ?></li>
						<li><?php esc_html_e( 'Formation professionnelle', 'cgt' ); ?></li>
						<li><?php esc_html_e( 'Informatique & ingénierie', 'cgt' ); ?></li>
						<li><?php esc_html_e( 'Organismes de contrôle et de prévention', 'cgt' ); ?></li>
						<li><?php esc_html_e( 'Sondage', 'cgt' ); ?></li>
					</ul>

					<h3><?php esc_html_e( 'Officines judiciaires et parajudiciaires', 'cgt' ); ?></h3>
					<ul>
						<li><?php esc_html_e( 'Avocats et personnels de cabinets', 'cgt' ); ?></li>
						<li><?php esc_html_e( 'Commissaires de justice et huissiers', 'cgt' ); ?></li>
						<li><?php esc_html_e( 'Notariat', 'cgt' ); ?></li>
						<li><?php esc_html_e( 'Professions réglementées auprès des juridictions', 'cgt' ); ?></li>
						<li><?php esc_html_e( 'Administrateurs et mandataires judiciaires', 'cgt' ); ?></li>
						<li><?php esc_html_e( 'Greffes des tribunaux de commerce', 'cgt' ); ?></li>
					</ul>
				</div>
				<div class="federation-branches__column">
					<h3><?php esc_html_e( 'Expertises et structures spécialisées', 'cgt' ); ?></h3>
					<ul>
						<li><?php esc_html_e( 'Coopératives d’activité et d’emploi', 'cgt' ); ?></li>
						<li><?php esc_html_e( 'Expertises industrielles et commerciales', 'cgt' ); ?></li>
						<li><?php esc_html_e( 'Experts automobiles', 'cgt' ); ?></li>
						<li><?php esc_html_e( 'Experts comptables et commissaires aux comptes', 'cgt' ); ?></li>
						<li><?php esc_html_e( 'Associations de gestion comptable', 'cgt' ); ?></li>
						<li><?php esc_html_e( 'Organismes de développement économique', 'cgt' ); ?></li>
						<li><?php esc_html_e( 'Portage salarial', 'cgt' ); ?></li>
					</ul>

					<h3><?php esc_html_e( 'Prestataires de services', 'cgt' ); ?></h3>
					<ul>
						<li><?php esc_html_e( 'Accueil en entreprise & animations commerciales', 'cgt' ); ?></li>
						<li><?php esc_html_e( 'Centres d’appels et télé-secrétariat', 'cgt' ); ?></li>
						<li><?php esc_html_e( 'Enquête civile et recouvrement de créances', 'cgt' ); ?></li>
						<li><?php esc_html_e( 'Location de bureaux et de salles', 'cgt' ); ?></li>
						<li><?php esc_html_e( 'Traduction et services linguistiques', 'cgt' ); ?></li>
						<li><?php esc_html_e( 'Syndicat National des Auteurs d’Invention Indépendants (SNAII)', 'cgt' ); ?></li>
					</ul>
				</div>
			</div>
		</div>
	</section>

	<section class="federation-section federation-section--actions">
		<div class="container">
			<h2><?php esc_html_e( 'Ce que nous faisons pour les salarié·es', 'cgt' ); ?></h2>
			<ul class="federation-actions">
				<li><?php esc_html_e( 'Accompagnement juridique, défense individuelle et collective, négociation d’accords.', 'cgt' ); ?></li>
				<li><?php esc_html_e( 'Formation syndicale des équipes, partage d’outils et de ressources pour structurer les collectifs.', 'cgt' ); ?></li>
				<li><?php esc_html_e( 'Veille sur les conventions collectives, revalorisation des salaires et amélioration des conditions de travail.', 'cgt' ); ?></li>
				<li><?php esc_html_e( 'Actions de solidarité et campagnes nationales pour faire progresser les droits dans toutes les branches.', 'cgt' ); ?></li>
			</ul>
		</div>
	</section>

	<section class="federation-section federation-section--cta">
		<div class="container federation-cta">
			<div class="federation-cta__content">
				<h2><?php esc_html_e( 'Envie de nous rejoindre ?', 'cgt' ); ?></h2>
				<p><?php esc_html_e( 'Adhérez pour bénéficier d’un accompagnement personnalisé et participer à la construction d’un syndicalisme offensif. Vous pouvez également nous contacter pour échanger avec une équipe CGT près de chez vous.', 'cgt' ); ?></p>
			</div>
			<div class="federation-cta__actions">
				<a class="btn" href="<?php echo esc_url( home_url( '/contact' ) ); ?>"><?php esc_html_e( 'Nous contacter', 'cgt' ); ?></a>
				<a class="btn btn-light" href="<?php echo esc_url( home_url( '/publier-article' ) ); ?>"><?php esc_html_e( 'Devenir adhérent·e', 'cgt' ); ?></a>
			</div>
		</div>
	</section>
</main>

<?php
get_footer();
