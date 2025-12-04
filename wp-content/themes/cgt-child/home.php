<?php
/**
 * Posts index template.
 *
 * @package CGT_Child
 */

get_header();
?>

<main id="primary" class="site-main">
	<section class="page-hero">
		<div class="container">
			<h1 class="page-hero__title"><?php bloginfo( 'name' ); ?></h1>
			<p class="page-hero__intro"><?php bloginfo( 'description' ); ?></p>
		</div>
	</section>

	<div class="container page-content">
		<?php if ( have_posts() ) : ?>
			<div class="post-grid">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'parts/card', null, array( 'context' => 'blog' ) );
				endwhile;
				?>
			</div>
			<?php the_posts_pagination(); ?>
		<?php else : ?>
			<p><?php esc_html_e( 'Aucun article publié à ce jour.', 'cgt' ); ?></p>
		<?php endif; ?>
	</div>
</main>

<?php
get_footer();
