<?php
/**
 * Admin Post Creation Pages
 * Pages d'administration pour créer des articles et tracts facilement
 *
 * @package CGT_Child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Add admin menu pages
 */
add_action( 'admin_menu', 'cgt_add_post_creation_pages' );
function cgt_add_post_creation_pages() {
	// Ajouter Article
	add_menu_page(
		__( 'Ajouter Article', 'cgt' ),
		__( 'Ajouter Article', 'cgt' ),
		'edit_posts',
		'cgt-add-article',
		'cgt_render_add_article_page',
		'dashicons-edit-large',
		25
	);

	// Ajouter Tract
	add_menu_page(
		__( 'Ajouter Tract', 'cgt' ),
		__( 'Ajouter Tract', 'cgt' ),
		'edit_posts',
		'cgt-add-tract',
		'cgt_render_add_tract_page',
		'dashicons-media-document',
		26
	);
}

/**
 * Enqueue admin styles and scripts for post creation pages
 */
add_action( 'admin_enqueue_scripts', 'cgt_enqueue_post_creation_assets' );
function cgt_enqueue_post_creation_assets( $hook ) {
	// Charger uniquement sur nos pages
	if ( 'toplevel_page_cgt-add-article' !== $hook && 'toplevel_page_cgt-add-tract' !== $hook ) {
		return;
	}

	// Charger le CSS du formulaire de soumission
	wp_enqueue_style(
		'cgt-admin-post-creation',
		get_stylesheet_directory_uri() . '/assets/css/submit-article.css',
		array(),
		CGT_CHILD_VERSION
	);

	// Charger WordPress media uploader
	wp_enqueue_media();
}

/**
 * Render the Add Article page
 */
function cgt_render_add_article_page() {
	$message = '';
	$errors  = array();

	// Traitement du formulaire
	if ( isset( $_POST['cgt_add_article_submit'] ) ) {
		if ( ! wp_verify_nonce( sanitize_key( $_POST['cgt_add_article_nonce'] ), 'cgt_add_article' ) ) {
			$errors[] = __( 'Jeton de sécurité invalide.', 'cgt' );
		} else {
			// Récupération des données
			$title      = isset( $_POST['cgt_article_title'] ) ? sanitize_text_field( wp_unslash( $_POST['cgt_article_title'] ) ) : '';
			$content    = isset( $_POST['cgt_article_content'] ) ? wp_kses_post( wp_unslash( $_POST['cgt_article_content'] ) ) : '';
			$excerpt    = isset( $_POST['cgt_article_excerpt'] ) ? sanitize_textarea_field( wp_unslash( $_POST['cgt_article_excerpt'] ) ) : '';
			$category   = isset( $_POST['cgt_article_category'] ) ? absint( $_POST['cgt_article_category'] ) : 0;
			$branche    = isset( $_POST['cgt_article_branche'] ) ? absint( $_POST['cgt_article_branche'] ) : 0;
			$keywords   = isset( $_POST['cgt_article_keywords'] ) ? sanitize_text_field( wp_unslash( $_POST['cgt_article_keywords'] ) ) : '';
			$sources    = isset( $_POST['cgt_article_sources'] ) ? sanitize_textarea_field( wp_unslash( $_POST['cgt_article_sources'] ) ) : '';
			$featured_id = isset( $_POST['cgt_article_featured_id'] ) ? absint( $_POST['cgt_article_featured_id'] ) : 0;

			// Validation
			if ( empty( $title ) ) {
				$errors[] = __( 'Le titre est requis.', 'cgt' );
			}

			if ( empty( $content ) ) {
				$errors[] = __( 'Le contenu est requis.', 'cgt' );
			}

			if ( empty( $errors ) ) {
				// Créer l'article
				$post_args = array(
					'post_type'    => 'post',
					'post_title'   => $title,
					'post_content' => $content,
					'post_excerpt' => $excerpt,
					'post_status'  => 'publish', // Publier directement
					'post_author'  => get_current_user_id(),
					'post_category'=> $category ? array( $category ) : array(),
				);

				$post_id = wp_insert_post( $post_args );

				if ( $post_id && ! is_wp_error( $post_id ) ) {
					// Ajouter la branche
					if ( $branche ) {
						wp_set_post_terms( $post_id, array( $branche ), 'branche', false );
					}

					// Ajouter les mots-clés
					if ( $keywords ) {
						$tags = array_map( 'trim', explode( ',', $keywords ) );
						wp_set_post_terms( $post_id, $tags, 'post_tag', false );
					}

					// Ajouter les sources
					if ( $sources ) {
						update_post_meta( $post_id, 'cgt_submission_sources', $sources );
					}

					// Ajouter l'image mise en avant
					if ( $featured_id ) {
						set_post_thumbnail( $post_id, $featured_id );
					}

					// Message de succès avec lien
					$edit_link = get_edit_post_link( $post_id );
					$view_link = get_permalink( $post_id );
					$message = sprintf(
						__( 'Article créé avec succès ! <a href="%1$s" target="_blank">Voir l\'article</a> | <a href="%2$s">Modifier</a>', 'cgt' ),
						esc_url( $view_link ),
						esc_url( $edit_link )
					);

					// Réinitialiser les champs
					$title = $content = $excerpt = $keywords = $sources = '';
					$category = $branche = $featured_id = 0;
				} else {
					$errors[] = __( 'Erreur lors de la création de l\'article.', 'cgt' );
				}
			}
		}
	}

	// Récupérer les catégories et branches
	$categories = get_categories( array( 'hide_empty' => false ) );
	$branches   = get_terms( array( 'taxonomy' => 'branche', 'hide_empty' => false ) );

	// Afficher le formulaire
	cgt_render_post_creation_form( 'article', $message, $errors, compact( 'categories', 'branches', 'title', 'content', 'excerpt', 'category', 'branche', 'keywords', 'sources', 'featured_id' ) );
}

