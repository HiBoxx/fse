<?php
/**
 * Shortcodes.
 *
 * @package CGT_Child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Obtenir le type MIME réel d'un fichier (sécurisé)
 *
 * @param string $file_path Chemin vers le fichier temporaire.
 * @return string|false Type MIME ou false en cas d'erreur.
 */
function cgt_get_file_mime_type( $file_path ) {
	// Utiliser finfo pour une détection fiable du type MIME
	if ( function_exists( 'finfo_open' ) ) {
		$finfo = finfo_open( FILEINFO_MIME_TYPE );
		if ( $finfo ) {
			$mime_type = finfo_file( $finfo, $file_path );
			finfo_close( $finfo );
			return $mime_type;
		}
	}

	// Fallback avec mime_content_type si finfo n'est pas disponible
	if ( function_exists( 'mime_content_type' ) ) {
		return mime_content_type( $file_path );
	}

	// Dernier recours : utiliser wp_check_filetype
	$filetype = wp_check_filetype( $file_path );
	return $filetype['type'] ?? false;
}

/**
 * Render the tracts shortcode.
 *
 * @return string
 */
function cgt_shortcode_tracts() {
	$paged = isset( $_GET['cgt_page'] ) ? max( 1, absint( $_GET['cgt_page'] ) ) : 1;
	$args  = array(
		'post_type'      => 'tracts',
		'posts_per_page' => 6,
		'paged'          => $paged,
	);

	$taxonomies = array(
		'branche',
		'thematique',
		'zone_internationale',
	);

	if ( ! cgt_user_can_read_private() ) {
		$args['meta_query'][] = array(
			'key'     => 'cgt_visibilite',
			'value'   => 'prive',
			'compare' => '!=',
		);
	}

	foreach ( $taxonomies as $taxonomy ) {
		if ( isset( $_GET[ $taxonomy ] ) && $_GET[ $taxonomy ] ) {
			$args['tax_query'][] = array(
				'taxonomy' => $taxonomy,
				'field'    => 'slug',
				'terms'    => sanitize_text_field( wp_unslash( $_GET[ $taxonomy ] ) ),
			);
		}
	}

	if ( isset( $_GET['cgt_search'] ) ) {
		$args['s'] = sanitize_text_field( wp_unslash( $_GET['cgt_search'] ) );
	}

	$query = new WP_Query( $args );

	ob_start();
	?>
	<form class="filters" method="get">
		<?php foreach ( $taxonomies as $taxonomy ) : ?>
			<label>
				<span class="sr-only"><?php echo esc_html( get_taxonomy( $taxonomy )->labels->singular_name ); ?></span>
				<select name="<?php echo esc_attr( $taxonomy ); ?>">
					<option value=""><?php echo esc_html( get_taxonomy( $taxonomy )->labels->all_items ); ?></option>
					<?php
					$terms = get_terms(
						array(
							'taxonomy'   => $taxonomy,
							'hide_empty' => false,
						)
					);
					$current = isset( $_GET[ $taxonomy ] ) ? sanitize_text_field( wp_unslash( $_GET[ $taxonomy ] ) ) : '';
					foreach ( $terms as $term ) :
						?>
						<option value="<?php echo esc_attr( $term->slug ); ?>" <?php selected( $current, $term->slug ); ?>>
							<?php echo esc_html( $term->name ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</label>
		<?php endforeach; ?>
		<label>
			<span class="sr-only"><?php esc_html_e( 'Rechercher un tract', 'cgt' ); ?></span>
			<input type="search" name="cgt_search" value="<?php echo isset( $_GET['cgt_search'] ) ? esc_attr( wp_unslash( $_GET['cgt_search'] ) ) : ''; ?>" placeholder="<?php esc_attr_e( 'Rechercher…', 'cgt' ); ?>">
		</label>
		<button class="btn" type="submit"><?php esc_html_e( 'Filtrer', 'cgt' ); ?></button>
	</form>

	<?php if ( $query->have_posts() ) : ?>
		<div class="document-grid">
			<?php
			while ( $query->have_posts() ) :
				$query->the_post();
				get_template_part( 'parts/card', null, array( 'context' => 'tract' ) );
			endwhile;
			?>
		</div>
		<?php
		$total_pages = $query->max_num_pages;
		if ( $total_pages > 1 ) :
			$current_page = max( 1, $paged );
			?>
			<nav class="pagination" aria-label="<?php esc_attr_e( 'Pagination', 'cgt' ); ?>">
				<?php
				echo wp_kses_post(
					paginate_links(
						array(
							'format'  => '?cgt_page=%#%',
							'current' => $current_page,
							'total'   => $total_pages,
							'add_args'=> array_filter(
								array(
									'cgt_search'        => isset( $_GET['cgt_search'] ) ? sanitize_text_field( wp_unslash( $_GET['cgt_search'] ) ) : '',
									'branche'           => isset( $_GET['branche'] ) ? sanitize_text_field( wp_unslash( $_GET['branche'] ) ) : '',
									'thematique'        => isset( $_GET['thematique'] ) ? sanitize_text_field( wp_unslash( $_GET['thematique'] ) ) : '',
									'zone_internationale' => isset( $_GET['zone_internationale'] ) ? sanitize_text_field( wp_unslash( $_GET['zone_internationale'] ) ) : '',
								)
							),
						)
					)
				);
				?>
			</nav>
		<?php endif; ?>
	<?php else : ?>
		<p><?php esc_html_e( 'Aucun tract ne correspond à votre recherche.', 'cgt' ); ?></p>
	<?php endif; ?>

	<?php
	wp_reset_postdata();

	return ob_get_clean();
}
add_shortcode( 'cgt_tracts', 'cgt_shortcode_tracts' );

/**
 * Render the communiqués shortcode.
 *
 * @return string
 */
function cgt_shortcode_communiques() {
	$args  = array(
		'post_type'      => 'communiques_de_presse',
		'posts_per_page' => 3,
	);
	$query = new WP_Query( $args );

	ob_start();
	if ( $query->have_posts() ) :
		?>
		<div class="post-grid">
			<?php
			while ( $query->have_posts() ) :
				$query->the_post();
				get_template_part( 'parts/card', null, array( 'context' => 'communique' ) );
			endwhile;
			?>
		</div>
		<p><a class="btn" href="<?php echo esc_url( get_post_type_archive_link( 'communiques_de_presse' ) ); ?>"><?php esc_html_e( 'Voir tous les communiqués', 'cgt' ); ?></a></p>
	<?php else : ?>
		<p><?php esc_html_e( 'Aucun communiqué pour le moment.', 'cgt' ); ?></p>
	<?php endif; ?>
	<?php
	wp_reset_postdata();

	return ob_get_clean();
}
add_shortcode( 'cgt_communiques', 'cgt_shortcode_communiques' );

/**
 * Render the questions shortcode.
 *
 * @return string
 */
function cgt_shortcode_questions() {
	$message = '';

	if ( 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['cgt_question_nonce'] ) ) {
		if ( ! is_user_logged_in() ) {
			$message = __( 'Vous devez être connecté pour poser une question.', 'cgt' );
		} elseif ( ! wp_verify_nonce( sanitize_key( $_POST['cgt_question_nonce'] ), 'cgt_question' ) ) {
			$message = __( 'Jeton de sécurité invalide.', 'cgt' );
		} elseif ( ! cgt_check_honeypot( 'cgt_hp_question' ) ) {
			cgt_log_spam_attempt( 'question', $_POST );
			$message = __( 'Erreur de validation.', 'cgt' );
		} elseif ( ! cgt_check_rate_limit( 'question', 5, 3600 ) ) {
			$message = __( 'Vous avez atteint la limite de questions. Veuillez réessayer plus tard.', 'cgt' );
		} else {
			$question = isset( $_POST['cgt_question'] ) ? wp_kses_post( wp_unslash( $_POST['cgt_question'] ) ) : '';
			$subject  = isset( $_POST['cgt_question_subject'] ) ? sanitize_text_field( wp_unslash( $_POST['cgt_question_subject'] ) ) : '';

			if ( empty( $question ) ) {
				$message = __( 'Merci de détailler votre question.', 'cgt' );
			} else {
				$current_user = wp_get_current_user();
				$post_id      = wp_insert_post(
					array(
						'post_type'   => 'cgt_contact',
						'post_status' => 'private',
						'post_title'  => $subject ? $subject : wp_trim_words( wp_strip_all_tags( $question ), 8, '…' ),
						'post_content'=> $question,
						'post_author' => get_current_user_id(),
					)
				);

				if ( $post_id && 0 !== $post_id && ! is_wp_error( $post_id ) ) {
					$name  = $current_user ? $current_user->display_name : '';
					$email = $current_user ? $current_user->user_email : '';

					update_post_meta( $post_id, 'cgt_submission_name', $name );
					update_post_meta( $post_id, 'cgt_submission_email', $email );
					update_post_meta( $post_id, 'cgt_submission_phone', '' );
					update_post_meta( $post_id, 'cgt_submission_subject', $subject );
					update_post_meta( $post_id, 'cgt_submission_message', $question );
					update_post_meta( $post_id, 'cgt_contact_type', 'question' );
					update_post_meta( $post_id, 'cgt_contact_user', get_current_user_id() );

					$message = __( 'Merci ! Votre question est en attente de modération.', 'cgt' );
					wp_mail(
						get_option( 'admin_email' ),
						sprintf( '[CGT] %s', __( 'Nouvelle question adhérent', 'cgt' ) ),
						sprintf(
							'%1$s %2$s',
							__( 'Une nouvelle question vient d’être soumise :', 'cgt' ),
							admin_url( sprintf( 'post.php?post=%d&action=edit', $post_id ) )
						)
					);
				} else {
					$message = __( 'Une erreur est survenue, veuillez réessayer.', 'cgt' );
				}
			}
		}
	}

	ob_start();

	if ( ! is_user_logged_in() ) {
		printf(
			'<p class="notice">%s</p>',
			wp_kses(
				sprintf(
					/* translators: %s: login URL. */
					__( 'Connectez-vous pour poser une question : <a href="%s">Accéder à la connexion</a>', 'cgt' ),
					esc_url( wp_login_url( get_permalink() ) )
				),
				array(
					'a' => array(
						'href' => array(),
					),
				)
			)
		);
		return ob_get_clean();
	}

	if ( $message ) {
		echo '<p class="notice">' . esc_html( $message ) . '</p>';
	}
	?>
	<form class="question-form" method="post">
		<?php echo cgt_render_honeypot( 'cgt_hp_question' ); ?>
		<label>
			<?php esc_html_e( 'Sujet (optionnel)', 'cgt' ); ?>
			<input type="text" name="cgt_question_subject" maxlength="120">
		</label>
		<label>
			<?php esc_html_e( 'Votre question', 'cgt' ); ?>
			<textarea name="cgt_question" rows="6" required></textarea>
		</label>
		<?php wp_nonce_field( 'cgt_question', 'cgt_question_nonce' ); ?>
		<button class="btn" type="submit"><?php esc_html_e( 'Envoyer ma question', 'cgt' ); ?></button>
	</form>
	<?php

	return ob_get_clean();
}
add_shortcode( 'cgt_questions', 'cgt_shortcode_questions' );

