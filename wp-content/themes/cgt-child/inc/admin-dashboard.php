<?php
/**
 * Admin dashboard helpers.
 *
 * @package CGT_Child
 */

defined( 'ABSPATH' ) || exit;

add_action( 'admin_post_cgt_create_private_adherent_article', 'cgt_create_private_adherent_article' );

/**
 * Register hidden dashboard page for creating private tracts with branch selection.
 */
add_action( 'admin_menu', 'cgt_register_private_tract_admin_page' );
function cgt_register_private_tract_admin_page() {
	if ( ! current_user_can( 'edit_posts' ) ) {
		return;
	}

	add_dashboard_page(
		__( 'Créer un tract privé', 'cgt' ),
		__( 'Créer un tract privé', 'cgt' ),
		'edit_posts',
		'cgt-create-private-tract',
		'cgt_render_private_tract_admin_page'
	);

	remove_submenu_page( 'index.php', 'cgt-create-private-tract' );
}

/**
 * Render the admin page allowing branch selection for private tracts.
 */
function cgt_render_private_tract_admin_page() {
	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_die( esc_html__( 'Accès refusé.', 'cgt' ) );
	}

	$branches = get_terms(
		array(
			'taxonomy'   => 'branche',
			'hide_empty' => false,
			'orderby'    => 'name',
			'order'      => 'ASC',
		)
	);

	$error = isset( $_GET['cgt_error'] ) ? sanitize_key( wp_unslash( $_GET['cgt_error'] ) ) : '';
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Créer un tract privé adhérent', 'cgt' ); ?></h1>
		<p><?php esc_html_e( 'Sélectionnez la branche concernée avant de rédiger le tract réservé aux adhérent·es.', 'cgt' ); ?></p>

		<?php if ( 'no_branch' === $error ) : ?>
			<div class="notice notice-error"><p><?php esc_html_e( 'Merci de sélectionner une branche avant de continuer.', 'cgt' ); ?></p></div>
		<?php elseif ( 'invalid_branch' === $error ) : ?>
			<div class="notice notice-error"><p><?php esc_html_e( 'La branche sélectionnée est introuvable.', 'cgt' ); ?></p></div>
		<?php endif; ?>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( 'cgt_create_private_tract' ); ?>
			<input type="hidden" name="action" value="cgt_create_private_tract">

			<table class="form-table">
				<tr>
					<th scope="row"><label for="cgt_private_tract_branch"><?php esc_html_e( 'Branche', 'cgt' ); ?></label></th>
					<td>
						<select id="cgt_private_tract_branch" name="cgt_private_tract_branch" required>
							<option value=""><?php esc_html_e( 'Sélectionner une branche', 'cgt' ); ?></option>
							<?php if ( ! is_wp_error( $branches ) ) : ?>
								<?php foreach ( $branches as $branch ) : ?>
									<option value="<?php echo esc_attr( $branch->slug ); ?>"><?php echo esc_html( $branch->name ); ?></option>
								<?php endforeach; ?>
							<?php endif; ?>
						</select>
					</td>
				</tr>
			</table>

			<?php submit_button( __( 'Créer le tract privé', 'cgt' ) ); ?>
		</form>
	</div>
	<?php
}

add_action( 'admin_menu', 'cgt_register_private_article_admin_page' );
function cgt_register_private_article_admin_page() {
	if ( ! current_user_can( 'edit_posts' ) ) {
		return;
	}

	add_submenu_page(
		'edit.php?post_type=articles_adherents',
		__( 'Créer un article privé', 'cgt' ),
		__( 'Créer un article privé', 'cgt' ),
		'edit_posts',
		'cgt-create-private-article',
		'cgt_render_private_article_admin_page'
	);

	add_submenu_page(
		'edit.php?post_type=articles_adherents',
		__( 'Créer un tract privé', 'cgt' ),
		__( 'Créer un tract privé', 'cgt' ),
		'edit_posts',
		'cgt-create-private-tract',
		'cgt_render_private_tract_admin_page'
	);

	// Ajouter un sous-menu dans Messages de contact vers la liste des questions adhérents.
	add_submenu_page(
		'edit.php?post_type=cgt_contact',
		__( 'Questions adhérents', 'cgt' ),
		__( 'Questions adhérents', 'cgt' ),
		'edit_posts',
		'edit.php?post_type=cgt_question'
	);
}

