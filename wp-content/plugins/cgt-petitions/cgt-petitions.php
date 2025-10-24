<?php
/**
 * Plugin Name: CGT Pétitions
 * Description: Créez des pétitions, collectez des signatures et intégrez-les via shortcode.
 * Version: 1.0.0
 * Author: CGT Dev
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CGT_Petitions_Plugin {
	const CPT         = 'cgt_petition';
	const META_TARGET = '_cgt_petition_target';
	const META_PDF    = '_cgt_petition_pdf_id';
	const TABLE_SIGN  = 'cgt_petition_signatures';

	public static function init() {
		static $instance = null;
		if ( null === $instance ) {
			$instance = new self();
		}
	}

	public function __construct() {
		register_activation_hook( __FILE__, array( __CLASS__, 'activate' ) );

		add_action( 'init', array( $this, 'register_post_type' ) );
		add_action( 'add_meta_boxes_' . self::CPT, array( $this, 'register_metaboxes' ) );
		add_action( 'save_post_' . self::CPT, array( $this, 'save_petition_meta' ) );

		add_action( 'admin_menu', array( $this, 'register_admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
		add_filter( 'manage_' . self::CPT . '_posts_columns', array( $this, 'manage_petition_columns' ) );
		add_action( 'manage_' . self::CPT . '_posts_custom_column', array( $this, 'render_petition_columns' ), 10, 2 );

	add_shortcode( 'cgt_petition', array( $this, 'petition_shortcode' ) );
	add_action( 'wp_enqueue_scripts', array( $this, 'register_front_assets' ) );
	add_action( 'init', array( $this, 'handle_signature_submission' ) );

	add_action( 'admin_post_cgt_delete_signature', array( $this, 'handle_delete_signature' ) );

	add_filter( 'the_content', array( $this, 'maybe_append_petition_content' ) );
}
	public static function activate() {
		global $wpdb;
		$table   = $wpdb->prefix . self::TABLE_SIGN;
		$charset = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS $table (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			petition_id BIGINT UNSIGNED NOT NULL,
			name VARCHAR(190) NOT NULL,
			email VARCHAR(190) NOT NULL,
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY petition (petition_id),
			KEY email (email)
		) $charset;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	public function register_post_type() {
		$labels = array(
			'name'               => __( 'Pétitions', 'cgt' ),
			'singular_name'      => __( 'Pétition', 'cgt' ),
			'add_new_item'       => __( 'Créer une pétition', 'cgt' ),
			'edit_item'          => __( 'Modifier la pétition', 'cgt' ),
			'all_items'          => __( 'Toutes les pétitions', 'cgt' ),
			'view_item'          => __( 'Voir la pétition', 'cgt' ),
		);

		$args = array(
			'labels'        => $labels,
			'public'        => true,
			'show_in_rest'  => true,
			'supports'      => array( 'title', 'editor', 'thumbnail' ),
			'has_archive'   => false,
			'menu_icon'     => 'dashicons-megaphone',
			'menu_position' => 25,
		);

		register_post_type( self::CPT, $args );
	}
	public function register_metaboxes() {
		add_meta_box(
			'cgt_petition_details',
			__( 'Paramètres de la pétition', 'cgt' ),
			array( $this, 'render_petition_metabox' ),
			self::CPT,
			'normal',
			'high'
		);
	}

	public function render_petition_metabox( $post ) {
		wp_nonce_field( 'cgt_petition_meta', 'cgt_petition_nonce' );
		$target = (int) get_post_meta( $post->ID, self::META_TARGET, true );
		$pdf_id = (int) get_post_meta( $post->ID, self::META_PDF, true );
		$pdf_url = $pdf_id ? wp_get_attachment_url( $pdf_id ) : '';
		?>
		<p>
			<label for="cgt_petition_target"><strong><?php esc_html_e( 'Objectif de signatures', 'cgt' ); ?></strong></label><br>
			<input type="number" min="1" name="cgt_petition_target" id="cgt_petition_target" value="<?php echo esc_attr( $target ); ?>" class="regular-text">
		</p>
		<p>
			<label for="cgt_petition_pdf"><strong><?php esc_html_e( 'Document PDF (facultatif)', 'cgt' ); ?></strong></label><br>
			<input type="file" name="cgt_petition_pdf" id="cgt_petition_pdf" accept="application/pdf">
		</p>
		<?php if ( $pdf_url ) : ?>
			<p>
				<a href="<?php echo esc_url( $pdf_url ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Voir le PDF actuel', 'cgt' ); ?></a>
				<label style="margin-left:12px;"><input type="checkbox" name="cgt_petition_pdf_remove" value="1"> <?php esc_html_e( 'Supprimer', 'cgt' ); ?></label>
			</p>
		<?php endif; ?>
		<?php
	}

	public function save_petition_meta( $post_id ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! isset( $_POST['cgt_petition_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['cgt_petition_nonce'] ), 'cgt_petition_meta' ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$target = isset( $_POST['cgt_petition_target'] ) ? max( 0, (int) $_POST['cgt_petition_target'] ) : 0;
		update_post_meta( $post_id, self::META_TARGET, $target );

		if ( ! empty( $_FILES['cgt_petition_pdf']['name'] ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/media.php';
			require_once ABSPATH . 'wp-admin/includes/image.php';

			$file     = $_FILES['cgt_petition_pdf'];
			$filetype = wp_check_filetype( $file['name'] );
			if ( 'application/pdf' !== $filetype['type'] ) {
				return;
			}

			$attachment_id = media_handle_upload( 'cgt_petition_pdf', $post_id );
			if ( ! is_wp_error( $attachment_id ) ) {
				update_post_meta( $post_id, self::META_PDF, $attachment_id );
			}
		} elseif ( isset( $_POST['cgt_petition_pdf_remove'] ) ) {
			delete_post_meta( $post_id, self::META_PDF );
		}
	}
	public function register_admin_menu() {
		add_menu_page(
			__( 'Pétitions', 'cgt' ),
			__( 'Pétitions', 'cgt' ),
			'edit_posts',
			'cgt-petitions',
			array( $this, 'render_petitions_page' ),
			'dashicons-megaphone',
			25
		);

		add_submenu_page(
			'cgt-petitions',
			__( 'Ajouter une pétition', 'cgt' ),
			__( 'Ajouter une pétition', 'cgt' ),
			'edit_posts',
			'post-new.php?post_type=' . self::CPT
		);

		add_submenu_page(
			'cgt-petitions',
			__( 'Signatures', 'cgt' ),
			__( 'Signatures', 'cgt' ),
			'edit_posts',
			'cgt-petitions-signatures',
			array( $this, 'render_signatures_page' )
		);
	}

	public function enqueue_admin_assets( $hook ) {
		if ( strpos( $hook, 'cgt-petitions' ) === false ) {
			return;
		}

		wp_register_style( 'cgt-petitions-admin', false );
		wp_enqueue_style( 'cgt-petitions-admin' );
		$css = '.cgt-petitions-table td{vertical-align:middle;} .cgt-progress{background:#f0f0f1;border-radius:6px;overflow:hidden;height:12px;margin-top:4px;} .cgt-progress span{display:block;height:100%;background:#d00000;}';
		wp_add_inline_style( 'cgt-petitions-admin', $css );
	}

	public function render_petitions_page() {
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_die( esc_html__( 'Accès refusé.', 'cgt' ) );
		}

		$petitions = get_posts(
			array(
				'post_type'      => self::CPT,
				'posts_per_page' => -1,
				'post_status'    => array( 'publish', 'draft' ),
			)
		);
		?>
		<div class="wrap">
			<h1 class="wp-heading-inline"><?php esc_html_e( 'Pétitions', 'cgt' ); ?></h1>
			<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=' . self::CPT ) ); ?>" class="page-title-action"><?php esc_html_e( 'Créer une pétition', 'cgt' ); ?></a>
			<hr class="wp-header-end">

			<table class="wp-list-table widefat fixed striped cgt-petitions-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Titre', 'cgt' ); ?></th>
						<th><?php esc_html_e( 'Statut', 'cgt' ); ?></th>
						<th><?php esc_html_e( 'Progression', 'cgt' ); ?></th>
						<th><?php esc_html_e( 'Shortcode', 'cgt' ); ?></th>
						<th><?php esc_html_e( 'Actions', 'cgt' ); ?></th>
					</tr>
				</thead>
				<tbody>
				<?php if ( $petitions ) : ?>
					<?php foreach ( $petitions as $petition ) :
						$counts   = $this->get_signature_counts( $petition->ID );
						$target   = (int) get_post_meta( $petition->ID, self::META_TARGET, true );
						$progress = $target > 0 ? min( 100, round( ( $counts / $target ) * 100 ) ) : 0;
						$shortcode = '[cgt_petition id="' . $petition->ID . '"]';
						?>
						<tr>
							<td><a href="<?php echo esc_url( get_edit_post_link( $petition->ID ) ); ?>"><?php echo esc_html( get_the_title( $petition->ID ) ); ?></a></td>
							<td><?php echo esc_html( get_post_status_object( $petition->post_status )->label ); ?></td>
							<td>
								<?php printf( esc_html__( '%1$s signatures / objectif : %2$s', 'cgt' ), number_format_i18n( $counts ), $target ? number_format_i18n( $target ) : '—' ); ?>
								<div class="cgt-progress"><span style="width: <?php echo esc_attr( $progress ); ?>%;"></span></div>
							</td>
							<td><code><?php echo esc_html( $shortcode ); ?></code></td>
							<td>
								<a class="button" href="<?php echo esc_url( add_query_arg( array( 'page' => 'cgt-petitions-signatures', 'petition_id' => $petition->ID ), admin_url( 'admin.php' ) ) ); ?>"><?php esc_html_e( 'Voir les signatures', 'cgt' ); ?></a>
								<a class="button" target="_blank" rel="noopener" href="<?php echo esc_url( get_permalink( $petition->ID ) ); ?>"><?php esc_html_e( 'Voir la page', 'cgt' ); ?></a>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php else : ?>
					<tr><td colspan="5"><?php esc_html_e( 'Aucune pétition pour le moment.', 'cgt' ); ?></td></tr>
				<?php endif; ?>
				</tbody>
			</table>
		</div>
		<?php
	}

	public function render_signatures_page() {
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_die( esc_html__( 'Accès refusé.', 'cgt' ) );
		}

		$petition_id = isset( $_GET['petition_id'] ) ? absint( $_GET['petition_id'] ) : 0;
		$petition    = $petition_id ? get_post( $petition_id ) : null;

		if ( ! $petition || self::CPT !== $petition->post_type ) {
			echo '<div class="wrap"><h1>' . esc_html__( 'Signatures', 'cgt' ) . '</h1><p>' . esc_html__( 'Pétition introuvable.', 'cgt' ) . '</p></div>';
			return;
		}

		global $wpdb;
		$table   = $wpdb->prefix . self::TABLE_SIGN;
		$results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table WHERE petition_id = %d ORDER BY created_at DESC", $petition_id ) );
		$count   = count( $results );
		$target  = (int) get_post_meta( $petition_id, self::META_TARGET, true );
		?>
		<div class="wrap">
			<h1><?php printf( esc_html__( 'Signatures – %s', 'cgt' ), esc_html( get_the_title( $petition_id ) ) ); ?></h1>
			<?php if ( isset( $_GET['deleted'] ) ) : ?>
				<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Signature supprimée.', 'cgt' ); ?></p></div>
			<?php endif; ?>
			<p><?php printf( esc_html__( '%1$s signatures collectées sur un objectif de %2$s.', 'cgt' ), number_format_i18n( $count ), $target ? number_format_i18n( $target ) : '—' ); ?></p>
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Nom', 'cgt' ); ?></th>
						<th><?php esc_html_e( 'Email', 'cgt' ); ?></th>
						<th><?php esc_html_e( 'Date', 'cgt' ); ?></th>
						<th><?php esc_html_e( 'Actions', 'cgt' ); ?></th>
					</tr>
				</thead>
				<tbody>
				<?php if ( $results ) : ?>
					<?php foreach ( $results as $row ) : ?>
						<tr>
							<td><?php echo esc_html( $row->name ); ?></td>
							<td><a href="mailto:<?php echo esc_attr( $row->email ); ?>"><?php echo esc_html( $row->email ); ?></a></td>
							<td><?php echo esc_html( mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $row->created_at ) ); ?></td>
							<td><a class="button button-link-delete" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=cgt_delete_signature&petition_id=' . $petition_id . '&signature_id=' . $row->id ), 'cgt_delete_signature_' . $row->id ) ); ?>"><?php esc_html_e( 'Supprimer', 'cgt' ); ?></a></td>
						</tr>
					<?php endforeach; ?>
				<?php else : ?>
					<tr><td colspan="4"><?php esc_html_e( 'Aucune signature pour le moment.', 'cgt' ); ?></td></tr>
				<?php endif; ?>
				</tbody>
			</table>
		</div>
		<?php
	}

	public function manage_petition_columns( $columns ) {
		$columns['signatures'] = __( 'Signatures', 'cgt' );
		return $columns;
	}

	public function render_petition_columns( $column, $post_id ) {
		if ( 'signatures' === $column ) {
			$count  = $this->get_signature_counts( $post_id );
			$target = (int) get_post_meta( $post_id, self::META_TARGET, true );
			echo esc_html( sprintf( '%1$s / %2$s', number_format_i18n( $count ), $target ? number_format_i18n( $target ) : '—' ) );
		}
	}

	private function get_signature_counts( $petition_id ) {
		global $wpdb;
		return (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . $wpdb->prefix . self::TABLE_SIGN . ' WHERE petition_id = %d', $petition_id ) );
	}

	public function register_front_assets() {
		wp_register_style(
			'cgt-petition-front',
			plugin_dir_url( __FILE__ ) . 'assets/css/petition-public.css',
			array(),
			'1.0.0'
		);

		wp_register_script(
			'cgt-petition-front-js',
			plugin_dir_url( __FILE__ ) . 'assets/js/petition-public.js',
			array(),
			'1.0.0',
			true
		);
	}

	public function maybe_append_petition_content( $content ) {
		if ( is_singular( self::CPT ) && in_the_loop() && is_main_query() ) {
			$petition_markup = $this->petition_shortcode( array( 'id' => get_the_ID() ) );

			// Avoid double output if editor already contains shortcode.
			if ( false === strpos( $content, '[cgt_petition' ) ) {
				$content .= $petition_markup;
			}

			return $content;
		}

		return $content;
	}

	public function petition_shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'id' => 0,
			),
			$atts,
			'cgt_petition'
		);

		$petition_id = $atts['id'] ? absint( $atts['id'] ) : get_the_ID();
		$petition    = get_post( $petition_id );

		if ( ! $petition || self::CPT !== $petition->post_type ) {
			return '';
		}

		// Enqueue assets
		wp_enqueue_style( 'cgt-petition-front' );
		wp_enqueue_script( 'cgt-petition-front-js' );

		$target   = (int) get_post_meta( $petition_id, self::META_TARGET, true );
		$pdf_id   = (int) get_post_meta( $petition_id, self::META_PDF, true );
		$pdf_url  = $pdf_id ? wp_get_attachment_url( $pdf_id ) : '';
		$count    = $this->get_signature_counts( $petition_id );
		$progress = $target > 0 ? min( 100, round( ( $count / $target ) * 100 ) ) : 0;
		$status   = isset( $_GET['petition_status'] ) ? sanitize_key( $_GET['petition_status'] ) : '';

		// Extract first paragraph as excerpt
		$content_parts = explode( '</p>', $petition->post_content );
		$excerpt = wp_strip_all_tags( $content_parts[0] ?? '' );

		ob_start();
		?>
		<div class="cgt-petition">

			<!-- Hero Section -->
			<div class="cgt-petition-hero">
				<div class="cgt-petition-hero__content">
					<div class="cgt-petition-hero__badge">
						<span>📢</span>
						<?php esc_html_e( 'Pétition en cours', 'cgt' ); ?>
					</div>
					<h1><?php echo esc_html( get_the_title( $petition_id ) ); ?></h1>
					<?php if ( $excerpt ) : ?>
						<p class="cgt-petition-hero__excerpt"><?php echo esc_html( $excerpt ); ?></p>
					<?php endif; ?>
				</div>
			</div>

			<!-- Stats & Progress -->
			<div class="cgt-petition-stats">
				<div class="cgt-petition-stats__header">
					<div class="cgt-petition-stats__numbers">
						<span class="cgt-petition-stats__current"><?php echo number_format_i18n( $count ); ?></span>
						<span class="cgt-petition-stats__target">
							/ <?php echo $target ? number_format_i18n( $target ) : '∞'; ?> <?php esc_html_e( 'signatures', 'cgt' ); ?>
						</span>
					</div>
					<div class="cgt-petition-stats__percentage">
						<?php echo esc_html( $progress ); ?>%
					</div>
				</div>

				<div class="cgt-petition-progress">
					<div class="cgt-petition-progress__fill" style="width: <?php echo esc_attr( $progress ); ?>%;"></div>
				</div>

				<div class="cgt-petition-progress__label">
					<span><?php echo number_format_i18n( $count ); ?> <?php esc_html_e( 'personnes ont signé', 'cgt' ); ?></span>
					<?php if ( $target > $count ) : ?>
						<span><?php printf( esc_html__( 'Encore %s signatures nécessaires', 'cgt' ), number_format_i18n( $target - $count ) ); ?></span>
					<?php else : ?>
						<span><?php esc_html_e( 'Objectif atteint !', 'cgt' ); ?> 🎉</span>
					<?php endif; ?>
				</div>
			</div>

			<!-- Content Grid -->
			<div class="cgt-petition-grid">

				<!-- Left: Content -->
				<div class="cgt-petition-content">
					<h2><?php esc_html_e( 'Notre demande', 'cgt' ); ?></h2>

					<div class="cgt-petition-content__text">
						<?php echo wpautop( wp_kses_post( $petition->post_content ) ); ?>
					</div>

					<?php if ( has_post_thumbnail( $petition_id ) ) : ?>
						<div class="cgt-petition-content__thumb">
							<?php echo get_the_post_thumbnail( $petition_id, 'large' ); ?>
						</div>
					<?php endif; ?>

					<?php if ( $pdf_url ) : ?>
						<div class="cgt-petition-pdf">
							<div class="cgt-petition-pdf__icon">📄</div>
							<p><strong><?php esc_html_e( 'Document disponible', 'cgt' ); ?></strong></p>
							<a href="<?php echo esc_url( $pdf_url ); ?>" target="_blank" rel="noopener" class="cgt-petition-pdf__button">
								<?php esc_html_e( 'Télécharger le PDF', 'cgt' ); ?>
							</a>
						</div>
					<?php endif; ?>
				</div>

				<!-- Right: Signature Form -->
				<div class="cgt-petition-form-wrapper">
					<div class="cgt-petition-form__header">
						<div class="cgt-petition-form__icon">✍️</div>
						<h3 class="cgt-petition-form__title"><?php esc_html_e( 'Signez la pétition', 'cgt' ); ?></h3>
						<p class="cgt-petition-form__subtitle"><?php esc_html_e( 'Ajoutez votre voix à ce mouvement', 'cgt' ); ?></p>
					</div>

					<?php if ( 'success' === $status ) : ?>
						<div class="cgt-petition-message cgt-petition-message--success">
							<?php esc_html_e( 'Merci pour votre soutien ! Votre signature a été enregistrée.', 'cgt' ); ?>
						</div>
					<?php elseif ( 'duplicate' === $status ) : ?>
						<div class="cgt-petition-message cgt-petition-message--error">
							<?php esc_html_e( 'Vous avez déjà signé cette pétition.', 'cgt' ); ?>
						</div>
					<?php elseif ( 'error' === $status ) : ?>
						<div class="cgt-petition-message cgt-petition-message--error">
							<?php esc_html_e( 'Une erreur est survenue. Merci de réessayer.', 'cgt' ); ?>
						</div>
					<?php endif; ?>

					<form method="post" class="cgt-petition-form">
						<?php wp_nonce_field( 'cgt_petition_sign_' . $petition_id, 'cgt_petition_nonce' ); ?>
						<input type="hidden" name="cgt_petition_action" value="sign">
						<input type="hidden" name="petition_id" value="<?php echo esc_attr( $petition_id ); ?>">

						<div class="cgt-petition-form__field">
							<label class="cgt-petition-form__label" for="cgt_petition_name_<?php echo esc_attr( $petition_id ); ?>">
								<?php esc_html_e( 'Nom et prénom', 'cgt' ); ?>
							</label>
							<input
								type="text"
								name="cgt_petition_name"
								id="cgt_petition_name_<?php echo esc_attr( $petition_id ); ?>"
								class="cgt-petition-form__input"
								required
								placeholder="<?php esc_attr_e( 'Jean Dupont', 'cgt' ); ?>"
							>
						</div>

						<div class="cgt-petition-form__field">
							<label class="cgt-petition-form__label" for="cgt_petition_email_<?php echo esc_attr( $petition_id ); ?>">
								<?php esc_html_e( 'Adresse email', 'cgt' ); ?>
							</label>
							<input
								type="email"
								name="cgt_petition_email"
								id="cgt_petition_email_<?php echo esc_attr( $petition_id ); ?>"
								class="cgt-petition-form__input"
								required
								placeholder="<?php esc_attr_e( 'jean.dupont@email.fr', 'cgt' ); ?>"
							>
						</div>

						<button type="submit" class="cgt-petition-form__submit">
							<?php esc_html_e( 'Signer la pétition', 'cgt' ); ?>
						</button>

						<p class="cgt-petition-form__privacy">
							<?php esc_html_e( 'Vos données sont sécurisées et ne seront jamais partagées avec des tiers.', 'cgt' ); ?>
						</p>
					</form>
				</div>

			</div>

		</div>
		<?php
		return ob_get_clean();
	}

	public function handle_signature_submission() {
		if ( ! isset( $_POST['cgt_petition_action'] ) || 'sign' !== $_POST['cgt_petition_action'] ) {
			return;
		}

		$petition_id = isset( $_POST['petition_id'] ) ? absint( $_POST['petition_id'] ) : 0;
		$petition    = get_post( $petition_id );

		if ( ! $petition || self::CPT !== $petition->post_type ) {
			wp_safe_redirect( add_query_arg( 'petition_status', 'error', wp_get_referer() ) );
			exit;
		}

		if ( ! isset( $_POST['cgt_petition_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['cgt_petition_nonce'] ), 'cgt_petition_sign_' . $petition_id ) ) {
			wp_safe_redirect( add_query_arg( 'petition_status', 'error', wp_get_referer() ) );
			exit;
		}

		$name  = isset( $_POST['cgt_petition_name'] ) ? sanitize_text_field( wp_unslash( $_POST['cgt_petition_name'] ) ) : '';
		$email = isset( $_POST['cgt_petition_email'] ) ? sanitize_email( wp_unslash( $_POST['cgt_petition_email'] ) ) : '';

		if ( empty( $name ) || empty( $email ) || ! is_email( $email ) ) {
			wp_safe_redirect( add_query_arg( 'petition_status', 'error', get_permalink( $petition_id ) ) );
			exit;
		}

		global $wpdb;
		$table = $wpdb->prefix . self::TABLE_SIGN;
		$exists = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $table WHERE petition_id = %d AND email = %s", $petition_id, $email ) );

		if ( $exists ) {
			wp_safe_redirect( add_query_arg( 'petition_status', 'duplicate', get_permalink( $petition_id ) ) );
			exit;
		}

		$wpdb->insert(
			$table,
			array(
				'petition_id' => $petition_id,
				'name'        => $name,
				'email'       => $email,
			),
			array( '%d', '%s', '%s' )
		);

		wp_safe_redirect( add_query_arg( 'petition_status', 'success', get_permalink( $petition_id ) ) );
		exit;
	}

	public function handle_delete_signature() {
		if ( ! current_user_can( 'delete_posts' ) ) {
			wp_die( esc_html__( 'Accès refusé.', 'cgt' ) );
		}

		$petition_id  = isset( $_GET['petition_id'] ) ? absint( $_GET['petition_id'] ) : 0;
		$signature_id = isset( $_GET['signature_id'] ) ? absint( $_GET['signature_id'] ) : 0;

		if ( ! $petition_id || ! $signature_id ) {
			wp_safe_redirect( admin_url( 'admin.php?page=cgt-petitions' ) );
			exit;
		}

		check_admin_referer( 'cgt_delete_signature_' . $signature_id );

		global $wpdb;
		$wpdb->delete( $wpdb->prefix . self::TABLE_SIGN, array( 'id' => $signature_id ), array( '%d' ) );

		wp_safe_redirect( admin_url( 'admin.php?page=cgt-petitions-signatures&petition_id=' . $petition_id . '&deleted=1' ) );
		exit;
	}
}

CGT_Petitions_Plugin::init();
