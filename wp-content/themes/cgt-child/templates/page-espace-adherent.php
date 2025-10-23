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

$has_access    = cgt_user_can_read_private();
$current_user  = wp_get_current_user();
$display_name  = $current_user && $current_user->display_name ? $current_user->display_name : $current_user->user_login;

$private_tracts = new WP_Query(
	array(
		'post_type'      => 'tracts',
		'post_status'    => 'publish',
		'posts_per_page' => 4,
		'no_found_rows'  => true,
		'meta_query'     => array(
			array(
				'key'   => 'cgt_visibilite',
				'value' => 'prive',
			),
		),
	)
);

$recent_communiques = new WP_Query(
	array(
		'post_type'      => 'communiques_de_presse',
		'post_status'    => 'publish',
		'posts_per_page' => 3,
		'no_found_rows'  => true,
	)
);

$agenda_events = new WP_Query(
	array(
		'post_type'      => 'post',
		'post_status'    => 'publish',
		'posts_per_page' => 4,
		'no_found_rows'  => true,
		'tax_query'      => array(
			array(
				'taxonomy' => 'thematique',
				'field'    => 'slug',
				'terms'    => array( 'agenda', 'evenements' ),
				'operator' => 'IN',
			),
		),
	)
);

$branch_sections = array();
$branch_terms    = get_terms(
	array(
		'taxonomy'   => 'branche',
		'hide_empty' => false,
	)
);

