<?php
/**
 * Templating helpers and activation routines.
 *
 * @package CGT_Child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Load custom templates from the /templates directory.
 *
 * @param string $template Template path.
 * @return string
 */
function cgt_template_loader( $template ) {
	$page_template = get_page_template_slug();
	$map           = array(
		'archive-communique.php'   => is_post_type_archive( 'communiques_de_presse' ),
		'single-communique.php'    => is_singular( 'communiques_de_presse' ),
		'archive-tract.php'        => is_post_type_archive( 'tracts' ),
		'archive-dossier.php'      => is_post_type_archive( 'dossiers_de_presse' ),
		'page-espace-adherent.php' => is_page( 'espace-adherent' ) || 'templates/page-espace-adherent.php' === $page_template,
		'page-espace-presse.php'   => is_page( 'espace-presse' ) || 'templates/page-espace-presse.php' === $page_template,
		'page-branches.php'        => is_page( 'branches' ) || 'templates/page-branches.php' === $page_template,
		'single-branch.php'        => is_singular( 'branch' ),
	);

	foreach ( $map as $file => $condition ) {
		if ( $condition ) {
			$path = get_stylesheet_directory() . '/templates/' . $file;
			if ( file_exists( $path ) ) {
				return $path;
			}
		}
	}

	return $template;
}
add_filter( 'template_include', 'cgt_template_loader' );

/**
 * Fallback for the primary menu.
 */
function cgt_wp_menu_fallback() {
	echo '<ul class="primary-menu">';
	wp_list_pages(
		array(
			'title_li' => '',
			'depth'    => 1,
		)
	);
	echo '</ul>';
}

/**
 * Register block patterns by including files located in /patterns.
 */
function cgt_register_block_patterns() {
	$pattern_dir = get_stylesheet_directory() . '/patterns';
	if ( ! is_dir( $pattern_dir ) ) {
		return;
	}

	foreach ( glob( $pattern_dir . '/*.php' ) as $pattern_file ) {
		require $pattern_file;
	}
}
add_action( 'init', 'cgt_register_block_patterns', 12 );

/**
 * Activation routine.
 */
function cgt_after_switch_theme() { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundInExtendedClass
	cgt_register_adherent_role();
	cgt_create_demo_adherent_user();
	cgt_setup_pages_and_menus();
	cgt_seed_demo_content();
	flush_rewrite_rules();
}

/**
 * Create demo adherent user if absent.
 */
function cgt_create_demo_adherent_user() {
	if ( get_user_by( 'login', 'demo_adherent' ) ) {
		return;
	}

	$user_id = wp_create_user( 'demo_adherent', 'Demo@123456', 'demo_adherent@example.com' );
	if ( is_wp_error( $user_id ) ) {
		return;
	}

	$user = new WP_User( $user_id );
	$user->set_role( 'adherent' );
}

/**
 * Ensure core pages & menus exist.
 */
