<?php
/**
 * Contact page template.
 *
 * @package CGT_Child
 */

get_header();
?>

<main id="primary" class="site-main contact-page contact-page--stacked">
	<section class="page-hero">
		<div class="container">
			<h1 class="page-hero__title contact-page__title"><?php esc_html_e( 'Contactez-nous', 'cgt' ); ?></h1>
			<p class="page-hero__intro contact-page__intro"><?php esc_html_e( 'Vous avez une question d’ordre juridique ou souhaitez des précisions sur nos activités ? Nous sommes à votre écoute.', 'cgt' ); ?></p>
		</div>
	</section>

	<div class="container contact-sections">
		<section class="contact-block">
			<h2><?php esc_html_e( 'Nous écrire', 'cgt' ); ?></h2>
			<p><?php esc_html_e( 'Vous avez une question d\'ordre juridique ou vous souhaitez des précisions sur notre activité dans un secteur précis, ou encore sur les conditions d\'adhésion ? Quel que soit votre besoin, contactez-nous via Internet, téléphone ou voie postale. Nous vous répondrons dans les meilleurs délais.', 'cgt' ); ?></p>
			<p><?php esc_html_e( 'Le cas échéant, nous pouvons vous recevoir dans nos locaux sur rendez-vous uniquement.', 'cgt' ); ?></p>
		</section>

		<section class="contact-block contact-block--form">
			<h2><?php esc_html_e( 'Contactez-nous via Internet', 'cgt' ); ?></h2>
			<p><?php esc_html_e( 'Veuillez compléter le formulaire ci-dessous pour poser votre question.', 'cgt' ); ?></p>
			<?php echo do_shortcode( '[cgt_contact_form]' ); ?>
		</section>

		<section class="contact-block">
			<h2><?php esc_html_e( 'Contact par voie postale ou téléphonique', 'cgt' ); ?></h2>
			<p><?php esc_html_e( 'Vous pouvez aussi nous joindre par voie postale ou téléphonique aux coordonnées ci-dessous :', 'cgt' ); ?></p>
			<address>
				<strong><?php esc_html_e( 'Fédération CGT des Sociétés d\'Etudes', 'cgt' ); ?></strong><br>
				<?php esc_html_e( '263, rue de Paris - Case 421 - 93514 Montreuil cedex', 'cgt' ); ?><br>
				<?php esc_html_e( 'Tel. : +33 1 55 82 89 41 - Fax. : +33 1 55 82 89 42', 'cgt' ); ?>
			</address>
		</section>

		<section class="contact-block contact-block--map">
			<h2><?php esc_html_e( 'Pour venir nous rencontrer', 'cgt' ); ?></h2>
			<p><?php esc_html_e( 'Le siège de la Fédération CGT des Sociétés d\'Etudes est situé à Montreuil (Seine-Saint-Denis), à quelques mètres de Paris, Porte de Montreuil.', 'cgt' ); ?></p>
			<div class="contact-map">
				<iframe src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d10500.790245258937!2d2.416309!3d48.854443!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x47e67281e14c7b45%3A0x92c098e3186757a9!2s263%20Rue%20de%20Paris%2C%2093100%20Montreuil!5e0!3m2!1sfr!2sfr!4v1644042953753!5m2!1sfr!2sfr" width="600" height="360" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
			</div>
			<ul class="contact-access">
				<li><?php esc_html_e( 'Par la route : Périphérique, sortie Porte-de-Montreuil.', 'cgt' ); ?></li>
				<li><?php esc_html_e( 'En métro : Porte-de-Montreuil ou Robespierre (Ligne 9).', 'cgt' ); ?></li>
				<li><?php esc_html_e( 'En bus/Tram : Lignes 57, 215, 351, Tram 3b (arrêts Porte-de-Montreuil), 318 (Etienne-Marcel).', 'cgt' ); ?></li>
			</ul>
		</section>
	</div>
</main>

<?php
get_footer();
