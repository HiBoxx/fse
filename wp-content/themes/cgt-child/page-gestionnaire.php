<?php
/**
 * Template Name: Espace Gestionnaire
 * Description: Interface moderne pour gérer les adhésions (réservé au gestionnaire)
 *
 * @package CGT_Child
 * @version 2.0
 */

defined( 'ABSPATH' ) || exit;

// Vérifier que l'utilisateur est connecté ET a le rôle gestionnaire
if ( ! is_user_logged_in() || ! current_user_can( 'view_cgt_adhesions' ) ) {
	wp_redirect( home_url( '/connexion' ) );
	exit;
}

// Enqueue le Design System
wp_enqueue_style( 'cgt-spaces', get_stylesheet_directory_uri() . '/assets/css/cgt-spaces.css', array(), '2.0' );

get_header();

// Récupérer les adhésions
$paged    = isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;
$per_page = 20;
$search   = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';

$args = array(
	'post_type'      => 'cgt_adhesion',
	'post_status'    => array( 'publish', 'pending' ),
	'posts_per_page' => $per_page,
	'paged'          => $paged,
	'orderby'        => 'date',
	'order'          => 'DESC',
	's'              => $search,
);

$adhesions_query = new WP_Query( $args );

// Statistiques
$total_adhesions = wp_count_posts( 'cgt_adhesion' );
$total_published = isset( $total_adhesions->publish ) ? $total_adhesions->publish : 0;
$total_pending   = isset( $total_adhesions->pending ) ? $total_adhesions->pending : 0;
$total           = $total_published + $total_pending;

$current_user = wp_get_current_user();
$initials     = strtoupper( substr( $current_user->display_name, 0, 2 ) );
?>

