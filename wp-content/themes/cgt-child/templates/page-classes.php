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

$classes_terms = wp_cache_get( 'cgt_classes_terms' );
if ( false === $classes_terms ) {
    $classes_terms = get_terms(
        array(
            'taxonomy'   => 'thematique',
            'hide_empty' => false,
            'orderby'    => 'name',
            'order'      => 'ASC',
        )
    );
    wp_cache_set( 'cgt_classes_terms', $classes_terms, '', HOUR_IN_SECONDS );
}

$branch_terms = wp_cache_get( 'cgt_branch_terms' );
if ( false === $branch_terms ) {
    $branch_terms = get_terms(
        array(
            'taxonomy'   => 'branche',
            'hide_empty' => false,
            'orderby'    => 'name',
            'order'      => 'ASC',
        )
    );
    wp_cache_set( 'cgt_branch_terms', $branch_terms, '', HOUR_IN_SECONDS );
}

$category_terms = wp_cache_get( 'cgt_category_terms' );
if ( false === $category_terms ) {
    $category_terms = get_terms(
        array(
            'taxonomy'   => 'category',
            'hide_empty' => false,
            'orderby'    => 'name',
            'order'      => 'ASC',
        )
    );
    wp_cache_set( 'cgt_category_terms', $category_terms, '', HOUR_IN_SECONDS );
}

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

// Check if we are on "Adresses utiles" page
$is_adresses_utiles = ( 'adresses-utiles' === $selected_class );
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

		<?php if ( $is_adresses_utiles ) : ?>
			<!-- Custom content for Adresses utiles -->
			<div class="adresses-utiles-section" style="margin: 3rem 0; padding: 3rem; background: white; border-radius: 0.75rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); text-align: center;">
				<div style="margin-bottom: 2rem;">
					<svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#e30613" stroke-width="2" style="display: inline-block; margin-bottom: 1.5rem;">
						<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
						<circle cx="12" cy="7" r="4"></circle>
					</svg>
					<h2 style="font-size: 2rem; font-weight: 700; color: #111827; margin: 0 0 1rem;"><?php esc_html_e( 'Carnet d\'adresse', 'cgt' ); ?></h2>
					<p style="font-size: 1.125rem; color: #6b7280; margin: 0 0 2rem; max-width: 600px; margin-left: auto; margin-right: auto;">
						<?php esc_html_e( 'Retrouvez nos experts et professionnels de confiance pour vous accompagner dans vos démarches juridiques, comptables et administratives.', 'cgt' ); ?>
					</p>
					<?php
					// Get the carnet d'adresse page
					$carnet_page = get_page_by_path( 'carnet-adresse', OBJECT, 'page' );
					if ( $carnet_page ) :
						$carnet_url = get_permalink( $carnet_page->ID );
					?>
						<a href="<?php echo esc_url( $carnet_url ); ?>" class="btn" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.875rem 2rem; background: #e30613; color: white; text-decoration: none; border-radius: 0.5rem; font-weight: 600; font-size: 1rem; transition: all 200ms;">
							<?php esc_html_e( 'Consulter le carnet d\'adresse', 'cgt' ); ?>
							<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
								<line x1="5" y1="12" x2="19" y2="12"></line>
								<polyline points="12 5 19 12 12 19"></polyline>
							</svg>
						</a>
					<?php else : ?>
						<p style="color: #dc2626;"><?php esc_html_e( 'La page du carnet d\'adresse n\'est pas encore créée.', 'cgt' ); ?></p>
					<?php endif; ?>
				</div>
			</div>
		<?php else : ?>
		<!-- Normal filter form for other classes -->
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
		<?php endif; // End if not adresses-utiles ?>

		<?php if ( ! $is_adresses_utiles && $classes_query->have_posts() ) : ?>
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

		<?php elseif ( ! $is_adresses_utiles ) : ?>
			<p class="classes-empty"><?php esc_html_e( 'Aucun article ne correspond actuellement à cette sélection.', 'cgt' ); ?></p>
		<?php endif; ?>
	</div>
</main>

<?php
get_footer();
