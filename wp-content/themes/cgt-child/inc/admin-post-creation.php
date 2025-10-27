<?php
/**
 * Admin Post Creation - Custom Edit Screens
 * Remplace les pages d'édition standard WordPress pour articles et tracts
 *
 * @package CGT_Child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Enqueue admin styles and scripts for post creation
 */
add_action( 'admin_enqueue_scripts', 'cgt_enqueue_post_creation_assets' );
function cgt_enqueue_post_creation_assets( $hook ) {
	global $post_type;

	// Charger uniquement sur les pages d'édition d'articles et tracts
	if ( ( 'post.php' !== $hook && 'post-new.php' !== $hook ) || ! in_array( $post_type, array( 'post', 'tracts' ), true ) ) {
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

	// Styles inline pour intégration
	wp_add_inline_style(
		'cgt-admin-post-creation',
		'
		.submit-article-container {
			background: #fff;
			border: 1px solid #c3c4c7;
			border-radius: 4px;
			padding: 0;
			margin: 20px 0;
		}
		.submit-article-header {
			background: linear-gradient(135deg, #c8102e 0%, #a00d26 100%);
			color: white;
			padding: 20px;
			border-radius: 4px 4px 0 0;
		}
		.submit-article-header h1 {
			color: white;
			margin: 0 0 10px 0;
			font-size: 24px;
		}
		.submit-article-header p {
			margin: 0;
			opacity: 0.9;
		}
		.cgt-submit-article {
			padding: 20px;
		}
		.form-step {
			display: block;
		}
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
		#post-body-content {
			margin-bottom: 20px;
		}
		'
	);
}

/**
 * Remove default editor and meta boxes for articles and tracts
 */
add_action( 'admin_init', 'cgt_remove_default_editor' );
function cgt_remove_default_editor() {
	remove_post_type_support( 'post', 'editor' );
	remove_post_type_support( 'tracts', 'editor' );
}

/**
 * Add custom meta boxes for articles and tracts
 */
add_action( 'add_meta_boxes', 'cgt_add_custom_post_meta_boxes' );
function cgt_add_custom_post_meta_boxes() {
	// Meta box pour les articles
	add_meta_box(
		'cgt_article_content',
		__( 'Contenu de l\'article', 'cgt' ),
		'cgt_render_article_meta_box',
		'post',
		'normal',
		'high'
	);

	// Meta box pour les tracts
	add_meta_box(
		'cgt_tract_content',
		__( 'Contenu du tract', 'cgt' ),
		'cgt_render_tract_meta_box',
		'tracts',
		'normal',
		'high'
	);
}

/**
 * Render article meta box
 */
function cgt_render_article_meta_box( $post ) {
	// Récupérer les valeurs existantes
	$content    = $post->post_content;
	$excerpt    = $post->post_excerpt;
	$category   = wp_get_post_categories( $post->ID );
	$category   = ! empty( $category ) ? $category[0] : 0;
	$branche_terms = wp_get_post_terms( $post->ID, 'branche' );
	$branche    = ! empty( $branche_terms ) ? $branche_terms[0]->term_id : 0;
	$tags       = wp_get_post_tags( $post->ID, array( 'fields' => 'names' ) );
	$keywords   = implode( ', ', $tags );
	$sources    = get_post_meta( $post->ID, 'cgt_submission_sources', true );
	$featured_id = get_post_thumbnail_id( $post->ID );

	// Récupérer les catégories et branches
	$categories = get_categories( array( 'hide_empty' => false ) );
	$branches   = get_terms( array( 'taxonomy' => 'branche', 'hide_empty' => false ) );

	wp_nonce_field( 'cgt_save_article_meta', 'cgt_article_meta_nonce' );

	cgt_render_post_fields( 'article', compact( 'content', 'excerpt', 'category', 'branche', 'keywords', 'sources', 'featured_id', 'categories', 'branches' ) );
}

/**
 * Render tract meta box
 */
function cgt_render_tract_meta_box( $post ) {
	// Récupérer les valeurs existantes
	$content    = $post->post_content;
	$excerpt    = $post->post_excerpt;
	$category_terms = wp_get_post_terms( $post->ID, 'thematique' );
	$category   = ! empty( $category_terms ) ? $category_terms[0]->term_id : 0;
	$branche_terms = wp_get_post_terms( $post->ID, 'branche' );
	$branche    = ! empty( $branche_terms ) ? $branche_terms[0]->term_id : 0;
	$tags       = wp_get_post_tags( $post->ID, array( 'fields' => 'names' ) );
	$keywords   = implode( ', ', $tags );
	$sources    = get_post_meta( $post->ID, 'cgt_submission_sources', true );
	$featured_id = get_post_thumbnail_id( $post->ID );
	$pdf_url    = get_post_meta( $post->ID, 'cgt_fichier_pdf', true );
	$pdf_id     = $pdf_url ? attachment_url_to_postid( $pdf_url ) : 0;

	// Récupérer les thématiques et branches
	$categories = get_terms( array( 'taxonomy' => 'thematique', 'hide_empty' => false ) );
	$branches   = get_terms( array( 'taxonomy' => 'branche', 'hide_empty' => false ) );

	wp_nonce_field( 'cgt_save_tract_meta', 'cgt_tract_meta_nonce' );

	cgt_render_post_fields( 'tract', compact( 'content', 'excerpt', 'category', 'branche', 'keywords', 'sources', 'featured_id', 'pdf_id', 'categories', 'branches' ) );
}

/**
 * Render post fields
 */
function cgt_render_post_fields( $type, $data ) {
	$is_article = ( 'article' === $type );
	$post_type_label = $is_article ? __( 'Article', 'cgt' ) : __( 'Tract', 'cgt' );
	$icon = $is_article ? '✍️' : '📄';
	$field_prefix = $is_article ? 'article' : 'tract';

	// Initialiser les variables par défaut si elles n'existent pas
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

	?>
	<div class="submit-article-container">
		<header class="submit-article-header">
			<h1>
				<span class="icon"><?php echo esc_html( $icon ); ?></span>
				<?php echo esc_html( sprintf( __( 'Informations du %s', 'cgt' ), strtolower( $post_type_label ) ) ); ?>
			</h1>
			<p><?php esc_html_e( 'Remplissez les informations ci-dessous pour votre publication.', 'cgt' ); ?></p>
		</header>

		<div class="cgt-submit-article">
			<div class="form-step active">
				<!-- Description (Contenu) -->
				<div class="form-group">
					<label>
						<?php esc_html_e( 'Description', 'cgt' ); ?> <span class="required">*</span>
						<span class="hint"><?php esc_html_e( 'Contenu complet de votre publication', 'cgt' ); ?></span>
					</label>
					<?php
					wp_editor(
						$content,
						'content',
						array(
							'textarea_name' => 'content',
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
						name="excerpt"
						class="form-control"
						rows="3"
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
							<?php echo $is_article ? esc_html__( 'Catégorie', 'cgt' ) : esc_html__( 'Thématique', 'cgt' ); ?>
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
						<span class="hint"><?php esc_html_e( 'Séparés par des virgules (ex: salaire, négociation, grève)', 'cgt' ); ?></span>
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
						placeholder="<?php esc_attr_e( 'Ex: Article du Monde, Rapport INSEE, etc.', 'cgt' ); ?>"
					><?php echo esc_textarea( $sources ); ?></textarea>
				</div>

				<!-- Image mise en avant -->
				<div class="form-group">
					<label>
						<?php esc_html_e( 'Image mise en avant', 'cgt' ); ?>
						<span class="hint"><?php esc_html_e( 'Image qui sera affichée dans les cartes', 'cgt' ); ?></span>
					</label>
					<div class="media-upload-container">
						<input type="hidden" name="cgt_<?php echo esc_attr( $field_prefix ); ?>_featured_id" id="<?php echo esc_attr( $field_prefix ); ?>-featured-id" value="<?php echo esc_attr( $featured_id ); ?>">
						<div id="<?php echo esc_attr( $field_prefix ); ?>-featured-preview" class="media-preview">
							<?php if ( ! empty( $featured_id ) ) : ?>
								<?php echo wp_get_attachment_image( $featured_id, 'medium' ); ?>
							<?php endif; ?>
						</div>
						<button type="button" class="button button-primary" id="<?php echo esc_attr( $field_prefix ); ?>-featured-upload">
							<?php esc_html_e( 'Sélectionner une image', 'cgt' ); ?>
						</button>
						<button type="button" class="button" id="<?php echo esc_attr( $field_prefix ); ?>-featured-remove" style="<?php echo empty( $featured_id ) ? 'display:none;' : ''; ?>">
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
							<input type="hidden" name="cgt_tract_pdf_id" id="tract-pdf-id" value="<?php echo esc_attr( $pdf_id ); ?>">
							<div id="tract-pdf-preview" class="media-preview">
								<?php if ( ! empty( $pdf_id ) ) : ?>
									<p>📄 <?php echo esc_html( basename( wp_get_attachment_url( $pdf_id ) ) ); ?></p>
								<?php endif; ?>
							</div>
							<button type="button" class="button button-primary" id="tract-pdf-upload">
								<?php esc_html_e( 'Sélectionner un PDF', 'cgt' ); ?>
							</button>
							<button type="button" class="button" id="tract-pdf-remove" style="<?php echo empty( $pdf_id ) ? 'display:none;' : ''; ?>">
								<?php esc_html_e( 'Supprimer le PDF', 'cgt' ); ?>
							</button>
						</div>
					</div>
				<?php endif; ?>
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
	<?php
}

/**
 * Save post meta data
 */
add_action( 'save_post', 'cgt_save_post_meta_data', 10, 2 );
function cgt_save_post_meta_data( $post_id, $post ) {
	// Vérifier les permissions
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	// Éviter l'auto-save
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	// Vérifier le type de post
	if ( ! in_array( $post->post_type, array( 'post', 'tracts' ), true ) ) {
		return;
	}

	$is_article = ( 'post' === $post->post_type );

	// Vérifier le nonce
	$nonce_name = $is_article ? 'cgt_article_meta_nonce' : 'cgt_tract_meta_nonce';
	$nonce_action = $is_article ? 'cgt_save_article_meta' : 'cgt_save_tract_meta';

	if ( ! isset( $_POST[ $nonce_name ] ) || ! wp_verify_nonce( sanitize_key( $_POST[ $nonce_name ] ), $nonce_action ) ) {
		return;
	}

	$field_prefix = $is_article ? 'article' : 'tract';

	// Sauvegarder le contenu et l'extrait (gérés par WordPress)
	// Pas besoin de les sauvegarder ici car WordPress le fait automatiquement

	// Sauvegarder la branche
	$branche = isset( $_POST[ 'cgt_' . $field_prefix . '_branche' ] ) ? absint( $_POST[ 'cgt_' . $field_prefix . '_branche' ] ) : 0;
	if ( $branche ) {
		wp_set_post_terms( $post_id, array( $branche ), 'branche', false );
	} else {
		wp_set_post_terms( $post_id, array(), 'branche', false );
	}

	// Sauvegarder la catégorie/thématique
	$category = isset( $_POST[ 'cgt_' . $field_prefix . '_category' ] ) ? absint( $_POST[ 'cgt_' . $field_prefix . '_category' ] ) : 0;
	if ( $category ) {
		if ( $is_article ) {
			wp_set_post_categories( $post_id, array( $category ) );
		} else {
			wp_set_post_terms( $post_id, array( $category ), 'thematique', false );
		}
	} else {
		if ( $is_article ) {
			wp_set_post_categories( $post_id, array() );
		} else {
			wp_set_post_terms( $post_id, array(), 'thematique', false );
		}
	}

	// Sauvegarder les mots-clés
	$keywords = isset( $_POST[ 'cgt_' . $field_prefix . '_keywords' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'cgt_' . $field_prefix . '_keywords' ] ) ) : '';
	if ( $keywords ) {
		$tags = array_map( 'trim', explode( ',', $keywords ) );
		wp_set_post_terms( $post_id, $tags, 'post_tag', false );
	} else {
		wp_set_post_terms( $post_id, array(), 'post_tag', false );
	}

	// Sauvegarder les sources
	$sources = isset( $_POST[ 'cgt_' . $field_prefix . '_sources' ] ) ? sanitize_textarea_field( wp_unslash( $_POST[ 'cgt_' . $field_prefix . '_sources' ] ) ) : '';
	if ( $sources ) {
		update_post_meta( $post_id, 'cgt_submission_sources', $sources );
	} else {
		delete_post_meta( $post_id, 'cgt_submission_sources' );
	}

	// Sauvegarder l'image mise en avant
	$featured_id = isset( $_POST[ 'cgt_' . $field_prefix . '_featured_id' ] ) ? absint( $_POST[ 'cgt_' . $field_prefix . '_featured_id' ] ) : 0;
	if ( $featured_id ) {
		set_post_thumbnail( $post_id, $featured_id );
	} else {
		delete_post_thumbnail( $post_id );
	}

	// Sauvegarder le PDF (uniquement pour les tracts)
	if ( ! $is_article ) {
		$pdf_id = isset( $_POST['cgt_tract_pdf_id'] ) ? absint( $_POST['cgt_tract_pdf_id'] ) : 0;
		if ( $pdf_id ) {
			$pdf_url = wp_get_attachment_url( $pdf_id );
			update_post_meta( $post_id, 'cgt_fichier_pdf', $pdf_url );
		} else {
			delete_post_meta( $post_id, 'cgt_fichier_pdf' );
		}

		// S'assurer que la visibilité est définie pour les nouveaux tracts
		if ( ! get_post_meta( $post_id, 'cgt_visibilite', true ) ) {
			update_post_meta( $post_id, 'cgt_visibilite', 'public' );
		}
	}
}
