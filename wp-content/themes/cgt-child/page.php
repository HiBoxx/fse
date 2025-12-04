<?php
/**
 * Default page template.
 *
 * @package CGT_Child
 */

get_header();
?>

<main id="primary" class="site-main">
	<section class="page-hero">
		<div class="container">
			<h1 class="page-hero__title"><?php single_post_title(); ?></h1>
			<p class="page-hero__intro">
				<?php
				$excerpt = get_post_meta( get_the_ID(), '_yoast_wpseo_metadesc', true );
				if ( ! $excerpt ) {
					$excerpt = get_the_excerpt();
				}
				echo esc_html( $excerpt ? $excerpt : __( 'Informations et ressources de la CGT.', 'cgt' ) );
				?>
			</p>
		</div>
	</section>

	<div class="container page-content">
		<?php
		while ( have_posts() ) :
			the_post();

			the_content();

			if ( comments_open() || get_comments_number() ) {
				comments_template();
			}
		endwhile;
		?>
	</div>
</main>

<?php
get_footer();
