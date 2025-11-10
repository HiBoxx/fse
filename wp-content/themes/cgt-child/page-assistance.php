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

// Enqueue le Design System
wp_enqueue_style( 'cgt-spaces', get_stylesheet_directory_uri() . '/assets/css/cgt-spaces.css', array(), '2.0' );

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

$initials = strtoupper( substr( $user->display_name, 0, 2 ) );

if ( 'enquetes' === $current_section ) {
	wp_enqueue_script(
		'cgt-assistance-enquetes',
		get_stylesheet_directory_uri() . '/assets/js/assistance-enquetes.js',
		array(),
		CGT_CHILD_VERSION,
		true
	);
}

get_header();
?>

<div class="cgt-space">
	<!-- Header avec navigation -->
	<div class="cgt-space-header">
		<div class="cgt-space-header-content">
			<div class="cgt-space-header-left">
				<div class="cgt-space-icon">
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
						<rect x="3" y="3" width="7" height="7"></rect>
						<rect x="14" y="3" width="7" height="7"></rect>
						<rect x="14" y="14" width="7" height="7"></rect>
						<rect x="3" y="14" width="7" height="7"></rect>
					</svg>
				</div>
				<div class="cgt-space-title">
					<h1><?php esc_html_e( 'Espace Assistance CGT', 'cgt' ); ?></h1>
					<p><?php esc_html_e( 'Gestion des contenus et des adhérents', 'cgt' ); ?></p>
				</div>
			</div>
			<div class="cgt-space-header-right">
				<div class="cgt-user-badge">
					<div class="cgt-user-avatar"><?php echo esc_html( $initials ); ?></div>
					<div class="cgt-user-info">
						<span class="cgt-user-name"><?php echo esc_html( $user->display_name ); ?></span>
						<span class="cgt-user-role"><?php esc_html_e( 'Assistance', 'cgt' ); ?></span>
					</div>
				</div>
				<a href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>" class="cgt-btn-logout">
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

	<!-- Navigation tabs -->
	<div class="cgt-space-tabs">
		<div class="cgt-space-tabs-container">
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
		<a href="?section=enquetes" class="cgt-tab <?php echo 'enquetes' === $current_section ? 'active' : ''; ?>">
			<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
				<path d="M11 19.4l-7-4.2V8.8l7-4.2 7 4.2v6.4z"></path>
				<path d="M11 10.3l7-4.2"></path>
				<path d="M4 6.1l7 4.2"></path>
				<path d="M11 18.9V22"></path>
			</svg>
			Enquêtes
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
				echo '<span class="cgt-tab-badge">' . esc_html( count( $pending_messages ) ) . '</span>';
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
	</div>

	<!-- Contenu principal -->
	<div class="cgt-space-container">
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
			case 'enquetes':
				include __DIR__ . '/inc/assistance/enquetes.php';
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


<?php get_footer(); ?>
