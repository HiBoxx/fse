<?php
/**
 * Brevo – envoi réel de campagnes email (bulletins uniquement).
 *
 * @package CGT_Child
 */

defined( 'ABSPATH' ) || exit;

// ─────────────────────────────────────────────
// Helpers API Brevo
// ─────────────────────────────────────────────

/**
 * Retourne les headers communs pour l'API Brevo.
 */
function cgt_brevo_headers() {
	return array(
		'api-key'      => defined( 'CGT_BREVO_API_KEY' ) ? CGT_BREVO_API_KEY : '',
		'Content-Type' => 'application/json',
		'accept'       => 'application/json',
	);
}

/**
 * Récupère les listes de diffusion depuis Brevo (avec cache 5 min).
 *
 * @return array|WP_Error
 */
function cgt_brevo_get_lists() {
	$cached = get_transient( 'cgt_brevo_lists' );
	if ( false !== $cached ) {
		return $cached;
	}

	$response = wp_remote_get(
		'https://api.brevo.com/v3/contacts/lists?limit=50&offset=0',
		array(
			'headers' => cgt_brevo_headers(),
			'timeout' => 15,
		)
	);

	if ( is_wp_error( $response ) ) {
		return $response;
	}

	$body = json_decode( wp_remote_retrieve_body( $response ), true );
	$code = wp_remote_retrieve_response_code( $response );

	if ( 200 !== $code ) {
		return new WP_Error( 'brevo_api', isset( $body['message'] ) ? $body['message'] : __( 'Erreur API Brevo', 'cgt' ) );
	}

	$lists = isset( $body['lists'] ) ? $body['lists'] : array();
	set_transient( 'cgt_brevo_lists', $lists, 5 * MINUTE_IN_SECONDS );

	return $lists;
}

/**
 * Envoie une campagne email via l'API Brevo.
 *
 * @param int   $post_id   ID de l'article bulletin.
 * @param int   $list_id   ID de la liste Brevo.
 * @param string $list_name Nom de la liste (pour le log).
 * @return array|WP_Error
 */
function cgt_brevo_send_campaign( $post_id, $list_id, $list_name ) {
	$post      = get_post( $post_id );
	$title     = get_the_title( $post_id );
	$permalink = get_permalink( $post_id );
	$excerpt   = wp_trim_words( get_the_excerpt( $post ), 30, '...' );
	$thumbnail = get_the_post_thumbnail_url( $post_id, 'large' );

	// Contenu HTML de la campagne
	$img_html = $thumbnail
		? '<img src="' . esc_url( $thumbnail ) . '" alt="" style="max-width:100%;height:auto;display:block;margin:0 auto 20px;">'
		: '';

	$html_content = '<!DOCTYPE html><html><body style="font-family:Arial,sans-serif;max-width:600px;margin:0 auto;padding:20px;">'
		. '<h1 style="color:#d11313;">' . esc_html( $title ) . '</h1>'
		. $img_html
		. '<p style="font-size:16px;line-height:1.6;">' . esc_html( $excerpt ) . '</p>'
		. '<p><a href="' . esc_url( $permalink ) . '" style="background:#d11313;color:#fff;padding:12px 24px;text-decoration:none;border-radius:4px;display:inline-block;">Lire le bulletin complet</a></p>'
		. '<hr style="margin-top:40px;border:none;border-top:1px solid #eee;">'
		. '<p style="font-size:12px;color:#888;">Fédération CGT des Sociétés d\'Études</p>'
		. '</body></html>';

	$sender_name  = get_option( 'cgt_brevo_sender_name', get_bloginfo( 'name' ) );
	$sender_email = get_option( 'cgt_brevo_sender_email', 'fsetud@cgt.fr' );

	$payload = array(
		'name'         => 'Bulletin – ' . $title . ' – ' . current_time( 'd/m/Y' ),
		'subject'      => $title,
		'sender'       => array(
			'name'  => $sender_name,
			'email' => $sender_email,
		),
		'recipients'   => array(
			'listIds' => array( (int) $list_id ),
		),
		'htmlContent'  => $html_content,
		'scheduledAt'  => null,
	);

	$response = wp_remote_post(
		'https://api.brevo.com/v3/emailCampaigns',
		array(
			'headers' => cgt_brevo_headers(),
			'body'    => wp_json_encode( $payload ),
			'timeout' => 20,
		)
	);

	if ( is_wp_error( $response ) ) {
		return $response;
	}

	$body = json_decode( wp_remote_retrieve_body( $response ), true );
	$code = wp_remote_retrieve_response_code( $response );

	if ( ! in_array( $code, array( 200, 201 ), true ) ) {
		return new WP_Error( 'brevo_api', isset( $body['message'] ) ? $body['message'] : __( 'Erreur lors de la création de la campagne.', 'cgt' ) );
	}

	$campaign_id = isset( $body['id'] ) ? $body['id'] : 0;

	// Envoyer immédiatement la campagne
	if ( $campaign_id ) {
		wp_remote_post(
			'https://api.brevo.com/v3/emailCampaigns/' . $campaign_id . '/sendNow',
			array(
				'headers' => cgt_brevo_headers(),
				'timeout' => 20,
			)
		);
	}

	return array(
		'campaign_id' => $campaign_id,
		'list_name'   => $list_name,
	);
}

