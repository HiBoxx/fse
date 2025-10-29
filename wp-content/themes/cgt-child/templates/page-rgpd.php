<?php
/**
 * Template Name: Protection des données (RGPD)
 *
 * @package CGT_Child
 */

get_header();
?>

<main id="primary" class="site-main legal-page">
	<div class="container">
		<header class="page-header">
			<h1 class="page-title"><?php esc_html_e( 'Protection des données personnelles (RGPD)', 'cgt' ); ?></h1>
			<p><?php esc_html_e( 'Fidèle à ses valeurs de défense des travailleurs, la Fédération CGT des Sociétés d’Études s’engage à garantir un usage responsable, transparent et sécurisé des données personnelles de ses adhérents, sympathisants et visiteurs.', 'cgt' ); ?></p>
		</header>

		<article class="legal-content">
			<section>
				<p><?php esc_html_e( 'Les informations collectées via nos formulaires d’adhésion, formulaires de contact, espace adhérents ou inscription à la newsletter / liste de diffusion sont traitées exclusivement par la Fédération, dans le but de :', 'cgt' ); ?></p>
				<ul>
					<li><?php esc_html_e( 'gérer les adhésions syndicales et la vie militante ;', 'cgt' ); ?></li>
					<li><?php esc_html_e( 'répondre aux demandes d’information ou d’assistance ;', 'cgt' ); ?></li>
					<li><?php esc_html_e( 'communiquer sur l’actualité syndicale et les actions de défense des salariés ;', 'cgt' ); ?></li>
					<li><?php esc_html_e( 'améliorer la qualité de nos outils numériques et de nos services.', 'cgt' ); ?></li>
				</ul>
				<p><?php esc_html_e( 'Aucune donnée personnelle n’est vendue, louée ou transmise à des entreprises commerciales ou publicitaires. Nous ne collectons aucune donnée dite sensible au sens de l’article 9 du RGPD, sauf en cas d’obligation légale ou consentement explicite de l’adhérent.', 'cgt' ); ?></p>
			</section>

			<section>
				<h2><?php esc_html_e( 'Durée de conservation', 'cgt' ); ?></h2>
				<p><?php esc_html_e( 'Les données sont conservées pour une durée maximale de 12 mois à compter du dernier contact, sauf si vous demandez leur suppression. En cas de demande explicite, la suppression est effectuée sous 15 jours maximum.', 'cgt' ); ?></p>
			</section>

			<section>
				<h2><?php esc_html_e( 'Cookies', 'cgt' ); ?></h2>
				<p><?php esc_html_e( 'Notre site utilise uniquement des cookies internes strictement nécessaires à son bon fonctionnement (accès sécurisé, maintien de session, espace adhérent, etc.). Aucun cookie publicitaire ou tracker externe n’est utilisé. Vous pouvez gérer vos préférences à tout moment.', 'cgt' ); ?></p>
			</section>

			<section>
				<h2><?php esc_html_e( 'Engagement accessibilité et sécurité', 'cgt' ); ?></h2>
				<p><?php esc_html_e( '🔒 La Fédération respecte le Règlement Général sur la Protection des Données (RGPD) ainsi que le Référentiel Général d’Amélioration de l’Accessibilité (RGAA). Le site est conçu pour être accessible, notamment grâce à un assistant vocal dédié aux personnes en situation de handicap.', 'cgt' ); ?></p>
				<p><?php esc_html_e( 'Aucune donnée n’est transférée hors de l’Union Européenne. L’hébergement est assuré chez OVH (UE – conformité RGPD).', 'cgt' ); ?></p>
			</section>

			<section>
				<h2><?php esc_html_e( 'Vos droits', 'cgt' ); ?></h2>
				<p><?php esc_html_e( 'Conformément aux articles 15 à 22 du RGPD, vous disposez d’un droit :', 'cgt' ); ?></p>
				<ul>
					<li><?php esc_html_e( 'd’accès et de rectification ;', 'cgt' ); ?></li>
					<li><?php esc_html_e( 'd’opposition ;', 'cgt' ); ?></li>
					<li><?php esc_html_e( 'de limitation ou de suppression ;', 'cgt' ); ?></li>
					<li><?php esc_html_e( 'ainsi que d’un droit à la portabilité de vos données.', 'cgt' ); ?></li>
				</ul>
				<p><?php esc_html_e( 'Vous pouvez exercer ces droits à tout moment, sans justification, en écrivant à :', 'cgt' ); ?></p>
				<p><strong><?php esc_html_e( '📩 rgpd.fsetud@cgt.fr', 'cgt' ); ?></strong></p>
			</section>

			<section>
				<h2><?php esc_html_e( 'Contacts officiels', 'cgt' ); ?></h2>
				<ul>
					<li><?php esc_html_e( 'Délégué à la protection des données (DPO) : N. B.', 'cgt' ); ?></li>
					<li><?php esc_html_e( 'Contact RGPD officiel : rgpd.fsetud@cgt.fr', 'cgt' ); ?></li>
					<li><?php esc_html_e( 'Support technique / développement : webmaster.fsetud@cgt.fr', 'cgt' ); ?></li>
					<li><?php esc_html_e( 'Hébergement : OVH – Union Européenne', 'cgt' ); ?></li>
				</ul>
			</section>
		</article>
	</div>
</main>

<?php
get_footer();
