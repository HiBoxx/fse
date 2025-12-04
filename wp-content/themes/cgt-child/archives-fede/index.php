<?php
/**
 * Archives de la Fédération - Page principale
 *
 * Affiche la liste des articles d'archive avec recherche et filtres.
 * Intégré dans WordPress pour avoir le header/footer.
 *
 * @package CGT_Child
 */

// Charger WordPress
require_once dirname( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) ) . '/wp-load.php';

// Charger les données JSON
$json_file = __DIR__ . '/data.json';
$data      = array();
$articles  = array();

if ( file_exists( $json_file ) ) {
	$json_content = file_get_contents( $json_file );
	$data         = json_decode( $json_content, true );
	$articles     = isset( $data['articles'] ) ? $data['articles'] : array();
}

// Extraire les filtres disponibles
$all_categories = array();
$all_tags       = array();
$all_branches   = array();
$all_years      = array();

foreach ( $articles as $article ) {
	// Catégories
	if ( ! empty( $article['categories'] ) ) {
		foreach ( $article['categories'] as $cat ) {
			$slug = $cat['slug'];
			if ( ! isset( $all_categories[ $slug ] ) ) {
				$all_categories[ $slug ] = $cat['name'];
			}
		}
	}

	// Tags
	if ( ! empty( $article['tags'] ) ) {
		foreach ( $article['tags'] as $tag ) {
			$slug = $tag['slug'];
			if ( ! isset( $all_tags[ $slug ] ) ) {
				$all_tags[ $slug ] = $tag['name'];
			}
		}
	}

	// Branches
	if ( ! empty( $article['branches'] ) ) {
		foreach ( $article['branches'] as $branch ) {
			$slug = $branch['slug'];
			if ( ! isset( $all_branches[ $slug ] ) ) {
				$all_branches[ $slug ] = $branch['name'];
			}
		}
	}

	// Années
	if ( ! empty( $article['year'] ) ) {
		$all_years[ $article['year'] ] = $article['year'];
	}
}

// Trier
ksort( $all_categories );
ksort( $all_tags );
// Conserver l'ordre d'origine pour les branches (pas de tri alphabétique).
krsort( $all_years ); // Années décroissantes

$total_articles   = count( $articles );
$total_categories = count( $all_categories );
$total_branches   = count( $all_branches );
$total_tags       = count( $all_tags );

get_header();
?>

<link rel="stylesheet" href="<?php echo esc_url( get_stylesheet_directory_uri() . '/archives-fede/style.css' ); ?>">

<main id="archives-main" class="site-main archives-fede">
	<div class="archives-hero">
		<div class="container archives-container">
			<div class="archives-hero__content">
				<div class="archives-hero__header">
					<p class="archives-kicker">📜 Mémoire syndicale</p>
					<h1 class="archives-title">Archives de la Fédération</h1>
					<p class="archives-description">
						Retrouvez toutes les publications de la FSETUD-CGT depuis sa création : communiqués, dossiers, tracts et analyses thématiques.
					</p>
					<div class="archives-stats">
						<span class="stat-chip stat-chip--primary">
							<strong><?php echo number_format( $total_articles, 0, ',', ' ' ); ?></strong> articles disponibles
						</span>
					</div>
				</div>

				<div class="archives-hero__search">
					<label class="search-label" for="search-input">🔍 Rechercher dans les archives</label>
					<div class="search-box">
						<input
							type="text"
							id="search-input"
							class="search-field"
							placeholder="Titre, contenu, branche..."
							aria-label="Rechercher dans les archives"
						>
						<button type="button" id="search-clear" class="search-clear" aria-label="Effacer la recherche" style="display: none;">×</button>
					</div>
					<div class="search-results-count" id="results-count">
						<?php echo $total_articles; ?> article(s)
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="container archives-container">

		<!-- Filtres -->
		<div class="archives-filters">
			<details class="filter-group" open>
				<summary class="filter-title">Filtres</summary>
				<div class="filter-content">

					<!-- Filtre Année -->
					<?php if ( ! empty( $all_years ) ) : ?>
					<div class="filter-item">
						<label for="filter-year">Année</label>
						<select id="filter-year" class="filter-select">
							<option value="">Toutes les années</option>
							<?php foreach ( $all_years as $year ) : ?>
								<option value="<?php echo esc_attr( $year ); ?>"><?php echo esc_html( $year ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<?php endif; ?>

					<!-- Filtre Branche -->
					<?php if ( ! empty( $all_branches ) ) : ?>
					<div class="filter-item">
						<label for="filter-branch">Branche</label>
						<select id="filter-branch" class="filter-select">
							<option value="">Toutes les branches</option>
							<?php foreach ( $all_branches as $slug => $name ) : ?>
								<option value="<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $name ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<?php endif; ?>

					<!-- Filtre Catégorie -->
					<?php if ( ! empty( $all_categories ) ) : ?>
					<div class="filter-item">
						<label for="filter-category">Catégorie</label>
						<select id="filter-category" class="filter-select">
							<option value="">Toutes les catégories</option>
							<?php foreach ( $all_categories as $slug => $name ) : ?>
								<option value="<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $name ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<?php endif; ?>

					<!-- Filtre Tag -->
					<?php if ( ! empty( $all_tags ) ) : ?>
					<div class="filter-item">
						<label for="filter-tag">Étiquette</label>
						<select id="filter-tag" class="filter-select">
							<option value="">Toutes les étiquettes</option>
							<?php foreach ( $all_tags as $slug => $name ) : ?>
								<option value="<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $name ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<?php endif; ?>

					<div class="filter-actions">
						<button type="button" id="filter-reset" class="btn-reset">Réinitialiser les filtres</button>
					</div>

				</div>
			</details>
		</div>

		<!-- Liste des articles -->
		<div id="articles-list" class="archives-list">
			<?php if ( empty( $articles ) ) : ?>
				<div class="no-results">
					<p>Aucun article disponible pour le moment.</p>
					<p>Les archives seront disponibles après la conversion du fichier XML.</p>
				</div>
			<?php else : ?>
				<!-- Les articles seront affichés dynamiquement par JavaScript -->
			<?php endif; ?>
		</div>

		<!-- Pagination -->
		<div id="pagination" class="archives-pagination"></div>

	</div>
</main>

<!-- Passer les données JSON au JavaScript -->
<script>
	window.archivesData = <?php echo json_encode( $articles, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ); ?>;
	window.archivesBaseUrl = '<?php echo esc_js( get_stylesheet_directory_uri() . '/archives-fede/' ); ?>';
</script>
<script src="<?php echo esc_url( get_stylesheet_directory_uri() . '/archives-fede/script.js' ); ?>"></script>

<?php
get_footer();