function cgt_setup_pages_and_menus() {
	$page_definitions = array(
		'accueil'             => array(
			'post_title'   => __( 'Accueil', 'cgt' ),
			'post_content' => '<p>' . esc_html__( 'Bienvenue sur le site de la Fédération CGT.', 'cgt' ) . '</p>',
			'post_status'  => 'publish',
		),
		'la-federation'       => array(
			'post_title'        => __( 'La CGT en France', 'cgt' ),
			'_wp_page_template' => 'templates/page-federation.php',
		),
		'materiels'           => array(
			'post_title'   => __( 'Matériels', 'cgt' ),
			'post_content' => '<p>' . esc_html__( 'Supports militants, visuels et ressources à télécharger.', 'cgt' ) . '</p>',
		),
		'branches'            => array(
			'post_title'   => __( 'Branches', 'cgt' ),
			'_wp_page_template' => 'templates/page-branches.php',
		),
		'international'       => array(
			'post_title'   => __( 'International', 'cgt' ),
			'post_content' => '<div class="placeholder" aria-hidden="true"></div><p>' . esc_html__( 'Actions internationales de la CGT.', 'cgt' ) . '</p>',
		),
		'espace-presse'       => array(
			'post_title'        => __( 'Espace Presse', 'cgt' ),
			'_wp_page_template' => 'templates/page-espace-presse.php',
		),
		'espace-adherent'     => array(
			'post_title'        => __( 'Espace Adhérent', 'cgt' ),
			'_wp_page_template' => 'templates/page-espace-adherent.php',
		),
		'publier-article'     => array(
			'post_title'   => __( 'Proposer un article', 'cgt' ),
			'post_content' => '[cgt_submit_article]',
		),
		'mediatheque'         => array(
			'post_title'   => __( 'Médiathèque', 'cgt' ),
			'post_content' => '<div class="placeholder" aria-hidden="true"></div><p>' . esc_html__( 'Logos, photos officielles et éléments de charte graphique.', 'cgt' ) . '</p>',
		),
		'contacts-presse'     => array(
			'post_title'   => __( 'Contacts presse', 'cgt' ),
			'post_content' => '<p>' . esc_html__( 'Coordonnées presse nationales et régionales.', 'cgt' ) . '</p>',
		),
		'contact'             => array(
			'post_title'        => __( 'Contact', 'cgt' ),
			'_wp_page_template' => 'templates/page-contact.php',
		),
		'mentions-legales'    => array(
			'post_title'   => __( 'Mentions légales', 'cgt' ),
		),
		'politique-de-confidentialite' => array(
			'post_title'   => __( 'Politique de confidentialité', 'cgt' ),
		),
	);

	$page_ids = array();

	foreach ( $page_definitions as $slug => $page_args ) {
		$existing = get_page_by_path( $slug, OBJECT, 'page' );
	if ( $existing ) {
		$page_ids[ $slug ] = $existing->ID;
		if ( isset( $page_args['_wp_page_template'] ) ) {
			update_post_meta( $existing->ID, '_wp_page_template', $page_args['_wp_page_template'] );
		}
		continue;
	}

		$page_data = wp_parse_args(
			$page_args,
			array(
				'post_type'   => 'page',
				'post_name'   => $slug,
				'post_status' => 'publish',
			)
		);

		$page_id = wp_insert_post( $page_data );
		if ( ! is_wp_error( $page_id ) ) {
			$page_ids[ $slug ] = $page_id;
			if ( isset( $page_args['_wp_page_template'] ) ) {
				update_post_meta( $page_id, '_wp_page_template', $page_args['_wp_page_template'] );
			}
		}
	}

	if ( ! isset( $page_ids['la-federation'] ) ) {
		$legacy = get_page_by_path( 'la-cgt-en-france', OBJECT, 'page' );
		if ( $legacy ) {
			wp_update_post(
				array(
					'ID'         => $legacy->ID,
					'post_name'  => 'la-federation',
					'post_title' => __( 'La CGT en France', 'cgt' ),
				)
			);
			$page_ids['la-federation'] = $legacy->ID;
		}
	}

	if ( isset( $page_ids['accueil'] ) ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $page_ids['accueil'] );
	}

	$primary_menu = wp_get_nav_menu_object( 'Principal CGT' );
	if ( ! $primary_menu ) {
		$primary_menu = wp_create_nav_menu( 'Principal CGT' );
	}

	if ( is_wp_error( $primary_menu ) ) {
		$primary_menu_id = 0;
	} elseif ( is_object( $primary_menu ) ) {
		$primary_menu_id = (int) $primary_menu->term_id;
	} else {
		$primary_menu_id = (int) $primary_menu;
		$primary_menu    = wp_get_nav_menu_object( $primary_menu_id );
	}

	$footer_menu = wp_get_nav_menu_object( 'Pied de page CGT' );
	if ( ! $footer_menu ) {
		$footer_menu = wp_create_nav_menu( 'Pied de page CGT' );
	}

	if ( is_wp_error( $footer_menu ) ) {
		$footer_menu_id = 0;
	} elseif ( is_object( $footer_menu ) ) {
		$footer_menu_id = (int) $footer_menu->term_id;
	} else {
		$footer_menu_id = (int) $footer_menu;
		$footer_menu    = wp_get_nav_menu_object( $footer_menu_id );
	}

	if ( $primary_menu_id && ! is_wp_error( $primary_menu ) ) {
		$items = wp_get_nav_menu_items( $primary_menu_id );
		if ( ! empty( $items ) ) {
			foreach ( $items as $item ) {
				wp_delete_post( $item->ID, true );
			}
		}

		$order       = array( 'accueil', 'la-federation', 'materiels', 'contact', 'espace-adherent' );
		$position    = 1;
		foreach ( $order as $slug ) {
			if ( isset( $page_ids[ $slug ] ) ) {
				$item_args = array(
					'menu-item-object-id' => $page_ids[ $slug ],
					'menu-item-object'    => 'page',
					'menu-item-type'      => 'post_type',
					'menu-item-status'    => 'publish',
					'menu-item-position'  => $position,
				);

				if ( 'espace-adherent' === $slug ) {
					$item_args['menu-item-classes'] = 'menu-item--cta';
				}

				if ( 'materiels' === $slug ) {
					$item_args['menu-item-classes'] = 'menu-item--drawer';
				}

				wp_update_nav_menu_item(
					$primary_menu_id,
					0,
					$item_args
				);
				$position++;
			}
		}
	}

	if ( $footer_menu_id && ! is_wp_error( $footer_menu ) ) {
		$items = wp_get_nav_menu_items( $footer_menu_id );
		if ( empty( $items ) ) {
			foreach ( array( 'mentions-legales', 'politique-de-confidentialite', 'contact' ) as $slug ) {
				if ( isset( $page_ids[ $slug ] ) ) {
					wp_update_nav_menu_item(
						$footer_menu_id,
						0,
						array(
							'menu-item-object-id' => $page_ids[ $slug ],
							'menu-item-object'    => 'page',
							'menu-item-type'      => 'post_type',
							'menu-item-status'    => 'publish',
						)
					);
				}
			}
		}
	}

	set_theme_mod(
		'nav_menu_locations',
		array(
			'primary' => $primary_menu_id,
			'footer'  => $footer_menu_id,
		)
	);
}

