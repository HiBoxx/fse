<?php
/**
 * Archives de la Fédération - Page article détail
 *
 * Affiche le contenu complet d'un article d'archive.
 * Intégré dans WordPress pour avoir le header/footer.
 *
 * URL: /archives-fede/article.php?id=123
 *
 * @package CGT_Child
 */

// Charger WordPress
require_once dirname( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) ) . '/wp-load.php';

// Récupérer l'ID de l'article
$article_id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;

// Charger les données JSON
$json_file = __DIR__ . '/data.json';
$article   = null;

if ( file_exists( $json_file ) && $article_id > 0 ) {
	$json_content = file_get_contents( $json_file );
	$data         = json_decode( $json_content, true );
	$articles     = isset( $data['articles'] ) ? $data['articles'] : array();

	// Chercher l'article
	foreach ( $articles as $item ) {
		if ( isset( $item['id'] ) && (int) $item['id'] === $article_id ) {
			$article = $item;
			break;
		}
	}
}

get_header();
?>

<link rel="stylesheet" href="<?php echo esc_url( get_stylesheet_directory_uri() . '/archives-fede/style.css' ); ?>">

<main id="article-main" class="site-main archives-fede archives-article">
	<div class="container article-container">

		<?php if ( $article ) : ?>

			<!-- Navigation retour -->
			<nav class="article-back">
				<a href="<?php echo esc_url( get_stylesheet_directory_uri() . '/archives-fede/index.php' ); ?>" class="btn-back">
					← Retour aux archives
				</a>
			</nav>

			<!-- En-tête de l'article -->
			<header class="article-header">
				<h1 class="article-title"><?php echo esc_html( $article['title'] ); ?></h1>

				<div class="article-meta">
					<time class="article-date" datetime="<?php echo esc_attr( $article['date'] ); ?>">
						<?php
						$date_obj = DateTime::createFromFormat( 'Y-m-d', $article['date'] );
						echo $date_obj ? $date_obj->format( 'd/m/Y' ) : esc_html( $article['date'] );
						?>
					</time>

					<?php if ( ! empty( $article['categories'] ) ) : ?>
						<span class="article-categories">
							<?php
							foreach ( $article['categories'] as $cat ) {
								echo '<span class="badge badge-category">' . esc_html( $cat['name'] ) . '</span> ';
							}
							?>
						</span>
					<?php endif; ?>
				</div>

				<?php if ( ! empty( $article['branches'] ) ) : ?>
					<div class="article-branches">
						<strong>Branche(s) :</strong>
						<?php
						$branch_names = array_map( function( $b ) {
							return esc_html( $b['name'] );
						}, $article['branches'] );
						echo implode( ', ', $branch_names );
						?>
					</div>
				<?php endif; ?>
			</header>

			<!-- Contenu de l'article -->
			<div class="article-content">
				<?php
				// Convertir les retours à la ligne en paragraphes
				$content = $article['content'];
				$content = nl2br( esc_html( $content ) );
				echo wpautop( $content );
				?>
			</div>

			<!-- Lien PDF -->
			<?php if ( ! empty( $article['pdf_url'] ) ) : ?>
				<div class="article-pdf">
					<a href="<?php echo esc_url( $article['pdf_url'] ); ?>" class="btn-pdf" target="_blank" rel="noopener">
						📄 Télécharger le PDF
					</a>
				</div>
			<?php endif; ?>

			<!-- Tags -->
			<?php if ( ! empty( $article['tags'] ) ) : ?>
				<footer class="article-footer">
					<div class="article-tags">
						<strong>Étiquettes :</strong>
						<?php
						foreach ( $article['tags'] as $tag ) {
							echo '<span class="badge badge-tag">' . esc_html( $tag['name'] ) . '</span> ';
						}
						?>
					</div>
				</footer>
			<?php endif; ?>

		<?php else : ?>

			<!-- Article non trouvé -->
			<div class="article-not-found">
				<h1>Article introuvable</h1>
				<p>L'article demandé n'existe pas ou n'est plus disponible.</p>
				<p>
					<a href="<?php echo esc_url( get_stylesheet_directory_uri() . '/archives-fede/index.php' ); ?>" class="btn-back">
						← Retour aux archives
					</a>
				</p>
			</div>

		<?php endif; ?>

	</div>
</main>

<?php
get_footer();
