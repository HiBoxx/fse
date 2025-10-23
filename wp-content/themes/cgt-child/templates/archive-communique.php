<?php
/**
 * Archive template for communiqués de presse.
 *
 * @package CGT_Child
 */

get_header();
?>
<main id="primary" class="site-main container">
	<header class="archive-header">
		<h1><?php post_type_archive_title(); ?></h1>
		<p><?php esc_html_e( 'Retrouvez l’ensemble des communiqués de presse de la CGT.', 'cgt' ); ?></p>
	</header>

	<form class="filters" method="get">
		<?php
		wp_dropdown_categories(
			array(
				'taxonomy'         => 'branche',
				'name'             => 'branche',
				'show_option_all'  => __( 'Toutes les branches', 'cgt' ),
				'value_field'      => 'slug',
				'selected'         => get_query_var( 'branche' ),
				'option_none_value'=> '',
			)
		);

		wp_dropdown_categories(
			array(
				'taxonomy'         => 'zone_internationale',
				'name'             => 'zone_internationale',
				'show_option_all'  => __( 'Toutes les zones', 'cgt' ),
				'value_field'      => 'slug',
				'selected'         => get_query_var( 'zone_internationale' ),
				'option_none_value'=> '',
			)
		);
		?>
		<button class="btn" type="submit"><?php esc_html_e( 'Filtrer', 'cgt' ); ?></button>
	</form>

	<?php if ( have_posts() ) : ?>
		<div class="post-grid">
			<?php
			while ( have_posts() ) :
				the_post();
				get_template_part( 'parts/card', null, array( 'context' => 'communique' ) );
			endwhile;
			?>
		</div>
		<?php the_posts_pagination(); ?>
	<?php else : ?>
		<p><?php esc_html_e( 'Aucun communiqué pour le moment.', 'cgt' ); ?></p>
	<?php endif; ?>
</main>
<?php
get_footer();
