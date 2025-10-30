<?php
/**
 * Template Name: Espace Administration
 * Espace frontend pour le rôle Administration (gestion adhérents + banque)
 *
 * @package CGT_Child
 */

// Check if user is logged in and has correct role
if ( ! is_user_logged_in() ) {
	wp_safe_redirect( wp_login_url( get_permalink() ) );
	exit;
}

$current_user = wp_get_current_user();
if ( ! in_array( 'cgt_administration', $current_user->roles, true ) && ! current_user_can( 'manage_options' ) ) {
	wp_die( __( 'Vous n\'avez pas accès à cet espace.', 'cgt' ) );
}

get_header();

// Get adhesions
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
$pending_count   = wp_count_posts( 'cgt_adhesion' )->pending ?? 0;
?>

<main id="primary" class="site-main custom-space">
	<header class="custom-space__header container">
		<div class="custom-space__welcome">
			<div class="custom-space__user">
				<span class="custom-space__icon">👤</span>
				<div>
					<h1 class="custom-space__title"><?php esc_html_e( 'Espace Administration', 'cgt' ); ?></h1>
					<p class="custom-space__subtitle"><?php printf( esc_html__( 'Bonjour %s', 'cgt' ), esc_html( $current_user->display_name ) ); ?></p>
				</div>
			</div>
		</div>
		<p><?php esc_html_e( 'Gérez les adhérents, les adhésions et les informations bancaires.', 'cgt' ); ?></p>
	</header>

	<section class="custom-space__content container">
		<!-- Statistics Cards -->
		<div class="custom-space__stats">
			<div class="stat-card">
				<div class="stat-card__icon">👥</div>
				<div class="stat-card__content">
					<div class="stat-card__value"><?php echo esc_html( $total_adhesions ); ?></div>
					<div class="stat-card__label"><?php esc_html_e( 'Adhésions totales', 'cgt' ); ?></div>
				</div>
			</div>
			<div class="stat-card">
				<div class="stat-card__icon">⏳</div>
				<div class="stat-card__content">
					<div class="stat-card__value"><?php echo esc_html( $pending_count ); ?></div>
					<div class="stat-card__label"><?php esc_html_e( 'En attente', 'cgt' ); ?></div>
				</div>
			</div>
			<div class="stat-card">
				<div class="stat-card__icon">💳</div>
				<div class="stat-card__content">
					<div class="stat-card__label"><?php esc_html_e( 'Section Banque', 'cgt' ); ?></div>
					<a href="#section-banque" class="btn btn-compact"><?php esc_html_e( 'Accéder', 'cgt' ); ?></a>
				</div>
			</div>
		</div>

		<!-- Adhesions Management -->
		<div class="custom-panel">
			<h2><?php esc_html_e( 'Gestion des adhésions', 'cgt' ); ?></h2>
			<div class="table-responsive">
				<table class="custom-table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Nom', 'cgt' ); ?></th>
							<th><?php esc_html_e( 'Prénom', 'cgt' ); ?></th>
							<th><?php esc_html_e( 'Email', 'cgt' ); ?></th>
							<th><?php esc_html_e( 'Téléphone', 'cgt' ); ?></th>
							<th><?php esc_html_e( 'Date', 'cgt' ); ?></th>
							<th><?php esc_html_e( 'Actions', 'cgt' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php if ( $adhesions_query->have_posts() ) : ?>
							<?php
							while ( $adhesions_query->have_posts() ) :
								$adhesions_query->the_post();
								$post_id = get_the_ID();
								$nom     = get_post_meta( $post_id, '_adhesion_nom', true );
								$prenom  = get_post_meta( $post_id, '_adhesion_prenom', true );
								$email   = get_post_meta( $post_id, '_adhesion_email', true );
								$tel     = get_post_meta( $post_id, '_adhesion_tel', true );
								?>
								<tr>
									<td><?php echo esc_html( $nom ); ?></td>
									<td><?php echo esc_html( $prenom ); ?></td>
									<td><a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a></td>
									<td><?php echo esc_html( $tel ); ?></td>
									<td><?php echo esc_html( get_the_date( 'd/m/Y' ) ); ?></td>
									<td class="table-actions">
										<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=cgt_download_adhesion_pdf&post_id=' . $post_id ), 'cgt_download_pdf_' . $post_id ) ); ?>" class="btn-icon" title="<?php esc_attr_e( 'Télécharger PDF', 'cgt' ); ?>">
											📄
										</a>
										<a href="<?php echo esc_url( admin_url( 'post.php?post=' . $post_id . '&action=edit' ) ); ?>" class="btn-icon" title="<?php esc_attr_e( 'Modifier', 'cgt' ); ?>">
											✏️
										</a>
										<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'post.php?post=' . $post_id . '&action=delete' ), 'delete-post_' . $post_id ) ); ?>" class="btn-icon btn-danger" title="<?php esc_attr_e( 'Supprimer', 'cgt' ); ?>" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette adhésion ?');">
											🗑️
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

		<!-- Bank Section -->
		<div class="custom-panel" id="section-banque">
			<h2><?php esc_html_e( 'Section Banque', 'cgt' ); ?></h2>
			<p><?php esc_html_e( 'Enregistrez les informations bancaires (RIB) et les mandats SEPA signés.', 'cgt' ); ?></p>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data" class="custom-form">
				<?php wp_nonce_field( 'cgt_bank_submit', 'cgt_bank_nonce' ); ?>
				<input type="hidden" name="action" value="cgt_bank_submit">

				<div class="form-row">
					<div class="form-group">
						<label for="bank_nom"><?php esc_html_e( 'Nom *', 'cgt' ); ?></label>
						<input type="text" id="bank_nom" name="bank_nom" required>
					</div>
					<div class="form-group">
						<label for="bank_prenom"><?php esc_html_e( 'Prénom *', 'cgt' ); ?></label>
						<input type="text" id="bank_prenom" name="bank_prenom" required>
					</div>
				</div>

				<div class="form-group">
					<label for="bank_rib"><?php esc_html_e( 'RIB / IBAN *', 'cgt' ); ?></label>
					<input type="text" id="bank_rib" name="bank_rib" required placeholder="FR76 XXXX XXXX XXXX XXXX XXXX XXX">
				</div>

				<div class="form-group">
					<label for="bank_pdf"><?php esc_html_e( 'Mandat SEPA signé (PDF)', 'cgt' ); ?></label>
					<input type="file" id="bank_pdf" name="bank_pdf" accept=".pdf">
					<small><?php esc_html_e( 'Taille max : 10 MB', 'cgt' ); ?></small>
				</div>

				<button type="submit" class="btn btn-primary"><?php esc_html_e( 'Enregistrer le mandat', 'cgt' ); ?></button>
			</form>

			<!-- List of existing mandats -->
			<h3><?php esc_html_e( 'Mandats enregistrés', 'cgt' ); ?></h3>
			<?php
			$mandats = get_posts(
				array(
					'post_type'      => 'cgt_mandat',
					'post_status'    => 'publish',
					'posts_per_page' => 20,
					'orderby'        => 'date',
					'order'          => 'DESC',
				)
			);
			?>
			<?php if ( $mandats ) : ?>
				<div class="table-responsive">
					<table class="custom-table">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Nom', 'cgt' ); ?></th>
								<th><?php esc_html_e( 'Prénom', 'cgt' ); ?></th>
								<th><?php esc_html_e( 'RIB', 'cgt' ); ?></th>
								<th><?php esc_html_e( 'Date', 'cgt' ); ?></th>
								<th><?php esc_html_e( 'PDF', 'cgt' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $mandats as $mandat ) : ?>
								<tr>
									<td><?php echo esc_html( get_post_meta( $mandat->ID, '_mandat_nom', true ) ); ?></td>
									<td><?php echo esc_html( get_post_meta( $mandat->ID, '_mandat_prenom', true ) ); ?></td>
									<td><?php echo esc_html( get_post_meta( $mandat->ID, '_mandat_rib', true ) ); ?></td>
									<td><?php echo esc_html( get_the_date( 'd/m/Y', $mandat->ID ) ); ?></td>
									<td>
										<?php
										$pdf_id  = get_post_meta( $mandat->ID, '_mandat_pdf_id', true );
										$pdf_url = $pdf_id ? wp_get_attachment_url( $pdf_id ) : '';
										?>
										<?php if ( $pdf_url ) : ?>
											<a href="<?php echo esc_url( $pdf_url ); ?>" target="_blank" class="btn btn-compact">📄 <?php esc_html_e( 'Télécharger', 'cgt' ); ?></a>
										<?php else : ?>
											<span>—</span>
										<?php endif; ?>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			<?php else : ?>
				<p><?php esc_html_e( 'Aucun mandat enregistré.', 'cgt' ); ?></p>
			<?php endif; ?>
		</div>
	</section>
</main>

<?php
get_footer();
