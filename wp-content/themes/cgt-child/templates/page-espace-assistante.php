<?php
/**
 * Template Name: Espace Assistante
 * Espace frontend pour le rôle Assistante (lecture seule)
 *
 * @package CGT_Child
 */

// Check if user is logged in and has correct role
if ( ! is_user_logged_in() ) {
	wp_safe_redirect( wp_login_url( get_permalink() ) );
	exit;
}

$current_user = wp_get_current_user();
if ( ! in_array( 'cgt_assistante', $current_user->roles, true ) && ! current_user_can( 'manage_options' ) ) {
	wp_die( __( 'Vous n\'avez pas accès à cet espace.', 'cgt' ) );
}

get_header();

// Get adhesions (read-only)
$adhesions_query = new WP_Query(
	array(
		'post_type'      => 'cgt_adhesion',
		'post_status'    => array( 'pending', 'publish', 'draft' ),
		'posts_per_page' => 50,
		'orderby'        => 'date',
		'order'          => 'DESC',
	)
);

$total_adhesions = $adhesions_query->found_posts;
?>

<main id="primary" class="site-main custom-space">
	<header class="custom-space__header container">
		<div class="custom-space__welcome">
			<div class="custom-space__user">
				<span class="custom-space__icon">👁️</span>
				<div>
					<h1 class="custom-space__title"><?php esc_html_e( 'Espace Assistante', 'cgt' ); ?></h1>
					<p class="custom-space__subtitle"><?php printf( esc_html__( 'Bonjour %s', 'cgt' ), esc_html( $current_user->display_name ) ); ?></p>
				</div>
			</div>
		</div>
		<p><?php esc_html_e( 'Consultation des adhésions en lecture seule.', 'cgt' ); ?></p>
	</header>

	<section class="custom-space__content container">
		<!-- Statistics -->
		<div class="custom-space__stats">
			<div class="stat-card">
				<div class="stat-card__icon">👥</div>
				<div class="stat-card__content">
					<div class="stat-card__value"><?php echo esc_html( $total_adhesions ); ?></div>
					<div class="stat-card__label"><?php esc_html_e( 'Adhésions totales', 'cgt' ); ?></div>
				</div>
			</div>
		</div>

		<!-- Adhesions List (Read-Only) -->
		<div class="custom-panel">
			<h2><?php esc_html_e( 'Liste des adhérents', 'cgt' ); ?></h2>
			<div class="alert alert-info">
				<strong>ℹ️ <?php esc_html_e( 'Mode lecture seule', 'cgt' ); ?></strong>
				<?php esc_html_e( 'Vous pouvez consulter les adhésions et télécharger les PDF, mais vous ne pouvez pas les modifier.', 'cgt' ); ?>
			</div>

			<div class="table-responsive">
				<table class="custom-table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Nom', 'cgt' ); ?></th>
							<th><?php esc_html_e( 'Prénom', 'cgt' ); ?></th>
							<th><?php esc_html_e( 'Date d\'adhésion', 'cgt' ); ?></th>
							<th><?php esc_html_e( 'Email', 'cgt' ); ?></th>
							<th><?php esc_html_e( 'Statut', 'cgt' ); ?></th>
							<th><?php esc_html_e( 'PDF', 'cgt' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php if ( $adhesions_query->have_posts() ) : ?>
							<?php
							while ( $adhesions_query->have_posts() ) :
								$adhesions_query->the_post();
								$post_id      = get_the_ID();
								$nom          = get_post_meta( $post_id, '_adhesion_nom', true );
								$prenom       = get_post_meta( $post_id, '_adhesion_prenom', true );
								$email        = get_post_meta( $post_id, '_adhesion_email', true );
								$status       = get_post_status();
								$status_label = $status === 'pending' ? '⏳ En attente' : '✅ Validée';
								?>
								<tr>
									<td><?php echo esc_html( $nom ); ?></td>
									<td><?php echo esc_html( $prenom ); ?></td>
									<td><?php echo esc_html( get_the_date( 'd/m/Y' ) ); ?></td>
									<td><?php echo esc_html( $email ); ?></td>
									<td><?php echo esc_html( $status_label ); ?></td>
									<td>
										<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=cgt_download_adhesion_pdf&post_id=' . $post_id ), 'cgt_download_pdf_' . $post_id ) ); ?>" class="btn btn-compact">
											📄 <?php esc_html_e( 'Télécharger', 'cgt' ); ?>
										</a>
									</td>
								</tr>
							<?php endwhile; ?>
						<?php else : ?>
							<tr>
								<td colspan="6" class="text-center"><?php esc_html_e( 'Aucune adhésion enregistrée.', 'cgt' ); ?></td>
							</tr>
						<?php endif; ?>
					</tbody>
				</table>
			</div>
		</div>
		<?php wp_reset_postdata(); ?>
	</section>
</main>

<?php
get_footer();
