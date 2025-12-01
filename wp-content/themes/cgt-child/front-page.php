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
	'post_type'      => 'tracts',
	'posts_per_page' => 3,
);

if ( ! cgt_user_can_read_private() ) {
	$resource_args['meta_query'] = array(
		'relation' => 'OR',
		array(
			'key'     => 'cgt_visibilite',
			'value'   => array( 'prive', 'privé' ),
			'compare' => 'NOT IN',
		),
		array(
			'key'     => 'cgt_visibilite',
			'compare' => 'NOT EXISTS',
		),
	);
}

$latest_resources = new WP_Query( $resource_args );

$agenda_events = new WP_Query(
	array(
		'post_type'      => 'cgt_agenda',
		'post_status'    => 'publish',
		'posts_per_page' => 6,
		'orderby'        => 'meta_value',
		'order'          => 'ASC',
		'meta_key'       => 'cgt_event_date',
		'meta_type'      => 'DATETIME',
		'meta_query'     => array(
			array(
				'key'     => 'cgt_event_date',
				'value'   => gmdate( 'Y-m-d H:i:s' ),
				'compare' => '>=',
				'type'    => 'DATETIME',
			),
		),
	)
);

if ( ! $agenda_events->have_posts() ) {
	wp_reset_postdata();
	$agenda_events = new WP_Query(
		array(
			'post_type'      => 'cgt_agenda',
			'post_status'    => 'publish',
			'posts_per_page' => 6,
			'orderby'        => 'meta_value',
			'order'          => 'DESC',
			'meta_key'       => 'cgt_event_date',
			'meta_type'      => 'DATETIME',
		)
	);
}

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
$home_slider_image = cgt_child_get_media_url( '2025/11/slider2.jpeg' );
if ( empty( $home_slider_image ) ) {
	$home_slider_image = 'https://fsetud-cgt.fr/wp-content/uploads/2025/11/slider2.jpeg';
}
?>