// ─────────────────────────────────────────────
// Metabox sur les articles bulletins
// ─────────────────────────────────────────────

add_action( 'add_meta_boxes', 'cgt_brevo_register_metabox' );
function cgt_brevo_register_metabox() {
	global $post;
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

function cgt_brevo_metabox_render( $post ) {
	if ( ! current_user_can( 'edit_post', $post->ID ) ) {
		return;
	}

	$last_list = get_post_meta( $post->ID, '_cgt_brevo_last_list', true );
	$last_date = get_post_meta( $post->ID, '_cgt_brevo_last_sent', true );
	$lists     = cgt_brevo_get_lists();

	echo '<div class="cgt-brevo-metabox">';

	if ( $last_date ) {
		printf(
			'<p><strong>%s</strong><br><span style="color:#555;font-size:12px;">%s — %s</span></p>',
			esc_html__( 'Dernier envoi :', 'cgt' ),
			esc_html( $last_date ),
			esc_html( $last_list ? $last_list : __( 'liste non renseignée', 'cgt' ) )
		);
	}

	echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
	wp_nonce_field( 'cgt_brevo_send', 'cgt_brevo_nonce' );
	echo '<input type="hidden" name="action" value="cgt_brevo_send">';
	echo '<input type="hidden" name="post_id" value="' . esc_attr( $post->ID ) . '">';

	if ( is_wp_error( $lists ) ) {
		echo '<p style="color:#d11313;">' . esc_html( $lists->get_error_message() ) . '</p>';
	} elseif ( ! empty( $lists ) ) {
		echo '<p><label for="cgt_brevo_list_id"><strong>' . esc_html__( 'Liste de diffusion', 'cgt' ) . '</strong></label><br>';
		echo '<select id="cgt_brevo_list_id" name="cgt_brevo_list_id" class="widefat" style="margin-top:4px;">';
		echo '<option value="">' . esc_html__( '— Choisir une liste —', 'cgt' ) . '</option>';
		foreach ( $lists as $list ) {
			printf(
				'<option value="%d" data-name="%s">%s (%d contacts)</option>',
				esc_attr( $list['id'] ),
				esc_attr( $list['name'] ),
				esc_html( $list['name'] ),
				(int) ( isset( $list['uniqueSubscribers'] ) ? $list['uniqueSubscribers'] : 0 )
			);
		}
		echo '</select></p>';
	} else {
		echo '<p>' . esc_html__( 'Aucune liste trouvée dans Brevo.', 'cgt' ) . '</p>';
	}

	echo '<p><button type="submit" class="button button-primary" style="width:100%">';
	echo esc_html__( 'Envoyer le bulletin', 'cgt' );
	echo '</button></p>';

	$log = get_post_meta( $post->ID, '_cgt_brevo_log', true );
	if ( ! empty( $log ) && is_array( $log ) ) {
		echo '<details style="margin-top:8px;"><summary style="cursor:pointer;font-size:12px;">' . esc_html__( 'Historique des envois', 'cgt' ) . '</summary><ul style="font-size:11px;margin:4px 0 0;padding-left:16px;">';
		foreach ( array_reverse( $log ) as $entry ) {
			printf(
				'<li>%s — %s %s</li>',
				esc_html( $entry['date'] ),
				esc_html( $entry['list'] ),
				! empty( $entry['campaign_id'] ) ? '<span style="color:#46b450;">(#' . esc_html( $entry['campaign_id'] ) . ')</span>' : '<span style="color:#d11313;">(erreur)</span>'
			);
		}
		echo '</ul></details>';
	}

	echo '</form></div>';
}

// ─────────────────────────────────────────────
// Handler envoi depuis la metabox
// ─────────────────────────────────────────────

add_action( 'admin_post_cgt_brevo_send', 'cgt_brevo_send_handler' );
function cgt_brevo_send_handler() {
	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_die( esc_html__( 'Accès refusé.', 'cgt' ) );
	}

	check_admin_referer( 'cgt_brevo_send', 'cgt_brevo_nonce' );

	$post_id  = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
	$list_id  = isset( $_POST['cgt_brevo_list_id'] ) ? absint( $_POST['cgt_brevo_list_id'] ) : 0;
	$list_name = isset( $_POST['cgt_brevo_list_name'] ) ? sanitize_text_field( wp_unslash( $_POST['cgt_brevo_list_name'] ) ) : '';

	if ( ! $post_id || 'post' !== get_post_type( $post_id ) ) {
		wp_die( esc_html__( 'Article introuvable.', 'cgt' ) );
	}
	if ( ! $list_id ) {
		wp_safe_redirect( add_query_arg( array( 'brevo' => 'no_list', 'post' => $post_id ), admin_url( 'post.php?action=edit' ) ) );
		exit;
	}

	$result    = cgt_brevo_send_campaign( $post_id, $list_id, $list_name );
	$timestamp = current_time( 'mysql' );

	if ( is_wp_error( $result ) ) {
		$log_entry = array( 'date' => $timestamp, 'list' => $list_name, 'campaign_id' => null, 'error' => $result->get_error_message() );
		$status    = 'error';
	} else {
		$log_entry = array( 'date' => $timestamp, 'list' => $list_name, 'campaign_id' => $result['campaign_id'] );
		$status    = 'sent';
		update_post_meta( $post_id, '_cgt_brevo_last_list', $list_name );
		update_post_meta( $post_id, '_cgt_brevo_last_sent', $timestamp );
	}

	$log   = get_post_meta( $post_id, '_cgt_brevo_log', true );
	$log   = is_array( $log ) ? $log : array();
	$log[] = $log_entry;
	update_post_meta( $post_id, '_cgt_brevo_log', $log );

	wp_safe_redirect( add_query_arg( array( 'brevo' => $status, 'post' => $post_id ), admin_url( 'post.php?action=edit' ) ) );
	exit;
}

