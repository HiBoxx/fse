<?php
/**
 * Front page template with structured sections.
 *
 * @package CGT_Child
 */

get_header();

$latest_communiques = new WP_Query(
    array(
        'post_type'      => 'communiques_de_presse',
        'posts_per_page' => 5,
    )
);

$resource_args = array(
	'post_type'      => array( 'tracts', 'dossiers_de_presse' ),
	'posts_per_page' => 4,
);

if ( ! cgt_user_can_read_private() ) {
	$resource_args['meta_query'][] = array(
		'key'     => 'cgt_visibilite',
		'value'   => 'prive',
		'compare' => '!=',
	);
}

$latest_resources = new WP_Query( $resource_args );

$branches = get_terms(
	array(
		'taxonomy'   => 'branche',
		'hide_empty' => false,
		'number'     => 6,
	)
);

$actualites_page = get_page_by_path( 'actualites', OBJECT, 'page' );
$actualites_link = $actualites_page ? get_permalink( $actualites_page ) : get_post_type_archive_link( 'post' );
$mediatheque_page = get_page_by_path( 'mediatheque', OBJECT, 'page' );
$mediatheque_link = $mediatheque_page ? get_permalink( $mediatheque_page ) : home_url( '/mediatheque' );
?>

<main id="primary" class="site-main">
	<section class="home-hero">
		<div class="container home-hero__inner">
			<div class="home-hero__content">
				<p class="home-hero__eyebrow"><?php esc_html_e( 'Fédération CGT des Sociétés d’Études', 'cgt' ); ?></p>
				<h1 class="home-hero__title"><?php esc_html_e( 'Organisons la solidarité dans les bureaux d’études, le conseil et l’expertise', 'cgt' ); ?></h1>
				<p class="home-hero__lead"><?php esc_html_e( 'Actualités, analyses et outils pour les salarié·es des sociétés d’études, d’ingénierie, de conseil et d’expertise.', 'cgt' ); ?></p>
				<div class="home-hero__actions">
					<a class="btn" href="<?php echo esc_url( home_url( '/contact' ) ); ?>"><?php esc_html_e( 'Rejoindre la CGT', 'cgt' ); ?></a>
					<a class="btn btn-light" href="<?php echo esc_url( home_url( '/espace-adherent' ) ); ?>"><?php esc_html_e( 'Espace adhérent', 'cgt' ); ?></a>
				</div>
				<ul class="home-hero__meta">
					<li><?php esc_html_e( 'Bureaux d’études | Conseil | Ingénierie | Expertise', 'cgt' ); ?></li>
					<li><?php esc_html_e( 'Soutien juridique, syndical et revendicatif au quotidien', 'cgt' ); ?></li>
					<li><?php esc_html_e( 'Mobilisations nationales et actions dans chaque branche', 'cgt' ); ?></li>
				</ul>
			</div>
			<div class="home-hero__media" aria-hidden="true">
				<span class="placeholder"></span>
			</div>
		</div>
	</section>

	<section class="home-nav">
		<div class="container">
			<ul class="home-nav__grid" aria-label="<?php esc_attr_e( 'Navigation rapide', 'cgt' ); ?>">
				<li>
					<a href="<?php echo esc_url( home_url( '/la-federation' ) ); ?>" class="home-nav__card">
						<span class="home-nav__icon" aria-hidden="true">⚙️</span>
						<span>
							<strong><?php esc_html_e( 'La Fédération', 'cgt' ); ?></strong>
							<small><?php esc_html_e( 'Nos priorités et nos élus', 'cgt' ); ?></small>
						</span>
					</a>
				</li>
				<li>
					<a href="<?php echo esc_url( $actualites_link ); ?>" class="home-nav__card">
						<span class="home-nav__icon" aria-hidden="true">🗞️</span>
						<span>
							<strong><?php esc_html_e( 'Articles', 'cgt' ); ?></strong>
							<small><?php esc_html_e( 'Prises de position publiques', 'cgt' ); ?></small>
						</span>
					</a>
				</li>
				<li>
					<a href="<?php echo esc_url( get_post_type_archive_link( 'tracts' ) ); ?>" class="home-nav__card home-nav__card--tracts">
						<span class="home-nav__icon" aria-hidden="true">📄</span>
						<span>
							<strong><?php esc_html_e( 'Tracts', 'cgt' ); ?></strong>
							<small><?php esc_html_e( 'Documents prêts à diffuser', 'cgt' ); ?></small>
						</span>
					</a>
				</li>
				<li>
					<a href="<?php echo esc_url( $mediatheque_link ); ?>" class="home-nav__card">
						<span class="home-nav__icon" aria-hidden="true">📚</span>
						<span>
							<strong><?php esc_html_e( 'Bibliothèque', 'cgt' ); ?></strong>
							<small><?php esc_html_e( 'Ressources presse', 'cgt' ); ?></small>
						</span>
					</a>
				</li>
			</ul>
		</div>
	</section>

	<?php
