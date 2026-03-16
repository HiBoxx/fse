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

	$limit      = 50;
	$offset     = 0;
	$page_count = 0;
	$max_pages  = 100;
	$lists      = array();

	while ( $page_count < $max_pages ) {
		$response = wp_remote_get(
			'https://api.brevo.com/v3/contacts/lists?limit=' . $limit . '&offset=' . $offset,
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

		$chunk = ( isset( $body['lists'] ) && is_array( $body['lists'] ) ) ? $body['lists'] : array();
		$lists = array_merge( $lists, $chunk );

		$retrieved = count( $chunk );
		$total     = isset( $body['count'] ) ? absint( $body['count'] ) : 0;

		if ( $retrieved < $limit || ( $total > 0 && count( $lists ) >= $total ) ) {
			break;
		}

		$offset += $limit;
		++$page_count;
	}

	set_transient( 'cgt_brevo_lists', $lists, 5 * MINUTE_IN_SECONDS );

	return $lists;
}

/**
 * Purge manuelle du cache des listes Brevo depuis le dashboard.
 */
add_action(
	'admin_init',
	function () {
		if ( ! is_admin() || ! current_user_can( 'edit_posts' ) ) {
			return;
		}

		$page   = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';
		$action = isset( $_GET['cgt_brevo_action'] ) ? sanitize_key( wp_unslash( $_GET['cgt_brevo_action'] ) ) : '';
		$nonce  = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';

		if ( 'cgt-brevo-dashboard' !== $page || 'refresh_lists' !== $action ) {
			return;
		}

		if ( ! wp_verify_nonce( $nonce, 'cgt_brevo_refresh_lists' ) ) {
			wp_die( esc_html__( 'Action non autorisée.', 'cgt' ) );
		}

		delete_transient( 'cgt_brevo_lists' );

		wp_safe_redirect(
			add_query_arg(
				array(
					'page'           => 'cgt-brevo-dashboard',
					'brevo_refreshed'=> '1',
				),
				admin_url( 'admin.php' )
			)
		);
		exit;
	}
);

/**
 * Retourne tous les IDs de listes Brevo liés à la branche d'un article et ses sous-branches.
 *
 * @param int $post_id ID du post.
 * @return int[]
 */
