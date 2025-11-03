<?php
/**
 * Admin Post Creation - Pages personnalisées pour remplacer l'éditeur WordPress
 * Remplace complètement les pages d'édition standard par une interface moderne
 *
 * @package CGT_Child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Cacher les liens "Ajouter nouveau" par défaut de WordPress et réorganiser
 */
add_action( 'admin_menu', 'cgt_customize_admin_menu', 999 );
function cgt_customize_admin_menu() {
	global $submenu;

	// Retirer "Ajouter" sous Articles
	if ( isset( $submenu['edit.php'] ) ) {
		foreach ( $submenu['edit.php'] as $key => $item ) {
			if ( $item[2] === 'post-new.php' ) {
				unset( $submenu['edit.php'][ $key ] );
			}
		}
	}

	// Retirer "Ajouter nouveau" sous Tracts
	if ( isset( $submenu['edit.php?post_type=tracts'] ) ) {
		foreach ( $submenu['edit.php?post_type=tracts'] as $key => $item ) {
			if ( $item[2] === 'post-new.php?post_type=tracts' ) {
				unset( $submenu['edit.php?post_type=tracts'][ $key ] );
			}
		}
	}

	// Retirer "Ajouter" sous Articles adhérents.
	if ( isset( $submenu['edit.php?post_type=articles_adherents'] ) ) {
		foreach ( $submenu['edit.php?post_type=articles_adherents'] as $key => $item ) {
			if ( in_array( $item[2], array( 'post-new.php?post_type=articles_adherents', 'post-new.php?post_type=articles_adherents&from=top' ), true ) ) {
				unset( $submenu['edit.php?post_type=articles_adherents'][ $key ] );
			}
		}
	}

	// Ajouter nos pages personnalisées EN PREMIER (position 0)
	add_submenu_page(
		'edit.php',
		__( 'Ajouter un article', 'cgt' ),
		__( 'Ajouter nouveau', 'cgt' ),
		'edit_posts',
		'cgt-add-article',
		'cgt_render_add_article_page',
		0
	);

	add_submenu_page(
		'edit.php?post_type=tracts',
		__( 'Ajouter un tract', 'cgt' ),
		__( 'Ajouter nouveau', 'cgt' ),
		'edit_posts',
		'cgt-add-tract',
		'cgt_render_add_tract_page',
		0
	);
}

/**
 * Réorganiser l'ordre des sous-menus pour mettre "Ajouter nouveau" en premier
 */
add_action( 'admin_menu', 'cgt_reorder_submenus', 999 );
function cgt_reorder_submenus() {
	global $submenu;

	// Réorganiser pour Articles
	if ( isset( $submenu['edit.php'] ) ) {
		$new_order = array();
		$add_new   = null;

		foreach ( $submenu['edit.php'] as $key => $item ) {
			if ( $item[2] === 'cgt-add-article' ) {
				$add_new = $item;
			} else {
				$new_order[ $key ] = $item;
			}
		}

		if ( $add_new ) {
			$submenu['edit.php'] = array_merge( array( 1 => $add_new ), $new_order );
		}
	}

	// Réorganiser pour Tracts
	if ( isset( $submenu['edit.php?post_type=tracts'] ) ) {
		$new_order = array();
		$add_new   = null;

		foreach ( $submenu['edit.php?post_type=tracts'] as $key => $item ) {
			if ( $item[2] === 'cgt-add-tract' ) {
				$add_new = $item;
			} else {
				$new_order[ $key ] = $item;
			}
		}

		if ( $add_new ) {
			$submenu['edit.php?post_type=tracts'] = array_merge( array( 1 => $add_new ), $new_order );
		}
	}
}

/**
 * Rediriger les accès directs à post-new.php et post.php vers nos pages personnalisées
 */
add_action( 'admin_init', 'cgt_redirect_new_post_pages' );
function cgt_redirect_new_post_pages() {
	global $pagenow;

	// Redirection création
	if ( 'post-new.php' === $pagenow ) {
		$post_type = isset( $_GET['post_type'] ) ? sanitize_key( $_GET['post_type'] ) : 'post';

		if ( 'post' === $post_type ) {
			wp_safe_redirect( admin_url( 'edit.php?page=cgt-add-article' ) );
			exit;
		} elseif ( 'tracts' === $post_type ) {
			wp_safe_redirect( admin_url( 'edit.php?post_type=tracts&page=cgt-add-tract' ) );
			exit;
		}
	}

	// Redirection édition
	if ( 'post.php' === $pagenow && isset( $_GET['action'] ) && 'edit' === $_GET['action'] && isset( $_GET['post'] ) ) {
		$post_id = absint( $_GET['post'] );
		$post    = get_post( $post_id );

		if ( $post ) {
			if ( 'post' === $post->post_type ) {
				wp_safe_redirect( admin_url( 'edit.php?page=cgt-add-article&edit_id=' . $post_id ) );
				exit;
			} elseif ( 'tracts' === $post->post_type ) {
				wp_safe_redirect( admin_url( 'edit.php?post_type=tracts&page=cgt-add-tract&edit_id=' . $post_id ) );
				exit;
			}
		}
	}
}

/**
 * Enqueue styles et scripts pour nos pages personnalisées
 */
