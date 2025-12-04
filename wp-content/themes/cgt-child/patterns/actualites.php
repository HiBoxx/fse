<?php
/**
 * Actualités pattern.
 *
 * @package CGT_Child
 */

if ( ! function_exists( 'register_block_pattern' ) ) {
	return;
}

register_block_pattern(
	'cgt/actualites',
	array(
		'title'       => __( 'Accueil – Actualités', 'cgt' ),
		'description' => __( 'Bloc listant les derniers communiqués.', 'cgt' ),
		'categories'  => array( 'cgt' ),
		'content'     => '
<!-- wp:group {"layout":{"type":"constrained"}} -->
<div class="wp-block-group"><div class="wp-block-group__inner-container">
<!-- wp:heading {"level":2} -->
<h2>' . esc_html__( 'Actualités syndicales', 'cgt' ) . '</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>' . esc_html__( 'Suivez les dernières prises de position de la fédération.', 'cgt' ) . '</p>
<!-- /wp:paragraph -->

<!-- wp:shortcode -->
[cgt_communiques]
<!-- /wp:shortcode -->
</div></div>
<!-- /wp:group -->
',
	)
);