/**
 * Seed demo content demanded by specs.
 */
function cgt_seed_demo_content() {
	$terms = array(
		'branche' => array(
			array( 'slug' => 'enseignement-superieur', 'name' => __( 'Enseignement supérieur', 'cgt' ) ),
			array( 'slug' => 'recherche', 'name' => __( 'Recherche', 'cgt' ) ),
		),
		'thematique' => array(
			array( 'slug' => 'droits-sociaux', 'name' => __( 'Droits sociaux', 'cgt' ) ),
			array( 'slug' => 'negociation', 'name' => __( 'Négociation', 'cgt' ) ),
		),
		'zone_internationale' => array(
			array( 'slug' => 'europe', 'name' => __( 'Europe', 'cgt' ) ),
			array( 'slug' => 'ameriques', 'name' => __( 'Amériques', 'cgt' ) ),
		),
	);

	foreach ( $terms as $taxonomy => $definitions ) {
		foreach ( $definitions as $definition ) {
			if ( ! term_exists( $definition['slug'], $taxonomy ) ) {
				wp_insert_term( $definition['name'], $taxonomy, array( 'slug' => $definition['slug'] ) );
			}
		}
	}

	$communiques = array(
		array(
			'slug'    => 'communique-salaires',
			'title'   => __( 'La CGT réclame une revalorisation immédiate des salaires', 'cgt' ),
			'excerpt' => __( 'La fédération alerte sur l’urgence sociale dans l’enseignement supérieur.', 'cgt' ),
			'meta'    => array(
				'cgt_chapo'    => __( 'Revaloriser les salaires est une urgence sociale.', 'cgt' ),
				'cgt_porteur'  => __( 'Syndicat CGT FSE', 'cgt' ),
				'cgt_embargo'  => date_i18n( 'Y-m-d\TH:i', strtotime( '+1 day' ) ),
			),
		),
		array(
			'slug'    => 'communique-conditions-travail',
			'title'   => __( 'Conditions de travail : la CGT interpelle le ministère', 'cgt' ),
			'excerpt' => __( 'La CGT appelle à une rencontre urgente pour améliorer les conditions de travail.', 'cgt' ),
			'meta'    => array(
				'cgt_chapo'    => __( 'Des milliers d’agents témoignent de situations dégradées.', 'cgt' ),
				'cgt_porteur'  => __( 'Collectif branches', 'cgt' ),
				'cgt_embargo'  => '',
			),
		),
		array(
			'slug'    => 'communique-international',
			'title'   => __( 'Solidarité internationale des personnels de la recherche', 'cgt' ),
			'excerpt' => __( 'La CGT s’engage auprès des personnels en lutte à l’international.', 'cgt' ),
			'meta'    => array(
				'cgt_chapo'    => __( 'La solidarité est au cœur de l’action syndicale.', 'cgt' ),
				'cgt_porteur'  => __( 'Commission internationale', 'cgt' ),
				'cgt_embargo'  => '',
			),
		),
	);

	foreach ( $communiques as $data ) {
		if ( get_page_by_path( $data['slug'], OBJECT, 'communiques_de_presse' ) ) {
			continue;
		}

		$post_id = wp_insert_post(
			array(
				'post_type'    => 'communiques_de_presse',
				'post_title'   => $data['title'],
				'post_name'    => $data['slug'],
				'post_status'  => 'publish',
				'post_excerpt' => $data['excerpt'],
				'post_content' => '<p>' . esc_html__( 'Contenu du communiqué à rédiger.', 'cgt' ) . '</p><div class="placeholder" aria-hidden="true"></div>',
			)
		);

		if ( $post_id && ! is_wp_error( $post_id ) ) {
			foreach ( $data['meta'] as $key => $value ) {
				update_post_meta( $post_id, $key, $value );
			}
			wp_set_post_terms( $post_id, array( 'enseignement-superieur' ), 'branche', false );
			wp_set_post_terms( $post_id, array( 'droits-sociaux' ), 'thematique', false );
		}
	}

	$tracts = array(
		array(
			'slug'     => 'tract-mobilisation-janvier',
			'title'    => __( 'Tract mobilisation de janvier', 'cgt' ),
			'status'   => 'publish',
			'meta'     => array(
				'cgt_fichier_pdf' => 'https://example.com/tract-janvier.pdf',
				'cgt_visibilite'  => 'public',
			),
		),
		array(
			'slug'     => 'tract-negociations-prives',
			'title'    => __( 'Négociations internes (privé adhérents)', 'cgt' ),
			'status'   => 'publish',
			'meta'     => array(
				'cgt_fichier_pdf' => 'https://example.com/tract-prive.pdf',
				'cgt_visibilite'  => 'prive',
			),
		),
	);

	foreach ( $tracts as $data ) {
		if ( get_page_by_path( $data['slug'], OBJECT, 'tracts' ) ) {
			continue;
		}

		$post_id = wp_insert_post(
			array(
				'post_type'    => 'tracts',
				'post_title'   => $data['title'],
				'post_name'    => $data['slug'],
				'post_status'  => $data['status'],
			'post_excerpt' => '',
				'post_content' => '<p>' . esc_html__( 'Télécharger le tract en cliquant sur le bouton ci-dessous.', 'cgt' ) . '</p>',
			)
		);

		if ( $post_id && ! is_wp_error( $post_id ) ) {
			foreach ( $data['meta'] as $key => $value ) {
				update_post_meta( $post_id, $key, $value );
			}
			wp_set_post_terms( $post_id, array( 'recherche' ), 'branche', false );
			wp_set_post_terms( $post_id, array( 'negociation' ), 'thematique', false );
		}
	}

	if ( ! get_page_by_path( 'article-interne-adherents', OBJECT, 'articles_adherents' ) ) {
		wp_insert_post(
			array(
				'post_type'    => 'articles_adherents',
				'post_title'   => __( 'Analyse interne réservée aux adhérents', 'cgt' ),
				'post_name'    => 'article-interne-adherents',
				'post_status'  => 'private',
				'post_excerpt' => __( 'Analyse des dossiers en cours, réservée aux adhérents CGT.', 'cgt' ),
				'post_content' => '<p>' . esc_html__( 'Ce contenu est réservé aux adhérents disposant d’un accès authentifié.', 'cgt' ) . '</p>',
			)
		);
	}
}

