<?php
/**
 * Gestion des adhérents dans l'espace assistance
 *
 * @package CGT_Child
 */

defined( 'ABSPATH' ) || exit;

// Query des adhésions
$paged = isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;
$per_page = 20;
$search = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';

$args = array(
	'post_type'      => 'cgt_adhesion',
	'post_status'    => array( 'publish', 'pending' ),
	'posts_per_page' => $per_page,
	'paged'          => $paged,
	'orderby'        => 'date',
	'order'          => 'DESC',
);

if ( ! empty( $search ) ) {
	$args['s'] = $search;
}

$query = new WP_Query( $args );

// Statistiques
$stats_total = wp_count_posts( 'cgt_adhesion' );
$total       = $stats_total->publish + $stats_total->pending;
$validees    = $stats_total->publish;
$en_attente  = $stats_total->pending;
?>

<div class="cgt-adherents-section">
	<div class="cgt-section-header">
		<div>
			<h2 class="cgt-section-title"><?php esc_html_e( 'Adhérents', 'cgt' ); ?></h2>
			<p class="cgt-section-description"><?php esc_html_e( 'Consultez et téléchargez les adhésions', 'cgt' ); ?></p>
		</div>
	</div>

	<!-- Statistiques -->
	<div class="cgt-stats-grid" style="margin-bottom: 2rem;">
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
				<div class="cgt-stat-label"><?php esc_html_e( 'Total', 'cgt' ); ?></div>
				<div class="cgt-stat-value"><?php echo esc_html( $total ); ?></div>
			</div>
		</div>

		<div class="cgt-stat-card">
			<div class="cgt-stat-icon">
				<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
					<polyline points="20 6 9 17 4 12"></polyline>
				</svg>
			</div>
			<div class="cgt-stat-content">
				<div class="cgt-stat-label"><?php esc_html_e( 'Validées', 'cgt' ); ?></div>
				<div class="cgt-stat-value"><?php echo esc_html( $validees ); ?></div>
			</div>
		</div>

		<div class="cgt-stat-card">
			<div class="cgt-stat-icon">
				<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
					<circle cx="12" cy="12" r="10"></circle>
					<polyline points="12 6 12 12 16 14"></polyline>
				</svg>
			</div>
			<div class="cgt-stat-content">
				<div class="cgt-stat-label"><?php esc_html_e( 'En attente', 'cgt' ); ?></div>
				<div class="cgt-stat-value"><?php echo esc_html( $en_attente ); ?></div>
			</div>
		</div>
	</div>

	<!-- Barre de recherche -->
	<div class="cgt-card">
		<form method="get" class="cgt-search-form">
			<input type="hidden" name="section" value="adherents">
			<div class="cgt-search-input-group">
				<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
					<circle cx="11" cy="11" r="8"></circle>
					<path d="m21 21-4.35-4.35"></path>
				</svg>
				<input
					type="search"
					name="s"
					value="<?php echo esc_attr( $search ); ?>"
					placeholder="<?php esc_attr_e( 'Rechercher un adhérent...', 'cgt' ); ?>"
					class="cgt-search-input"
				>
				<button type="submit" class="cgt-btn cgt-btn-primary cgt-btn-sm">
					<?php esc_html_e( 'Rechercher', 'cgt' ); ?>
				</button>
				<?php if ( ! empty( $search ) ) : ?>
				<a href="?section=adherents" class="cgt-btn cgt-btn-secondary cgt-btn-sm">
					<?php esc_html_e( 'Réinitialiser', 'cgt' ); ?>
				</a>
				<?php endif; ?>
			</div>
		</form>
	</div>

	<!-- Liste des adhérents -->
	<div class="cgt-card">
		<div class="cgt-card-header">
			<h3 class="cgt-card-title">
				<?php
				/* translators: %d: number of members */
				printf( esc_html__( '%d adhérent(s)', 'cgt' ), $query->found_posts );
				?>
			</h3>
		</div>

		<?php if ( $query->have_posts() ) : ?>
		<table class="cgt-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Adhérent', 'cgt' ); ?></th>
					<th><?php esc_html_e( 'Email', 'cgt' ); ?></th>
					<th><?php esc_html_e( 'Téléphone', 'cgt' ); ?></th>
					<th><?php esc_html_e( 'Entreprise', 'cgt' ); ?></th>
					<th><?php esc_html_e( 'Date', 'cgt' ); ?></th>
					<th><?php esc_html_e( 'Statut', 'cgt' ); ?></th>
					<th><?php esc_html_e( 'Actions', 'cgt' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php
				while ( $query->have_posts() ) :
					$query->the_post();
					$post_id    = get_the_ID();
					$nom        = get_post_meta( $post_id, '_adhesion_nom', true );
					$prenom     = get_post_meta( $post_id, '_adhesion_prenom', true );
					$email      = get_post_meta( $post_id, '_adhesion_email', true );
					$tel        = get_post_meta( $post_id, '_adhesion_tel', true );
					$entreprise = get_post_meta( $post_id, '_adhesion_entreprise_nom', true );
					$status     = get_post_status();
					?>
				<tr>
					<td><strong><?php echo esc_html( $prenom . ' ' . $nom ); ?></strong></td>
					<td><a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a></td>
					<td><?php echo esc_html( $tel ); ?></td>
					<td><?php echo esc_html( $entreprise ); ?></td>
					<td><?php echo esc_html( get_the_date( 'd/m/Y' ) ); ?></td>
					<td>
						<?php
						$status_class = 'pending' === $status ? 'cgt-status-pending' : 'cgt-status-publish';
						$status_label = 'pending' === $status ? __( 'En attente', 'cgt' ) : __( 'Validée', 'cgt' );
						?>
						<span class="cgt-status <?php echo esc_attr( $status_class ); ?>"><?php echo esc_html( $status_label ); ?></span>
					</td>
					<td>
						<div class="cgt-actions">
							<a href="?section=adherents&download_adhesion=<?php echo esc_attr( $post_id ); ?>" class="cgt-action-btn cgt-action-view">
								<?php esc_html_e( 'PDF', 'cgt' ); ?>
							</a>
						</div>
					</td>
				</tr>
				<?php endwhile; ?>
			</tbody>
		</table>
		<?php else : ?>
		<div class="cgt-empty-state">
			<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
				<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
				<circle cx="9" cy="7" r="4"></circle>
				<path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
				<path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
			</svg>
			<h3><?php esc_html_e( 'Aucun adhérent trouvé', 'cgt' ); ?></h3>
			<p><?php esc_html_e( 'Aucun adhérent ne correspond à votre recherche.', 'cgt' ); ?></p>
		</div>
		<?php endif; ?>
		<?php wp_reset_postdata(); ?>
	</div>
</div>