/**
 * Render the Add Tract page
 */
function cgt_render_add_tract_page() {
	$message = '';
	$errors  = array();

	// Traitement du formulaire
	if ( isset( $_POST['cgt_add_tract_submit'] ) ) {
		if ( ! wp_verify_nonce( sanitize_key( $_POST['cgt_add_tract_nonce'] ), 'cgt_add_tract' ) ) {
			$errors[] = __( 'Jeton de sécurité invalide.', 'cgt' );
		} else {
			// Récupération des données
			$title      = isset( $_POST['cgt_tract_title'] ) ? sanitize_text_field( wp_unslash( $_POST['cgt_tract_title'] ) ) : '';
			$content    = isset( $_POST['cgt_tract_content'] ) ? wp_kses_post( wp_unslash( $_POST['cgt_tract_content'] ) ) : '';
			$excerpt    = isset( $_POST['cgt_tract_excerpt'] ) ? sanitize_textarea_field( wp_unslash( $_POST['cgt_tract_excerpt'] ) ) : '';
			$category   = isset( $_POST['cgt_tract_category'] ) ? absint( $_POST['cgt_tract_category'] ) : 0;
			$branche    = isset( $_POST['cgt_tract_branche'] ) ? absint( $_POST['cgt_tract_branche'] ) : 0;
			$keywords   = isset( $_POST['cgt_tract_keywords'] ) ? sanitize_text_field( wp_unslash( $_POST['cgt_tract_keywords'] ) ) : '';
			$sources    = isset( $_POST['cgt_tract_sources'] ) ? sanitize_textarea_field( wp_unslash( $_POST['cgt_tract_sources'] ) ) : '';
			$featured_id = isset( $_POST['cgt_tract_featured_id'] ) ? absint( $_POST['cgt_tract_featured_id'] ) : 0;
			$pdf_id     = isset( $_POST['cgt_tract_pdf_id'] ) ? absint( $_POST['cgt_tract_pdf_id'] ) : 0;

			// Validation
			if ( empty( $title ) ) {
				$errors[] = __( 'Le titre est requis.', 'cgt' );
			}

			if ( empty( $content ) ) {
				$errors[] = __( 'Le contenu est requis.', 'cgt' );
			}

			if ( empty( $errors ) ) {
				// Créer le tract
				$post_args = array(
					'post_type'    => 'tracts',
					'post_title'   => $title,
					'post_content' => $content,
					'post_excerpt' => $excerpt,
					'post_status'  => 'publish', // Publier directement
					'post_author'  => get_current_user_id(),
				);

				$post_id = wp_insert_post( $post_args );

				if ( $post_id && ! is_wp_error( $post_id ) ) {
					// Ajouter la catégorie (thématique)
					if ( $category ) {
						wp_set_post_terms( $post_id, array( $category ), 'thematique', false );
					}

					// Ajouter la branche
					if ( $branche ) {
						wp_set_post_terms( $post_id, array( $branche ), 'branche', false );
					}

					// Ajouter les mots-clés
					if ( $keywords ) {
						$tags = array_map( 'trim', explode( ',', $keywords ) );
						wp_set_post_terms( $post_id, $tags, 'post_tag', false );
					}

					// Ajouter les sources
					if ( $sources ) {
						update_post_meta( $post_id, 'cgt_submission_sources', $sources );
					}

					// Ajouter l'image mise en avant
					if ( $featured_id ) {
						set_post_thumbnail( $post_id, $featured_id );
					}

					// Ajouter le PDF
					if ( $pdf_id ) {
						$pdf_url = wp_get_attachment_url( $pdf_id );
						update_post_meta( $post_id, 'cgt_fichier_pdf', $pdf_url );
					}

					// Visibilité par défaut : public
					update_post_meta( $post_id, 'cgt_visibilite', 'public' );

					// Message de succès avec lien
					$edit_link = get_edit_post_link( $post_id );
					$view_link = get_permalink( $post_id );
					$message = sprintf(
						__( 'Tract créé avec succès ! <a href="%1$s" target="_blank">Voir le tract</a> | <a href="%2$s">Modifier</a>', 'cgt' ),
						esc_url( $view_link ),
						esc_url( $edit_link )
					);

					// Réinitialiser les champs
					$title = $content = $excerpt = $keywords = $sources = '';
					$category = $branche = $featured_id = $pdf_id = 0;
				} else {
					$errors[] = __( 'Erreur lors de la création du tract.', 'cgt' );
				}
			}
		}
	}

	// Récupérer les thématiques et branches
	$categories = get_terms( array( 'taxonomy' => 'thematique', 'hide_empty' => false ) );
	$branches   = get_terms( array( 'taxonomy' => 'branche', 'hide_empty' => false ) );

	// Afficher le formulaire
	cgt_render_post_creation_form( 'tract', $message, $errors, compact( 'categories', 'branches', 'title', 'content', 'excerpt', 'category', 'branche', 'keywords', 'sources', 'featured_id', 'pdf_id' ) );
}

