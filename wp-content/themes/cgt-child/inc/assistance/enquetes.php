<?php
/**
 * Gestion des enquêtes dans l'espace assistance.
 *
 * @package CGT_Child
 */

defined( 'ABSPATH' ) || exit;

$status_param   = isset( $_GET['status'] ) ? sanitize_key( wp_unslash( $_GET['status'] ) ) : '';
$search         = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
$paged          = isset( $_GET['paged'] ) ? max( 1, absint( $_GET['paged'] ) ) : 1;
$per_page       = 10;
$view_enquete   = isset( $_GET['view'] ) ? absint( $_GET['view'] ) : 0;
$responses_page = isset( $_GET['responses_page'] ) ? max( 1, absint( $_GET['responses_page'] ) ) : 1;

$enquetes_query = new WP_Query(
	array(
		'post_type'      => 'cgt_enquete',
		'post_status'    => array( 'publish', 'draft', 'pending' ),
		'posts_per_page' => $per_page,
		'paged'          => $paged,
		'orderby'        => 'date',
		'order'          => 'DESC',
		's'              => $search,
	)
);

$responses_query = null;
if ( $view_enquete ) {
	$responses_query = new WP_Query(
		array(
			'post_type'      => 'cgt_enquete_response',
			'post_status'    => 'publish',
			'posts_per_page' => 15,
			'paged'          => $responses_page,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'meta_query'     => array(
				array(
					'key'   => 'cgt_enquete_id',
					'value' => $view_enquete,
				),
			),
		)
	);
}

?>