$communiques_tabs = array(
	'actualites'       => array(
		'label' => __( 'Actualités', 'cgt' ),
		'term'  => 'actualites',
	),
	'bulletins'        => array(
		'label' => __( 'Bulletins', 'cgt' ),
		'term'  => 'bulletins',
	),
	'tracts'           => array(
		'label' => __( 'Tracts d’entreprise', 'cgt' ),
		'term'  => 'tracts-de-la-federation',
	),
	'presse'           => array(
		'label' => __( 'Presse', 'cgt' ),
		'term'  => 'presse',
	),
);
	?>
	<section class="home-section">
		<div class="container">
			<header class="home-section__header">
				<h2><?php esc_html_e( 'Communiqués récents', 'cgt' ); ?></h2>
				<p class="section-subtitle-smaller"><?php esc_html_e( 'Filtrez nos prises de position par thématique.', 'cgt' ); ?></p>
			</header>

			<div class="home-tabs" role="tablist" aria-label="<?php esc_attr_e( 'Catégories de communiqués', 'cgt' ); ?>">
				<?php
				$first_tab = true;
				foreach ( $communiques_tabs as $slug => $tab ) :
					$tab_id = 'home-communiques-tab-' . $slug;
					?>
					<button
						id="<?php echo esc_attr( $tab_id ); ?>"
						class="home-tab<?php echo $first_tab ? ' is-active' : ''; ?>"
						type="button"
						role="tab"
						data-tab="<?php echo esc_attr( $slug ); ?>"
						aria-selected="<?php echo $first_tab ? 'true' : 'false'; ?>"
						aria-controls="<?php echo esc_attr( 'home-communiques-panel-' . $slug ); ?>"
					>
						<?php echo esc_html( $tab['label'] ); ?>
					</button>
					<?php
					$first_tab = false;
				endforeach;
				?>
			</div>

			<div class="home-tab-panels">
				<?php
				$first_panel = true;
				foreach ( $communiques_tabs as $slug => $tab ) :
					$query_args = array(
						'post_type'      => 'post',
						'posts_per_page' => 4,
						'tax_query'      => array(
							array(
								'taxonomy' => 'category',
								'field'    => 'slug',
								'terms'    => $tab['term'],
							),
						),
					);

					if ( empty( $tab['term'] ) ) {
						unset( $query_args['tax_query'] );
					}

					$tab_query = new WP_Query( $query_args );
					?>
					<div
						id="<?php echo esc_attr( 'home-communiques-panel-' . $slug ); ?>"
						class="home-tab-panel<?php echo $first_panel ? ' is-active' : ''; ?>"
						role="tabpanel"
						data-tab-panel="<?php echo esc_attr( $slug ); ?>"
						aria-labelledby="<?php echo esc_attr( 'home-communiques-tab-' . $slug ); ?>"
					>
					<?php if ( $tab_query->have_posts() ) : ?>
						<div class="home-communique-panel-grid">
							<?php
							while ( $tab_query->have_posts() ) :
								$tab_query->the_post();
								get_template_part( 'parts/card', null, array( 'context' => 'communique' ) );
							endwhile;
							?>
						</div>
					<?php else : ?>
						<?php
						$fallback_query = new WP_Query(
							array(
								'post_type'      => 'post',
								'posts_per_page' => 4,
							)
						);
						if ( $fallback_query->have_posts() ) :
							?>
							<div class="home-communique-panel-grid home-communique-panel-grid--fallback">
								<?php
								while ( $fallback_query->have_posts() ) :
									$fallback_query->the_post();
									get_template_part( 'parts/card', null, array( 'context' => 'communique' ) );
								endwhile;
								wp_reset_postdata();
								?>
							</div>
						<?php else : ?>
							<p><?php esc_html_e( 'Aucun article publié pour le moment.', 'cgt' ); ?></p>
						<?php endif; ?>
						<?php wp_reset_postdata(); ?>
					<?php endif; ?>
					<?php wp_reset_postdata(); ?>
				</div>
					<?php
					$first_panel = false;
				endforeach;
				?>
			</div>

			<p class="home-section__footer">
				<a class="btn btn-compact" href="<?php echo esc_url( $actualites_link ); ?>">
					<?php esc_html_e( 'Voir tous les articles', 'cgt' ); ?>
				</a>
			</p>
		</div>
	</section>

	<section class="home-section home-section--shade">
		<div class="container">
			<header class="home-section__header">
				<h2><?php esc_html_e( 'Tracts', 'cgt' ); ?></h2>
				<p class="section-subtitle-smaller"><?php esc_html_e( 'Retrouvez les tracts récents prêts à diffuser.', 'cgt' ); ?></p>
			</header>

			<?php if ( $latest_resources->have_posts() ) : ?>
				<div class="home-grid">
					<?php
					while ( $latest_resources->have_posts() ) :
						$latest_resources->the_post();
						if ( 'tracts' === get_post_type() ) {
							get_template_part( 'parts/card', null, array( 'context' => 'tract' ) );
						}
					endwhile;
					?>
				</div>
				<p class="home-section__footer home-section__footer--actions">
					<a class="btn" href="<?php echo esc_url( get_post_type_archive_link( 'tracts' ) ); ?>">
						<?php esc_html_e( 'Tous les tracts', 'cgt' ); ?>
					</a>
				</p>
			<?php else : ?>
				<p><?php esc_html_e( 'Les ressources seront bientôt disponibles.', 'cgt' ); ?></p>
			<?php endif; ?>
			<?php wp_reset_postdata(); ?>
		</div>
	</section>

	<section class="home-section home-section--cta">
		<div class="container home-section__cta">
			<div>
				<h2><?php esc_html_e( 'Adhérez et accédez à vos ressources dédiées', 'cgt' ); ?></h2>
				<p><?php esc_html_e( 'Tracts privés, analyses internes et accompagnement personnalisé pour défendre vos droits.', 'cgt' ); ?></p>
				<div class="home-cta__actions">
					<a class="btn" href="<?php echo esc_url( home_url( '/contact' ) ); ?>"><?php esc_html_e( 'Prendre contact', 'cgt' ); ?></a>
					<a class="btn btn-light" href="<?php echo esc_url( home_url( '/espace-adherent' ) ); ?>"><?php esc_html_e( 'Se connecter', 'cgt' ); ?></a>
				</div>
			</div>
			<div class="home-cta__aside" aria-hidden="true">
				<span class="placeholder"></span>
			</div>
		</div>
	</section>

	<section class="home-section home-section--shade">
		<div class="container">
			<div class="home-section__duo">
				<div class="home-duo-card">
					<h2><?php esc_html_e( 'Contact presse & médias', 'cgt' ); ?></h2>
					<p><?php esc_html_e( 'Nous répondons rapidement aux demandes d’interview et fournissons des dossiers prêts à l’emploi.', 'cgt' ); ?></p>
					<div class="home-duo-card__content">
						<p><?php esc_html_e( 'Écrivez-nous pour organiser une interview, vérifier une information ou obtenir des éléments chiffrés.', 'cgt' ); ?></p>
						<a class="btn" href="mailto:ccnprest@cgt.fr"><?php esc_html_e( 'Contacter la cellule presse', 'cgt' ); ?></a>
					</div>
				</div>
				<div class="home-duo-card">
					<h2><?php esc_html_e( 'Publiez vos articles', 'cgt' ); ?></h2>
					<p><?php esc_html_e( 'Partagez vos bulletins, tracts ou communications avec la fédération en quelques clics.', 'cgt' ); ?></p>
					<div class="home-duo-card__content">
						<p><?php esc_html_e( 'Envoyez-nous vos contenus via le formulaire dédié. Ils seront vérifiés puis publiés par l’équipe fédérale.', 'cgt' ); ?></p>
						<a class="btn" href="<?php echo esc_url( home_url( '/publier-article' ) ); ?>"><?php esc_html_e( 'Proposer un article', 'cgt' ); ?></a>
					</div>
				</div>
			</div>
		</div>
	</section>

	<!-- Section Rejoignez-nous & Slider -->
	<section class="home-section home-section--join">
		<div class="container">
			<div class="home-join-wrapper">
				<div class="home-join-content">
					<h2><?php esc_html_e( 'Rejoignez-nous', 'cgt' ); ?></h2>
					<p><?php esc_html_e( 'Ensemble, nous sommes plus forts. Syndiquez-vous et restez informé·e de l\'actualité fédérale.', 'cgt' ); ?></p>

					<div class="home-join-actions">
						<div class="home-join-card">
							<div class="home-join-card__icon">
								<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
									<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
									<circle cx="9" cy="7" r="4"></circle>
									<path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
									<path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
								</svg>
							</div>
							<h3><?php esc_html_e( 'Se syndiquer', 'cgt' ); ?></h3>
							<p><?php esc_html_e( 'Adhérez à la CGT et bénéficiez d\'un accompagnement syndical complet.', 'cgt' ); ?></p>
							<a href="<?php echo esc_url( home_url( '/connexion' ) ); ?>" class="btn btn-compact"><?php esc_html_e( 'Adhérer maintenant', 'cgt' ); ?></a>
						</div>

						<div class="home-join-card">
							<div class="home-join-card__icon">
								<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
									<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
									<polyline points="22,6 12,13 2,6"></polyline>
								</svg>
							</div>
							<h3><?php esc_html_e( 'Liste de diffusion', 'cgt' ); ?></h3>
							<p><?php esc_html_e( 'Recevez nos actualités, tracts et bulletins directement par email.', 'cgt' ); ?></p>
							<button type="button" class="btn btn-compact btn-outline" id="openNewsletterModal"><?php esc_html_e( 'S\'inscrire', 'cgt' ); ?></button>
						</div>
					</div>
				</div>

				<div class="home-join-slider">
					<?php
					// Récupérer les images du slider depuis les options
					$slider_images = get_option( 'cgt_home_slider_images', array() );

					if ( ! empty( $slider_images ) && is_array( $slider_images ) ) :
						?>
						<div class="home-slider">
							<div class="home-slider__track">
								<?php foreach ( $slider_images as $index => $image_id ) : ?>
									<?php if ( $image_id ) : ?>
										<div class="home-slider__slide <?php echo 0 === $index ? 'is-active' : ''; ?>">
											<?php echo wp_get_attachment_image( $image_id, 'medium_large', false, array( 'class' => 'home-slider__image' ) ); ?>
										</div>
									<?php endif; ?>
								<?php endforeach; ?>
							</div>

							<?php if ( count( $slider_images ) > 1 ) : ?>
								<div class="home-slider__controls">
									<button type="button" class="home-slider__btn home-slider__btn--prev" aria-label="<?php esc_attr_e( 'Image précédente', 'cgt' ); ?>">
										<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
											<polyline points="15 18 9 12 15 6"></polyline>
										</svg>
									</button>
									<button type="button" class="home-slider__btn home-slider__btn--next" aria-label="<?php esc_attr_e( 'Image suivante', 'cgt' ); ?>">
										<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
											<polyline points="9 18 15 12 9 6"></polyline>
										</svg>
									</button>
								</div>
								<div class="home-slider__dots">
									<?php foreach ( $slider_images as $index => $image_id ) : ?>
										<button type="button" class="home-slider__dot <?php echo 0 === $index ? 'is-active' : ''; ?>" data-slide="<?php echo esc_attr( $index ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Aller à l\'image %d', 'cgt' ), $index + 1 ) ); ?>"></button>
									<?php endforeach; ?>
								</div>
							<?php endif; ?>
						</div>
					<?php else : ?>
						<div class="home-slider home-slider--placeholder">
							<div class="home-slider__placeholder">
								<svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
									<rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
									<circle cx="8.5" cy="8.5" r="1.5"></circle>
									<polyline points="21 15 16 10 5 21"></polyline>
								</svg>
								<p><?php esc_html_e( 'Aucune image configurée', 'cgt' ); ?></p>
							</div>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<!-- Modal Newsletter -->
		<div class="newsletter-modal" id="newsletterModal">
			<div class="newsletter-modal__overlay"></div>
			<div class="newsletter-modal__content">
				<button type="button" class="newsletter-modal__close" id="closeNewsletterModal" aria-label="<?php esc_attr_e( 'Fermer', 'cgt' ); ?>">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
						<line x1="18" y1="6" x2="6" y2="18"></line>
						<line x1="6" y1="6" x2="18" y2="18"></line>
					</svg>
				</button>

				<div class="newsletter-modal__header">
					<div class="newsletter-modal__icon">
						<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
							<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
							<polyline points="22,6 12,13 2,6"></polyline>
						</svg>
					</div>
					<h2><?php esc_html_e( 'Inscription à la liste de diffusion', 'cgt' ); ?></h2>
					<p><?php esc_html_e( 'Restez informé·e de nos actualités, tracts et bulletins fédéraux.', 'cgt' ); ?></p>
				</div>

				<form id="newsletterForm" class="newsletter-form">
					<div class="newsletter-form__message" id="newsletterMessage" style="display: none;"></div>

					<div class="newsletter-form__field">
						<label for="newsletter_prenom"><?php esc_html_e( 'Prénom', 'cgt' ); ?> <span class="required">*</span></label>
						<input type="text" id="newsletter_prenom" name="prenom" required>
					</div>

					<div class="newsletter-form__field">
						<label for="newsletter_nom"><?php esc_html_e( 'Nom', 'cgt' ); ?> <span class="required">*</span></label>
						<input type="text" id="newsletter_nom" name="nom" required>
					</div>

					<div class="newsletter-form__field">
						<label for="newsletter_email"><?php esc_html_e( 'Email', 'cgt' ); ?> <span class="required">*</span></label>
						<input type="email" id="newsletter_email" name="email" required>
					</div>

					<button type="submit" class="btn btn-full"><?php esc_html_e( 'S\'inscrire', 'cgt' ); ?></button>
				</form>
			</div>
		</div>
	</section>

</main>

<?php
get_footer();
