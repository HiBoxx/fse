<?php
/**
 * Footer partial.
 *
 * @package CGT_Child
 */
?>

<footer class="site-footer">
	<div class="container footer-grid">
		<div class="footer-brand">
			<p class="footer-slogan"><?php esc_html_e( 'Soutenons les salarié·es des sociétés d’études, du conseil et de l’ingénierie.', 'cgt' ); ?></p>
		</div>
		<div class="footer-links">
			<h2 class="footer-title"><?php esc_html_e( 'Liens utiles', 'cgt' ); ?></h2>
			<ul class="footer-list footer-list--compact">
				<li><a href="<?php echo esc_url( home_url( '/mentions-legales' ) ); ?>"><?php esc_html_e( 'Mentions légales', 'cgt' ); ?></a></li>
				<li><a href="<?php echo esc_url( home_url( '/politique-de-confidentialite' ) ); ?>"><?php esc_html_e( 'Politique de confidentialité', 'cgt' ); ?></a></li>
				<li><a href="<?php echo esc_url( home_url( '/contact' ) ); ?>"><?php esc_html_e( 'Contact', 'cgt' ); ?></a></li>
			</ul>
		</div>
		<div class="footer-social">
			<h2 class="footer-title"><?php esc_html_e( 'Réseaux sociaux', 'cgt' ); ?></h2>
			<ul class="footer-list footer-list--compact">
				<li><a href="#"><?php esc_html_e( 'Facebook', 'cgt' ); ?></a></li>
				<li><a href="#"><?php esc_html_e( 'Twitter', 'cgt' ); ?></a></li>
				<li><a href="#"><?php esc_html_e( 'YouTube', 'cgt' ); ?></a></li>
			</ul>
		</div>
	</div>
	<div class="footer-bottom">
		<p>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php esc_html_e( 'CGT', 'cgt' ); ?> — <?php esc_html_e( 'Fédération CGT des Sociétés d’Études, tous droits réservés.', 'cgt' ); ?></p>
	</div>
</footer>
