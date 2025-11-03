<?php
/**
 * Template for individual cgt_agenda events (optional).
 *
 * @package CGT_Child
 */

get_header();

$event_date = get_post_meta( get_the_ID(), 'cgt_event_date', true );
$event_addr = get_post_meta( get_the_ID(), 'cgt_event_address', true );
$event_doc  = get_post_meta( get_the_ID(), 'cgt_event_document', true );
$event_doc_url = $event_doc ? wp_get_attachment_url( $event_doc ) : '';
$event_doc_title = $event_doc ? get_the_title( $event_doc ) : '';

$event_datetime   = null;
$event_timestamp  = null;
$event_date_label = '';
$event_time_label = '';
$event_iso        = '';

if ( $event_date ) {
	$event_datetime = date_create_from_format( 'Y-m-d H:i:s', $event_date, new DateTimeZone( 'UTC' ) );
	if ( $event_datetime ) {
		$event_datetime->setTimezone( wp_timezone() );
		$event_timestamp  = $event_datetime->getTimestamp();
		$event_date_label = wp_date( 'l j F Y', $event_timestamp );
		$event_time_label = wp_date( 'H\hi', $event_timestamp );
		$event_iso        = $event_datetime->format( DATE_ATOM );
	}
}

$event_terms = get_the_terms( get_the_ID(), 'branche' );
$event_excerpt = get_the_excerpt();

$back_link = apply_filters( 'cgt_agenda_back_link', home_url( '/' ) );

$share_url   = rawurlencode( get_permalink() );
$share_title = rawurlencode( wp_strip_all_tags( get_the_title() ) );
$share_mailto = 'mailto:?subject=' . rawurlencode( sprintf( __( 'Invitation : %s', 'cgt' ), get_the_title() ) ) . '&body=' . rawurlencode( get_permalink() );
$share_twitter = 'https://twitter.com/intent/tweet?text=' . $share_title . '&url=' . $share_url;
$share_facebook = 'https://www.facebook.com/sharer/sharer.php?u=' . $share_url;
?>

