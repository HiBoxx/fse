<?php
/**
 * Gestion des articles dans l'espace assistance
 *
 * @package CGT_Child
 */

defined( 'ABSPATH' ) || exit;

//  Récupérer la pagination
$paged = isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;
$per_page = 20;

// Récupérer la recherche
$search = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';

// Query des articles
$args = array(
	'post_type'      => 'post',
	'post_status'    => array( 'publish', 'draft', 'pending', 'private' ),
	'posts_per_page' => $per_page,
	'paged'          => $paged,
	'orderby'        => 'date',
	'order'          => 'DESC',
);

if ( ! empty( $search ) ) {
	$args['s'] = $search;
}

$query = new WP_Query( $args );
?>

<div class="cgt-articles-section">
	<div class="cgt-section-header">
		<div>
			<h2 class="cgt-section-title"><?php esc_html_e( 'Articles', 'cgt' ); ?></h2>
			<p class="cgt-section-description"><?php esc_html_e( 'Gérez tous les articles du site', 'cgt' ); ?></p>
		</div>
		<div>
			<p class="cgt-info-message">
				<?php esc_html_e( 'Pour créer ou modifier des articles, utilisez l\'éditeur WordPress classique via le dashboard admin.', 'cgt' ); ?>
			</p>
		</div>
	</div>

	<!-- Barre de recherche -->
	<div class="cgt-card">
		<form method="get" class="cgt-search-form">
			<input type="hidden" name="section" value="articles">
			<div class="cgt-search-input-group">
				<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
					<circle cx="11" cy="11" r="8"></circle>
					<path d="m21 21-4.35-4.35"></path>
				</svg>
				<input
					type="search"
					name="s"
					value="<?php echo esc_attr( $search ); ?>"
					placeholder="<?php esc_attr_e( 'Rechercher un article...', 'cgt' ); ?>"
					class="cgt-search-input"
				>
				<button type="submit" class="cgt-btn cgt-btn-primary cgt-btn-sm">
					<?php esc_html_e( 'Rechercher', 'cgt' ); ?>
				</button>
				<?php if ( ! empty( $search ) ) : ?>
				<a href="?section=articles" class="cgt-btn cgt-btn-secondary cgt-btn-sm">
					<?php esc_html_e( 'Réinitialiser', 'cgt' ); ?>
				</a>
				<?php endif; ?>
			</div>
		</form>
	</div>

	<!-- Liste des articles -->
	<div class="cgt-card">
		<div class="cgt-card-header">
			<h3 class="cgt-card-title">
				<?php
				/* translators: %d: number of articles */
				printf( esc_html__( '%d article(s)', 'cgt' ), $query->found_posts );
				?>
			</h3>
		</div>

		<?php if ( $query->have_posts() ) : ?>
		<table class="cgt-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Titre', 'cgt' ); ?></th>
					<th><?php esc_html_e( 'Auteur', 'cgt' ); ?></th>
					<th><?php esc_html_e( 'Catégories', 'cgt' ); ?></th>
					<th><?php esc_html_e( 'Statut', 'cgt' ); ?></th>
					<th><?php esc_html_e( 'Date', 'cgt' ); ?></th>
					<th><?php esc_html_e( 'Actions', 'cgt' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php
				while ( $query->have_posts() ) :
					$query->the_post();
					$post_id = get_the_ID();
					?>
				<tr>
					<td><strong><?php the_title(); ?></strong></td>
					<td><?php echo esc_html( get_the_author() ); ?></td>
					<td>
						<?php
						$categories = get_the_category();
						if ( ! empty( $categories ) ) {
							$cat_names = array_map(
								function( $cat ) {
									return $cat->name;
								},
								$categories
							);
							echo esc_html( implode( ', ', $cat_names ) );
						} else {
							echo '—';
						}
						?>
					</td>
					<td>
						<?php
						$status       = get_post_status();
						$status_class = '';
						$status_label = '';
						switch ( $status ) {
							case 'publish':
								$status_class = 'cgt-status-publish';
								$status_label = __( 'Publié', 'cgt' );
								break;
							case 'draft':
								$status_class = 'cgt-status-draft';
								$status_label = __( 'Brouillon', 'cgt' );
								break;
							case 'pending':
								$status_class = 'cgt-status-pending';
								$status_label = __( 'En attente', 'cgt' );
								break;
							case 'private':
								$status_class = 'cgt-status-private';
								$status_label = __( 'Privé', 'cgt' );
								break;
						}
						?>
						<span class="cgt-status <?php echo esc_attr( $status_class ); ?>"><?php echo esc_html( $status_label ); ?></span>
					</td>
					<td><?php echo esc_html( get_the_date( 'd/m/Y' ) ); ?></td>
					<td>
						<div class="cgt-actions">
							<a href="<?php echo esc_url( get_permalink() ); ?>" class="cgt-action-btn cgt-action-view" target="_blank">
								<?php esc_html_e( 'Voir', 'cgt' ); ?>
							</a>
						</div>
					</td>
				</tr>
				<?php endwhile; ?>
			</tbody>
		</table>

		<!-- Pagination -->
		<?php if ( $query->max_num_pages > 1 ) : ?>
		<div class="cgt-pagination">
			<?php
			$current_page = max( 1, $paged );
			$base_url     = add_query_arg( 'section', 'articles', remove_query_arg( 'paged' ) );
			if ( ! empty( $search ) ) {
				$base_url = add_query_arg( 's', $search, $base_url );
			}

			// Page précédente
			if ( $current_page > 1 ) {
				printf(
					'<a href="%s" class="cgt-pagination-link">%s</a>',
					esc_url( add_query_arg( 'paged', $current_page - 1, $base_url ) ),
					esc_html__( '« Précédent', 'cgt' )
				);
			}

			// Numéros de pages
			for ( $i = 1; $i <= $query->max_num_pages; $i++ ) {
				if ( $i === $current_page ) {
					printf(
						'<span class="cgt-pagination-current">%d</span>',
						$i
					);
				} else {
					printf(
						'<a href="%s" class="cgt-pagination-link">%d</a>',
						esc_url( add_query_arg( 'paged', $i, $base_url ) ),
						$i
					);
				}
			}

			// Page suivante
			if ( $current_page < $query->max_num_pages ) {
				printf(
					'<a href="%s" class="cgt-pagination-link">%s</a>',
					esc_url( add_query_arg( 'paged', $current_page + 1, $base_url ) ),
					esc_html__( 'Suivant »', 'cgt' )
				);
			}
			?>
		</div>
		<?php endif; ?>

		<?php else : ?>
		<div class="cgt-empty-state">
			<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
				<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
				<polyline points="14 2 14 8 20 8"></polyline>
			</svg>
			<h3><?php esc_html_e( 'Aucun article trouvé', 'cgt' ); ?></h3>
			<p><?php esc_html_e( 'Aucun article ne correspond à votre recherche.', 'cgt' ); ?></p>
		</div>
		<?php endif; ?>
		<?php wp_reset_postdata(); ?>
	</div>
</div>

<style>
.cgt-section-header {
	display: flex;
	justify-content: space-between;
	align-items: flex-start;
	margin-bottom: 2rem;
	gap: 2rem;
}

.cgt-info-message {
	background: #eff6ff;
	border-left: 4px solid #3b82f6;
	padding: 1rem;
	color: #1e40af;
	font-size: 0.9rem;
	margin: 0;
	border-radius: 4px;
}

.cgt-search-form {
	width: 100%;
}

.cgt-search-input-group {
	display: flex;
	align-items: center;
	gap: 0.75rem;
}

.cgt-search-input-group svg {
	color: #9ca3af;
}

.cgt-search-input {
	flex: 1;
	padding: 0.625rem 1rem;
	border: 1px solid #d1d5db;
	border-radius: 6px;
	font-size: 0.9rem;
}

.cgt-search-input:focus {
	outline: none;
	border-color: #e30613;
	box-shadow: 0 0 0 3px rgba(227, 6, 19, 0.1);
}

.cgt-pagination {
	display: flex;
	align-items: center;
	justify-content: center;
	gap: 0.5rem;
	padding: 1.5rem 0 0.5rem;
	border-top: 1px solid #e5e7eb;
}

.cgt-pagination-link {
	padding: 0.5rem 0.875rem;
	border: 1px solid #d1d5db;
	border-radius: 6px;
	color: #374151;
	text-decoration: none;
	transition: all 0.2s;
}

.cgt-pagination-link:hover {
	background: #f9fafb;
	border-color: #e30613;
	color: #e30613;
}

.cgt-pagination-current {
	padding: 0.5rem 0.875rem;
	background: #e30613;
	color: white;
	border-radius: 6px;
	font-weight: 600;
}
</style>