/**
 * Render the post creation form (Article ou Tract)
 */
function cgt_render_post_creation_form( $type, $message, $errors, $data ) {
	$is_article = ( 'article' === $type );
	$post_type_label = $is_article ? __( 'Article', 'cgt' ) : __( 'Tract', 'cgt' );
	$icon = $is_article ? '✍️' : '📄';
	$nonce_action = $is_article ? 'cgt_add_article' : 'cgt_add_tract';
	$nonce_name = $is_article ? 'cgt_add_article_nonce' : 'cgt_add_tract_nonce';
	$submit_name = $is_article ? 'cgt_add_article_submit' : 'cgt_add_tract_submit';
	$field_prefix = $is_article ? 'article' : 'tract';

	?>
	<div class="wrap">
		<div class="submit-article-page">
			<div class="submit-article-container">
				<!-- Header -->
				<header class="submit-article-header">
					<h1>
						<span class="icon"><?php echo $icon; ?></span>
						<?php echo esc_html( sprintf( __( 'Ajouter un %s', 'cgt' ), $post_type_label ) ); ?>
					</h1>
					<p><?php echo esc_html( sprintf( __( 'Créez rapidement un nouveau %s et publiez-le directement sur le site.', 'cgt' ), strtolower( $post_type_label ) ) ); ?></p>
				</header>

				<?php if ( ! empty( $message ) ) : ?>
					<div class="notice success">
						<span style="font-size: 1.5rem;">✓</span>
						<div>
							<?php echo wp_kses_post( $message ); ?>
						</div>
					</div>
				<?php endif; ?>

				<?php if ( ! empty( $errors ) ) : ?>
					<div class="notice error">
						<span style="font-size: 1.5rem;">⚠</span>
						<div>
							<strong><?php esc_html_e( 'Erreurs détectées :', 'cgt' ); ?></strong>
							<ul>
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

					<div class="form-step active">
						<!-- Titre -->
						<div class="form-group">
							<label>
								<?php esc_html_e( 'Titre', 'cgt' ); ?> <span class="required">*</span>
								<span class="hint"><?php esc_html_e( 'Titre clair et explicite', 'cgt' ); ?></span>
							</label>
							<input
								type="text"
								name="cgt_<?php echo esc_attr( $field_prefix ); ?>_title"
								class="form-control"
								value="<?php echo isset( $data['title'] ) ? esc_attr( $data['title'] ) : ''; ?>"
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
							$content = isset( $data['content'] ) ? $data['content'] : '';
							wp_editor(
								$content,
								'cgt_' . $field_prefix . '_content',
								array(
									'textarea_name' => 'cgt_' . $field_prefix . '_content',
									'media_buttons' => true,
									'textarea_rows' => 10,
									'teeny'         => false,
									'tinymce'       => true,
								)
							);
							?>
						</div>

						<!-- Extrait (Facultatif) -->
						<div class="form-group">
							<label>
								<?php esc_html_e( 'Extrait', 'cgt' ); ?> <span class="optional"><?php esc_html_e( '(Facultatif)', 'cgt' ); ?></span>
								<span class="hint"><?php esc_html_e( 'Résumé court pour l\'aperçu', 'cgt' ); ?></span>
							</label>
							<textarea
								name="cgt_<?php echo esc_attr( $field_prefix ); ?>_excerpt"
								class="form-control"
								rows="3"
							><?php echo isset( $data['excerpt'] ) ? esc_textarea( $data['excerpt'] ) : ''; ?></textarea>
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
									<?php foreach ( $data['branches'] as $branch ) : ?>
										<option value="<?php echo esc_attr( $branch->term_id ); ?>" <?php selected( isset( $data['branche'] ) ? $data['branche'] : '', $branch->term_id ); ?>>
											<?php echo esc_html( $branch->name ); ?>
										</option>
									<?php endforeach; ?>
								</select>
							</div>

							<!-- Catégorie/Thématique -->
							<div class="form-group">
								<label>
									<?php echo $is_article ? esc_html__( 'Catégorie', 'cgt' ) : esc_html__( 'Thématique', 'cgt' ); ?>
									<span class="hint"><?php esc_html_e( 'Sélectionnez la plus pertinente', 'cgt' ); ?></span>
								</label>
								<select name="cgt_<?php echo esc_attr( $field_prefix ); ?>_category" class="form-control">
									<option value=""><?php esc_html_e( '— Choisir —', 'cgt' ); ?></option>
									<?php foreach ( $data['categories'] as $cat ) : ?>
										<option value="<?php echo esc_attr( $cat->term_id ); ?>" <?php selected( isset( $data['category'] ) ? $data['category'] : '', $cat->term_id ); ?>>
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
								<span class="hint"><?php esc_html_e( 'Séparés par des virgules (ex: salaire, négociation, grève)', 'cgt' ); ?></span>
							</label>
							<input
								type="text"
								name="cgt_<?php echo esc_attr( $field_prefix ); ?>_keywords"
								class="form-control"
								value="<?php echo isset( $data['keywords'] ) ? esc_attr( $data['keywords'] ) : ''; ?>"
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
								placeholder="<?php esc_attr_e( 'Ex: Article du Monde, Rapport INSEE, etc.', 'cgt' ); ?>"
							><?php echo isset( $data['sources'] ) ? esc_textarea( $data['sources'] ) : ''; ?></textarea>
						</div>

						<!-- Image mise en avant -->
						<div class="form-group">
							<label>
								<?php esc_html_e( 'Image mise en avant', 'cgt' ); ?>
								<span class="hint"><?php esc_html_e( 'Image qui sera affichée dans les cartes', 'cgt' ); ?></span>
							</label>
							<div class="media-upload-container">
								<input type="hidden" name="cgt_<?php echo esc_attr( $field_prefix ); ?>_featured_id" id="<?php echo esc_attr( $field_prefix ); ?>-featured-id" value="<?php echo isset( $data['featured_id'] ) ? esc_attr( $data['featured_id'] ) : ''; ?>">
								<div id="<?php echo esc_attr( $field_prefix ); ?>-featured-preview" class="media-preview">
									<?php if ( ! empty( $data['featured_id'] ) ) : ?>
										<?php echo wp_get_attachment_image( $data['featured_id'], 'medium' ); ?>
									<?php endif; ?>
								</div>
								<button type="button" class="button button-primary" id="<?php echo esc_attr( $field_prefix ); ?>-featured-upload">
									<?php esc_html_e( 'Sélectionner une image', 'cgt' ); ?>
								</button>
								<button type="button" class="button" id="<?php echo esc_attr( $field_prefix ); ?>-featured-remove" style="<?php echo empty( $data['featured_id'] ) ? 'display:none;' : ''; ?>">
									<?php esc_html_e( 'Supprimer l\'image', 'cgt' ); ?>
								</button>
							</div>
						</div>

						<?php if ( ! $is_article ) : ?>
							<!-- PDF (uniquement pour tract) -->
							<div class="form-group">
								<label>
									<?php esc_html_e( 'Fichier PDF', 'cgt' ); ?>
									<span class="hint"><?php esc_html_e( 'Document PDF du tract (max 15 MB)', 'cgt' ); ?></span>
								</label>
								<div class="media-upload-container">
									<input type="hidden" name="cgt_tract_pdf_id" id="tract-pdf-id" value="<?php echo isset( $data['pdf_id'] ) ? esc_attr( $data['pdf_id'] ) : ''; ?>">
									<div id="tract-pdf-preview" class="media-preview">
										<?php if ( ! empty( $data['pdf_id'] ) ) : ?>
											<p>📄 <?php echo esc_html( basename( wp_get_attachment_url( $data['pdf_id'] ) ) ); ?></p>
										<?php endif; ?>
									</div>
									<button type="button" class="button button-primary" id="tract-pdf-upload">
										<?php esc_html_e( 'Sélectionner un PDF', 'cgt' ); ?>
									</button>
									<button type="button" class="button" id="tract-pdf-remove" style="<?php echo empty( $data['pdf_id'] ) ? 'display:none;' : ''; ?>">
										<?php esc_html_e( 'Supprimer le PDF', 'cgt' ); ?>
									</button>
								</div>
							</div>
						<?php endif; ?>

						<!-- Bouton submit -->
						<div class="form-actions">
							<button type="submit" name="<?php echo esc_attr( $submit_name ); ?>" class="button button-primary button-hero">
								<?php echo esc_html( sprintf( __( 'Publier le %s', 'cgt' ), $post_type_label ) ); ?>
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
				$('#<?php echo esc_js( $field_prefix ); ?>-featured-preview').html('<img src="' + attachment.url + '" style="max-width: 300px; height: auto;">');
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

		<?php if ( ! $is_article ) : ?>
		// PDF (uniquement pour tract)
		var pdfUploader;
		$('#tract-pdf-upload').on('click', function(e) {
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
				$('#tract-pdf-id').val(attachment.id);
				$('#tract-pdf-preview').html('<p>📄 ' + attachment.filename + '</p>');
				$('#tract-pdf-remove').show();
			});
			pdfUploader.open();
		});

		$('#tract-pdf-remove').on('click', function(e) {
			e.preventDefault();
			$('#tract-pdf-id').val('');
			$('#tract-pdf-preview').html('');
			$(this).hide();
		});
		<?php endif; ?>
	});
	</script>

	<style>
		.media-upload-container {
			margin-top: 10px;
		}
		.media-preview {
			margin-bottom: 10px;
			padding: 10px;
			background: #f9f9f9;
			border: 1px solid #ddd;
			border-radius: 4px;
			min-height: 50px;
		}
		.media-preview:empty {
			display: none;
		}
		.media-preview img {
			max-width: 300px;
			height: auto;
			display: block;
		}
		.form-actions {
			margin-top: 2rem;
			padding-top: 2rem;
			border-top: 2px solid #e5e7eb;
		}
		.optional {
			font-weight: normal;
			color: #6b7280;
			font-size: 0.9em;
		}
		.notice.success {
			background: #d1fae5;
			border-left: 4px solid #10b981;
			padding: 1rem;
			margin-bottom: 1.5rem;
			border-radius: 4px;
		}
		.notice.success a {
			color: #065f46;
			font-weight: 600;
			text-decoration: underline;
		}
		.notice.success a:hover {
			color: #047857;
		}
	</style>
	<?php
}