<div class="cgt-space">

	<!-- Header -->
	<div class="cgt-space-header">
		<div class="cgt-space-header-content">
			<div class="cgt-space-header-left">
				<div class="cgt-space-icon">
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
						<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
						<circle cx="9" cy="7" r="4"></circle>
						<path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
						<path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
					</svg>
				</div>
				<div class="cgt-space-title">
					<h1><?php esc_html_e( 'Espace Gestionnaire', 'cgt' ); ?></h1>
					<p><?php esc_html_e( 'Gestion des adhésions CGT', 'cgt' ); ?></p>
				</div>
			</div>
			<div class="cgt-space-header-right">
				<div class="cgt-user-badge">
					<div class="cgt-user-avatar"><?php echo esc_html( $initials ); ?></div>
					<div class="cgt-user-info">
						<span class="cgt-user-name"><?php echo esc_html( $current_user->display_name ); ?></span>
						<span class="cgt-user-role"><?php esc_html_e( 'Gestionnaire', 'cgt' ); ?></span>
					</div>
				</div>
				<a href="<?php echo esc_url( wp_logout_url( home_url( '/connexion' ) ) ); ?>" class="cgt-btn-logout">
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
						<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
						<polyline points="16 17 21 12 16 7"></polyline>
						<line x1="21" y1="12" x2="9" y2="12"></line>
					</svg>
					<?php esc_html_e( 'Déconnexion', 'cgt' ); ?>
				</a>
			</div>
		</div>
	</div>

	<!-- Container -->
	<div class="cgt-space-container">

		<!-- Statistiques -->
		<div class="cgt-stats-grid">
			<div class="cgt-stat-card">
				<div class="cgt-stat-content">
					<div class="cgt-stat-icon">
						<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
							<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
							<circle cx="9" cy="7" r="4"></circle>
							<path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
							<path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
						</svg>
					</div>
					<div class="cgt-stat-info">
						<div class="cgt-stat-label"><?php esc_html_e( 'Total adhésions', 'cgt' ); ?></div>
						<div class="cgt-stat-value"><?php echo esc_html( $total ); ?></div>
					</div>
				</div>
			</div>

			<div class="cgt-stat-card">
				<div class="cgt-stat-content">
					<div class="cgt-stat-icon">
						<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
							<polyline points="20 6 9 17 4 12"></polyline>
						</svg>
					</div>
					<div class="cgt-stat-info">
						<div class="cgt-stat-label"><?php esc_html_e( 'Validées', 'cgt' ); ?></div>
						<div class="cgt-stat-value"><?php echo esc_html( $total_published ); ?></div>
					</div>
				</div>
			</div>

			<div class="cgt-stat-card">
				<div class="cgt-stat-content">
					<div class="cgt-stat-icon">
						<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
							<circle cx="12" cy="12" r="10"></circle>
							<polyline points="12 6 12 12 16 14"></polyline>
						</svg>
					</div>
					<div class="cgt-stat-info">
						<div class="cgt-stat-label"><?php esc_html_e( 'En attente', 'cgt' ); ?></div>
						<div class="cgt-stat-value"><?php echo esc_html( $total_pending ); ?></div>
					</div>
				</div>
			</div>
		</div>

		<!-- Recherche -->
		<div class="cgt-card">
			<div class="cgt-card-header">
				<h3 class="cgt-card-title">
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
						<circle cx="11" cy="11" r="8"></circle>
						<path d="m21 21-4.35-4.35"></path>
					</svg>
					<?php esc_html_e( 'Rechercher une adhésion', 'cgt' ); ?>
				</h3>
			</div>
			<form method="get" class="cgt-search-form">
				<input
					type="search"
					name="s"
					class="cgt-form-input"
					placeholder="<?php esc_attr_e( 'Nom, prénom, email, entreprise...', 'cgt' ); ?>"
					value="<?php echo esc_attr( $search ); ?>"
				>
				<button type="submit" class="cgt-btn cgt-btn-primary">
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
						<circle cx="11" cy="11" r="8"></circle>
						<path d="m21 21-4.35-4.35"></path>
					</svg>
					<?php esc_html_e( 'Rechercher', 'cgt' ); ?>
				</button>
				<?php if ( $search ) : ?>
					<a href="<?php echo esc_url( remove_query_arg( 's' ) ); ?>" class="cgt-btn cgt-btn-secondary">
						<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
							<line x1="18" y1="6" x2="6" y2="18"></line>
							<line x1="6" y1="6" x2="18" y2="18"></line>
						</svg>
						<?php esc_html_e( 'Réinitialiser', 'cgt' ); ?>
					</a>
				<?php endif; ?>
			</form>
		</div>

		<!-- Tableau des adhésions -->
		<?php if ( $adhesions_query->have_posts() ) : ?>
			<div class="cgt-table-wrapper">
				<table class="cgt-table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Adhérent', 'cgt' ); ?></th>
							<th><?php esc_html_e( 'Entreprise', 'cgt' ); ?></th>
							<th><?php esc_html_e( 'Contact', 'cgt' ); ?></th>
							<th><?php esc_html_e( 'Date', 'cgt' ); ?></th>
							<th><?php esc_html_e( 'Statut', 'cgt' ); ?></th>
							<th><?php esc_html_e( 'Actions', 'cgt' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php
						while ( $adhesions_query->have_posts() ) :
							$adhesions_query->the_post();
							$post_id     = get_the_ID();
							$nom         = get_post_meta( $post_id, '_adhesion_nom', true );
							$prenom      = get_post_meta( $post_id, '_adhesion_prenom', true );
							$email       = get_post_meta( $post_id, '_adhesion_email', true );
							$tel         = get_post_meta( $post_id, '_adhesion_tel', true );
							$entreprise  = get_post_meta( $post_id, '_adhesion_entreprise_nom', true );
							$date        = get_the_date( 'd/m/Y' );
							$status      = get_post_status();
							$badge_class = ( 'publish' === $status ) ? 'cgt-badge-success' : 'cgt-badge-warning';
							$badge_label = ( 'publish' === $status ) ? __( 'Validée', 'cgt' ) : __( 'En attente', 'cgt' );
							?>
							<tr>
								<td>
									<strong><?php echo esc_html( $prenom . ' ' . $nom ); ?></strong>
								</td>
								<td><?php echo esc_html( $entreprise ); ?></td>
								<td>
									<strong><?php echo esc_html( $email ); ?></strong>
									<small><?php echo esc_html( $tel ); ?></small>
								</td>
								<td><?php echo esc_html( $date ); ?></td>
								<td>
									<span class="cgt-badge <?php echo esc_attr( $badge_class ); ?>">
										<?php echo esc_html( $badge_label ); ?>
									</span>
								</td>
								<td>
									<div class="cgt-actions">
										<a href="<?php echo esc_url( add_query_arg( 'download_adhesion', $post_id ) ); ?>" class="cgt-action-btn cgt-action-download" target="_blank">
											<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
												<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
												<polyline points="7 10 12 15 17 10"></polyline>
												<line x1="12" y1="15" x2="12" y2="3"></line>
											</svg>
											<?php esc_html_e( 'PDF', 'cgt' ); ?>
										</a>
									</div>
								</td>
							</tr>
						<?php endwhile; ?>
					</tbody>
				</table>

				<!-- Pagination -->
				<?php if ( $adhesions_query->max_num_pages > 1 ) : ?>
					<div class="cgt-pagination">
						<?php
						echo paginate_links(
							array(
								'base'      => add_query_arg( 'paged', '%#%' ),
								'format'    => '',
								'current'   => $paged,
								'total'     => $adhesions_query->max_num_pages,
								'prev_text' => '← ' . __( 'Précédent', 'cgt' ),
								'next_text' => __( 'Suivant', 'cgt' ) . ' →',
							)
						);
						?>
					</div>
				<?php endif; ?>
			</div>

		<?php else : ?>
			<div class="cgt-card">
				<div class="cgt-empty-state">
					<div class="cgt-empty-state-icon">
						<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
							<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
							<circle cx="12" cy="7" r="4"></circle>
						</svg>
					</div>
					<h3><?php esc_html_e( 'Aucune adhésion trouvée', 'cgt' ); ?></h3>
					<p>
						<?php
						echo $search
							? esc_html__( 'Aucun résultat ne correspond à votre recherche.', 'cgt' )
							: esc_html__( 'Aucune adhésion enregistrée pour le moment.', 'cgt' );
						?>
					</p>
				</div>
			</div>
		<?php endif; ?>
		<?php wp_reset_postdata(); ?>

	</div>

</div>

<?php
get_footer();
