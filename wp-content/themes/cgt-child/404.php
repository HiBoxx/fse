<?php
/**
 * 404 template.
 *
 * @package CGT_Child
 */

get_header();
?>

<main id="primary" class="site-main">
	<section class="page-hero">
		<div class="container">
			<h1 class="page-hero__title"><?php esc_html_e( 'Page introuvable', 'cgt' ); ?></h1>
			<p class="page-hero__intro"><?php esc_html_e( 'La page demandée n’existe pas ou plus.', 'cgt' ); ?></p>
		</div>
	</section>

	<div class="container page-content">
		<p><?php esc_html_e( 'Essayez de revenir à la page d’accueil ou utilisez la recherche.', 'cgt' ); ?></p>
		<p>
			<a class="btn" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Retour à l’accueil', 'cgt' ); ?></a>
		</p>
		<?php get_search_form(); ?>
	</div>
</main>

<?php
get_footer();