if ( ! is_wp_error( $branch_terms ) ) {
	foreach ( $branch_terms as $branch_term ) {
		if ( count( $branch_sections ) >= 4 ) {
			break;
		}

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

$bulletins_term = get_term_by( 'slug', 'bulletins', 'thematique' );
$bulletins_link = ( $bulletins_term && ! is_wp_error( $bulletins_term ) ) ? get_term_link( $bulletins_term ) : get_post_type_archive_link( 'communiques_de_presse' );

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
		'label' => __( 'Tracts d’entreprise', 'cgt' ),
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

$library_query_args = array(
	'post_type'      => array( 'post', 'communiques_de_presse', 'dossiers_de_presse', 'tracts' ),
	'post_status'    => 'publish',
	'posts_per_page' => 6,
	'no_found_rows'  => true,
);

if ( $library_search ) {
	$library_query_args['s'] = $library_search;
}

if ( $library_selected && isset( $library_options[ $library_selected ] ) ) {
	$option = $library_options[ $library_selected ];
	if ( 'tax' === $option['type'] ) {
		$library_query_args['tax_query'] = array(
			array(
				'taxonomy' => 'thematique',
				'field'    => 'slug',
				'terms'    => $library_selected,
			),
		);
	} elseif ( 'post_type' === $option['type'] ) {
		$library_query_args['post_type'] = $option['value'];
	}
}

$library_query = new WP_Query( $library_query_args );

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

$bulletins_term = get_term_by( 'slug', 'bulletins', 'thematique' );
$bulletins_link = ( $bulletins_term && ! is_wp_error( $bulletins_term ) ) ? get_term_link( $bulletins_term ) : get_post_type_archive_link( 'communiques_de_presse' );

$agenda_term = get_term_by( 'slug', 'agenda', 'thematique' );
$agenda_link = ( $agenda_term && ! is_wp_error( $agenda_term ) ) ? get_term_link( $agenda_term ) : '#';

$faq_query = new WP_Query(
	array(
		'post_type'      => 'post',
		'post_status'    => 'publish',
		'posts_per_page' => 5,
		'no_found_rows'  => true,
		'category_name'  => 'faq',
	)
);

$member_questions = new WP_Query(
	array(
		'post_type'      => 'cgt_question',
		'post_status'    => array( 'pending', 'publish' ),
		'author'         => get_current_user_id(),
		'posts_per_page' => 5,
		'no_found_rows'  => true,
	)
);
?>

<main id="primary" class="site-main member-dashboard">
	<header class="member-dashboard__header container">
		<h1><?php printf( esc_html__( 'Bonjour %s', 'cgt' ), esc_html( $display_name ) ); ?></h1>
		<p><?php esc_html_e( 'Votre tableau de bord centralise les ressources privées, les actions à venir et vos espaces d’échanges.', 'cgt' ); ?></p>
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
				<?php if ( $recent_communiques->have_posts() ) : ?>
					<ul class="member-list">
						<?php while ( $recent_communiques->have_posts() ) : $recent_communiques->the_post(); ?>
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
			</div>

			<div class="member-panel member-panel--agenda">
				<h2><?php esc_html_e( 'Agenda & événements', 'cgt' ); ?></h2>
				<?php if ( $agenda_events->have_posts() ) : ?>
					<ul class="member-agenda">
						<?php while ( $agenda_events->have_posts() ) : $agenda_events->the_post(); ?>
							<li>
								<span class="member-agenda__date"><?php echo esc_html( get_the_date( 'd M Y' ) ); ?></span>
								<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
							</li>
						<?php endwhile; wp_reset_postdata(); ?>
					</ul>
				<?php else : ?>
					<p><?php esc_html_e( 'Aucun événement planifié pour le moment. Créez vos rendez-vous depuis l’administration.', 'cgt' ); ?></p>
				<?php endif; ?>
			</div>
		</section>

		<section class="member-section container">
			<div class="member-panel member-panel--branches">
				<h2><?php esc_html_e( 'Articles adhérents par branche', 'cgt' ); ?></h2>
				<?php if ( ! empty( $branch_sections ) ) : ?>
					<div class="member-branch-grid">
						<?php foreach ( $branch_sections as $section ) : ?>
							<div class="member-branch-panel">
								<h3><?php echo esc_html( $section['term']->name ); ?></h3>
								<ul>
									<?php foreach ( $section['posts'] as $branch_post ) : ?>
										<li><a href="<?php echo esc_url( get_permalink( $branch_post ) ); ?>"><?php echo esc_html( get_the_title( $branch_post ) ); ?></a></li>
									<?php endforeach; ?>
								</ul>
							</div>
						<?php endforeach; ?>
					</div>
				<?php else : ?>
					<p><?php esc_html_e( 'Aucun article par branche pour le moment. Ajoutez des contenus privés dans l’administration.', 'cgt' ); ?></p>
				<?php endif; ?>
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
				<?php else : ?>
					<p><?php esc_html_e( 'Aucun document trouvé pour cette sélection.', 'cgt' ); ?></p>
				<?php endif; ?>
			</div>
		</section>

		<section class="member-section member-section--two-col container member-section--actions">
			<div class="member-panel member-panel--actions">
				<h2><?php esc_html_e( 'Bulletins fédéraux', 'cgt' ); ?></h2>
				<p><?php esc_html_e( 'Consultez l’intégralité des bulletins publiés pour suivre la vie de la fédération et de ses branches.', 'cgt' ); ?></p>
				<a class="btn btn-compact" href="<?php echo esc_url( $bulletins_link ); ?>"><?php esc_html_e( 'Voir tous les bulletins', 'cgt' ); ?></a>
			</div>
			<div class="member-panel member-panel--actions">
				<h2><?php esc_html_e( 'Agenda complet', 'cgt' ); ?></h2>
				<p><?php esc_html_e( 'Retrouvez l’ensemble des événements, formations et rendez-vous à venir pour les adhérent·es.', 'cgt' ); ?></p>
				<a class="btn btn-compact" href="<?php echo esc_url( $agenda_link ); ?>"><?php esc_html_e( 'Voir l’agenda et les événements', 'cgt' ); ?></a>
			</div>
		</section>

		<section class="member-section member-section--two-col container">
			<div class="member-panel member-panel--question">
				<h2><?php esc_html_e( 'Envoyer une question', 'cgt' ); ?></h2>
				<p><?php esc_html_e( 'Partagez vos questions, vos besoins d’appui juridique ou vos retours terrain.', 'cgt' ); ?></p>
				<?php echo do_shortcode( '[cgt_questions]' ); ?>
			</div>

			<div class="member-panel member-panel--faq">
				<h2><?php esc_html_e( 'Questions fréquentes', 'cgt' ); ?></h2>
				<?php if ( $faq_query->have_posts() ) : ?>
					<div class="member-faq">
						<?php while ( $faq_query->have_posts() ) : $faq_query->the_post(); ?>
							<details>
								<summary><?php the_title(); ?></summary>
								<div class="member-faq__content"><?php the_excerpt(); ?></div>
							</details>
						<?php endwhile; wp_reset_postdata(); ?>
					</div>
				<?php else : ?>
					<p><?php esc_html_e( 'Ajoutez vos questions fréquentes dans la catégorie “FAQ” pour les rendre accessibles ici.', 'cgt' ); ?></p>
				<?php endif; ?>
			</div>
		</section>

		<section class="member-section container">
			<div class="member-panel member-panel--history">
				<h2><?php esc_html_e( 'Mes questions en cours', 'cgt' ); ?></h2>
				<?php if ( $member_questions->have_posts() ) : ?>
					<ul class="question-list">
						<?php while ( $member_questions->have_posts() ) : $member_questions->the_post(); ?>
							<li>
								<strong><?php the_title(); ?></strong>
								<span class="question-status"><?php echo esc_html( get_post_status_object( get_post_status() )->label ); ?></span>
							</li>
						<?php endwhile; wp_reset_postdata(); ?>
					</ul>
				<?php else : ?>
					<p><?php esc_html_e( 'Vous n’avez pas encore posé de question.', 'cgt' ); ?></p>
				<?php endif; ?>
			</div>
		</section>
	<?php endif; ?>
</main>

<?php
get_footer();
