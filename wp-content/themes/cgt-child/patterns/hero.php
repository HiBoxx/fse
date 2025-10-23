<?php
/**
 * Hero pattern.
 *
 * @package CGT_Child
 */

if ( ! function_exists( 'register_block_pattern' ) ) {
	return;
}

register_block_pattern(
	'cgt/hero',
	array(
		'title'       => __( 'Accueil – Hero rouge', 'cgt' ),
		'description' => __( 'Section hero rouge avec CTA adhésion.', 'cgt' ),
		'categories'  => array( 'cgt' ),
		'content'     => '
<!-- wp:group {"className":"hero-block","layout":{"type":"constrained"}} -->
<div class="wp-block-group hero-block"><div class="wp-block-group__inner-container">
<!-- wp:heading {"level":1} -->
<h1>' . esc_html__( 'La CGT agit pour les personnels de l’enseignement supérieur', 'cgt' ) . '</h1>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>' . esc_html__( 'Mobilisations, analyses et outils pour défendre nos droits collectifs.', 'cgt' ) . '</p>
<!-- /wp:paragraph -->

<!-- wp:buttons -->
<div class="wp-block-buttons">
<!-- wp:button {"className":"is-style-fill"} -->
<div class="wp-block-button is-style-fill"><a class="wp-block-button__link wp-element-button" href="/adhesion">' . esc_html__( 'Adhérer', 'cgt' ) . '</a></div>
<!-- /wp:button -->

<!-- wp:button {"className":"is-style-outline"} -->
<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="/espace-adherent">' . esc_html__( 'Espace adhérent', 'cgt' ) . '</a></div>
<!-- /wp:button -->
</div>
<!-- /wp:buttons -->

<!-- wp:html -->
<div class="placeholder" aria-hidden="true"></div>
<!-- /wp:html -->
</div></div>
<!-- /wp:group -->
',
	)
);
