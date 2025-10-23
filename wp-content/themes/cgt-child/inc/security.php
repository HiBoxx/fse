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
		<p><?php esc_html_e( 'Ce site utilise des cookies techniques pour assurer son bon fonctionnement. Continuer implique votre accord.', 'cgt' ); ?></p>
		<button class="btn btn-light" type="button"><?php esc_html_e( "J'ai compris", 'cgt' ); ?></button>
	</div>
	<?php
}
add_action( 'wp_footer', 'cgt_output_cookie_banner' );
