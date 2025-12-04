<?php
/**
 * Brevo admin helpers (front-end only, sans intégration API).
 *
 * @package CGT_Child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Ajout de la metabox Brevo sur les articles (bulletins uniquement).
 */
add_action( 'add_meta_boxes', 'cgt_brevo_register_metabox' );
function cgt_brevo_register_metabox() {
	global $post;

	// Vérifier si l'article a la catégorie "bulletins"
	if ( $post && has_category( 'bulletins', $post ) ) {
		add_meta_box(
			'cgt-brevo-metabox',
			__( 'Envoi Brevo', 'cgt' ),
			'cgt_brevo_metabox_render',
			'post',
			'side',
			'default'
		);
	}
}

/**
 * Rendu de la metabox Brevo.
 */
function cgt_brevo_metabox_render( $post ) {
	if ( ! current_user_can( 'edit_post', $post->ID ) ) {
		return;
	}

	$last_branch = get_post_meta( $post->ID, '_cgt_brevo_last_branch', true );
	$last_date   = get_post_meta( $post->ID, '_cgt_brevo_last_sent', true );
	$branches    = get_terms(
		array(
			'taxonomy'   => 'branche',
			'hide_empty' => false,
			'orderby'    => 'term_id',
			'order'      => 'ASC',
		)
	);

	echo '<div class="cgt-brevo-metabox">';
	echo '<p>' . esc_html__( 'Enregistrez ici l\'envoi de cet article à une branche via Brevo (simulation de front-end).', 'cgt' ) . '</p>';

	if ( $last_date ) {
		printf(
			'<p><strong>%s</strong><br><span class="cgt-brevo-meta">%s</span></p>',
			esc_html__( 'Dernier envoi enregistré :', 'cgt' ),
			esc_html( sprintf( '%s — %s', $last_date, $last_branch ? $last_branch : __( 'branche non renseignée', 'cgt' ) ) )
		);
	}

	echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
	wp_nonce_field( 'cgt_brevo_mark_send', 'cgt_brevo_nonce' );
	echo '<input type="hidden" name="action" value="cgt_brevo_mark_send">';
	echo '<input type="hidden" name="post_id" value="' . esc_attr( $post->ID ) . '">';

	if ( ! is_wp_error( $branches ) && $branches ) {
		echo '<p><label for="cgt_brevo_branch">' . esc_html__( 'Branche', 'cgt' ) . '</label><br>';
		echo '<select id="cgt_brevo_branch" name="cgt_brevo_branch" class="widefat">';
		echo '<option value="">' . esc_html__( 'Sélectionner une branche', 'cgt' ) . '</option>';
		foreach ( $branches as $branch ) {
			printf(
				'<option value="%1$s" %3$s>%2$s</option>',
				esc_attr( $branch->slug ),
				esc_html( $branch->name ),
				selected( $last_branch, $branch->slug, false )
			);
		}
		echo '</select></p>';
	} else {
		echo '<p>' . esc_html__( 'Aucune branche définie pour le moment.', 'cgt' ) . '</p>';
	}

	echo '<p><button type="submit" class="button button-primary" style="width:100%">' . esc_html__( 'Enregistrer l\'envoi Brevo', 'cgt' ) . '</button></p>';
	$log = get_post_meta( $post->ID, '_cgt_brevo_log', true );
	if ( ! empty( $log ) && is_array( $log ) ) {
		echo '<details class="cgt-brevo-log"><summary>' . esc_html__( 'Historique des envois', 'cgt' ) . '</summary><ul>';
		foreach ( array_reverse( $log ) as $entry ) {
			printf( '<li>%s — %s</li>', esc_html( $entry['date'] ), esc_html( $entry['branch'] ) );
		}
		echo '</ul></details>';
	}

	echo '</form></div>';
}

/**
 * Traitement de la soumission depuis la metabox.
 */
add_action( 'admin_post_cgt_brevo_mark_send', 'cgt_brevo_mark_send_handler' );
function cgt_brevo_mark_send_handler() {
	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_die( esc_html__( 'Accès refusé.', 'cgt' ) );
	}

	$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
	if ( ! $post_id || 'post' !== get_post_type( $post_id ) ) {
		wp_die( esc_html__( 'Article introuvable.', 'cgt' ) );
	}

	check_admin_referer( 'cgt_brevo_mark_send', 'cgt_brevo_nonce' );

	$branch = isset( $_POST['cgt_brevo_branch'] ) ? sanitize_text_field( wp_unslash( $_POST['cgt_brevo_branch'] ) ) : '';

	update_post_meta( $post_id, '_cgt_brevo_last_branch', $branch );
	$timestamp = current_time( 'mysql' );
	update_post_meta( $post_id, '_cgt_brevo_last_sent', $timestamp );

	$log   = get_post_meta( $post_id, '_cgt_brevo_log', true );
	$log   = is_array( $log ) ? $log : array();
	$log[] = array(
		'date'   => $timestamp,
		'branch' => $branch,
	);
	update_post_meta( $post_id, '_cgt_brevo_log', $log );

	$redirect = add_query_arg( array( 'brevo' => 'sent', 'post' => $post_id ), admin_url( 'post.php?action=edit' ) );
	wp_safe_redirect( $redirect );
	exit;
}

