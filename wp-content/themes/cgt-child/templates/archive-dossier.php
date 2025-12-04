<?php
/**
 * Archive template for dossiers de presse.
 *
 * @package CGT_Child
 */

get_header();
?>
<main id="primary" class="site-main container">
	<header class="archive-header">
		<h1><?php post_type_archive_title(); ?></h1>
		<p><?php esc_html_e( 'Accédez aux dossiers de presse prêts à être diffusés.', 'cgt' ); ?></p>
	</header>

	<?php if ( have_posts() ) : ?>
		<div class="post-grid">
			<?php
			while ( have_posts() ) :
				the_post();
				get_template_part( 'parts/card', null, array( 'context' => 'dossier' ) );
			endwhile;
			?>
		</div>
		<?php the_posts_pagination(); ?>
	<?php else : ?>
		<p><?php esc_html_e( 'Aucun dossier pour le moment.', 'cgt' ); ?></p>
	<?php endif; ?>
</main>
<?php
get_footer();
