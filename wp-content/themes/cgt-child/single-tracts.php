<?php
/**
 * Template pour l'affichage d'un tract individuel.
 *
 * @package CGT_Child
 */

get_header();

while ( have_posts() ) :
	the_post();

	$sources = get_post_meta( get_the_ID(), 'cgt_submission_sources', true );
	$pdf_url = get_post_meta( get_the_ID(), 'cgt_fichier_pdf', true );
	?>

	<article id="post-<?php the_ID(); ?>" <?php post_class( 'cgt-single-tract' ); ?>>
		<div class="tract-container">
			<header class="tract-header">
				<div class="tract-meta">
					<?php
					$classes = wp_get_post_terms( get_the_ID(), 'thematique' );
					if ( ! empty( $classes ) && ! is_wp_error( $classes ) ) {
						foreach ( $classes as $class ) {
							printf( '<span class="tract-chip tract-class">%s</span>', esc_html( $class->name ) );
						}
					}

					$branches = wp_get_post_terms( get_the_ID(), 'branche' );
					if ( ! empty( $branches ) && ! is_wp_error( $branches ) ) {
						foreach ( $branches as $branch ) {
							printf( '<span class="tract-chip tract-branch">%s</span>', esc_html( $branch->name ) );
						}
					}
					?>
					<span class="tract-chip tract-date">
						<span aria-hidden="true">📅</span>
						<?php echo esc_html( get_the_date( 'd/m/Y' ) ); ?>
					</span>
				</div>

				<h1 class="tract-title"><?php the_title(); ?></h1>

				<p class="tract-author">
					<?php
					printf(
						/* translators: %s: post author. */
						esc_html__( 'Par %s', 'cgt' ),
						'<span class="author-name">' . esc_html( get_the_author() ) . '</span>'
					);
					?>
				</p>
			</header>

			<?php if ( has_post_thumbnail() ) : ?>
				<div class="tract-featured-image">
					<?php the_post_thumbnail( 'large', array( 'class' => 'featured-img' ) ); ?>
				</div>
			<?php endif; ?>

			<?php if ( has_excerpt() ) : ?>
				<section class="tract-excerpt">
					<div class="excerpt-icon" aria-hidden="true">📝</div>
					<div class="excerpt-content">
						<?php the_excerpt(); ?>
					</div>
				</section>
			<?php endif; ?>

			<section class="tract-content">
				<?php the_content(); ?>
			</section>

			<?php if ( $pdf_url ) : ?>
				<section class="tract-pdf-download">
					<div class="pdf-icon" aria-hidden="true">📄</div>
					<div class="pdf-info">
						<h2 class="pdf-title"><?php esc_html_e( 'Télécharger le tract en PDF', 'cgt' ); ?></h2>
						<p class="pdf-description">
							<?php esc_html_e( 'Téléchargez la version PDF de ce tract pour l’imprimer ou la partager.', 'cgt' ); ?>
						</p>
						<a class="pdf-download-button" href="<?php echo esc_url( $pdf_url ); ?>" download>
							<?php esc_html_e( 'Télécharger le PDF', 'cgt' ); ?>
						</a>
					</div>
				</section>
			<?php endif; ?>

			<?php if ( $sources ) : ?>
				<section class="tract-sources">
					<h2 class="sources-title"><?php esc_html_e( 'Sources et références', 'cgt' ); ?></h2>
					<div class="sources-content">
						<?php echo wp_kses_post( wpautop( esc_html( $sources ) ) ); ?>
					</div>
				</section>
			<?php endif; ?>

			<?php
			$tags = get_the_tags();
			if ( $tags ) :
				?>
				<section class="tract-tags">
					<h2 class="tags-label"><?php esc_html_e( 'Mots-clés', 'cgt' ); ?></h2>
					<ul class="tags-list">
						<?php foreach ( $tags as $tag ) : ?>
							<li><span class="tag"><?php echo esc_html( $tag->name ); ?></span></li>
						<?php endforeach; ?>
					</ul>
				</section>
			<?php endif; ?>

			<nav class="tract-navigation" aria-label="<?php esc_attr_e( 'Navigation des tracts', 'cgt' ); ?>">
				<div class="nav-previous">
					<?php
					$prev_post = get_previous_post();
					if ( $prev_post ) :
						?>
						<a href="<?php echo esc_url( get_permalink( $prev_post->ID ) ); ?>" rel="prev">
							<span aria-hidden="true">←</span>
							<span class="nav-title"><?php echo esc_html( get_the_title( $prev_post->ID ) ); ?></span>
						</a>
					<?php endif; ?>
				</div>
				<div class="nav-next">
					<?php
					$next_post = get_next_post();
					if ( $next_post ) :
						?>
						<a href="<?php echo esc_url( get_permalink( $next_post->ID ) ); ?>" rel="next">
							<span class="nav-title"><?php echo esc_html( get_the_title( $next_post->ID ) ); ?></span>
							<span aria-hidden="true">→</span>
						</a>
					<?php endif; ?>
				</div>
			</nav>

			<p class="tract-back">
				<a class="back-button" href="<?php echo esc_url( home_url( '/' ) ); ?>">
					<span aria-hidden="true">←</span>
					<?php esc_html_e( 'Retour à l’accueil', 'cgt' ); ?>
				</a>
			</p>
		</div>
	</article>

	<style>
		.cgt-single-tract {
			background: #fff;
			max-width: 920px;
			margin: 40px auto;
			box-shadow: var(--shadow);
			border-radius: var(--radius);
		}

		.tract-container {
			padding: 40px;
		}

		.tract-header {
			margin-bottom: 30px;
			padding-bottom: 24px;
			border-bottom: 3px solid var(--cgt-red);
		}

		.tract-meta {
			display: flex;
			flex-wrap: wrap;
			gap: 12px;
			margin-bottom: 18px;
		}

		.tract-chip {
			display: inline-flex;
			align-items: center;
			gap: 6px;
			padding: 6px 14px;
			border-radius: 999px;
			font-size: 13px;
			font-weight: 600;
			background: #f3f4f6;
			color: #374151;
		}

		.tract-class {
			background: #dbeafe;
			color: #1e3a8a;
		}

		.tract-branch {
			background: #fef3c7;
			color: #92400e;
		}

		.tract-title {
			font-size: clamp(2.2rem, 4vw, 2.8rem);
			margin: 0 0 8px;
		}

		.tract-author {
			margin: 0;
			color: rgba(17, 17, 17, 0.65);
			font-weight: 600;
		}

		.tract-featured-image {
			margin: 24px 0;
			border-radius: var(--radius);
			overflow: hidden;
		}

		.tract-featured-image img {
			width: 100%;
			display: block;
		}

		.tract-excerpt {
			display: grid;
			grid-template-columns: auto 1fr;
			gap: 16px;
			background: rgba(229, 231, 235, 0.35);
			border-radius: var(--radius);
			padding: 20px;
			margin-bottom: 28px;
		}

		.excerpt-icon {
			font-size: 1.8rem;
		}

		.tract-content {
			display: grid;
			gap: 1.5rem;
			margin-bottom: 32px;
			font-size: 1.05rem;
			line-height: 1.7;
		}

		.tract-pdf-download {
			display: grid;
			grid-template-columns: auto 1fr;
			gap: 18px;
			align-items: center;
			padding: 24px;
			background: rgba(208, 0, 0, 0.07);
			border-radius: var(--radius);
			margin-bottom: 32px;
		}

		.pdf-icon {
			font-size: 2rem;
		}

		.pdf-info {
			display: grid;
			gap: 0.6rem;
		}

		.pdf-title {
			margin: 0;
			font-size: 1.3rem;
		}

		.pdf-description {
			margin: 0;
			color: rgba(17, 17, 17, 0.7);
		}

		.pdf-download-button {
			display: inline-flex;
			align-items: center;
			justify-content: center;
			gap: 0.5rem;
			padding: 0.85rem 1.6rem;
			border-radius: var(--radius);
			background: var(--cgt-red);
			color: var(--cgt-white);
			font-weight: 600;
		}

		.pdf-download-button:hover,
		.pdf-download-button:focus {
			background: var(--cgt-red-600);
			color: var(--cgt-white);
		}

		.tract-sources {
			margin-bottom: 32px;
			padding: 22px;
			border-radius: var(--radius);
			background: rgba(229, 231, 235, 0.35);
		}

		.sources-title {
			margin: 0 0 0.8rem;
			font-size: 1.2rem;
		}

		.tract-tags {
			margin-bottom: 32px;
		}

		.tags-label {
			font-weight: 700;
			margin-bottom: 0.6rem;
			display: block;
		}

		.tags-list {
			display: flex;
			flex-wrap: wrap;
			gap: 0.5rem;
			list-style: none;
			margin: 0;
			padding: 0;
		}

		.tag {
			display: inline-flex;
			align-items: center;
			padding: 0.4rem 0.9rem;
			border-radius: 999px;
			background: rgba(208, 0, 0, 0.1);
			color: var(--cgt-red);
			font-weight: 600;
		}

		.tract-navigation {
			display: flex;
			justify-content: space-between;
			gap: 1rem;
			margin-bottom: 32px;
			padding-top: 24px;
			border-top: 1px solid rgba(17, 17, 17, 0.1);
		}

		.tract-navigation a {
			display: inline-flex;
			gap: 0.6rem;
			align-items: center;
			font-weight: 600;
			color: var(--cgt-red);
		}

		.tract-back {
			text-align: center;
			margin: 0;
		}

		.back-button {
			display: inline-flex;
			align-items: center;
			gap: 0.6rem;
			font-weight: 600;
			color: var(--cgt-red);
		}

		@media (max-width: 720px) {
			.tract-container {
				padding: 28px;
			}

			.tract-pdf-download,
			.tract-excerpt {
				grid-template-columns: 1fr;
			}

			.tract-navigation {
				flex-direction: column;
				align-items: stretch;
			}
		}
	</style>
	<?php
endwhile;

get_footer();
