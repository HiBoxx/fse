<?php
/**
 * Tableau de bord de l’espace adhérent.
 *
 * @package CGT_Child
 */

if ( ! is_user_logged_in() ) {
	wp_safe_redirect( wp_login_url( get_permalink() ) );
	exit;
}

require_once ABSPATH . 'wp-admin/includes/post.php';

get_header();

$current_user  = wp_get_current_user();
$user_roles    = $current_user ? (array) $current_user->roles : array();
$has_access    = cgt_user_can_read_private() || in_array( 'gestionnaire', $user_roles, true ) || in_array( 'assistance', $user_roles, true );
$display_name  = $current_user && $current_user->display_name ? $current_user->display_name : $current_user->user_login;
$role_overrides = array( 'gestionnaire', 'assistance' );
$has_override_role = array_intersect( $user_roles, $role_overrides );
$requires_branch_selection = in_array( 'adherent', $user_roles, true ) && empty( $has_override_role );

// Get user's selected branch
$user_branch_id = get_user_meta( get_current_user_id(), 'cgt_user_branch', true );

// Build tax_query for user's branch
$branch_tax_query = array();
if ( $user_branch_id ) {
	$branch_tax_query = array(
		array(
			'taxonomy' => 'branche',
			'field'    => 'term_id',
			'terms'    => $user_branch_id,
		),
	);
}

$private_tracts_args = array(
	'post_type'      => 'tracts',
	'post_status'    => array( 'publish', 'draft', 'pending', 'private' ),
	'posts_per_page' => 2,
	'no_found_rows'  => true,
	'meta_query'     => array(
		'relation' => 'OR',
		array(
			'key'     => 'cgt_visibilite',
			'value'   => array( 'prive', 'privé' ),
			'compare' => 'IN',
		),
		array(
			'key'     => 'cgt_visibilite',
			'compare' => 'NOT EXISTS',
		),
		array(
			'key'     => 'cgt_visibilite',
			'value'   => '',
			'compare' => '=',
		),
	),
);

if ( $branch_tax_query ) {
	$private_tracts_args['tax_query'] = $branch_tax_query;
}

$private_tracts = new WP_Query( $private_tracts_args );

$all_articles_page = get_page_by_path( 'actualites', OBJECT, 'page' );
$all_articles_link = $all_articles_page ? get_permalink( $all_articles_page ) : get_post_type_archive_link( 'post' );

$federal_articles_args = array(
	'post_type'      => array( 'post', 'articles_adherents' ),
	'post_status'    => array( 'publish', 'private' ),
	'posts_per_page' => 2,
	'no_found_rows'  => true,
	'orderby'        => 'date',
	'order'          => 'DESC',
);

$federal_articles = new WP_Query( $federal_articles_args );

$agenda_events = new WP_Query(
	array(
		'post_type'      => 'cgt_agenda',
		'post_status'    => 'publish',
		'posts_per_page' => 6,
		'no_found_rows'  => true,
		'meta_key'       => 'cgt_event_date',
		'orderby'        => 'meta_value',
		'order'          => 'ASC',
		'meta_query'     => array(
			array(
				'key'     => 'cgt_event_date',
				'value'   => gmdate( 'Y-m-d H:i:s' ),
				'compare' => '>=',
				'type'    => 'DATETIME',
			),
		),
	)
);

$branch_sections = array();
$branch_terms    = get_terms(
	array(
		'taxonomy'   => 'branche',
		'hide_empty' => false,
		'orderby'    => 'term_id',
		'order'      => 'ASC',
	)
);

if ( ! is_wp_error( $branch_terms ) ) {
	foreach ( $branch_terms as $branch_term ) {
		if ( count( $branch_sections ) >= 4 ) {
			break;
		}

		$branch_posts = get_posts(
			array(
				'post_type'      => 'articles_adherents',
				'post_status'    => array( 'private' ),
				'posts_per_page' => 3,
				'tax_query'      => array(
					array(
						'taxonomy' => 'branche',
						'field'    => 'term_id',
						'terms'    => $branch_term->term_id,
					),
				),
			)
		);

		if ( $branch_posts ) {
			$branch_sections[] = array(
				'term'  => $branch_term,
				'posts' => $branch_posts,
			);
		}
	}
}