function cgt_brevo_get_post_branch_list_ids( $post_id ) {
	$terms = wp_get_post_terms( $post_id, 'branche' );
	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return array();
	}

	$term_ids = array();
	foreach ( $terms as $term ) {
		$term_ids[] = (int) $term->term_id;
		$children   = get_term_children( $term->term_id, 'branche' );
		if ( ! is_wp_error( $children ) && ! empty( $children ) ) {
			foreach ( $children as $child_id ) {
				$term_ids[] = (int) $child_id;
			}
		}
	}

	$term_ids = array_values( array_unique( array_filter( $term_ids ) ) );
	$list_ids = array();

	foreach ( $term_ids as $term_id ) {
		$raw_value = (string) get_term_meta( $term_id, 'branche_id_brevo', true );
		if ( '' === trim( $raw_value ) ) {
			continue;
		}

		$parts = preg_split( '/[\s,;]+/', $raw_value );
		if ( ! is_array( $parts ) ) {
			continue;
		}

		foreach ( $parts as $part ) {
			$list_id = absint( $part );
			if ( $list_id > 0 ) {
				$list_ids[] = $list_id;
			}
		}
	}

	return array_values( array_unique( $list_ids ) );
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
	$list_ids  = is_array( $list_id ) ? array_map( 'absint', $list_id ) : array( absint( $list_id ) );
	$list_ids  = array_values( array_unique( array_filter( $list_ids ) ) );
	$template_id = absint( get_option( 'cgt_brevo_template_id', 0 ) );

	if ( empty( $list_ids ) ) {
		return new WP_Error( 'brevo_no_list', __( 'Aucune liste Brevo sélectionnée.', 'cgt' ) );
	}

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
			'listIds' => $list_ids,
		),
	);

	if ( $template_id > 0 ) {
		$payload['templateId'] = $template_id;
		$payload['params']     = array(
			'POST_TITLE'   => $title,
			'POST_URL'     => $permalink,
			'POST_EXCERPT' => $excerpt,
			'POST_IMAGE'   => $thumbnail,
		);
	} else {
		$payload['htmlContent'] = $html_content;
	}

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
		$send_response = wp_remote_post(
			'https://api.brevo.com/v3/emailCampaigns/' . $campaign_id . '/sendNow',
			array(
				'headers' => cgt_brevo_headers(),
				'timeout' => 20,
			)
		);

		if ( is_wp_error( $send_response ) ) {
			return $send_response;
		}

		$send_code = wp_remote_retrieve_response_code( $send_response );
		if ( ! in_array( $send_code, array( 200, 201, 202, 204 ), true ) ) {
			$send_body = json_decode( wp_remote_retrieve_body( $send_response ), true );
			return new WP_Error( 'brevo_send', isset( $send_body['message'] ) ? $send_body['message'] : __( 'Erreur lors de l\'envoi de la campagne.', 'cgt' ) );
		}
	}

	return array(
		'campaign_id' => $campaign_id,
		'list_name'   => $list_name,
		'list_ids'    => $list_ids,
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
		echo '<select id="cgt_brevo_list_id" name="cgt_brevo_list_id" class="widefat" style="margin-top:4px;" onchange="this.form.querySelector(\'[name=cgt_brevo_list_name]\').value = this.options[this.selectedIndex].dataset.name">';
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
			echo '<input type="hidden" name="cgt_brevo_list_name" value="">';
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
	if ( ! has_category( 'bulletins', $post_id ) ) {
		wp_die( esc_html__( 'Cet article n\'est pas un bulletin.', 'cgt' ) );
	}
	if ( ! $list_id ) {
		wp_safe_redirect( add_query_arg( array( 'brevo' => 'no_list', 'post' => $post_id ), admin_url( 'post.php?action=edit' ) ) );
		exit;
	}

	if ( '' === $list_name ) {
		$lists = cgt_brevo_get_lists();
		if ( ! is_wp_error( $lists ) ) {
			foreach ( $lists as $list ) {
				if ( isset( $list['id'] ) && (int) $list['id'] === $list_id ) {
					$list_name = isset( $list['name'] ) ? (string) $list['name'] : '';
					break;
				}
			}
		}
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

/**
 * Envoi automatique Brevo lors de la première publication d'un bulletin.
 */
add_action( 'transition_post_status', 'cgt_brevo_auto_send_on_publish', 10, 3 );
function cgt_brevo_auto_send_on_publish( $new_status, $old_status, $post ) {
	if ( 'publish' !== $new_status || 'publish' === $old_status ) {
		return;
	}

	if ( ! $post instanceof WP_Post || 'post' !== $post->post_type ) {
		return;
	}

	if ( ! has_category( 'bulletins', $post ) ) {
		return;
	}

	if ( '0' === (string) get_option( 'cgt_brevo_auto_send_enabled', '1' ) ) {
		return;
	}

	$already_sent = get_post_meta( $post->ID, '_cgt_brevo_auto_sent', true );
	if ( ! empty( $already_sent ) ) {
		return;
	}

	$list_ids  = cgt_brevo_get_post_branch_list_ids( $post->ID );
	$timestamp = current_time( 'mysql' );
	$log       = get_post_meta( $post->ID, '_cgt_brevo_log', true );
	$log       = is_array( $log ) ? $log : array();

	if ( empty( $list_ids ) ) {
		$log[] = array(
			'date'        => $timestamp,
			'list'        => __( 'Aucune liste branche/sous-branche trouvée', 'cgt' ),
			'campaign_id' => null,
			'error'       => __( 'Aucun ID Brevo défini sur la branche et ses sous-branches.', 'cgt' ),
			'trigger'     => 'auto',
		);
		update_post_meta( $post->ID, '_cgt_brevo_log', $log );
		return;
	}

	$result = cgt_brevo_send_campaign( $post->ID, $list_ids, __( 'Auto (branche + sous-branches)', 'cgt' ) );
	if ( is_wp_error( $result ) ) {
		$log[] = array(
			'date'        => $timestamp,
			'list'        => __( 'Auto (branche + sous-branches)', 'cgt' ),
			'campaign_id' => null,
			'error'       => $result->get_error_message(),
			'trigger'     => 'auto',
		);
	} else {
		$list_label = sprintf(
			/* translators: %s: comma-separated list ids */
			__( 'Auto IDs: %s', 'cgt' ),
			implode( ', ', array_map( 'strval', $result['list_ids'] ) )
		);

		$log[] = array(
			'date'        => $timestamp,
			'list'        => $list_label,
			'campaign_id' => $result['campaign_id'],
			'trigger'     => 'auto',
		);

		update_post_meta( $post->ID, '_cgt_brevo_last_list', $list_label );
		update_post_meta( $post->ID, '_cgt_brevo_last_sent', $timestamp );
		update_post_meta( $post->ID, '_cgt_brevo_auto_sent', $timestamp );
	}

	update_post_meta( $post->ID, '_cgt_brevo_log', $log );
}

// ─────────────────────────────────────────────
// Tableau de bord Brevo (bulletins uniquement)
// ─────────────────────────────────────────────

add_action( 'admin_menu', 'cgt_register_brevo_dashboard', 20 );
function cgt_register_brevo_dashboard() {
	add_menu_page(
		__( 'Brevo', 'cgt' ),
		__( 'Brevo', 'cgt' ),
		'edit_posts',
		'cgt-brevo-dashboard',
		'cgt_render_brevo_dashboard',
		'dashicons-megaphone',
		24
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

	if ( isset( $_GET['brevo_refreshed'] ) && '1' === sanitize_text_field( wp_unslash( $_GET['brevo_refreshed'] ) ) ) {
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Cache des listes Brevo rafraîchi.', 'cgt' ) . '</p></div>';
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
		<p>
			<a class="button" href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'page' => 'cgt-brevo-dashboard', 'cgt_brevo_action' => 'refresh_lists' ), admin_url( 'admin.php' ) ), 'cgt_brevo_refresh_lists' ) ); ?>">
				<?php esc_html_e( 'Rafraîchir les listes Brevo', 'cgt' ); ?>
			</a>
		</p>

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
					<tr>
						<th><?php esc_html_e( 'Template ID Brevo', 'cgt' ); ?></th>
						<td>
							<input type="number" min="0" step="1" name="cgt_brevo_template_id" class="small-text" value="<?php echo esc_attr( (string) absint( get_option( 'cgt_brevo_template_id', 0 ) ) ); ?>">
							<p class="description"><?php esc_html_e( 'Utilisé pour les campagnes. Si vide/0, le HTML généré par le site est utilisé.', 'cgt' ); ?></p>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Envoi auto à la publication', 'cgt' ); ?></th>
						<td>
							<label>
								<input type="hidden" name="cgt_brevo_auto_send_enabled" value="0">
								<input type="checkbox" name="cgt_brevo_auto_send_enabled" value="1" <?php checked( (string) get_option( 'cgt_brevo_auto_send_enabled', '1' ), '1' ); ?>>
								<?php esc_html_e( 'Envoyer automatiquement les bulletins publiés vers les listes Brevo liées à la branche + sous-branches.', 'cgt' ); ?>
							</label>
						</td>
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
	register_setting(
		'cgt_brevo_settings',
		'cgt_brevo_template_id',
		function ( $value ) {
			return absint( $value );
		}
	);
	register_setting(
		'cgt_brevo_settings',
		'cgt_brevo_auto_send_enabled',
		function ( $value ) {
			return '1' === (string) $value ? '1' : '0';
		}
	);
}