// ─────────────────────────────────────────────
// Tableau de bord Brevo (bulletins uniquement)
// ─────────────────────────────────────────────

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

	// Notices
	if ( isset( $_GET['brevo'] ) ) {
		$status = sanitize_key( wp_unslash( $_GET['brevo'] ) );
		if ( 'sent' === $status ) {
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Campagne envoyée avec succès via Brevo !', 'cgt' ) . '</p></div>';
		} elseif ( 'error' === $status ) {
			echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Erreur lors de l\'envoi. Vérifiez l\'historique de l\'article.', 'cgt' ) . '</p></div>';
		} elseif ( 'no_list' === $status ) {
			echo '<div class="notice notice-warning is-dismissible"><p>' . esc_html__( 'Veuillez sélectionner une liste de diffusion.', 'cgt' ) . '</p></div>';
		}
	}

	// Récupérer uniquement les bulletins
	$query = new WP_Query(
		array(
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'posts_per_page' => 30,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'tax_query'      => array(
				array(
					'taxonomy' => 'category',
					'field'    => 'slug',
					'terms'    => 'bulletins',
				),
			),
		)
	);

	// Récupérer les listes Brevo
	$lists = cgt_brevo_get_lists();

	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Envois Brevo — Bulletins', 'cgt' ); ?></h1>
		<p><?php esc_html_e( 'Envoyez les bulletins directement vers vos listes de diffusion Brevo.', 'cgt' ); ?></p>

		<?php if ( is_wp_error( $lists ) ) : ?>
			<div class="notice notice-error">
				<p><strong><?php esc_html_e( 'Erreur API Brevo :', 'cgt' ); ?></strong> <?php echo esc_html( $lists->get_error_message() ); ?></p>
			</div>
		<?php endif; ?>

		<table class="widefat fixed striped">
			<thead>
				<tr>
					<th style="width:35%"><?php esc_html_e( 'Bulletin', 'cgt' ); ?></th>
					<th style="width:100px"><?php esc_html_e( 'Date', 'cgt' ); ?></th>
					<th style="width:20%"><?php esc_html_e( 'Dernier envoi', 'cgt' ); ?></th>
					<th><?php esc_html_e( 'Envoyer vers une liste', 'cgt' ); ?></th>
				</tr>
			</thead>
			<tbody>
			<?php if ( $query->have_posts() ) : ?>
				<?php while ( $query->have_posts() ) : $query->the_post();
					$post_id   = get_the_ID();
					$last_list = get_post_meta( $post_id, '_cgt_brevo_last_list', true );
					$last_sent = get_post_meta( $post_id, '_cgt_brevo_last_sent', true );
					$log       = get_post_meta( $post_id, '_cgt_brevo_log', true );
					?>
					<tr>
						<td>
							<a href="<?php echo esc_url( get_edit_post_link( $post_id ) ); ?>">
								<strong><?php the_title(); ?></strong>
							</a>
						</td>
						<td style="white-space:nowrap;"><?php echo esc_html( get_the_date() ); ?></td>
						<td>
							<?php if ( $last_sent ) : ?>
								<span style="color:#46b450;">&#10003;</span>
								<span style="font-size:12px;"><?php echo esc_html( $last_sent ); ?></span><br>
								<span style="font-size:11px;color:#555;"><?php echo esc_html( $last_list ); ?></span>
							<?php else : ?>
								<span style="color:#999;font-size:12px;"><?php esc_html_e( 'Pas encore envoyé', 'cgt' ); ?></span>
							<?php endif; ?>
							<?php if ( ! empty( $log ) && is_array( $log ) ) : ?>
								<details style="margin-top:4px;">
									<summary style="font-size:11px;cursor:pointer;"><?php esc_html_e( 'Historique', 'cgt' ); ?></summary>
									<ul style="font-size:11px;margin:2px 0;padding-left:14px;">
										<?php foreach ( array_reverse( $log ) as $entry ) : ?>
											<li>
												<?php echo esc_html( $entry['date'] . ' — ' . $entry['list'] ); ?>
												<?php if ( ! empty( $entry['campaign_id'] ) ) : ?>
													<span style="color:#46b450;">(#<?php echo esc_html( $entry['campaign_id'] ); ?>)</span>
												<?php else : ?>
													<span style="color:#d11313;">(<?php echo esc_html( isset( $entry['error'] ) ? $entry['error'] : 'erreur' ); ?>)</span>
												<?php endif; ?>
											</li>
										<?php endforeach; ?>
									</ul>
								</details>
							<?php endif; ?>
						</td>
						<td>
							<?php if ( ! is_wp_error( $lists ) && ! empty( $lists ) ) : ?>
								<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:flex;gap:6px;align-items:center;flex-wrap:wrap;">
									<?php wp_nonce_field( 'cgt_brevo_send', 'cgt_brevo_nonce' ); ?>
									<input type="hidden" name="action" value="cgt_brevo_send">
									<input type="hidden" name="post_id" value="<?php echo esc_attr( $post_id ); ?>">
									<select name="cgt_brevo_list_id" style="flex:1;min-width:160px;" onchange="this.form.querySelector('[name=cgt_brevo_list_name]').value = this.options[this.selectedIndex].dataset.name">
										<option value=""><?php esc_html_e( '— Liste —', 'cgt' ); ?></option>
										<?php foreach ( $lists as $list ) : ?>
											<option value="<?php echo esc_attr( $list['id'] ); ?>" data-name="<?php echo esc_attr( $list['name'] ); ?>">
												<?php echo esc_html( $list['name'] ); ?> (<?php echo (int) ( isset( $list['uniqueSubscribers'] ) ? $list['uniqueSubscribers'] : 0 ); ?>)
											</option>
										<?php endforeach; ?>
									</select>
									<input type="hidden" name="cgt_brevo_list_name" value="">
									<button type="submit" class="button button-primary"><?php esc_html_e( 'Envoyer', 'cgt' ); ?></button>
								</form>
							<?php else : ?>
								<span style="color:#999;font-size:12px;"><?php esc_html_e( 'Listes indisponibles', 'cgt' ); ?></span>
							<?php endif; ?>
						</td>
					</tr>
				<?php endwhile; ?>
			<?php else : ?>
				<tr><td colspan="4" style="text-align:center;padding:20px;">
					<?php esc_html_e( 'Aucun bulletin publié pour le moment.', 'cgt' ); ?>
				</td></tr>
			<?php endif; ?>
			</tbody>
		</table>

		<h2 style="margin-top:30px;"><?php esc_html_e( 'Paramètres expéditeur', 'cgt' ); ?></h2>
		<form method="post" action="options.php">
			<?php settings_fields( 'cgt_brevo_settings' ); ?>
			<table class="form-table">
				<tr>
					<th><?php esc_html_e( 'Nom expéditeur', 'cgt' ); ?></th>
					<td><input type="text" name="cgt_brevo_sender_name" class="regular-text" value="<?php echo esc_attr( get_option( 'cgt_brevo_sender_name', get_bloginfo( 'name' ) ) ); ?>"></td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Email expéditeur', 'cgt' ); ?></th>
					<td><input type="email" name="cgt_brevo_sender_email" class="regular-text" value="<?php echo esc_attr( get_option( 'cgt_brevo_sender_email', 'fsetud@cgt.fr' ) ); ?>"></td>
				</tr>
			</table>
			<?php submit_button( __( 'Enregistrer', 'cgt' ) ); ?>
		</form>
	</div>
	<?php
	wp_reset_postdata();
}

// Enregistrer les options expéditeur
add_action( 'admin_init', 'cgt_brevo_register_settings' );
function cgt_brevo_register_settings() {
	register_setting( 'cgt_brevo_settings', 'cgt_brevo_sender_name', 'sanitize_text_field' );
	register_setting( 'cgt_brevo_settings', 'cgt_brevo_sender_email', 'sanitize_email' );
}
