<?php
/**
 * Card component.
 *
 * @package CGT_Child
 *
 * @var array $args Arguments passed to the template part.
 */

$context      = isset( $args['context'] ) ? $args['context'] : 'loop';
$post_id      = get_the_ID();
$post_type    = get_post_type( $post_id );
$permalink    = 'single' === $context ? '' : get_permalink( $post_id );
$cta_label    = __( 'Lire la suite', 'cgt' );
$card_classes = array( 'card' );

if ( $post_type ) {
	$card_classes[] = 'card--type-' . sanitize_html_class( $post_type );
}

switch ( $post_type ) {
	case 'tracts':
		$cta_label = __( 'Consulter le tract', 'cgt' );
		break;
	case 'communiques_de_presse':
		$cta_label = __( 'Lire le communiqué', 'cgt' );
		break;
	case 'dossiers_de_presse':
		$cta_label = __( 'Consulter le dossier', 'cgt' );
		break;
}
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( $card_classes ); ?>>
	<?php if ( $permalink ) : ?>
	<a class="card-link" href="<?php echo esc_url( $permalink ); ?>">
	<?php endif; ?>
		<div class="placeholder" aria-hidden="true"></div>
		<header class="card-header">
			<span class="card-meta"><?php echo esc_html( get_the_date() ); ?></span>
			<h3 class="card-title"><?php the_title(); ?></h3>
		</header>
		<?php if ( 'tract' !== $context ) : ?>
		<div class="card-summary">
			<?php the_excerpt(); ?>
		</div>
		<?php endif; ?>
		<span class="card-cta"><?php echo esc_html( $cta_label ); ?></span>
	<?php if ( $permalink ) : ?>
	</a>
	<?php endif; ?>
</article>
