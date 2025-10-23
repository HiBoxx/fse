<?php
/**
 * Shortcodes.
 *
 * @package CGT_Child
 */

defined( 'ABSPATH' ) || exit;

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
		} else {
			$question = isset( $_POST['cgt_question'] ) ? wp_kses_post( wp_unslash( $_POST['cgt_question'] ) ) : '';
			$subject  = isset( $_POST['cgt_question_subject'] ) ? sanitize_text_field( wp_unslash( $_POST['cgt_question_subject'] ) ) : '';

			if ( empty( $question ) ) {
				$message = __( 'Merci de détailler votre question.', 'cgt' );
			} else {
				$post_id = wp_insert_post(
					array(
						'post_type'   => 'cgt_question',
						'post_status' => 'pending',
						'post_title'  => $subject ? $subject : wp_trim_words( wp_strip_all_tags( $question ), 8, '…' ),
						'post_content'=> $question,
						'post_author' => get_current_user_id(),
					)
				);

				if ( $post_id && 0 !== $post_id ) {
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
		if ( ! wp_verify_nonce( sanitize_key( $_POST['cgt_submit_article_nonce'] ), 'cgt_submit_article' ) ) {
			$errors[] = __( 'Jeton de sécurité invalide. Merci de recharger la page.', 'cgt' );
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

					if ( ! empty( $_FILES['cgt_article_featured']['name'] ) ) {
						$attachment_id = media_handle_upload( 'cgt_article_featured', $post_id );
						if ( ! is_wp_error( $attachment_id ) ) {
							set_post_thumbnail( $post_id, $attachment_id );
						}
					}

					if ( ! empty( $_FILES['cgt_article_pdf']['name'] ) ) {
						$pdf_id = media_handle_upload( 'cgt_article_pdf', $post_id );
						if ( ! is_wp_error( $pdf_id ) ) {
							update_post_meta( $post_id, 'cgt_submission_pdf', $pdf_id );
						}
					}

					$message = __( 'Merci ! Votre article a été soumis et se trouve en attente de validation.', 'cgt' );
				} else {
					$errors[] = __( 'Une erreur est survenue lors de la soumission. Merci de réessayer.', 'cgt' );
				}
			}
		}
	}

	$categories = get_categories( array( 'hide_empty' => false ) );

	ob_start();

	if ( ! empty( $message ) ) {
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
	<form class="cgt-submit-article" method="post" enctype="multipart/form-data">
		<h2><?php esc_html_e( 'Proposer un article', 'cgt' ); ?></h2>
		<p class="form-intro"><?php esc_html_e( 'Page de publication Fédéral : Cette page est réservée à la publication de bulletins, tracts ou communications syndicales. Merci de ne partager que des informations utiles.', 'cgt' ); ?></p>

		<label>
			<?php esc_html_e( 'Titre de l’article *', 'cgt' ); ?>
			<input type="text" name="cgt_article_title" value="<?php echo isset( $title ) ? esc_attr( $title ) : ''; ?>" required placeholder="<?php esc_attr_e( 'Titre clair et accrocheur', 'cgt' ); ?>">
		</label>

		<label>
			<?php esc_html_e( 'Contenu *', 'cgt' ); ?>
			<textarea name="cgt_article_content" rows="8" required placeholder="<?php esc_attr_e( 'Rédigez l\'article.', 'cgt' ); ?>"><?php echo isset( $content ) ? esc_textarea( $content ) : ''; ?></textarea>
		</label>

		<label>
			<?php esc_html_e( 'Catégorie', 'cgt' ); ?>
			<select name="cgt_article_category">
				<option value=""><?php esc_html_e( '— Choisir —', 'cgt' ); ?></option>
				<?php foreach ( $categories as $cat ) : ?>
					<option value="<?php echo esc_attr( $cat->term_id ); ?>" <?php selected( isset( $category ) ? $category : '', $cat->term_id ); ?>><?php echo esc_html( $cat->name ); ?></option>
				<?php endforeach; ?>
			</select>
		</label>

		<label>
			<?php esc_html_e( 'Mots-clés', 'cgt' ); ?>
			<input type="text" name="cgt_article_keywords" value="<?php echo isset( $keywords ) ? esc_attr( $keywords ) : ''; ?>" placeholder="<?php esc_attr_e( 'Ex: politique, économie', 'cgt' ); ?>">
		</label>

		<label>
			<?php esc_html_e( 'Image à la une (JPG/PNG, max 5 Mo)', 'cgt' ); ?>
			<input type="file" name="cgt_article_featured" accept="image/jpeg,image/png">
		</label>

		<label>
			<?php esc_html_e( 'Fichier PDF (max 15 Mo)', 'cgt' ); ?>
			<input type="file" name="cgt_article_pdf" accept="application/pdf">
		</label>

		<label>
			<?php esc_html_e( 'Votre nom *', 'cgt' ); ?>
			<input type="text" name="cgt_article_author_name" value="<?php echo isset( $name ) ? esc_attr( $name ) : ''; ?>" required>
		</label>

		<label>
			<?php esc_html_e( 'Votre email *', 'cgt' ); ?>
			<input type="email" name="cgt_article_author_email" value="<?php echo isset( $email ) ? esc_attr( $email ) : ''; ?>" required>
		</label>

		<label>
			<?php esc_html_e( 'Sources', 'cgt' ); ?>
			<textarea name="cgt_article_sources" rows="3" placeholder="<?php esc_attr_e( 'Indiquez vos références ou sources éventuelles.', 'cgt' ); ?>"><?php echo isset( $sources ) ? esc_textarea( $sources ) : ''; ?></textarea>
		</label>

		<label class="accept-cgv">
			<input type="checkbox" name="cgt_article_accept_cgv" value="1" <?php checked( ! empty( $accept_cgv ) ); ?> required>
			<span><?php esc_html_e( 'J’accepte les CGV. Le traitement se fait manuellement pour publier votre article. Celui-ci ne sera pas publié automatiquement. Pour toute question, contactez fsetud@cgt.fr.', 'cgt' ); ?></span>
		</label>

		<?php wp_nonce_field( 'cgt_submit_article', 'cgt_submit_article_nonce' ); ?>
		<button class="btn" type="submit"><?php esc_html_e( 'Soumettre l’article', 'cgt' ); ?></button>
	</form>
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
						'post_type'    => 'cgt_question',
						'post_title'   => sprintf( '%1$s – %2$s', $subject, $name ),
						'post_content' => $content . '\n\n' . sprintf( 'Téléphone : %s', $phone ? $phone : __( 'non fourni', 'cgt' ) ),
						'post_status'  => 'pending',
					)
				);

				if ( $post_id && ! is_wp_error( $post_id ) ) {
					update_post_meta( $post_id, 'cgt_submission_name', $name );
					update_post_meta( $post_id, 'cgt_submission_email', $email );
					update_post_meta( $post_id, 'cgt_submission_phone', $phone );
					update_post_meta( $post_id, 'cgt_submission_subject', $subject );

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