<main id="primary" class="site-main">
	<section class="home-hero">
		<div class="container home-hero__inner">
			<div class="home-hero__content">
				<p class="home-hero__eyebrow"><?php esc_html_e( 'Fédération CGT des Sociétés d\'Études', 'cgt' ); ?></p>
				<h1 class="home-hero__title"><?php esc_html_e( 'Organisons la solidarité dans les bureaux d\'études, le conseil et l\'expertise et de la prévoyance', 'cgt' ); ?></h1>
				<p class="home-hero__lead"><?php esc_html_e( 'Actualités, analyses et outils pour les salarié·es des sociétés d\'études, d\'ingénierie, de conseil et d\'expertise.', 'cgt' ); ?></p>
				<div class="home-hero__actions">
					<a class="btn" href="<?php echo esc_url( home_url( '/contact' ) ); ?>"><?php esc_html_e( 'Rejoindre la CGT', 'cgt' ); ?></a>
					<?php
					$member_button_text = __( 'Espace adhérent', 'cgt' );
					if ( is_user_logged_in() ) {
						$current_user = wp_get_current_user();
						$first_name = get_user_meta( $current_user->ID, 'first_name', true );
						$last_name = get_user_meta( $current_user->ID, 'last_name', true );

						if ( ! empty( $first_name ) && ! empty( $last_name ) ) {
							$member_button_text = $first_name . ' ' . $last_name;
						} elseif ( ! empty( $first_name ) ) {
							$member_button_text = $first_name;
						} elseif ( ! empty( $last_name ) ) {
							$member_button_text = $last_name;
						} else {
							$member_button_text = $current_user->display_name;
						}
					}
					?>
					<a class="btn btn-light" href="<?php echo esc_url( home_url( '/espace-adherent' ) ); ?>"><?php echo esc_html( $member_button_text ); ?></a>
				</div>
				<ul class="home-hero__meta">
					<li><?php esc_html_e( 'Bureaux d’études | Conseil | Ingénierie | Expertise', 'cgt' ); ?></li>
					<li><?php esc_html_e( 'Soutien juridique, syndical et revendicatif au quotidien', 'cgt' ); ?></li>
					<li><?php esc_html_e( 'Mobilisations nationales et actions dans chaque branche', 'cgt' ); ?></li>
				</ul>
			</div>
			<div class="home-hero__media" aria-hidden="true">
			<img
				class="home-hero__image"
				src="<?php echo esc_url( $home_slider_image ); ?>"
					alt="<?php esc_attr_e( 'Visuel illustrant la mobilisation de la Fédération CGT des Sociétés d’Études', 'cgt' ); ?>"
					loading="lazy"
				>
			</div>
		</div>
	</section>

	<section class="home-nav">
		<div class="container">
			<ul class="home-nav__grid" aria-label="<?php esc_attr_e( 'Navigation rapide', 'cgt' ); ?>">
				<li>
					<button type="button" class="home-nav__card home-nav__card--agenda" data-open-agenda>
						<span class="home-nav__icon" aria-hidden="true">🗓️</span>
						<span>
							<strong><?php esc_html_e( 'Agenda', 'cgt' ); ?></strong>
							<small><?php esc_html_e( 'Événements syndicaux', 'cgt' ); ?></small>
						</span>
					</button>
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

	<div class="agenda-modal" id="agendaModal" aria-hidden="true" hidden>
		<div class="agenda-modal__overlay" data-close-agenda></div>
		<div class="agenda-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="agendaModalTitle">
			<button type="button" class="agenda-modal__close" data-close-agenda aria-label="<?php esc_attr_e( 'Fermer l’agenda', 'cgt' ); ?>">×</button>
			<header class="agenda-modal__header">
				<h2 id="agendaModalTitle"><?php esc_html_e( 'Agenda militant', 'cgt' ); ?></h2>
				<p><?php esc_html_e( 'Retrouvez les rendez-vous de la fédération et des branches.', 'cgt' ); ?></p>
			</header>
			<div class="agenda-modal__body">
				<?php if ( $agenda_events->have_posts() ) : ?>
					<ul class="agenda-modal__list">
						<?php
						while ( $agenda_events->have_posts() ) :
							$agenda_events->the_post();
							$raw_date   = get_post_meta( get_the_ID(), 'cgt_event_date', true );
							$timestamp  = $raw_date ? strtotime( $raw_date ) : false;
							$event_date = $timestamp ? wp_date( 'd F Y \à H\hi', $timestamp ) : '';
							$address    = get_post_meta( get_the_ID(), 'cgt_event_address', true );
							$document   = get_post_meta( get_the_ID(), 'cgt_event_document', true );
							$document_url = $document ? wp_get_attachment_url( $document ) : '';
							?>
							<li class="agenda-item">
								<div class="agenda-item__date">
									<span class="agenda-item__day"><?php echo esc_html( $timestamp ? wp_date( 'd', $timestamp ) : '--' ); ?></span>
									<span class="agenda-item__month"><?php echo esc_html( $timestamp ? wp_date( 'M', $timestamp ) : '--' ); ?></span>
								</div>
								<div class="agenda-item__content">
									<h3 class="agenda-item__title"><?php the_title(); ?></h3>
									<?php if ( $event_date ) : ?>
										<p class="agenda-item__meta"><?php echo esc_html( $event_date ); ?></p>
									<?php endif; ?>
									<?php if ( $address ) : ?>
										<p class="agenda-item__address"><?php echo esc_html( $address ); ?></p>
									<?php endif; ?>
									<div class="agenda-item__actions">
										<a class="btn btn-compact" href="<?php the_permalink(); ?>">
											<?php esc_html_e( 'Voir l’événement', 'cgt' ); ?>
										</a>
										<?php if ( $document_url ) : ?>
											<a class="btn btn-compact btn-light" href="<?php echo esc_url( $document_url ); ?>" target="_blank" rel="noopener">
												<?php esc_html_e( 'Télécharger le document', 'cgt' ); ?>
											</a>
										<?php endif; ?>
									</div>
								</div>
							</li>
							<?php
						endwhile;
						wp_reset_postdata();
						?>
					</ul>
				<?php else : ?>
					<p><?php esc_html_e( 'Aucun événement enregistré pour le moment. Revenez bientôt.', 'cgt' ); ?></p>
				<?php endif; ?>
			</div>
		</div>
	</div>

	<?php