<div class="cgt-articles-section">
	<div class="cgt-section-header">
		<div>
			<h2 class="cgt-section-title"><?php esc_html_e( 'Enquêtes', 'cgt' ); ?></h2>
			<p class="cgt-section-description"><?php esc_html_e( 'Créez des questionnaires multi-choix et suivez les réponses transmises par les adhérents.', 'cgt' ); ?></p>
		</div>
		<div>
			<?php if ( 'success' === $status_param ) : ?>
				<p class="cgt-info-message" style="background:#ecfdf5;color:#065f46;">
					<?php esc_html_e( 'L’enquête a été enregistrée et sera soumise à validation.', 'cgt' ); ?>
				</p>
			<?php elseif ( in_array( $status_param, array( 'missing', 'error', 'nonce' ), true ) ) : ?>
				<p class="cgt-info-message" style="background:#fee2e2;color:#991b1b;">
					<?php
					switch ( $status_param ) {
						case 'missing':
							esc_html_e( 'Merci de renseigner le titre, la description et au moins une question avec deux réponses.', 'cgt' );
							break;
						case 'nonce':
							esc_html_e( 'Votre session a expiré. Merci de réessayer.', 'cgt' );
							break;
						default:
							esc_html_e( 'Une erreur est survenue lors de l’enregistrement de l’enquête.', 'cgt' );
							break;
					}
					?>
				</p>
			<?php else : ?>
				<p class="cgt-info-message">
					<?php esc_html_e( 'Chaque nouvelle enquête reste en brouillon le temps d’être relue par l’administration.', 'cgt' ); ?>
				</p>
			<?php endif; ?>
		</div>
	</div>

	<div class="cgt-card cgt-card-form">
		<details class="cgt-form-details" <?php echo 'success' === $status_param ? '' : 'open'; ?>>
			<summary class="cgt-form-summary">
				<span>➕ <?php esc_html_e( 'Nouvelle enquête', 'cgt' ); ?></span>
			</summary>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data" class="cgt-form-grid cgt-form-grid--two">
				<?php wp_nonce_field( 'cgt_assistance_enquete', 'cgt_assistance_enquete_nonce' ); ?>
				<input type="hidden" name="action" value="cgt_assistance_enquete_submit">

				<label class="cgt-form-field">
					<span><?php esc_html_e( 'Titre de l’enquête', 'cgt' ); ?> *</span>
					<input type="text" name="title" required>
				</label>

				<label class="cgt-form-field">
					<span><?php esc_html_e( 'Résumé court', 'cgt' ); ?></span>
					<textarea name="excerpt" rows="3" placeholder="<?php esc_attr_e( 'Optionnel, affiché sur la page publique.', 'cgt' ); ?>"></textarea>
				</label>

				<label class="cgt-form-field cgt-form-field--full">
					<span><?php esc_html_e( 'Description détaillée', 'cgt' ); ?> *</span>
					<textarea name="description" rows="6" required></textarea>
				</label>

				<label class="cgt-form-field">
					<span><?php esc_html_e( 'Image mise en avant (optionnel)', 'cgt' ); ?></span>
					<input type="file" name="featured_image" accept="image/*">
				</label>

				<label class="cgt-form-field">
					<span><?php esc_html_e( 'Brief / Note de cadrage (PDF)', 'cgt' ); ?></span>
					<input type="file" name="pdf_file" accept="application/pdf">
				</label>

				<div class="cgt-form-field cgt-form-field--full">
					<span><?php esc_html_e( 'Questions et réponses (multi-choix)', 'cgt' ); ?> *</span>
					<div class="cgt-questionnaire-builder" data-questionnaire-builder>
						<p class="cgt-questionnaire-helper">
							<?php esc_html_e( 'Ajoutez une question, puis listez au moins deux propositions de réponse. Les adhérents ne peuvent sélectionner qu’une proposition par question.', 'cgt' ); ?>
						</p>
						<div class="cgt-question-list" data-question-list></div>
						<button type="button" class="cgt-btn cgt-btn-secondary" data-add-question><?php esc_html_e( 'Ajouter une question', 'cgt' ); ?></button>
						<template id="cgt-question-template">
							<div class="cgt-question-block" data-question-block>
								<div class="cgt-question-block__header">
									<strong class="cgt-question-block__title"><?php esc_html_e( 'Nouvelle question', 'cgt' ); ?></strong>
									<button type="button" class="cgt-btn cgt-btn-ghost cgt-btn-sm" data-remove-question>
										<?php esc_html_e( 'Supprimer', 'cgt' ); ?>
									</button>
								</div>
								<label>
									<span><?php esc_html_e( 'Intitulé de la question', 'cgt' ); ?> *</span>
									<input type="text" data-name="questions[__index__][label]" required>
								</label>
								<div class="cgt-option-list" data-option-list></div>
								<button type="button" class="cgt-btn cgt-btn-light cgt-btn-sm" data-add-option>
									<?php esc_html_e( 'Ajouter une option', 'cgt' ); ?>
								</button>
							</div>
						</template>
					</div>
				</div>

				<div class="cgt-form-actions">
					<button type="submit" class="cgt-btn cgt-btn-primary"><?php esc_html_e( 'Enregistrer l’enquête', 'cgt' ); ?></button>
					<a class="cgt-btn cgt-btn-secondary" href="<?php echo esc_url( cgt_get_enquetes_page_url() ); ?>" target="_blank" rel="noreferrer noopener">
						<?php esc_html_e( 'Voir la page publique', 'cgt' ); ?>
					</a>
				</div>
				<p class="cgt-form-note"><?php esc_html_e( 'L’enquête sera créée en brouillon le temps de la validation éditoriale.', 'cgt' ); ?></p>
			</form>
		</details>
	</div>

	<div class="cgt-card">
		<form method="get" class="cgt-search-form">
			<input type="hidden" name="section" value="enquetes">
			<div class="cgt-search-input-group">
				<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
					<circle cx="11" cy="11" r="8"></circle>
					<path d="m21 21-4.35-4.35"></path>
				</svg>
				<input type="search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php esc_attr_e( 'Rechercher une enquête...', 'cgt' ); ?>" class="cgt-search-input">
				<button type="submit" class="cgt-btn cgt-btn-primary cgt-btn-sm"><?php esc_html_e( 'Rechercher', 'cgt' ); ?></button>
				<?php if ( $search ) : ?>
					<a href="?section=enquetes" class="cgt-btn cgt-btn-secondary cgt-btn-sm"><?php esc_html_e( 'Réinitialiser', 'cgt' ); ?></a>
				<?php endif; ?>
			</div>
		</form>
	</div>

	<div class="cgt-card">
		<div class="cgt-card-header">
			<h3 class="cgt-card-title">
				<?php
				printf(
					/* translators: %d: number of surveys */
					esc_html__( '%d enquête(s)', 'cgt' ),
					(int) $enquetes_query->found_posts
				);
				?>
			</h3>
		</div>

		<?php if ( $enquetes_query->have_posts() ) : ?>
			<table class="cgt-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Titre', 'cgt' ); ?></th>
						<th><?php esc_html_e( 'Statut', 'cgt' ); ?></th>
						<th><?php esc_html_e( 'Questions', 'cgt' ); ?></th>
						<th><?php esc_html_e( 'Réponses', 'cgt' ); ?></th>
						<th><?php esc_html_e( 'Dernière réponse', 'cgt' ); ?></th>
						<th><?php esc_html_e( 'Actions', 'cgt' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php
					while ( $enquetes_query->have_posts() ) :
						$enquetes_query->the_post();
						$enquete_id      = get_the_ID();
						$questions_count = count( cgt_get_enquete_questions( $enquete_id ) );
						$responses_count = cgt_get_enquete_response_count( $enquete_id );
						$last_response   = get_post_meta( $enquete_id, 'cgt_enquete_last_response', true );
						$status_obj      = get_post_status_object( get_post_status( $enquete_id ) );
						?>
					<tr>
						<td>
							<strong><?php the_title(); ?></strong>
							<p class="cgt-table-subtext"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 14 ) ); ?></p>
						</td>
						<td>
							<span class="cgt-status-badge cgt-status-<?php echo esc_attr( get_post_status( $enquete_id ) ); ?>">
								<?php echo esc_html( $status_obj ? $status_obj->label : __( 'Indéfini', 'cgt' ) ); ?>
							</span>
						</td>
						<td><?php echo esc_html( $questions_count ); ?></td>
						<td><?php echo esc_html( $responses_count ); ?></td>
						<td>
							<?php
							echo $last_response
								? esc_html( wp_date( 'd/m/Y H:i', strtotime( $last_response ) ) )
								: '—';
							?>
						</td>
						<td class="cgt-table-actions">
							<a class="cgt-btn cgt-btn-ghost cgt-btn-sm" href="<?php echo esc_url( add_query_arg( array( 'section' => 'enquetes', 'view' => $enquete_id ), get_permalink() ) ); ?>">
								<?php esc_html_e( 'Voir les réponses', 'cgt' ); ?>
							</a>
							<a class="cgt-btn cgt-btn-secondary cgt-btn-sm" href="<?php echo esc_url( get_edit_post_link( $enquete_id ) ); ?>" target="_blank" rel="noreferrer noopener">
								<?php esc_html_e( 'Modifier', 'cgt' ); ?>
							</a>
						</td>
					</tr>
					<?php endwhile; ?>
				</tbody>
			</table>

			<?php
			$total_pages = $enquetes_query->max_num_pages;
			if ( $total_pages > 1 ) :
				?>
				<div class="cgt-pagination">
					<?php
					echo wp_kses_post(
						paginate_links(
							array(
								'current' => $paged,
								'total'   => $total_pages,
								'format'  => '?section=enquetes&paged=%#%',
								'add_args'=> $search ? array( 's' => $search ) : false,
							)
						)
					);
					?>
				</div>
				<?php
			endif;
			?>
			<?php wp_reset_postdata(); ?>
		<?php else : ?>
			<p><?php esc_html_e( 'Aucune enquête pour le moment.', 'cgt' ); ?></p>
		<?php endif; ?>
	</div>

	<?php if ( $view_enquete ) : ?>
		<div class="cgt-card" id="enquete-responses">
			<div class="cgt-card-header">
				<h3 class="cgt-card-title">
					<?php
					printf(
						/* translators: %s: survey title */
						esc_html__( 'Réponses pour « %s »', 'cgt' ),
						esc_html( get_the_title( $view_enquete ) )
					);
					?>
				</h3>
			</div>

			<?php if ( $responses_query && $responses_query->have_posts() ) : ?>
				<table class="cgt-table cgt-table--stacked">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Adhérent', 'cgt' ); ?></th>
							<th><?php esc_html_e( 'Branche', 'cgt' ); ?></th>
							<th><?php esc_html_e( 'Réponses', 'cgt' ); ?></th>
							<th><?php esc_html_e( 'Date', 'cgt' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php
						while ( $responses_query->have_posts() ) :
							$responses_query->the_post();
							$response_id = get_the_ID();
							$user_id     = get_post_field( 'post_author', $response_id );
							$user        = get_user_by( 'id', $user_id );
							$branch_id   = (int) get_post_meta( $response_id, 'cgt_enquete_branch', true );
							$branch_name = '—';
							if ( $branch_id ) {
								$term = get_term( $branch_id, 'branche' );
								if ( $term && ! is_wp_error( $term ) ) {
									$branch_name = $term->name;
								}
							}
							$answers = cgt_get_enquete_response_payload( $response_id );
							?>
						<tr>
							<td>
								<strong><?php echo esc_html( $user ? $user->display_name : __( 'Compte supprimé', 'cgt' ) ); ?></strong>
								<p class="cgt-table-subtext"><?php echo esc_html( $user ? $user->user_email : '—' ); ?></p>
							</td>
							<td><?php echo esc_html( $branch_name ); ?></td>
							<td>
								<ul class="cgt-response-list">
									<?php foreach ( $answers as $answer ) : ?>
										<li>
											<strong><?php echo esc_html( $answer['question'] ); ?></strong>
											<span><?php echo esc_html( $answer['label'] ); ?></span>
										</li>
									<?php endforeach; ?>
								</ul>
							</td>
							<td><?php echo esc_html( get_the_date( 'd/m/Y H:i' ) ); ?></td>
						</tr>
						<?php endwhile; ?>
					</tbody>
				</table>

				<?php
				$total_responses_pages = $responses_query->max_num_pages;
				if ( $total_responses_pages > 1 ) :
					?>
					<div class="cgt-pagination">
						<?php
						echo wp_kses_post(
							paginate_links(
								array(
									'current' => $responses_page,
									'total'   => $total_responses_pages,
									'format'  => '?section=enquetes&view=' . $view_enquete . '&responses_page=%#%',
								)
							)
						);
						?>
					</div>
					<?php
				endif;
				?>
				<?php wp_reset_postdata(); ?>
			<?php else : ?>
				<p><?php esc_html_e( 'Aucune réponse enregistrée pour le moment.', 'cgt' ); ?></p>
			<?php endif; ?>
		</div>
	<?php endif; ?>
</div>
