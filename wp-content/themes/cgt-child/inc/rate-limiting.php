<?php
/**
 * Rate Limiting & Anti-Spam Protection
 *
 * @package CGT_Child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Vérifier le rate limiting pour les formulaires
 *
 * @param string $action Identifiant de l'action (ex: 'adhesion', 'contact', 'article').
 * @param int    $limit Nombre maximum de soumissions autorisées.
 * @param int    $time_window Fenêtre de temps en secondes (défaut: 3600 = 1 heure).
 * @return bool True si autorisé, False si limite dépassée.
 */
function cgt_check_rate_limit( $action, $limit = 3, $time_window = 3600 ) {
	$ip = cgt_get_client_ip();
	$transient_key = 'cgt_rate_limit_' . md5( $action . $ip );

	$attempts = get_transient( $transient_key );

	if ( false === $attempts ) {
		// Première tentative
		set_transient( $transient_key, 1, $time_window );
		return true;
	}

	if ( $attempts >= $limit ) {
		// Limite atteinte
		return false;
	}

	// Incrémenter le compteur
	set_transient( $transient_key, $attempts + 1, $time_window );
	return true;
}

/**
 * Obtenir l'adresse IP du client de manière sécurisée
 *
 * ✅ CORRECTION SÉCURITÉ :
 * - Vérifie que la requête vient d'un proxy de confiance avant d'accepter les headers
 * - Prévient l'IP spoofing
 * - Valide les IPs avec filtres stricts
 *
 * @since 1.2.0
 *
 * @return string Adresse IP du client.
 */
function cgt_get_client_ip() {
	// ✅ Liste des plages IP des proxys de confiance (CloudFlare)
	// Source: https://www.cloudflare.com/ips/
	$trusted_proxies = array(
		'173.245.48.0/20',
		'103.21.244.0/22',
		'103.22.200.0/22',
		'103.31.4.0/22',
		'141.101.64.0/18',
		'108.162.192.0/18',
		'190.93.240.0/20',
		'188.114.96.0/20',
		'197.234.240.0/22',
		'198.41.128.0/17',
		'162.158.0.0/15',
		'104.16.0.0/13',
		'104.24.0.0/14',
		'172.64.0.0/13',
		'131.0.72.0/22',
	);

	$remote_addr = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '0.0.0.0';

	// ✅ Vérifier si la requête vient d'un proxy de confiance
	$is_trusted_proxy = false;
	foreach ( $trusted_proxies as $proxy_range ) {
		if ( cgt_ip_in_range( $remote_addr, $proxy_range ) ) {
			$is_trusted_proxy = true;
			break;
		}
	}

	// ✅ Accepter les headers UNIQUEMENT si le proxy est de confiance
	if ( $is_trusted_proxy ) {
		$ip_keys = array(
			'HTTP_CF_CONNECTING_IP', // CloudFlare
			'HTTP_X_FORWARDED_FOR',
			'HTTP_X_REAL_IP',
		);

		foreach ( $ip_keys as $key ) {
			if ( ! empty( $_SERVER[ $key ] ) ) {
				$ip = sanitize_text_field( wp_unslash( $_SERVER[ $key ] ) );

				// Si plusieurs IPs, prendre la première (client réel)
				if ( strpos( $ip, ',' ) !== false ) {
					$ip_array = explode( ',', $ip );
					$ip = trim( $ip_array[0] );
				}

				// ✅ Validation stricte : exclure IPs privées et réservées
				if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) ) {
					return $ip;
				}
			}
		}
	}

	// ✅ Fallback sécurisé : toujours utiliser REMOTE_ADDR si pas de proxy de confiance
	if ( filter_var( $remote_addr, FILTER_VALIDATE_IP ) ) {
		return $remote_addr;
	}

	return '0.0.0.0';
}

/**
 * Vérifier si une IP est dans une plage CIDR
 *
 * @since 1.2.0
 *
 * @param string $ip    Adresse IP à vérifier.
 * @param string $range Plage CIDR (ex: 192.168.1.0/24).
 *
 * @return bool True si l'IP est dans la plage.
 */
function cgt_ip_in_range( $ip, $range ) {
	if ( strpos( $range, '/' ) === false ) {
		return $ip === $range;
	}

	list( $subnet, $bits ) = explode( '/', $range );

	// Convertir en long
	$ip = ip2long( $ip );
	$subnet = ip2long( $subnet );

	if ( false === $ip || false === $subnet ) {
		return false;
	}

	// Créer le masque
	$mask = -1 << ( 32 - (int) $bits );
	$subnet &= $mask;

	return ( $ip & $mask ) === $subnet;
}

