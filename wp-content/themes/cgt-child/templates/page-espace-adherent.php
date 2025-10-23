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
				<a class="btn btn-compact" href="<?php echo esc_url( get_post_type_archive_link( 'communiques_de_presse' ) ); ?>"><?php esc_html_e( 'Voir toutes les actualités', 'cgt' ); ?></a>
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
