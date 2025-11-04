<?php
/**
 * Template Name: Espace Gestionnaire
 * Description: Interface moderne pour gérer les adhésions (réservé au gestionnaire)
 *
 * @package CGT_Child
 */

defined( 'ABSPATH' ) || exit;

// ✅ Vérifier que l'utilisateur est connecté ET a le rôle gestionnaire
if ( ! is_user_logged_in() || ! current_user_can( 'view_cgt_adhesions' ) ) {
	wp_redirect( home_url( '/connexion' ) );
	exit;
}

get_header();

// Récupérer les adhésions
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
	's'              => $search,
);

$adhesions_query = new WP_Query( $args );

// Statistiques
$total_adhesions = wp_count_posts( 'cgt_adhesion' );
$total_published = isset( $total_adhesions->publish ) ? $total_adhesions->publish : 0;
$total_pending = isset( $total_adhesions->pending ) ? $total_adhesions->pending : 0;
$total = $total_published + $total_pending;

?>

<style>
	/* ✅ Design moderne pour l'espace gestionnaire */
	.gestionnaire-wrapper {
		min-height: 100vh;
		background: #f8fafc;
		padding: 0;
		margin: 0;
	}

	.gestionnaire-header {
		background: linear-gradient(135deg, #e30613 0%, #b8050f 100%);
		color: white;
		padding: 40px 50px;
		box-shadow: 0 4px 6px rgba(0,0,0,0.1);
	}

	.gestionnaire-header h1 {
		margin: 0 0 10px 0;
		font-size: 36px;
		font-weight: 700;
		color: white;
		display: flex;
		align-items: center;
		gap: 15px;
	}

	.gestionnaire-header .subtitle {
		opacity: 0.95;
		font-size: 16px;
		margin: 0;
	}

	.gestionnaire-header .user-info {
		float: right;
		text-align: right;
	}

	.gestionnaire-header .user-info strong {
		display: block;
		font-size: 18px;
		margin-bottom: 5px;
	}

	.gestionnaire-header .btn-logout {
		background: rgba(255,255,255,0.2);
		color: white;
		padding: 8px 20px;
		border-radius: 6px;
		text-decoration: none;
		display: inline-block;
		margin-top: 10px;
		transition: background 0.3s;
	}

	.gestionnaire-header .btn-logout:hover {
		background: rgba(255,255,255,0.3);
	}

	.gestionnaire-container {
		max-width: 1400px;
		margin: 0 auto;
		padding: 40px 50px;
	}

	.stats-grid {
		display: grid;
		grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
		gap: 25px;
		margin-bottom: 40px;
	}

	.stat-card {
		background: white;
		border-radius: 12px;
		padding: 25px;
		box-shadow: 0 2px 8px rgba(0,0,0,0.06);
		border: 1px solid #e5e7eb;
		transition: transform 0.3s, box-shadow 0.3s;
	}

	.stat-card:hover {
		transform: translateY(-5px);
		box-shadow: 0 8px 16px rgba(0,0,0,0.1);
	}

	.stat-card .stat-icon {
		font-size: 36px;
		margin-bottom: 15px;
	}

	.stat-card .stat-value {
		font-size: 40px;
		font-weight: 700;
		color: #1e293b;
		margin: 0;
	}

	.stat-card .stat-label {
		color: #64748b;
		font-size: 14px;
		margin-top: 5px;
	}

	.search-bar {
		background: white;
		border-radius: 12px;
		padding: 25px;
		margin-bottom: 30px;
		box-shadow: 0 2px 8px rgba(0,0,0,0.06);
		border: 1px solid #e5e7eb;
	}

	.search-bar h3 {
		margin: 0 0 15px 0;
		font-size: 18px;
		color: #1e293b;
	}

	.search-form {
		display: flex;
		gap: 10px;
	}

	.search-input {
		flex: 1;
		padding: 12px 20px;
		border: 2px solid #e5e7eb;
		border-radius: 8px;
		font-size: 15px;
		transition: border-color 0.3s;
	}

	.search-input:focus {
		outline: none;
		border-color: #e30613;
	}

	.btn-search {
		background: #e30613;
		color: white;
		padding: 12px 30px;
		border: none;
		border-radius: 8px;
		font-size: 15px;
		font-weight: 600;
		cursor: pointer;
		transition: background 0.3s;
	}

	.btn-search:hover {
		background: #b8050f;
	}

	.btn-reset {
		background: #f1f5f9;
		color: #64748b;
		padding: 12px 25px;
		border: none;
		border-radius: 8px;
		font-size: 15px;
		font-weight: 600;
		cursor: pointer;
		text-decoration: none;
		transition: background 0.3s;
	}

	.btn-reset:hover {
		background: #e2e8f0;
	}

	.adhesions-table-wrapper {
		background: white;
		border-radius: 12px;
		box-shadow: 0 2px 8px rgba(0,0,0,0.06);
		border: 1px solid #e5e7eb;
		overflow: hidden;
	}

	.adhesions-table {
		width: 100%;
		border-collapse: collapse;
	}

	.adhesions-table thead {
		background: #f8fafc;
		border-bottom: 2px solid #e5e7eb;
	}

	.adhesions-table th {
		text-align: left;
		padding: 18px 20px;
		font-weight: 600;
		color: #1e293b;
		font-size: 14px;
		text-transform: uppercase;
		letter-spacing: 0.5px;
	}

	.adhesions-table tbody tr {
		border-bottom: 1px solid #f1f5f9;
		transition: background 0.2s;
	}

	.adhesions-table tbody tr:hover {
		background: #f8fafc;
	}

	.adhesions-table td {
		padding: 18px 20px;
		color: #475569;
		font-size: 14px;
	}

	.adhesions-table td strong {
		color: #1e293b;
		display: block;
		margin-bottom: 3px;
	}

	.adhesions-table td small {
		color: #94a3b8;
		font-size: 12px;
	}

	.status-badge {
		display: inline-block;
		padding: 6px 12px;
		border-radius: 20px;
		font-size: 12px;
		font-weight: 600;
		text-transform: uppercase;
		letter-spacing: 0.5px;
	}

	.status-badge.pending {
		background: #fef3c7;
		color: #92400e;
	}

	.status-badge.publish {
		background: #d1fae5;
		color: #065f46;
	}

	.btn-download {
		background: #e30613;
		color: white;
		padding: 10px 20px;
		border-radius: 8px;
		text-decoration: none;
		display: inline-flex;
		align-items: center;
		gap: 8px;
		font-size: 13px;
		font-weight: 600;
		transition: all 0.3s;
	}

	.btn-download:hover {
		background: #b8050f;
		transform: translateY(-2px);
		box-shadow: 0 4px 8px rgba(227,6,19,0.3);
	}

	.pagination {
		padding: 25px;
		text-align: center;
		background: #f8fafc;
		border-top: 1px solid #e5e7eb;
	}

	.pagination a,
	.pagination span {
		padding: 10px 15px;
		margin: 0 3px;
		background: white;
		border: 1px solid #e5e7eb;
		border-radius: 6px;
		text-decoration: none;
		color: #64748b;
		display: inline-block;
		transition: all 0.2s;
	}

	.pagination a:hover {
		background: #e30613;
		color: white;
		border-color: #e30613;
	}

	.pagination .current {
		background: #e30613;
		color: white;
		border-color: #e30613;
		font-weight: 600;
	}

	.empty-state {
		text-align: center;
		padding: 80px 40px;
	}

	.empty-state-icon {
		font-size: 64px;
		margin-bottom: 20px;
	}

	.empty-state h3 {
		font-size: 24px;
		color: #1e293b;
		margin: 0 0 10px 0;
	}

	.empty-state p {
		color: #64748b;
		font-size: 16px;
	}
</style>

<div class="gestionnaire-wrapper">

	<!-- Header -->
	<div class="gestionnaire-header">
		<div style="overflow: hidden;">
			<div style="float: left;">
				<h1>
					<span>👥</span>
					Espace Gestionnaire
				</h1>
				<p class="subtitle">Gestion des adhésions CGT</p>
			</div>
			<div class="user-info">
				<strong>👤 <?php echo esc_html( wp_get_current_user()->display_name ); ?></strong>
				<small>Gestionnaire</small>
				<a href="<?php echo esc_url( wp_logout_url( home_url( '/connexion' ) ) ); ?>" class="btn-logout">
					🚪 Déconnexion
				</a>
			</div>
		</div>
	</div>

	<!-- Container -->
	<div class="gestionnaire-container">

		<!-- Statistiques -->
		<div class="stats-grid">
			<div class="stat-card">
				<div class="stat-icon">📊</div>
				<div class="stat-value"><?php echo esc_html( $total ); ?></div>
				<div class="stat-label">Total adhésions</div>
			</div>
			<div class="stat-card">
				<div class="stat-icon">✅</div>
				<div class="stat-value"><?php echo esc_html( $total_published ); ?></div>
				<div class="stat-label">Validées</div>
			</div>
			<div class="stat-card">
				<div class="stat-icon">⏳</div>
				<div class="stat-value"><?php echo esc_html( $total_pending ); ?></div>
				<div class="stat-label">En attente</div>
			</div>
		</div>

		<!-- Recherche -->
		<div class="search-bar">
			<h3>🔍 Rechercher une adhésion</h3>
			<form method="get" class="search-form">
				<input type="text" name="s" class="search-input" placeholder="Nom, prénom, email, entreprise..." value="<?php echo esc_attr( $search ); ?>">
				<button type="submit" class="btn-search">Rechercher</button>
				<?php if ( $search ) : ?>
					<a href="<?php echo esc_url( remove_query_arg( 's' ) ); ?>" class="btn-reset">Réinitialiser</a>
				<?php endif; ?>
			</form>
		</div>

		<!-- Tableau des adhésions -->
		<div class="adhesions-table-wrapper">
			<?php if ( $adhesions_query->have_posts() ) : ?>
				<table class="adhesions-table">
					<thead>
						<tr>
							<th>📋 Adhérent</th>
							<th>🏢 Entreprise</th>
							<th>📧 Contact</th>
							<th>📅 Date</th>
							<th>🎯 Statut</th>
							<th>📄 Actions</th>
						</tr>
					</thead>
					<tbody>
						<?php while ( $adhesions_query->have_posts() ) : $adhesions_query->the_post();
							$post_id = get_the_ID();
							$nom = get_post_meta( $post_id, '_adhesion_nom', true );
							$prenom = get_post_meta( $post_id, '_adhesion_prenom', true );
							$email = get_post_meta( $post_id, '_adhesion_email', true );
							$tel = get_post_meta( $post_id, '_adhesion_tel', true );
							$entreprise = get_post_meta( $post_id, '_adhesion_entreprise_nom', true );
							$date = get_the_date( 'd/m/Y' );
							$status = get_post_status();
							$status_label = ( 'publish' === $status ) ? 'Validée' : 'En attente';
							$status_class = ( 'publish' === $status ) ? 'publish' : 'pending';
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
									<span class="status-badge <?php echo esc_attr( $status_class ); ?>">
										<?php echo esc_html( $status_label ); ?>
									</span>
								</td>
								<td>
									<a href="<?php echo esc_url( add_query_arg( 'download_adhesion', $post_id ) ); ?>" class="btn-download" target="_blank">
										📥 Télécharger PDF
									</a>
								</td>
							</tr>
						<?php endwhile; ?>
					</tbody>
				</table>

				<!-- Pagination -->
				<?php if ( $adhesions_query->max_num_pages > 1 ) : ?>
					<div class="pagination">
						<?php
						echo paginate_links( array(
							'base'      => add_query_arg( 'paged', '%#%' ),
							'format'    => '',
							'current'   => $paged,
							'total'     => $adhesions_query->max_num_pages,
							'prev_text' => '← Précédent',
							'next_text' => 'Suivant →',
						) );
						?>
					</div>
				<?php endif; ?>

			<?php else : ?>
				<div class="empty-state">
					<div class="empty-state-icon">📭</div>
					<h3>Aucune adhésion trouvée</h3>
					<p><?php echo $search ? 'Aucun résultat pour votre recherche.' : 'Aucune adhésion enregistrée pour le moment.'; ?></p>
				</div>
			<?php endif; ?>
			<?php wp_reset_postdata(); ?>
		</div>

	</div>

</div>

<?php
get_footer();
