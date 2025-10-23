<?php
/**
 * Template for the branches page.
 *
 * @package CGT_Child
 */

get_header();

$terms = get_terms(
	array(
		'taxonomy'   => 'branche',
		'hide_empty' => false,
	)
);
?>
<main id="primary" class="site-main container">
	<header class="page-header">
		<h1><?php esc_html_e( 'Les branches de la CGT', 'cgt' ); ?></h1>
		<p><?php esc_html_e( 'Parcourez les branches et les ressources associées.', 'cgt' ); ?></p>
	</header>

	<?php if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) : ?>
		<div class="document-grid">
			<?php foreach ( $terms as $term ) : ?>
				<article class="card">
					<div class="placeholder" aria-hidden="true"></div>
					<h2 class="card-title"><?php echo esc_html( $term->name ); ?></h2>
					<p><?php echo esc_html( $term->description ? $term->description : __( 'Description à venir.', 'cgt' ) ); ?></p>
					<a class="btn" href="<?php echo esc_url( get_term_link( $term ) ); ?>"><?php esc_html_e( 'Voir les contenus', 'cgt' ); ?></a>
				</article>
			<?php endforeach; ?>
		</div>
	<?php else : ?>
		<p><?php esc_html_e( 'Aucune branche disponible pour le moment.', 'cgt' ); ?></p>
	<?php endif; ?>
</main>
<?php
get_footer();