/**
 * Tableau de bord Brevo (liste des articles).
 */
add_action( 'admin_menu', 'cgt_register_brevo_dashboard' );
function cgt_register_brevo_dashboard() {
	add_submenu_page(
		'edit.php',
		__( 'Envois Brevo', 'cgt' ),
		__( 'Envois Brevo', 'cgt' ),
		'edit_posts',
		'cgt-brevo-dashboard',
		'cgt_render_brevo_dashboard'
	);
}

function cgt_render_brevo_dashboard() {
	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_die( esc_html__( 'Accès refusé', 'cgt' ) );
	}

	if ( isset( $_GET['brevo'] ) && 'sent' === sanitize_key( wp_unslash( $_GET['brevo'] ) ) ) {
		printf( '<div class="notice notice-success"><p>%s</p></div>', esc_html__( 'Envoi enregistré.', 'cgt' ) );
	}

	$query = new WP_Query(
		array(
			'post_type'      => 'post',
			'post_status'    => array( 'publish' ),
			'posts_per_page' => 20,
			'orderby'        => 'date',
			'order'          => 'DESC',
		)
	);
	$branches = get_terms(
		array(
			'taxonomy'   => 'branche',
			'hide_empty' => false,
		)
	);

	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Envois Brevo', 'cgt' ); ?></h1>
		<p><?php esc_html_e( 'Enregistrez manuellement les envois d’articles vers les branches Brevo (simulation).', 'cgt' ); ?></p>
		<table class="widefat fixed striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Article', 'cgt' ); ?></th>
					<th style="width:120px"><?php esc_html_e( 'Date', 'cgt' ); ?></th>
					<th><?php esc_html_e( 'Dernier envoi', 'cgt' ); ?></th>
					<th><?php esc_html_e( 'Action', 'cgt' ); ?></th>
				</tr>
			</thead>
			<tbody>
			<?php if ( $query->have_posts() ) : ?>
				<?php while ( $query->have_posts() ) : $query->the_post();
					$post_id    = get_the_ID();
					$last_branch = get_post_meta( $post_id, '_cgt_brevo_last_branch', true );
					$last_sent   = get_post_meta( $post_id, '_cgt_brevo_last_sent', true );
					$log         = get_post_meta( $post_id, '_cgt_brevo_log', true );
					?>
					<tr>
						<td><a href="<?php echo esc_url( get_edit_post_link( $post_id ) ); ?>"><?php the_title(); ?></a></td>
						<td style="white-space:nowrap;"><?php echo esc_html( get_the_date() ); ?></td>
						<td>
							<?php
							if ( $last_sent ) {
								printf( '%s — %s', esc_html( $last_sent ), esc_html( $last_branch ) );
							} else {
								esc_html_e( 'Jamais envoyé', 'cgt' );
							}
							?>
							<?php if ( ! empty( $log ) && is_array( $log ) ) : ?>
								<details>
									<summary><?php esc_html_e( 'Historique', 'cgt' ); ?></summary>
									<ul>
										<?php foreach ( array_reverse( $log ) as $entry ) : ?>
											<li><?php echo esc_html( $entry['date'] . ' — ' . $entry['branch'] ); ?></li>
										<?php endforeach; ?>
									</ul>
								</details>
							<?php endif; ?>
						</td>
						<td>
							<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
								<?php wp_nonce_field( 'cgt_brevo_mark_send', 'cgt_brevo_nonce' ); ?>
								<input type="hidden" name="action" value="cgt_brevo_mark_send">
								<input type="hidden" name="post_id" value="<?php echo esc_attr( $post_id ); ?>">
								<select name="cgt_brevo_branch">
									<option value="">— <?php esc_html_e( 'Branche', 'cgt' ); ?> —</option>
									<?php if ( ! is_wp_error( $branches ) ) : foreach ( $branches as $branch ) : ?>
										<option value="<?php echo esc_attr( $branch->slug ); ?>"><?php echo esc_html( $branch->name ); ?></option>
									<?php endforeach; endif; ?>
								</select>
									<button type="submit" class="button button-primary"><?php esc_html_e( 'Envoyer', 'cgt' ); ?></button>
							</form>
						</td>
					</tr>
				<?php endwhile; ?>
			<?php else : ?>
				<tr><td colspan="4"><?php esc_html_e( 'Aucun article trouvé.', 'cgt' ); ?></td></tr>
			<?php endif; ?>
			</tbody>
		</table>
	</div>
	<?php
	wp_reset_postdata();
}
