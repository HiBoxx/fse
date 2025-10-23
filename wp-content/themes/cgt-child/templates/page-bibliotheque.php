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
	<div class="container">
		<header class="page-header">
			<h1 class="page-title"><?php the_title(); ?></h1>
			<p><?php esc_html_e( 'Filtrez la bibliothèque fédérale par branche, catégorie ou année de publication.', 'cgt' ); ?></p>
		</header>

		<form class="library-filters" method="get">
			<div class="library-filter">
				<label for="library-branch"><?php esc_html_e( 'Branches', 'cgt' ); ?></label>
				<select id="library-branch" name="branche">
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

			<div class="library-filter">
				<label for="library-category"><?php esc_html_e( 'Catégories', 'cgt' ); ?></label>
				<select id="library-category" name="categorie">
					<option value=""><?php esc_html_e( 'Toutes les catégories', 'cgt' ); ?></option>
					<?php foreach ( $categories as $category ) : ?>
						<option value="<?php echo esc_attr( $category->slug ); ?>" <?php selected( $selected_category, $category->slug ); ?>>
							<?php echo esc_html( $category->name ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>

			<div class="library-filter">
				<label for="library-year"><?php esc_html_e( 'Année', 'cgt' ); ?></label>
				<select id="library-year" name="annee">
					<option value="0"><?php esc_html_e( 'Toutes les années', 'cgt' ); ?></option>
					<?php foreach ( $years as $year ) : ?>
						<option value="<?php echo esc_attr( $year ); ?>" <?php selected( $selected_year, $year ); ?>>
							<?php echo esc_html( $year ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>

			<div class="library-filter library-filter--actions">
				<button class="btn btn-compact" type="submit"><?php esc_html_e( 'Filtrer', 'cgt' ); ?></button>
				<?php if ( $selected_branch || $selected_category || $selected_year ) : ?>
					<a class="btn btn-compact btn-outline" href="<?php echo esc_url( get_permalink() ); ?>">
						<?php esc_html_e( 'Réinitialiser', 'cgt' ); ?>
					</a>
				<?php endif; ?>
			</div>
		</form>

		<section class="library-results">
			<?php if ( $library_query->have_posts() ) : ?>
				<ul class="library-list">
					<?php
					while ( $library_query->have_posts() ) :
						$library_query->the_post();
						$post_types_labels = get_post_type_object( get_post_type() );
						?>
						<li class="library-list__item">
							<a href="<?php the_permalink(); ?>">
								<span class="library-list__title"><?php the_title(); ?></span>
								<span class="library-list__meta">
									<?php echo esc_html( get_the_date( 'd/m/Y' ) ); ?>
									<?php if ( $post_types_labels ) : ?>
										· <?php echo esc_html( $post_types_labels->labels->singular_name ); ?>
									<?php endif; ?>
								</span>
							</a>
						</li>
					<?php endwhile; ?>
				</ul>

				<nav class="pagination" aria-label="<?php esc_attr_e( 'Pagination', 'cgt' ); ?>">
					<?php
					echo wp_kses_post(
						paginate_links(
							array(
								'total'   => $library_query->max_num_pages,
								'current' => $paged,
								'format'  => '?paged=%#%',
								'add_args'=> array(
									'branche'  => $selected_branch,
									'categorie'=> $selected_category,
									'annee'    => $selected_year,
								),
								'prev_text' => __( 'Précédent', 'cgt' ),
								'next_text' => __( 'Suivant', 'cgt' ),
							)
						)
					);
					?>
				</nav>
			<?php else : ?>
				<p class="library-empty"><?php esc_html_e( 'Aucun contenu ne correspond à vos filtres pour le moment.', 'cgt' ); ?></p>
			<?php endif; ?>
			<?php wp_reset_postdata(); ?>
		</section>
	</div>
</main>

<?php
get_footer();
