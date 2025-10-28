<?php
/**
 * Template pour afficher un tract individuel
 *
 * @package CGT_Child
 */

get_header();

while ( have_posts() ) :
	the_post();

	// Récupérer les métadonnées
	$sources = get_post_meta( get_the_ID(), 'cgt_submission_sources', true );
	$pdf_url = get_post_meta( get_the_ID(), 'cgt_fichier_pdf', true );
	?>

	<article id="post-<?php the_ID(); ?>" <?php post_class( 'cgt-single-tract' ); ?>>
		<div class="tract-container">
			<!-- Header du tract -->
			<header class="tract-header">
				<div class="tract-meta">
					<?php
					// Th�matiques
					$thematiques = wp_get_post_terms( get_the_ID(), 'thematique' );
					if ( ! empty( $thematiques ) ) {
						foreach ( $thematiques as $thematique ) {
							echo '<span class="tract-category">' . esc_html( $thematique->name ) . '</span>';
						}
					}

					// Branche
					$branches = wp_get_post_terms( get_the_ID(), 'branche' );
					if ( ! empty( $branches ) ) {
						foreach ( $branches as $branch ) {
							echo '<span class="tract-branch">' . esc_html( $branch->name ) . '</span>';
						}
					}
					?>
					<span class="tract-date">=� <?php echo get_the_date( 'd/m/Y' ); ?></span>
				</div>

				<h1 class="tract-title"><?php the_title(); ?></h1>

				<div class="tract-author">
					<?php
					printf(
						/* translators: %s: post author. */
						esc_html__( 'Par %s', 'cgt' ),
						'<span class="author-name">' . esc_html( get_the_author() ) . '</span>'
					);
					?>
				</div>
			</header>

			<!-- Image mise en avant -->
			<?php if ( has_post_thumbnail() ) : ?>
				<div class="tract-featured-image">
					<?php the_post_thumbnail( 'large', array( 'class' => 'featured-img' ) ); ?>
				</div>
			<?php endif; ?>

			<!-- Extrait -->
			<?php if ( has_excerpt() ) : ?>
				<div class="tract-excerpt">
					<div class="excerpt-icon">=�</div>
					<div class="excerpt-content">
						<?php the_excerpt(); ?>
					</div>
				</div>
			<?php endif; ?>

			<!-- Contenu principal -->
			<div class="tract-content">
				<?php the_content(); ?>
			</div>

			<!-- PDF � t�l�charger -->
			<?php if ( $pdf_url ) : ?>
				<div class="tract-pdf-download">
					<div class="pdf-icon">=�</div>
					<div class="pdf-info">
						<h3 class="pdf-title">T�l�charger le tract en PDF</h3>
						<p class="pdf-description">T�l�chargez la version PDF de ce tract pour l'imprimer ou la partager</p>
						<a href="<?php echo esc_url( $pdf_url ); ?>" class="pdf-download-button" download>
							 T�l�charger le PDF
						</a>
					</div>
				</div>
			<?php endif; ?>

			<!-- Sources et r�f�rences -->
			<?php if ( $sources ) : ?>
				<div class="tract-sources">
					<h3 class="sources-title">=� Sources et r�f�rences</h3>
					<div class="sources-content">
						<?php echo nl2br( esc_html( $sources ) ); ?>
					</div>
				</div>
			<?php endif; ?>

			<!-- Tags -->
			<?php
			$tags = get_the_tags();
			if ( $tags ) :
				?>
				<div class="tract-tags">
					<span class="tags-label"><� Mots-cl�s :</span>
					<?php
					foreach ( $tags as $tag ) {
						echo '<span class="tag">' . esc_html( $tag->name ) . '</span>';
					}
					?>
				</div>
			<?php endif; ?>

			<!-- Navigation -->
			<nav class="tract-navigation">
				<div class="nav-previous">
					<?php
					$prev_post = get_previous_post();
					if ( $prev_post ) :
						?>
						<a href="<?php echo esc_url( get_permalink( $prev_post->ID ) ); ?>" rel="prev">
							<span class="nav-arrow">�</span>
							<span class="nav-title"><?php echo esc_html( $prev_post->post_title ); ?></span>
						</a>
					<?php endif; ?>
				</div>
				<div class="nav-next">
					<?php
					$next_post = get_next_post();
					if ( $next_post ) :
						?>
						<a href="<?php echo esc_url( get_permalink( $next_post->ID ) ); ?>" rel="next">
							<span class="nav-title"><?php echo esc_html( $next_post->post_title ); ?></span>
							<span class="nav-arrow">�</span>
						</a>
					<?php endif; ?>
				</div>
			</nav>

			<!-- Retour -->
			<div class="tract-back">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="back-button">
					� Retour � l'accueil
				</a>
			</div>
		</div>
	</article>

	<style>
		.cgt-single-tract {
			background: #fff;
			max-width: 900px;
			margin: 40px auto;
			padding: 0;
		}

		.tract-container {
			padding: 40px;
		}

		.tract-header {
			margin-bottom: 30px;
			padding-bottom: 25px;
			border-bottom: 3px solid #c8102e;
		}

		.tract-meta {
			display: flex;
			flex-wrap: wrap;
			gap: 12px;
			margin-bottom: 20px;
		}

		.tract-category,
		.tract-branch,
		.tract-date {
			display: inline-block;
			padding: 6px 14px;
			border-radius: 20px;
			font-size: 13px;
			font-weight: 600;
			background: #f3f4f6;
			color: #374151;
		}

		.tract-category {
			background: #dbeafe;
			color: #1e40af;
		}

		.tract-branch {
			background: #fef3c7;
			color: #92400e;
		}

		.tract-title {
			font-size: 38px;
			font-weight: 700;
			line-height: 1.2;
			margin: 0 0 15px 0;
			color: #1f2937;
		}

		.tract-author {
			font-size: 15px;
			color: #6b7280;
		}

		.author-name {
			font-weight: 600;
			color: #c8102e;
		}

		.tract-featured-image {
			margin-bottom: 35px;
			border-radius: 12px;
			overflow: hidden;
			box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
		}

		.featured-img {
			width: 100%;
			height: auto;
			display: block;
		}

		.tract-excerpt {
			background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
			border-left: 4px solid #3b82f6;
			border-radius: 8px;
			padding: 25px;
			margin-bottom: 35px;
			display: flex;
			gap: 20px;
			align-items: flex-start;
		}

		.excerpt-icon {
			font-size: 32px;
			flex-shrink: 0;
		}

		.excerpt-content {
			flex: 1;
			font-size: 17px;
			line-height: 1.7;
			color: #1e3a8a;
			font-weight: 500;
		}

		.excerpt-content p {
			margin: 0;
		}

		.tract-content {
			font-size: 17px;
			line-height: 1.8;
			color: #374151;
			margin-bottom: 35px;
		}

		.tract-content p {
			margin-bottom: 20px;
		}

		.tract-content h2,
		.tract-content h3 {
			color: #1f2937;
			margin-top: 35px;
			margin-bottom: 18px;
			font-weight: 700;
		}

		.tract-content h2 {
			font-size: 28px;
			border-bottom: 2px solid #e5e7eb;
			padding-bottom: 10px;
		}

		.tract-content h3 {
			font-size: 22px;
		}

		.tract-content ul,
		.tract-content ol {
			margin-bottom: 20px;
			padding-left: 30px;
		}

		.tract-content li {
			margin-bottom: 10px;
		}

		.tract-content a {
			color: #c8102e;
			text-decoration: underline;
			font-weight: 600;
		}

		.tract-content a:hover {
			color: #a00d26;
		}

		.tract-pdf-download {
			background: linear-gradient(135deg, #c8102e 0%, #a00d26 100%);
			color: white;
			border-radius: 12px;
			padding: 30px;
			margin-bottom: 35px;
			display: flex;
			gap: 25px;
			align-items: center;
			box-shadow: 0 10px 15px -3px rgba(200, 16, 46, 0.3);
		}

		.pdf-icon {
			font-size: 60px;
			flex-shrink: 0;
		}

		.pdf-info {
			flex: 1;
		}

		.pdf-title {
			font-size: 22px;
			font-weight: 700;
			margin: 0 0 10px 0;
			color: white;
		}

		.pdf-description {
			margin: 0 0 20px 0;
			color: rgba(255, 255, 255, 0.9);
			font-size: 15px;
			line-height: 1.6;
		}

		.pdf-download-button {
			display: inline-block;
			padding: 12px 28px;
			background: white;
			color: #c8102e;
			text-decoration: none;
			border-radius: 6px;
			font-weight: 700;
			font-size: 16px;
			transition: all 0.2s;
			box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
		}

		.pdf-download-button:hover {
			transform: translateY(-2px);
			box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
			background: #f9fafb;
		}

		.tract-sources {
			background: #f9fafb;
			border: 2px solid #e5e7eb;
			border-radius: 8px;
			padding: 25px;
			margin-bottom: 30px;
		}

		.sources-title {
			font-size: 20px;
			font-weight: 700;
			margin: 0 0 15px 0;
			color: #1f2937;
		}

		.sources-content {
			font-size: 15px;
			line-height: 1.7;
			color: #4b5563;
		}

		.tract-tags {
			display: flex;
			flex-wrap: wrap;
			gap: 10px;
			align-items: center;
			padding: 20px 0;
			border-top: 1px solid #e5e7eb;
			border-bottom: 1px solid #e5e7eb;
			margin-bottom: 30px;
		}

		.tags-label {
			font-weight: 600;
			color: #6b7280;
			font-size: 14px;
		}

		.tag {
			display: inline-block;
			padding: 5px 12px;
			background: #f3f4f6;
			border: 1px solid #d1d5db;
			border-radius: 16px;
			font-size: 13px;
			color: #4b5563;
			transition: all 0.2s;
		}

		.tag:hover {
			background: #e5e7eb;
			border-color: #9ca3af;
		}

		.tract-navigation {
			display: flex;
			justify-content: space-between;
			gap: 20px;
			margin-bottom: 30px;
		}

		.nav-previous,
		.nav-next {
			flex: 1;
		}

		.nav-previous a,
		.nav-next a {
			display: flex;
			align-items: center;
			gap: 12px;
			padding: 15px 20px;
			background: #f9fafb;
			border: 2px solid #e5e7eb;
			border-radius: 8px;
			text-decoration: none;
			color: #1f2937;
			transition: all 0.2s;
		}

		.nav-next a {
			justify-content: flex-end;
			text-align: right;
		}

		.nav-previous a:hover,
		.nav-next a:hover {
			background: #c8102e;
			border-color: #c8102e;
			color: #fff;
		}

		.nav-arrow {
			font-size: 20px;
			font-weight: 700;
		}

		.nav-title {
			font-size: 14px;
			font-weight: 600;
			line-height: 1.4;
		}

		.tract-back {
			text-align: center;
			padding-top: 20px;
		}

		.back-button {
			display: inline-block;
			padding: 12px 30px;
			background: #c8102e;
			color: #fff;
			text-decoration: none;
			border-radius: 6px;
			font-weight: 600;
			font-size: 15px;
			transition: all 0.2s;
		}

		.back-button:hover {
			background: #a00d26;
			transform: translateY(-2px);
			box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
		}

		@media (max-width: 768px) {
			.tract-container {
				padding: 25px;
			}

			.tract-title {
				font-size: 28px;
			}

			.tract-excerpt {
				flex-direction: column;
				padding: 20px;
			}

			.tract-pdf-download {
				flex-direction: column;
				text-align: center;
				padding: 25px;
			}

			.pdf-icon {
				font-size: 50px;
			}

			.tract-navigation {
				flex-direction: column;
			}

			.nav-next a {
				justify-content: flex-start;
				text-align: left;
			}
		}
	</style>

	<?php
endwhile;

get_footer();
