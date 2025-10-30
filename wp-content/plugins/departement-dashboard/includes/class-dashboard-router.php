<?php

namespace CGT\Dashboard;

defined( 'ABSPATH' ) || exit;

class Router {

	const QUERY_VAR = 'cgt_dashboard';

	public static $routes = array(
		'admin'       => array(
			'role' => Roles::ROLE_ADMIN,
			'view' => 'dashboard-admin',
		),
		'gestionnaire'=> array(
			'role' => Roles::ROLE_GESTIONNAIRE,
			'view' => 'dashboard-gestionnaire',
		),
		'assistante'  => array(
			'role' => Roles::ROLE_ASSISTANTE,
			'view' => 'dashboard-assistante',
		),
		'login-test'  => array(
			'role' => '',
			'view' => 'login-test',
		),
	);

	public function hooks() {
		add_action( 'init', array( $this, 'register_routes' ) );
		add_filter( 'query_vars', array( $this, 'add_query_vars' ) );
		add_action( 'template_redirect', array( $this, 'maybe_render_dashboard' ) );
	}

	public static function register_routes() {
		add_rewrite_rule( '^dashboard/(admin|gestionnaire|assistante)/?$', 'index.php?' . self::QUERY_VAR . '=$matches[1]', 'top' );
		add_rewrite_rule( '^login-test/?$', 'index.php?' . self::QUERY_VAR . '=login-test', 'top' );
	}

	public function add_query_vars( $vars ) {
		$vars[] = self::QUERY_VAR;
		return $vars;
	}

	public function maybe_render_dashboard() {
		$route = get_query_var( self::QUERY_VAR );
		if ( ! $route ) {
			return;
		}

		if ( empty( self::$routes[ $route ] ) ) {
			return;
		}

		$config = self::$routes[ $route ];

		if ( ! is_user_logged_in() && 'login-test' !== $route ) {
			wp_safe_redirect( wp_login_url( home_url( "/dashboard/{$route}" ) ) );
			exit;
		}

		if ( ! empty( $config['role'] ) && ! current_user_can( 'administrator' ) ) {
			$user = wp_get_current_user();
			if ( ! in_array( $config['role'], (array) $user->roles, true ) ) {
				wp_die( esc_html__( 'Vous n’avez pas les droits nécessaires pour accéder à ce tableau de bord.', 'departement-dashboard' ), '', array( 'response' => 403 ) );
			}
		}

		$context = apply_filters(
			'cgt_dd_dashboard_context',
			array(
				'route' => $route,
				'user'  => wp_get_current_user(),
			),
			$route
		);

		status_header( 200 );
		nocache_headers();

		render( $config['view'], $context );
		exit;
	}
}