/**
 * Customize Branch submenu: remove "Branches" taxonomy, add "Articles", keep "Thématiques".
 */
add_action( 'admin_menu', 'cgt_customize_branch_menu', 999 );
function cgt_customize_branch_menu() {
	// Remove "Branches" taxonomy submenu
	remove_submenu_page( 'edit.php?post_type=branch', 'edit-tags.php?taxonomy=branche&post_type=branch' );

	// Add "Articles" submenu to redirect to posts filtered by branch taxonomy
	add_submenu_page(
		'edit.php?post_type=branch',
		__( 'Articles', 'cgt' ),
		__( 'Articles', 'cgt' ),
		'edit_posts',
		'edit.php?taxonomy=branche'
	);
}

function cgt_render_private_article_admin_page() {
	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_die( esc_html__( 'Accès refusé.', 'cgt' ) );
	}

	$branches = get_terms(
		array(
			'taxonomy'   => 'branche',
			'hide_empty' => false,
			'orderby'    => 'name',
			'order'      => 'ASC',
		)
	);

	$error = isset( $_GET['cgt_error'] ) ? sanitize_key( wp_unslash( $_GET['cgt_error'] ) ) : '';
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Créer un article privé adhérent', 'cgt' ); ?></h1>
		<p><?php esc_html_e( 'Sélectionnez la branche concernée avant de rédiger l’article réservé aux adhérent·es.', 'cgt' ); ?></p>

		<?php if ( 'no_branch' === $error ) : ?>
			<div class="notice notice-error"><p><?php esc_html_e( 'Merci de sélectionner une branche avant de continuer.', 'cgt' ); ?></p></div>
		<?php elseif ( 'invalid_branch' === $error ) : ?>
			<div class="notice notice-error"><p><?php esc_html_e( 'La branche sélectionnée est introuvable.', 'cgt' ); ?></p></div>
		<?php endif; ?>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( 'cgt_create_private_article' ); ?>
			<input type="hidden" name="action" value="cgt_create_private_adherent_article">

			<table class="form-table">
				<tr>
					<th scope="row"><label for="cgt_private_article_branch"><?php esc_html_e( 'Branche', 'cgt' ); ?></label></th>
					<td>
						<select id="cgt_private_article_branch" name="cgt_private_article_branch" required>
							<option value=""><?php esc_html_e( 'Sélectionner une branche', 'cgt' ); ?></option>
							<?php if ( ! is_wp_error( $branches ) ) : ?>
								<?php foreach ( $branches as $branch ) : ?>
									<option value="<?php echo esc_attr( $branch->slug ); ?>"><?php echo esc_html( $branch->name ); ?></option>
								<?php endforeach; ?>
							<?php endif; ?>
						</select>
					</td>
				</tr>
			</table>

			<?php submit_button( __( 'Créer l’article privé', 'cgt' ) ); ?>
		</form>
	</div>
	<?php
}

add_action( 'admin_post_cgt_create_private_adherent_article', 'cgt_create_private_adherent_article' );

