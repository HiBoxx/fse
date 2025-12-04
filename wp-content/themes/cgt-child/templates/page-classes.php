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
$branch_groups = cgt_get_branch_groups();

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
	<!-- Hero Section -->
	<div class="classes-hero">
		<div class="classes-hero__overlay"></div>
		<div class="container">
			<div class="classes-hero__content">
				<div class="classes-hero__badge">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
						<path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
						<path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
					</svg>
					<span><?php esc_html_e( 'Ressources', 'cgt' ); ?></span>
				</div>
				<h1 class="classes-hero__title"><?php echo esc_html( $page_title ); ?></h1>
				<?php if ( ! empty( $subtitle ) ) : ?>
					<p class="classes-hero__subtitle"><?php echo wp_kses_post( $subtitle ); ?></p>
				<?php endif; ?>
				<?php if ( ! empty( $active_filters ) ) : ?>
					<div class="classes-active-filters" aria-live="polite">
						<span class="classes-active-filters__label">
							<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
								<polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
							</svg>
							<?php esc_html_e( 'Filtres actifs', 'cgt' ); ?>
						</span>
						<div class="classes-active-filters__list">
							<?php foreach ( $active_filters as $label ) : ?>
								<span class="filter-tag"><?php echo esc_html( $label ); ?></span>
							<?php endforeach; ?>
						</div>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>

	<div class="container classes-content">

		<?php if ( $is_adresses_utiles ) : ?>
			<!-- Custom content for Adresses utiles - Display expert cards -->
			<?php
			// Enqueue styles et scripts for expert cards
			wp_enqueue_style( 'cgt-carnet-adresse', get_stylesheet_directory_uri() . '/assets/css/carnet-adresse.css', array(), '1.0' );
			wp_enqueue_script( 'cgt-carnet-adresse', get_stylesheet_directory_uri() . '/assets/js/carnet-adresse.js', array( 'jquery' ), '1.0', true );

			// Get all expertise terms
			$expertises = get_terms(
				array(
					'taxonomy'   => 'expertise',
					'hide_empty' => false,
					'orderby'    => 'name',
					'order'      => 'ASC',
				)
			);

			// Get all experts
			$experts_args = array(
				'post_type'      => 'cgt_expert',
				'posts_per_page' => -1,
				'orderby'        => 'title',
				'order'          => 'ASC',
				'post_status'    => 'publish',
			);

			$experts_query = new WP_Query( $experts_args );
			?>

			<div class="carnet-hero" style="background: linear-gradient(135deg, #e30613 0%, #b00510 100%); padding: 3rem 0; margin: -2rem 0 3rem; border-radius: 0.75rem;">
				<div style="max-width: 800px; margin: 0 auto; text-align: center; color: white; padding: 0 1.5rem;">
					<div style="margin-bottom: 1rem;">
						<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block;">
							<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
							<circle cx="12" cy="7" r="4"></circle>
						</svg>
					</div>
					<h2 style="font-size: 2rem; font-weight: 700; color: white; margin: 0 0 1rem;"><?php esc_html_e( 'Carnet d\'adresse', 'cgt' ); ?></h2>
					<p style="font-size: 1.125rem; color: rgba(255,255,255,0.9); margin: 0;">
						<?php esc_html_e( 'Retrouvez nos experts et professionnels de confiance pour vous accompagner dans vos démarches juridiques, comptables et administratives.', 'cgt' ); ?>
					</p>
				</div>
			</div>

			<!-- Filters -->
			<?php if ( ! empty( $expertises ) && ! is_wp_error( $expertises ) ) : ?>
				<div class="carnet-filters" style="margin-bottom: 2rem;">
					<div class="carnet-filters__buttons" style="display: flex; flex-wrap: wrap; gap: 0.75rem; justify-content: center;">
						<button class="filter-btn active" data-filter="all" style="padding: 0.5rem 1.25rem; border: 2px solid #e30613; background: #e30613; color: white; border-radius: 2rem; font-weight: 600; cursor: pointer; transition: all 200ms;">
							<?php esc_html_e( 'Tous', 'cgt' ); ?>
							<span class="filter-count" style="margin-left: 0.5rem; background: rgba(255,255,255,0.2); padding: 0.125rem 0.5rem; border-radius: 1rem; font-size: 0.875rem;"><?php echo esc_html( $experts_query->found_posts ); ?></span>
						</button>
						<?php foreach ( $expertises as $expertise ) : ?>
							<?php
							$count_args = array(
								'post_type'      => 'cgt_expert',
								'posts_per_page' => -1,
								'post_status'    => 'publish',
								'tax_query'      => array(
									array(
										'taxonomy' => 'expertise',
										'field'    => 'term_id',
										'terms'    => $expertise->term_id,
									),
								),
							);
							$count_query = new WP_Query( $count_args );
							?>
							<button class="filter-btn" data-filter="<?php echo esc_attr( $expertise->slug ); ?>" style="padding: 0.5rem 1.25rem; border: 2px solid #e5e7eb; background: white; color: #374151; border-radius: 2rem; font-weight: 600; cursor: pointer; transition: all 200ms;">
								<?php echo esc_html( $expertise->name ); ?>
								<span class="filter-count" style="margin-left: 0.5rem; background: #f3f4f6; padding: 0.125rem 0.5rem; border-radius: 1rem; font-size: 0.875rem;"><?php echo esc_html( $count_query->found_posts ); ?></span>
							</button>
							<?php wp_reset_postdata(); ?>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>

			<!-- Expert cards grid -->
			<?php if ( $experts_query->have_posts() ) : ?>
				<div class="experts-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem; margin-top: 2rem;">
					<?php while ( $experts_query->have_posts() ) : $experts_query->the_post(); ?>
						<?php
						$post_id     = get_the_ID();
						$prenom      = get_post_meta( $post_id, '_expert_prenom', true );
						$telephone   = get_post_meta( $post_id, '_expert_telephone', true );
						$email       = get_post_meta( $post_id, '_expert_email', true );
						$adresse     = get_post_meta( $post_id, '_expert_adresse', true );
						$description = get_post_meta( $post_id, '_expert_description', true );
						$terms       = get_the_terms( $post_id, 'expertise' );
						$expertise_slugs = array();
						if ( $terms && ! is_wp_error( $terms ) ) {
							foreach ( $terms as $term ) {
								$expertise_slugs[] = $term->slug;
							}
						}
						?>
						<div class="expert-card" data-expertise="<?php echo esc_attr( implode( ' ', $expertise_slugs ) ); ?>" style="background: white; border-radius: 0.75rem; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); transition: all 200ms;">
							<div class="expert-card__header" style="display: flex; gap: 1rem; margin-bottom: 1rem;">
								<div class="expert-card__photo" style="flex-shrink: 0;">
									<?php if ( has_post_thumbnail() ) : ?>
										<?php the_post_thumbnail( 'thumbnail', array( 'class' => 'expert-photo', 'style' => 'width: 80px; height: 80px; object-fit: cover; border-radius: 0.5rem;' ) ); ?>
									<?php else : ?>
										<div class="expert-photo-placeholder" style="width: 80px; height: 80px; background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%); border-radius: 0.5rem; display: flex; align-items: center; justify-content: center;">
											<svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="1.5">
												<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
												<circle cx="12" cy="7" r="4"></circle>
											</svg>
										</div>
									<?php endif; ?>
								</div>
								<div class="expert-card__info" style="flex: 1; min-width: 0;">
									<h3 class="expert-card__name" style="font-size: 1.125rem; font-weight: 700; color: #111827; margin: 0 0 0.5rem; overflow: hidden; text-overflow: ellipsis;">
										<?php echo esc_html( $prenom . ' ' . get_the_title() ); ?>
									</h3>
									<?php if ( $terms && ! is_wp_error( $terms ) ) : ?>
										<div class="expert-card__expertises" style="display: flex; flex-wrap: wrap; gap: 0.25rem;">
											<?php foreach ( $terms as $term ) : ?>
												<span class="expert-badge" style="display: inline-block; padding: 0.25rem 0.75rem; background: #fef2f2; color: #e30613; border-radius: 1rem; font-size: 0.75rem; font-weight: 600;">
													<?php echo esc_html( $term->name ); ?>
												</span>
											<?php endforeach; ?>
										</div>
									<?php endif; ?>
								</div>
							</div>
							<?php if ( $description ) : ?>
								<p class="expert-card__description" style="color: #6b7280; font-size: 0.875rem; margin: 0 0 1rem; line-height: 1.5;">
									<?php echo esc_html( wp_trim_words( $description, 20 ) ); ?>
								</p>
							<?php endif; ?>
							<div class="expert-card__contact" style="display: flex; flex-direction: column; gap: 0.5rem; padding-top: 1rem; border-top: 1px solid #f3f4f6;">
								<?php if ( $telephone ) : ?>
									<a href="tel:<?php echo esc_attr( $telephone ); ?>" style="display: flex; align-items: center; gap: 0.5rem; color: #374151; text-decoration: none; font-size: 0.875rem;">
										<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
											<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
										</svg>
										<?php echo esc_html( $telephone ); ?>
									</a>
								<?php endif; ?>
								<?php if ( $email ) : ?>
									<a href="mailto:<?php echo esc_attr( $email ); ?>" style="display: flex; align-items: center; gap: 0.5rem; color: #374151; text-decoration: none; font-size: 0.875rem; word-break: break-all;">
										<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
											<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
											<polyline points="22,6 12,13 2,6"></polyline>
										</svg>
										<?php echo esc_html( $email ); ?>
									</a>
								<?php endif; ?>
								<?php if ( $adresse ) : ?>
									<div style="display: flex; align-items: flex-start; gap: 0.5rem; color: #6b7280; font-size: 0.875rem;">
										<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink: 0; margin-top: 0.125rem;">
											<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
											<circle cx="12" cy="10" r="3"></circle>
										</svg>
										<?php echo esc_html( $adresse ); ?>
									</div>
								<?php endif; ?>
							</div>
						</div>
					<?php endwhile; ?>
					<?php wp_reset_postdata(); ?>
				</div>
			<?php else : ?>
				<div class="experts-empty" style="text-align: center; padding: 3rem; background: #f9fafb; border-radius: 0.75rem;">
					<svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#d1d5db" stroke-width="2" style="display: inline-block; margin-bottom: 1rem;">
						<circle cx="12" cy="12" r="10"></circle>
						<line x1="12" y1="8" x2="12" y2="12"></line>
						<line x1="12" y1="16" x2="12.01" y2="16"></line>
					</svg>
					<p style="color: #6b7280; font-size: 1rem;"><?php esc_html_e( 'Aucun expert n\'a été ajouté pour le moment.', 'cgt' ); ?></p>
				</div>
			<?php endif; ?>
		<?php else : ?>
		<!-- Modern filter form -->
		<div class="classes-filters-wrapper">
			<form class="classes-filters" method="get" action="<?php echo esc_url( $current_url ); ?>">
				<!-- Search Bar -->
				<div class="classes-search-bar">
					<svg class="search-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
						<circle cx="11" cy="11" r="8"></circle>
						<path d="m21 21-4.35-4.35"></path>
					</svg>
					<input
						type="search"
						id="classes-search"
						name="s"
						class="classes-search-input"
						placeholder="<?php esc_attr_e( 'Rechercher un article par titre ou contenu…', 'cgt' ); ?>"
						value="<?php echo esc_attr( $search_query ); ?>"
					>
				</div>

				<!-- Filter Dropdowns -->
				<div class="classes-filters-grid">
					<div class="filter-group">
						<label for="classes-filter-class" class="filter-label">
							<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
								<path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
								<path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
							</svg>
							<?php esc_html_e( 'Classe', 'cgt' ); ?>
						</label>
						<select id="classes-filter-class" name="classe" class="filter-select">
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

					<div class="filter-group">
						<label for="classes-filter-branch" class="filter-label">
							<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
								<path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
								<polyline points="10 17 15 12 10 7"></polyline>
								<line x1="15" y1="12" x2="3" y2="12"></line>
							</svg>
							<?php esc_html_e( 'Branche', 'cgt' ); ?>
						</label>
						<select id="classes-filter-branch" name="branche" class="filter-select">
							<option value=""><?php esc_html_e( 'Toutes les branches', 'cgt' ); ?></option>
							<?php cgt_render_branch_options( $selected_branch ); ?>
						</select>
					</div>

					<div class="filter-group">
						<label for="classes-filter-category" class="filter-label">
							<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
								<path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
							</svg>
							<?php esc_html_e( 'Catégorie', 'cgt' ); ?>
						</label>
						<select id="classes-filter-category" name="categorie" class="filter-select">
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
				</div>

				<!-- Action Buttons -->
				<div class="classes-filters-actions">
					<button type="submit" class="btn-filter btn-primary">
						<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
							<polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
						</svg>
						<?php esc_html_e( 'Appliquer les filtres', 'cgt' ); ?>
					</button>
					<?php if ( $selected_class || $selected_branch || $selected_category || $search_query ) : ?>
						<a class="btn-filter btn-reset" href="<?php echo esc_url( $current_url ); ?>">
							<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
								<polyline points="1 4 1 10 7 10"></polyline>
								<path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"></path>
							</svg>
							<?php esc_html_e( 'Réinitialiser', 'cgt' ); ?>
						</a>
					<?php endif; ?>
				</div>
			</form>
		</div>
		<?php endif; // End if not adresses-utiles ?>

		<?php if ( ! $is_adresses_utiles && ( $selected_class || $selected_branch || $selected_category || $search_query ) ) : ?>
			<!-- Archives Notice - Fixed Message -->
			<div class="classes-archives-notice">
				<div class="classes-archives-notice__icon">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
						<circle cx="12" cy="12" r="10"></circle>
						<line x1="12" y1="16" x2="12" y2="12"></line>
						<line x1="12" y1="8" x2="12.01" y2="8"></line>
					</svg>
				</div>
				<div class="classes-archives-notice__content">
					<p><?php esc_html_e( 'Si vous ne trouvez pas votre article ici, vous pouvez le rechercher dans les archives complètes de la fédération.', 'cgt' ); ?></p>
					<a href="<?php echo esc_url( get_stylesheet_directory_uri() . '/archives-fede/index.php' ); ?>" class="btn btn-compact">
						<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
							<path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
						</svg>
						<?php esc_html_e( 'Rechercher dans les archives', 'cgt' ); ?>
					</a>
				</div>
			</div>
		<?php endif; ?>

		<?php if ( ! $is_adresses_utiles && $classes_query->have_posts() ) : ?>
			<!-- Results Header -->
			<div class="classes-results-header">
				<div class="results-count">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
						<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
						<polyline points="14 2 14 8 20 8"></polyline>
					</svg>
					<span>
						<?php
						printf(
							/* translators: %s: number of results */
							esc_html( _n( '%s résultat trouvé', '%s résultats trouvés', $classes_query->found_posts, 'cgt' ) ),
							'<strong>' . number_format_i18n( $classes_query->found_posts ) . '</strong>'
						);
						?>
					</span>
				</div>
			</div>

			<!-- Results Grid -->
			<div class="classes-results-grid">
				<?php
				while ( $classes_query->have_posts() ) :
					$classes_query->the_post();
					get_template_part( 'parts/card', null, array( 'context' => 'post' ) );
				endwhile;
				wp_reset_postdata();
				?>
			</div>

			<!-- Pagination -->
			<?php
			$pagination = paginate_links(
				array(
					'total'   => $classes_query->max_num_pages,
					'current' => $paged,
					'type'    => 'list',
					'prev_text' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"></polyline></svg> ' . __( 'Précédent', 'cgt' ),
					'next_text' => __( 'Suivant', 'cgt' ) . ' <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>',
				)
			);

			if ( $pagination ) :
				?>
				<nav class="classes-pagination" aria-label="<?php esc_attr_e( 'Pagination des résultats', 'cgt' ); ?>">
					<?php echo wp_kses_post( $pagination ); ?>
				</nav>
			<?php endif; ?>

		<?php elseif ( ! $is_adresses_utiles ) : ?>
			<!-- Empty State -->
			<div class="classes-empty-state">
				<div class="empty-state-icon">
					<svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
						<circle cx="11" cy="11" r="8"></circle>
						<path d="m21 21-4.35-4.35"></path>
					</svg>
				</div>
				<h3><?php esc_html_e( 'Aucun résultat trouvé', 'cgt' ); ?></h3>
				<p><?php esc_html_e( 'Essayez de modifier vos critères de recherche ou de réinitialiser les filtres.', 'cgt' ); ?></p>
				<a href="<?php echo esc_url( $current_url ); ?>" class="btn-empty-reset">
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
						<polyline points="1 4 1 10 7 10"></polyline>
						<path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"></path>
					</svg>
					<?php esc_html_e( 'Réinitialiser les filtres', 'cgt' ); ?>
				</a>
			</div>
		<?php endif; ?>
	</div>
</main>

<?php
get_footer();
