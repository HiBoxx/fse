<?php
/**
 * Header partial.
 *
 * @package CGT_Child
 */

$logo_url        = cgt_child_get_logo_url();
$posts_page_id   = (int) get_option( 'page_for_posts' );
$posts_page_link = $posts_page_id ? get_permalink( $posts_page_id ) : home_url( '/actualites' );
$classes_page    = get_page_by_path( 'classes' );
$classes_base    = $classes_page ? get_permalink( $classes_page ) : home_url( '/classes/' );

if ( ! function_exists( 'cgt_classes_link' ) ) {
	function cgt_classes_link( $slug, $base_url ) {
		return esc_url( add_query_arg( 'classe', $slug, $base_url ) );
	}
}
?>

<header class="header-bar" role="banner">
	<div class="container header-inner">
		<a class="site-logo site-logo--image" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
			<img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php esc_attr_e( 'FSETUD – Fédération CGT des Sociétés d’Études', 'cgt' ); ?>">
			<span class="sr-only"><?php esc_html_e( 'Retour à la page d’accueil', 'cgt' ); ?></span>
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
						<li><a href="<?php echo cgt_classes_link( 'presentation-vie-federale', $classes_base ); ?>"><?php esc_html_e( 'Présentation / Vie fédérale', 'cgt' ); ?></a></li>
						<li><a href="<?php echo cgt_classes_link( 'communication-federale', $classes_base ); ?>"><?php esc_html_e( 'Communication fédérale', 'cgt' ); ?></a></li>
						<li><a href="<?php echo cgt_classes_link( 'commissions-executives', $classes_base ); ?>"><?php esc_html_e( 'Commissions exécutives', 'cgt' ); ?></a></li>
						<li><a href="<?php echo cgt_classes_link( 'congres', $classes_base ); ?>"><?php esc_html_e( 'Congrès', 'cgt' ); ?></a></li>
					</ul>
				</li>
				<li>
					<h3 class="drawer-section__title" aria-hidden="true"><?php esc_html_e( 'Publications', 'cgt' ); ?></h3>
					<ul class="drawer-menu">
						<li><a href="<?php echo cgt_classes_link( 'actualites', $classes_base ); ?>"><?php esc_html_e( 'Actualités', 'cgt' ); ?></a></li>
						<li><a href="<?php echo cgt_classes_link( 'le-lien-syndical', $classes_base ); ?>"><?php esc_html_e( 'Le Lien Syndical', 'cgt' ); ?></a></li>
						<li><a href="<?php echo cgt_classes_link( 'brochures-analyses', $classes_base ); ?>"><?php esc_html_e( 'Brochures & Analyses', 'cgt' ); ?></a></li>
						<li><a href="<?php echo cgt_classes_link( 'dossiers-thematiques', $classes_base ); ?>"><?php esc_html_e( 'Dossiers thématiques', 'cgt' ); ?></a></li>
					</ul>
				</li>
				<li>
					<h3 class="drawer-section__title" aria-hidden="true"><?php esc_html_e( 'Actualités des branches', 'cgt' ); ?></h3>
					<ul class="drawer-menu">
						<li><a href="<?php echo cgt_classes_link( 'bulletins', $classes_base ); ?>"><?php esc_html_e( 'Bulletins', 'cgt' ); ?></a></li>
						<li><a href="<?php echo cgt_classes_link( 'conventions-collectives', $classes_base ); ?>"><?php esc_html_e( 'Conventions collectives', 'cgt' ); ?></a></li>
						<li><a href="<?php echo cgt_classes_link( 'tracts-entreprise', $classes_base ); ?>"><?php esc_html_e( 'Tracts d’entreprise', 'cgt' ); ?></a></li>
						<li><a href="<?php echo cgt_classes_link( 'tracts-federation', $classes_base ); ?>"><?php esc_html_e( 'Tracts de la fédération', 'cgt' ); ?></a></li>
					</ul>
				</li>
				<li>
					<h3 class="drawer-section__title" aria-hidden="true"><?php esc_html_e( 'Engagement', 'cgt' ); ?></h3>
					<ul class="drawer-menu">
						<li><a href="<?php echo cgt_classes_link( 'syndicalisation', $classes_base ); ?>"><?php esc_html_e( 'Syndicalisation', 'cgt' ); ?></a></li>
						<li><a href="<?php echo cgt_classes_link( 'creer-un-syndicat', $classes_base ); ?>"><?php esc_html_e( 'Créer un syndicat', 'cgt' ); ?></a></li>
						<li><a href="<?php echo cgt_classes_link( 'elections-professionnelles', $classes_base ); ?>"><?php esc_html_e( 'Élections professionnelles', 'cgt' ); ?></a></li>
						<li><a href="<?php echo cgt_classes_link( 'formation-syndicale', $classes_base ); ?>"><?php esc_html_e( 'Formation syndicale', 'cgt' ); ?></a></li>
					</ul>
				</li>
				<li>
					<h3 class="drawer-section__title" aria-hidden="true"><?php esc_html_e( 'Nos outils & ressources', 'cgt' ); ?></h3>
					<ul class="drawer-menu">
						<li><a href="<?php echo cgt_classes_link( 'agenda-social', $classes_base ); ?>"><?php esc_html_e( 'Agenda social', 'cgt' ); ?></a></li>
						<li><a href="<?php echo cgt_classes_link( 'modeles-lettre', $classes_base ); ?>"><?php esc_html_e( 'Modèles de lettre', 'cgt' ); ?></a></li>
						<li><a href="<?php echo cgt_classes_link( 'guides', $classes_base ); ?>"><?php esc_html_e( 'Guides', 'cgt' ); ?></a></li>
						<li><a href="<?php echo cgt_classes_link( 'adresses-utiles', $classes_base ); ?>"><?php esc_html_e( 'Adresses utiles', 'cgt' ); ?></a></li>
						<li><a href="<?php echo home_url( '/enquetes/' ); ?>"><?php esc_html_e( 'Enquêtes', 'cgt' ); ?></a></li>
						<li><a href="<?php echo cgt_classes_link( 'simulateur-calcul', $classes_base ); ?>"><?php esc_html_e( 'Simulateur de calcul', 'cgt' ); ?></a></li>
					</ul>
				</li>
				<li>
					<h3 class="drawer-section__title" aria-hidden="true"><?php esc_html_e( 'Espace presse', 'cgt' ); ?></h3>
					<ul class="drawer-menu">
						<li><a href="<?php echo cgt_classes_link( 'communiques-de-presse', $classes_base ); ?>"><?php esc_html_e( 'Communiqués de presse', 'cgt' ); ?></a></li>
						<li><a href="<?php echo cgt_classes_link( 'revues-de-presse', $classes_base ); ?>"><?php esc_html_e( 'Revues de presse', 'cgt' ); ?></a></li>
						<li><a href="<?php echo cgt_classes_link( 'international', $classes_base ); ?>"><?php esc_html_e( 'International', 'cgt' ); ?></a></li>
					</ul>
				</li>
			</ul>

		</nav>
	</div>
	</div>
</div>
<div class="site-drawer__overlay" hidden></div>