$bulletins_link_base = $all_articles_link ? $all_articles_link : get_post_type_archive_link( 'post' );
$bulletins_link_args = array( 'categorie' => 'bulletins' );

if ( $user_branch_id ) {
	$user_branch = get_term( $user_branch_id, 'branche' );
	if ( $user_branch && ! is_wp_error( $user_branch ) ) {
		$bulletins_link_args['branche'] = $user_branch->slug;
	}
}

$bulletins_link = add_query_arg( array_filter( $bulletins_link_args ), $bulletins_link_base );

$agenda_term = get_term_by( 'slug', 'agenda', 'thematique' );
$agenda_link = ( $agenda_term && ! is_wp_error( $agenda_term ) ) ? get_term_link( $agenda_term ) : '#';

$library_options = array(
	'agenda-social'                    => array(
		'label' => __( 'Agenda social', 'cgt' ),
		'type'  => 'tax',
	),
	'analyses'                         => array(
		'label' => __( 'Analyses', 'cgt' ),
		'type'  => 'tax',
	),
	'affiches-syndicales'              => array(
		'label' => __( 'Affiches syndicales', 'cgt' ),
		'type'  => 'tax',
	),
	'bulletins'                        => array(
		'label' => __( 'Bulletins', 'cgt' ),
		'type'  => 'tax',
	),
	'brochures'                        => array(
		'label' => __( 'Brochures', 'cgt' ),
		'type'  => 'tax',
	),
	'communiques'                      => array(
		'label' => __( 'Communiqués de presse', 'cgt' ),
		'type'  => 'post_type',
		'value' => 'communiques_de_presse',
	),
	'compte-rendus'                    => array(
		'label' => __( 'Compte-rendus', 'cgt' ),
		'type'  => 'tax',
	),
	'dossiers-thematiques'             => array(
		'label' => __( 'Dossiers thématiques', 'cgt' ),
		'type'  => 'tax',
	),
	'guides-pratiques'                 => array(
		'label' => __( 'Guides pratiques', 'cgt' ),
		'type'  => 'tax',
	),
	'jurisprudences-federales'         => array(
		'label' => __( 'Jurisprudences fédérales', 'cgt' ),
		'type'  => 'tax',
	),
	'le-lien-syndical'                 => array(
		'label' => __( 'Le Lien Syndical', 'cgt' ),
		'type'  => 'tax',
	),
	'les-cahiers-du-numerique'         => array(
		'label' => __( 'Les cahiers du numérique', 'cgt' ),
		'type'  => 'tax',
	),
	'modeles-de-lettre'                => array(
		'label' => __( 'Modèles de lettre', 'cgt' ),
		'type'  => 'tax',
	),
	'tract-entreprise'                 => array(
		'label' => __( 'Tracts d\'entreprise', 'cgt' ),
		'type'  => 'tax',
	),
	'tracts'                           => array(
		'label' => __( 'Tracts de la fédération', 'cgt' ),
		'type'  => 'post_type',
		'value' => 'tracts',
	),
);

$library_selected = isset( $_GET['library_term'] ) ? sanitize_text_field( wp_unslash( $_GET['library_term'] ) ) : '';
$library_search   = isset( $_GET['library_search'] ) ? sanitize_text_field( wp_unslash( $_GET['library_search'] ) ) : '';

$library_per_page = 6;
$library_query_args = array(
	'post_type'      => array( 'post', 'communiques_de_presse', 'dossiers_de_presse', 'tracts' ),
	'post_status'    => 'publish',
	'posts_per_page' => $library_per_page,
	'no_found_rows'  => false,
);

// Initialize tax_query array
$library_tax_query = array();

