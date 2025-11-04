<?php
/**
 * Dashboard de l'espace assistance
 *
 * @package CGT_Child
 */

defined( 'ABSPATH' ) || exit;

// Récupérer les statistiques
$stats_articles   = wp_count_posts( 'post' );
$stats_tracts     = wp_count_posts( 'tracts' );
$stats_events     = wp_count_posts( 'cgt_agenda' );
$stats_messages   = wp_count_posts( 'cgt_contact' );
$stats_adhesions  = wp_count_posts( 'cgt_adhesion' );

// Récupérer les contenus récents
$recent_posts = get_posts(
	array(
		'post_type'      => array( 'post', 'tracts', 'cgt_agenda' ),
		'posts_per_page' => 5,
		'orderby'        => 'date',
		'order'          => 'DESC',
	)
);
?>

<div class="cgt-dashboard">
	<h2 class="cgt-section-title"><?php esc_html_e( 'Bienvenue dans l\'espace assistance', 'cgt' ); ?></h2>
	<p class="cgt-section-description"><?php esc_html_e( 'Gérez tous les contenus du site depuis cette interface.', 'cgt' ); ?></p>

	<!-- Statistiques -->
	<div class="cgt-stats-grid">
		<div class="cgt-stat-card">
			<div class="cgt-stat-icon">
				<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
					<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
					<polyline points="14 2 14 8 20 8"></polyline>
				</svg>
			</div>
			<div class="cgt-stat-content">
				<div class="cgt-stat-label"><?php esc_html_e( 'Articles', 'cgt' ); ?></div>
				<div class="cgt-stat-value"><?php echo esc_html( $stats_articles->publish + $stats_articles->draft + $stats_articles->pending ); ?></div>
				<div class="cgt-stat-details">
					<?php
					printf(
						/* translators: %1$d: published, %2$d: draft */
						esc_html__( '%1$d publiés, %2$d brouillons', 'cgt' ),
						(int) $stats_articles->publish,
						(int) $stats_articles->draft
					);
					?>
				</div>
			</div>
		</div>

		<div class="cgt-stat-card">
			<div class="cgt-stat-icon">
				<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
					<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
					<polyline points="14 2 14 8 20 8"></polyline>
				</svg>
			</div>
			<div class="cgt-stat-content">
				<div class="cgt-stat-label"><?php esc_html_e( 'Tracts', 'cgt' ); ?></div>
				<div class="cgt-stat-value"><?php echo esc_html( $stats_tracts->publish + $stats_tracts->draft ); ?></div>
				<div class="cgt-stat-details">
					<?php
					printf(
						/* translators: %d: published */
						esc_html__( '%d publiés', 'cgt' ),
						(int) $stats_tracts->publish
					);
					?>
				</div>
			</div>
		</div>

		<div class="cgt-stat-card">
			<div class="cgt-stat-icon">
				<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
					<rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
					<line x1="16" y1="2" x2="16" y2="6"></line>
					<line x1="8" y1="2" x2="8" y2="6"></line>
					<line x1="3" y1="10" x2="21" y2="10"></line>
				</svg>
			</div>
			<div class="cgt-stat-content">
				<div class="cgt-stat-label"><?php esc_html_e( 'Événements', 'cgt' ); ?></div>
				<div class="cgt-stat-value"><?php echo esc_html( $stats_events->publish + $stats_events->draft ); ?></div>
				<div class="cgt-stat-details">
					<?php
					// Compter les événements à venir
					$upcoming_events = get_posts(
						array(
							'post_type'      => 'cgt_agenda',
							'posts_per_page' => -1,
							'fields'         => 'ids',
							'meta_query'     => array(
								array(
									'key'     => 'cgt_event_date',
									'value'   => current_time( 'Y-m-d' ),
									'compare' => '>=',
									'type'    => 'DATE',
								),
							),
						)
					);
					printf(
						/* translators: %d: upcoming events */
						esc_html__( '%d à venir', 'cgt' ),
						count( $upcoming_events )
					);
					?>
				</div>
			</div>
		</div>

		<div class="cgt-stat-card">
			<div class="cgt-stat-icon">
				<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
					<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
				</svg>
			</div>
			<div class="cgt-stat-content">
				<div class="cgt-stat-label"><?php esc_html_e( 'Messages', 'cgt' ); ?></div>
				<div class="cgt-stat-value"><?php echo esc_html( $stats_messages->pending + $stats_messages->publish ); ?></div>
				<div class="cgt-stat-details">
					<?php
					printf(
						/* translators: %d: pending messages */
						esc_html__( '%d non traités', 'cgt' ),
						(int) $stats_messages->pending
					);
					?>
				</div>
			</div>
		</div>

		<div class="cgt-stat-card">
			<div class="cgt-stat-icon">
				<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
					<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
					<circle cx="9" cy="7" r="4"></circle>
					<path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
					<path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
				</svg>
			</div>
			<div class="cgt-stat-content">
				<div class="cgt-stat-label"><?php esc_html_e( 'Adhésions', 'cgt' ); ?></div>
				<div class="cgt-stat-value"><?php echo esc_html( $stats_adhesions->pending + $stats_adhesions->publish ); ?></div>
				<div class="cgt-stat-details">
					<?php
					printf(
						/* translators: %d: pending adhesions */
						esc_html__( '%d en attente', 'cgt' ),
						(int) $stats_adhesions->pending
					);
					?>
				</div>
			</div>
		</div>
	</div>

	<!-- Actions rapides -->
	<div class="cgt-card">
		<div class="cgt-card-header">
			<h3 class="cgt-card-title"><?php esc_html_e( 'Actions rapides', 'cgt' ); ?></h3>
		</div>
		<div class="cgt-quick-actions">
			<a href="?section=articles&action=new" class="cgt-quick-action">
				<div class="cgt-quick-action-icon">
					<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
						<line x1="12" y1="5" x2="12" y2="19"></line>
						<line x1="5" y1="12" x2="19" y2="12"></line>
					</svg>
				</div>
				<div class="cgt-quick-action-content">
					<h4><?php esc_html_e( 'Nouvel article', 'cgt' ); ?></h4>
					<p><?php esc_html_e( 'Publier un nouvel article', 'cgt' ); ?></p>
				</div>
			</a>

			<a href="?section=tracts&action=new" class="cgt-quick-action">
				<div class="cgt-quick-action-icon">
					<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
						<line x1="12" y1="5" x2="12" y2="19"></line>
						<line x1="5" y1="12" x2="19" y2="12"></line>
					</svg>
				</div>
				<div class="cgt-quick-action-content">
					<h4><?php esc_html_e( 'Nouveau tract', 'cgt' ); ?></h4>
					<p><?php esc_html_e( 'Ajouter un tract', 'cgt' ); ?></p>
				</div>
			</a>

			<a href="?section=events&action=new" class="cgt-quick-action">
				<div class="cgt-quick-action-icon">
					<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
						<line x1="12" y1="5" x2="12" y2="19"></line>
						<line x1="5" y1="12" x2="19" y2="12"></line>
					</svg>
				</div>
				<div class="cgt-quick-action-content">
					<h4><?php esc_html_e( 'Nouvel événement', 'cgt' ); ?></h4>
					<p><?php esc_html_e( 'Créer un événement', 'cgt' ); ?></p>
				</div>
			</a>

			<a href="?section=messages" class="cgt-quick-action">
				<div class="cgt-quick-action-icon">
					<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
						<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
					</svg>
				</div>
				<div class="cgt-quick-action-content">
					<h4><?php esc_html_e( 'Voir les messages', 'cgt' ); ?></h4>
					<p><?php esc_html_e( 'Consulter les messages', 'cgt' ); ?></p>
				</div>
			</a>
		</div>
	</div>

	<!-- Contenus récents -->
	<?php if ( ! empty( $recent_posts ) ) : ?>
	<div class="cgt-card">
		<div class="cgt-card-header">
			<h3 class="cgt-card-title"><?php esc_html_e( 'Contenus récents', 'cgt' ); ?></h3>
		</div>
		<table class="cgt-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Titre', 'cgt' ); ?></th>
					<th><?php esc_html_e( 'Type', 'cgt' ); ?></th>
					<th><?php esc_html_e( 'Statut', 'cgt' ); ?></th>
					<th><?php esc_html_e( 'Date', 'cgt' ); ?></th>
					<th><?php esc_html_e( 'Actions', 'cgt' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $recent_posts as $post ) : ?>
				<tr>
					<td><strong><?php echo esc_html( $post->post_title ); ?></strong></td>
					<td>
						<?php
						$post_type_obj = get_post_type_object( $post->post_type );
						echo esc_html( $post_type_obj->labels->singular_name );
						?>
					</td>
					<td>
						<?php
						$status_class = '';
						$status_label = '';
						switch ( $post->post_status ) {
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
					<td><?php echo esc_html( get_the_date( 'd/m/Y H:i', $post ) ); ?></td>
					<td>
						<div class="cgt-actions">
							<a href="<?php echo esc_url( get_permalink( $post ) ); ?>" class="cgt-action-btn cgt-action-view" target="_blank">
								<?php esc_html_e( 'Voir', 'cgt' ); ?>
							</a>
						</div>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<?php endif; ?>
</div>

<style>
.cgt-section-title {
	font-size: 1.75rem;
	font-weight: 700;
	color: #1f2937;
	margin: 0 0 0.5rem;
}

.cgt-section-description {
	color: #6b7280;
	margin: 0 0 2rem;
}

.cgt-stat-details {
	font-size: 0.8rem;
	color: #9ca3af;
	margin-top: 0.25rem;
}

.cgt-quick-actions {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
	gap: 1rem;
}

.cgt-quick-action {
	display: flex;
	align-items: center;
	gap: 1rem;
	padding: 1rem;
	border: 1px solid #e5e7eb;
	border-radius: 8px;
	text-decoration: none;
	transition: all 0.2s;
}

.cgt-quick-action:hover {
	border-color: #e30613;
	box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
	transform: translateY(-2px);
}

.cgt-quick-action-icon {
	width: 48px;
	height: 48px;
	display: flex;
	align-items: center;
	justify-content: center;
	background: #fee2e2;
	color: #e30613;
	border-radius: 8px;
}

.cgt-quick-action-content h4 {
	margin: 0 0 0.25rem;
	color: #1f2937;
	font-size: 1rem;
	font-weight: 600;
}

.cgt-quick-action-content p {
	margin: 0;
	color: #6b7280;
	font-size: 0.85rem;
}
</style>
