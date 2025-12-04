<?php
/**
 * Template pour afficher un événement individuel - Design Moderne
 *
 * @package CGT_Child
 */

get_header();

while ( have_posts() ) :
	the_post();

	// Récupérer les métadonnées de l'événement
	$event_date     = get_post_meta( get_the_ID(), 'cgt_event_date', true );
	$event_address  = get_post_meta( get_the_ID(), 'cgt_event_address', true );
	$event_document = get_post_meta( get_the_ID(), 'cgt_event_document', true );

	// Formatter la date
	$event_timestamp = $event_date ? strtotime( $event_date ) : null;
	$is_past_event   = $event_timestamp && $event_timestamp < current_time( 'timestamp' );

	// Jour de la semaine et date
	$event_day_name = $event_timestamp ? date_i18n( 'l', $event_timestamp ) : '';
	$event_day_num  = $event_timestamp ? date_i18n( 'd', $event_timestamp ) : '';
	$event_month    = $event_timestamp ? date_i18n( 'F', $event_timestamp ) : '';
	$event_month_short = $event_timestamp ? date_i18n( 'M', $event_timestamp ) : '';
	$event_year     = $event_timestamp ? date_i18n( 'Y', $event_timestamp ) : '';
	$event_time     = $event_timestamp ? date_i18n( 'H:i', $event_timestamp ) : '';

	// URL du document
	$document_url = $event_document ? wp_get_attachment_url( $event_document ) : '';

	// Google Calendar link
	$gcal_date_start = $event_timestamp ? gmdate( 'Ymd\THis\Z', $event_timestamp ) : '';
	$gcal_date_end   = $event_timestamp ? gmdate( 'Ymd\THis\Z', $event_timestamp + 7200 ) : ''; // +2h
	$gcal_title      = urlencode( get_the_title() );
	$gcal_location   = urlencode( $event_address );
	$gcal_desc       = urlencode( wp_strip_all_tags( get_the_excerpt() ) );
	$gcal_url        = "https://calendar.google.com/calendar/render?action=TEMPLATE&text={$gcal_title}&dates={$gcal_date_start}/{$gcal_date_end}&details={$gcal_desc}&location={$gcal_location}";
	?>

	<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
		<!-- Hero Section with Event Details -->
		<div class="event-hero">
			<div class="event-hero__overlay"></div>
			<div class="event-hero-content">
				<!-- Status Badge -->
				<div class="event-status-badge <?php echo $is_past_event ? 'past' : 'upcoming'; ?>">
					<?php if ( $is_past_event ) : ?>
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
							<circle cx="12" cy="12" r="10"></circle>
							<polyline points="12 6 12 12 16 14"></polyline>
						</svg>
						<?php esc_html_e( 'Événement passé', 'cgt' ); ?>
					<?php else : ?>
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
							<circle cx="12" cy="12" r="10"></circle>
							<polyline points="10 8 16 12 10 16"></polyline>
						</svg>
						<?php esc_html_e( 'À venir', 'cgt' ); ?>
					<?php endif; ?>
				</div>

				<!-- Event Date Display -->
				<?php if ( $event_timestamp ) : ?>
					<div class="event-date-display">
						<div class="event-calendar-icon">
							<div class="calendar-month"><?php echo esc_html( strtoupper( $event_month_short ) ); ?></div>
							<div class="calendar-day"><?php echo esc_html( $event_day_num ); ?></div>
						</div>
						<div class="event-date-details">
							<div class="event-day-name"><?php echo esc_html( $event_day_name ); ?></div>
							<div class="event-full-date">
								<?php echo esc_html( $event_day_num . ' ' . $event_month . ' ' . $event_year ); ?>
							</div>
							<div class="event-time">
								<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
									<circle cx="12" cy="12" r="10"></circle>
									<polyline points="12 6 12 12 16 14"></polyline>
								</svg>
								<?php echo esc_html( $event_time ); ?>
							</div>
						</div>
					</div>
				<?php endif; ?>

				<!-- Event Title -->
				<h1 class="event-title"><?php the_title(); ?></h1>

				<!-- Location -->
				<?php if ( $event_address ) : ?>
					<div class="event-location">
						<span class="location-icon">
							<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
								<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
								<circle cx="12" cy="10" r="3"></circle>
							</svg>
						</span>
						<div class="location-text"><?php echo esc_html( $event_address ); ?></div>
					</div>
				<?php endif; ?>
			</div>
		</div>

		<!-- Event Container -->
		<div class="event-container">
			<!-- Featured Image -->
			<?php
			$featured_html = cgt_child_get_post_thumbnail_html(
				get_the_ID(),
				'large',
				array(
					'class'   => 'event-featured-img',
					'loading' => 'lazy',
					'alt'     => get_the_title(),
				)
			);
			if ( $featured_html ) :
				?>
				<div class="event-featured-image">
					<?php echo $featured_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
			<?php endif; ?>

			<!-- Event Description -->
			<?php if ( get_the_content() ) : ?>
				<div class="event-content-section">
					<h2 class="section-title">
						<span class="section-icon">
							<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
								<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
								<polyline points="14 2 14 8 20 8"></polyline>
								<line x1="16" y1="13" x2="8" y2="13"></line>
								<line x1="16" y1="17" x2="8" y2="17"></line>
								<polyline points="10 9 9 9 8 9"></polyline>
							</svg>
						</span>
						<?php esc_html_e( 'Description de l\'événement', 'cgt' ); ?>
					</h2>
					<div class="event-content">
						<?php the_content(); ?>
					</div>
				</div>
			<?php endif; ?>

			<!-- Event Info Box -->
			<div class="event-info-box">
				<h3 class="info-box-title">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
						<circle cx="12" cy="12" r="10"></circle>
						<line x1="12" y1="16" x2="12" y2="12"></line>
						<line x1="12" y1="8" x2="12.01" y2="8"></line>
					</svg>
					<?php esc_html_e( 'Informations pratiques', 'cgt' ); ?>
				</h3>
				<div class="info-box-grid">
					<?php if ( $event_timestamp ) : ?>
						<div class="info-item">
							<div class="info-label">
								<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
									<rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
									<line x1="16" y1="2" x2="16" y2="6"></line>
									<line x1="8" y1="2" x2="8" y2="6"></line>
									<line x1="3" y1="10" x2="21" y2="10"></line>
								</svg>
								<?php esc_html_e( 'Date et heure', 'cgt' ); ?>
							</div>
							<div class="info-value">
								<?php echo esc_html( $event_day_name . ' ' . $event_day_num . ' ' . $event_month . ' ' . $event_year ); ?>
								<br>
								<?php esc_html_e( 'à', 'cgt' ); ?> <?php echo esc_html( $event_time ); ?>
							</div>
						</div>
					<?php endif; ?>

					<?php if ( $event_address ) : ?>
						<div class="info-item">
							<div class="info-label">
								<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
									<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
									<circle cx="12" cy="10" r="3"></circle>
								</svg>
								<?php esc_html_e( 'Lieu', 'cgt' ); ?>
							</div>
							<div class="info-value"><?php echo esc_html( $event_address ); ?></div>
						</div>
					<?php endif; ?>

					<?php
					// Catégories
					$categories = get_the_terms( get_the_ID(), 'category' );
					if ( $categories && ! is_wp_error( $categories ) ) :
						?>
						<div class="info-item">
							<div class="info-label">
								<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
									<path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path>
									<line x1="7" y1="7" x2="7.01" y2="7"></line>
								</svg>
								<?php esc_html_e( 'Catégorie', 'cgt' ); ?>
							</div>
							<div class="info-value">
								<?php
								$cat_names = array();
								foreach ( $categories as $cat ) {
									$cat_names[] = $cat->name;
								}
								echo esc_html( implode( ', ', $cat_names ) );
								?>
							</div>
						</div>
					<?php endif; ?>

					<?php
					// Branches
					$branches = wp_get_post_terms( get_the_ID(), 'branche' );
					if ( $branches && ! is_wp_error( $branches ) ) :
						?>
						<div class="info-item">
							<div class="info-label">
								<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
									<line x1="8" y1="6" x2="21" y2="6"></line>
									<line x1="8" y1="12" x2="21" y2="12"></line>
									<line x1="8" y1="18" x2="21" y2="18"></line>
									<line x1="3" y1="6" x2="3.01" y2="6"></line>
									<line x1="3" y1="12" x2="3.01" y2="12"></line>
									<line x1="3" y1="18" x2="3.01" y2="18"></line>
								</svg>
								<?php esc_html_e( 'Branche', 'cgt' ); ?>
							</div>
							<div class="info-value">
								<?php
								$branch_names = array();
								foreach ( $branches as $branch ) {
									$branch_names[] = $branch->name;
								}
								echo esc_html( implode( ', ', $branch_names ) );
								?>
							</div>
						</div>
					<?php endif; ?>
				</div>
			</div>

			<!-- Add to Calendar -->
			<?php if ( $event_timestamp && ! $is_past_event ) : ?>
				<div class="event-calendar-section">
					<h3 class="calendar-section-title">
						<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
							<rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
							<line x1="16" y1="2" x2="16" y2="6"></line>
							<line x1="8" y1="2" x2="8" y2="6"></line>
							<line x1="3" y1="10" x2="21" y2="10"></line>
						</svg>
						<?php esc_html_e( 'Ajouter à votre calendrier', 'cgt' ); ?>
					</h3>
					<div class="calendar-buttons">
						<a href="<?php echo esc_url( $gcal_url ); ?>" target="_blank" rel="noopener" class="calendar-btn">
							<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
								<rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
								<line x1="16" y1="2" x2="16" y2="6"></line>
								<line x1="8" y1="2" x2="8" y2="6"></line>
								<line x1="3" y1="10" x2="21" y2="10"></line>
							</svg>
							<?php esc_html_e( 'Google Calendar', 'cgt' ); ?>
						</a>
					</div>
				</div>
			<?php endif; ?>

			<!-- Document Download -->
			<?php if ( $document_url ) : ?>
				<div class="event-document">
					<h3 class="document-title">
						<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
							<path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"></path>
						</svg>
						<?php esc_html_e( 'Document associé', 'cgt' ); ?>
					</h3>
					<a href="<?php echo esc_url( $document_url ); ?>" class="document-download-button" target="_blank" rel="noopener">
						<span class="download-icon">
							<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
								<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
								<polyline points="7 10 12 15 17 10"></polyline>
								<line x1="12" y1="15" x2="12" y2="3"></line>
							</svg>
						</span>
						<span class="download-text"><?php esc_html_e( 'Télécharger le document', 'cgt' ); ?></span>
					</a>
				</div>
			<?php endif; ?>

			<!-- Event Navigation -->
			<nav class="event-navigation">
				<div class="nav-previous">
					<?php
					$prev_post = get_previous_post();
					if ( $prev_post ) :
						$prev_date = get_post_meta( $prev_post->ID, 'cgt_event_date', true );
						$prev_timestamp = $prev_date ? strtotime( $prev_date ) : null;
						?>
						<a href="<?php echo esc_url( get_permalink( $prev_post->ID ) ); ?>" rel="prev">
							<span class="nav-arrow">
								<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
									<polyline points="15 18 9 12 15 6"></polyline>
								</svg>
							</span>
							<div class="nav-content">
								<span class="nav-label"><?php esc_html_e( 'Événement précédent', 'cgt' ); ?></span>
								<span class="nav-title"><?php echo esc_html( $prev_post->post_title ); ?></span>
								<?php if ( $prev_timestamp ) : ?>
									<span class="nav-date"><?php echo esc_html( date_i18n( 'd/m/Y', $prev_timestamp ) ); ?></span>
								<?php endif; ?>
							</div>
						</a>
					<?php endif; ?>
				</div>
				<div class="nav-next">
					<?php
					$next_post = get_next_post();
					if ( $next_post ) :
						$next_date = get_post_meta( $next_post->ID, 'cgt_event_date', true );
						$next_timestamp = $next_date ? strtotime( $next_date ) : null;
						?>
						<a href="<?php echo esc_url( get_permalink( $next_post->ID ) ); ?>" rel="next">
							<div class="nav-content">
								<span class="nav-label"><?php esc_html_e( 'Événement suivant', 'cgt' ); ?></span>
								<span class="nav-title"><?php echo esc_html( $next_post->post_title ); ?></span>
								<?php if ( $next_timestamp ) : ?>
									<span class="nav-date"><?php echo esc_html( date_i18n( 'd/m/Y', $next_timestamp ) ); ?></span>
								<?php endif; ?>
							</div>
							<span class="nav-arrow">
								<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
									<polyline points="9 18 15 12 9 6"></polyline>
								</svg>
							</span>
						</a>
					<?php endif; ?>
				</div>
			</nav>

			<!-- Back to Agenda -->
			<div class="event-back">
				<a href="<?php echo esc_url( get_post_type_archive_link( 'cgt_agenda' ) ); ?>" class="back-button">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
						<polyline points="15 18 9 12 15 6"></polyline>
					</svg>
					<?php esc_html_e( 'Retour à l\'agenda', 'cgt' ); ?>
				</a>
			</div>
		</div>
	</article>

	<?php
endwhile;

get_footer();
