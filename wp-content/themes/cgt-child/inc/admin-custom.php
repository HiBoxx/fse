<?php
/**
 * Custom Admin Interface
 * Complete WordPress admin redesign with CGT branding
 *
 * @package CGT_Child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Enqueue custom admin styles for all admin pages
 */
add_action( 'admin_enqueue_scripts', 'cgt_enqueue_global_admin_styles' );

function cgt_enqueue_global_admin_styles() {
	wp_enqueue_style(
		'cgt-admin-global',
		get_stylesheet_directory_uri() . '/assets/css/admin-global.css',
		array(),
		filemtime( get_stylesheet_directory() . '/assets/css/admin-global.css' )
	);
}

/**
 * Enqueue custom login page styles
 */
add_action( 'login_enqueue_scripts', 'cgt_enqueue_login_styles' );

function cgt_enqueue_login_styles() {
	wp_enqueue_style(
		'cgt-admin-global',
		get_stylesheet_directory_uri() . '/assets/css/admin-global.css',
		array(),
		filemtime( get_stylesheet_directory() . '/assets/css/admin-global.css' )
	);
}

/**
 * Change login logo URL
 */
add_filter( 'login_headerurl', 'cgt_login_logo_url' );

function cgt_login_logo_url() {
	return home_url();
}

/**
 * Change login logo title
 */
add_filter( 'login_headertext', 'cgt_login_logo_url_title' );

function cgt_login_logo_url_title() {
	return 'CGT Fédération des Sociétés d\'Études';
}

/**
 * Custom admin footer text
 */
add_filter( 'admin_footer_text', 'cgt_admin_footer_text' );

function cgt_admin_footer_text() {
	return '<span id="footer-thankyou">CGT Fédération des Sociétés d\'Études</span>';
}

/**
 * Remove WordPress version from admin footer
 */
add_filter( 'update_footer', 'cgt_remove_footer_version', 11 );

function cgt_remove_footer_version() {
	return '';
}

/**
 * Add custom dashboard welcome message
 */
add_action( 'wp_dashboard_setup', 'cgt_add_dashboard_welcome_widget' );

function cgt_add_dashboard_welcome_widget() {
	wp_add_dashboard_widget(
		'cgt_dashboard_welcome',
		'Bienvenue dans l\'administration CGT',
		'cgt_dashboard_welcome_content'
	);
}

function cgt_dashboard_welcome_content() {
	$current_user = wp_get_current_user();
	?>
	<div style="padding: 20px;">
		<h2 style="color: #8B1538; margin-top: 0;">Bonjour <?php echo esc_html( $current_user->display_name ); ?> 👋</h2>
		<p style="font-size: 15px; line-height: 1.6; color: #555;">
			Bienvenue sur le tableau de bord de la <strong>CGT Fédération des Sociétés d'Études</strong>.
		</p>
		<div style="background: #f8f8f8; padding: 15px; border-radius: 8px; margin-top: 15px; border-left: 4px solid #8B1538;">
			<h3 style="margin-top: 0; color: #8B1538;">Accès rapide</h3>
			<ul style="margin: 0; padding-left: 20px;">
				<li style="margin-bottom: 8px;">
					<a href="<?php echo admin_url( 'edit.php?post_type=cgt_adhesion' ); ?>" style="color: #8B1538; text-decoration: none; font-weight: 500;">
						Gérer les adhésions
					</a>
				</li>
				<li style="margin-bottom: 8px;">
					<a href="<?php echo admin_url( 'edit.php?post_type=page' ); ?>" style="color: #8B1538; text-decoration: none; font-weight: 500;">
						Gérer les pages
					</a>
				</li>
				<li style="margin-bottom: 8px;">
					<a href="<?php echo admin_url( 'edit.php' ); ?>" style="color: #8B1538; text-decoration: none; font-weight: 500;">
						Gérer les articles
					</a>
				</li>
			</ul>
		</div>
	</div>
	<?php
}

/**
 * Customize admin color scheme (optional - users can still change it)
 */
add_action( 'user_register', 'cgt_set_default_admin_color' );
add_action( 'profile_update', 'cgt_set_default_admin_color' );

function cgt_set_default_admin_color( $user_id ) {
	$args = array(
		'ID'          => $user_id,
		'admin_color' => 'midnight', // Using midnight as base - will be overridden by custom CSS
	);
	wp_update_user( $args );
}

/**
 * Remove default WordPress dashboard widgets
 */
add_action( 'wp_dashboard_setup', 'cgt_remove_default_dashboard_widgets' );

function cgt_remove_default_dashboard_widgets() {
	// Remove welcome panel
	remove_action( 'welcome_panel', 'wp_welcome_panel' );

	// Remove other default widgets (optional - uncomment if needed)
	// remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
	// remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );
	// remove_meta_box( 'dashboard_activity', 'dashboard', 'normal' );
}

/**
 * Add custom CSS to Gutenberg editor
 */
add_action( 'enqueue_block_editor_assets', 'cgt_enqueue_editor_styles' );

function cgt_enqueue_editor_styles() {
	wp_enqueue_style(
		'cgt-editor-styles',
		get_stylesheet_directory_uri() . '/assets/css/admin-global.css',
		array(),
		filemtime( get_stylesheet_directory() . '/assets/css/admin-global.css' )
	);
}
