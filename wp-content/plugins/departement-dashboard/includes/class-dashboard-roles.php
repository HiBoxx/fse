<?php

namespace CGT\Dashboard;

defined( 'ABSPATH' ) || exit;

class Roles {

	const ROLE_ADMIN        = 'cgt_administration';
	const ROLE_GESTIONNAIRE = 'cgt_gestionnaire';
	const ROLE_ASSISTANTE   = 'cgt_assistante';

	/**
	 * Register WordPress hooks.
	 */
	public function hooks() {
		add_action( 'init', array( $this, 'register_roles' ) );
		add_action( 'init', array( $this, 'setup_caps' ), 15 );
	}

	/**
	 * Create custom roles.
	 */
	public static function register_roles() {
		if ( ! get_role( self::ROLE_ADMIN ) ) {
			add_role(
				self::ROLE_ADMIN,
				__( 'Administration', 'departement-dashboard' ),
				array(
					'read'                    => true,
					'edit_cgt_adhesion'       => true,
					'edit_cgt_adhesions'      => true,
					'delete_cgt_adhesions'    => true,
					'publish_cgt_adhesions'   => true,
					'upload_files'            => true,
					'edit_cgt_mandat'         => true,
					'edit_cgt_mandats'        => true,
					'edit_others_cgt_mandats' => true,
					'publish_cgt_mandats'     => true,
					'read_private_cgt_mandats'=> true,
					'delete_cgt_mandat'       => true,
					'delete_cgt_mandats'      => true,
					'delete_others_cgt_mandats'=> true,
					'list_users'              => true,
					'edit_users'              => true,
				)
			);
		}

		if ( ! get_role( self::ROLE_GESTIONNAIRE ) ) {
			add_role(
				self::ROLE_GESTIONNAIRE,
				__( 'Gestionnaire', 'departement-dashboard' ),
				array(
					'read'                   => true,
					'edit_posts'             => true,
					'edit_others_posts'      => true,
					'publish_posts'          => true,
					'upload_files'           => true,
					'edit_tracts'            => true,
					'publish_tracts'         => true,
					'edit_communiques_de_presse' => true,
					'publish_communiques_de_presse' => true,
					'edit_cgt_agenda'        => true,
					'publish_cgt_agenda'     => true,
				)
			);
		}

		if ( ! get_role( self::ROLE_ASSISTANTE ) ) {
			add_role(
				self::ROLE_ASSISTANTE,
				__( 'Assistante', 'departement-dashboard' ),
				array(
					'read'              => true,
					'cgt_view_adhesions'=> true,
				)
			);
		}
	}

	/**
	 * Ensure custom caps sync with existing CPTs.
	 */
	public function setup_caps() {
		$admin_caps = array(
			'edit_cgt_mandat',
			'edit_cgt_mandats',
			'edit_others_cgt_mandats',
			'publish_cgt_mandats',
			'read_private_cgt_mandats',
			'delete_cgt_mandat',
			'delete_cgt_mandats',
			'delete_others_cgt_mandats',
			'edit_cgt_adhesion',
			'edit_cgt_adhesions',
			'delete_cgt_adhesions',
			'publish_cgt_adhesions',
		);

		$role = get_role( self::ROLE_ADMIN );
		if ( $role ) {
			foreach ( $admin_caps as $cap ) {
				$role->add_cap( $cap );
			}
		}

		$assistant_cap = get_role( self::ROLE_ASSISTANTE );
		if ( $assistant_cap ) {
			$assistant_cap->add_cap( 'read' );
			$assistant_cap->add_cap( 'cgt_view_adhesions' );
		}
	}

	/**
	 * Create default users.
	 */
	public static function ensure_users() {
		self::create_user_if_missing( 'administration', 'admin123', self::ROLE_ADMIN, 'administration@fse.local' );
		self::create_user_if_missing( 'gestionnaire', 'gestion123', self::ROLE_GESTIONNAIRE, 'gestionnaire@fse.local' );
		self::create_user_if_missing( 'assistante', 'assist123', self::ROLE_ASSISTANTE, 'assistante@fse.local' );
	}

	private static function create_user_if_missing( $login, $password, $role, $email ) {
		if ( username_exists( $login ) ) {
			return;
		}

		$user_id = wp_insert_user(
			array(
				'user_login' => $login,
				'user_pass'  => $password,
				'user_email' => $email,
				'role'       => $role,
				'first_name' => ucfirst( $login ),
				'display_name' => ucfirst( $login ),
			)
		);

		if ( ! is_wp_error( $user_id ) ) {
			update_user_meta( $user_id, 'cgt_dd_temp_password', $password );
		}
	}
}
