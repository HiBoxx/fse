<?php
/**
 * Single post template.
 *
 * @package CGT_Child
 */

get_header();
?>

<main id="primary" class="site-main">
	<section class="page-hero">
		<div class="container">
			<h1 class="page-hero__title"><?php the_title(); ?></h1>
			<p class="page-hero__intro"><?php echo esc_html( get_the_date() ); ?></p>
		</div>
	</section>

	<div class="container page-content">
		<?php
		while ( have_posts() ) :
			the_post();
			the_content();
			wp_link_pages();

			if ( comments_open() || get_comments_number() ) {
				comments_template();
			}
		endwhile;
		?>
	</div>
</main>

<?php
get_footer();