function cgt_create_private_adherent_article() {
	if ( ! current_user_can( 'edit_others_posts' ) ) {
		wp_die( esc_html__( 'Accès refusé.', 'cgt' ) );
	}

	check_admin_referer( 'cgt_create_private_article' );

	$branch_slug = isset( $_POST['cgt_private_article_branch'] ) ? sanitize_text_field( wp_unslash( $_POST['cgt_private_article_branch'] ) ) : '';
	$redirect    = admin_url( 'index.php?page=cgt-create-private-article' );

	if ( empty( $branch_slug ) ) {
		wp_safe_redirect( add_query_arg( 'cgt_error', 'no_branch', $redirect ) );
		exit;
	}

	$branch_term = get_term_by( 'slug', $branch_slug, 'branche' );
	if ( ! $branch_term || is_wp_error( $branch_term ) ) {
		wp_safe_redirect( add_query_arg( 'cgt_error', 'invalid_branch', $redirect ) );
		exit;
	}

	$post_id = wp_insert_post(
		array(
			'post_type'   => 'articles_adherents',
			'post_status' => 'private',
			'post_title'  => __( 'Privé adhérent – article', 'cgt' ),
		),
		true
	);

	if ( is_wp_error( $post_id ) || ! $post_id ) {
		wp_safe_redirect( add_query_arg( 'cgt_error', 'creation_failed', $redirect ) );
		exit;
	}

	wp_set_object_terms( $post_id, array( (int) $branch_term->term_id ), 'branche', false );

	$edit_link = get_edit_post_link( $post_id, 'raw' );
	if ( ! $edit_link ) {
		$edit_link = admin_url( 'post.php?post=' . $post_id . '&action=edit' );
	}

	wp_safe_redirect( $edit_link );
	exit;
}

add_action( 'admin_post_cgt_create_private_tract', 'cgt_create_private_tract' );

/**
 * Create a blank private tract for adherents.
 */
function cgt_create_private_tract() {
	if ( ! current_user_can( 'edit_others_posts' ) ) {
		wp_die( esc_html__( 'Accès refusé.', 'cgt' ) );
	}

	check_admin_referer( 'cgt_create_private_tract' );

	$branch_slug = isset( $_POST['cgt_private_tract_branch'] ) ? sanitize_text_field( wp_unslash( $_POST['cgt_private_tract_branch'] ) ) : '';

	$redirect = admin_url( 'index.php?page=cgt-create-private-tract' );
	if ( empty( $branch_slug ) ) {
		wp_safe_redirect( add_query_arg( 'cgt_error', 'no_branch', $redirect ) );
		exit;
	}

	$branch_term = get_term_by( 'slug', $branch_slug, 'branche' );
	if ( ! $branch_term || is_wp_error( $branch_term ) ) {
		wp_safe_redirect( add_query_arg( 'cgt_error', 'invalid_branch', $redirect ) );
		exit;
	}

	$post_id = wp_insert_post(
		array(
			'post_type'   => 'tracts',
			'post_status' => 'private',
			'post_title'  => __( 'Privé adhérent – tract', 'cgt' ),
		),
		true
	);

	if ( is_wp_error( $post_id ) || ! $post_id ) {
		wp_safe_redirect( add_query_arg( 'cgt_error', 'creation_failed', $redirect ) );
		exit;
	}

	update_post_meta( $post_id, 'cgt_visibilite', 'prive' );
	wp_set_object_terms( $post_id, array( (int) $branch_term->term_id ), 'branche', false );

	$edit_link = get_edit_post_link( $post_id, 'raw' );
	if ( ! $edit_link ) {
		$edit_link = admin_url( 'post.php?post=' . $post_id . '&action=edit' );
	}

	wp_safe_redirect( $edit_link );
	exit;
}

/**
 * Handle user branch selection from member dashboard.
 */
add_action( 'admin_post_cgt_select_user_branch', 'cgt_handle_user_branch_selection' );
add_action( 'admin_post_nopriv_cgt_select_user_branch', 'cgt_handle_user_branch_selection' );

function cgt_handle_user_branch_selection() {
	if ( ! is_user_logged_in() ) {
		wp_die( esc_html__( 'Vous devez être connecté.', 'cgt' ) );
	}

	check_admin_referer( 'cgt_select_branch', 'cgt_branch_nonce' );

	$user_id = get_current_user_id();
	$branch_id = isset( $_POST['user_branch'] ) ? absint( $_POST['user_branch'] ) : 0;

	if ( ! $branch_id ) {
		wp_safe_redirect( home_url( '/espace-adherent' ) );
		exit;
	}

	$branch_term = get_term( $branch_id, 'branche' );
	if ( ! $branch_term || is_wp_error( $branch_term ) ) {
		wp_safe_redirect( home_url( '/espace-adherent' ) );
		exit;
	}

	update_user_meta( $user_id, 'cgt_user_branch', $branch_id );

	wp_safe_redirect( home_url( '/espace-adherent' ) );
	exit;
}
