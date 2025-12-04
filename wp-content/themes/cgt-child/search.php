<?php
/**
 * Search results template.
 *
 * @package CGT_Child
 */

get_header();

$query = get_search_query();
?>

<main id="primary" class="site-main">
	<section class="page-hero">
		<div class="container">
			<h1 class="page-hero__title">
				<?php
				printf(
					/* translators: %s: search query. */
					esc_html__( 'Résultats pour « %s »', 'cgt' ),
					esc_html( $query )
				);
				?>
			</h1>
			<p class="page-hero__intro"><?php esc_html_e( 'Voici les contenus correspondant à votre recherche.', 'cgt' ); ?></p>
		</div>
	</section>

	<div class="container page-content">
		<?php if ( have_posts() ) : ?>
			<div class="post-grid">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'parts/card', null, array( 'context' => 'search' ) );
				endwhile;
				?>
			</div>
			<?php the_posts_pagination(); ?>
		<?php else : ?>
			<p><?php esc_html_e( 'Aucun résultat trouvé. Essayez une autre recherche.', 'cgt' ); ?></p>
		<?php endif; ?>
	</div>
</main>

<?php
get_footer();