/**
 * Render article submission form.
 *
 * @return string
 */
function cgt_shortcode_submit_article() {
	$message = '';
	$errors  = array();

	if ( 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['cgt_submit_article_nonce'] ) ) {
		if ( ! function_exists( 'media_handle_upload' ) ) {
			require_once ABSPATH . 'wp-admin/includes/media.php';
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/image.php';
		}

		if ( ! wp_verify_nonce( sanitize_key( $_POST['cgt_submit_article_nonce'] ), 'cgt_submit_article' ) ) {
			$errors[] = __( 'Jeton de sécurité invalide. Merci de recharger la page.', 'cgt' );
		} elseif ( ! cgt_check_honeypot( 'cgt_hp_article' ) ) {
			cgt_log_spam_attempt( 'article', $_POST );
			$errors[] = __( 'Erreur de validation.', 'cgt' );
		} elseif ( ! cgt_check_rate_limit( 'article', 3, 3600 ) ) {
			$errors[] = __( 'Vous avez atteint la limite de soumissions. Veuillez réessayer plus tard.', 'cgt' );
		} else {
			$title      = isset( $_POST['cgt_article_title'] ) ? sanitize_text_field( wp_unslash( $_POST['cgt_article_title'] ) ) : '';
			$content    = isset( $_POST['cgt_article_content'] ) ? wp_kses_post( wp_unslash( $_POST['cgt_article_content'] ) ) : '';
			$category   = isset( $_POST['cgt_article_category'] ) ? absint( $_POST['cgt_article_category'] ) : 0;
			$keywords   = isset( $_POST['cgt_article_keywords'] ) ? sanitize_text_field( wp_unslash( $_POST['cgt_article_keywords'] ) ) : '';
			$name       = isset( $_POST['cgt_article_author_name'] ) ? sanitize_text_field( wp_unslash( $_POST['cgt_article_author_name'] ) ) : '';
			$email      = isset( $_POST['cgt_article_author_email'] ) ? sanitize_email( wp_unslash( $_POST['cgt_article_author_email'] ) ) : '';
			$sources    = isset( $_POST['cgt_article_sources'] ) ? sanitize_textarea_field( wp_unslash( $_POST['cgt_article_sources'] ) ) : '';
			$accept_cgv = isset( $_POST['cgt_article_accept_cgv'] );

			if ( empty( $title ) ) {
				$errors[] = __( 'Merci de renseigner un titre pour votre article.', 'cgt' );
			}

			if ( empty( $content ) ) {
				$errors[] = __( 'Merci de rédiger le contenu de votre article.', 'cgt' );
			}

			if ( ! $accept_cgv ) {
				$errors[] = __( 'Vous devez accepter les conditions pour soumettre votre article.', 'cgt' );
			}

			if ( empty( $errors ) ) {
				$post_args = array(
					'post_type'    => 'post',
					'post_title'   => $title,
					'post_content' => $content,
					'post_status'  => 'pending',
					'post_author'  => get_current_user_id() ? get_current_user_id() : 0,
					'post_category'=> $category ? array( $category ) : array(),
				); // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn

				$post_id = wp_insert_post( $post_args );

				if ( $post_id && ! is_wp_error( $post_id ) ) {
					if ( $keywords ) {
						$tags = array_map( 'trim', explode( ',', $keywords ) );
						wp_set_post_terms( $post_id, $tags, 'post_tag', false );
					}

					update_post_meta( $post_id, 'cgt_submission_name', $name );
					update_post_meta( $post_id, 'cgt_submission_email', $email );
					update_post_meta( $post_id, 'cgt_submission_sources', $sources );

					// Upload de l'image avec validation MIME
					if ( ! empty( $_FILES['cgt_article_featured']['name'] ) ) {
						// Validation MIME pour les images
						$allowed_image_types = array( 'image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp' );
						$file_mime_type = cgt_get_file_mime_type( $_FILES['cgt_article_featured']['tmp_name'] );

						if ( in_array( $file_mime_type, $allowed_image_types, true ) ) {
							// Validation de la taille (5 MB max)
							if ( $_FILES['cgt_article_featured']['size'] <= 5 * 1024 * 1024 ) {
								$attachment_id = media_handle_upload( 'cgt_article_featured', $post_id );
								if ( ! is_wp_error( $attachment_id ) ) {
									set_post_thumbnail( $post_id, $attachment_id );
								} else {
									$errors[] = __( 'Erreur lors du téléchargement de l\'image.', 'cgt' );
								}
							} else {
								$errors[] = __( 'L\'image est trop volumineuse (max 5 MB).', 'cgt' );
							}
						} else {
							$errors[] = __( 'Format d\'image non autorisé. Utilisez JPG, PNG, GIF ou WebP.', 'cgt' );
						}
					}

					// Upload du PDF avec validation MIME
					if ( ! empty( $_FILES['cgt_article_pdf']['name'] ) ) {
						// Validation MIME pour les PDF
						$file_mime_type = cgt_get_file_mime_type( $_FILES['cgt_article_pdf']['tmp_name'] );

						if ( 'application/pdf' === $file_mime_type ) {
							// Validation de la taille (15 MB max)
							if ( $_FILES['cgt_article_pdf']['size'] <= 15 * 1024 * 1024 ) {
								$pdf_id = media_handle_upload( 'cgt_article_pdf', $post_id );
								if ( ! is_wp_error( $pdf_id ) ) {
									update_post_meta( $post_id, 'cgt_submission_pdf', $pdf_id );
								} else {
									$errors[] = __( 'Erreur lors du téléchargement du PDF.', 'cgt' );
								}
							} else {
								$errors[] = __( 'Le PDF est trop volumineux (max 15 MB).', 'cgt' );
							}
						} else {
							$errors[] = __( 'Seuls les fichiers PDF sont autorisés.', 'cgt' );
						}
					}

					if ( empty( $errors ) ) {
						$message = __( 'Merci ! Votre article a été soumis et se trouve en attente de validation.', 'cgt' );
					}
				} else {
					$errors[] = __( 'Une erreur est survenue lors de la soumission. Merci de réessayer.', 'cgt' );
				}
			}
		}
	}

	$categories = get_categories( array( 'hide_empty' => false ) );

	ob_start();

	?>
	<div class="submit-article-page">
		<div class="submit-article-container">
			<!-- Header -->
			<header class="submit-article-header">
				<h1>
					<span class="icon">✍️</span>
					<?php esc_html_e( 'Proposer un article', 'cgt' ); ?>
				</h1>
				<p><?php esc_html_e( 'Partagez vos bulletins, tracts ou communications syndicales en quelques étapes simples.', 'cgt' ); ?></p>
			</header>

			<?php if ( ! empty( $message ) ) : ?>
				<div class="notice success">
					<span style="font-size: 1.5rem;">✓</span>
					<p><?php echo esc_html( $message ); ?></p>
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

			<!-- Progress Bar -->
			<div class="progress-bar-container">
				<div class="progress-steps">
					<div class="progress-line" style="width: 0%"></div>
					<div class="progress-step active" data-step="1">
						<div class="progress-step-circle">1</div>
						<span class="progress-step-label"><?php esc_html_e( 'Informations', 'cgt' ); ?></span>
					</div>
					<div class="progress-step" data-step="2">
						<div class="progress-step-circle">2</div>
						<span class="progress-step-label"><?php esc_html_e( 'Contenu', 'cgt' ); ?></span>
					</div>
					<div class="progress-step" data-step="3">
						<div class="progress-step-circle">3</div>
						<span class="progress-step-label"><?php esc_html_e( 'Médias', 'cgt' ); ?></span>
					</div>
					<div class="progress-step" data-step="4">
						<div class="progress-step-circle">4</div>
						<span class="progress-step-label"><?php esc_html_e( 'Coordonnées', 'cgt' ); ?></span>
					</div>
				</div>
			</div>

			<!-- Form -->
			<form class="cgt-submit-article" method="post" enctype="multipart/form-data" id="article-form">
				<?php echo cgt_render_honeypot( 'cgt_hp_article' ); ?>
				<?php wp_nonce_field( 'cgt_submit_article', 'cgt_submit_article_nonce' ); ?>

				<!-- ÉTAPE 1: Informations de base -->
				<div class="form-step active" data-step="1">
					<div class="step-header">
						<div class="step-icon">📝</div>
						<h2><?php esc_html_e( 'Informations de l\'article', 'cgt' ); ?></h2>
					</div>
					<p class="step-description"><?php esc_html_e( 'Commencez par nous donner les informations essentielles sur votre article.', 'cgt' ); ?></p>

					<div class="form-group">
						<label>
							<?php esc_html_e( 'Titre de l\'article', 'cgt' ); ?> <span class="required">*</span>
							<span class="hint"><?php esc_html_e( 'Un titre clair et accrocheur pour votre article', 'cgt' ); ?></span>
						</label>
						<input type="text" name="cgt_article_title" id="article-title" class="form-control" value="<?php echo isset( $title ) ? esc_attr( $title ) : ''; ?>" required placeholder="<?php esc_attr_e( 'Ex: Accord salarial 2024 signé', 'cgt' ); ?>">
						<div class="char-counter" data-field="article-title" data-max="200"></div>
						<span class="form-error-message"><?php esc_html_e( 'Le titre est requis', 'cgt' ); ?></span>
					</div>

					<div class="form-row">
						<div class="form-group">
							<label>
								<?php esc_html_e( 'Catégorie', 'cgt' ); ?>
								<span class="hint"><?php esc_html_e( 'Sélectionnez la catégorie la plus pertinente', 'cgt' ); ?></span>
							</label>
							<select name="cgt_article_category" id="article-category" class="form-control">
								<option value=""><?php esc_html_e( '— Choisir une catégorie —', 'cgt' ); ?></option>
								<?php foreach ( $categories as $cat ) : ?>
									<option value="<?php echo esc_attr( $cat->term_id ); ?>" <?php selected( isset( $category ) ? $category : '', $cat->term_id ); ?>><?php echo esc_html( $cat->name ); ?></option>
								<?php endforeach; ?>
							</select>
						</div>

						<div class="form-group">
							<label>
								<?php esc_html_e( 'Mots-clés', 'cgt' ); ?>
								<span class="hint"><?php esc_html_e( 'Séparés par des virgules', 'cgt' ); ?></span>
							</label>
							<input type="text" name="cgt_article_keywords" id="article-keywords" class="form-control" value="<?php echo isset( $keywords ) ? esc_attr( $keywords ) : ''; ?>" placeholder="<?php esc_attr_e( 'Ex: salaire, négociation, accord', 'cgt' ); ?>">
						</div>
					</div>

					<div class="form-navigation">
						<div></div>
						<button type="button" class="btn btn-primary" data-next-step="2">
							<?php esc_html_e( 'Suivant', 'cgt' ); ?> →
						</button>
					</div>
				</div>

				<!-- ÉTAPE 2: Contenu -->
				<div class="form-step" data-step="2">
					<div class="step-header">
						<div class="step-icon">📄</div>
						<h2><?php esc_html_e( 'Contenu de l\'article', 'cgt' ); ?></h2>
					</div>
					<p class="step-description"><?php esc_html_e( 'Rédigez le contenu principal de votre article.', 'cgt' ); ?></p>

					<div class="form-group">
						<label>
							<?php esc_html_e( 'Contenu', 'cgt' ); ?> <span class="required">*</span>
							<span class="hint"><?php esc_html_e( 'Rédigez votre article de manière claire et structurée', 'cgt' ); ?></span>
						</label>
						<textarea name="cgt_article_content" id="article-content" class="form-control" required placeholder="<?php esc_attr_e( 'Rédigez votre article...', 'cgt' ); ?>"><?php echo isset( $content ) ? esc_textarea( $content ) : ''; ?></textarea>
						<div class="char-counter" data-field="article-content" data-max="10000"></div>
						<span class="form-error-message"><?php esc_html_e( 'Le contenu est requis', 'cgt' ); ?></span>
					</div>

					<div class="form-group">
						<label>
							<?php esc_html_e( 'Sources et références', 'cgt' ); ?>
							<span class="hint"><?php esc_html_e( 'Indiquez vos sources ou références si nécessaire', 'cgt' ); ?></span>
						</label>
						<textarea name="cgt_article_sources" id="article-sources" class="form-control" rows="4" placeholder="<?php esc_attr_e( 'Ex: Accord d\'entreprise du 15/01/2024, article INSEE...', 'cgt' ); ?>"><?php echo isset( $sources ) ? esc_textarea( $sources ) : ''; ?></textarea>
					</div>

					<div class="form-navigation">
						<button type="button" class="btn btn-back" data-prev-step="1">
							← <?php esc_html_e( 'Retour', 'cgt' ); ?>
						</button>
						<button type="button" class="btn btn-primary" data-next-step="3">
							<?php esc_html_e( 'Suivant', 'cgt' ); ?> →
						</button>
					</div>
				</div>

				<!-- ÉTAPE 3: Médias -->
				<div class="form-step" data-step="3">
					<div class="step-header">
						<div class="step-icon">🖼️</div>
						<h2><?php esc_html_e( 'Fichiers et médias', 'cgt' ); ?></h2>
					</div>
					<p class="step-description"><?php esc_html_e( 'Ajoutez une image à la une ou un fichier PDF à votre article (facultatif).', 'cgt' ); ?></p>

					<div class="form-group">
						<label><?php esc_html_e( 'Image à la une', 'cgt' ); ?></label>
						<div class="file-upload-container" id="image-upload-zone" data-field="cgt_article_featured">
							<div class="file-upload-icon">📷</div>
							<div class="file-upload-text"><?php esc_html_e( 'Cliquez ou glissez une image ici', 'cgt' ); ?></div>
							<div class="file-upload-hint"><?php esc_html_e( 'JPG, PNG ou WebP - Max 5 Mo', 'cgt' ); ?></div>
							<input type="file" name="cgt_article_featured" id="cgt_article_featured" accept="image/jpeg,image/png,image/webp">
						</div>
						<div class="image-preview" id="image-preview"></div>
						<div class="file-preview" id="image-file-preview">
							<span class="file-preview-icon">✓</span>
							<div class="file-preview-info">
								<div class="file-preview-name"></div>
								<div class="file-preview-size"></div>
							</div>
							<button type="button" class="file-preview-remove" data-file="image"><?php esc_html_e( 'Supprimer', 'cgt' ); ?></button>
						</div>
					</div>

					<div class="form-group">
						<label><?php esc_html_e( 'Fichier PDF', 'cgt' ); ?></label>
						<div class="file-upload-container" id="pdf-upload-zone" data-field="cgt_article_pdf">
							<div class="file-upload-icon">📄</div>
							<div class="file-upload-text"><?php esc_html_e( 'Cliquez ou glissez un PDF ici', 'cgt' ); ?></div>
							<div class="file-upload-hint"><?php esc_html_e( 'PDF uniquement - Max 15 Mo', 'cgt' ); ?></div>
							<input type="file" name="cgt_article_pdf" id="cgt_article_pdf" accept="application/pdf">
						</div>
						<div class="file-preview" id="pdf-file-preview">
							<span class="file-preview-icon">✓</span>
							<div class="file-preview-info">
								<div class="file-preview-name"></div>
								<div class="file-preview-size"></div>
							</div>
							<button type="button" class="file-preview-remove" data-file="pdf"><?php esc_html_e( 'Supprimer', 'cgt' ); ?></button>
						</div>
					</div>

					<div class="form-navigation">
						<button type="button" class="btn btn-back" data-prev-step="2">
							← <?php esc_html_e( 'Retour', 'cgt' ); ?>
						</button>
						<button type="button" class="btn btn-primary" data-next-step="4">
							<?php esc_html_e( 'Suivant', 'cgt' ); ?> →
						</button>
					</div>
				</div>

				<!-- ÉTAPE 4: Coordonnées -->
				<div class="form-step" data-step="4">
					<div class="step-header">
						<div class="step-icon">👤</div>
						<h2><?php esc_html_e( 'Vos coordonnées', 'cgt' ); ?></h2>
					</div>
					<p class="step-description"><?php esc_html_e( 'Pour finir, indiquez-nous vos coordonnées pour que nous puissions vous recontacter.', 'cgt' ); ?></p>

					<div class="form-row">
						<div class="form-group">
							<label>
								<?php esc_html_e( 'Votre nom', 'cgt' ); ?> <span class="required">*</span>
							</label>
							<input type="text" name="cgt_article_author_name" id="author-name" class="form-control" value="<?php echo isset( $name ) ? esc_attr( $name ) : ''; ?>" required placeholder="<?php esc_attr_e( 'Prénom Nom', 'cgt' ); ?>">
							<span class="form-error-message"><?php esc_html_e( 'Le nom est requis', 'cgt' ); ?></span>
						</div>

						<div class="form-group">
							<label>
								<?php esc_html_e( 'Votre email', 'cgt' ); ?> <span class="required">*</span>
							</label>
							<input type="email" name="cgt_article_author_email" id="author-email" class="form-control" value="<?php echo isset( $email ) ? esc_attr( $email ) : ''; ?>" required placeholder="<?php esc_attr_e( 'votre.email@exemple.fr', 'cgt' ); ?>">
							<span class="form-error-message"><?php esc_html_e( 'Un email valide est requis', 'cgt' ); ?></span>
						</div>
					</div>

					<div class="form-group">
						<div class="form-checkbox">
							<input type="checkbox" name="cgt_article_accept_cgv" id="accept-cgv" value="1" <?php checked( ! empty( $accept_cgv ) ); ?> required>
							<label for="accept-cgv">
								<?php esc_html_e( 'J\'accepte que mon article soit traité manuellement par l\'équipe fédérale. Celui-ci ne sera pas publié automatiquement. Pour toute question, contactez', 'cgt' ); ?> <a href="mailto:fsetud@cgt.fr" style="color: var(--cgt-red); font-weight: 600;">fsetud@cgt.fr</a> <span class="required">*</span>
							</label>
						</div>
						<span class="form-error-message"><?php esc_html_e( 'Vous devez accepter les conditions', 'cgt' ); ?></span>
					</div>

					<div class="form-navigation">
						<button type="button" class="btn btn-back" data-prev-step="3">
							← <?php esc_html_e( 'Retour', 'cgt' ); ?>
						</button>
						<button type="submit" class="btn btn-primary">
							<?php esc_html_e( 'Soumettre l\'article', 'cgt' ); ?> ✓
						</button>
					</div>
				</div>
			</form>
		</div>
	</div>
	<?php

	return ob_get_clean();
}
add_shortcode( 'cgt_submit_article', 'cgt_shortcode_submit_article' );

