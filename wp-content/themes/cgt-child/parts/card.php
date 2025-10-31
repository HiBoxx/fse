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

// Limites de caractères
$title_limit       = 60; // Caractères pour le titre
$description_limit = 20; // Mots pour la description

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

// Récupérer et tronquer le titre
$title = get_the_title();
if ( mb_strlen( $title ) > $title_limit ) {
	$title = mb_substr( $title, 0, $title_limit ) . '...';
}

// Récupérer et tronquer l'extrait
$excerpt = get_the_excerpt();
$excerpt_trimmed = wp_trim_words( $excerpt, $description_limit, '...' );
$thumbnail_html  = cgt_child_get_post_thumbnail_html(
	$post_id,
	'medium',
	array(
		'class'   => 'card-image',
		'loading' => 'lazy',
		'alt'     => get_the_title( $post_id ),
	)
);

?>

<article id="post-<?php the_ID(); ?>" <?php post_class( $card_classes ); ?>>
	<?php if ( $permalink ) : ?>
	<a class="card-link" href="<?php echo esc_url( $permalink ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Lire: %s', 'cgt' ), get_the_title() ) ); ?>">
	<?php endif; ?>
		<?php if ( $thumbnail_html ) : ?>
			<div class="card-thumbnail">
				<?php echo $thumbnail_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
		<?php endif; ?>
		<div class="card-content">
			<header class="card-header">
				<span class="card-meta">
					<svg class="card-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
						<rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
						<line x1="16" y1="2" x2="16" y2="6"></line>
						<line x1="8" y1="2" x2="8" y2="6"></line>
						<line x1="3" y1="10" x2="21" y2="10"></line>
					</svg>
					<?php echo esc_html( get_the_date() ); ?>
				</span>
				<h3 class="card-title"><?php echo esc_html( $title ); ?></h3>
			</header>
			<?php if ( 'tract' !== $context ) : ?>
			<div class="card-summary">
				<p><?php echo esc_html( $excerpt_trimmed ); ?></p>
			</div>
			<?php endif; ?>
			<div class="card-footer">
				<span class="card-cta">
					<?php echo esc_html( $cta_label ); ?>
					<svg class="card-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
						<line x1="5" y1="12" x2="19" y2="12"></line>
						<polyline points="12 5 19 12 12 19"></polyline>
					</svg>
				</span>
			</div>
		</div>
	<?php if ( $permalink ) : ?>
	</a>
	<?php endif; ?>
</article>