/**
 * Ensure primary menu matches the new specification.
 */
function cgt_sync_primary_menu_once() {
	$current_version = '20240710';

	if ( get_option( 'cgt_primary_menu_version' ) === $current_version ) {
		return;
	}

	cgt_setup_pages_and_menus();
	update_option( 'cgt_primary_menu_version', $current_version );
}
add_action( 'init', 'cgt_sync_primary_menu_once', 20 );

/**
 * Seed demo posts for each branch term.
 */
function cgt_seed_branch_demo_posts_once() {
	$current_version = '20240716';

	if ( get_option( 'cgt_branch_demo_version' ) === $current_version ) {
		return;
	}

	$branches = get_terms(
		array(
			'taxonomy'   => 'branche',
			'hide_empty' => false,
		)
	);

	if ( ! is_wp_error( $branches ) ) {
		foreach ( $branches as $branch ) {
			$slug      = 'demo-article-' . sanitize_title( $branch->slug );
			$existing  = get_page_by_path( $slug, OBJECT, array( 'post' ) );
			if ( $existing ) {
				continue;
			}

			$post_id = wp_insert_post(
				array(
					'post_type'    => 'post',
					'post_title'   => sprintf( __( 'Actualité de la branche %s', 'cgt' ), $branch->name ),
					'post_name'    => $slug,
					'post_status'  => 'publish',
					'post_excerpt' => __( 'Exemple de contenu mettant en avant les actions de cette branche.', 'cgt' ),
					'post_content' => '<p>' . esc_html__( 'Cet article de démonstration illustre la mise en page d’une actualité de branche.', 'cgt' ) . '</p><div class="placeholder" aria-hidden="true"></div><p>' . esc_html__( 'Remplacez ce texte par vos informations syndicales, actions locales, réunions et ressources utiles.', 'cgt' ) . '</p>',
				)
			);

			if ( $post_id && ! is_wp_error( $post_id ) ) {
				wp_set_post_terms( $post_id, array( $branch->term_id ), 'branche', false );
			}
		}
	}

	update_option( 'cgt_branch_demo_version', $current_version );
}
add_action( 'init', 'cgt_seed_branch_demo_posts_once', 25 );

