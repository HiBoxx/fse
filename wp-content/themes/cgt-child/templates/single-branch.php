<?php
/**
 * Single template for Branch CPT.
 *
 * @package CGT_Child
 */

get_header();

$post_id  = get_the_ID();
$term_ids = wp_get_post_terms(
	$post_id,
	'branche',
	array(
		'fields' => 'ids',
	)
);
?>
<main id="primary" class="site-main container">
	<article <?php post_class( 'single-branch' ); ?>>
		<header class="single-header">
			<h1><?php the_title(); ?></h1>
			<div class="placeholder" aria-hidden="true"></div>
		</header>
		<div class="single-content">
			<?php the_content(); ?>
		</div>
	</article>

	<?php if ( ! empty( $term_ids ) ) : ?>
		<section aria-labelledby="branch-communiques">
			<h2 id="branch-communiques"><?php esc_html_e( 'Communiqués associés', 'cgt' ); ?></h2>
			<?php
			$related_communiques = new WP_Query(
				array(
					'post_type'      => 'communiques_de_presse',
					'posts_per_page' => 3,
					'tax_query'      => array(
						array(
							'taxonomy' => 'branche',
							'terms'    => $term_ids,
						),
					),
				)
			);

			if ( $related_communiques->have_posts() ) :
				?>
				<div class="post-grid">
					<?php
					while ( $related_communiques->have_posts() ) :
						$related_communiques->the_post();
						get_template_part( 'parts/card', null, array( 'context' => 'communique' ) );
					endwhile;
					?>
				</div>
				<?php
			else :
				?>
				<p><?php esc_html_e( 'Aucun communiqué lié pour le moment.', 'cgt' ); ?></p>
			<?php endif; ?>
			<?php wp_reset_postdata(); ?>
		</section>

		<section aria-labelledby="branch-tracts">
			<h2 id="branch-tracts"><?php esc_html_e( 'Tracts liés', 'cgt' ); ?></h2>
			<?php
			$related_tracts = new WP_Query(
				array(
					'post_type'      => 'tracts',
					'posts_per_page' => 3,
					'meta_query'     => cgt_user_can_read_private()
						? array()
						: array(
							array(
								'key'     => 'cgt_visibilite',
								'value'   => 'prive',
								'compare' => '!=',
							),
						),
					'tax_query'      => array(
						array(
							'taxonomy' => 'branche',
							'terms'    => $term_ids,
						),
					),
				)
			);

			if ( $related_tracts->have_posts() ) :
				?>
				<div class="document-grid">
					<?php
					while ( $related_tracts->have_posts() ) :
						$related_tracts->the_post();
						get_template_part( 'parts/card', null, array( 'context' => 'tract' ) );
					endwhile;
					?>
				</div>
				<?php
			else :
				?>
				<p><?php esc_html_e( 'Aucun tract lié pour le moment.', 'cgt' ); ?></p>
			<?php endif; ?>
			<?php wp_reset_postdata(); ?>
		</section>
	<?php endif; ?>
</main>
<?php
get_footer();
