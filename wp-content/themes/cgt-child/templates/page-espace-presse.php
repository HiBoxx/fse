<?php
/**
 * Template for the press space.
 *
 * @package CGT_Child
 */

get_header();
?>
<main id="primary" class="site-main container">
	<header class="page-header">
		<h1><?php esc_html_e( 'Espace Presse', 'cgt' ); ?></h1>
		<p><?php esc_html_e( 'Téléchargez logo, dossiers et communiqués en un clic.', 'cgt' ); ?></p>
		<div class="header-actions">
			<a class="btn" href="#"><?php esc_html_e( 'Télécharger le logo', 'cgt' ); ?></a>
			<a class="btn btn-light" href="#"><?php esc_html_e( 'Télécharger le dossier presse', 'cgt' ); ?></a>
		</div>
	</header>

	<section aria-labelledby="section-communiques">
		<h2 id="section-communiques"><?php esc_html_e( 'Derniers communiqués', 'cgt' ); ?></h2>
		<?php
		$communiques = new WP_Query(
			array(
				'post_type'      => 'communiques_de_presse',
				'posts_per_page' => 3,
			)
		);

		if ( $communiques->have_posts() ) :
			?>
			<div class="post-grid">
				<?php
				while ( $communiques->have_posts() ) :
					$communiques->the_post();
					get_template_part( 'parts/card', null, array( 'context' => 'communique' ) );
				endwhile;
				?>
			</div>
			<p><a class="btn" href="<?php echo esc_url( get_post_type_archive_link( 'communiques_de_presse' ) ); ?>"><?php esc_html_e( 'Tous les communiqués', 'cgt' ); ?></a></p>
			<?php
		else :
			?>
			<p><?php esc_html_e( 'Aucun communiqué pour le moment.', 'cgt' ); ?></p>
		<?php endif; ?>
		<?php wp_reset_postdata(); ?>
	</section>

	<section aria-labelledby="section-dossiers">
		<h2 id="section-dossiers"><?php esc_html_e( 'Dossiers de presse', 'cgt' ); ?></h2>
		<?php
		$dossiers = new WP_Query(
			array(
				'post_type'      => 'dossiers_de_presse',
				'posts_per_page' => 3,
			)
		);

		if ( $dossiers->have_posts() ) :
			?>
			<div class="post-grid">
				<?php
				while ( $dossiers->have_posts() ) :
					$dossiers->the_post();
					get_template_part( 'parts/card', null, array( 'context' => 'dossier' ) );
				endwhile;
				?>
			</div>
			<p><a class="btn" href="<?php echo esc_url( get_post_type_archive_link( 'dossiers_de_presse' ) ); ?>"><?php esc_html_e( 'Tous les dossiers', 'cgt' ); ?></a></p>
			<?php
		else :
			?>
			<p><?php esc_html_e( 'Aucun dossier pour le moment.', 'cgt' ); ?></p>
		<?php endif; ?>
		<?php wp_reset_postdata(); ?>
	</section>
</main>
<?php
get_footer();