/**
 * Contact form shortcode.
 *
 * @return string
 */
function cgt_contact_form_shortcode() {
	$message = '';
	$errors  = array();

	if ( 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['cgt_contact_nonce'] ) ) {
		if ( ! wp_verify_nonce( sanitize_key( $_POST['cgt_contact_nonce'] ), 'cgt_contact_form' ) ) {
			$errors[] = __( 'Jeton de sécurité invalide. Merci de recharger la page.', 'cgt' );
		} elseif ( ! cgt_check_honeypot( 'cgt_hp_contact' ) ) {
			cgt_log_spam_attempt( 'contact', $_POST );
			$errors[] = __( 'Erreur de validation.', 'cgt' );
		} elseif ( ! cgt_check_rate_limit( 'contact', 5, 3600 ) ) {
			$errors[] = __( 'Vous avez atteint la limite de messages. Veuillez réessayer plus tard.', 'cgt' );
		} else {
			$name    = isset( $_POST['cgt_contact_name'] ) ? sanitize_text_field( wp_unslash( $_POST['cgt_contact_name'] ) ) : '';
			$email   = isset( $_POST['cgt_contact_email'] ) ? sanitize_email( wp_unslash( $_POST['cgt_contact_email'] ) ) : '';
			$phone   = isset( $_POST['cgt_contact_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['cgt_contact_phone'] ) ) : '';
			$subject = isset( $_POST['cgt_contact_subject'] ) ? sanitize_text_field( wp_unslash( $_POST['cgt_contact_subject'] ) ) : '';
			$content = isset( $_POST['cgt_contact_message'] ) ? wp_kses_post( wp_unslash( $_POST['cgt_contact_message'] ) ) : '';

			if ( empty( $name ) ) {
				$errors[] = __( 'Merci de renseigner votre nom et prénom.', 'cgt' );
			}

			if ( empty( $email ) || ! is_email( $email ) ) {
				$errors[] = __( 'Merci de renseigner une adresse email valide.', 'cgt' );
			}

			if ( empty( $subject ) ) {
				$errors[] = __( 'Merci de préciser le sujet de votre message.', 'cgt' );
			}

			if ( empty( $content ) ) {
				$errors[] = __( 'Merci de rédiger votre message.', 'cgt' );
			}

			if ( empty( $errors ) ) {
				$post_id = wp_insert_post(
					array(
						'post_type'    => 'cgt_contact',
						'post_title'   => $subject ? $subject : wp_trim_words( wp_strip_all_tags( $content ), 8, '…' ),
						'post_content' => $content,
						'post_status'  => 'private',
						'post_author'  => is_user_logged_in() ? get_current_user_id() : 0,
					)
				);

				if ( $post_id && ! is_wp_error( $post_id ) ) {
					update_post_meta( $post_id, 'cgt_submission_name', $name );
					update_post_meta( $post_id, 'cgt_submission_email', $email );
					update_post_meta( $post_id, 'cgt_submission_phone', $phone );
					update_post_meta( $post_id, 'cgt_submission_subject', $subject );
					update_post_meta( $post_id, 'cgt_submission_message', $content );
					update_post_meta( $post_id, 'cgt_contact_type', 'contact' );
					if ( is_user_logged_in() ) {
						update_post_meta( $post_id, 'cgt_contact_user', get_current_user_id() );
					}

					wp_mail(
						get_option( 'admin_email' ),
						sprintf( '[CGT] %s', $subject ),
						sprintf( "Message soumis via le formulaire contact :\n\nNom : %s\nEmail : %s\nTéléphone : %s\n\n%s", $name, $email, $phone ? $phone : __( 'non fourni', 'cgt' ), wp_strip_all_tags( $content ) )
					);

					$message = __( 'Merci pour votre message. Notre équipe reviendra vers vous rapidement.', 'cgt' );
					$name = $email = $phone = $subject = $content = '';
				} else {
					$errors[] = __( 'Une erreur est survenue lors de l’envoi. Merci de réessayer.', 'cgt' );
				}
			}
		}
	}

	ob_start();
	if ( $message ) {
		echo '<p class="notice">' . esc_html( $message ) . '</p>';
	}
	if ( ! empty( $errors ) ) {
		echo '<ul class="notice">';
		foreach ( $errors as $error ) {
			echo '<li>' . esc_html( $error ) . '</li>';
		}
		echo '</ul>';
	}
	?>
	<form class="cgt-contact-form" method="post">
		<?php echo cgt_render_honeypot( 'cgt_hp_contact' ); ?>
		<label>
			<span class="sr-only"><?php esc_html_e( 'Nom et prénom *', 'cgt' ); ?></span>
			<input type="text" name="cgt_contact_name" value="<?php echo isset( $name ) ? esc_attr( $name ) : ''; ?>" required placeholder="<?php esc_attr_e( 'Nom et prénom *', 'cgt' ); ?>">
		</label>
		<label>
			<span class="sr-only"><?php esc_html_e( 'Email *', 'cgt' ); ?></span>
			<input type="email" name="cgt_contact_email" value="<?php echo isset( $email ) ? esc_attr( $email ) : ''; ?>" required placeholder="<?php esc_attr_e( 'Email *', 'cgt' ); ?>">
		</label>
		<label>
			<span class="sr-only"><?php esc_html_e( 'Téléphone', 'cgt' ); ?></span>
			<input type="tel" name="cgt_contact_phone" value="<?php echo isset( $phone ) ? esc_attr( $phone ) : ''; ?>" placeholder="<?php esc_attr_e( 'Téléphone', 'cgt' ); ?>">
		</label>
		<label>
			<span class="sr-only"><?php esc_html_e( 'Sujet *', 'cgt' ); ?></span>
			<input type="text" name="cgt_contact_subject" value="<?php echo isset( $subject ) ? esc_attr( $subject ) : ''; ?>" required placeholder="<?php esc_attr_e( 'Sujet *', 'cgt' ); ?>">
		</label>
		<label>
			<span class="sr-only"><?php esc_html_e( 'Message *', 'cgt' ); ?></span>
			<textarea name="cgt_contact_message" rows="6" required placeholder="<?php esc_attr_e( 'Message *', 'cgt' ); ?>"><?php echo isset( $content ) ? esc_textarea( $content ) : ''; ?></textarea>
		</label>
		<?php wp_nonce_field( 'cgt_contact_form', 'cgt_contact_nonce' ); ?>
		<button class="btn" type="submit"><?php esc_html_e( 'Envoyer', 'cgt' ); ?></button>
	</form>
	<?php

	return ob_get_clean();
}
add_shortcode( 'cgt_contact_form', 'cgt_contact_form_shortcode' );
