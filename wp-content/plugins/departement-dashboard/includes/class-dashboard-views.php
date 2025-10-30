<?php

namespace CGT\Dashboard;

defined( 'ABSPATH' ) || exit;

class Views {

	public function hooks() {
		add_filter( 'cgt_dd_dashboard_context', array( $this, 'augment_context' ), 10, 2 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	public function enqueue_assets() {
		if ( ! get_query_var( Router::QUERY_VAR ) ) {
			return;
		}

		wp_enqueue_style(
			'cgt-dd-tailwind',
			'https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css',
			array(),
			'2.2.19'
		);

		wp_enqueue_script(
			'cgt-dd-alpine',
			'https://cdn.jsdelivr.net/npm/alpinejs@3.13.5/dist/cdn.min.js',
			array(),
			'3.13.5',
			true
		);
	}

	public function augment_context( $context, $route ) {
		switch ( $route ) {
			case 'admin':
				$context['adhesions'] = $this->get_adhesions();
				$context['mandats']   = $this->get_mandats();
				break;
			case 'gestionnaire':
				$context['post_types'] = $this->get_publishable_types();
				break;
			case 'assistante':
				$context['adhesions'] = $this->get_adhesions();
				break;
		}

		return $context;
	}

	private function get_adhesions() {
		$args = array(
			'post_type'      => 'cgt_adhesion',
			'posts_per_page' => 100,
			'post_status'    => array( 'publish', 'private' ),
			'orderby'        => 'date',
			'order'          => 'DESC',
		);

		$query = new \WP_Query( $args );
		$data  = array();

		foreach ( $query->posts as $post ) {
			$data[] = array(
				'id'         => $post->ID,
				'title'      => get_the_title( $post ),
				'date'       => get_the_date( 'd/m/Y', $post ),
				'email'      => get_post_meta( $post->ID, 'cgt_adhesion_email', true ),
				'status'     => $post->post_status,
				'permalink'  => get_edit_post_link( $post->ID ),
			);
		}

		wp_reset_postdata();
		return $data;
	}

	private function get_mandats() {
		$args = array(
			'post_type'      => 'cgt_mandat',
			'posts_per_page' => 100,
			'post_status'    => array( 'publish', 'private' ),
			'orderby'        => 'date',
			'order'          => 'DESC',
		);

		$query = new \WP_Query( $args );
		$data  = array();

		foreach ( $query->posts as $post ) {
			$data[] = array(
				'id'        => $post->ID,
				'title'     => get_the_title( $post ),
				'date'      => get_the_date( 'd/m/Y', $post ),
				'rib'       => get_post_meta( $post->ID, '_cgt_mandat_rib', true ),
				'mandat'    => get_post_meta( $post->ID, '_cgt_mandat_file', true ),
				'author_id' => $post->post_author,
			);
		}

		wp_reset_postdata();
		return $data;
	}

	private function get_publishable_types() {
		$types = array(
			'post'                    => __( 'Article', 'departement-dashboard' ),
			'tracts'                  => __( 'Tract', 'departement-dashboard' ),
			'communiques_de_presse'   => __( 'Pétition', 'departement-dashboard' ),
			'cgt_agenda'              => __( 'Événement', 'departement-dashboard' ),
		);

		return apply_filters( 'cgt_dd_publishable_types', $types );
	}
}
