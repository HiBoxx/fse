<?php
/**
 * Template Name: Actualités fédérales
 *
 * Liste tous les articles publiés avec filtres par branche et catégorie.
 *
 * @package CGT_Child
 */

get_header();

$current_page = max( 1, get_query_var( 'paged' ) ? get_query_var( 'paged' ) : ( isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1 ) );
$per_page     = 12;
$search_term  = isset( $_GET['recherche'] ) ? sanitize_text_field( wp_unslash( $_GET['recherche'] ) ) : '';
$branch_slug  = isset( $_GET['branche'] ) ? sanitize_text_field( wp_unslash( $_GET['branche'] ) ) : '';
$category_slug = isset( $_GET['categorie'] ) ? sanitize_text_field( wp_unslash( $_GET['categorie'] ) ) : '';

$query_args = array(
	'post_type'      => 'post',
	'post_status'    => 'publish',
	'paged'          => $current_page,
	'posts_per_page' => $per_page,
);

if ( $search_term ) {
	$query_args['s'] = $search_term;
}

$tax_query = array();

if ( $branch_slug ) {
	$tax_query[] = array(
		'taxonomy' => 'branche',
		'field'    => 'slug',
		'terms'    => $branch_slug,
	);
}

if ( $category_slug ) {
	$tax_query[] = array(
		'taxonomy' => 'category',
		'field'    => 'slug',
		'terms'    => $category_slug,
	);
}

if ( ! empty( $tax_query ) ) {
	if ( count( $tax_query ) > 1 ) {
		$tax_query['relation'] = 'AND';
	}
	$query_args['tax_query'] = $tax_query;
}

$articles_query = new WP_Query( $query_args );
$branches       = get_terms(
	array(
		'taxonomy'   => 'branche',
		'hide_empty' => false,
		'orderby'    => 'name',
		'order'      => 'ASC',
	)
);
$categories     = get_categories(
	array(
		'orderby'    => 'name',
		'order'      => 'ASC',
		'hide_empty' => false,
	)
);
?>

<main id="primary" class="site-main archive-actualites">
	<header class="page-header container">
		<h1 class="page-title"><?php the_title(); ?></h1>
		<p class="page-subtitle"><?php esc_html_e( 'Retrouvez toutes les publications de la fédération et filtrez selon vos besoins.', 'cgt' ); ?></p>
	</header>

	<section class="archive-filters container">
		<form class="archive-filters__form" method="get">
			<div class="archive-filter">
				<label for="filtre-recherche"><?php esc_html_e( 'Recherche', 'cgt' ); ?></label>
				<input
					id="filtre-recherche"
					type="search"
					name="recherche"
					value="<?php echo esc_attr( $search_term ); ?>"
					placeholder="<?php esc_attr_e( 'Mots-clés…', 'cgt' ); ?>"
				>
			</div>

			<div class="archive-filter">
				<label for="filtre-categorie"><?php esc_html_e( 'Catégorie', 'cgt' ); ?></label>
				<select id="filtre-categorie" name="categorie">
					<option value=""><?php esc_html_e( 'Toutes les catégories', 'cgt' ); ?></option>
					<?php foreach ( $categories as $category ) : ?>
						<option value="<?php echo esc_attr( $category->slug ); ?>" <?php selected( $category_slug, $category->slug ); ?>>
							<?php echo esc_html( $category->name ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>

			<div class="archive-filter">
				<label for="filtre-branche"><?php esc_html_e( 'Branche', 'cgt' ); ?></label>
				<select id="filtre-branche" name="branche">
					<option value=""><?php esc_html_e( 'Toutes les branches', 'cgt' ); ?></option>
					<?php if ( ! is_wp_error( $branches ) ) : ?>
						<?php foreach ( $branches as $branch ) : ?>
							<option value="<?php echo esc_attr( $branch->slug ); ?>" <?php selected( $branch_slug, $branch->slug ); ?>>
								<?php echo esc_html( $branch->name ); ?>
							</option>
						<?php endforeach; ?>
					<?php endif; ?>
				</select>
			</div>

			<div class="archive-filter archive-filter--actions">
				<button class="btn btn-compact" type="submit"><?php esc_html_e( 'Filtrer', 'cgt' ); ?></button>
				<?php if ( $search_term || $branch_slug || $category_slug ) : ?>
					<a class="btn btn-compact btn-outline" href="<?php echo esc_url( get_permalink() ); ?>"><?php esc_html_e( 'Réinitialiser', 'cgt' ); ?></a>
				<?php endif; ?>
			</div>
		</form>
	</section>

	<section class="archive-results container">
		<?php if ( $articles_query->have_posts() ) : ?>
			<div class="archive-grid">
				<?php
				while ( $articles_query->have_posts() ) :
					$articles_query->the_post();
					get_template_part( 'parts/card', null, array( 'context' => 'communique' ) );
				endwhile;
				?>
			</div>

			<nav class="pagination" aria-label="<?php esc_attr_e( 'Pagination', 'cgt' ); ?>">
				<?php
				echo wp_kses_post(
					paginate_links(
						array(
							'total'   => $articles_query->max_num_pages,
							'current' => $current_page,
							'format'  => '?paged=%#%',
							'add_args'=> array(
								'branche'   => $branch_slug,
								'categorie' => $category_slug,
								'recherche' => $search_term,
							),
							'prev_text' => __( 'Précédent', 'cgt' ),
							'next_text' => __( 'Suivant', 'cgt' ),
						)
					)
				);
				?>
			</nav>
		<?php else : ?>
			<p class="archive-empty"><?php esc_html_e( 'Aucun article ne correspond à vos filtres pour le moment.', 'cgt' ); ?></p>
		<?php endif; ?>

		<?php wp_reset_postdata(); ?>
	</section>
</main>

<?php
get_footer();
