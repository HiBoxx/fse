<?php
/**
 * Template for single enquete
 *
 * @package CGT_Child
 */

get_header();

// Enqueue styles and scripts
wp_enqueue_style( 'cgt-enquete', get_stylesheet_directory_uri() . '/assets/css/enquete.css', array(), '1.0' );
wp_enqueue_script( 'cgt-enquete', get_stylesheet_directory_uri() . '/assets/js/enquete.js', array( 'jquery' ), '1.0', true );

while ( have_posts() ) :
	the_post();

	$post_id = get_the_ID();
	$questions = get_post_meta( $post_id, '_enquete_questions', true );
	$results = get_post_meta( $post_id, '_enquete_results', true );
	$actif = get_post_meta( $post_id, '_enquete_active', true );
	$date_fin = get_post_meta( $post_id, '_enquete_date_fin', true );
	$multiple_votes = get_post_meta( $post_id, '_enquete_multiple_votes', true );

	if ( ! is_array( $questions ) ) {
		$questions = array();
	}

	if ( ! is_array( $results ) ) {
		$results = array();
	}

	// Check if enquete is active
	$is_active = ( $actif === '1' );
	if ( $date_fin && strtotime( $date_fin ) < time() ) {
		$is_active = false;
	}

	// Check if user has already voted
	$user_has_voted = false;
	if ( ! $multiple_votes || $multiple_votes !== '1' ) {
		$vote_cookie_name = 'cgt_enquete_' . $post_id . '_voted';
		$user_has_voted = isset( $_COOKIE[ $vote_cookie_name ] );
	}

	// Localize script data
	wp_localize_script(
		'cgt-enquete',
		'cgtEnquete',
		array(
			'ajaxurl'     => admin_url( 'admin-ajax.php' ),
			'post_id'     => $post_id,
			'nonce'       => wp_create_nonce( 'cgt_enquete_vote_' . $post_id ),
			'has_voted'   => $user_has_voted,
			'is_active'   => $is_active,
			'strings'     => array(
				'vote_success' => __( 'Merci pour votre participation !', 'cgt' ),
				'vote_error'   => __( 'Une erreur est survenue. Veuillez réessayer.', 'cgt' ),
				'already_voted' => __( 'Vous avez déjà voté pour cette enquête.', 'cgt' ),
			),
		)
	);
	?>

	<main id="primary" class="site-main enquete-page">
		<!-- Hero Section -->
		<div class="enquete-hero">
			<div class="container">
				<div class="enquete-hero__content">
					<div class="enquete-hero__badge">
						<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
							<path d="M9 11H3v2h6m1 0a1 1 0 001 1h2a1 1 0 001-1m-4 0V9a1 1 0 011-1h2a1 1 0 011 1v2m0 0h6v2h-6"></path>
							<path d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
						</svg>
						<span><?php esc_html_e( 'Enquête', 'cgt' ); ?></span>
					</div>
					<h1 class="enquete-hero__title"><?php the_title(); ?></h1>
					<?php if ( get_the_content() ) : ?>
						<div class="enquete-hero__description">
							<?php the_content(); ?>
						</div>
					<?php endif; ?>

					<?php if ( ! $is_active ) : ?>
						<div class="enquete-alert enquete-alert--warning">
							<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
								<path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"></path>
								<line x1="12" y1="9" x2="12" y2="13"></line>
								<line x1="12" y1="17" x2="12.01" y2="17"></line>
							</svg>
							<?php
							if ( $date_fin && strtotime( $date_fin ) < time() ) {
								printf(
									/* translators: %s: end date */
									esc_html__( 'Cette enquête est terminée depuis le %s.', 'cgt' ),
									esc_html( date_i18n( 'd/m/Y', strtotime( $date_fin ) ) )
								);
							} else {
								esc_html_e( 'Cette enquête est actuellement inactive.', 'cgt' );
							}
							?>
						</div>
					<?php elseif ( $user_has_voted ) : ?>
						<div class="enquete-alert enquete-alert--info">
							<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
								<circle cx="12" cy="12" r="10"></circle>
								<line x1="12" y1="16" x2="12" y2="12"></line>
								<line x1="12" y1="8" x2="12.01" y2="8"></line>
							</svg>
							<?php esc_html_e( 'Vous avez déjà participé à cette enquête. Merci !', 'cgt' ); ?>
						</div>
					<?php elseif ( $date_fin ) : ?>
						<div class="enquete-meta">
							<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
								<circle cx="12" cy="12" r="10"></circle>
								<polyline points="12 6 12 12 16 14"></polyline>
							</svg>
							<?php
							printf(
								/* translators: %s: end date */
								esc_html__( 'Date de clôture : %s', 'cgt' ),
								'<strong>' . esc_html( date_i18n( 'd/m/Y', strtotime( $date_fin ) ) ) . '</strong>'
							);
							?>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<div class="container enquete-container">
			<?php if ( empty( $questions ) ) : ?>
				<div class="enquete-empty">
					<svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
						<circle cx="12" cy="12" r="10"></circle>
						<line x1="12" y1="8" x2="12" y2="12"></line>
						<line x1="12" y1="16" x2="12.01" y2="16"></line>
					</svg>
					<p><?php esc_html_e( 'Aucune question n\'a été définie pour cette enquête.', 'cgt' ); ?></p>
				</div>
			<?php else : ?>
				<div class="enquete-content">
					<form id="enquete-form" class="enquete-form" method="post">
						<?php foreach ( $questions as $q_index => $question ) : ?>
							<div class="enquete-question" data-question-index="<?php echo esc_attr( $q_index ); ?>">
								<div class="enquete-question__header">
									<span class="enquete-question__number"><?php echo esc_html( sprintf( __( 'Question %d', 'cgt' ), $q_index + 1 ) ); ?></span>
									<h3 class="enquete-question__title"><?php echo esc_html( $question['question'] ); ?></h3>
								</div>

								<?php if ( $is_active && ! $user_has_voted ) : ?>
									<!-- Vote form -->
									<div class="enquete-options">
										<?php if ( ! empty( $question['options'] ) ) : ?>
											<?php foreach ( $question['options'] as $opt_index => $option ) : ?>
												<label class="enquete-option">
													<input
														type="radio"
														name="enquete_vote[<?php echo esc_attr( $q_index ); ?>]"
														value="<?php echo esc_attr( $opt_index ); ?>"
														required
													>
													<span class="enquete-option__label"><?php echo esc_html( $option ); ?></span>
													<span class="enquete-option__checkmark">
														<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
															<polyline points="20 6 9 17 4 12"></polyline>
														</svg>
													</span>
												</label>
											<?php endforeach; ?>
										<?php endif; ?>
									</div>
								<?php else : ?>
									<!-- Show results -->
									<div class="enquete-results">
										<?php
										$question_total = 0;
										if ( isset( $results[ $q_index ] ) && is_array( $results[ $q_index ] ) ) {
											$question_total = array_sum( $results[ $q_index ] );
										}
										?>
										<?php if ( ! empty( $question['options'] ) ) : ?>
											<?php foreach ( $question['options'] as $opt_index => $option ) : ?>
												<?php
												$votes = isset( $results[ $q_index ][ $opt_index ] ) ? (int) $results[ $q_index ][ $opt_index ] : 0;
												$percentage = $question_total > 0 ? round( ( $votes / $question_total ) * 100, 1 ) : 0;
												?>
												<div class="enquete-result-item">
													<div class="enquete-result-item__header">
														<span class="enquete-result-item__label"><?php echo esc_html( $option ); ?></span>
														<span class="enquete-result-item__stats">
															<strong><?php echo esc_html( $percentage ); ?>%</strong>
															<span class="enquete-result-item__votes">(<?php echo esc_html( $votes ); ?> vote<?php echo $votes > 1 ? 's' : ''; ?>)</span>
														</span>
													</div>
													<div class="enquete-result-item__bar">
														<div class="enquete-result-item__progress" style="width: <?php echo esc_attr( $percentage ); ?>%"></div>
													</div>
												</div>
											<?php endforeach; ?>
										<?php endif; ?>
										<div class="enquete-result-total">
											<?php
											printf(
												/* translators: %d: total votes */
												esc_html( _n( 'Total : %d vote', 'Total : %d votes', $question_total, 'cgt' ) ),
												esc_html( $question_total )
											);
											?>
										</div>
									</div>
								<?php endif; ?>
							</div>
						<?php endforeach; ?>

						<?php if ( $is_active && ! $user_has_voted ) : ?>
							<div class="enquete-submit">
								<button type="submit" class="enquete-submit-btn">
									<?php esc_html_e( 'Envoyer mes réponses', 'cgt' ); ?>
									<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
										<line x1="5" y1="12" x2="19" y2="12"></line>
										<polyline points="12 5 19 12 12 19"></polyline>
									</svg>
								</button>
							</div>
						<?php endif; ?>
					</form>

					<!-- Success message (hidden by default) -->
					<div id="enquete-success-message" class="enquete-alert enquete-alert--success" style="display: none;">
						<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
							<path d="M22 11.08V12a10 10 0 11-5.93-9.14"></path>
							<polyline points="22 4 12 14.01 9 11.01"></polyline>
						</svg>
						<?php esc_html_e( 'Merci pour votre participation ! Vos réponses ont été enregistrées.', 'cgt' ); ?>
					</div>
				</div>
			<?php endif; ?>
		</div>
	</main>

	<?php
endwhile;

get_footer();
