<?php
/**
 * Template Name: Carnet d'adresse
 * Description: Page affichant tous les experts avec filtres par expertise
 *
 * @package CGT_Child
 */

get_header();

// Enqueue styles et scripts
wp_enqueue_style( 'cgt-carnet-adresse', get_stylesheet_directory_uri() . '/assets/css/carnet-adresse.css', array(), '1.0' );
wp_enqueue_script( 'cgt-carnet-adresse', get_stylesheet_directory_uri() . '/assets/js/carnet-adresse.js', array( 'jquery' ), '1.0', true );

// Récupérer toutes les expertises
$expertises = get_terms(
	array(
		'taxonomy'   => 'expertise',
		'hide_empty' => false,
		'orderby'    => 'name',
		'order'      => 'ASC',
	)
);

// Récupérer tous les experts
$args = array(
	'post_type'      => 'cgt_expert',
	'posts_per_page' => -1,
	'orderby'        => 'title',
	'order'          => 'ASC',
	'post_status'    => 'publish',
);

$experts_query = new WP_Query( $args );
?>

<main id="primary" class="site-main carnet-adresse-page">
	<!-- Hero Header -->
	<div class="carnet-hero">
		<div class="container">
			<div class="carnet-hero__content">
				<div class="carnet-hero__badge">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
						<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
						<circle cx="12" cy="7" r="4"></circle>
					</svg>
					<span><?php esc_html_e( 'Ressources', 'cgt' ); ?></span>
				</div>
				<h1 class="carnet-hero__title"><?php esc_html_e( 'Carnet d\'adresse', 'cgt' ); ?></h1>
				<p class="carnet-hero__description">
					<?php esc_html_e( 'Retrouvez nos experts et professionnels de confiance pour vous accompagner dans vos démarches juridiques, comptables et administratives.', 'cgt' ); ?>
				</p>
			</div>
		</div>
	</div>

	<div class="container carnet-container">
		<!-- Filtres -->
		<?php if ( ! empty( $expertises ) && ! is_wp_error( $expertises ) ) : ?>
			<div class="carnet-filters">
				<div class="carnet-filters__header">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
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
					<span><?php esc_html_e( 'Filtrer par expertise', 'cgt' ); ?></span>
				</div>
				<div class="carnet-filters__buttons">
					<button class="filter-btn active" data-filter="all">
						<?php esc_html_e( 'Tous', 'cgt' ); ?>
						<span class="filter-count"><?php echo esc_html( $experts_query->found_posts ); ?></span>
					</button>
					<?php foreach ( $expertises as $expertise ) : ?>
						<?php
						$count_args = array(
							'post_type'      => 'cgt_expert',
							'posts_per_page' => -1,
							'post_status'    => 'publish',
							'tax_query'      => array(
								array(
									'taxonomy' => 'expertise',
									'field'    => 'term_id',
									'terms'    => $expertise->term_id,
								),
							),
						);
						$count_query = new WP_Query( $count_args );
						?>
						<button class="filter-btn" data-filter="<?php echo esc_attr( $expertise->slug ); ?>">
							<?php echo esc_html( $expertise->name ); ?>
							<span class="filter-count"><?php echo esc_html( $count_query->found_posts ); ?></span>
						</button>
						<?php wp_reset_postdata(); ?>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endif; ?>

		<!-- Grille des experts -->
		<?php if ( $experts_query->have_posts() ) : ?>
			<div class="experts-grid">
				<?php while ( $experts_query->have_posts() ) : $experts_query->the_post(); ?>
					<?php
					$post_id     = get_the_ID();
					$prenom      = get_post_meta( $post_id, '_expert_prenom', true );
					$telephone   = get_post_meta( $post_id, '_expert_telephone', true );
					$email       = get_post_meta( $post_id, '_expert_email', true );
					$adresse     = get_post_meta( $post_id, '_expert_adresse', true );
					$description = get_post_meta( $post_id, '_expert_description', true );
					$terms       = get_the_terms( $post_id, 'expertise' );
					$expertise_slugs = array();
					$expertise_names = array();
					if ( $terms && ! is_wp_error( $terms ) ) {
						foreach ( $terms as $term ) {
							$expertise_slugs[] = $term->slug;
							$expertise_names[] = $term->name;
						}
					}
					$data_filter = implode( ' ', $expertise_slugs );
					?>
					<div class="expert-card" data-expertise="<?php echo esc_attr( $data_filter ); ?>">
						<div class="expert-card__header">
							<div class="expert-card__photo">
								<?php if ( has_post_thumbnail() ) : ?>
									<?php the_post_thumbnail( 'medium', array( 'class' => 'expert-photo' ) ); ?>
								<?php else : ?>
									<div class="expert-photo-placeholder">
										<svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
											<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
											<circle cx="12" cy="7" r="4"></circle>
										</svg>
									</div>
								<?php endif; ?>
							</div>
							<div class="expert-card__info">
								<h3 class="expert-card__name">
									<?php echo esc_html( $prenom . ' ' . get_the_title() ); ?>
								</h3>
								<?php if ( ! empty( $expertise_names ) ) : ?>
									<div class="expert-card__expertise">
										<?php foreach ( $expertise_names as $name ) : ?>
											<span class="expertise-badge"><?php echo esc_html( $name ); ?></span>
										<?php endforeach; ?>
									</div>
								<?php endif; ?>
							</div>
						</div>

						<?php if ( $description ) : ?>
							<div class="expert-card__description">
								<?php echo esc_html( wp_trim_words( $description, 25, '...' ) ); ?>
							</div>
						<?php endif; ?>

						<div class="expert-card__contact">
							<?php if ( $telephone ) : ?>
								<div class="contact-item">
									<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
										<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
									</svg>
									<a href="tel:<?php echo esc_attr( str_replace( ' ', '', $telephone ) ); ?>"><?php echo esc_html( $telephone ); ?></a>
								</div>
							<?php endif; ?>

							<?php if ( $email ) : ?>
								<div class="contact-item">
									<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
										<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
										<polyline points="22,6 12,13 2,6"></polyline>
									</svg>
									<a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a>
								</div>
							<?php endif; ?>

							<?php if ( $adresse ) : ?>
								<div class="contact-item">
									<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
										<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
										<circle cx="12" cy="10" r="3"></circle>
									</svg>
									<span><?php echo esc_html( $adresse ); ?></span>
								</div>
							<?php endif; ?>
						</div>
					</div>
				<?php endwhile; ?>
			</div>

			<div class="experts-empty" style="display: none;">
				<div class="experts-empty__icon">
					<svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
						<circle cx="12" cy="12" r="10"></circle>
						<line x1="12" y1="8" x2="12" y2="12"></line>
						<line x1="12" y1="16" x2="12.01" y2="16"></line>
					</svg>
				</div>
				<h3><?php esc_html_e( 'Aucun expert trouvé', 'cgt' ); ?></h3>
				<p><?php esc_html_e( 'Essayez de sélectionner un autre filtre.', 'cgt' ); ?></p>
			</div>
		<?php else : ?>
			<div class="experts-empty">
				<div class="experts-empty__icon">
					<svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
						<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
						<circle cx="12" cy="7" r="4"></circle>
					</svg>
				</div>
				<h3><?php esc_html_e( 'Aucun expert pour le moment', 'cgt' ); ?></h3>
				<p><?php esc_html_e( 'Les experts seront ajoutés prochainement.', 'cgt' ); ?></p>
			</div>
		<?php endif; ?>

		<?php wp_reset_postdata(); ?>
	</div>
</main>

<?php
get_footer();
