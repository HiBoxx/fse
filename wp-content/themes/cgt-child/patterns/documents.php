<?php
/**
 * Documents pattern.
 *
 * @package CGT_Child
 */

if ( ! function_exists( 'register_block_pattern' ) ) {
	return;
}

register_block_pattern(
	'cgt/documents',
	array(
		'title'       => __( 'Accueil – Documents', 'cgt' ),
		'description' => __( 'Grille de documents à télécharger.', 'cgt' ),
		'categories'  => array( 'cgt' ),
		'content'     => '
<!-- wp:group {"layout":{"type":"constrained"}} -->
<div class="wp-block-group"><div class="wp-block-group__inner-container">
<!-- wp:heading {"level":2} -->
<h2>' . esc_html__( 'Documents à télécharger', 'cgt' ) . '</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>' . esc_html__( 'Tracts, analyses et outils mobilisables immédiatement.', 'cgt' ) . '</p>
<!-- /wp:paragraph -->

<!-- wp:shortcode -->
[cgt_tracts]
<!-- /wp:shortcode -->
</div></div>
<!-- /wp:group -->
',
	)
);
