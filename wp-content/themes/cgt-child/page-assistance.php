<?php
/**
 * Template Name: Espace Assistance
 * Description: Interface de gestion des contenus CGT (articles, tracts, événements, médiathèque, messages, adhérents)
 *
 * @package CGT_Child
 */

defined( 'ABSPATH' ) || exit;

// Vérifier l'accès - Seuls les utilisateurs avec le rôle "assistance" peuvent accéder
if ( ! is_user_logged_in() || ! current_user_can( 'edit_posts' ) ) {
	wp_redirect( home_url( '/connexion' ) );
	exit;
}

// Vérifier que l'utilisateur a bien le rôle assistance
$user = wp_get_current_user();
if ( ! in_array( 'assistance', (array) $user->roles, true ) ) {
	wp_redirect( home_url() );
	exit;
}

// Récupérer les statistiques pour le dashboard
$stats = array(
	'articles'   => wp_count_posts( 'post' ),
	'tracts'     => wp_count_posts( 'tracts' ),
	'events'     => wp_count_posts( 'cgt_agenda' ),
	'messages'   => wp_count_posts( 'cgt_contact' ),
	'adhesions'  => wp_count_posts( 'cgt_adhesion' ),
);

// Gérer la section active (via paramètre URL)
$current_section = isset( $_GET['section'] ) ? sanitize_key( $_GET['section'] ) : 'dashboard';

get_header();
?>

<div class="cgt-assistance-space">
	<!-- Header avec navigation -->
	<div class="cgt-assistance-header">
		<div class="cgt-assistance-header-content">
			<div class="cgt-assistance-logo">
				<h1><?php esc_html_e( 'Espace Assistance CGT', 'cgt' ); ?></h1>
				<p><?php esc_html_e( 'Gestion des contenus et des adhérents', 'cgt' ); ?></p>
			</div>
			<div class="cgt-assistance-user-info">
				<span class="user-name"><?php echo esc_html( $user->display_name ); ?></span>
				<a href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>" class="logout-link">
					<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
						<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
						<polyline points="16 17 21 12 16 7"></polyline>
						<line x1="21" y1="12" x2="9" y2="12"></line>
					</svg>
					<?php esc_html_e( 'Déconnexion', 'cgt' ); ?>
				</a>
			</div>
		</div>
	</div>

	<!-- Navigation tabs -->
	<div class="cgt-assistance-tabs">
		<a href="?section=dashboard" class="cgt-tab <?php echo 'dashboard' === $current_section ? 'active' : ''; ?>">
			<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
				<rect x="3" y="3" width="7" height="7"></rect>
				<rect x="14" y="3" width="7" height="7"></rect>
				<rect x="14" y="14" width="7" height="7"></rect>
				<rect x="3" y="14" width="7" height="7"></rect>
			</svg>
			Dashboard
		</a>
		<a href="?section=articles" class="cgt-tab <?php echo 'articles' === $current_section ? 'active' : ''; ?>">
			<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
				<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
				<polyline points="14 2 14 8 20 8"></polyline>
				<line x1="16" y1="13" x2="8" y2="13"></line>
				<line x1="16" y1="17" x2="8" y2="17"></line>
				<polyline points="10 9 9 9 8 9"></polyline>
			</svg>
			Articles
		</a>
		<a href="?section=tracts" class="cgt-tab <?php echo 'tracts' === $current_section ? 'active' : ''; ?>">
			<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
				<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
				<polyline points="14 2 14 8 20 8"></polyline>
			</svg>
			Tracts
		</a>
		<a href="?section=events" class="cgt-tab <?php echo 'events' === $current_section ? 'active' : ''; ?>">
			<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
				<rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
				<line x1="16" y1="2" x2="16" y2="6"></line>
				<line x1="8" y1="2" x2="8" y2="6"></line>
				<line x1="3" y1="10" x2="21" y2="10"></line>
			</svg>
			Événements
		</a>
		<a href="?section=media" class="cgt-tab <?php echo 'media' === $current_section ? 'active' : ''; ?>">
			<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
				<rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
				<circle cx="8.5" cy="8.5" r="1.5"></circle>
				<polyline points="21 15 16 10 5 21"></polyline>
			</svg>
			Médiathèque
		</a>
		<a href="?section=messages" class="cgt-tab <?php echo 'messages' === $current_section ? 'active' : ''; ?>">
			<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
				<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
			</svg>
			Messages
			<?php
			$pending_messages = get_posts(
				array(
					'post_type'      => 'cgt_contact',
					'post_status'    => 'pending',
					'posts_per_page' => -1,
					'fields'         => 'ids',
				)
			);
			if ( count( $pending_messages ) > 0 ) {
				echo '<span class="badge">' . esc_html( count( $pending_messages ) ) . '</span>';
			}
			?>
		</a>
		<a href="?section=adherents" class="cgt-tab <?php echo 'adherents' === $current_section ? 'active' : ''; ?>">
			<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
				<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
				<circle cx="9" cy="7" r="4"></circle>
				<path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
				<path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
			</svg>
			Adhérents
		</a>
	</div>

	<!-- Contenu principal -->
	<div class="cgt-assistance-content">
		<?php
		// Afficher la section appropriée
		switch ( $current_section ) {
			case 'dashboard':
				include __DIR__ . '/inc/assistance/dashboard.php';
				break;
			case 'articles':
				include __DIR__ . '/inc/assistance/articles.php';
				break;
			case 'tracts':
				include __DIR__ . '/inc/assistance/tracts.php';
				break;
			case 'events':
				include __DIR__ . '/inc/assistance/events.php';
				break;
			case 'media':
				include __DIR__ . '/inc/assistance/media.php';
				break;
			case 'messages':
				include __DIR__ . '/inc/assistance/messages.php';
				break;
			case 'adherents':
				include __DIR__ . '/inc/assistance/adherents.php';
				break;
			default:
				include __DIR__ . '/inc/assistance/dashboard.php';
				break;
		}
		?>
	</div>
