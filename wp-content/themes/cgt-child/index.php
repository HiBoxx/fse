<?php
/**
 * Primary template.
 *
 * @package CGT_Child
 */

get_header();
?>

<main id="primary" class="site-main container">
	<?php if ( have_posts() ) : ?>
		<div class="post-grid">
			<?php
			while ( have_posts() ) :
				the_post();
				get_template_part( 'parts/card', null, array( 'context' => 'loop' ) );
			endwhile;
			?>
		</div>
		<?php the_posts_pagination(); ?>
	<?php else : ?>
		<p><?php esc_html_e( 'Aucun contenu disponible pour le moment.', 'cgt' ); ?></p>
	<?php endif; ?>
</main>

<?php
get_footer();
