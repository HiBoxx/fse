<?php
/**
 * Archive template for tracts - Modern Design.
 *
 * @package CGT_Child
 */

get_header();
?>
<main id="primary" class="site-main tracts-archive-page">
	<!-- Hero Header -->
	<div class="tracts-hero">
		<div class="container">
			<div class="tracts-hero__content">
				<div class="tracts-hero__badge">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
						<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
						<polyline points="14 2 14 8 20 8"></polyline>
						<line x1="16" y1="13" x2="8" y2="13"></line>
						<line x1="16" y1="17" x2="8" y2="17"></line>
						<polyline points="10 9 9 9 8 9"></polyline>
					</svg>
					<span><?php esc_html_e( 'Ressources CGT', 'cgt' ); ?></span>
				</div>
				<h1 class="tracts-hero__title"><?php post_type_archive_title(); ?></h1>
				<p class="tracts-hero__description"><?php esc_html_e( 'Téléchargez les derniers tracts publiés par la CGT. Documents prêts à imprimer et à diffuser dans votre entreprise.', 'cgt' ); ?></p>
			</div>
		</div>
	</div>

	<div class="container tracts-container">
<?php
$current_branch = get_query_var( 'branche' );

$branch_groups = array(
	__( 'Bureaux d\'études techniques et de conseil', 'cgt' ) => array(
		'associations-agreees-qualite-air'          => __( 'Associations agréées de surveillance de la qualité de l\'air', 'cgt' ),
		'conseil-architecture-urbanisme-environnement' => __( 'Conseil d\'Architecture d\'Urbanisme et d\'Environnement', 'cgt' ),
		'etudes-conseil'                             => __( 'Études et conseil', 'cgt' ),
		'foires-salons'                              => __( 'Foires et salons', 'cgt' ),
		'formation-professionnelle'                  => __( 'Formation professionnelle', 'cgt' ),
		'informatique'                               => __( 'Informatique', 'cgt' ),
		'ingenierie'                                 => __( 'Ingénierie', 'cgt' ),
		'organismes-controle-prevention'            => __( 'Organismes de Contrôle et de Prévention', 'cgt' ),
		'sondage'                                    => __( 'Sondage', 'cgt' ),
	),
	__( 'Experts comptables et commissaires aux comptes', 'cgt' ) => array(
		'associations-gestion-comptable' => __( 'Associations de gestion comptable', 'cgt' ),
	),
	__( 'Officines judiciaires et parajudiciaires', 'cgt' ) => array(
		'avocats'                               => __( 'Avocats', 'cgt' ),
		'avocats-salaries'                      => __( 'Avocats salariés', 'cgt' ),
		'salaries-cabinets-avocats'             => __( 'Salariés des cabinets d\'avocats', 'cgt' ),
		'avoues'                                => __( 'Avoués', 'cgt' ),
		'commissaires-justice'                  => __( 'Commissaires de justice', 'cgt' ),
		'commissaires-priseurs'                 => __( 'Commissaires priseurs', 'cgt' ),
		'huissiers-justice'                     => __( 'Huissiers de justice', 'cgt' ),
		'notariat'                              => __( 'Notariat', 'cgt' ),
		'professions-reglementees-juridictions' => __( 'Professions réglementées auprès des juridictions', 'cgt' ),
		'administrateurs-mandataires-judiciaires' => __( 'Administrateurs et mandataires judiciaires', 'cgt' ),
		'avocats-cour-cassation-conseil-etat'   => __( 'Avocats à la Cour de Cassation et au Conseil d\'État', 'cgt' ),
		'greffes-tribunaux-commerce'            => __( 'Greffes des tribunaux de commerce', 'cgt' ),
	),
	__( 'Prestataires de services', 'cgt' ) => array(
		'accueil-entreprise'        => __( 'Accueil en entreprise', 'cgt' ),
		'animations-commerciales'    => __( 'Animations commerciales', 'cgt' ),
		'centres-appels'            => __( 'Centres d\'appels', 'cgt' ),
		'enquete-civile'            => __( 'Enquête civile', 'cgt' ),
		'location-bureaux-salles'   => __( 'Location de bureaux et de salles', 'cgt' ),
		'recouvrement-creances'     => __( 'Recouvrement de créances', 'cgt' ),
		'tele-secretariat'          => __( 'Télé-secrétariat', 'cgt' ),
		'traduction'                => __( 'Traduction', 'cgt' ),
	),
);

