<?php
/**
 * Template Name: Classes archive
 *
 * Page qui affiche les articles filtrables par branche, catégorie et classe.
 *
 * @package CGT_Child
 */

get_header();

$request      = wp_unslash( $_GET ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$selected_class = isset( $request['classe'] ) ? sanitize_text_field( $request['classe'] ) : '';
$selected_branch = isset( $request['branche'] ) ? sanitize_text_field( $request['branche'] ) : '';
$selected_category = isset( $request['categorie'] ) ? sanitize_text_field( $request['categorie'] ) : '';
$search_query = isset( $request['s'] ) ? sanitize_text_field( $request['s'] ) : '';

$tax_query = array();

if ( $selected_class ) {
	$tax_query[] = array(
		'taxonomy' => 'thematique',
		'field'    => 'slug',
		'terms'    => $selected_class,
	);
}

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

if ( count( $tax_query ) > 1 ) {
	$tax_query = array_merge( array( 'relation' => 'AND' ), $tax_query );
} elseif ( empty( $tax_query ) ) {
	$tax_query = array();
}

$paged      = max( 1, (int) get_query_var( 'paged', 1 ) );
$post_types = array( 'post', 'articles_adherents' );
$post_status = is_user_logged_in() ? array( 'publish', 'private' ) : 'publish';

$query_args = array(
	'post_type'      => $post_types,
	'post_status'    => $post_status,
	'posts_per_page' => 12,
	'paged'          => $paged,
	'orderby'        => 'date',
	'order'          => 'DESC',
	's'              => $search_query,
	'tax_query'      => $tax_query,
);

$classes_query = new WP_Query( $query_args );

$classes_terms = get_terms(
	array(
		'taxonomy'   => 'thematique',
		'hide_empty' => false,
		'orderby'    => 'name',
		'order'      => 'ASC',
	)
);

$branch_terms = get_terms(
	array(
		'taxonomy'   => 'branche',
		'hide_empty' => false,
		'orderby'    => 'name',
		'order'      => 'ASC',
	)
);

$category_terms = get_terms(
	array(
		'taxonomy'   => 'category',
		'hide_empty' => false,
		'orderby'    => 'name',
		'order'      => 'ASC',
	)
);

$current_url = get_permalink();

$default_title = get_the_title();
$page_title    = $default_title;
$default_intro = __( 'Explorez nos contenus publics et privés grâce aux filtres ci-dessous.', 'cgt' );
$subtitle      = $default_intro;
$active_filters = array();

$selected_class_term = $selected_class ? get_term_by( 'slug', $selected_class, 'thematique' ) : false;
if ( $selected_class_term && ! is_wp_error( $selected_class_term ) ) {
	$page_title = $selected_class_term->name;
	if ( ! empty( $selected_class_term->description ) ) {
		$subtitle = $selected_class_term->description;
	}
	$active_filters[] = sprintf(
		/* translators: %s: class label */
		__( 'Classe : %s', 'cgt' ),
		$selected_class_term->name
	);
}

$selected_branch_term = $selected_branch ? get_term_by( 'slug', $selected_branch, 'branche' ) : false;
if ( $selected_branch_term && ! is_wp_error( $selected_branch_term ) ) {
	if ( $page_title === $default_title ) {
		$page_title = $selected_branch_term->name;
	}
	$active_filters[] = sprintf(
		/* translators: %s: branch label */
		__( 'Branche : %s', 'cgt' ),
		$selected_branch_term->name
	);
}

$selected_category_term = $selected_category ? get_term_by( 'slug', $selected_category, 'category' ) : false;
if ( $selected_category_term && ! is_wp_error( $selected_category_term ) ) {
	if ( $page_title === $default_title ) {
		$page_title = $selected_category_term->name;
	}
	$active_filters[] = sprintf(
		/* translators: %s: category label */
		__( 'Catégorie : %s', 'cgt' ),
		$selected_category_term->name
	);
}

if ( empty( $active_filters ) ) {
	$subtitle = $default_intro;
}
?>

<main id="primary" class="site-main classes-archive">
	<div class="container">
		<header class="classes-header">
			<h1 class="page-title"><?php echo esc_html( $page_title ); ?></h1>
			<?php if ( ! empty( $subtitle ) ) : ?>
				<p><?php echo wp_kses_post( $subtitle ); ?></p>
			<?php endif; ?>
			<?php if ( ! empty( $active_filters ) ) : ?>
				<div class="classes-active-filters" aria-live="polite">
					<span class="classes-active-filters__label"><?php esc_html_e( 'Filtres actifs :', 'cgt' ); ?></span>
					<ul class="classes-active-filters__list">
						<?php foreach ( $active_filters as $label ) : ?>
							<li><?php echo esc_html( $label ); ?></li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>
		</header>

		<form class="classes-filters" method="get" action="<?php echo esc_url( $current_url ); ?>">
			<div class="classes-filters__row">
				<label for="classes-search">
					<span class="sr-only"><?php esc_html_e( 'Rechercher un article', 'cgt' ); ?></span>
				</label>
				<input
					type="search"
					id="classes-search"
					name="s"
					placeholder="<?php esc_attr_e( 'Rechercher un article…', 'cgt' ); ?>"
					value="<?php echo esc_attr( $search_query ); ?>"
				>
			</div>
			<div class="classes-filters__row">
				<label for="classes-filter-class"><?php esc_html_e( 'Classes', 'cgt' ); ?></label>
				<select id="classes-filter-class" name="classe">
					<option value=""><?php esc_html_e( 'Toutes les classes', 'cgt' ); ?></option>
					<?php if ( ! is_wp_error( $classes_terms ) ) : ?>
						<?php foreach ( $classes_terms as $term ) : ?>
							<option value="<?php echo esc_attr( $term->slug ); ?>" <?php selected( $selected_class, $term->slug ); ?>>
								<?php echo esc_html( $term->name ); ?>
							</option>
						<?php endforeach; ?>
					<?php endif; ?>
				</select>
			</div>
			<div class="classes-filters__row">
				<label for="classes-filter-branch"><?php esc_html_e( 'Branches', 'cgt' ); ?></label>
				<select id="classes-filter-branch" name="branche">
					<option value=""><?php esc_html_e( 'Toutes les branches', 'cgt' ); ?></option>
					<?php if ( ! is_wp_error( $branch_terms ) ) : ?>
						<?php foreach ( $branch_terms as $term ) : ?>
							<option value="<?php echo esc_attr( $term->slug ); ?>" <?php selected( $selected_branch, $term->slug ); ?>>
								<?php echo esc_html( $term->name ); ?>
							</option>
						<?php endforeach; ?>
					<?php endif; ?>
				</select>
			</div>
			<div class="classes-filters__row">
				<label for="classes-filter-category"><?php esc_html_e( 'Catégories', 'cgt' ); ?></label>
				<select id="classes-filter-category" name="categorie">
					<option value=""><?php esc_html_e( 'Toutes les catégories', 'cgt' ); ?></option>
					<?php if ( ! is_wp_error( $category_terms ) ) : ?>
						<?php foreach ( $category_terms as $term ) : ?>
							<option value="<?php echo esc_attr( $term->slug ); ?>" <?php selected( $selected_category, $term->slug ); ?>>
								<?php echo esc_html( $term->name ); ?>
							</option>
						<?php endforeach; ?>
					<?php endif; ?>
				</select>
			</div>
			<div class="classes-filters__actions">
				<button type="submit" class="btn"><?php esc_html_e( 'Filtrer', 'cgt' ); ?></button>
				<?php if ( $selected_class || $selected_branch || $selected_category || $search_query ) : ?>
					<a class="btn btn-light" href="<?php echo esc_url( $current_url ); ?>"><?php esc_html_e( 'Réinitialiser', 'cgt' ); ?></a>
				<?php endif; ?>
			</div>
		</form>

		<?php if ( $classes_query->have_posts() ) : ?>
			<div class="classes-results">
				<?php
				while ( $classes_query->have_posts() ) :
					$classes_query->the_post();
					get_template_part( 'parts/card', null, array( 'context' => 'post' ) );
				endwhile;
				wp_reset_postdata();
				?>
			</div>

			<?php
			$pagination = paginate_links(
				array(
					'total'   => $classes_query->max_num_pages,
					'current' => $paged,
					'type'    => 'list',
				)
			);

			if ( $pagination ) :
				?>
				<nav class="classes-pagination" aria-label="<?php esc_attr_e( 'Pagination des résultats', 'cgt' ); ?>">
					<?php echo wp_kses_post( $pagination ); ?>
				</nav>
			<?php endif; ?>

		<?php else : ?>
			<p class="classes-empty"><?php esc_html_e( 'Aucun article ne correspond actuellement à cette sélection.', 'cgt' ); ?></p>
		<?php endif; ?>
	</div>
</main>

<?php
get_footer();
