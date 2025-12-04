<?php
/**
 * Gestion des événements dans l'espace assistance
 *
 * @package CGT_Child
 */

defined( 'ABSPATH' ) || exit;

$status_param = isset( $_GET['status'] ) ? sanitize_key( wp_unslash( $_GET['status'] ) ) : '';

$paged    = isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;
$per_page = 20;
$search   = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';

$args = array(
	'post_type'      => 'cgt_agenda',
	'post_status'    => array( 'publish', 'draft', 'pending', 'private' ),
	'posts_per_page' => $per_page,
	'paged'          => $paged,
	'orderby'        => 'date',
	'order'          => 'DESC',
);

if ( $search ) {
	$args['s'] = $search;
}

$query = new WP_Query( $args );

?>

<div class="cgt-articles-section">
	<div class="cgt-section-header">
		<div>
			<h2 class="cgt-section-title"><?php esc_html_e( 'Événements', 'cgt' ); ?></h2>
			<p class="cgt-section-description"><?php esc_html_e( 'Planification des rendez-vous fédéraux', 'cgt' ); ?></p>
		</div>
		<div>
			<?php if ( 'success' === $status_param ) : ?>
				<p class="cgt-info-message" style="background:#ecfdf5;color:#047857;">
					<?php esc_html_e( 'Événement envoyé à l’administration pour validation.', 'cgt' ); ?>
				</p>
			<?php elseif ( in_array( $status_param, array( 'missing', 'error', 'nonce' ), true ) ) : ?>
				<p class="cgt-info-message" style="background:#fee2e2;color:#b91c1c;">
					<?php
					switch ( $status_param ) {
						case 'missing':
							esc_html_e( 'Merci de renseigner au minimum le titre, la description et la date.', 'cgt' );
							break;
						case 'nonce':
							esc_html_e( 'Votre session a expiré. Veuillez réessayer.', 'cgt' );
							break;
						default:
							esc_html_e( 'Une erreur est survenue lors de l’envoi de l’événement.', 'cgt' );
							break;
					}
					?>
				</p>
			<?php else : ?>
				<p class="cgt-info-message">
					<?php esc_html_e( 'Soumettez un événement via le formulaire ci-dessous. Il sera transmis à l’administration pour validation.', 'cgt' ); ?>
				</p>
			<?php endif; ?>
		</div>
	</div>

	<div class="cgt-card cgt-card-form">
		<details class="cgt-form-details" <?php echo 'success' === $status_param ? '' : 'open'; ?>>
			<summary class="cgt-form-summary">
				<span>➕ <?php esc_html_e( 'Nouvel événement', 'cgt' ); ?></span>
			</summary>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data" class="cgt-form-grid">
				<?php wp_nonce_field( 'cgt_assistance_event', 'cgt_assistance_event_nonce' ); ?>
				<input type="hidden" name="action" value="cgt_assistance_event_submit">

				<label class="cgt-form-field">
					<span><?php esc_html_e( 'Titre', 'cgt' ); ?> *</span>
					<input type="text" name="title" required>
				</label>

				<label class="cgt-form-field cgt-form-field--full">
					<span><?php esc_html_e( 'Description', 'cgt' ); ?> *</span>
					<textarea name="description" rows="6" required></textarea>
				</label>

				<label class="cgt-form-field">
					<span><?php esc_html_e( 'Date et heure', 'cgt' ); ?> *</span>
					<input type="datetime-local" name="event_date" required>
				</label>

				<label class="cgt-form-field cgt-form-field--full">
					<span><?php esc_html_e( 'Adresse / Lieu', 'cgt' ); ?></span>
					<textarea name="event_address" rows="3"></textarea>
				</label>

				<label class="cgt-form-field">
					<span><?php esc_html_e( 'Extrait', 'cgt' ); ?></span>
					<textarea name="excerpt" rows="3"></textarea>
				</label>

				<label class="cgt-form-field">
					<span><?php esc_html_e( 'Mots-clés (séparés par des virgules)', 'cgt' ); ?></span>
					<input type="text" name="keywords">
				</label>

				<label class="cgt-form-field">
					<span><?php esc_html_e( 'Branche (optionnel)', 'cgt' ); ?></span>
					<select name="branch">
						<option value=""><?php esc_html_e( '-- Choisir une branche --', 'cgt' ); ?></option>
						<?php cgt_render_branch_options(); ?>
					</select>
				</label>

				<label class="cgt-form-field cgt-form-field--full">
					<span><?php esc_html_e( 'Sources et références', 'cgt' ); ?></span>
					<textarea name="sources" rows="3"></textarea>
				</label>

				<label class="cgt-form-field">
					<span><?php esc_html_e( 'Image mise en avant (optionnel)', 'cgt' ); ?></span>
					<input type="file" name="featured_image" accept="image/*">
				</label>

				<label class="cgt-form-field">
					<span><?php esc_html_e( 'Document PDF (optionnel)', 'cgt' ); ?></span>
					<input type="file" name="pdf_file" accept="application/pdf">
				</label>

				<div class="cgt-form-actions">
					<button type="submit" class="cgt-btn cgt-btn-primary"><?php esc_html_e( 'Envoyer à l’administration', 'cgt' ); ?></button>
				</div>
				<p class="cgt-form-note"><?php esc_html_e( 'L’événement sera enregistré en attente de validation par l’administration.', 'cgt' ); ?></p>
			</form>
		</details>
	</div>

	<div class="cgt-card">
		<form method="get" class="cgt-search-form">
			<input type="hidden" name="section" value="events">
			<div class="cgt-search-input-group">
				<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
					<circle cx="11" cy="11" r="8"></circle>
					<path d="m21 21-4.35-4.35"></path>
				</svg>
				<input type="search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php esc_attr_e( 'Rechercher un événement...', 'cgt' ); ?>" class="cgt-search-input">
				<button type="submit" class="cgt-btn cgt-btn-primary cgt-btn-sm"><?php esc_html_e( 'Rechercher', 'cgt' ); ?></button>
				<?php if ( $search ) : ?>
					<a href="?section=events" class="cgt-btn cgt-btn-secondary cgt-btn-sm"><?php esc_html_e( 'Réinitialiser', 'cgt' ); ?></a>
				<?php endif; ?>
			</div>
		</form>
	</div>

	<div class="cgt-card">
		<div class="cgt-card-header">
			<h3 class="cgt-card-title"><?php printf( esc_html__( '%d événement(s)', 'cgt' ), (int) $query->found_posts ); ?></h3>
		</div>

		<?php if ( $query->have_posts() ) : ?>
		<table class="cgt-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Titre', 'cgt' ); ?></th>
					<th><?php esc_html_e( 'Date', 'cgt' ); ?></th>
					<th><?php esc_html_e( 'Lieu', 'cgt' ); ?></th>
					<th><?php esc_html_e( 'Statut', 'cgt' ); ?></th>
					<th><?php esc_html_e( 'Actions', 'cgt' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php
				while ( $query->have_posts() ) :
					$query->the_post();
					$post_id   = get_the_ID();
					$event_at  = get_post_meta( $post_id, 'cgt_event_date', true );
					$event_loc = get_post_meta( $post_id, 'cgt_event_address', true );
					$status    = get_post_status();
					$label     = __( 'Brouillon', 'cgt' );
					$class     = 'cgt-status-draft';
					switch ( $status ) {
						case 'publish':
							$label = __( 'Publié', 'cgt' );
							$class = 'cgt-status-publish';
							break;
						case 'pending':
							$label = __( 'En attente', 'cgt' );
							$class = 'cgt-status-pending';
							break;
						case 'private':
							$label = __( 'Privé', 'cgt' );
							$class = 'cgt-status-private';
							break;
					}
					?>
				<tr>
					<td><strong><?php the_title(); ?></strong></td>
					<td><?php echo $event_at ? esc_html( wp_date( 'd/m/Y H:i', strtotime( $event_at ) ) ) : '—'; ?></td>
					<td><?php echo $event_loc ? esc_html( $event_loc ) : '—'; ?></td>
					<td><span class="cgt-status <?php echo esc_attr( $class ); ?>"><?php echo esc_html( $label ); ?></span></td>
					<td>
						<div class="cgt-actions">
							<a href="<?php echo esc_url( get_permalink() ); ?>" class="cgt-action-btn cgt-action-view" target="_blank"><?php esc_html_e( 'Voir', 'cgt' ); ?></a>
						</div>
					</td>
				</tr>
				<?php endwhile; ?>
			</tbody>
		</table>

		<?php if ( $query->max_num_pages > 1 ) : ?>
			<div class="cgt-pagination">
				<?php
				$current_page = max( 1, $paged );
				$base_url     = add_query_arg( 'section', 'events', remove_query_arg( 'paged' ) );
				if ( $search ) {
					$base_url = add_query_arg( 's', $search, $base_url );
				}

				if ( $current_page > 1 ) {
					printf( '<a href="%s" class="cgt-pagination-link">%s</a>', esc_url( add_query_arg( 'paged', $current_page - 1, $base_url ) ), esc_html__( '« Précédent', 'cgt' ) );
				}

				for ( $i = 1; $i <= $query->max_num_pages; $i++ ) {
					if ( $i === $current_page ) {
						printf( '<span class="cgt-pagination-current">%d</span>', $i );
					} else {
						printf( '<a href="%s" class="cgt-pagination-link">%d</a>', esc_url( add_query_arg( 'paged', $i, $base_url ) ), $i );
					}
				}

				if ( $current_page < $query->max_num_pages ) {
					printf( '<a href="%s" class="cgt-pagination-link">%s</a>', esc_url( add_query_arg( 'paged', $current_page + 1, $base_url ) ), esc_html__( 'Suivant »', 'cgt' ) );
				}
				?>
			</div>
		<?php endif; ?>

		<?php else : ?>
			<div class="cgt-empty-state">
				<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
					<rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
					<line x1="16" y1="2" x2="16" y2="6"></line>
					<line x1="8" y1="2" x2="8" y2="6"></line>
					<line x1="3" y1="10" x2="21" y2="10"></line>
				</svg>
				<h3><?php esc_html_e( 'Aucun événement trouvé', 'cgt' ); ?></h3>
				<p><?php esc_html_e( 'Aucun événement ne correspond à votre recherche.', 'cgt' ); ?></p>
			</div>
		<?php endif; ?>
		<?php wp_reset_postdata(); ?>
	</div>
</div>
