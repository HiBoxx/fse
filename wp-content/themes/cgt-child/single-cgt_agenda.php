<?php
/**
 * Template pour afficher un événement individuel
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
	$event_year     = $event_timestamp ? date_i18n( 'Y', $event_timestamp ) : '';
	$event_time     = $event_timestamp ? date_i18n( 'H:i', $event_timestamp ) : '';

	// URL du document
	$document_url = $event_document ? wp_get_attachment_url( $event_document ) : '';
	?>

	<article id="post-<?php the_ID(); ?>" <?php post_class( 'cgt-single-event' ); ?>>
		<div class="event-container">
			<!-- Header avec date prominente -->
			<div class="event-hero">
				<div class="event-hero-content">
					<!-- Badge de statut -->
					<div class="event-status-badge <?php echo $is_past_event ? 'past' : 'upcoming'; ?>">
						<?php echo $is_past_event ? '📅 Événement passé' : '🔴 À venir'; ?>
					</div>

					<!-- Grande date -->
					<?php if ( $event_timestamp ) : ?>
						<div class="event-date-display">
							<div class="event-calendar-icon">
								<div class="calendar-month"><?php echo esc_html( strtoupper( substr( $event_month, 0, 3 ) ) ); ?></div>
								<div class="calendar-day"><?php echo esc_html( $event_day_num ); ?></div>
							</div>
							<div class="event-date-details">
								<div class="event-day-name"><?php echo esc_html( $event_day_name ); ?></div>
								<div class="event-full-date">
									<?php echo esc_html( $event_day_num . ' ' . $event_month . ' ' . $event_year ); ?>
								</div>
								<div class="event-time">🕐 <?php echo esc_html( $event_time ); ?></div>
							</div>
						</div>
					<?php endif; ?>

					<!-- Titre -->
					<h1 class="event-title"><?php the_title(); ?></h1>

					<!-- Lieu -->
					<?php if ( $event_address ) : ?>
						<div class="event-location">
							<span class="location-icon">📍</span>
							<div class="location-text"><?php echo nl2br( esc_html( $event_address ) ); ?></div>
						</div>
					<?php endif; ?>
				</div>
			</div>

			<!-- Image mise en avant -->
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

			<!-- Contenu de l'événement -->
			<div class="event-content-section">
				<h2 class="section-title">📝 Description de l'événement</h2>
				<div class="event-content">
					<?php the_content(); ?>
				</div>
			</div>

			<!-- Informations pratiques -->
			<div class="event-info-box">
				<h3 class="info-box-title">ℹ️ Informations pratiques</h3>
				<div class="info-box-grid">
					<?php if ( $event_timestamp ) : ?>
						<div class="info-item">
							<div class="info-label">Date et heure</div>
							<div class="info-value">
								<?php echo esc_html( $event_day_name . ' ' . $event_day_num . ' ' . $event_month . ' ' . $event_year ); ?>
								<br>
								à <?php echo esc_html( $event_time ); ?>
							</div>
						</div>
					<?php endif; ?>

					<?php if ( $event_address ) : ?>
						<div class="info-item">
							<div class="info-label">Lieu</div>
							<div class="info-value"><?php echo nl2br( esc_html( $event_address ) ); ?></div>
						</div>
					<?php endif; ?>

					<?php
					// Catégories
					$categories = get_the_terms( get_the_ID(), 'category' );
					if ( $categories && ! is_wp_error( $categories ) ) :
						?>
						<div class="info-item">
							<div class="info-label">Catégorie</div>
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
							<div class="info-label">Branche</div>
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

			<!-- Document téléchargeable -->
			<?php if ( $document_url ) : ?>
				<div class="event-document">
					<h3 class="document-title">📎 Document associé</h3>
					<a href="<?php echo esc_url( $document_url ); ?>" class="document-download-button" target="_blank" rel="noopener">
						<span class="download-icon">⬇️</span>
						<span class="download-text">Télécharger le document</span>
					</a>
				</div>
			<?php endif; ?>

			<!-- Navigation événements -->
			<nav class="event-navigation">
				<div class="nav-previous">
					<?php
					$prev_post = get_previous_post();
					if ( $prev_post ) :
						$prev_date = get_post_meta( $prev_post->ID, 'cgt_event_date', true );
						$prev_timestamp = $prev_date ? strtotime( $prev_date ) : null;
						?>
						<a href="<?php echo esc_url( get_permalink( $prev_post->ID ) ); ?>" rel="prev">
							<span class="nav-arrow">←</span>
							<div class="nav-content">
								<span class="nav-label">Événement précédent</span>
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
								<span class="nav-label">Événement suivant</span>
								<span class="nav-title"><?php echo esc_html( $next_post->post_title ); ?></span>
								<?php if ( $next_timestamp ) : ?>
									<span class="nav-date"><?php echo esc_html( date_i18n( 'd/m/Y', $next_timestamp ) ); ?></span>
								<?php endif; ?>
							</div>
							<span class="nav-arrow">→</span>
						</a>
					<?php endif; ?>
				</div>
			</nav>

			<!-- Retour à l'agenda -->
			<div class="event-back">
				<a href="<?php echo esc_url( get_post_type_archive_link( 'cgt_agenda' ) ); ?>" class="back-button">
					← Retour à l'agenda
				</a>
			</div>
		</div>
	</article>

	<?php
endwhile;

get_footer();
