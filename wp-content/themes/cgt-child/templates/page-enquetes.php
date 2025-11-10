<?php
/**
 * Template Name: Enquêtes fédérales
 *
 * Présentation des enquêtes et formulaire de participation.
 *
 * @package CGT_Child
 */

get_header();

$status_param   = isset( $_GET['enquete_status'] ) ? sanitize_key( wp_unslash( $_GET['enquete_status'] ) ) : '';
$focus_enquete  = isset( $_GET['enquete'] ) ? absint( $_GET['enquete'] ) : 0;
$can_participate = function_exists( 'cgt_enquete_user_can_participate' ) ? cgt_enquete_user_can_participate() : is_user_logged_in();
$login_url       = cgt_get_login_page_url() ? cgt_get_login_page_url() : wp_login_url( get_permalink() );

$enquete_query = new WP_Query(
	array(
		'post_type'      => 'cgt_enquete',
		'post_status'    => 'publish',
		'orderby'        => 'date',
		'order'          => 'DESC',
		'posts_per_page' => -1,
	)
);
?>

<main id="primary" class="enquetes-page">
	<section class="enquetes-hero">
		<div class="container">
			<div class="enquetes-hero__content">
				<p class="enquetes-hero__eyebrow"><?php esc_html_e( 'Consultations fédérales', 'cgt' ); ?></p>
				<h1><?php the_title(); ?></h1>
				<p><?php esc_html_e( 'Participez aux enquêtes internes pour partager vos priorités de terrain, orienter les actions fédérales et proposer des pistes concrètes d’amélioration.', 'cgt' ); ?></p>
			</div>
			<div class="enquetes-hero__cta">
				<span><?php esc_html_e( 'Accès réservé aux adhérents et équipes mandatées.', 'cgt' ); ?></span>
				<?php if ( ! $can_participate ) : ?>
					<a class="btn" href="<?php echo esc_url( $login_url ); ?>"><?php esc_html_e( 'Se connecter pour participer', 'cgt' ); ?></a>
				<?php else : ?>
					<p class="enquetes-hero__status"><?php esc_html_e( 'Vous êtes authentifié·e. Merci pour votre contribution.', 'cgt' ); ?></p>
				<?php endif; ?>
			</div>
		</div>
	</section>

	<div class="container enquetes-container">
		<?php if ( $status_param ) : ?>
			<div class="enquetes-notice <?php echo in_array( $status_param, array( 'success' ), true ) ? 'is-success' : 'is-warning'; ?>">
				<?php
				switch ( $status_param ) {
					case 'success':
						esc_html_e( 'Merci, votre réponse a bien été enregistrée.', 'cgt' );
						break;
					case 'duplicate':
						esc_html_e( 'Vous avez déjà participé à cette enquête.', 'cgt' );
						break;
					case 'forbidden':
						esc_html_e( 'Votre compte n’a pas les droits nécessaires pour répondre à cette enquête.', 'cgt' );
						break;
					case 'missing':
						esc_html_e( 'Toutes les questions doivent être complétées.', 'cgt' );
						break;
					case 'nonce':
						esc_html_e( 'Votre session a expiré. Merci de réessayer.', 'cgt' );
						break;
					default:
						esc_html_e( 'Une erreur est survenue lors de l’enregistrement de votre réponse.', 'cgt' );
						break;
				}
				?>
			</div>
		<?php endif; ?>

		<?php if ( $enquete_query->have_posts() ) : ?>
			<div class="enquetes-grid">
				<?php
				while ( $enquete_query->have_posts() ) :
					$enquete_query->the_post();
					$enquete_id = get_the_ID();
					$questions  = function_exists( 'cgt_get_enquete_questions' ) ? cgt_get_enquete_questions( $enquete_id ) : array();
					$pdf_id     = (int) get_post_meta( $enquete_id, 'cgt_enquete_pdf_id', true );
					$pdf_url    = $pdf_id ? wp_get_attachment_url( $pdf_id ) : '';
					$has_answer = $can_participate && function_exists( 'cgt_user_has_answered_enquete' ) ? cgt_user_has_answered_enquete( $enquete_id ) : false;
					?>
				<article class="enquete-card" id="enquete-<?php echo esc_attr( $enquete_id ); ?>">
					<div class="enquete-card__media">
						<?php
						if ( has_post_thumbnail() ) {
							the_post_thumbnail( 'large' );
						} else {
							echo '<div class="placeholder" aria-hidden="true"></div>';
						}
						?>
						<div class="enquete-card__badge">
							<span><?php echo esc_html( wp_date( 'd M Y', get_post_timestamp( $enquete_id ) ) ); ?></span>
							<?php if ( ! empty( $questions ) ) : ?>
								<span><?php echo esc_html( sprintf( _n( '%d question', '%d questions', count( $questions ), 'cgt' ), count( $questions ) ) ); ?></span>
							<?php endif; ?>
						</div>
					</div>
					<div class="enquete-card__body">
						<h2><?php the_title(); ?></h2>
						<p class="enquete-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 35 ) ); ?></p>

						<?php if ( $pdf_url ) : ?>
							<a class="enquete-card__download" href="<?php echo esc_url( $pdf_url ); ?>" target="_blank" rel="noreferrer noopener">
								<?php esc_html_e( 'Télécharger la note de cadrage (PDF)', 'cgt' ); ?>
							</a>
						<?php endif; ?>

						<?php if ( $questions ) : ?>
							<div class="enquete-card__form">
								<?php if ( ! $can_participate ) : ?>
									<p class="enquete-card__info">
										<?php esc_html_e( 'Connectez-vous à votre espace adhérent pour répondre à cette enquête.', 'cgt' ); ?>
									</p>
								<?php elseif ( $has_answer ) : ?>
									<p class="enquete-card__info is-success">
										<?php esc_html_e( 'Merci, votre réponse a été enregistrée pour cette enquête.', 'cgt' ); ?>
									</p>
								<?php else : ?>
									<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="enquete-form">
										<?php wp_nonce_field( 'cgt_enquete_response', 'cgt_enquete_response_nonce' ); ?>
										<input type="hidden" name="action" value="cgt_enquete_response">
										<input type="hidden" name="enquete_id" value="<?php echo esc_attr( $enquete_id ); ?>">

										<?php foreach ( $questions as $index => $question ) : ?>
											<fieldset>
												<legend>
													<?php
													printf(
														/* translators: %d: question number. */
														esc_html__( 'Question %d', 'cgt' ),
														(int) $index + 1
													);
													?>
													<span><?php echo esc_html( $question['label'] ); ?></span>
												</legend>
												<?php foreach ( $question['options'] as $option_index => $option ) : ?>
													<div class="enquete-option">
														<input
															type="radio"
															id="<?php echo esc_attr( sprintf( 'enquete-%d-%d-%d', $enquete_id, $index, $option_index ) ); ?>"
															name="responses[<?php echo esc_attr( $question['key'] ); ?>]"
															value="<?php echo esc_attr( $option['value'] ); ?>"
															required
														>
														<label for="<?php echo esc_attr( sprintf( 'enquete-%d-%d-%d', $enquete_id, $index, $option_index ) ); ?>">
															<?php echo esc_html( $option['label'] ); ?>
														</label>
													</div>
												<?php endforeach; ?>
											</fieldset>
										<?php endforeach; ?>

										<button type="submit" class="btn btn-primary">
											<?php esc_html_e( 'Envoyer mes réponses', 'cgt' ); ?>
										</button>
									</form>
								<?php endif; ?>
							</div>
						<?php else : ?>
							<p class="enquete-card__info">
								<?php esc_html_e( 'Le questionnaire sera bientôt disponible.', 'cgt' ); ?>
							</p>
						<?php endif; ?>
					</div>
				</article>
				<?php endwhile; ?>
			</div>
			<?php wp_reset_postdata(); ?>
		<?php else : ?>
			<div class="enquetes-empty">
				<div class="placeholder" aria-hidden="true"></div>
				<p><?php esc_html_e( 'Aucune enquête n’est disponible pour le moment.', 'cgt' ); ?></p>
			</div>
		<?php endif; ?>
	</div>
</main>

<?php
get_footer();
