<?php
/**
 * Generic archive template.
 *
 * @package CGT_Child
 */

get_header();
?>

<main id="primary" class="site-main">
	<section class="page-hero">
		<div class="container">
			<h1 class="page-hero__title"><?php the_archive_title(); ?></h1>
			<p class="page-hero__intro"><?php the_archive_description(); ?></p>
		</div>
	</section>

	<div class="container page-content">
		<?php if ( have_posts() ) : ?>
			<div class="post-grid">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'parts/card', null, array( 'context' => 'archive' ) );
				endwhile;
				?>
			</div>
			<?php the_posts_pagination(); ?>
		<?php else : ?>
			<p><?php esc_html_e( 'Aucun contenu disponible pour le moment.', 'cgt' ); ?></p>
		<?php endif; ?>
	</div>
</main>

<?php
get_footer();
