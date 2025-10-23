<?php
/**
 * Header partial.
 *
 * @package CGT_Child
 */

$logo_url = 'https://www.fsetud.cgt.fr/wp-content/uploads/2025/07/cropped-cropped-cropped-logo_cgt.png';
$posts_page_id   = (int) get_option( 'page_for_posts' );
$posts_page_link = $posts_page_id ? get_permalink( $posts_page_id ) : home_url( '/actualites' );
?>

<header class="header-bar" role="banner">
	<div class="container header-inner">
		<a class="site-logo site-logo--text" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
			<span class="site-logo__text">FSETUD</span>
		</a>
		<div class="header-nav">
			<?php get_template_part( 'parts/nav-primary' ); ?>
		</div>
	</div>
</header>

<div class="site-drawer" id="site-drawer" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="site-drawer-title">
	<div class="site-drawer__panel">
		<button class="site-drawer__close" type="button">
			<span class="sr-only"><?php esc_html_e( 'Fermer le menu', 'cgt' ); ?></span>
			<span aria-hidden="true">&times;</span>
		</button>
	<div class="site-drawer__content">
		<nav class="site-drawer__nav" aria-label="<?php esc_attr_e( 'Arborescence du site', 'cgt' ); ?>">
			<form class="drawer-search" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
				<label for="drawer-search-field" class="sr-only"><?php esc_html_e( 'Rechercher', 'cgt' ); ?></label>
				<input id="drawer-search-field" type="search" name="s" placeholder="<?php esc_attr_e( 'Je cherche un article…', 'cgt' ); ?>">
				<button type="submit" class="btn btn-light"><?php esc_html_e( 'Rechercher', 'cgt' ); ?></button>
			</form>

			<ul class="drawer-section-list">
				<li>
					<h3 class="drawer-section__title" aria-hidden="true"><?php esc_html_e( 'La Fédération', 'cgt' ); ?></h3>
					<ul class="drawer-menu">
						<li><a href="<?php echo esc_url( home_url( '/la-federation' ) ); ?>"><?php esc_html_e( 'Présentation / Vie fédérale', 'cgt' ); ?></a></li>
						<li><a href="<?php echo esc_url( home_url( '/communication-federale' ) ); ?>"><?php esc_html_e( 'Communication fédérale', 'cgt' ); ?></a></li>
						<li><a href="<?php echo esc_url( home_url( '/commissions-executives' ) ); ?>"><?php esc_html_e( 'Commissions exécutives', 'cgt' ); ?></a></li>
						<li><a href="<?php echo esc_url( home_url( '/congres' ) ); ?>"><?php esc_html_e( 'Congrès', 'cgt' ); ?></a></li>
					</ul>
				</li>
				<li>
					<h3 class="drawer-section__title" aria-hidden="true"><?php esc_html_e( 'Publications', 'cgt' ); ?></h3>
					<ul class="drawer-menu">
						<li><a href="<?php echo esc_url( $posts_page_link ); ?>"><?php esc_html_e( 'Actualités', 'cgt' ); ?></a></li>
						<li><a href="<?php echo esc_url( home_url( '/le-lien-syndical' ) ); ?>"><?php esc_html_e( 'Le Lien Syndical', 'cgt' ); ?></a></li>
						<li><a href="<?php echo esc_url( home_url( '/brochures' ) ); ?>"><?php esc_html_e( 'Brochures', 'cgt' ); ?></a></li>
						<li><a href="<?php echo esc_url( home_url( '/dossiers-thematiques' ) ); ?>"><?php esc_html_e( 'Dossiers thématiques', 'cgt' ); ?></a></li>
						<li><a href="<?php echo esc_url( home_url( '/cahiers-numerique' ) ); ?>"><?php esc_html_e( 'Les cahiers du numérique', 'cgt' ); ?></a></li>
					</ul>
				</li>
				<li>
					<h3 class="drawer-section__title" aria-hidden="true"><?php esc_html_e( 'Nos outils & ressources', 'cgt' ); ?></h3>
					<ul class="drawer-menu">
						<li><a href="<?php echo esc_url( home_url( '/agenda-social' ) ); ?>"><?php esc_html_e( 'Agenda social', 'cgt' ); ?></a></li>
						<li><a href="<?php echo esc_url( home_url( '/modeles-lettre' ) ); ?>"><?php esc_html_e( 'Modèles de lettre', 'cgt' ); ?></a></li>
						<li><a href="<?php echo esc_url( home_url( '/guides-pratiques' ) ); ?>"><?php esc_html_e( 'Guides pratiques', 'cgt' ); ?></a></li>
						<li><a href="<?php echo esc_url( home_url( '/jurisprudences-federales' ) ); ?>"><?php esc_html_e( 'Jurisprudences fédérales', 'cgt' ); ?></a></li>
						<li><a href="<?php echo esc_url( home_url( '/adresses-utiles' ) ); ?>"><?php esc_html_e( 'Adresses utiles', 'cgt' ); ?></a></li>
						<li><a href="<?php echo esc_url( home_url( '/affiches-syndicales' ) ); ?>"><?php esc_html_e( 'Affiches syndicales', 'cgt' ); ?></a></li>
						<li><a href="<?php echo esc_url( home_url( '/simulateur-calcul' ) ); ?>"><?php esc_html_e( 'Simulateur de calcul', 'cgt' ); ?></a></li>
					</ul>
				</li>
				<li>
					<h3 class="drawer-section__title" aria-hidden="true"><?php esc_html_e( 'Actualités des branches', 'cgt' ); ?></h3>
					<ul class="drawer-menu">
						<li><a href="<?php echo esc_url( home_url( '/analyses' ) ); ?>"><?php esc_html_e( 'Analyses', 'cgt' ); ?></a></li>
						<li><a href="<?php echo esc_url( home_url( '/bulletins' ) ); ?>"><?php esc_html_e( 'Bulletins', 'cgt' ); ?></a></li>
						<li><a href="<?php echo esc_url( home_url( '/compte-rendus' ) ); ?>"><?php esc_html_e( 'Compte-rendus', 'cgt' ); ?></a></li>
						<li><a href="<?php echo esc_url( home_url( '/conventions-collectives' ) ); ?>"><?php esc_html_e( 'Conventions collectives', 'cgt' ); ?></a></li>
						<li><a href="<?php echo esc_url( home_url( '/tracts-entreprise' ) ); ?>"><?php esc_html_e( 'Tracts d’entreprise', 'cgt' ); ?></a></li>
						<li><a href="<?php echo esc_url( home_url( '/tracts-federation' ) ); ?>"><?php esc_html_e( 'Tracts de la fédération', 'cgt' ); ?></a></li>
					</ul>
				</li>
				<li>
					<h3 class="drawer-section__title" aria-hidden="true"><?php esc_html_e( 'Engagement', 'cgt' ); ?></h3>
					<ul class="drawer-menu">
						<li><a href="<?php echo esc_url( home_url( '/creer-un-syndicat' ) ); ?>"><?php esc_html_e( 'Créer un syndicat', 'cgt' ); ?></a></li>
						<li><a href="<?php echo esc_url( home_url( '/syndicalisation' ) ); ?>"><?php esc_html_e( 'Syndicalisation', 'cgt' ); ?></a></li>
						<li><a href="<?php echo esc_url( home_url( '/elections-professionnelles' ) ); ?>"><?php esc_html_e( 'Élections professionnelles', 'cgt' ); ?></a></li>
						<li><a href="<?php echo esc_url( home_url( '/formation-syndicale' ) ); ?>"><?php esc_html_e( 'Formation syndicale', 'cgt' ); ?></a></li>
					</ul>
				</li>
				<li>
					<h3 class="drawer-section__title" aria-hidden="true"><?php esc_html_e( 'Espace presse', 'cgt' ); ?></h3>
					<ul class="drawer-menu">
						<li><a href="<?php echo esc_url( get_post_type_archive_link( 'communiques_de_presse' ) ); ?>"><?php esc_html_e( 'Communiqués de presse', 'cgt' ); ?></a></li>
						<li><a href="<?php echo esc_url( home_url( '/revue-de-presse' ) ); ?>"><?php esc_html_e( 'Revues de presse', 'cgt' ); ?></a></li>
						<li><a href="<?php echo esc_url( home_url( '/international' ) ); ?>"><?php esc_html_e( 'International', 'cgt' ); ?></a></li>
					</ul>
				</li>
				<li>
					<h3 class="drawer-section__title" aria-hidden="true"><?php esc_html_e( 'Espace adhérent', 'cgt' ); ?></h3>
					<?php if ( is_user_logged_in() ) : ?>
						<ul class="drawer-menu">
							<li><a href="<?php echo esc_url( home_url( '/espace-adherent' ) ); ?>"><?php esc_html_e( 'Tableau de bord', 'cgt' ); ?></a></li>
							<li><a href="<?php echo esc_url( get_post_type_archive_link( 'tracts' ) ); ?>"><?php esc_html_e( 'Tracts', 'cgt' ); ?></a></li>
							<li><a href="<?php echo esc_url( home_url( '/branches' ) ); ?>"><?php esc_html_e( 'Informations sur ma branche', 'cgt' ); ?></a></li>
							<li><a href="<?php echo esc_url( home_url( '/espace-adherent#section-questions' ) ); ?>"><?php esc_html_e( 'Poser une question', 'cgt' ); ?></a></li>
							<li><a href="<?php echo esc_url( home_url( '/espace-adherent#section-articles-adherents' ) ); ?>"><?php esc_html_e( 'Articles adhérents', 'cgt' ); ?></a></li>
						</ul>
					<?php else : ?>
						<a class="btn" href="<?php echo esc_url( wp_login_url( home_url( '/espace-adherent' ) ) ); ?>"><?php esc_html_e( 'Espace adhérent', 'cgt' ); ?></a>
					<?php endif; ?>
				</li>
			</ul>

		</nav>
	</div>
	</div>
</div>
<div class="site-drawer__overlay" hidden></div>
