<?php
/**
 * Réorganisation du menu admin WordPress
 * Regroupe Apparence, Extensions, Outils et Réglages dans un menu "Admin"
 *
 * @package CGT_Child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Réorganiser le menu admin
 */
function cgt_reorganize_admin_menu() {
	global $menu, $submenu;

	// Supprimer les menus principaux d'origine
	remove_menu_page( 'themes.php' );        // Apparence
	remove_menu_page( 'plugins.php' );       // Extensions
	remove_menu_page( 'tools.php' );         // Outils
	remove_menu_page( 'options-general.php' ); // Réglages

	// Créer le menu principal "Admin"
	add_menu_page(
		__( 'Administration', 'cgt' ),           // Page title
		__( 'Admin', 'cgt' ),                    // Menu title
		'manage_options',                        // Capability
		'cgt-admin',                             // Menu slug
		'cgt_render_admin_overview',            // Function
		'dashicons-admin-generic',               // Icon
		100                                      // Position (à la fin)
	);

	// Sous-menu : Apparence
	add_submenu_page(
		'cgt-admin',
		__( 'Apparence', 'cgt' ),
		'🎨 ' . __( 'Apparence', 'cgt' ),
		'switch_themes',
		'themes.php'
	);

	// Sous-menus de Apparence
	add_submenu_page(
		'cgt-admin',
		__( 'Thèmes', 'cgt' ),
		'&nbsp;&nbsp;&nbsp;→ ' . __( 'Thèmes', 'cgt' ),
		'switch_themes',
		'themes.php'
	);

	add_submenu_page(
		'cgt-admin',
		__( 'Personnaliser', 'cgt' ),
		'&nbsp;&nbsp;&nbsp;→ ' . __( 'Personnaliser', 'cgt' ),
		'customize',
		'customize.php'
	);

	add_submenu_page(
		'cgt-admin',
		__( 'Menus', 'cgt' ),
		'&nbsp;&nbsp;&nbsp;→ ' . __( 'Menus', 'cgt' ),
		'edit_theme_options',
		'nav-menus.php'
	);

	// Sous-menu : Extensions
	add_submenu_page(
		'cgt-admin',
		__( 'Extensions', 'cgt' ),
		'🔌 ' . __( 'Extensions', 'cgt' ),
		'activate_plugins',
		'plugins.php'
	);

	add_submenu_page(
		'cgt-admin',
		__( 'Extensions installées', 'cgt' ),
		'&nbsp;&nbsp;&nbsp;→ ' . __( 'Extensions installées', 'cgt' ),
		'activate_plugins',
		'plugins.php'
	);

	add_submenu_page(
		'cgt-admin',
		__( 'Ajouter une extension', 'cgt' ),
		'&nbsp;&nbsp;&nbsp;→ ' . __( 'Ajouter', 'cgt' ),
		'install_plugins',
		'plugin-install.php'
	);

	// Sous-menu : Outils
	add_submenu_page(
		'cgt-admin',
		__( 'Outils', 'cgt' ),
		'🛠️ ' . __( 'Outils', 'cgt' ),
		'manage_options',
		'tools.php'
	);

	add_submenu_page(
		'cgt-admin',
		__( 'Outils disponibles', 'cgt' ),
		'&nbsp;&nbsp;&nbsp;→ ' . __( 'Outils disponibles', 'cgt' ),
		'manage_options',
		'tools.php'
	);

	add_submenu_page(
		'cgt-admin',
		__( 'Importer', 'cgt' ),
		'&nbsp;&nbsp;&nbsp;→ ' . __( 'Importer', 'cgt' ),
		'import',
		'import.php'
	);

	add_submenu_page(
		'cgt-admin',
		__( 'Exporter', 'cgt' ),
		'&nbsp;&nbsp;&nbsp;→ ' . __( 'Exporter', 'cgt' ),
		'export',
		'export.php'
	);

	// Sous-menu : Réglages
	add_submenu_page(
		'cgt-admin',
		__( 'Réglages', 'cgt' ),
		'⚙️ ' . __( 'Réglages', 'cgt' ),
		'manage_options',
		'options-general.php'
	);

	add_submenu_page(
		'cgt-admin',
		__( 'Général', 'cgt' ),
		'&nbsp;&nbsp;&nbsp;→ ' . __( 'Général', 'cgt' ),
		'manage_options',
		'options-general.php'
	);

	add_submenu_page(
		'cgt-admin',
		__( 'Écriture', 'cgt' ),
		'&nbsp;&nbsp;&nbsp;→ ' . __( 'Écriture', 'cgt' ),
		'manage_options',
		'options-writing.php'
	);

	add_submenu_page(
		'cgt-admin',
		__( 'Lecture', 'cgt' ),
		'&nbsp;&nbsp;&nbsp;→ ' . __( 'Lecture', 'cgt' ),
		'manage_options',
		'options-reading.php'
	);

	add_submenu_page(
		'cgt-admin',
		__( 'Discussion', 'cgt' ),
		'&nbsp;&nbsp;&nbsp;→ ' . __( 'Discussion', 'cgt' ),
		'manage_options',
		'options-discussion.php'
	);

	add_submenu_page(
		'cgt-admin',
		__( 'Médias', 'cgt' ),
		'&nbsp;&nbsp;&nbsp;→ ' . __( 'Médias', 'cgt' ),
		'manage_options',
		'options-media.php'
	);

	add_submenu_page(
		'cgt-admin',
		__( 'Permaliens', 'cgt' ),
		'&nbsp;&nbsp;&nbsp;→ ' . __( 'Permaliens', 'cgt' ),
		'manage_options',
		'options-permalink.php'
	);

	add_submenu_page(
		'cgt-admin',
		__( 'Confidentialité', 'cgt' ),
		'&nbsp;&nbsp;&nbsp;→ ' . __( 'Confidentialité', 'cgt' ),
		'manage_privacy_options',
		'options-privacy.php'
	);
}
add_action( 'admin_menu', 'cgt_reorganize_admin_menu', 999 );

