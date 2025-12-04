<?php
/**
 * Primary navigation partial.
 *
 * @package CGT_Child
 */
?>

<nav class="primary-nav" aria-label="<?php esc_attr_e( 'Menu principal', 'cgt' ); ?>">
	<?php
	wp_nav_menu(
		array(
			'theme_location'  => 'primary',
			'menu_id'         => 'primary-menu',
			'menu_class'      => 'primary-menu',
			'container'       => false,
			'fallback_cb'     => 'cgt_wp_menu_fallback',
			'depth'           => 2,
			'link_before'     => '<span>',
			'link_after'      => '</span>',
		)
	);
	?>
</nav>