</div>

<style>
/* ===== ESPACE ASSISTANCE - STYLES ===== */

.cgt-assistance-space {
	min-height: 100vh;
	background: #f5f5f5;
}

/* Header */
.cgt-assistance-header {
	background: linear-gradient(135deg, #e30613 0%, #b8050f 100%);
	color: white;
	padding: 2rem 0;
	box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.cgt-assistance-header-content {
	max-width: 1400px;
	margin: 0 auto;
	padding: 0 2rem;
	display: flex;
	justify-content: space-between;
	align-items: center;
}

.cgt-assistance-logo h1 {
	margin: 0;
	font-size: 1.8rem;
	font-weight: 700;
	color: white;
}

.cgt-assistance-logo p {
	margin: 0.5rem 0 0;
	opacity: 0.95;
	font-size: 0.95rem;
}

.cgt-assistance-user-info {
	display: flex;
	align-items: center;
	gap: 1.5rem;
}

.user-name {
	font-weight: 500;
}

.logout-link {
	display: flex;
	align-items: center;
	gap: 0.5rem;
	color: white;
	text-decoration: none;
	padding: 0.5rem 1rem;
	border: 1px solid rgba(255, 255, 255, 0.3);
	border-radius: 6px;
	transition: all 0.2s;
}

.logout-link:hover {
	background: rgba(255, 255, 255, 0.1);
	border-color: rgba(255, 255, 255, 0.5);
}

/* Navigation Tabs */
.cgt-assistance-tabs {
	background: white;
	border-bottom: 1px solid #e5e7eb;
	max-width: 1400px;
	margin: 0 auto;
	padding: 0 2rem;
	display: flex;
	gap: 0.5rem;
	overflow-x: auto;
}

.cgt-tab {
	display: flex;
	align-items: center;
	gap: 0.5rem;
	padding: 1rem 1.5rem;
	color: #6b7280;
	text-decoration: none;
	border-bottom: 3px solid transparent;
	transition: all 0.2s;
	white-space: nowrap;
	position: relative;
}

.cgt-tab:hover {
	color: #e30613;
	background: #fef2f2;
}

.cgt-tab.active {
	color: #e30613;
	border-bottom-color: #e30613;
	font-weight: 600;
}

.cgt-tab .badge {
	background: #e30613;
	color: white;
	font-size: 0.75rem;
	padding: 0.125rem 0.5rem;
	border-radius: 12px;
	font-weight: 600;
	min-width: 20px;
	text-align: center;
}

/* Contenu principal */
.cgt-assistance-content {
	max-width: 1400px;
	margin: 0 auto;
	padding: 2rem;
}

/* Cards communs */
.cgt-card {
	background: white;
	border-radius: 8px;
	padding: 1.5rem;
	box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
	margin-bottom: 1.5rem;
}

.cgt-card-header {
	display: flex;
	justify-content: space-between;
	align-items: center;
	margin-bottom: 1.5rem;
	padding-bottom: 1rem;
	border-bottom: 1px solid #e5e7eb;
}

.cgt-card-title {
	font-size: 1.25rem;
	font-weight: 600;
	color: #1f2937;
	margin: 0;
}

/* Boutons */
.cgt-btn {
	display: inline-flex;
	align-items: center;
	gap: 0.5rem;
	padding: 0.625rem 1.25rem;
	border: none;
	border-radius: 6px;
	font-size: 0.9rem;
	font-weight: 500;
	cursor: pointer;
	transition: all 0.2s;
	text-decoration: none;
}

.cgt-btn-primary {
	background: #e30613;
	color: white;
}

.cgt-btn-primary:hover {
	background: #b8050f;
}

.cgt-btn-secondary {
	background: #6b7280;
	color: white;
}

.cgt-btn-secondary:hover {
	background: #4b5563;
}

.cgt-btn-sm {
	padding: 0.375rem 0.75rem;
	font-size: 0.85rem;
}

/* Tables */
.cgt-table {
	width: 100%;
	border-collapse: collapse;
}

.cgt-table th {
	text-align: left;
	padding: 0.75rem 1rem;
	background: #f9fafb;
	font-weight: 600;
	font-size: 0.85rem;
	text-transform: uppercase;
	letter-spacing: 0.05em;
	color: #6b7280;
	border-bottom: 1px solid #e5e7eb;
}

.cgt-table td {
	padding: 1rem;
	border-bottom: 1px solid #e5e7eb;
	color: #374151;
}

.cgt-table tbody tr:hover {
	background: #f9fafb;
}

/* Stats Grid (pour le dashboard) */
.cgt-stats-grid {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
	gap: 1.5rem;
	margin-bottom: 2rem;
}

.cgt-stat-card {
	background: white;
	border-radius: 8px;
	padding: 1.5rem;
	box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
	display: flex;
	align-items: center;
	gap: 1rem;
}

.cgt-stat-icon {
	width: 48px;
	height: 48px;
	display: flex;
	align-items: center;
	justify-content: center;
	border-radius: 8px;
	background: #fee2e2;
	color: #e30613;
}

.cgt-stat-content {
	flex: 1;
}

.cgt-stat-label {
	font-size: 0.875rem;
	color: #6b7280;
	margin-bottom: 0.25rem;
}

.cgt-stat-value {
	font-size: 1.75rem;
	font-weight: 700;
	color: #1f2937;
}

/* Actions */
.cgt-actions {
	display: flex;
	gap: 0.5rem;
}

.cgt-action-btn {
	padding: 0.375rem 0.75rem;
	border-radius: 4px;
	font-size: 0.85rem;
	text-decoration: none;
	transition: all 0.2s;
}

.cgt-action-view {
	background: #3b82f6;
	color: white;
}

.cgt-action-view:hover {
	background: #2563eb;
}

.cgt-action-edit {
	background: #10b981;
	color: white;
}

.cgt-action-edit:hover {
	background: #059669;
}

.cgt-action-delete {
	background: #ef4444;
	color: white;
}

.cgt-action-delete:hover {
	background: #dc2626;
}

/* Status badges */
.cgt-status {
	display: inline-block;
	padding: 0.25rem 0.75rem;
	border-radius: 12px;
	font-size: 0.8rem;
	font-weight: 500;
}

.cgt-status-publish {
	background: #d1fae5;
	color: #065f46;
}

.cgt-status-draft {
	background: #fed7aa;
	color: #92400e;
}

.cgt-status-pending {
	background: #fef3c7;
	color: #92400e;
}

.cgt-status-private {
	background: #e0e7ff;
	color: #3730a3;
}

/* Formulaires de soumission */
.cgt-card-form {
	margin-bottom: 1.5rem;
}

.cgt-form-details {
	border: 1px solid #e5e7eb;
	border-radius: 8px;
	padding: 0.75rem 1rem;
	background: #f9fafb;
}

.cgt-form-summary {
	font-weight: 600;
	font-size: 0.95rem;
	cursor: pointer;
	color: #1f2937;
}

.cgt-form-summary::-webkit-details-marker {
	display: none;
}

.cgt-form-summary::before {
	content: '\25BC';
	display: inline-block;
	margin-right: 0.5rem;
	transition: transform 0.2s ease;
}

.cgt-form-details[open] .cgt-form-summary::before {
	transform: rotate(180deg);
}

.cgt-form-grid {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
	gap: 1rem 1.5rem;
	padding-top: 1rem;
}

.cgt-form-field {
	display: grid;
	gap: 0.5rem;
}

.cgt-form-field span {
	font-weight: 600;
	font-size: 0.9rem;
	color: #475569;
}

.cgt-form-field input,
.cgt-form-field textarea,
.cgt-form-field select {
	width: 100%;
	border: 1px solid #d1d5db;
	border-radius: 6px;
	padding: 0.65rem 0.75rem;
	font-size: 0.95rem;
	transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.cgt-form-field input:focus,
.cgt-form-field textarea:focus,
.cgt-form-field select:focus {
	outline: none;
	border-color: #e30613;
	box-shadow: 0 0 0 2px rgba(227, 6, 19, 0.15);
}

.cgt-form-field--full {
	grid-column: 1 / -1;
}

.cgt-form-actions {
	grid-column: 1 / -1;
}

.cgt-form-note {
	grid-column: 1 / -1;
	font-size: 0.85rem;
	color: #6b7280;
	margin: 0;
}

/* Empty state */
.cgt-empty-state {
	text-align: center;
	padding: 3rem 1rem;
	color: #6b7280;
}

.cgt-empty-state svg {
	width: 64px;
	height: 64px;
	margin: 0 auto 1rem;
	opacity: 0.5;
}

.cgt-empty-state h3 {
	margin: 0 0 0.5rem;
	color: #374151;
}

/* Responsive */
@media (max-width: 768px) {
	.cgt-assistance-header-content {
		flex-direction: column;
		gap: 1rem;
		text-align: center;
	}

	.cgt-assistance-tabs {
		padding: 0 1rem;
	}

	.cgt-assistance-content {
		padding: 1rem;
	}

	.cgt-stats-grid {
		grid-template-columns: 1fr;
	}

	.cgt-table {
		font-size: 0.85rem;
	}

	.cgt-table th,
	.cgt-table td {
		padding: 0.5rem;
	}
}
</style>

<?php get_footer(); ?>
