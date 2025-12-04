<?php
/**
 * Archive template for enquetes
 *
 * @package CGT_Child
 */

get_header();

// Enqueue styles
wp_enqueue_style( 'cgt-enquete', get_stylesheet_directory_uri() . '/assets/css/enquete.css', array(), '1.0' );
?>

<main id="primary" class="site-main enquetes-archive">
	<!-- Hero Section -->
	<div class="enquete-hero">
		<div class="container">
			<div class="enquete-hero__content">
				<div class="enquete-hero__badge">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
						<path d="M9 11H3v2h6m1 0a1 1 0 001 1h2a1 1 0 001-1m-4 0V9a1 1 0 011-1h2a1 1 0 011 1v2m0 0h6v2h-6"></path>
						<path d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
					</svg>
					<span><?php esc_html_e( 'Enquêtes', 'cgt' ); ?></span>
				</div>
				<h1 class="enquete-hero__title"><?php esc_html_e( 'Participez à nos enquêtes', 'cgt' ); ?></h1>
				<p class="enquete-hero__description">
					<?php esc_html_e( 'Votre opinion compte ! Participez aux enquêtes de la CGT et contribuez à faire entendre la voix des travailleur·ses.', 'cgt' ); ?>
				</p>
			</div>
		</div>
	</div>

	<div class="container enquete-container">
		<?php if ( have_posts() ) : ?>
			<div class="enquetes-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 2rem; margin-bottom: 3rem;">
				<?php while ( have_posts() ) : the_post(); ?>
					<?php
					$post_id = get_the_ID();
					$questions = get_post_meta( $post_id, '_enquete_questions', true );
					$results = get_post_meta( $post_id, '_enquete_results', true );
					$actif = get_post_meta( $post_id, '_enquete_active', true );
					$date_fin = get_post_meta( $post_id, '_enquete_date_fin', true );

					// Calculate total votes
					$total_votes = 0;
					if ( is_array( $results ) ) {
						foreach ( $results as $question_results ) {
							if ( is_array( $question_results ) ) {
								$total_votes += array_sum( $question_results );
							}
						}
					}

					// Check if active
					$is_active = ( $actif === '1' );
					if ( $date_fin && strtotime( $date_fin ) < time() ) {
						$is_active = false;
					}

					// Count questions
					$num_questions = is_array( $questions ) ? count( $questions ) : 0;
					?>

					<article class="enquete-card" style="background: white; border-radius: 0.75rem; padding: 2rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); transition: all 200ms; display: flex; flex-direction: column;">
						<div style="flex: 1;">
							<!-- Status Badge -->
							<div style="margin-bottom: 1rem;">
								<?php if ( $is_active ) : ?>
									<span style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.25rem 0.75rem; background: rgba(34, 197, 94, 0.1); color: rgb(22, 163, 74); border-radius: 1rem; font-size: 0.75rem; font-weight: 600;">
										<span style="display: block; width: 6px; height: 6px; background: currentColor; border-radius: 50%;"></span>
										<?php esc_html_e( 'En cours', 'cgt' ); ?>
									</span>
								<?php else : ?>
									<span style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.25rem 0.75rem; background: rgba(156, 163, 175, 0.1); color: rgb(107, 114, 128); border-radius: 1rem; font-size: 0.75rem; font-weight: 600;">
										<?php esc_html_e( 'Terminée', 'cgt' ); ?>
									</span>
								<?php endif; ?>
							</div>

							<!-- Title -->
							<h2 style="font-size: 1.5rem; font-weight: 700; color: #111827; margin: 0 0 1rem; line-height: 1.3;">
								<a href="<?php the_permalink(); ?>" style="color: inherit; text-decoration: none;">
									<?php the_title(); ?>
								</a>
							</h2>

							<!-- Excerpt -->
							<?php if ( has_excerpt() ) : ?>
								<div style="color: #6b7280; font-size: 0.875rem; line-height: 1.6; margin-bottom: 1.5rem;">
									<?php the_excerpt(); ?>
								</div>
							<?php endif; ?>

							<!-- Meta Info -->
							<div style="display: flex; flex-wrap: wrap; gap: 1rem; padding: 1rem 0; border-top: 1px solid #f3f4f6; border-bottom: 1px solid #f3f4f6; margin-bottom: 1.5rem;">
								<div style="display: flex; align-items: center; gap: 0.5rem; color: #6b7280; font-size: 0.875rem;">
									<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
										<path d="M9 11H3v2h6m1 0a1 1 0 001 1h2a1 1 0 001-1m-4 0V9a1 1 0 011-1h2a1 1 0 011 1v2m0 0h6v2h-6"></path>
									</svg>
									<span><?php echo esc_html( sprintf( _n( '%d question', '%d questions', $num_questions, 'cgt' ), $num_questions ) ); ?></span>
								</div>

								<div style="display: flex; align-items: center; gap: 0.5rem; color: #6b7280; font-size: 0.875rem;">
									<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
										<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
										<circle cx="9" cy="7" r="4"></circle>
										<path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
										<path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
									</svg>
									<span><?php echo esc_html( sprintf( _n( '%d vote', '%d votes', $total_votes, 'cgt' ), $total_votes ) ); ?></span>
								</div>

								<?php if ( $date_fin && $is_active ) : ?>
									<div style="display: flex; align-items: center; gap: 0.5rem; color: #6b7280; font-size: 0.875rem;">
										<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
											<circle cx="12" cy="12" r="10"></circle>
											<polyline points="12 6 12 12 16 14"></polyline>
										</svg>
										<span><?php echo esc_html( sprintf( __( 'Jusqu\'au %s', 'cgt' ), date_i18n( 'd/m/Y', strtotime( $date_fin ) ) ) ); ?></span>
									</div>
								<?php endif; ?>
							</div>
						</div>

						<!-- CTA Button -->
						<div>
							<a href="<?php the_permalink(); ?>" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1.5rem; background: #e30613; color: white; text-decoration: none; border-radius: 0.5rem; font-weight: 600; font-size: 0.875rem; transition: all 200ms;">
								<?php
								if ( $is_active ) {
									esc_html_e( 'Participer', 'cgt' );
								} else {
									esc_html_e( 'Voir les résultats', 'cgt' );
								}
								?>
								<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
									<line x1="5" y1="12" x2="19" y2="12"></line>
									<polyline points="12 5 19 12 12 19"></polyline>
								</svg>
							</a>
						</div>
					</article>

				<?php endwhile; ?>
			</div>

			<?php
			// Pagination
			the_posts_pagination(
				array(
					'mid_size'  => 2,
					'prev_text' => __( '← Précédent', 'cgt' ),
					'next_text' => __( 'Suivant →', 'cgt' ),
				)
			);
			?>

		<?php else : ?>
			<div class="enquete-empty">
				<svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
					<circle cx="12" cy="12" r="10"></circle>
					<line x1="12" y1="8" x2="12" y2="12"></line>
					<line x1="12" y1="16" x2="12.01" y2="16"></line>
				</svg>
				<p><?php esc_html_e( 'Aucune enquête disponible pour le moment.', 'cgt' ); ?></p>
			</div>
		<?php endif; ?>
	</div>
</main>

<style>
	.enquete-card:hover {
		box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
		transform: translateY(-4px);
	}

	.enquete-card a[style*="background: #e30613"]:hover {
		background: #b00510 !important;
	}

	@media (max-width: 768px) {
		.enquetes-grid {
			grid-template-columns: 1fr !important;
		}

		.enquete-card {
			padding: 1.5rem !important;
		}
	}
</style>

<?php
get_footer();