$branch_singles = array(
	'cooperative-activite-emploi'              => __( 'Coopérative d\'activité et d\'emploi', 'cgt' ),
	'expertises-evaluations-industrie-commerce' => __( 'Expertises en matière d\'évaluations industrielles et commerciales', 'cgt' ),
	'experts-automobiles'                       => __( 'Experts automobiles', 'cgt' ),
	'organismes-developpement-economique'      => __( 'Organismes de développement économique', 'cgt' ),
	'portage-salarial'                         => __( 'Portage salarial', 'cgt' ),
	'snaii'                                    => __( 'Syndicat National des Auteurs d\'Invention Indépendants (SNAII)', 'cgt' ),
);
?>

		<!-- Filters Section -->
		<div class="tracts-filters-wrapper">
			<div class="tracts-filters-header">
				<h2><?php esc_html_e( 'Filtrer par branche', 'cgt' ); ?></h2>
				<p><?php esc_html_e( 'Trouvez les tracts spécifiques à votre secteur d\'activité', 'cgt' ); ?></p>
			</div>

			<form class="tracts-filters" method="get">
				<div class="filter-group">
					<label class="filter-label" for="tracts-branch-select">
						<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
							<path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
						</svg>
						<?php esc_html_e( 'Sélectionner une branche', 'cgt' ); ?>
					</label>
					<select id="tracts-branch-select" name="branche" class="filter-select">
						<option value=""><?php esc_html_e( 'Toutes les branches', 'cgt' ); ?></option>
						<?php foreach ( $branch_groups as $group_label => $group_options ) : ?>
							<optgroup label="<?php echo esc_attr( $group_label ); ?>">
								<?php foreach ( $group_options as $slug => $label ) : ?>
									<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $current_branch, $slug ); ?>><?php echo esc_html( $label ); ?></option>
								<?php endforeach; ?>
							</optgroup>
						<?php endforeach; ?>
						<?php foreach ( $branch_singles as $slug => $label ) : ?>
							<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $current_branch, $slug ); ?>><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<button class="btn filter-submit" type="submit">
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
						<line x1="4" y1="21" x2="4" y2="14"></line>
						<line x1="4" y1="10" x2="4" y2="3"></line>
						<line x1="12" y1="21" x2="12" y2="12"></line>
						<line x1="12" y1="8" x2="12" y2="3"></line>
						<line x1="20" y1="21" x2="20" y2="16"></line>
						<line x1="20" y1="12" x2="20" y2="3"></line>
						<line x1="1" y1="14" x2="7" y2="14"></line>
						<line x1="9" y1="8" x2="15" y2="8"></line>
						<line x1="17" y1="16" x2="23" y2="16"></line>
					</svg>
					<?php esc_html_e( 'Filtrer', 'cgt' ); ?>
				</button>
				<?php if ( $current_branch ) : ?>
					<a href="<?php echo esc_url( get_post_type_archive_link( 'tracts' ) ); ?>" class="btn btn-secondary filter-reset">
						<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
							<line x1="18" y1="6" x2="6" y2="18"></line>
							<line x1="6" y1="6" x2="18" y2="18"></line>
						</svg>
						<?php esc_html_e( 'Réinitialiser', 'cgt' ); ?>
					</a>
				<?php endif; ?>
			</form>
		</div>

		<!-- Tracts Grid -->
		<?php if ( have_posts() ) : ?>
			<div class="tracts-grid">
				<?php
				while ( have_posts() ) :
					the_post();
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
											<span class="tract-card__branch-tag"><?php echo esc_html( $branch->name ); ?></span>
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
				<?php endwhile; ?>
			</div>

			<div class="tracts-pagination">
				<?php
				the_posts_pagination(
					array(
						'mid_size'  => 2,
						'prev_text' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"></polyline></svg> ' . __( 'Précédent', 'cgt' ),
						'next_text' => __( 'Suivant', 'cgt' ) . ' <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>',
					)
				);
				?>
			</div>
		<?php else : ?>
			<div class="tracts-empty">
				<div class="tracts-empty__icon">
					<svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
						<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
						<polyline points="14 2 14 8 20 8"></polyline>
					</svg>
				</div>
				<h3><?php esc_html_e( 'Aucun tract disponible', 'cgt' ); ?></h3>
				<p><?php esc_html_e( 'Aucun tract ne correspond à votre recherche. Essayez de modifier vos filtres.', 'cgt' ); ?></p>
				<?php if ( $current_branch ) : ?>
					<a href="<?php echo esc_url( get_post_type_archive_link( 'tracts' ) ); ?>" class="btn">
						<?php esc_html_e( 'Voir tous les tracts', 'cgt' ); ?>
					</a>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
</main>
<?php
get_footer();
