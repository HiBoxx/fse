<?php
/**
 * Template Name: Bibliothèque
 *
 * Liste des contenus avec filtres par branche, catégorie et année.
 *
 * @package CGT_Child
 */

get_header();

$per_page = 25;
$paged    = max(
	1,
	get_query_var( 'paged' ) ? get_query_var( 'paged' ) : ( isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1 )
);

$selected_branch   = isset( $_GET['branche'] ) ? sanitize_text_field( wp_unslash( $_GET['branche'] ) ) : '';
$selected_category = isset( $_GET['categorie'] ) ? sanitize_text_field( wp_unslash( $_GET['categorie'] ) ) : '';
$selected_year     = isset( $_GET['annee'] ) ? absint( $_GET['annee'] ) : 0;

$post_types = array( 'post', 'communiques_de_presse', 'dossiers_de_presse', 'tracts' );

$query_args = array(
	'post_type'      => $post_types,
	'post_status'    => 'publish',
	'orderby'        => 'date',
	'order'          => 'DESC',
	'posts_per_page' => $per_page,
	'paged'          => $paged,
);

$tax_query = array();

if ( $selected_branch ) {
	$tax_query[] = array(
		'taxonomy' => 'branche',
		'field'    => 'slug',
		'terms'    => $selected_branch,
	);
}

if ( $selected_category ) {
	$tax_query[] = array(
		'taxonomy' => 'category',
		'field'    => 'slug',
		'terms'    => $selected_category,
	);
}

if ( ! empty( $tax_query ) ) {
	if ( count( $tax_query ) > 1 ) {
		$tax_query['relation'] = 'AND';
	}
	$query_args['tax_query'] = $tax_query;
}

if ( $selected_year ) {
	$query_args['date_query'] = array(
		array(
			'year' => $selected_year,
		),
	);
}

$library_query = new WP_Query( $query_args );

$branches   = get_terms(
	array(
		'taxonomy'   => 'branche',
		'hide_empty' => false,
		'orderby'    => 'name',
		'order'      => 'ASC',
	)
);
$categories = get_categories(
	array(
		'hide_empty' => false,
		'orderby'    => 'name',
		'order'      => 'ASC',
	)
);

global $wpdb;
$allowed_types = implode( "','", array_map( 'esc_sql', $post_types ) );
$years         = $wpdb->get_col(
	"SELECT DISTINCT YEAR(post_date) FROM {$wpdb->posts}
	WHERE post_status = 'publish'
	AND post_type IN ( '{$allowed_types}' )
	ORDER BY post_date DESC"
);
$years = array_map( 'absint', $years );
?>

