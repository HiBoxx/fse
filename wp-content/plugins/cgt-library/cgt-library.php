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

		wp_register_script( 'cgt-library-admin', '', array( 'jquery' ), false, true );
		wp_enqueue_script( 'cgt-library-admin' );
		wp_enqueue_style( 'wp-components' );

        $inline_css = <<<CSS
.cgt-library-wrapper { display: grid; grid-template-columns: 220px 1fr; gap: 24px; align-items: start; }
.cgt-library-sidebar { background: #fff; border: 1px solid #dcdcde; border-radius: 6px; padding: 16px; }
.cgt-library-sidebar ul { margin: 0; padding-left: 18px; }
.cgt-library-sidebar li { margin: 6px 0; }
.cgt-library-content table.wp-list-table td { vertical-align: middle; }
.cgt-library-actions { display: flex; gap: 8px; align-items: center; }
@media (max-width: 960px) {
	.cgt-library-wrapper { grid-template-columns: 1fr; }
}
CSS;
        wp_add_inline_style( 'wp-components', $inline_css );

        $inline_js = <<<JS
document.addEventListener('click', function(event) {
    if ( event.target.classList.contains('cgt-copy-shortcode') ) {
        event.preventDefault();
        const shortcode = event.target.getAttribute('data-shortcode');
        if ( shortcode ) {
            navigator.clipboard.writeText(shortcode).then(function() {
                event.target.innerText = event.target.getAttribute('data-success');
                setTimeout(function(){ event.target.innerText = event.target.getAttribute('data-label'); }, 2000);
            });
        }
    }

    if ( event.target.classList.contains('cgt-delete-pdf') ) {
        if ( ! window.confirm(event.target.getAttribute('data-confirm')) ) {
            event.preventDefault();
        }
    }
});
JS;
        wp_add_inline_script( 'cgt-library-admin', $inline_js );
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
			'posts_per_page' => -1,
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
		$categories = get_categories( array( 'hide_empty' => false ) );
		?>
		<div class="wrap">
			<h1 class="wp-heading-inline"><?php esc_html_e( 'Bibliothèque de PDF', 'cgt' ); ?></h1>
			<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=' . self::CPT ) ); ?>" class="page-title-action"><?php esc_html_e( 'Ajouter un PDF', 'cgt' ); ?></a>
			<?php if ( isset( $_GET['deleted'] ) ) : ?>
				<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Document supprimé.', 'cgt' ); ?></p></div>
			<?php endif; ?>
			<hr class="wp-header-end">
			<div class="cgt-library-wrapper">
				<aside class="cgt-library-sidebar">
					<h2><?php esc_html_e( 'Catégories', 'cgt' ); ?></h2>
					<ul>
						<li><a href="<?php echo esc_url( admin_url( 'admin.php?page=cgt-library' ) ); ?>"<?php echo 0 === $selected_cat ? ' class="current"' : ''; ?>><?php esc_html_e( 'Toutes les catégories', 'cgt' ); ?></a></li>
						<?php foreach ( $categories as $cat ) : ?>
							<li><a href="<?php echo esc_url( add_query_arg( array( 'page' => 'cgt-library', 'cgt_cat' => $cat->term_id ), admin_url( 'admin.php' ) ) ); ?>"<?php echo $selected_cat === $cat->term_id ? ' class="current"' : ''; ?>><?php echo esc_html( $cat->name ); ?> (<?php echo (int) $cat->count; ?>)</a></li>
						<?php endforeach; ?>
					</ul>
				</aside>
				<div class="cgt-library-content">
					<form method="get" class="alignleft actions cgt-library-filter">
						<input type="hidden" name="page" value="cgt-library">
						<?php
						wp_dropdown_categories(
							array(
								'taxonomy'         => 'category',
								'show_option_all'  => __( 'Toutes les catégories', 'cgt' ),
								'name'             => 'cgt_cat',
								'orderby'          => 'name',
								'selected'         => $selected_cat,
								'show_count'       => true,
								'hide_empty'       => false,
							)
						);
						submit_button( __( 'Filtrer', 'cgt' ), '', 'filter_action', false );
						?>
					</form>
					<table class="wp-list-table widefat fixed striped">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Titre', 'cgt' ); ?></th>
								<th><?php esc_html_e( 'Catégories', 'cgt' ); ?></th>
								<th><?php esc_html_e( 'Fichier', 'cgt' ); ?></th>
								<th><?php esc_html_e( 'Shortcode', 'cgt' ); ?></th>
								<th><?php esc_html_e( 'Actions', 'cgt' ); ?></th>
							</tr>
						</thead>
						<tbody>
						<?php if ( $query->have_posts() ) : ?>
							<?php
							while ( $query->have_posts() ) :
								$query->the_post();
								$post_id   = get_the_ID();
								$file_id   = (int) get_post_meta( $post_id, self::META_FILE, true );
								$file_url  = $file_id ? wp_get_attachment_url( $file_id ) : '';
								$shortcode = '[pdf_document id="' . $post_id . '"]';
								?>
								<tr>
									<td><strong><a href="<?php echo esc_url( get_edit_post_link( $post_id ) ); ?>"><?php the_title(); ?></a></strong></td>
									<td>
										<?php
										$terms = get_the_terms( $post_id, 'category' );
										if ( $terms && ! is_wp_error( $terms ) ) {
											echo esc_html( implode( ', ', wp_list_pluck( $terms, 'name' ) ) );
										} else {
											esc_html_e( 'Non classé', 'cgt' );
										}
										?>
									</td>
									<td>
										<?php
										if ( $file_url ) {
											echo '<a href="' . esc_url( $file_url ) . '" target="_blank" rel="noopener">' . esc_html__( 'Voir le PDF', 'cgt' ) . '</a>';
										} else {
											esc_html_e( 'Aucun fichier', 'cgt' );
										}
										?>
									</td>
									<td>
										<code><?php echo esc_html( $shortcode ); ?></code><br>
										<button class="button button-secondary cgt-copy-shortcode" data-shortcode="<?php echo esc_attr( $shortcode ); ?>" data-success="<?php esc_attr_e( 'Copié !', 'cgt' ); ?>" data-label="<?php esc_attr_e( 'Copier', 'cgt' ); ?>"><?php esc_html_e( 'Copier', 'cgt' ); ?></button>
									</td>
									<td>
										<div class="cgt-library-actions">
											<a class="button" href="<?php echo esc_url( get_edit_post_link( $post_id ) ); ?>"><?php esc_html_e( 'Modifier', 'cgt' ); ?></a>
											<a class="button button-link-delete cgt-delete-pdf" data-confirm="<?php esc_attr_e( 'Supprimer ce PDF ?', 'cgt' ); ?>" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=cgt_delete_pdf&post=' . $post_id ), 'cgt_delete_pdf_' . $post_id ) ); ?>"><?php esc_html_e( 'Supprimer', 'cgt' ); ?></a>
										</div>
									</td>
								</tr>
								<?php
							endwhile;
							wp_reset_postdata();
							?>
						<?php else : ?>
							<tr><td colspan="5"><?php esc_html_e( 'Aucun PDF disponible pour cette sélection.', 'cgt' ); ?></td></tr>
						<?php endif; ?>
						</tbody>
					</table>
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
				$new_columns['shortcode'] = __( 'Shortcode', 'cgt' );
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

		if ( 'shortcode' === $column ) {
			echo '<code>[pdf_document id="' . absint( $post_id ) . '"]</code>';
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