/**
 * Vérifier le honeypot (champ caché anti-spam)
 *
 * @param string $field_name Nom du champ honeypot.
 * @return bool True si valide (pas de spam), False si spam détecté.
 */
function cgt_check_honeypot( $field_name = 'cgt_hp_field' ) {
	// Le honeypot doit être vide
	if ( ! empty( $_POST[ $field_name ] ) ) {
		// Spam détecté
		return false;
	}

	return true;
}

/**
 * Ajouter un champ honeypot HTML
 *
 * @param string $field_name Nom du champ (optionnel).
 * @return string HTML du champ honeypot.
 */
function cgt_render_honeypot( $field_name = 'cgt_hp_field' ) {
	ob_start();
	?>
	<div style="position:absolute;left:-9999px;height:0;overflow:hidden;" aria-hidden="true" tabindex="-1">
		<label for="<?php echo esc_attr( $field_name ); ?>">Ne pas remplir ce champ</label>
		<input type="text" name="<?php echo esc_attr( $field_name ); ?>" id="<?php echo esc_attr( $field_name ); ?>" value="" autocomplete="off">
	</div>
	<?php
	return ob_get_clean();
}

/**
 * Enregistrer une tentative de spam
 *
 * @param string $action Type d'action où le spam a été détecté.
 * @param array  $data Données du formulaire (optionnel).
 */
function cgt_log_spam_attempt( $action, $data = array() ) {
	$ip = cgt_get_client_ip();
	$user_agent = ! empty( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : 'unknown';

	// Logger dans les options WordPress (max 100 entrées)
	$spam_log = get_option( 'cgt_spam_log', array() );

	$spam_log[] = array(
		'date'       => current_time( 'mysql' ),
		'ip'         => $ip,
		'user_agent' => $user_agent,
		'action'     => $action,
		'data'       => wp_json_encode( $data ),
	);

	// Garder seulement les 100 dernières entrées
	if ( count( $spam_log ) > 100 ) {
		$spam_log = array_slice( $spam_log, -100 );
	}

	update_option( 'cgt_spam_log', $spam_log, false );
}

/**
 * Bloquer temporairement une IP après trop de tentatives
 *
 * @param string $ip Adresse IP à bloquer.
 * @param int    $duration Durée du blocage en secondes (défaut: 24h).
 */
function cgt_block_ip( $ip, $duration = 86400 ) {
	$blocked_ips = get_option( 'cgt_blocked_ips', array() );

	$blocked_ips[ $ip ] = time() + $duration;

	update_option( 'cgt_blocked_ips', $blocked_ips, false );
}

/**
 * Vérifier si une IP est bloquée
 *
 * @param string $ip Adresse IP à vérifier (optionnel, utilise l'IP actuelle par défaut).
 * @return bool True si bloquée, False sinon.
 */
function cgt_is_ip_blocked( $ip = '' ) {
	if ( empty( $ip ) ) {
		$ip = cgt_get_client_ip();
	}

	$blocked_ips = get_option( 'cgt_blocked_ips', array() );

	if ( ! isset( $blocked_ips[ $ip ] ) ) {
		return false;
	}

	// Vérifier si le blocage a expiré
	if ( $blocked_ips[ $ip ] < time() ) {
		// Supprimer le blocage expiré
		unset( $blocked_ips[ $ip ] );
		update_option( 'cgt_blocked_ips', $blocked_ips, false );
		return false;
	}

	return true;
}

/**
 * Nettoyer les anciens blocages d'IP (appelé quotidiennement via cron)
 */
function cgt_cleanup_blocked_ips() {
	$blocked_ips = get_option( 'cgt_blocked_ips', array() );
	$current_time = time();
	$updated = false;

	foreach ( $blocked_ips as $ip => $expiry ) {
		if ( $expiry < $current_time ) {
			unset( $blocked_ips[ $ip ] );
			$updated = true;
		}
	}

	if ( $updated ) {
		update_option( 'cgt_blocked_ips', $blocked_ips, false );
	}
}

// Planifier le nettoyage quotidien
if ( ! wp_next_scheduled( 'cgt_daily_cleanup' ) ) {
	wp_schedule_event( time(), 'daily', 'cgt_daily_cleanup' );
}
add_action( 'cgt_daily_cleanup', 'cgt_cleanup_blocked_ips' );

/**
 * Bloquer les IPs bloquées dès le début de WordPress
 */
add_action( 'init', 'cgt_check_blocked_ip', 1 );
function cgt_check_blocked_ip() {
	if ( cgt_is_ip_blocked() ) {
		wp_die(
			'<h1>Accès refusé</h1><p>Votre adresse IP a été temporairement bloquée en raison d\'activités suspectes.</p>',
			'Accès refusé',
			array( 'response' => 403 )
		);
	}
}