// First priority: Filter by user's branch if selected
if ( $branch_tax_query ) {
	$library_tax_query[] = $branch_tax_query[0];
}

// Second priority: Filter by category/rubrique if selected
if ( $library_selected && isset( $library_options[ $library_selected ] ) ) {
	$option = $library_options[ $library_selected ];
	if ( 'tax' === $option['type'] ) {
		$library_tax_query[] = array(
			'taxonomy' => 'thematique',
			'field'    => 'slug',
			'terms'    => $library_selected,
		);
	} elseif ( 'post_type' === $option['type'] ) {
		$library_query_args['post_type'] = $option['value'];
	}
}

// Apply tax_query if we have filters
if ( ! empty( $library_tax_query ) ) {
	if ( count( $library_tax_query ) > 1 ) {
		$library_tax_query['relation'] = 'AND';
	}
	$library_query_args['tax_query'] = $library_tax_query;
}

// Third priority: Search
if ( $library_search ) {
	$library_query_args['s'] = $library_search;
}

$library_query = new WP_Query( $library_query_args );

$member_questions = new WP_Query(
	array(
		'post_type'      => 'cgt_contact',
		'post_status'    => array( 'private', 'publish' ),
		'posts_per_page' => 10,
		'orderby'        => 'date',
		'order'          => 'DESC',
		'no_found_rows'  => true,
		'meta_query'     => array(
			array(
				'key'   => 'cgt_contact_type',
				'value' => 'question',
			),
			array(
				'key'     => 'cgt_contact_user',
				'value'   => get_current_user_id(),
				'type'    => 'NUMERIC',
				'compare' => '=',
			),
		),
	)
);

$article_submission_link = home_url( '/publier-article' );
$tract_submission_link   = add_query_arg( 'type', 'tract', $article_submission_link );
?>