add_action( 'admin_enqueue_scripts', 'cgt_enqueue_custom_post_creation_assets' );
function cgt_enqueue_custom_post_creation_assets( $hook ) {
	// Charger uniquement sur nos pages personnalisées
	$allowed_hooks = array(
		'posts_page_cgt-add-article',
		'tracts_page_cgt-add-tract',
		'articles-adherents_page_cgt-add-private-article',
		'articles-adherents_page_cgt-add-private-tract',
	);

	// Support pour différentes variations de noms de hooks
	$is_custom_page = in_array( $hook, $allowed_hooks, true ) ||
		strpos( $hook, 'cgt-add-article' ) !== false ||
		strpos( $hook, 'cgt-add-tract' ) !== false ||
		strpos( $hook, 'cgt-add-private-article' ) !== false ||
		strpos( $hook, 'cgt-add-private-tract' ) !== false;

	if ( ! $is_custom_page ) {
		return;
	}

	// Charger le CSS personnalisé avec priorité
	wp_enqueue_style(
		'cgt-admin-post-creation',
		get_stylesheet_directory_uri() . '/assets/css/submit-article.css',
		array(),
		CGT_CHILD_VERSION . '-' . time() // Cache busting pour forcer le rechargement
	);

	// Charger WordPress media uploader
	wp_enqueue_media();

	// Styles supplémentaires pour l'admin
	wp_add_inline_style(
		'cgt-admin-post-creation',
		'
		body {
			background: #f0f0f1;
		}
		#wpcontent {
			padding-left: 0;
		}
		.cgt-custom-editor-page {
			max-width: 1200px;
			margin: 0 auto;
			padding: 20px;
		}
		.submit-article-page {
			background: transparent;
		}
		.submit-article-container {
			background: #fff;
			border-radius: 8px;
			box-shadow: 0 1px 3px rgba(0,0,0,0.1);
			overflow: hidden;
		}
		.submit-article-header {
			background: linear-gradient(135deg, #ce1010 0%, #a30f0f 100%);
			color: white;
			padding: 30px;
			border-radius: 0;
		}
		.submit-article-header h1 {
			color: white;
			font-size: 28px;
			margin: 0 0 10px 0;
			font-weight: 600;
		}
		.submit-article-header .icon {
			font-size: 32px;
			margin-right: 10px;
		}
		.submit-article-header p {
			margin: 0;
			opacity: 1;
			font-size: 16px;
			color: #ffffff;
		}
		.cgt-submit-article {
			padding: 30px;
		}
		.form-group {
			margin-bottom: 25px;
		}
		.form-group label {
			display: block;
			font-weight: 600;
			margin-bottom: 8px;
			color: #1d2939;
			font-size: 14px;
		}
		.form-group .hint {
			display: block;
			font-weight: 400;
			color: #6b7280;
			font-size: 13px;
			margin-top: 4px;
		}
		.form-group .required {
			color: #ce1010;
		}
		.form-group .optional {
			font-weight: normal;
			color: #6b7280;
			font-size: 0.9em;
		}
		.form-control {
			width: 100%;
			padding: 10px 12px;
			border: 1px solid #d1d5db;
			border-radius: 6px;
			font-size: 14px;
			transition: all 0.2s;
		}
		.form-control:focus {
			outline: none;
			border-color: #ce1010;
			box-shadow: 0 0 0 3px rgba(200, 16, 46, 0.1);
		}
		.form-row {
			display: grid;
			grid-template-columns: 1fr 1fr;
			gap: 20px;
		}
		@media (max-width: 768px) {
			.form-row {
				grid-template-columns: 1fr;
			}
		}
		.media-upload-container {
			margin-top: 10px;
		}
		.media-preview {
			margin-bottom: 15px;
			padding: 15px;
			background: #f9fafb;
			border: 2px dashed #d1d5db;
			border-radius: 8px;
			min-height: 60px;
			display: flex;
			align-items: center;
			justify-content: center;
		}
		.media-preview:empty::before {
			content: "Aucune image sélectionnée";
			color: #9ca3af;
			font-size: 14px;
		}
		.media-preview img {
			max-width: 100%;
			max-height: 300px;
			height: auto;
			display: block;
			border-radius: 4px;
		}
		.button-group {
			display: flex;
			gap: 10px;
			flex-wrap: wrap;
		}
		.button-primary {
			background: #ce1010 !important;
			border-color: #ce1010 !important;
			text-shadow: none !important;
			box-shadow: none !important;
			transition: all 0.2s;
		}
		.button-primary:hover {
			background: #a30f0f !important;
			border-color: #a30f0f !important;
		}
		.form-actions {
			margin-top: 40px;
			padding-top: 30px;
			border-top: 2px solid #e5e7eb;
			display: flex;
			justify-content: space-between;
			align-items: center;
		}
		.button-hero {
			padding: 12px 40px !important;
			height: auto !important;
			font-size: 16px !important;
			font-weight: 600 !important;
		}
		.notice {
			margin: 20px 0;
			padding: 15px 20px;
			border-radius: 8px;
			display: flex;
			align-items: flex-start;
			gap: 15px;
		}
		.notice.success {
			background: #d1fae5;
			border-left: 4px solid #10b981;
		}
		.notice.error {
			background: #fee2e2;
			border-left: 4px solid #ef4444;
		}
		.notice-icon {
			font-size: 24px;
			flex-shrink: 0;
		}
		.notice-content {
			flex: 1;
		}
		.notice a {
			color: #065f46;
			font-weight: 600;
			text-decoration: underline;
		}
		.notice a:hover {
			color: #047857;
		}
		.wp-editor-container {
			border: 1px solid #d1d5db;
			border-radius: 6px;
			overflow: hidden;
		}
		'
	);
}

/**
 * Render page Ajouter/Modifier un article
 */
