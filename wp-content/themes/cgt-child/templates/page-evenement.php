<?php
/**
 * Template for individual cgt_agenda events (optional).
 *
 * @package CGT_Child
 */

get_header();

if ( ! is_user_logged_in() || ! function_exists( 'cgt_user_can_read_private' ) || ! cgt_user_can_read_private() ) {
	wp_safe_redirect( wp_login_url( get_permalink() ) );
	exit;
}

$event_date = get_post_meta( get_the_ID(), 'cgt_event_date', true );
$event_addr = get_post_meta( get_the_ID(), 'cgt_event_address', true );
$event_doc  = get_post_meta( get_the_ID(), 'cgt_event_document', true );
?>

<main id="primary" class="site-main event-page">
	<div class="container">
		<article <?php post_class( 'event-card' ); ?>>
			<header class="event-header">
				<h1 class="event-title"><?php the_title(); ?></h1>
				<?php if ( $event_date ) : ?>
					<p class="event-date"><?php echo esc_html( wp_date( 'd F Y H:i', strtotime( $event_date ) ) ); ?></p>
				<?php endif; ?>
			</header>

			<div class="event-content">
				<?php the_content(); ?>
			</div>

			<?php if ( $event_addr ) : ?>
				<section class="event-section">
					<h2><?php esc_html_e( 'Adresse', 'cgt' ); ?></h2>
					<p><?php echo nl2br( esc_html( $event_addr ) ); ?></p>
				</section>
			<?php endif; ?>

			<?php if ( $event_doc ) : ?>
				<section class="event-section">
					<h2><?php esc_html_e( 'Document associé', 'cgt' ); ?></h2>
					<p><a class="btn" href="<?php echo esc_url( wp_get_attachment_url( $event_doc ) ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Télécharger', 'cgt' ); ?></a></p>
				</section>
			<?php endif; ?>
		</article>
	</div>
</main>

<?php
get_footer();