<main id="primary" class="site-main member-dashboard">
	<header class="member-dashboard__header container">
		<div class="member-dashboard__welcome">
			<div class="member-dashboard__user">
				<span class="member-dashboard__icon">👤</span>
				<div>
					<h1 class="member-dashboard__title"><?php printf( esc_html__( 'Bonjour %s', 'cgt' ), esc_html( $display_name ) ); ?></h1>
					<?php
					// Use already defined $user_branch_id from line 22
					if ( $requires_branch_selection && $user_branch_id ) {
						$user_branch = get_term( $user_branch_id, 'branche' );
						if ( $user_branch && ! is_wp_error( $user_branch ) ) {
							echo '<div class="member-dashboard__branch-container">';
							echo '<p class="member-dashboard__branch">' . esc_html( $user_branch->name ) . '</p>';
							echo '<button type="button" class="btn-change-branch" id="changeBranchBtn">';
							echo '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>';
							esc_html_e( 'Modifier la branche', 'cgt' );
							echo '</button>';
							echo '<a href="' . esc_url( wp_logout_url( home_url() ) ) . '" class="btn-logout">';
							echo '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>';
							esc_html_e( 'Déconnexion', 'cgt' );
							echo '</a>';
							echo '</div>';
						}
					}
					?>
				</div>
			</div>

			<?php if ( $requires_branch_selection ) : ?>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="member-branch-selector" id="branchSelectorForm" style="<?php echo $user_branch_id ? 'display: none;' : ''; ?>">
					<?php wp_nonce_field( 'cgt_select_branch', 'cgt_branch_nonce' ); ?>
					<input type="hidden" name="action" value="cgt_select_user_branch">
					<label for="user_branch"><?php esc_html_e( $user_branch_id ? 'Modifier votre branche :' : 'Sélectionnez votre branche :', 'cgt' ); ?></label>
					<select name="user_branch" id="user_branch" required>
						<option value=""><?php esc_html_e( '-- Choisir une branche --', 'cgt' ); ?></option>
						<?php
						if ( ! is_wp_error( $branch_terms ) ) {
							foreach ( $branch_terms as $branch ) {
								$selected = ( $user_branch_id && $branch->term_id == $user_branch_id ) ? ' selected' : '';
								echo '<option value="' . esc_attr( $branch->term_id ) . '"' . $selected . '>' . esc_html( $branch->name ) . '</option>';
							}
						}
						?>
					</select>
					<div class="member-branch-selector__actions">
						<button type="submit" class="btn btn-compact"><?php esc_html_e( 'Valider', 'cgt' ); ?></button>
						<?php if ( $user_branch_id ) : ?>
							<button type="button" class="btn btn-compact btn-outline" id="cancelBranchBtn"><?php esc_html_e( 'Annuler', 'cgt' ); ?></button>
						<?php endif; ?>
					</div>
				</form>
			<?php endif; ?>
		</div>
		<p><?php esc_html_e( 'Votre tableau de bord centralise les ressources privées, les actions à venir et vos espaces d\'échanges.', 'cgt' ); ?></p>
	</header>

	<?php if ( ! $has_access ) : ?>
		<div class="container">
			<p class="notice member-notice"><?php esc_html_e( "Votre compte ne possède pas encore les droits pour accéder aux contenus réservés. Merci de contacter la CGT.", 'cgt' ); ?></p>
		</div>
	<?php else : ?>
		<section class="member-section member-section--grid container">
			<div class="member-panel member-panel--tracts">
				<h2><?php esc_html_e( 'Derniers tracts privés', 'cgt' ); ?></h2>
				<?php if ( $private_tracts->have_posts() ) : ?>
					<ul class="member-list">
						<?php while ( $private_tracts->have_posts() ) : $private_tracts->the_post(); ?>
							<li>
								<a href="<?php the_permalink(); ?>" class="member-list__link">
									<span class="member-list__title"><?php the_title(); ?></span>
									<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
								</a>
							</li>
						<?php endwhile; wp_reset_postdata(); ?>
					</ul>
				<?php else : ?>
					<p><?php esc_html_e( 'Aucun tract privé disponible pour le moment.', 'cgt' ); ?></p>
				<?php endif; ?>
				<a class="btn btn-compact" href="<?php echo esc_url( get_post_type_archive_link( 'tracts' ) ); ?>"><?php esc_html_e( 'Voir tous les tracts', 'cgt' ); ?></a>
			</div>

			<div class="member-panel member-panel--news">
				<h2><?php esc_html_e( 'Actualités fédérales', 'cgt' ); ?></h2>
				<?php if ( $federal_articles->have_posts() ) : ?>
					<ul class="member-list">
						<?php while ( $federal_articles->have_posts() ) : $federal_articles->the_post(); ?>
							<li>
								<a href="<?php the_permalink(); ?>" class="member-list__link">
									<span class="member-list__title"><?php the_title(); ?></span>
									<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
								</a>
							</li>
						<?php endwhile; wp_reset_postdata(); ?>
					</ul>
				<?php else : ?>
					<p><?php esc_html_e( 'Aucune actualité récente pour vos secteurs.', 'cgt' ); ?></p>
				<?php endif; ?>
				<?php if ( $all_articles_link ) : ?>
					<a class="btn btn-compact" href="<?php echo esc_url( $all_articles_link ); ?>"><?php esc_html_e( 'Voir tous les articles', 'cgt' ); ?></a>
				<?php endif; ?>
			</div>

			<div class="member-panel member-panel--agenda">
				<h2><?php esc_html_e( 'Agenda & événements', 'cgt' ); ?></h2>
				<?php if ( $agenda_events->have_posts() ) : ?>
					<ul class="member-agenda">
						<?php while ( $agenda_events->have_posts() ) : $agenda_events->the_post(); ?>
							<li>
								<?php
								$event_date = get_post_meta( get_the_ID(), 'cgt_event_date', true );
								$event_addr = get_post_meta( get_the_ID(), 'cgt_event_address', true );
								$event_doc  = get_post_meta( get_the_ID(), 'cgt_event_document', true );
								?>
								<span class="member-agenda__date">
									<?php echo esc_html( $event_date ? wp_date( 'd M Y', strtotime( $event_date ) ) : get_the_date( 'd M Y' ) ); ?>
								</span>
								<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
								<?php if ( $event_addr ) : ?>
									<span class="member-agenda__meta"><?php echo esc_html( $event_addr ); ?></span>
								<?php endif; ?>
								<?php if ( $event_doc ) : ?>
									<span class="member-agenda__meta">
										<a href="<?php echo esc_url( wp_get_attachment_url( $event_doc ) ); ?>" target="_blank" rel="noopener">
											<?php esc_html_e( 'Télécharger le document', 'cgt' ); ?>
										</a>
									</span>
								<?php endif; ?>
							</li>
						<?php endwhile; wp_reset_postdata(); ?>
					</ul>
				<?php else : ?>
					<p><?php esc_html_e( 'Aucun événement planifié pour le moment. Créez vos rendez-vous depuis l\'administration.', 'cgt' ); ?></p>
				<?php endif; ?>
			</div>
		</section>

	<section class="member-section container" id="section-articles-adherents">
		<div class="member-panel member-panel--branches">
			<h2><?php esc_html_e( 'Articles adhérents par branche', 'cgt' ); ?></h2>
			<div class="member-panel__actions">
				<a class="btn btn-compact" href="<?php echo esc_url( $article_submission_link ); ?>"><?php esc_html_e( 'Proposer un article adhérent', 'cgt' ); ?></a>
				<a class="btn btn-compact btn-outline" href="<?php echo esc_url( $tract_submission_link ); ?>"><?php esc_html_e( 'Soumettre un tract réservé aux adhérent·es', 'cgt' ); ?></a>
			</div>
		</div>
	</section>

		<section class="member-section container">
			<div class="member-panel member-panel--library">
				<h2><?php esc_html_e( 'Bibliothèque de la fédération', 'cgt' ); ?></h2>
				<form class="member-library-controls" method="get">
					<label class="sr-only" for="library-term"><?php esc_html_e( 'Choisir une rubrique', 'cgt' ); ?></label>
					<select id="library-term" name="library_term">
						<option value=""><?php esc_html_e( 'Toutes les rubriques', 'cgt' ); ?></option>
						<?php foreach ( $library_options as $slug => $option ) : ?>
							<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $library_selected, $slug ); ?>><?php echo esc_html( $option['label'] ); ?></option>
						<?php endforeach; ?>
					</select>
					<label class="sr-only" for="library-search"><?php esc_html_e( 'Rechercher par titre', 'cgt' ); ?></label>
					<input type="search" id="library-search" name="library_search" placeholder="<?php esc_attr_e( 'Rechercher…', 'cgt' ); ?>" value="<?php echo esc_attr( $library_search ); ?>">
					<button class="btn btn-compact" type="submit"><?php esc_html_e( 'Filtrer', 'cgt' ); ?></button>
				</form>

				<?php if ( $library_query->have_posts() ) : ?>
					<div class="member-library-grid">
						<?php
						while ( $library_query->have_posts() ) :
							$library_query->the_post();
							?>
							<article class="member-library-card">
								<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
								<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
								<p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 22, '…' ) ); ?></p>
							</article>
							<?php
						endwhile;
						wp_reset_postdata();
						?>
					</div>
					<?php if ( $library_query->found_posts > $library_per_page ) : ?>
						<p class="member-library-more">
							<a class="btn btn-compact" href="<?php echo esc_url( home_url( '/mediatheque' ) ); ?>">
								<?php esc_html_e( 'Voir la bibliothèque complète', 'cgt' ); ?>
							</a>
						</p>
					<?php endif; ?>
				<?php else : ?>
					<p><?php esc_html_e( 'Aucun document trouvé pour cette sélection.', 'cgt' ); ?></p>
				<?php endif; ?>
			</div>
		</section>

		<section class="member-section container member-section--actions">
			<div class="member-panel member-panel--actions">
				<h2><?php esc_html_e( 'Bulletins fédéraux', 'cgt' ); ?></h2>
				<p><?php esc_html_e( 'Consultez l\'intégralité des bulletins publiés pour suivre la vie de la fédération et de ses branches.', 'cgt' ); ?></p>
				<a class="btn btn-compact" href="<?php echo esc_url( $bulletins_link ); ?>"><?php esc_html_e( 'Voir tous les bulletins', 'cgt' ); ?></a>
			</div>
		</section>

		<section class="member-section member-section--two-col container">
			<div class="member-panel member-panel--question">
				<h2><?php esc_html_e( 'Envoyer une question', 'cgt' ); ?></h2>
				<p><?php esc_html_e( 'Partagez vos questions, vos besoins d\'appui juridique ou vos retours terrain.', 'cgt' ); ?></p>
				<?php echo do_shortcode( '[cgt_questions]' ); ?>

				<div class="member-contact-info">
					<h3><?php esc_html_e( 'Contact par voie postale ou téléphonique', 'cgt' ); ?></h3>
					<p><?php esc_html_e( 'Vous pouvez aussi nous joindre par voie postale ou téléphonique aux coordonnées ci-dessous :', 'cgt' ); ?></p>
					<div class="member-contact-info__details">
						<div class="member-contact-info__item">
							<svg class="member-contact-info__icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
								<path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
								<polyline points="9 22 9 12 15 12 15 22"></polyline>
							</svg>
							<div>
								<strong><?php esc_html_e( 'Fédération CGT des Sociétés d\'Études', 'cgt' ); ?></strong><br>
								<?php esc_html_e( '263, rue de Paris - Case 421', 'cgt' ); ?><br>
								<?php esc_html_e( '93514 Montreuil cedex', 'cgt' ); ?>
							</div>
						</div>
						<div class="member-contact-info__item">
							<svg class="member-contact-info__icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
								<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
							</svg>
							<div>
								<strong><?php esc_html_e( 'Tél.', 'cgt' ); ?></strong> <a href="tel:+33155828941">+33 1 55 82 89 41</a><br>
								<strong><?php esc_html_e( 'Fax.', 'cgt' ); ?></strong> +33 1 55 82 89 42
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="member-panel member-panel--faq">
				<h2><?php esc_html_e( 'Questions fréquentes', 'cgt' ); ?></h2>
				<div class="member-faq">
					<?php
					$faq_items = array(
						array(
							'question' => __( 'Quels sont mes droits en cas de licenciement ?', 'cgt' ),
							'answer'   => __( 'En cas de licenciement, l\'employeur doit respecter une procédure (convocation, entretien, notification, préavis). Selon l\'ancienneté, des indemnités légales ou conventionnelles sont dues. Vérifiez votre convention collective et contactez la fédération pour un accompagnement personnalisé.', 'cgt' ),
						),
						array(
							'question' => __( 'Comment contester un licenciement ?', 'cgt' ),
							'answer'   => __( 'Vous disposez de 12 mois pour saisir le conseil de prud\'hommes après la notification. Il est recommandé de réunir tous les justificatifs (contrat, échanges, bulletins de salaire) et de solliciter la CGT pour préparer le dossier et, si nécessaire, bénéficier d\'un appui juridique.', 'cgt' ),
						),
						array(
							'question' => __( 'Que prévoit la loi sur les heures supplémentaires ?', 'cgt' ),
							'answer'   => __( 'Les heures au-delà de 35h doivent être rémunérées avec une majoration (généralement +25 % puis +50 %). La convention collective peut prévoir des taux plus favorables. Elles doivent être demandées ou validées par l\'employeur et apparaître sur le bulletin de salaire.', 'cgt' ),
						),
						array(
							'question' => __( 'Puis-je refuser une modification de mon contrat ?', 'cgt' ),
							'answer'   => __( 'Toute modification d\'un élément essentiel du contrat (salaire, horaires, lieu de travail) nécessite votre accord écrit. En cas de refus, l\'employeur peut tenter un licenciement mais devra justifier d\'une cause réelle et sérieuse. Contactez la fédération avant de répondre.', 'cgt' ),
						),
						array(
							'question' => __( 'Comment faire respecter un accord collectif ?', 'cgt' ),
							'answer'   => __( 'Les accords collectifs s\'imposent à l\'employeur. Si vous constatez un non-respect (salaires, classifications, congés), signalez-le à vos représentants CGT ou à la fédération. Ils pourront organiser un rappel à l\'employeur ou saisir l\'inspection du travail.', 'cgt' ),
						),
						array(
							'question' => __( 'Quelles démarches pour obtenir des formations ?', 'cgt' ),
							'answer'   => __( 'Vous pouvez mobiliser le Compte Personnel de Formation (CPF), le plan de développement des compétences de l\'entreprise ou demander un congé de formation économique, sociale et syndicale. L\'employeur doit répondre à votre demande dans des délais précis.', 'cgt' ),
						),
						array(
							'question' => __( 'Que faire en cas de harcèlement ?', 'cgt' ),
							'answer'   => __( 'Conservez toutes les preuves (mails, témoignages). Alertez vos représentants CGT et la fédération. L\'employeur a l\'obligation d\'enquêter et de protéger les salarié·es. Vous pouvez aussi saisir l\'inspection du travail et, si besoin, le conseil de prud\'hommes.', 'cgt' ),
						),
						array(
							'question' => __( 'Comment utiliser mon droit de grève ?', 'cgt' ),
							'answer'   => __( 'La grève doit être collective et concerner des revendications professionnelles. Informez-vous auprès de la CGT pour organiser le mouvement dans le cadre légal. L\'employeur ne peut sanctionner qu\'en cas de faute lourde, mais la retenue de salaire est possible pour les heures non travaillées.', 'cgt' ),
						),
					);

					foreach ( $faq_items as $faq ) :
						?>
						<details>
							<summary><?php echo esc_html( $faq['question'] ); ?></summary>
							<div class="member-faq__content">
								<p><?php echo esc_html( $faq['answer'] ); ?></p>
							</div>
						</details>
						<?php
					endforeach;
					?>
				</div>
			</div>
		</section>

		<section class="member-section container">
			<div class="member-panel member-panel--history">
				<h2><?php esc_html_e( 'Mes questions en cours', 'cgt' ); ?></h2>
				<?php if ( $member_questions->have_posts() ) : ?>
					<ul class="question-list">
						<?php
						while ( $member_questions->have_posts() ) :
							$member_questions->the_post();
							$post_id   = get_the_ID();
							$subject   = get_post_meta( $post_id, 'cgt_submission_subject', true );
							$response  = get_post_meta( $post_id, 'cgt_contact_response', true );
							$status    = get_post_status_object( get_post_status( $post_id ) );
							?>
							<li class="question-list__item">
								<div class="question-list__header">
									<strong><?php echo $subject ? esc_html( $subject ) : esc_html( get_the_title() ); ?></strong>
									<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
								</div>
								<?php if ( $response ) : ?>
									<div class="question-response">
										<span class="question-response__label"><?php esc_html_e( 'Réponse', 'cgt' ); ?></span>
										<div class="question-response__content">
											<?php echo wp_kses_post( wpautop( $response ) ); ?>
										</div>
									</div>
								<?php else : ?>
									<p class="question-status question-status--pending">
										<?php esc_html_e( 'En attente de réponse.', 'cgt' ); ?>
										<?php if ( $status && isset( $status->label ) ) : ?>
											<span>(<?php echo esc_html( $status->label ); ?>)</span>
										<?php endif; ?>
									</p>
								<?php endif; ?>
							</li>
							<?php
						endwhile;
						wp_reset_postdata();
						?>
					</ul>
				<?php else : ?>
					<p><?php esc_html_e( 'Vous n\'avez pas encore posé de question.', 'cgt' ); ?></p>
				<?php endif; ?>
			</div>
		</section>
	<?php endif; ?>
</main>

<?php
get_footer();