function cgt_render_add_article_page() {
	$message = '';
	$errors  = array();

	// Détecter le mode édition
	$edit_id  = isset( $_GET['edit_id'] ) ? absint( $_GET['edit_id'] ) : 0;
	$is_edit  = $edit_id > 0;

	// Valeurs par défaut
	$title       = '';
	$content     = '';
	$excerpt     = '';
	$category    = 0;
	$branche     = 0;
	$keywords    = '';
	$sources     = '';
	$featured_id = 0;
	$pdf_id      = 0;

	// Charger les données existantes si en mode édition
	if ( $is_edit ) {
		$post = get_post( $edit_id );
		if ( $post && 'post' === $post->post_type ) {
			$title       = $post->post_title;
			$content     = $post->post_content;
			$excerpt     = $post->post_excerpt;
			$featured_id = get_post_thumbnail_id( $post->ID );
			$sources     = get_post_meta( $post->ID, 'cgt_submission_sources', true );

			// Catégorie
			$categories = wp_get_post_categories( $post->ID );
			$category   = ! empty( $categories ) ? $categories[0] : 0;

			// Branche
			$branche_terms = wp_get_post_terms( $post->ID, 'branche' );
			$branche       = ! empty( $branche_terms ) ? $branche_terms[0]->term_id : 0;

			// Mots-clés
			$tags     = wp_get_post_tags( $post->ID, array( 'fields' => 'names' ) );
			$keywords = implode( ', ', $tags );

			// PDF
			$pdf_url = get_post_meta( $post->ID, 'cgt_fichier_pdf', true );
			$pdf_id  = $pdf_url ? attachment_url_to_postid( $pdf_url ) : 0;
		} else {
			$errors[] = __( 'Article introuvable.', 'cgt' );
			$is_edit  = false;
		}
	}

	// Traitement du formulaire
	if ( isset( $_POST['cgt_add_article_submit'] ) ) {
		if ( ! isset( $_POST['cgt_add_article_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['cgt_add_article_nonce'] ), 'cgt_add_article' ) ) {
			$errors[] = __( 'Erreur de sécurité. Veuillez réessayer.', 'cgt' );
		} else {
			// Récupération des données
			$title       = isset( $_POST['cgt_article_title'] ) ? sanitize_text_field( wp_unslash( $_POST['cgt_article_title'] ) ) : '';
			$content     = isset( $_POST['cgt_article_content'] ) ? wp_kses_post( wp_unslash( $_POST['cgt_article_content'] ) ) : '';
			$excerpt     = isset( $_POST['cgt_article_excerpt'] ) ? sanitize_textarea_field( wp_unslash( $_POST['cgt_article_excerpt'] ) ) : '';
			$category    = isset( $_POST['cgt_article_category'] ) ? absint( $_POST['cgt_article_category'] ) : 0;
			$branche     = isset( $_POST['cgt_article_branche'] ) ? absint( $_POST['cgt_article_branche'] ) : 0;
			$keywords    = isset( $_POST['cgt_article_keywords'] ) ? sanitize_text_field( wp_unslash( $_POST['cgt_article_keywords'] ) ) : '';
			$sources     = isset( $_POST['cgt_article_sources'] ) ? sanitize_textarea_field( wp_unslash( $_POST['cgt_article_sources'] ) ) : '';
			$featured_id = isset( $_POST['cgt_article_featured_id'] ) ? absint( $_POST['cgt_article_featured_id'] ) : 0;
			$pdf_id      = isset( $_POST['cgt_article_pdf_id'] ) ? absint( $_POST['cgt_article_pdf_id'] ) : 0;
			$edit_id_post = isset( $_POST['cgt_article_edit_id'] ) ? absint( $_POST['cgt_article_edit_id'] ) : 0;

			// Validation
			if ( empty( $title ) ) {
				$errors[] = __( 'Le titre est requis.', 'cgt' );
			}

			if ( empty( $content ) ) {
				$errors[] = __( 'Le contenu est requis.', 'cgt' );
			}

			if ( empty( $errors ) ) {
				// Créer ou mettre à jour l'article
				$post_args = array(
					'post_type'     => 'post',
					'post_title'    => $title,
					'post_content'  => $content,
					'post_excerpt'  => $excerpt,
					'post_status'   => 'publish',
					'post_category' => $category ? array( $category ) : array(),
				);

				// Édition ou création
				if ( $edit_id_post > 0 ) {
					$post_args['ID'] = $edit_id_post;
					$post_id         = wp_update_post( $post_args );
					$success_message = __( 'Article modifié avec succès ! %s', 'cgt' );
				} else {
					$post_args['post_author'] = get_current_user_id();
					$post_id                  = wp_insert_post( $post_args );
					$success_message          = __( 'Article créé avec succès ! %s', 'cgt' );
				}

				if ( $post_id && ! is_wp_error( $post_id ) ) {
					// Ajouter la branche
					if ( $branche ) {
						wp_set_post_terms( $post_id, array( $branche ), 'branche', false );
					} else {
						wp_set_post_terms( $post_id, array(), 'branche', false );
					}

					// Ajouter les mots-clés
					if ( $keywords ) {
						$tags = array_map( 'trim', explode( ',', $keywords ) );
						wp_set_post_terms( $post_id, $tags, 'post_tag', false );
					} else {
						wp_set_post_terms( $post_id, array(), 'post_tag', false );
					}

					// Ajouter les sources
					if ( $sources ) {
						update_post_meta( $post_id, 'cgt_submission_sources', $sources );
					} else {
						delete_post_meta( $post_id, 'cgt_submission_sources' );
					}

					// Ajouter l'image mise en avant
					if ( $featured_id ) {
						set_post_thumbnail( $post_id, $featured_id );
					} else {
						delete_post_thumbnail( $post_id );
					}

					// Ajouter le PDF
					if ( $pdf_id ) {
						$pdf_url = wp_get_attachment_url( $pdf_id );
						update_post_meta( $post_id, 'cgt_fichier_pdf', $pdf_url );
					} else {
						delete_post_meta( $post_id, 'cgt_fichier_pdf' );
					}

					// Message de succès
					$view_link = get_permalink( $post_id );
					$edit_link = admin_url( 'edit.php?page=cgt-add-article&edit_id=' . $post_id );
					$message   = sprintf(
						$success_message,
						'<a href="' . esc_url( $view_link ) . '" target="_blank">' . __( 'Voir l\'article', 'cgt' ) . '</a> | <a href="' . esc_url( $edit_link ) . '">' . __( 'Modifier', 'cgt' ) . '</a>'
					);

					// Recharger les données pour l'affichage
					$edit_id = $post_id;
					$is_edit = true;
				} else {
					$errors[] = $edit_id_post > 0 ? __( 'Erreur lors de la modification de l\'article.', 'cgt' ) : __( 'Erreur lors de la création de l\'article.', 'cgt' );
				}
			}
		}
	}

	// Récupérer les catégories et branches
	$categories = get_categories( array( 'hide_empty' => false ) );
	$branches   = get_terms( array( 'taxonomy' => 'branche', 'hide_empty' => false ) );

	// Afficher la page
	cgt_render_custom_post_page(
		'article',
		$message,
		$errors,
		compact( 'title', 'content', 'excerpt', 'category', 'branche', 'keywords', 'sources', 'featured_id', 'pdf_id', 'categories', 'branches', 'is_edit', 'edit_id' )
	);
}

/**
 * Render page Créer/Modifier un article adhérent privé.
 */
function cgt_render_add_private_article_page() {
	$message = '';
	$errors  = array();

	$edit_id  = isset( $_GET['edit_id'] ) ? absint( $_GET['edit_id'] ) : 0;
	$is_edit  = $edit_id > 0;

	$title       = '';
	$content     = '';
	$excerpt     = '';
	$classe      = 0;
	$branche     = 0;
	$keywords    = '';
	$sources     = '';
	$featured_id = 0;

	if ( $is_edit ) {
		$post = get_post( $edit_id );
		if ( $post && 'articles_adherents' === $post->post_type ) {
			$title       = $post->post_title;
			$content     = $post->post_content;
			$excerpt     = $post->post_excerpt;
			$featured_id = get_post_thumbnail_id( $post->ID );
			$sources     = get_post_meta( $post->ID, 'cgt_submission_sources', true );

			$classe_terms = wp_get_post_terms( $post->ID, 'thematique' );
			$classe       = ! empty( $classe_terms ) ? $classe_terms[0]->term_id : 0;

			$branche_terms = wp_get_post_terms( $post->ID, 'branche' );
			$branche       = ! empty( $branche_terms ) ? $branche_terms[0]->term_id : 0;

			$tags     = wp_get_post_tags( $post->ID, array( 'fields' => 'names' ) );
			$keywords = implode( ', ', $tags );
		} else {
			$errors[] = __( 'Article privé introuvable.', 'cgt' );
			$is_edit  = false;
		}
	}

	if ( isset( $_POST['cgt_add_private_article_submit'] ) ) {
		if ( ! isset( $_POST['cgt_add_private_article_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['cgt_add_private_article_nonce'] ), 'cgt_add_private_article' ) ) {
			$errors[] = __( 'Erreur de sécurité. Veuillez réessayer.', 'cgt' );
		} else {
			$title        = isset( $_POST['cgt_private_article_title'] ) ? sanitize_text_field( wp_unslash( $_POST['cgt_private_article_title'] ) ) : '';
			$content      = isset( $_POST['cgt_private_article_content'] ) ? wp_kses_post( wp_unslash( $_POST['cgt_private_article_content'] ) ) : '';
			$excerpt      = isset( $_POST['cgt_private_article_excerpt'] ) ? sanitize_textarea_field( wp_unslash( $_POST['cgt_private_article_excerpt'] ) ) : '';
			$classe       = isset( $_POST['cgt_private_article_category'] ) ? absint( $_POST['cgt_private_article_category'] ) : 0;
			$branche      = isset( $_POST['cgt_private_article_branche'] ) ? absint( $_POST['cgt_private_article_branche'] ) : 0;
			$keywords     = isset( $_POST['cgt_private_article_keywords'] ) ? sanitize_text_field( wp_unslash( $_POST['cgt_private_article_keywords'] ) ) : '';
			$sources      = isset( $_POST['cgt_private_article_sources'] ) ? sanitize_textarea_field( wp_unslash( $_POST['cgt_private_article_sources'] ) ) : '';
			$featured_id  = isset( $_POST['cgt_private_article_featured_id'] ) ? absint( $_POST['cgt_private_article_featured_id'] ) : 0;
			$edit_id_post = isset( $_POST['cgt_private_article_edit_id'] ) ? absint( $_POST['cgt_private_article_edit_id'] ) : 0;

			if ( empty( $title ) ) {
				$errors[] = __( 'Le titre est requis.', 'cgt' );
			}

			if ( empty( $content ) ) {
				$errors[] = __( 'Le contenu est requis.', 'cgt' );
			}

			if ( empty( $errors ) ) {
				$post_args = array(
					'post_type'   => 'articles_adherents',
					'post_title'  => $title,
					'post_content'=> $content,
					'post_excerpt'=> $excerpt,
					'post_status' => 'private',
				);

				if ( $edit_id_post > 0 ) {
					$post_args['ID'] = $edit_id_post;
					$post_id         = wp_update_post( $post_args );
					$success_message = __( 'Article privé modifié avec succès ! %s', 'cgt' );
				} else {
					$post_args['post_author'] = get_current_user_id();
					$post_id                   = wp_insert_post( $post_args );
					$success_message           = __( 'Article privé créé avec succès ! %s', 'cgt' );
				}

				if ( $post_id && ! is_wp_error( $post_id ) ) {
					if ( $classe ) {
						wp_set_post_terms( $post_id, array( $classe ), 'thematique', false );
					} else {
						wp_set_post_terms( $post_id, array(), 'thematique', false );
					}

					if ( $branche ) {
						wp_set_post_terms( $post_id, array( $branche ), 'branche', false );
					} else {
						wp_set_post_terms( $post_id, array(), 'branche', false );
					}

					if ( $keywords ) {
						$tags = array_map( 'trim', explode( ',', $keywords ) );
						wp_set_post_terms( $post_id, $tags, 'post_tag', false );
					} else {
						wp_set_post_terms( $post_id, array(), 'post_tag', false );
					}

					if ( $sources ) {
						update_post_meta( $post_id, 'cgt_submission_sources', $sources );
					} else {
						delete_post_meta( $post_id, 'cgt_submission_sources' );
					}

					if ( $featured_id ) {
						set_post_thumbnail( $post_id, $featured_id );
					} else {
						delete_post_thumbnail( $post_id );
					}

					$view_link = get_permalink( $post_id );
					$edit_link = admin_url( 'edit.php?post_type=articles_adherents&page=cgt-add-private-article&edit_id=' . $post_id );
					$message   = sprintf(
						$success_message,
						'<a href="' . esc_url( $view_link ) . '" target="_blank">' . __( 'Voir l’article privé', 'cgt' ) . '</a> | <a href="' . esc_url( $edit_link ) . '">' . __( 'Modifier', 'cgt' ) . '</a>'
					);

					$edit_id = $post_id;
					$is_edit = true;
				} else {
					$errors[] = $edit_id_post > 0 ? __( 'Erreur lors de la modification de l’article privé.', 'cgt' ) : __( 'Erreur lors de la création de l’article privé.', 'cgt' );
				}
			}
		}
	}

	$classes  = get_terms( array( 'taxonomy' => 'thematique', 'hide_empty' => false ) );
	$branches = get_terms( array( 'taxonomy' => 'branche', 'hide_empty' => false ) );

	if ( is_wp_error( $classes ) ) {
		$classes = array();
	}

	if ( is_wp_error( $branches ) ) {
		$branches = array();
	}

	cgt_render_custom_post_page(
		'private_article',
		$message,
		$errors,
		array(
			'title'       => $title,
			'content'     => $content,
			'excerpt'     => $excerpt,
			'category'    => $classe,
			'branche'     => $branche,
			'keywords'    => $keywords,
			'sources'     => $sources,
			'featured_id' => $featured_id,
			'categories'  => $classes,
			'branches'    => $branches,
			'is_edit'     => $is_edit,
			'edit_id'     => $edit_id,
		)
	);
}

/**
 * Render page Ajouter/Modifier un tract
 */
function cgt_render_add_tract_page() {
	$message = '';
	$errors  = array();

	// Détecter le mode édition
	$edit_id  = isset( $_GET['edit_id'] ) ? absint( $_GET['edit_id'] ) : 0;
	$is_edit  = $edit_id > 0;

	// Valeurs par défaut
	$title       = '';
	$content     = '';
	$excerpt     = '';
	$category    = 0;
	$branche     = 0;
	$keywords    = '';
	$sources     = '';
	$featured_id = 0;
	$pdf_id      = 0;

	// Charger les données existantes si en mode édition
	if ( $is_edit ) {
		$post = get_post( $edit_id );
		if ( $post && 'tracts' === $post->post_type ) {
			$title       = $post->post_title;
			$content     = $post->post_content;
			$excerpt     = $post->post_excerpt;
			$featured_id = get_post_thumbnail_id( $post->ID );
			$sources     = get_post_meta( $post->ID, 'cgt_submission_sources', true );

			// Thématique
			$category_terms = wp_get_post_terms( $post->ID, 'thematique' );
			$category       = ! empty( $category_terms ) ? $category_terms[0]->term_id : 0;

			// Branche
			$branche_terms = wp_get_post_terms( $post->ID, 'branche' );
			$branche       = ! empty( $branche_terms ) ? $branche_terms[0]->term_id : 0;

			// Mots-clés
			$tags     = wp_get_post_tags( $post->ID, array( 'fields' => 'names' ) );
			$keywords = implode( ', ', $tags );

			// PDF
			$pdf_url = get_post_meta( $post->ID, 'cgt_fichier_pdf', true );
			$pdf_id  = $pdf_url ? attachment_url_to_postid( $pdf_url ) : 0;
		} else {
			$errors[] = __( 'Tract introuvable.', 'cgt' );
			$is_edit  = false;
		}
	}

	// Traitement du formulaire
	if ( isset( $_POST['cgt_add_tract_submit'] ) ) {
		if ( ! isset( $_POST['cgt_add_tract_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['cgt_add_tract_nonce'] ), 'cgt_add_tract' ) ) {
			$errors[] = __( 'Erreur de sécurité. Veuillez réessayer.', 'cgt' );
		} else {
			// Récupération des données
			$title        = isset( $_POST['cgt_tract_title'] ) ? sanitize_text_field( wp_unslash( $_POST['cgt_tract_title'] ) ) : '';
			$content      = isset( $_POST['cgt_tract_content'] ) ? wp_kses_post( wp_unslash( $_POST['cgt_tract_content'] ) ) : '';
			$excerpt      = isset( $_POST['cgt_tract_excerpt'] ) ? sanitize_textarea_field( wp_unslash( $_POST['cgt_tract_excerpt'] ) ) : '';
			$category     = isset( $_POST['cgt_tract_category'] ) ? absint( $_POST['cgt_tract_category'] ) : 0;
			$branche      = isset( $_POST['cgt_tract_branche'] ) ? absint( $_POST['cgt_tract_branche'] ) : 0;
			$keywords     = isset( $_POST['cgt_tract_keywords'] ) ? sanitize_text_field( wp_unslash( $_POST['cgt_tract_keywords'] ) ) : '';
			$sources      = isset( $_POST['cgt_tract_sources'] ) ? sanitize_textarea_field( wp_unslash( $_POST['cgt_tract_sources'] ) ) : '';
			$featured_id  = isset( $_POST['cgt_tract_featured_id'] ) ? absint( $_POST['cgt_tract_featured_id'] ) : 0;
			$pdf_id       = isset( $_POST['cgt_tract_pdf_id'] ) ? absint( $_POST['cgt_tract_pdf_id'] ) : 0;
			$edit_id_post = isset( $_POST['cgt_tract_edit_id'] ) ? absint( $_POST['cgt_tract_edit_id'] ) : 0;

			// Validation
			if ( empty( $title ) ) {
				$errors[] = __( 'Le titre est requis.', 'cgt' );
			}

			if ( empty( $content ) ) {
				$errors[] = __( 'Le contenu est requis.', 'cgt' );
			}

			if ( empty( $errors ) ) {
				// Créer ou mettre à jour le tract
				$post_args = array(
					'post_type'    => 'tracts',
					'post_title'   => $title,
					'post_content' => $content,
					'post_excerpt' => $excerpt,
					'post_status'  => 'publish',
				);

				// Édition ou création
				if ( $edit_id_post > 0 ) {
					$post_args['ID'] = $edit_id_post;
					$post_id         = wp_update_post( $post_args );
					$success_message = __( 'Tract modifié avec succès ! %s', 'cgt' );
				} else {
					$post_args['post_author'] = get_current_user_id();
					$post_id                  = wp_insert_post( $post_args );
					$success_message          = __( 'Tract créé avec succès ! %s', 'cgt' );
				}

				if ( $post_id && ! is_wp_error( $post_id ) ) {
					// Ajouter la catégorie (thématique)
					if ( $category ) {
						wp_set_post_terms( $post_id, array( $category ), 'thematique', false );
					} else {
						wp_set_post_terms( $post_id, array(), 'thematique', false );
					}

					// Ajouter la branche
					if ( $branche ) {
						wp_set_post_terms( $post_id, array( $branche ), 'branche', false );
					} else {
						wp_set_post_terms( $post_id, array(), 'branche', false );
					}

					// Ajouter les mots-clés
					if ( $keywords ) {
						$tags = array_map( 'trim', explode( ',', $keywords ) );
						wp_set_post_terms( $post_id, $tags, 'post_tag', false );
					} else {
						wp_set_post_terms( $post_id, array(), 'post_tag', false );
					}

					// Ajouter les sources
					if ( $sources ) {
						update_post_meta( $post_id, 'cgt_submission_sources', $sources );
					} else {
						delete_post_meta( $post_id, 'cgt_submission_sources' );
					}

					// Ajouter l'image mise en avant
					if ( $featured_id ) {
						set_post_thumbnail( $post_id, $featured_id );
					} else {
						delete_post_thumbnail( $post_id );
					}

					// Ajouter le PDF
					if ( $pdf_id ) {
						$pdf_url = wp_get_attachment_url( $pdf_id );
						update_post_meta( $post_id, 'cgt_fichier_pdf', $pdf_url );
					} else {
						delete_post_meta( $post_id, 'cgt_fichier_pdf' );
					}


					// Message de succès
					$view_link = get_permalink( $post_id );
					$edit_link = admin_url( 'edit.php?post_type=tracts&page=cgt-add-tract&edit_id=' . $post_id );
					$message   = sprintf(
						$success_message,
						'<a href="' . esc_url( $view_link ) . '" target="_blank">' . __( 'Voir le tract', 'cgt' ) . '</a> | <a href="' . esc_url( $edit_link ) . '">' . __( 'Modifier', 'cgt' ) . '</a>'
					);

					// Recharger les données pour l'affichage
					$edit_id = $post_id;
					$is_edit = true;
				} else {
					$errors[] = $edit_id_post > 0 ? __( 'Erreur lors de la modification du tract.', 'cgt' ) : __( 'Erreur lors de la création du tract.', 'cgt' );
				}
			}
		}
	}

	// Récupérer les thématiques et branches
	$categories = get_terms( array( 'taxonomy' => 'thematique', 'hide_empty' => false ) );
	$branches   = get_terms( array( 'taxonomy' => 'branche', 'hide_empty' => false ) );

	// Afficher la page
	cgt_render_custom_post_page(
		'tract',
		$message,
		$errors,
		compact( 'title', 'content', 'excerpt', 'category', 'branche', 'keywords', 'sources', 'featured_id', 'pdf_id', 'categories', 'branches', 'is_edit', 'edit_id' )
	);
}

/**
 * Render page Créer/Modifier un tract privé adhérent.
 */
function cgt_render_add_private_tract_page() {
	$message = '';
	$errors  = array();

	$edit_id  = isset( $_GET['edit_id'] ) ? absint( $_GET['edit_id'] ) : 0;
	$is_edit  = $edit_id > 0;

	$title       = '';
	$content     = '';
	$excerpt     = '';
	$classe      = 0;
	$branche     = 0;
	$keywords    = '';
	$sources     = '';
	$featured_id = 0;
	$pdf_id      = 0;

	if ( $is_edit ) {
		$post = get_post( $edit_id );
		if ( $post && 'tracts' === $post->post_type ) {
			$title       = $post->post_title;
			$content     = $post->post_content;
			$excerpt     = $post->post_excerpt;
			$featured_id = get_post_thumbnail_id( $post->ID );
			$sources     = get_post_meta( $post->ID, 'cgt_submission_sources', true );

			$classe_terms = wp_get_post_terms( $post->ID, 'thematique' );
			$classe       = ! empty( $classe_terms ) ? $classe_terms[0]->term_id : 0;

			$branche_terms = wp_get_post_terms( $post->ID, 'branche' );
			$branche       = ! empty( $branche_terms ) ? $branche_terms[0]->term_id : 0;

			$tags     = wp_get_post_tags( $post->ID, array( 'fields' => 'names' ) );
			$keywords = implode( ', ', $tags );

			$pdf_url = get_post_meta( $post->ID, 'cgt_fichier_pdf', true );
			$pdf_id  = $pdf_url ? attachment_url_to_postid( $pdf_url ) : 0;
		} else {
			$errors[] = __( 'Tract privé introuvable.', 'cgt' );
			$is_edit  = false;
		}
	}

	if ( isset( $_POST['cgt_add_private_tract_submit'] ) ) {
		if ( ! isset( $_POST['cgt_add_private_tract_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['cgt_add_private_tract_nonce'] ), 'cgt_add_private_tract' ) ) {
			$errors[] = __( 'Erreur de sécurité. Veuillez réessayer.', 'cgt' );
		} else {
			$title        = isset( $_POST['cgt_private_tract_title'] ) ? sanitize_text_field( wp_unslash( $_POST['cgt_private_tract_title'] ) ) : '';
			$content      = isset( $_POST['cgt_private_tract_content'] ) ? wp_kses_post( wp_unslash( $_POST['cgt_private_tract_content'] ) ) : '';
			$excerpt      = isset( $_POST['cgt_private_tract_excerpt'] ) ? sanitize_textarea_field( wp_unslash( $_POST['cgt_private_tract_excerpt'] ) ) : '';
			$classe       = isset( $_POST['cgt_private_tract_category'] ) ? absint( $_POST['cgt_private_tract_category'] ) : 0;
			$branche      = isset( $_POST['cgt_private_tract_branche'] ) ? absint( $_POST['cgt_private_tract_branche'] ) : 0;
			$keywords     = isset( $_POST['cgt_private_tract_keywords'] ) ? sanitize_text_field( wp_unslash( $_POST['cgt_private_tract_keywords'] ) ) : '';
			$sources      = isset( $_POST['cgt_private_tract_sources'] ) ? sanitize_textarea_field( wp_unslash( $_POST['cgt_private_tract_sources'] ) ) : '';
			$featured_id  = isset( $_POST['cgt_private_tract_featured_id'] ) ? absint( $_POST['cgt_private_tract_featured_id'] ) : 0;
			$pdf_id       = isset( $_POST['cgt_private_tract_pdf_id'] ) ? absint( $_POST['cgt_private_tract_pdf_id'] ) : 0;
			$edit_id_post = isset( $_POST['cgt_private_tract_edit_id'] ) ? absint( $_POST['cgt_private_tract_edit_id'] ) : 0;

			if ( empty( $title ) ) {
				$errors[] = __( 'Le titre est requis.', 'cgt' );
			}

			if ( empty( $content ) ) {
				$errors[] = __( 'Le contenu est requis.', 'cgt' );
			}

			if ( empty( $errors ) ) {
				$post_args = array(
					'post_type'    => 'tracts',
					'post_title'   => $title,
					'post_content' => $content,
					'post_excerpt' => $excerpt,
					'post_status'  => 'private',
				);

				if ( $edit_id_post > 0 ) {
					$post_args['ID'] = $edit_id_post;
					$post_id         = wp_update_post( $post_args );
					$success_message = __( 'Tract privé modifié avec succès ! %s', 'cgt' );
				} else {
					$post_args['post_author'] = get_current_user_id();
					$post_id                   = wp_insert_post( $post_args );
					$success_message           = __( 'Tract privé créé avec succès ! %s', 'cgt' );
				}

				if ( $post_id && ! is_wp_error( $post_id ) ) {
					if ( $classe ) {
						wp_set_post_terms( $post_id, array( $classe ), 'thematique', false );
					} else {
						wp_set_post_terms( $post_id, array(), 'thematique', false );
					}

					if ( $branche ) {
						wp_set_post_terms( $post_id, array( $branche ), 'branche', false );
					} else {
						wp_set_post_terms( $post_id, array(), 'branche', false );
					}

					if ( $keywords ) {
						$tags = array_map( 'trim', explode( ',', $keywords ) );
						wp_set_post_terms( $post_id, $tags, 'post_tag', false );
					} else {
						wp_set_post_terms( $post_id, array(), 'post_tag', false );
					}

					if ( $sources ) {
						update_post_meta( $post_id, 'cgt_submission_sources', $sources );
					} else {
						delete_post_meta( $post_id, 'cgt_submission_sources' );
					}

					if ( $featured_id ) {
						set_post_thumbnail( $post_id, $featured_id );
					} else {
						delete_post_thumbnail( $post_id );
					}

					if ( $pdf_id ) {
						update_post_meta( $post_id, 'cgt_fichier_pdf', wp_get_attachment_url( $pdf_id ) );
					} else {
						delete_post_meta( $post_id, 'cgt_fichier_pdf' );
					}

					update_post_meta( $post_id, 'cgt_visibilite', 'prive' );

					$view_link = get_permalink( $post_id );
					$edit_link = admin_url( 'edit.php?post_type=articles_adherents&page=cgt-add-private-tract&edit_id=' . $post_id );
					$message   = sprintf(
						$success_message,
						'<a href="' . esc_url( $view_link ) . '" target="_blank">' . __( 'Voir le tract privé', 'cgt' ) . '</a> | <a href="' . esc_url( $edit_link ) . '">' . __( 'Modifier', 'cgt' ) . '</a>'
					);

					$edit_id = $post_id;
					$is_edit = true;
				} else {
					$errors[] = $edit_id_post > 0 ? __( 'Erreur lors de la modification du tract privé.', 'cgt' ) : __( 'Erreur lors de la création du tract privé.', 'cgt' );
				}
			}
		}
	}

	$classes  = get_terms( array( 'taxonomy' => 'thematique', 'hide_empty' => false ) );
	$branches = get_terms( array( 'taxonomy' => 'branche', 'hide_empty' => false ) );

	if ( is_wp_error( $classes ) ) {
		$classes = array();
	}

	if ( is_wp_error( $branches ) ) {
		$branches = array();
	}

	cgt_render_custom_post_page(
		'private_tract',
		$message,
		$errors,
		array(
			'title'       => $title,
			'content'     => $content,
			'excerpt'     => $excerpt,
			'category'    => $classe,
			'branche'     => $branche,
			'keywords'    => $keywords,
			'sources'     => $sources,
			'featured_id' => $featured_id,
			'pdf_id'      => $pdf_id,
			'categories'  => $classes,
			'branches'    => $branches,
			'is_edit'     => $is_edit,
			'edit_id'     => $edit_id,
		)
	);
}

/**
 * Render la page personnalisée complète
 */
function cgt_render_custom_post_page( $type, $message, $errors, $data ) {
	$is_private_article = ( 'private_article' === $type );
	$is_private_tract   = ( 'private_tract' === $type );
	$is_article         = in_array( $type, array( 'article', 'private_article' ), true );

	switch ( $type ) {
		case 'article':
			$post_type_label = __( 'Article', 'cgt' );
			$icon            = '✍️';
			$nonce_action    = 'cgt_add_article';
			$nonce_name      = 'cgt_add_article_nonce';
			$submit_name     = 'cgt_add_article_submit';
			$field_prefix    = 'article';
			break;
		case 'private_article':
			$post_type_label = __( 'Article privé', 'cgt' );
			$icon            = '🔒';
			$nonce_action    = 'cgt_add_private_article';
			$nonce_name      = 'cgt_add_private_article_nonce';
			$submit_name     = 'cgt_add_private_article_submit';
			$field_prefix    = 'private_article';
			break;
		case 'private_tract':
			$post_type_label = __( 'Tract privé', 'cgt' );
			$icon            = '🔒';
			$nonce_action    = 'cgt_add_private_tract';
			$nonce_name      = 'cgt_add_private_tract_nonce';
			$submit_name     = 'cgt_add_private_tract_submit';
			$field_prefix    = 'private_tract';
			break;
		default:
			$post_type_label = __( 'Tract', 'cgt' );
			$icon            = '📄';
			$nonce_action    = 'cgt_add_tract';
			$nonce_name      = 'cgt_add_tract_nonce';
			$submit_name     = 'cgt_add_tract_submit';
			$field_prefix    = 'tract';
	}

	// Valeurs par défaut
	$title       = isset( $data['title'] ) ? $data['title'] : '';
	$content     = isset( $data['content'] ) ? $data['content'] : '';
	$excerpt     = isset( $data['excerpt'] ) ? $data['excerpt'] : '';
	$category    = isset( $data['category'] ) ? $data['category'] : 0;
	$branche     = isset( $data['branche'] ) ? $data['branche'] : 0;
	$keywords    = isset( $data['keywords'] ) ? $data['keywords'] : '';
	$sources     = isset( $data['sources'] ) ? $data['sources'] : '';
	$featured_id = isset( $data['featured_id'] ) ? $data['featured_id'] : 0;
	$pdf_id      = isset( $data['pdf_id'] ) ? $data['pdf_id'] : 0;
	$categories  = isset( $data['categories'] ) ? $data['categories'] : array();
	$branches    = isset( $data['branches'] ) ? $data['branches'] : array();
	$is_edit     = isset( $data['is_edit'] ) ? $data['is_edit'] : false;
	$edit_id     = isset( $data['edit_id'] ) ? $data['edit_id'] : 0;

	// Titres et descriptions selon le mode
	$page_title  = $is_edit ? sprintf( __( 'Modifier %s', 'cgt' ), strtolower( $post_type_label ) ) : sprintf( __( 'Créer un nouveau %s', 'cgt' ), strtolower( $post_type_label ) );
	if ( $is_private_article || $is_private_tract ) {
		$page_description = $is_edit
			? sprintf( __( 'Modifiez les informations de votre %s réservé aux adhérents.', 'cgt' ), strtolower( $post_type_label ) )
			: sprintf( __( 'Remplissez le formulaire ci-dessous pour enregistrer votre %s réservé aux adhérents.', 'cgt' ), strtolower( $post_type_label ) );
		$button_text = $is_edit
			? sprintf( __( 'Mettre à jour le %s privé', 'cgt' ), strtolower( $post_type_label ) )
			: sprintf( __( 'Enregistrer le %s privé', 'cgt' ), strtolower( $post_type_label ) );
	} else {
		$page_description = $is_edit
			? sprintf( __( 'Modifiez les informations de votre %s ci-dessous.', 'cgt' ), strtolower( $post_type_label ) )
			: sprintf( __( 'Remplissez le formulaire ci-dessous pour publier votre %s sur le site CGT.', 'cgt' ), strtolower( $post_type_label ) );
		$button_text = $is_edit
			? sprintf( __( 'Mettre à jour le %s', 'cgt' ), strtolower( $post_type_label ) )
			: sprintf( __( 'Publier le %s', 'cgt' ), strtolower( $post_type_label ) );
	}

	$category_label = $is_article ? __( 'Catégorie', 'cgt' ) : __( 'Thématique', 'cgt' );
	if ( $is_private_article || $is_private_tract ) {
		$category_label = __( 'Classe', 'cgt' );
	}

	?>
	<div class="wrap cgt-custom-editor-page">
		<div class="submit-article-page">
			<div class="submit-article-container">
				<!-- Header -->
				<header class="submit-article-header">
					<h1>
						<span class="icon"><?php echo esc_html( $icon ); ?></span>
						<?php echo esc_html( $page_title ); ?>
					</h1>
					<p><?php echo esc_html( $page_description ); ?></p>
				</header>

				<?php if ( ! empty( $message ) ) : ?>
					<div class="notice success">
						<span class="notice-icon">✓</span>
						<div class="notice-content">
							<?php echo wp_kses_post( $message ); ?>
						</div>
					</div>
				<?php endif; ?>

				<?php if ( ! empty( $errors ) ) : ?>
					<div class="notice error">
						<span class="notice-icon">⚠</span>
						<div class="notice-content">
							<strong><?php esc_html_e( 'Erreurs détectées :', 'cgt' ); ?></strong>
							<ul style="margin: 10px 0 0 20px;">
								<?php foreach ( $errors as $error ) : ?>
									<li><?php echo esc_html( $error ); ?></li>
								<?php endforeach; ?>
							</ul>
						</div>
					</div>
				<?php endif; ?>

				<!-- Form -->
				<form class="cgt-submit-article" method="post" enctype="multipart/form-data">
					<?php wp_nonce_field( $nonce_action, $nonce_name ); ?>
					<?php if ( $is_edit && $edit_id > 0 ) : ?>
						<input type="hidden" name="cgt_<?php echo esc_attr( $field_prefix ); ?>_edit_id" value="<?php echo esc_attr( $edit_id ); ?>">
					<?php endif; ?>

					<div class="form-step active">
						<!-- Titre -->
						<div class="form-group">
							<label>
								<?php esc_html_e( 'Titre', 'cgt' ); ?> <span class="required">*</span>
								<span class="hint"><?php esc_html_e( 'Titre clair et explicite de votre publication', 'cgt' ); ?></span>
							</label>
							<input
								type="text"
								name="cgt_<?php echo esc_attr( $field_prefix ); ?>_title"
								class="form-control"
								value="<?php echo esc_attr( $title ); ?>"
								placeholder="<?php esc_attr_e( 'Exemple : Négociations salariales 2025', 'cgt' ); ?>"
								required
							>
						</div>

						<!-- Description (Contenu) -->
						<div class="form-group">
							<label>
								<?php esc_html_e( 'Description', 'cgt' ); ?> <span class="required">*</span>
								<span class="hint"><?php esc_html_e( 'Contenu complet de votre publication', 'cgt' ); ?></span>
							</label>
							<?php
							wp_editor(
								$content,
								'cgt_' . $field_prefix . '_content',
								array(
									'textarea_name' => 'cgt_' . $field_prefix . '_content',
									'media_buttons' => true,
									'textarea_rows' => 12,
									'teeny'         => false,
									'tinymce'       => true,
									'quicktags'     => true,
								)
							);
							?>
						</div>

						<!-- Extrait (Facultatif) -->
						<div class="form-group">
							<label>
								<?php esc_html_e( 'Extrait', 'cgt' ); ?> <span class="optional"><?php esc_html_e( '(Facultatif)', 'cgt' ); ?></span>
								<span class="hint"><?php esc_html_e( 'Résumé court qui apparaîtra dans les aperçus', 'cgt' ); ?></span>
							</label>
							<textarea
								name="cgt_<?php echo esc_attr( $field_prefix ); ?>_excerpt"
								class="form-control"
								rows="3"
								placeholder="<?php esc_attr_e( 'Résumé en quelques phrases...', 'cgt' ); ?>"
							><?php echo esc_textarea( $excerpt ); ?></textarea>
						</div>


						<div class="form-row">
							<!-- Branche -->
							<div class="form-group">
								<label>
									<?php esc_html_e( 'Branche', 'cgt' ); ?>
									<span class="hint"><?php esc_html_e( 'Sélectionnez la branche concernée', 'cgt' ); ?></span>
								</label>
								<select name="cgt_<?php echo esc_attr( $field_prefix ); ?>_branche" class="form-control">
									<option value=""><?php esc_html_e( '— Choisir une branche —', 'cgt' ); ?></option>
									<?php foreach ( $branches as $branch ) : ?>
										<option value="<?php echo esc_attr( $branch->term_id ); ?>" <?php selected( $branche, $branch->term_id ); ?>>
											<?php echo esc_html( $branch->name ); ?>
										</option>
									<?php endforeach; ?>
								</select>
							</div>

							<!-- Catégorie/Thématique -->
							<div class="form-group">
						<label>
							<?php echo esc_html( $category_label ); ?>
							<span class="hint"><?php esc_html_e( 'Sélectionnez la plus pertinente', 'cgt' ); ?></span>
						</label>
						<select name="cgt_<?php echo esc_attr( $field_prefix ); ?>_category" class="form-control">
									<option value=""><?php esc_html_e( '— Choisir —', 'cgt' ); ?></option>
									<?php foreach ( $categories as $cat ) : ?>
										<option value="<?php echo esc_attr( $cat->term_id ); ?>" <?php selected( $category, $cat->term_id ); ?>>
											<?php echo esc_html( $cat->name ); ?>
										</option>
									<?php endforeach; ?>
								</select>
							</div>
						</div>

						<!-- Mots-clés -->
						<div class="form-group">
							<label>
								<?php esc_html_e( 'Mots-clés', 'cgt' ); ?>
								<span class="hint"><?php esc_html_e( 'Séparés par des virgules pour faciliter la recherche', 'cgt' ); ?></span>
							</label>
							<input
								type="text"
								name="cgt_<?php echo esc_attr( $field_prefix ); ?>_keywords"
								class="form-control"
								value="<?php echo esc_attr( $keywords ); ?>"
								placeholder="<?php esc_attr_e( 'Ex: salaire, négociation, grève', 'cgt' ); ?>"
							>
						</div>

						<!-- Sources et références -->
						<div class="form-group">
							<label>
								<?php esc_html_e( 'Sources et références', 'cgt' ); ?>
								<span class="hint"><?php esc_html_e( 'Liens, documents ou références utilisés', 'cgt' ); ?></span>
							</label>
							<textarea
								name="cgt_<?php echo esc_attr( $field_prefix ); ?>_sources"
								class="form-control"
								rows="3"
								placeholder="<?php esc_attr_e( 'Ex: Article du Monde, Rapport INSEE, Document interne...', 'cgt' ); ?>"
							><?php echo esc_textarea( $sources ); ?></textarea>
						</div>

						<!-- Image mise en avant -->
						<div class="form-group">
							<label>
								<?php esc_html_e( 'Image mise en avant', 'cgt' ); ?>
								<span class="hint"><?php esc_html_e( 'Image qui sera affichée dans les cartes et aperçus', 'cgt' ); ?></span>
							</label>
							<div class="media-upload-container">
								<input type="hidden" name="cgt_<?php echo esc_attr( $field_prefix ); ?>_featured_id" id="<?php echo esc_attr( $field_prefix ); ?>-featured-id" value="<?php echo esc_attr( $featured_id ); ?>">
								<div id="<?php echo esc_attr( $field_prefix ); ?>-featured-preview" class="media-preview">
									<?php if ( ! empty( $featured_id ) ) : ?>
										<?php echo wp_get_attachment_image( $featured_id, 'medium' ); ?>
									<?php endif; ?>
								</div>
								<div class="button-group">
									<button type="button" class="button button-primary" id="<?php echo esc_attr( $field_prefix ); ?>-featured-upload">
										📷 <?php esc_html_e( 'Sélectionner une image', 'cgt' ); ?>
									</button>
									<button type="button" class="button" id="<?php echo esc_attr( $field_prefix ); ?>-featured-remove" style="<?php echo empty( $featured_id ) ? 'display:none;' : ''; ?>">
										🗑️ <?php esc_html_e( 'Supprimer', 'cgt' ); ?>
									</button>
								</div>
							</div>
						</div>

						<!-- Fichier PDF -->
						<div class="form-group">
							<label>
								<?php esc_html_e( 'Fichier PDF', 'cgt' ); ?> <span class="optional"><?php esc_html_e( '(Facultatif)', 'cgt' ); ?></span>
								<span class="hint"><?php echo $is_article ? esc_html__( 'Document PDF complémentaire (max 15 MB)', 'cgt' ) : esc_html__( 'Document PDF du tract (max 15 MB)', 'cgt' ); ?></span>
							</label>
							<div class="media-upload-container">
								<input type="hidden" name="cgt_<?php echo esc_attr( $field_prefix ); ?>_pdf_id" id="<?php echo esc_attr( $field_prefix ); ?>-pdf-id" value="<?php echo esc_attr( $pdf_id ); ?>">
								<div id="<?php echo esc_attr( $field_prefix ); ?>-pdf-preview" class="media-preview">
									<?php if ( ! empty( $pdf_id ) ) : ?>
										<p style="margin:0; color: #059669; font-weight: 600;">📄 <?php echo esc_html( basename( wp_get_attachment_url( $pdf_id ) ) ); ?></p>
									<?php endif; ?>
								</div>
								<div class="button-group">
									<button type="button" class="button button-primary" id="<?php echo esc_attr( $field_prefix ); ?>-pdf-upload">
										📎 <?php esc_html_e( 'Sélectionner un PDF', 'cgt' ); ?>
									</button>
									<button type="button" class="button" id="<?php echo esc_attr( $field_prefix ); ?>-pdf-remove" style="<?php echo empty( $pdf_id ) ? 'display:none;' : ''; ?>">
										🗑️ <?php esc_html_e( 'Supprimer', 'cgt' ); ?>
									</button>
								</div>
							</div>
						</div>

						<!-- Bouton submit -->
						<div class="form-actions">
							<div>
								<a href="<?php echo esc_url( $is_article ? admin_url( 'edit.php' ) : admin_url( 'edit.php?post_type=tracts' ) ); ?>" class="button button-secondary">
									← <?php esc_html_e( 'Retour à la liste', 'cgt' ); ?>
								</a>
							</div>
							<button type="submit" name="<?php echo esc_attr( $submit_name ); ?>" class="button button-primary button-hero">
								✓ <?php echo esc_html( $button_text ); ?>
							</button>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>

	<script>
	jQuery(document).ready(function($) {
		// Image mise en avant
		var featuredUploader;
		$('#<?php echo esc_js( $field_prefix ); ?>-featured-upload').on('click', function(e) {
			e.preventDefault();
			if (featuredUploader) {
				featuredUploader.open();
				return;
			}
			featuredUploader = wp.media({
				title: '<?php esc_html_e( 'Choisir une image', 'cgt' ); ?>',
				button: { text: '<?php esc_html_e( 'Sélectionner', 'cgt' ); ?>' },
				library: { type: 'image' },
				multiple: false
			});
			featuredUploader.on('select', function() {
				var attachment = featuredUploader.state().get('selection').first().toJSON();
				$('#<?php echo esc_js( $field_prefix ); ?>-featured-id').val(attachment.id);
				var imgUrl = attachment.sizes && attachment.sizes.medium ? attachment.sizes.medium.url : attachment.url;
				$('#<?php echo esc_js( $field_prefix ); ?>-featured-preview').html('<img src="' + imgUrl + '">');
				$('#<?php echo esc_js( $field_prefix ); ?>-featured-remove').show();
			});
			featuredUploader.open();
		});

		$('#<?php echo esc_js( $field_prefix ); ?>-featured-remove').on('click', function(e) {
			e.preventDefault();
			$('#<?php echo esc_js( $field_prefix ); ?>-featured-id').val('');
			$('#<?php echo esc_js( $field_prefix ); ?>-featured-preview').html('');
			$(this).hide();
		});

		// PDF
		var pdfUploader;
		$('#<?php echo esc_js( $field_prefix ); ?>-pdf-upload').on('click', function(e) {
			e.preventDefault();
			if (pdfUploader) {
				pdfUploader.open();
				return;
			}
			pdfUploader = wp.media({
				title: '<?php esc_html_e( 'Choisir un PDF', 'cgt' ); ?>',
				button: { text: '<?php esc_html_e( 'Sélectionner', 'cgt' ); ?>' },
				library: { type: 'application/pdf' },
				multiple: false
			});
			pdfUploader.on('select', function() {
				var attachment = pdfUploader.state().get('selection').first().toJSON();
				$('#<?php echo esc_js( $field_prefix ); ?>-pdf-id').val(attachment.id);
				$('#<?php echo esc_js( $field_prefix ); ?>-pdf-preview').html('<p style="margin:0; color: #059669; font-weight: 600;">📄 ' + attachment.filename + '</p>');
				$('#<?php echo esc_js( $field_prefix ); ?>-pdf-remove').show();
			});
			pdfUploader.open();
		});

		$('#<?php echo esc_js( $field_prefix ); ?>-pdf-remove').on('click', function(e) {
			e.preventDefault();
			$('#<?php echo esc_js( $field_prefix ); ?>-pdf-id').val('');
			$('#<?php echo esc_js( $field_prefix ); ?>-pdf-preview').html('');
			$(this).hide();
		});
	});
	</script>
	<?php
}
