<?php
/**
 * Plugin Name: CGT Bibliothèque PDF
 * Description: Gestion d'une bibliothèque de PDF classés par catégories existantes, avec shortcodes dédiés.
 * Author: CGT Dev
 * Version: 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CGT_PDF_Library {

	const CPT       = 'cgt_pdf_library';
	const META_FILE = '_cgt_pdf_file_id';

	public function __construct() {
		add_action( 'init', array( $this, 'register_post_type' ) );
		add_action( 'add_meta_boxes_' . self::CPT, array( $this, 'register_metabox' ) );
		add_action( 'save_post_' . self::CPT, array( $this, 'save_pdf_meta' ) );
		add_action( 'admin_menu', array( $this, 'register_admin_pages' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );

		add_filter( 'manage_' . self::CPT . '_posts_columns', array( $this, 'manage_columns' ) );
		add_action( 'manage_' . self::CPT . '_posts_custom_column', array( $this, 'render_custom_columns' ), 10, 2 );

		add_shortcode( 'pdf_document', array( $this, 'shortcode_output' ) );
	}

	/**
	 * Register the custom post type.
	 */
	public function register_post_type() {
		$labels = array(
			'name'               => __( 'Bibliothèque PDF', 'cgt' ),
			'singular_name'      => __( 'Document PDF', 'cgt' ),
			'add_new_item'       => __( 'Ajouter un PDF', 'cgt' ),
			'edit_item'          => __( 'Modifier le PDF', 'cgt' ),
			'all_items'          => __( 'Tous les PDF', 'cgt' ),
			'new_item'           => __( 'Nouveau PDF', 'cgt' ),
			'view_item'          => __( 'Voir le PDF', 'cgt' ),
			'search_items'       => __( 'Rechercher un PDF', 'cgt' ),
			'not_found'          => __( 'Aucun PDF trouvé.', 'cgt' ),
			'not_found_in_trash' => __( 'Aucun PDF dans la corbeille.', 'cgt' ),
		);

		$args = array(
			'labels'              => $labels,
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => false,
			'supports'            => array( 'title' ),
			'taxonomies'          => array( 'category' ),
			'capability_type'     => 'post',
			'map_meta_cap'        => true,
		);

		register_post_type( self::CPT, $args );
	}

	/**
	 * Register metabox.
	 */
	public function register_metabox() {
		add_meta_box(
			'cgt_pdf_file_box',
			__( 'Fichier PDF', 'cgt' ),
			array( $this, 'render_metabox' ),
			self::CPT,
			'normal',
			'high'
		);
	}

	/**
	 * Render metabox content.
	 *
	 * @param WP_Post $post Post object.
	 */
	public function render_metabox( $post ) {
		wp_nonce_field( 'cgt_pdf_file', 'cgt_pdf_file_nonce' );

		$file_id  = (int) get_post_meta( $post->ID, self::META_FILE, true );
		$file_url = $file_id ? wp_get_attachment_url( $file_id ) : '';
		?>
		<p>
			<label for="cgt_pdf_file_input"><?php esc_html_e( 'Sélectionnez un fichier PDF à associer.', 'cgt' ); ?></label>
			<input type="file" id="cgt_pdf_file_input" name="cgt_pdf_file" accept="application/pdf">
		</p>

		<?php if ( $file_url ) : ?>
			<p>
				<strong><?php esc_html_e( 'Fichier actuel :', 'cgt' ); ?></strong>
				<a href="<?php echo esc_url( $file_url ); ?>" target="_blank" rel="noopener">
					<?php echo esc_html( basename( $file_url ) ); ?>
				</a>
			</p>
			<p>
				<label>
					<input type="checkbox" name="cgt_pdf_file_remove" value="1">
					<?php esc_html_e( 'Supprimer le fichier actuel', 'cgt' ); ?>
				</label>
			</p>
		<?php endif; ?>

		<p class="description">
			<?php esc_html_e( 'Associez ce document à une catégorie via le bloc “Catégories” classique.', 'cgt' ); ?>
		</p>
		<?php
	}

	/**
	 * Save meta.
	 *
	 * @param int $post_id Post ID.
	 */
	public function save_pdf_meta( $post_id ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! isset( $_POST['cgt_pdf_file_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['cgt_pdf_file_nonce'] ), 'cgt_pdf_file' ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( ! empty( $_FILES['cgt_pdf_file']['name'] ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/media.php';
			require_once ABSPATH . 'wp-admin/includes/image.php';

			$file     = $_FILES['cgt_pdf_file'];
			$filetype = wp_check_filetype( $file['name'] );
			if ( ! in_array( $filetype['type'], array( 'application/pdf' ), true ) ) {
				wp_die( esc_html__( 'Veuillez téléverser uniquement des fichiers PDF.', 'cgt' ) );
			}

			$attachment_id = media_handle_upload( 'cgt_pdf_file', $post_id );
			if ( ! is_wp_error( $attachment_id ) ) {
				update_post_meta( $post_id, self::META_FILE, $attachment_id );
			}
		} elseif ( isset( $_POST['cgt_pdf_file_remove'] ) ) {
			delete_post_meta( $post_id, self::META_FILE );
		}
	}

	/**
	 * Register admin menu entries.
	 */
	public function register_admin_pages() {
		add_menu_page(
			__( 'Bibliothèque', 'cgt' ),
			__( 'Bibliothèque', 'cgt' ),
			'edit_posts',
			'cgt-library',
			array( $this, 'render_library_page' ),
			'dashicons-media-document',
			25
		);

		add_submenu_page(
			'cgt-library',
			__( 'Ajouter un PDF', 'cgt' ),
			__( 'Ajouter un PDF', 'cgt' ),
			'edit_posts',
			'post-new.php?post_type=' . self::CPT
		);
	}

	/**
	 * Enqueue assets.
	 *
	 * @param string $hook Current admin page.
	 */
	public function enqueue_admin_assets( $hook ) {
        if ( 'toplevel_page_cgt-library' !== $hook ) {
            return;
        }

		// Enqueue custom CSS
		wp_enqueue_style(
			'cgt-library-admin-css',
			plugin_dir_url( __FILE__ ) . 'assets/css/library-admin.css',
			array(),
			'1.0.0'
		);

		// Enqueue custom JS
		wp_enqueue_script(
			'cgt-library-admin-js',
			plugin_dir_url( __FILE__ ) . 'assets/js/library-admin.js',
			array( 'jquery' ),
			'1.0.0',
			true
		);
	}

	/**
	 * Render the main library admin page.
	 */
	public function render_library_page() {
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_die( esc_html__( 'Accès refusé.', 'cgt' ) );
		}

		$selected_cat = isset( $_GET['cgt_cat'] ) ? absint( $_GET['cgt_cat'] ) : 0;

		$args = array(
			'post_type'      => self::CPT,
			'post_status'    => array( 'publish', 'private', 'pending', 'draft' ),
			'posts_per_page' => -1,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'no_found_rows'  => false,
		);

		if ( $selected_cat ) {
			$args['tax_query'] = array(
				array(
					'taxonomy' => 'category',
					'field'    => 'term_id',
					'terms'    => $selected_cat,
				),
			);
		}

		$query      = new WP_Query( $args );
		$categories = get_categories(
			array(
				'hide_empty' => false,
				'taxonomy'   => 'category',
			)
		);

		$category_counts = array();
		foreach ( $categories as $cat ) {
			$count_query = new WP_Query(
				array(
					'post_type'      => self::CPT,
					'post_status'    => array( 'publish', 'private', 'pending', 'draft' ),
					'posts_per_page' => 1,
					'no_found_rows'  => true,
					'tax_query'      => array(
						array(
							'taxonomy' => 'category',
							'field'    => 'term_id',
							'terms'    => $cat->term_id,
						),
					),
				)
			);
			$category_counts[ $cat->term_id ] = (int) $count_query->found_posts;
			wp_reset_postdata();
		}
		?>
		<div class="wrap cgt-library-page">

			<!-- Header -->
			<div class="cgt-library-header">
				<h1>
					<span class="icon">📚</span>
					<?php esc_html_e( 'Bibliothèque PDF', 'cgt' ); ?>
				</h1>
				<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=' . self::CPT ) ); ?>" class="page-title-action">
					<?php esc_html_e( 'Ajouter un PDF', 'cgt' ); ?>
				</a>
			</div>

			<!-- Success Notice -->
			<?php if ( isset( $_GET['deleted'] ) ) : ?>
				<div class="cgt-library-notice">
					<?php esc_html_e( 'Document supprimé avec succès.', 'cgt' ); ?>
				</div>
			<?php endif; ?>

			<!-- Main Wrapper -->
			<div class="cgt-library-wrapper">

				<!-- Sidebar -->
				<aside class="cgt-library-sidebar">
					<h2><?php esc_html_e( 'Catégories', 'cgt' ); ?></h2>
					<ul>
						<li>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=cgt-library' ) ); ?>"<?php echo 0 === $selected_cat ? ' class="current"' : ''; ?>>
								<?php esc_html_e( 'Toutes les catégories', 'cgt' ); ?>
								<span class="count"><?php echo (int) $query->found_posts; ?></span>
							</a>
						</li>
						<?php foreach ( $categories as $cat ) : ?>
							<li>
								<a href="<?php echo esc_url( add_query_arg( array( 'page' => 'cgt-library', 'cgt_cat' => $cat->term_id ), admin_url( 'admin.php' ) ) ); ?>"<?php echo $selected_cat === $cat->term_id ? ' class="current"' : ''; ?>>
								<?php echo esc_html( $cat->name ); ?>
								<span class="count"><?php echo isset( $category_counts[ $cat->term_id ] ) ? (int) $category_counts[ $cat->term_id ] : 0; ?></span>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				</aside>

				<!-- Content Area -->
				<div class="cgt-library-content">

					<!-- Filters -->
					<form method="get" class="cgt-library-filters">
						<input type="hidden" name="page" value="cgt-library">
						<label for="cgt_cat_select"><?php esc_html_e( 'Filtrer par catégorie:', 'cgt' ); ?></label>
						<?php
						wp_dropdown_categories(
							array(
								'taxonomy'         => 'category',
								'show_option_all'  => __( 'Toutes les catégories', 'cgt' ),
								'name'             => 'cgt_cat',
								'id'               => 'cgt_cat_select',
								'orderby'          => 'name',
								'selected'         => $selected_cat,
								'show_count'       => true,
								'hide_empty'       => false,
							)
						);
						submit_button( __( 'Filtrer', 'cgt' ), 'primary', 'filter_action', false );
						?>
					</form>

					<!-- PDF Cards Grid -->
					<div class="cgt-library-grid">
						<?php if ( $query->have_posts() ) : ?>
							<?php
							while ( $query->have_posts() ) :
								$query->the_post();
								$post_id   = get_the_ID();
								$file_id   = (int) get_post_meta( $post_id, self::META_FILE, true );
								$file_url  = $file_id ? wp_get_attachment_url( $file_id ) : '';
								$terms     = get_the_terms( $post_id, 'category' );
								$is_synced = get_post_meta( $post_id, '_cgt_auto_synced', true );
								$source_id = get_post_meta( $post_id, '_cgt_source_post_id', true );
								$source_type = get_post_meta( $post_id, '_cgt_source_post_type', true );
								?>

								<article class="cgt-pdf-card">

									<!-- Card Header -->
									<div class="cgt-pdf-card-header">
										<div class="cgt-pdf-icon">📄</div>
										<div class="cgt-pdf-card-title">
											<h3>
												<a href="<?php echo esc_url( get_edit_post_link( $post_id ) ); ?>">
													<?php the_title(); ?>
												</a>
											</h3>
										</div>
									</div>

									<!-- Categories -->
									<div class="cgt-pdf-categories">
										<?php if ( $terms && ! is_wp_error( $terms ) ) : ?>
											<?php foreach ( $terms as $term ) : ?>
												<span class="cgt-pdf-category-badge">
													<?php echo esc_html( $term->name ); ?>
												</span>
											<?php endforeach; ?>
										<?php else : ?>
											<span class="cgt-pdf-category-badge empty">
												<?php esc_html_e( 'Non classé', 'cgt' ); ?>
											</span>
										<?php endif; ?>
									</div>

									<!-- File Info -->
									<div class="cgt-pdf-file-info <?php echo ! $file_url ? 'no-file' : ''; ?>">
										<?php if ( $file_url ) : ?>
											<span class="icon">📎</span>
											<a href="<?php echo esc_url( $file_url ); ?>" target="_blank" rel="noopener">
												<?php esc_html_e( 'Voir le PDF', 'cgt' ); ?>
											</a>
										<?php else : ?>
											<span class="icon">⚠️</span>
											<?php esc_html_e( 'Aucun fichier associé', 'cgt' ); ?>
										<?php endif; ?>
									</div>

									<!-- Source de synchronisation -->
									<?php if ( $is_synced && $source_id ) : ?>
										<div class="cgt-pdf-source">
											<div class="cgt-pdf-source-label">
												<span style="color: #10b981;">✓</span> <?php esc_html_e( 'Synchronisé depuis', 'cgt' ); ?>
											</div>
											<div class="cgt-pdf-source-info">
												<?php
												$source_post = get_post( $source_id );
												if ( $source_post ) {
													$source_label = 'post' === $source_type ? 'Article' : 'Tract';
													$edit_url = admin_url( 'post.php?post=' . $source_id . '&action=edit' );
													echo '<strong>' . esc_html( $source_label ) . ':</strong> ';
													echo '<a href="' . esc_url( $edit_url ) . '" target="_blank">' . esc_html( $source_post->post_title ) . '</a>';
												}
												?>
											</div>
										</div>
									<?php endif; ?>

									<!-- Actions -->
									<div class="cgt-pdf-actions">
										<a class="button button-primary" href="<?php echo esc_url( get_edit_post_link( $post_id ) ); ?>">
											✏️ <?php esc_html_e( 'Modifier', 'cgt' ); ?>
										</a>
										<a class="button button-delete cgt-delete-pdf"
										   data-confirm="<?php esc_attr_e( 'Êtes-vous sûr de vouloir supprimer ce PDF ?', 'cgt' ); ?>"
										   href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=cgt_delete_pdf&post=' . $post_id ), 'cgt_delete_pdf_' . $post_id ) ); ?>">
											🗑️ <?php esc_html_e( 'Supprimer', 'cgt' ); ?>
										</a>
									</div>

								</article>

								<?php
							endwhile;
							wp_reset_postdata();
							?>
						<?php else : ?>

							<!-- Empty State -->
							<div class="cgt-library-empty">
								<div class="icon">📭</div>
								<h3><?php esc_html_e( 'Aucun PDF disponible', 'cgt' ); ?></h3>
								<p><?php esc_html_e( 'Commencez par ajouter votre premier document PDF à la bibliothèque.', 'cgt' ); ?></p>
								<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=' . self::CPT ) ); ?>" class="button">
									<?php esc_html_e( 'Ajouter un PDF', 'cgt' ); ?>
								</a>
							</div>

						<?php endif; ?>
					</div>

				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Custom columns for CPT listing.
	 */
	public function manage_columns( $columns ) {
		$new_columns = array();
		foreach ( $columns as $key => $label ) {
			$new_columns[ $key ] = $label;
			if ( 'date' === $key ) {
				$new_columns['category']  = __( 'Catégorie', 'cgt' );
				$new_columns['source'] = __( 'Source', 'cgt' );
			}
		}

		return $new_columns;
	}

	public function render_custom_columns( $column, $post_id ) {
		if ( 'category' === $column ) {
			$terms = get_the_terms( $post_id, 'category' );
			if ( $terms && ! is_wp_error( $terms ) ) {
				echo esc_html( implode( ', ', wp_list_pluck( $terms, 'name' ) ) );
			} else {
				esc_html_e( '—', 'cgt' );
			}
		}

		if ( 'source' === $column ) {
			$is_synced = get_post_meta( $post_id, '_cgt_auto_synced', true );
			$source_id = get_post_meta( $post_id, '_cgt_source_post_id', true );
			$source_type = get_post_meta( $post_id, '_cgt_source_post_type', true );

			if ( $is_synced && $source_id ) {
				$source_post = get_post( $source_id );
				if ( $source_post ) {
					$source_label = 'post' === $source_type ? 'Article' : 'Tract';
					$edit_url = admin_url( 'post.php?post=' . $source_id . '&action=edit' );
					echo '<span style="color: #10b981;">✓</span> <a href="' . esc_url( $edit_url ) . '" target="_blank">' . esc_html( $source_label ) . '</a>';
				}
			} else {
				echo '<span style="color: #9ca3af;">Manuel</span>';
			}
		}
	}

	public function handle_delete_pdf() {
		if ( ! current_user_can( 'delete_posts' ) ) {
			wp_die( esc_html__( 'Vous ne pouvez pas supprimer ce document.', 'cgt' ) );
		}

		$post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0;
		if ( ! $post_id || self::CPT !== get_post_type( $post_id ) ) {
			wp_die( esc_html__( 'Document introuvable.', 'cgt' ) );
		}

		check_admin_referer( 'cgt_delete_pdf_' . $post_id );

		wp_delete_post( $post_id, true );
		wp_safe_redirect( admin_url( 'admin.php?page=cgt-library&deleted=1' ) );
		exit;
	}

	/**
	 * Shortcode output.
	 *
	 * @param array $atts Shortcode attributes.
	 */
	public function shortcode_output( $atts ) {
		$atts = shortcode_atts(
			array(
				'id' => 0,
			),
			$atts,
			'pdf_document'
		);

		$post_id = absint( $atts['id'] );
		if ( ! $post_id ) {
			return '';
		}

		$post = get_post( $post_id );
		if ( ! $post || self::CPT !== $post->post_type ) {
			return '';
		}

		$file_id = (int) get_post_meta( $post_id, self::META_FILE, true );
		$file_url = $file_id ? wp_get_attachment_url( $file_id ) : '';
		if ( ! $file_url ) {
			return '';
		}

		$title = get_the_title( $post_id );
		return '<div class="cgt-pdf-link"><a href="' . esc_url( $file_url ) . '" target="_blank" rel="noopener">' . esc_html( $title ) . '</a></div>';
	}
}

function cgt_pdf_library() {
	static $instance = null;
	if ( null === $instance ) {
		$instance = new CGT_PDF_Library();
		add_action( 'admin_post_cgt_delete_pdf', array( $instance, 'handle_delete_pdf' ) );
	}

	return $instance;
}

cgt_pdf_library();