$communiques_tabs = array(
	'tout'           => array(
		'label' => __( 'Tout', 'cgt' ),
		'terms' => null,
	),
	'actualites'     => array(
		'label' => __( 'Actualités', 'cgt' ),
		'terms' => array( 'actualites' ),
	),
	'bulletins'      => array(
		'label' => __( 'Bulletins', 'cgt' ),
		'terms' => array( 'bulletins' ),
	),
	'communications' => array(
		'label' => __( 'Communications', 'cgt' ),
		'terms' => array( 'communication-federale' ),
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
						'post_status'    => 'publish',
						'posts_per_page' => 4,
					);

					if ( ! empty( $tab['terms'] ) ) {
						$query_args['tax_query'] = array(
							array(
								'taxonomy' => 'category',
								'field'    => 'slug',
								'terms'    => (array) $tab['terms'],
							),
						);
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
				<div class="tracts-grid">
					<?php
					while ( $latest_resources->have_posts() ) :
						$latest_resources->the_post();
						if ( 'tracts' === get_post_type() ) {
							$visibility = get_post_meta( get_the_ID(), 'cgt_visibilite', true );
							$pdf_url    = get_post_meta( get_the_ID(), 'cgt_fichier_pdf', true );
							$is_private = 'prive' === $visibility;
							$can_access = ! $is_private || cgt_user_can_read_private();

							// Tronquer le titre
							$title = get_the_title();
							$title_limit = 70;
							if ( mb_strlen( $title ) > $title_limit ) {
								$title = mb_substr( $title, 0, $title_limit ) . '...';
							}
							?>
							<article <?php post_class( 'tract-card' ); ?>>
								<a href="<?php the_permalink(); ?>" class="tract-card__link" aria-label="<?php echo esc_attr( sprintf( __( 'Voir le tract : %s', 'cgt' ), get_the_title() ) ); ?>">
									<div class="tract-card__icon">
										<?php if ( $is_private ) : ?>
											<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
												<rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
												<path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
											</svg>
										<?php else : ?>
											<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
												<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
												<polyline points="14 2 14 8 20 8"></polyline>
												<line x1="16" y1="13" x2="8" y2="13"></line>
												<line x1="16" y1="17" x2="8" y2="17"></line>
												<polyline points="10 9 9 9 8 9"></polyline>
											</svg>
										<?php endif; ?>
									</div>

									<div class="tract-card__content">
										<div class="tract-card__header">
											<span class="tract-card__date">
												<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
													<rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
													<line x1="16" y1="2" x2="16" y2="6"></line>
													<line x1="8" y1="2" x2="8" y2="6"></line>
													<line x1="3" y1="10" x2="21" y2="10"></line>
												</svg>
												<?php echo esc_html( get_the_date() ); ?>
											</span>
											<?php if ( $is_private ) : ?>
												<span class="tract-card__badge tract-card__badge--private">
													<svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor">
														<rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
														<path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
													</svg>
													<?php esc_html_e( 'Adhérents', 'cgt' ); ?>
												</span>
											<?php endif; ?>
										</div>

										<h2 class="tract-card__title"><?php echo esc_html( $title ); ?></h2>

										<?php
										$branches = wp_get_post_terms( get_the_ID(), 'branche' );
										if ( ! empty( $branches ) && ! is_wp_error( $branches ) ) :
											?>
											<div class="tract-card__branches">
												<?php foreach ( array_slice( $branches, 0, 2 ) as $branch ) : ?>
													<span class="tract-card__branch-tag"><?php echo esc_html( cgt_get_branch_hierarchy( $branch ) ); ?></span>
												<?php endforeach; ?>
												<?php if ( count( $branches ) > 2 ) : ?>
													<span class="tract-card__branch-tag tract-card__branch-tag--more">+<?php echo esc_html( count( $branches ) - 2 ); ?></span>
												<?php endif; ?>
											</div>
										<?php endif; ?>
									</div>
								</a>

								<div class="tract-card__footer">
									<?php if ( $pdf_url && $can_access ) : ?>
										<a class="tract-card__download" href="<?php echo esc_url( $pdf_url ); ?>" download>
											<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
												<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
												<polyline points="7 10 12 15 17 10"></polyline>
												<line x1="12" y1="15" x2="12" y2="3"></line>
											</svg>
											<?php esc_html_e( 'Télécharger le PDF', 'cgt' ); ?>
										</a>
									<?php elseif ( $is_private && ! cgt_user_can_read_private() ) : ?>
										<div class="tract-card__locked">
											<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
												<rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
												<path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
											</svg>
											<span><?php esc_html_e( 'Réservé aux adhérents', 'cgt' ); ?></span>
											<a href="<?php echo esc_url( home_url( '/connexion' ) ); ?>" class="tract-card__login-link"><?php esc_html_e( 'Se connecter', 'cgt' ); ?></a>
										</div>
									<?php endif; ?>
								</div>
							</article>
							<?php
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
			<div class="home-cta__aside">
				<figure class="home-cta__media">
					<img
						src="<?php echo esc_url( 'https://fsetud-cgt.fr/wp-content/uploads/2025/10/adherent-photo.png' ); ?>"
						alt="<?php esc_attr_e( 'Adhérents de la fédération réunis', 'cgt' ); ?>"
						loading="lazy"
					>
				</figure>
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
							<p><?php esc_html_e( 'Adhérez à la CGT et renforcez la voix collective de la FSETUD dans chaque entreprise.', 'cgt' ); ?></p>
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
					$slider_links  = get_option( 'cgt_home_slider_links', array() );

					if ( ! empty( $slider_images ) && is_array( $slider_images ) ) :
						?>
						<div class="home-slider">
							<div class="home-slider__track">
								<?php foreach ( $slider_images as $index => $image_id ) : ?>
									<?php if ( $image_id ) : ?>
										<div class="home-slider__slide <?php echo 0 === $index ? 'is-active' : ''; ?>">
											<?php
											$link_url = isset( $slider_links[ $index ] ) ? $slider_links[ $index ] : '';

											if ( ! empty( $link_url ) ) {
												echo '<a href="' . esc_url( $link_url ) . '" target="_blank" rel="noopener noreferrer" class="home-slider__link">';
											}

											echo wp_get_attachment_image(
												$image_id,
												'medium_large',
												false,
												array(
													'class'   => 'home-slider__image',
													'loading' => 0 === $index ? 'eager' : 'lazy', // Lazy load sauf première image
												)
											);

											if ( ! empty( $link_url ) ) {
												echo '</a>';
											}
											?>
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

				<?php
				$newsletter_regions = array(
					'Auvergne-Rhône-Alpes',
					'Bourgogne-Franche-Comté',
					'Bretagne',
					'Centre-Val de Loire',
					'Corse',
					'Grand Est',
					'Hauts-de-France',
					'Île-de-France',
					'Normandie',
					'Nouvelle-Aquitaine',
					'Occitanie',
					'Pays de la Loire',
					'Provence-Alpes-Côte d\'Azur',
					'Guadeloupe',
					'Guyane',
					'Martinique',
					'La Réunion',
					'Mayotte',
				);

				$newsletter_branches = get_terms(
					array(
						'taxonomy'   => 'branche',
						'hide_empty' => false,
						'orderby'    => 'name',
						'order'      => 'ASC',
					)
				);
				?>

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

					<div class="newsletter-form__field">
						<label for="newsletter_region"><?php esc_html_e( 'Région', 'cgt' ); ?> <span class="required">*</span></label>
						<select id="newsletter_region" name="region" required>
							<option value=""><?php esc_html_e( 'Choisissez votre région', 'cgt' ); ?></option>
							<?php foreach ( $newsletter_regions as $region_label ) : ?>
								<option value="<?php echo esc_attr( $region_label ); ?>"><?php echo esc_html( $region_label ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>

					<?php if ( ! is_wp_error( $newsletter_branches ) && ! empty( $newsletter_branches ) ) : ?>
						<div class="newsletter-form__field">
							<label for="newsletter_branche"><?php esc_html_e( 'Branche', 'cgt' ); ?> <span class="required">*</span></label>
							<select id="newsletter_branche" name="branche" required>
								<option value=""><?php esc_html_e( 'Choisissez votre branche', 'cgt' ); ?></option>
								<?php foreach ( $newsletter_branches as $branch_term ) : ?>
									<option value="<?php echo esc_attr( $branch_term->slug ); ?>"><?php echo esc_html( $branch_term->name ); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
					<?php endif; ?>

					<div class="newsletter-form__field newsletter-form__field--checkbox">
						<label class="newsletter-form__checkbox-label">
							<input type="checkbox" name="consent" id="newsletter_consent" required>
							<span>
								<?php
								printf(
									/* translators: %s: Link to privacy policy */
									esc_html__( 'J\'accepte que mes données soient utilisées pour recevoir la newsletter. Voir notre %s.', 'cgt' ),
									'<a href="' . esc_url( home_url( '/politique-de-confidentialite' ) ) . '" target="_blank" rel="noopener">' . esc_html__( 'politique de confidentialité', 'cgt' ) . '</a>'
								);
								?>
								<span class="required">*</span>
							</span>
						</label>
					</div>

					<button type="submit" class="btn btn-full"><?php esc_html_e( 'S\'inscrire', 'cgt' ); ?></button>
				</form>
			</div>
		</div>
	</section>

</main>

<?php
get_footer();
