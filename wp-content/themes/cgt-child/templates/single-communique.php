<?php
/**
 * Single communiqué template.
 *
 * @package CGT_Child
 */

get_header();

$post_id  = get_the_ID();
$chapo    = get_post_meta( $post_id, 'cgt_chapo', true );
$porteur  = get_post_meta( $post_id, 'cgt_porteur', true );
$embargo  = get_post_meta( $post_id, 'cgt_embargo', true );
$is_future_embargo = $embargo && strtotime( $embargo ) > current_time( 'timestamp' );

$schema = array(
	'@context'      => 'https://schema.org',
	'@type'         => 'NewsArticle',
	'headline'      => get_the_title(),
	'datePublished' => get_the_date( 'c' ),
	'dateModified'  => get_the_modified_date( 'c' ),
	'author'        => array(
		'@type' => 'Organization',
		'name'  => __( 'CGT', 'cgt' ),
	),
	'publisher'     => array(
		'@type' => 'Organization',
		'name'  => get_bloginfo( 'name' ),
	),
	'mainEntityOfPage' => get_permalink(),
);
?>
<main id="primary" class="site-main container">
	<article <?php post_class( 'single-communique' ); ?>>
		<header class="single-header">
			<h1><?php the_title(); ?></h1>
			<div class="single-meta">
				<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
				<?php if ( $is_future_embargo ) : ?>
					<span class="badge"><?php esc_html_e( 'Embargo', 'cgt' ); ?></span>
				<?php endif; ?>
				<?php if ( $porteur ) : ?>
					<span><?php echo esc_html( $porteur ); ?></span>
				<?php endif; ?>
			</div>
			<?php if ( $chapo ) : ?>
				<p class="notice"><?php echo esc_html( $chapo ); ?></p>
			<?php endif; ?>
			<div class="placeholder" aria-hidden="true"></div>
		</header>
		<div class="single-content">
			<?php the_content(); ?>
		</div>
		<footer class="single-footer">
			<?php
			$attachments = get_attached_media( 'application/pdf', $post_id );
			if ( $attachments ) :
				?>
				<h2><?php esc_html_e( 'Fichiers à télécharger', 'cgt' ); ?></h2>
				<ul>
					<?php
					foreach ( $attachments as $attachment ) :
						$url = wp_get_attachment_url( $attachment->ID );
						?>
						<li><a class="btn" href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $attachment->post_title ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
			<p><a href="<?php echo esc_url( get_post_type_archive_link( 'communiques_de_presse' ) ); ?>"><?php esc_html_e( 'Retour aux communiqués', 'cgt' ); ?></a></p>
		</footer>
	</article>
</main>
<script type="application/ld+json">
<?php echo wp_json_encode( $schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ); ?>
</script>
<?php
get_footer();