/**
 * Seed demo communiqués.
 */
function cgt_seed_communique_demo_posts_once() {
	$current_version = '20240715';

	if ( get_option( 'cgt_communique_demo_version' ) === $current_version ) {
		return;
	}

	$demo_posts = array(
		array(
			'slug'    => 'demo-communique-revalorisation',
			'title'   => __( 'Revalorisation des personnels : communiqué de la CGT', 'cgt' ),
			'excerpt' => __( 'La CGT appelle à une revalorisation immédiate des salaires et des carrières.', 'cgt' ),
			'content' => '<p>' . esc_html__( 'Communiqué de démonstration présentant une prise de position de la CGT sur la revalorisation salariale.', 'cgt' ) . '</p><div class="placeholder" aria-hidden="true"></div><p>' . esc_html__( 'Ajoutez ici votre contenu complet, citations, liens vers des ressources et documents téléchargeables.', 'cgt' ) . '</p>',
		),
		array(
			'slug'    => 'demo-communique-conditions-travail',
			'title'   => __( 'Conditions de travail : alerte de la CGT', 'cgt' ),
			'excerpt' => __( 'Exemple de communiqué soulignant les conditions de travail et les actions en cours.', 'cgt' ),
			'content' => '<p>' . esc_html__( 'Ce communiqué de test met en lumière les revendications sur les conditions de travail et les actions de la CGT.', 'cgt' ) . '</p><div class="placeholder" aria-hidden="true"></div><p>' . esc_html__( 'Complétez ce texte avec vos données, contacts presse et documents à télécharger.', 'cgt' ) . '</p>',
		),
		array(
			'slug'    => 'demo-communique-soutien-international',
			'title'   => __( 'Solidarité internationale : la CGT en action', 'cgt' ),
			'excerpt' => __( 'Un exemple de communiqué mettant en avant les coopérations internationales.', 'cgt' ),
			'content' => '<p>' . esc_html__( 'Ce communiqué illustre les actions de solidarité internationale conduites par la fédération.', 'cgt' ) . '</p><div class="placeholder" aria-hidden="true"></div><p>' . esc_html__( 'Décrivez ici les partenariats, les campagnes et les soutiens concrets apportés aux personnels.', 'cgt' ) . '</p>',
		),
		array(
			'slug'    => 'demo-communique-negociations',
			'title'   => __( 'Négociations en cours : revendications de la CGT', 'cgt' ),
			'excerpt' => __( 'Communiqué fictif présentant l’avancée des négociations nationales.', 'cgt' ),
			'content' => '<p>' . esc_html__( 'Exposez ici les points abordés lors des négociations et les propositions portées par la CGT.', 'cgt' ) . '</p><div class="placeholder" aria-hidden="true"></div><p>' . esc_html__( 'Ajoutez des citations de porte-paroles, des objectifs chiffrés et les prochaines échéances.', 'cgt' ) . '</p>',
		),
		array(
			'slug'    => 'demo-communique-appel-mobilisation',
			'title'   => __( 'Appel à mobilisation : rassemblement national', 'cgt' ),
			'excerpt' => __( 'Annonce d’une mobilisation fictive pour illustrer la mise en page du site.', 'cgt' ),
			'content' => '<p>' . esc_html__( 'Utilisez ce communiqué de démonstration pour annoncer vos mobilisations et rendez-vous militants.', 'cgt' ) . '</p><div class="placeholder" aria-hidden="true"></div><p>' . esc_html__( 'Précisez les dates, lieux, revendications principales et moyens de participer.', 'cgt' ) . '</p>',
		),
	);

	foreach ( $demo_posts as $data ) {
		$existing = get_page_by_path( $data['slug'], OBJECT, 'communiques_de_presse' );
		if ( $existing ) {
			continue;
		}

		wp_insert_post(
			array(
				'post_type'    => 'communiques_de_presse',
				'post_title'   => $data['title'],
				'post_name'    => $data['slug'],
				'post_status'  => 'publish',
				'post_excerpt' => $data['excerpt'],
				'post_content' => $data['content'],
			)
		);
	}

	update_option( 'cgt_communique_demo_version', $current_version );
}
add_action( 'init', 'cgt_seed_communique_demo_posts_once', 26 );
