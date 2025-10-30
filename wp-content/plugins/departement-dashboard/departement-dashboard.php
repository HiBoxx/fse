<?php
/**
 * Plugin Name: Département Dashboard
 * Description: Tableaux de bord dédiés pour les équipes Administration, Gestionnaire et Assistante.
 * Version: 0.1.0
 * Author: CGT Child Theme
 * Text Domain: departement-dashboard
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'CGT_DD_PATH' ) ) {
	define( 'CGT_DD_PATH', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'CGT_DD_URL' ) ) {
	define( 'CGT_DD_URL', plugin_dir_url( __FILE__ ) );
}

require_once CGT_DD_PATH . 'includes/helpers.php';
require_once CGT_DD_PATH . 'includes/class-dashboard-roles.php';
require_once CGT_DD_PATH . 'includes/class-dashboard-router.php';
require_once CGT_DD_PATH . 'includes/class-dashboard-views.php';
require_once CGT_DD_PATH . 'includes/class-dashboard-admin.php';
require_once CGT_DD_PATH . 'includes/class-dashboard-cpt.php';

/**
 * Activation tasks.
 */
function cgt_dd_activate() {
	\CGT\Dashboard\Roles::register_roles();
	\CGT\Dashboard\Roles::ensure_users();
	\CGT\Dashboard\Router::register_routes();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'cgt_dd_activate' );

/**
 * Deactivation cleanup.
 */
function cgt_dd_deactivate() {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'cgt_dd_deactivate' );

/**
 * Bootstrap plugin.
 */
function cgt_dd_bootstrap() {
	$roles  = new \CGT\Dashboard\Roles();
	$router = new \CGT\Dashboard\Router();
	$views  = new \CGT\Dashboard\Views();
	$admin  = new \CGT\Dashboard\Admin();
	$cpt    = new \CGT\Dashboard\CPT();

	$roles->hooks();
	$router->hooks();
	$views->hooks();
	$admin->hooks();
	$cpt->hooks();
}
add_action( 'plugins_loaded', 'cgt_dd_bootstrap' );
