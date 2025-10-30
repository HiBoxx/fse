<?php

namespace CGT\Dashboard;

defined( 'ABSPATH' ) || exit;

class CPT {

	public function hooks() {
		add_action( 'init', array( $this, 'register_cpts' ) );
	}

	public function register_cpts() {
		register_post_type(
			'cgt_mandat',
			array(
				'label'               => __( 'Mandats bancaires', 'departement-dashboard' ),
				'labels'              => array(
					'name'          => __( 'Mandats bancaires', 'departement-dashboard' ),
					'singular_name' => __( 'Mandat bancaire', 'departement-dashboard' ),
				),
				'public'              => false,
				'show_ui'             => true,
				'show_in_menu'        => false,
				'supports'            => array( 'title', 'author' ),
				'capability_type'     => 'cgt_mandat',
				'capabilities'        => array(
					'edit_post'          => 'edit_cgt_mandat',
					'read_post'          => 'read_cgt_mandat',
					'delete_post'        => 'delete_cgt_mandat',
					'edit_posts'         => 'edit_cgt_mandats',
					'edit_others_posts'  => 'edit_others_cgt_mandats',
					'publish_posts'      => 'publish_cgt_mandats',
					'read_private_posts' => 'read_private_cgt_mandats',
					'delete_posts'       => 'delete_cgt_mandats',
					'delete_others_posts'=> 'delete_others_cgt_mandats',
				),
				'map_meta_cap'        => true,
			)
		);
	}
}
