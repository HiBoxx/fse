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
					<a href="<?php echo esc_url( get_post_type_archive_link( 'communiques_de_presse' ) ); ?>" class="home-nav__card">
						<span class="home-nav__icon" aria-hidden="true">🗞️</span>
						<span>
							<strong><?php esc_html_e( 'Communiqués', 'cgt' ); ?></strong>
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
					<a href="<?php echo esc_url( home_url( '/espace-presse' ) ); ?>" class="home-nav__card">
						<span class="home-nav__icon" aria-hidden="true">🎙️</span>
						<span>
							<strong><?php esc_html_e( 'Espace presse', 'cgt' ); ?></strong>
							<small><?php esc_html_e( 'Ressources presse', 'cgt' ); ?></small>
						</span>
					</a>
				</li>
			</ul>
		</div>
	</section>

	<?php
	$communiques_tabs = array(
		'actualites'         => array(
			'label' => __( 'Actualités', 'cgt' ),
			'term'  => 'communication',
		),
		'bulletins'          => array(
			'label' => __( 'Bulletins', 'cgt' ),
			'term'  => 'bulletins',
		),
		'tract-entreprise'   => array(
			'label' => __( 'Tracts d’entreprise', 'cgt' ),
			'term'  => 'tract-entreprise',
		),
		'presse'             => array(
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
						'post_type'      => 'communiques_de_presse',
						'posts_per_page' => 4,
						'tax_query'      => array(
							array(
								'taxonomy' => 'thematique',
								'field'    => 'slug',
								'terms'    => $tab['term'],
							),
						),
					);

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
						<p><?php esc_html_e( 'Aucun communiqué pour cette catégorie pour le moment.', 'cgt' ); ?></p>
					<?php endif; ?>
					<?php wp_reset_postdata(); ?>
				</div>
					<?php
					$first_panel = false;
				endforeach;
				?>
			</div>

			<p class="home-section__footer">
				<a class="btn btn-compact" href="<?php echo esc_url( get_post_type_archive_link( 'communiques_de_presse' ) ); ?>">
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

	<section class="home-section home-section--publish">
		<div class="container">
			<header class="home-section__header">
				<h2><?php esc_html_e( 'Publiez vos articles', 'cgt' ); ?></h2>
				<p><?php esc_html_e( 'Partagez vos bulletins, tracts ou communications avec la fédération en quelques clics.', 'cgt' ); ?></p>
			</header>
			<div class="home-submit">
				<p><?php esc_html_e( 'Envoyez-nous vos contenus via le formulaire dédié. Ils seront vérifiés puis publiés par l’équipe fédérale.', 'cgt' ); ?></p>
				<a class="btn" href="<?php echo esc_url( home_url( '/publier-article' ) ); ?>"><?php esc_html_e( 'Proposer un article', 'cgt' ); ?></a>
			</div>
		</div>
	</section>

	<section class="home-section home-section--shade">
		<div class="container">
			<header class="home-section__header">
				<h2><?php esc_html_e( 'Contact presse & médias', 'cgt' ); ?></h2>
				<p><?php esc_html_e( 'Nous répondons rapidement aux demandes d’interview et fournissons des dossiers prêts à l’emploi.', 'cgt' ); ?></p>
			</header>

			<div class="home-contact">
				<div>
					<h3><?php esc_html_e( 'Besoin d’un contact direct ?', 'cgt' ); ?></h3>
					<p><?php esc_html_e( 'Écrivez-nous pour organiser une interview, vérifier une information ou obtenir des éléments chiffrés.', 'cgt' ); ?></p>
					<a class="btn" href="mailto:contact@example.com"><?php esc_html_e( 'Contacter la cellule presse', 'cgt' ); ?></a>
				</div>
				<div>
					<h3><?php esc_html_e( 'Ressources en libre accès', 'cgt' ); ?></h3>
					<ul class="home-contact__links">
						<li><a href="<?php echo esc_url( home_url( '/espace-presse' ) ); ?>"><?php esc_html_e( 'Espace Presse', 'cgt' ); ?></a></li>
						<li><a href="<?php echo esc_url( get_post_type_archive_link( 'communiques_de_presse' ) ); ?>"><?php esc_html_e( 'Communiqués', 'cgt' ); ?></a></li>
						<li><a href="<?php echo esc_url( get_post_type_archive_link( 'dossiers_de_presse' ) ); ?>"><?php esc_html_e( 'Dossiers de presse', 'cgt' ); ?></a></li>
					</ul>
				</div>
			</div>
		</div>
	</section>

</main>

<?php
get_footer();