/**
 * Page d'aperçu du menu Admin
 */
function cgt_render_admin_overview() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Vous n\'avez pas les permissions nécessaires.', 'cgt' ) );
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Administration du site CGT', 'cgt' ); ?></h1>

		<div class="cgt-admin-overview">
			<p style="font-size: 16px; margin-bottom: 30px;">
				<?php esc_html_e( 'Bienvenue dans l\'espace d\'administration. Utilisez les sous-menus pour accéder aux différentes sections.', 'cgt' ); ?>
			</p>

			<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-top: 30px;">
				<!-- Apparence -->
				<div style="background: white; border: 1px solid #c3c4c7; border-radius: 8px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
					<h2 style="margin-top: 0; font-size: 18px; color: #c8102e;">
						🎨 <?php esc_html_e( 'Apparence', 'cgt' ); ?>
					</h2>
					<p><?php esc_html_e( 'Gérez les thèmes, menus et personnalisations visuelles du site.', 'cgt' ); ?></p>
					<a href="<?php echo esc_url( admin_url( 'themes.php' ) ); ?>" class="button button-primary">
						<?php esc_html_e( 'Accéder', 'cgt' ); ?>
					</a>
				</div>

				<!-- Extensions -->
				<div style="background: white; border: 1px solid #c3c4c7; border-radius: 8px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
					<h2 style="margin-top: 0; font-size: 18px; color: #c8102e;">
						🔌 <?php esc_html_e( 'Extensions', 'cgt' ); ?>
					</h2>
					<p><?php esc_html_e( 'Installez et gérez les plugins pour étendre les fonctionnalités.', 'cgt' ); ?></p>
					<a href="<?php echo esc_url( admin_url( 'plugins.php' ) ); ?>" class="button button-primary">
						<?php esc_html_e( 'Accéder', 'cgt' ); ?>
					</a>
				</div>

				<!-- Outils -->
				<div style="background: white; border: 1px solid #c3c4c7; border-radius: 8px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
					<h2 style="margin-top: 0; font-size: 18px; color: #c8102e;">
						🛠️ <?php esc_html_e( 'Outils', 'cgt' ); ?>
					</h2>
					<p><?php esc_html_e( 'Importez, exportez et utilisez les outils de maintenance.', 'cgt' ); ?></p>
					<a href="<?php echo esc_url( admin_url( 'tools.php' ) ); ?>" class="button button-primary">
						<?php esc_html_e( 'Accéder', 'cgt' ); ?>
					</a>
				</div>

				<!-- Réglages -->
				<div style="background: white; border: 1px solid #c3c4c7; border-radius: 8px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
					<h2 style="margin-top: 0; font-size: 18px; color: #c8102e;">
						⚙️ <?php esc_html_e( 'Réglages', 'cgt' ); ?>
					</h2>
					<p><?php esc_html_e( 'Configurez les paramètres généraux, lecture, écriture et permaliens.', 'cgt' ); ?></p>
					<a href="<?php echo esc_url( admin_url( 'options-general.php' ) ); ?>" class="button button-primary">
						<?php esc_html_e( 'Accéder', 'cgt' ); ?>
					</a>
				</div>
			</div>
		</div>
	</div>

	<style>
		.cgt-admin-overview .button-primary {
			background: linear-gradient(135deg, #c8102e 0%, #a00d26 100%);
			border-color: #c8102e;
			margin-top: 10px;
		}
		.cgt-admin-overview .button-primary:hover {
			background: #a00d26;
			border-color: #a00d26;
		}
	</style>
	<?php
}