<main id="primary" class="site-main library-page">
	<!-- Hero Header -->
	<div class="library-hero">
		<div class="container">
			<div class="library-hero__content">
				<div class="library-hero__badge">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
						<path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
						<path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
					</svg>
					<span><?php esc_html_e( 'Ressources Fédérales', 'cgt' ); ?></span>
				</div>
				<h1 class="library-hero__title"><?php the_title(); ?></h1>
				<p class="library-hero__description"><?php esc_html_e( 'Accédez à l\'ensemble des publications de la fédération : articles, communiqués, dossiers de presse et tracts. Filtrez par branche, catégorie ou année.', 'cgt' ); ?></p>
			</div>
		</div>
	</div>

	<div class="container library-container">
		<!-- Filters Section -->
		<div class="library-filters-wrapper">
			<div class="library-filters-header">
				<h2><?php esc_html_e( 'Filtrer les ressources', 'cgt' ); ?></h2>
				<p><?php esc_html_e( 'Affinez votre recherche par branche, catégorie ou année', 'cgt' ); ?></p>
			</div>

			<form class="library-filters" method="get">
				<div class="filter-group">
					<label class="filter-label" for="library-branch">
						<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
							<path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
						</svg>
						<?php esc_html_e( 'Branches', 'cgt' ); ?>
					</label>
					<select id="library-branch" name="branche" class="filter-select">
						<option value=""><?php esc_html_e( 'Toutes les branches', 'cgt' ); ?></option>
						<?php if ( ! is_wp_error( $branches ) ) : ?>
							<?php foreach ( $branches as $branch ) : ?>
								<option value="<?php echo esc_attr( $branch->slug ); ?>" <?php selected( $selected_branch, $branch->slug ); ?>>
									<?php echo esc_html( $branch->name ); ?>
								</option>
							<?php endforeach; ?>
						<?php endif; ?>
					</select>
				</div>

				<div class="filter-group">
					<label class="filter-label" for="library-category">
						<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
							<path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path>
						</svg>
						<?php esc_html_e( 'Catégories', 'cgt' ); ?>
					</label>
					<select id="library-category" name="categorie" class="filter-select">
						<option value=""><?php esc_html_e( 'Toutes les catégories', 'cgt' ); ?></option>
						<?php foreach ( $categories as $category ) : ?>
							<option value="<?php echo esc_attr( $category->slug ); ?>" <?php selected( $selected_category, $category->slug ); ?>>
								<?php echo esc_html( $category->name ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>

				<div class="filter-group">
					<label class="filter-label" for="library-year">
						<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
							<rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
							<line x1="16" y1="2" x2="16" y2="6"></line>
							<line x1="8" y1="2" x2="8" y2="6"></line>
							<line x1="3" y1="10" x2="21" y2="10"></line>
						</svg>
						<?php esc_html_e( 'Année', 'cgt' ); ?>
					</label>
					<select id="library-year" name="annee" class="filter-select">
						<option value="0"><?php esc_html_e( 'Toutes les années', 'cgt' ); ?></option>
						<?php foreach ( $years as $year ) : ?>
							<option value="<?php echo esc_attr( $year ); ?>" <?php selected( $selected_year, $year ); ?>>
								<?php echo esc_html( $year ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>

				<button class="btn filter-submit" type="submit">
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
						<line x1="4" y1="21" x2="4" y2="14"></line>
						<line x1="4" y1="10" x2="4" y2="3"></line>
						<line x1="12" y1="21" x2="12" y2="12"></line>
						<line x1="12" y1="8" x2="12" y2="3"></line>
						<line x1="20" y1="21" x2="20" y2="16"></line>
						<line x1="20" y1="12" x2="20" y2="3"></line>
						<line x1="1" y1="14" x2="7" y2="14"></line>
						<line x1="9" y1="8" x2="15" y2="8"></line>
						<line x1="17" y1="16" x2="23" y2="16"></line>
					</svg>
					<?php esc_html_e( 'Filtrer', 'cgt' ); ?>
				</button>
				<?php if ( $selected_branch || $selected_category || $selected_year ) : ?>
					<a href="<?php echo esc_url( get_permalink() ); ?>" class="btn btn-secondary filter-reset">
						<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
							<line x1="18" y1="6" x2="6" y2="18"></line>
							<line x1="6" y1="6" x2="18" y2="18"></line>
						</svg>
						<?php esc_html_e( 'Réinitialiser', 'cgt' ); ?>
					</a>
				<?php endif; ?>
			</form>
		</div>

		<!-- Results Grid -->
		<?php if ( $library_query->have_posts() ) : ?>
			<div class="library-results-info">
				<p>
					<strong><?php echo esc_html( $library_query->found_posts ); ?></strong>
					<?php
					echo esc_html(
						sprintf(
							_n( 'document trouvé', 'documents trouvés', $library_query->found_posts, 'cgt' )
						)
					);
					?>
				</p>
			</div>

			<div class="library-grid">
				<?php
				while ( $library_query->have_posts() ) :
					$library_query->the_post();
					$post_type_obj = get_post_type_object( get_post_type() );
					$type_label    = $post_type_obj ? $post_type_obj->labels->singular_name : '';

					// Récupérer les branches
					$branches = wp_get_post_terms( get_the_ID(), 'branche' );

					// Récupérer les catégories
					$post_categories = get_the_category();

					// Tronquer le titre
					$title = get_the_title();
					$title_limit = 80;
					if ( mb_strlen( $title ) > $title_limit ) {
						$title = mb_substr( $title, 0, $title_limit ) . '...';
					}

					// Récupérer l'extrait
					$excerpt = get_the_excerpt();
					$excerpt_trimmed = wp_trim_words( $excerpt, 15, '...' );

					// Déterminer l'icône selon le type de post
					$icon_svg = '';
					switch ( get_post_type() ) {
						case 'tracts':
							$icon_svg = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
								<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
								<polyline points="14 2 14 8 20 8"></polyline>
								<line x1="16" y1="13" x2="8" y2="13"></line>
								<line x1="16" y1="17" x2="8" y2="17"></line>
							</svg>';
							break;
						case 'communiques_de_presse':
						case 'dossiers_de_presse':
							$icon_svg = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
								<path d="M3 3v18h18"></path>
								<path d="M18 17V9"></path>
								<path d="M13 17V5"></path>
								<path d="M8 17v-3"></path>
							</svg>';
							break;
						default:
							$icon_svg = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
								<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
								<polyline points="14 2 14 8 20 8"></polyline>
							</svg>';
					}
					?>
					<article <?php post_class( 'library-card' ); ?>>
						<a href="<?php the_permalink(); ?>" class="library-card__link" aria-label="<?php echo esc_attr( sprintf( __( 'Lire : %s', 'cgt' ), get_the_title() ) ); ?>">
							<div class="library-card__icon">
								<?php echo wp_kses_post( $icon_svg ); ?>
							</div>

							<div class="library-card__content">
								<div class="library-card__header">
									<span class="library-card__date">
										<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
											<rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
											<line x1="16" y1="2" x2="16" y2="6"></line>
											<line x1="8" y1="2" x2="8" y2="6"></line>
											<line x1="3" y1="10" x2="21" y2="10"></line>
										</svg>
										<?php echo esc_html( get_the_date() ); ?>
									</span>
									<?php if ( $type_label ) : ?>
										<span class="library-card__type"><?php echo esc_html( $type_label ); ?></span>
									<?php endif; ?>
								</div>

								<h2 class="library-card__title"><?php echo esc_html( $title ); ?></h2>

								<?php if ( $excerpt_trimmed ) : ?>
									<p class="library-card__excerpt"><?php echo esc_html( $excerpt_trimmed ); ?></p>
								<?php endif; ?>

								<?php if ( ! empty( $branches ) && ! is_wp_error( $branches ) ) : ?>
									<div class="library-card__branches">
										<?php foreach ( array_slice( $branches, 0, 2 ) as $branch ) : ?>
											<span class="library-card__branch-tag"><?php echo esc_html( $branch->name ); ?></span>
										<?php endforeach; ?>
										<?php if ( count( $branches ) > 2 ) : ?>
											<span class="library-card__branch-tag library-card__branch-tag--more">+<?php echo esc_html( count( $branches ) - 2 ); ?></span>
										<?php endif; ?>
									</div>
								<?php elseif ( ! empty( $post_categories ) ) : ?>
									<div class="library-card__branches">
										<?php foreach ( array_slice( $post_categories, 0, 2 ) as $cat ) : ?>
											<span class="library-card__branch-tag"><?php echo esc_html( $cat->name ); ?></span>
										<?php endforeach; ?>
										<?php if ( count( $post_categories ) > 2 ) : ?>
											<span class="library-card__branch-tag library-card__branch-tag--more">+<?php echo esc_html( count( $post_categories ) - 2 ); ?></span>
										<?php endif; ?>
									</div>
								<?php endif; ?>
							</div>
						</a>
					</article>
				<?php endwhile; ?>
			</div>

			<div class="library-pagination">
				<?php
				echo wp_kses_post(
					paginate_links(
						array(
							'total'     => $library_query->max_num_pages,
							'current'   => $paged,
							'format'    => '?paged=%#%',
							'add_args'  => array(
								'branche'   => $selected_branch,
								'categorie' => $selected_category,
								'annee'     => $selected_year,
							),
							'prev_text' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"></polyline></svg> ' . __( 'Précédent', 'cgt' ),
							'next_text' => __( 'Suivant', 'cgt' ) . ' <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>',
						)
					)
				);
				?>
			</div>
		<?php else : ?>
			<div class="library-empty">
				<div class="library-empty__icon">
					<svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
						<path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
						<path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
					</svg>
				</div>
				<h3><?php esc_html_e( 'Aucun document disponible', 'cgt' ); ?></h3>
				<p><?php esc_html_e( 'Aucun document ne correspond à vos critères de recherche. Essayez de modifier vos filtres.', 'cgt' ); ?></p>
				<?php if ( $selected_branch || $selected_category || $selected_year ) : ?>
					<a href="<?php echo esc_url( get_permalink() ); ?>" class="btn">
						<?php esc_html_e( 'Voir tous les documents', 'cgt' ); ?>
					</a>
				<?php endif; ?>
			</div>
		<?php endif; ?>
		<?php wp_reset_postdata(); ?>
	</div>
</main>

<?php
get_footer();
