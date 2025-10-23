<?php
/**
 * Archive template for tracts.
 *
 * @package CGT_Child
 */

get_header();
?>
<main id="primary" class="site-main container">
	<header class="archive-header">
		<h1><?php post_type_archive_title(); ?></h1>
		<p><?php esc_html_e( 'Téléchargez les derniers tracts publiés par la CGT.', 'cgt' ); ?></p>
	</header>

<?php
$current_branch = get_query_var( 'branche' );

$branch_groups = array(
	__( 'Bureaux d’études techniques et de conseil', 'cgt' ) => array(
		'associations-agreees-qualite-air'          => __( 'Associations agréées de surveillance de la qualité de l’air', 'cgt' ),
		'conseil-architecture-urbanisme-environnement' => __( 'Conseil d’Architecture d’Urbanisme et d’Environnement', 'cgt' ),
		'etudes-conseil'                             => __( 'Études et conseil', 'cgt' ),
		'foires-salons'                              => __( 'Foires et salons', 'cgt' ),
		'formation-professionnelle'                  => __( 'Formation professionnelle', 'cgt' ),
		'informatique'                               => __( 'Informatique', 'cgt' ),
		'ingenierie'                                 => __( 'Ingénierie', 'cgt' ),
		'organismes-controle-prevention'            => __( 'Organismes de Contrôle et de Prévention', 'cgt' ),
		'sondage'                                    => __( 'Sondage', 'cgt' ),
	),
	__( 'Experts comptables et commissaires aux comptes', 'cgt' ) => array(
		'associations-gestion-comptable' => __( 'Associations de gestion comptable', 'cgt' ),
	),
	__( 'Officines judiciaires et parajudiciaires', 'cgt' ) => array(
		'avocats'                               => __( 'Avocats', 'cgt' ),
		'avocats-salaries'                      => __( 'Avocats salariés', 'cgt' ),
		'salaries-cabinets-avocats'             => __( 'Salariés des cabinets d’avocats', 'cgt' ),
		'avoues'                                => __( 'Avoués', 'cgt' ),
		'commissaires-justice'                  => __( 'Commissaires de justice', 'cgt' ),
		'commissaires-priseurs'                 => __( 'Commissaires priseurs', 'cgt' ),
		'huissiers-justice'                     => __( 'Huissiers de justice', 'cgt' ),
		'notariat'                              => __( 'Notariat', 'cgt' ),
		'professions-reglementees-juridictions' => __( 'Professions réglementées auprès des juridictions', 'cgt' ),
		'administrateurs-mandataires-judiciaires' => __( 'Administrateurs et mandataires judiciaires', 'cgt' ),
		'avocats-cour-cassation-conseil-etat'   => __( 'Avocats à la Cour de Cassation et au Conseil d’État', 'cgt' ),
		'greffes-tribunaux-commerce'            => __( 'Greffes des tribunaux de commerce', 'cgt' ),
	),
	__( 'Prestataires de services', 'cgt' ) => array(
		'accueil-entreprise'        => __( 'Accueil en entreprise', 'cgt' ),
		'animations-commerciales'    => __( 'Animations commerciales', 'cgt' ),
		'centres-appels'            => __( 'Centres d’appels', 'cgt' ),
		'enquete-civile'            => __( 'Enquête civile', 'cgt' ),
		'location-bureaux-salles'   => __( 'Location de bureaux et de salles', 'cgt' ),
		'recouvrement-creances'     => __( 'Recouvrement de créances', 'cgt' ),
		'tele-secretariat'          => __( 'Télé-secrétariat', 'cgt' ),
		'traduction'                => __( 'Traduction', 'cgt' ),
	),
);

$branch_singles = array(
	'cooperative-activite-emploi'              => __( 'Coopérative d’activité et d’emploi', 'cgt' ),
	'expertises-evaluations-industrie-commerce' => __( 'Expertises en matière d’évaluations industrielles et commerciales', 'cgt' ),
	'experts-automobiles'                       => __( 'Experts automobiles', 'cgt' ),
	'organismes-developpement-economique'      => __( 'Organismes de développement économique', 'cgt' ),
	'portage-salarial'                         => __( 'Portage salarial', 'cgt' ),
	'snaii'                                    => __( 'Syndicat National des Auteurs d’Invention Indépendants (SNAII)', 'cgt' ),
);
?>

	<form class="filters" method="get">
		<label class="sr-only" for="tracts-branch-select"><?php esc_html_e( 'Branches', 'cgt' ); ?></label>
		<select id="tracts-branch-select" name="branche">
			<option value=""><?php esc_html_e( 'Toutes les branches', 'cgt' ); ?></option>
			<?php foreach ( $branch_groups as $group_label => $group_options ) : ?>
				<optgroup label="<?php echo esc_attr( $group_label ); ?>">
					<?php foreach ( $group_options as $slug => $label ) : ?>
						<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $current_branch, $slug ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</optgroup>
			<?php endforeach; ?>
			<?php foreach ( $branch_singles as $slug => $label ) : ?>
				<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $current_branch, $slug ); ?>><?php echo esc_html( $label ); ?></option>
			<?php endforeach; ?>
		</select>
		<button class="btn" type="submit"><?php esc_html_e( 'Filtrer', 'cgt' ); ?></button>
	</form>

	<?php if ( have_posts() ) : ?>
		<div class="document-grid">
			<?php
			while ( have_posts() ) :
				the_post();
				$visibility = get_post_meta( get_the_ID(), 'cgt_visibilite', true );
				$pdf_url    = get_post_meta( get_the_ID(), 'cgt_fichier_pdf', true );
				?>
				<article <?php post_class( 'card' ); ?>>
					<div class="placeholder" aria-hidden="true"></div>
					<header class="card-header">
						<span class="card-meta"><?php echo esc_html( get_the_date() ); ?></span>
						<h2 class="card-title"><?php the_title(); ?></h2>
					</header>
					<?php if ( $pdf_url && ( 'prive' !== $visibility || cgt_user_can_read_private() ) ) : ?>
						<a class="btn btn-compact" href="<?php echo esc_url( $pdf_url ); ?>">
							<?php esc_html_e( 'Télécharger le PDF', 'cgt' ); ?>
							<?php if ( 'prive' === $visibility ) : ?>
								<span class="btn-subtag"><?php esc_html_e( '(Réservé adhérents)', 'cgt' ); ?></span>
							<?php endif; ?>
						</a>
					<?php elseif ( 'prive' === $visibility ) : ?>
						<p><?php esc_html_e( 'Connectez-vous pour accéder au PDF.', 'cgt' ); ?></p>
					<?php endif; ?>
				</article>
			<?php endwhile; ?>
		</div>
		<?php the_posts_pagination(); ?>
	<?php else : ?>
		<p><?php esc_html_e( 'Aucun tract actuellement.', 'cgt' ); ?></p>
	<?php endif; ?>
</main>
<?php
get_footer();