<main id="primary" class="site-main event-page">
	<section class="event-hero">
		<div class="container event-hero__inner">
			<span class="event-hero__eyebrow"><?php esc_html_e( 'Agenda syndical', 'cgt' ); ?></span>
			<h1 class="event-hero__title"><?php the_title(); ?></h1>

			<div class="event-hero__meta">
				<?php if ( $event_date_label ) : ?>
					<div class="event-meta__item">
						<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
							<rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
							<line x1="16" y1="2" x2="16" y2="6"></line>
							<line x1="8" y1="2" x2="8" y2="6"></line>
							<line x1="3" y1="10" x2="21" y2="10"></line>
						</svg>
						<time datetime="<?php echo esc_attr( $event_iso ); ?>">
							<span><?php echo esc_html( $event_date_label ); ?></span>
							<?php if ( $event_time_label ) : ?>
								<span><?php echo esc_html( sprintf( __( 'à %s', 'cgt' ), $event_time_label ) ); ?></span>
							<?php endif; ?>
						</time>
					</div>
				<?php endif; ?>

				<?php if ( $event_addr ) : ?>
					<div class="event-meta__item">
						<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
							<path d="M21 10c0 6-9 13-9 13s-9-7-9-13a9 9 0 1 1 18 0z"></path>
							<circle cx="12" cy="10" r="3"></circle>
						</svg>
						<span><?php echo nl2br( esc_html( $event_addr ) ); ?></span>
					</div>
				<?php endif; ?>

				<?php if ( $event_terms && ! is_wp_error( $event_terms ) ) : ?>
					<div class="event-meta__item event-meta__item--tags" aria-label="<?php esc_attr_e( 'Branches concernées', 'cgt' ); ?>">
						<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
							<path d="M20.59 13.41l-7.17 7.18a2 2 0 0 1-2.83 0L3 13.41V4a2 2 0 0 1 2-2h9.41l6.18 6.18a2 2 0 0 1 0 2.83z"></path>
							<line x1="7" y1="7" x2="7.01" y2="7"></line>
						</svg>
						<ul class="event-tags">
							<?php foreach ( $event_terms as $term ) : ?>
								<li><?php echo esc_html( $term->name ); ?></li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</section>

	<div class="container event-layout">
		<article <?php post_class( 'event-article' ); ?>>
			<div class="event-content">
				<?php the_content(); ?>
			</div>

			<?php if ( $event_doc_url ) : ?>
				<section class="event-callout">
					<h2><?php esc_html_e( 'Document associé', 'cgt' ); ?></h2>
					<p><?php esc_html_e( 'Téléchargez la note ou le matériel de communication lié à cet événement.', 'cgt' ); ?></p>
					<a class="btn btn-light" href="<?php echo esc_url( $event_doc_url ); ?>" target="_blank" rel="noopener">
						<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
							<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
							<polyline points="7 10 12 15 17 10"></polyline>
							<line x1="12" y1="15" x2="12" y2="3"></line>
						</svg>
						<?php echo esc_html( $event_doc_title ? $event_doc_title : __( 'Télécharger le document', 'cgt' ) ); ?>
					</a>
				</section>
			<?php endif; ?>
		</article>

		<aside class="event-sidebar" aria-labelledby="event-sidebar-title">
			<section class="event-panel event-panel--info">
				<h2 id="event-sidebar-title"><?php esc_html_e( 'Infos pratiques', 'cgt' ); ?></h2>
				<ul class="event-info-list">
					<?php if ( $event_date_label ) : ?>
						<li>
							<strong><?php esc_html_e( 'Date', 'cgt' ); ?></strong>
							<time datetime="<?php echo esc_attr( $event_iso ); ?>">
								<?php echo esc_html( $event_date_label ); ?>
								<?php if ( $event_time_label ) : ?>
									<br><span><?php echo esc_html( sprintf( __( 'Début à %s', 'cgt' ), $event_time_label ) ); ?></span>
								<?php endif; ?>
							</time>
						</li>
					<?php endif; ?>

					<?php if ( $event_addr ) : ?>
						<li>
							<strong><?php esc_html_e( 'Lieu', 'cgt' ); ?></strong>
							<p><?php echo nl2br( esc_html( $event_addr ) ); ?></p>
						</li>
					<?php endif; ?>

					<?php if ( $event_terms && ! is_wp_error( $event_terms ) ) : ?>
						<li>
							<strong><?php esc_html_e( 'Branches concernées', 'cgt' ); ?></strong>
							<ul class="event-branch-list">
								<?php foreach ( $event_terms as $term ) : ?>
									<li><?php echo esc_html( $term->name ); ?></li>
								<?php endforeach; ?>
							</ul>
						</li>
					<?php endif; ?>
				</ul>
			</section>

			<section class="event-panel event-panel--share">
				<h2><?php esc_html_e( 'Partager', 'cgt' ); ?></h2>
				<ul class="event-share-list">
					<li><a href="<?php echo esc_url( $share_mailto ); ?>" class="event-share__link"><?php esc_html_e( 'Par e-mail', 'cgt' ); ?></a></li>
					<li><a href="<?php echo esc_url( $share_facebook ); ?>" class="event-share__link" target="_blank" rel="noopener"><?php esc_html_e( 'Sur Facebook', 'cgt' ); ?></a></li>
					<li><a href="<?php echo esc_url( $share_twitter ); ?>" class="event-share__link" target="_blank" rel="noopener"><?php esc_html_e( 'Sur X/Twitter', 'cgt' ); ?></a></li>
				</ul>
			</section>

			<section class="event-panel event-panel--contact">
				<h2><?php esc_html_e( 'Besoin d’un contact ?', 'cgt' ); ?></h2>
				<p><?php esc_html_e( 'Pour toute question sur l’organisation ou la logistique, contactez la fédération.', 'cgt' ); ?></p>
				<a class="btn" href="<?php echo esc_url( home_url( '/contact' ) ); ?>"><?php esc_html_e( 'Nous écrire', 'cgt' ); ?></a>
			</section>
		</aside>
	</div>

	<?php
	$related_events = new WP_Query(
		array(
			'post_type'      => 'cgt_agenda',
			'post_status'    => 'publish',
			'posts_per_page' => 3,
			'post__not_in'   => array( get_the_ID() ),
			'meta_key'       => 'cgt_event_date',
			'orderby'        => 'meta_value',
			'order'          => 'ASC',
			'meta_query'     => array(
				array(
					'key'     => 'cgt_event_date',
					'value'   => gmdate( 'Y-m-d H:i:s' ),
					'compare' => '>=',
					'type'    => 'DATETIME',
				),
			),
		)
	);

	if ( ! $related_events->have_posts() ) {
		wp_reset_postdata();
		$related_events = new WP_Query(
			array(
				'post_type'      => 'cgt_agenda',
				'post_status'    => 'publish',
				'posts_per_page' => 3,
				'post__not_in'   => array( get_the_ID() ),
				'meta_key'       => 'cgt_event_date',
				'orderby'        => 'meta_value',
				'order'          => 'DESC',
			)
		);
	}

	if ( $related_events->have_posts() ) :
		?>
		<section class="event-related">
			<div class="container">
				<header class="event-related__header">
					<h2><?php esc_html_e( 'Autres événements à venir', 'cgt' ); ?></h2>
					<p><?php esc_html_e( 'Restez informé·e de la vie syndicale et des prochaines mobilisations.', 'cgt' ); ?></p>
				</header>

				<ul class="event-related__list">
					<?php
					while ( $related_events->have_posts() ) :
						$related_events->the_post();
						$related_date = get_post_meta( get_the_ID(), 'cgt_event_date', true );
						$related_label = '';
						if ( $related_date ) {
							$related_dt = date_create_from_format( 'Y-m-d H:i:s', $related_date, new DateTimeZone( 'UTC' ) );
							if ( $related_dt ) {
								$related_dt->setTimezone( wp_timezone() );
								$related_label = wp_date( 'd M Y', $related_dt->getTimestamp() );
							}
						}
						?>
						<li class="event-related__item">
							<a href="<?php the_permalink(); ?>" class="event-related__link">
								<span class="event-related__date"><?php echo esc_html( $related_label ); ?></span>
								<span class="event-related__title"><?php the_title(); ?></span>
								<span class="event-related__cta"><?php esc_html_e( 'Découvrir', 'cgt' ); ?></span>
							</a>
						</li>
					<?php endwhile; ?>
				</ul>
			</div>
		</section>
		<?php
	endif;
	wp_reset_postdata();
	?>
</main>

<?php
get_footer();
