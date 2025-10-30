<?php

namespace CGT\Dashboard;

use function CGT\Dashboard\ensure_mandat_dir;

defined( 'ABSPATH' ) || exit;

class Admin {

	public function hooks() {
		add_action( 'admin_menu', array( $this, 'register_admin_pages' ) );
		add_action( 'admin_post_cgt_dd_update_password', array( $this, 'handle_password_update' ) );
		add_action( 'admin_post_cgt_dd_save_mandat', array( $this, 'handle_save_mandat' ) );
		add_action( 'admin_post_cgt_dd_create_content', array( $this, 'handle_create_content' ) );
		add_action( 'admin_post_cgt_dd_export_adhesions', array( $this, 'handle_export_adhesions' ) );
		add_action( 'admin_post_nopriv_cgt_dd_export_adhesions', array( $this, 'handle_export_adhesions' ) );
	}

	public function register_admin_pages() {
		add_users_page(
			__( 'Comptes dédiés', 'departement-dashboard' ),
			__( 'Comptes dédiés', 'departement-dashboard' ),
			'manage_options',
			'cgt-dd-accounts',
			array( $this, 'render_accounts_page' )
		);
	}

	public function render_accounts_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Accès refusé.', 'departement-dashboard' ) );
		}

		$accounts = array(
			Roles::ROLE_ADMIN        => 'administration',
			Roles::ROLE_GESTIONNAIRE => 'gestionnaire',
			Roles::ROLE_ASSISTANTE   => 'assistante',
		);
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Comptes dédiés aux tableaux de bord', 'departement-dashboard' ); ?></h1>
			<p><?php esc_html_e( 'Modifiez les mots de passe temporaires attribués aux tableaux de bord dédiés.', 'departement-dashboard' ); ?></p>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Utilisateur', 'departement-dashboard' ); ?></th>
						<th><?php esc_html_e( 'Rôle', 'departement-dashboard' ); ?></th>
						<th><?php esc_html_e( 'Modifier le mot de passe', 'departement-dashboard' ); ?></th>
					</tr>
				</thead>
				<tbody>
				<?php foreach ( $accounts as $role => $login ) : ?>
					<?php $user = get_user_by( 'login', $login ); ?>
					<tr>
						<td><?php echo esc_html( $login ); ?></td>
						<td><?php echo esc_html( $role ); ?></td>
						<td>
							<?php if ( $user ) : ?>
								<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
									<?php wp_nonce_field( 'cgt_dd_update_password_' . $user->ID ); ?>
									<input type="hidden" name="action" value="cgt_dd_update_password">
									<input type="hidden" name="user_id" value="<?php echo esc_attr( $user->ID ); ?>">
									<input type="password" name="new_password" required placeholder="<?php esc_attr_e( 'Nouveau mot de passe', 'departement-dashboard' ); ?>">
									<button type="submit" class="button button-primary"><?php esc_html_e( 'Mettre à jour', 'departement-dashboard' ); ?></button>
								</form>
							<?php else : ?>
								<?php esc_html_e( 'Utilisateur introuvable.', 'departement-dashboard' ); ?>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<?php
	}

	public function handle_password_update() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Accès refusé.', 'departement-dashboard' ) );
		}

		$user_id = isset( $_POST['user_id'] ) ? absint( $_POST['user_id'] ) : 0;
		$new_pass = isset( $_POST['new_password'] ) ? sanitize_text_field( wp_unslash( $_POST['new_password'] ) ) : '';

		if ( ! $user_id || empty( $new_pass ) || ! wp_verify_nonce( wp_unslash( $_POST['_wpnonce'] ?? '' ), 'cgt_dd_update_password_' . $user_id ) ) {
			wp_die( esc_html__( 'Requête invalide.', 'departement-dashboard' ) );
		}

		wp_set_password( $new_pass, $user_id );
		update_user_meta( $user_id, 'cgt_dd_temp_password', $new_pass );

		wp_safe_redirect( add_query_arg( 'updated', 'true', admin_url( 'users.php?page=cgt-dd-accounts' ) ) );
		exit;
	}

	public function handle_save_mandat() {
		if ( ! current_user_can( 'manage_cgt_adhesions' ) ) {
			wp_die( esc_html__( 'Accès refusé.', 'departement-dashboard' ) );
		}

		check_admin_referer( 'cgt_dd_save_mandat' );

		$name = sanitize_text_field( wp_unslash( $_POST['mandat_name'] ?? '' ) );
		$rib  = sanitize_textarea_field( wp_unslash( $_POST['mandat_rib'] ?? '' ) );

		if ( empty( $name ) || empty( $rib ) ) {
			wp_die( esc_html__( 'Les champs sont requis.', 'departement-dashboard' ) );
		}

		$mandat_file = '';
		if ( ! empty( $_FILES['mandat_pdf']['name'] ) ) {
			if ( ( $_FILES['mandat_pdf']['size'] ?? 0 ) > 10 * MB_IN_BYTES ) {
				wp_die( esc_html__( 'Le fichier dépasse la taille maximale autorisée (10 Mo).', 'departement-dashboard' ) );
			}

			$uploaded = wp_handle_upload(
				$_FILES['mandat_pdf'],
				array(
					'test_form' => false,
					'mimes'     => array( 'pdf' => 'application/pdf' ),
				)
			);

		if ( isset( $uploaded['error'] ) ) {
			wp_die( esc_html( $uploaded['error'] ) );
		}

		$tmp_path = $uploaded['file'];
		$finfo    = finfo_open( FILEINFO_MIME_TYPE );
		$mime     = $finfo ? finfo_file( $finfo, $tmp_path ) : null;
		if ( $finfo ) {
			finfo_close( $finfo );
		}

		if ( 'application/pdf' !== $mime ) {
			@unlink( $tmp_path );
			wp_die( esc_html__( 'Seuls les fichiers PDF sont autorisés.', 'departement-dashboard' ) );
		}

		$dest_dir = ensure_mandat_dir();
		$filename = wp_basename( $uploaded['file'] );
		$new_path = trailingslashit( $dest_dir ) . $filename;
		if ( ! rename( $uploaded['file'], $new_path ) ) {
				wp_die( esc_html__( 'Impossible de déplacer le fichier téléchargé.', 'departement-dashboard' ) );
			}

			$upload_dir = wp_upload_dir();
			$mandat_file = trailingslashit( $upload_dir['baseurl'] ) . 'mandats/' . $filename;
		}

		$post_id = wp_insert_post(
			array(
				'post_type'   => 'cgt_mandat',
				'post_status' => 'private',
				'post_title'  => $name,
			),
			true
		);

		if ( is_wp_error( $post_id ) ) {
			wp_die( esc_html__( 'Impossible d’enregistrer le mandat.', 'departement-dashboard' ) );
		}

		update_post_meta( $post_id, '_cgt_mandat_rib', $rib );
		update_post_meta( $post_id, '_cgt_mandat_file', $mandat_file );

		wp_safe_redirect( add_query_arg( 'mandat', 'saved', home_url( '/dashboard/admin' ) ) );
		exit;
	}

	public function handle_create_content() {
		if ( ! $this->user_has_role( Roles::ROLE_GESTIONNAIRE ) && ! current_user_can( 'administrator' ) ) {
			wp_die( esc_html__( 'Accès refusé.', 'departement-dashboard' ) );
		}

		check_admin_referer( 'cgt_dd_create_content' );

		$type    = sanitize_key( $_POST['content_type'] ?? '' );
		$title   = sanitize_text_field( wp_unslash( $_POST['content_title'] ?? '' ) );
		$content = wp_kses_post( $_POST['content_body'] ?? '' );
		$is_private = ! empty( $_POST['content_private'] );

		if ( empty( $type ) || empty( $title ) ) {
			wp_die( esc_html__( 'Merci de renseigner tous les champs obligatoires.', 'departement-dashboard' ) );
		}

		$postarr = array(
			'post_title'   => $title,
			'post_content' => $content,
			'post_type'    => $type,
			'post_status'  => $is_private ? 'private' : 'publish',
		);

		$post_id = wp_insert_post( $postarr, true );

		if ( is_wp_error( $post_id ) ) {
			wp_die( esc_html__( 'Impossible de créer la publication.', 'departement-dashboard' ) );
		}

		wp_safe_redirect( add_query_arg( 'created', 'true', home_url( '/dashboard/gestionnaire' ) ) );
		exit;
	}

	public function handle_export_adhesions() {
	if ( ! ( current_user_can( 'manage_cgt_adhesions' ) || $this->user_has_role( Roles::ROLE_ASSISTANTE ) ) ) {
		wp_die( esc_html__( 'Accès refusé.', 'departement-dashboard' ) );
	}

		check_admin_referer( 'cgt_dd_export_adhesions' );

		$args = array(
			'post_type'      => 'cgt_adhesion',
			'posts_per_page' => -1,
			'post_status'    => array( 'publish', 'private' ),
			'orderby'        => 'date',
			'order'          => 'DESC',
		);

		$query = new \WP_Query( $args );

		$rows = array();
		foreach ( $query->posts as $post ) {
			$rows[] = array(
				'titre'  => get_the_title( $post ),
				'date'   => get_the_date( 'd/m/Y', $post ),
				'email'  => get_post_meta( $post->ID, 'cgt_adhesion_email', true ),
				'statut' => $post->post_status,
			);
		}

		$html  = '<h1>Liste des adhésions</h1>';
		$html .= '<table border="1" cellpadding="6" cellspacing="0" width="100%">';
		$html .= '<thead><tr><th>Nom</th><th>Date</th><th>Email</th><th>Statut</th></tr></thead><tbody>';
		foreach ( $rows as $row ) {
			$html .= '<tr>';
			$html .= '<td>' . esc_html( $row['titre'] ) . '</td>';
			$html .= '<td>' . esc_html( $row['date'] ) . '</td>';
			$html .= '<td>' . esc_html( $row['email'] ) . '</td>';
			$html .= '<td>' . esc_html( $row['statut'] ) . '</td>';
			$html .= '</tr>';
		}
		$html .= '</tbody></table>';

		if ( class_exists( '\\Dompdf\\Dompdf' ) ) {
			$dompdf = new \Dompdf\Dompdf();
			$dompdf->loadHtml( $html );
			$dompdf->setPaper( 'A4', 'portrait' );
			$dompdf->render();
			$dompdf->stream( 'adhesions.pdf', array( 'Attachment' => true ) );
		} else {
			header( 'Content-Type: application/pdf' );
			header( 'Content-Disposition: attachment; filename="adhesions.pdf"' );
			echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		exit;
	}

	private function user_has_role( $role ) {
		$user = wp_get_current_user();
		return in_array( $role, (array) $user->roles, true );
	}
}
