<?php
/**
 * Security & compliance tweaks.
 *
 * @package CGT_Child
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'xmlrpc_enabled', '__return_false' );
add_filter( 'the_generator', '__return_empty_string' );

add_filter(
	'login_errors',
	function () {
		return __( 'Identifiants invalides.', 'cgt' );
	}
);

/**
 * Prevent author enumeration.
 */
function cgt_prevent_author_enumeration() {
	if ( is_admin() ) {
		return;
	}

	if ( isset( $_GET['author'] ) ) {
		wp_safe_redirect( home_url() );
		exit;
	}
}
add_action( 'init', 'cgt_prevent_author_enumeration' );

/**
 * Output a placeholder cookie banner.
 */
function cgt_output_cookie_banner() {
	if ( is_admin() ) {
		return;
	}

	?>
	<div class="cookie-banner" role="dialog" aria-live="polite" aria-label="<?php esc_attr_e( 'Bannière cookies', 'cgt' ); ?>">
		<div class="cookie-content">
			<p class="cookie-text">
				<?php esc_html_e( 'Notre site utilise uniquement des cookies internes nécessaires à son bon fonctionnement.', 'cgt' ); ?>
				<br>
				<?php esc_html_e( 'Aucune donnée personnelle n’est vendue ou transmise à des entreprises publicitaires.', 'cgt' ); ?>
			</p>
			<p class="cookie-text cookie-text--highlight">
				<?php esc_html_e( '🔒 La Fédération s’engage à respecter le Règlement Général sur la Protection des Données (RGPD) et le Référentiel Général d’Amélioration de l’Accessibilité (RGAA).', 'cgt' ); ?>
				<br>
				<?php esc_html_e( 'Le site est entièrement accessible, notamment grâce à son assistant vocal pour les personnes en situation de handicap.', 'cgt' ); ?>
			</p>
			<p class="cookie-text">
				<?php esc_html_e( 'Vous pouvez gérer vos préférences à tout moment.', 'cgt' ); ?>
			</p>
			<div class="cookie-actions">
				<button class="btn btn-light cookie-accept" type="button"><?php esc_html_e( 'Accepter', 'cgt' ); ?></button>
				<button class="btn btn-outline cookie-decline" type="button"><?php esc_html_e( 'Refuser', 'cgt' ); ?></button>
				<a class="btn btn-link cookie-more" href="<?php echo esc_url( home_url( '/mentions-legales' ) ); ?>"><?php esc_html_e( 'En savoir plus', 'cgt' ); ?></a>
			</div>
		</div>
	</div>
	<?php
}
add_action( 'wp_footer', 'cgt_output_cookie_banner' );
