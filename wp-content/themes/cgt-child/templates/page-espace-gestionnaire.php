<?php
/**
 * Template Name: Espace Gestionnaire
 * Espace frontend pour le rôle Gestionnaire (publication de contenu)
 *
 * @package CGT_Child
 */

// Check if user is logged in and has correct role
if ( ! is_user_logged_in() ) {
	wp_safe_redirect( wp_login_url( get_permalink() ) );
	exit;
}

$current_user = wp_get_current_user();
if ( ! in_array( 'cgt_gestionnaire', $current_user->roles, true ) && ! current_user_can( 'manage_options' ) ) {
	wp_die( __( 'Vous n\'avez pas accès à cet espace.', 'cgt' ) );
}

get_header();

// Get user's publications
$user_posts = new WP_Query(
	array(
		'author'         => get_current_user_id(),
		'post_type'      => array( 'post', 'tracts' ),
		'post_status'    => array( 'publish', 'private', 'draft' ),
		'posts_per_page' => 10,
		'orderby'        => 'date',
		'order'          => 'DESC',
	)
);

$success_message = isset( $_GET['published'] ) && 'success' === $_GET['published'];
$error_message   = isset( $_GET['published'] ) && 'error' === $_GET['published'];
?>

<main id="primary" class="site-main custom-space">
	<header class="custom-space__header container">
		<div class="custom-space__welcome">
			<div class="custom-space__user">
				<span class="custom-space__icon">✍️</span>
				<div>
					<h1 class="custom-space__title"><?php esc_html_e( 'Espace Gestionnaire', 'cgt' ); ?></h1>
					<p class="custom-space__subtitle"><?php printf( esc_html__( 'Bonjour %s', 'cgt' ), esc_html( $current_user->display_name ) ); ?></p>
				</div>
			</div>
		</div>
		<p><?php esc_html_e( 'Publiez des articles, tracts, pétitions et événements pour le site CGT.', 'cgt' ); ?></p>
	</header>

	<section class="custom-space__content container">
		<?php if ( $success_message ) : ?>
			<div class="alert alert-success">
				<strong>✅ <?php esc_html_e( 'Succès !', 'cgt' ); ?></strong>
				<?php esc_html_e( 'Votre contenu a été publié avec succès.', 'cgt' ); ?>
			</div>
		<?php endif; ?>

		<?php if ( $error_message ) : ?>
			<div class="alert alert-error">
				<strong>❌ <?php esc_html_e( 'Erreur !', 'cgt' ); ?></strong>
				<?php esc_html_e( 'Une erreur est survenue lors de la publication.', 'cgt' ); ?>
			</div>
		<?php endif; ?>

		<!-- Statistics -->
		<div class="custom-space__stats">
			<div class="stat-card">
				<div class="stat-card__icon">📚</div>
				<div class="stat-card__content">
					<div class="stat-card__value"><?php echo esc_html( $user_posts->found_posts ); ?></div>
					<div class="stat-card__label"><?php esc_html_e( 'Mes publications', 'cgt' ); ?></div>
				</div>
			</div>
		</div>

		<!-- Publication Form -->
		<div class="custom-panel">
			<h2><?php esc_html_e( 'Publier un contenu', 'cgt' ); ?></h2>
			<p><?php esc_html_e( 'Remplissez le formulaire ci-dessous pour publier un nouveau contenu.', 'cgt' ); ?></p>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="custom-form">
				<?php wp_nonce_field( 'cgt_publish_content', 'cgt_publish_nonce' ); ?>
				<input type="hidden" name="action" value="cgt_publish_content">

				<div class="form-group">
					<label for="content_type"><?php esc_html_e( 'Type de contenu *', 'cgt' ); ?></label>
					<select id="content_type" name="content_type" required>
						<option value=""><?php esc_html_e( '— Choisir un type —', 'cgt' ); ?></option>
						<option value="article"><?php esc_html_e( 'Article', 'cgt' ); ?></option>
						<option value="tract"><?php esc_html_e( 'Tract', 'cgt' ); ?></option>
						<option value="petition"><?php esc_html_e( 'Pétition', 'cgt' ); ?></option>
						<option value="evenement"><?php esc_html_e( 'Événement', 'cgt' ); ?></option>
					</select>
				</div>

				<div class="form-group">
					<label for="content_title"><?php esc_html_e( 'Titre *', 'cgt' ); ?></label>
					<input type="text" id="content_title" name="content_title" required>
				</div>

				<div class="form-group">
					<label for="content_content"><?php esc_html_e( 'Contenu *', 'cgt' ); ?></label>
					<?php
					wp_editor(
						'',
						'content_content',
						array(
							'textarea_name' => 'content_content',
							'media_buttons' => true,
							'textarea_rows' => 12,
							'teeny'         => false,
							'tinymce'       => true,
							'quicktags'     => true,
						)
					);
					?>
				</div>

				<div class="form-group">
					<label><?php esc_html_e( 'Visibilité', 'cgt' ); ?></label>
					<div class="radio-group">
						<label class="radio-label">
							<input type="radio" name="content_visibility" value="public" checked>
							<span><?php esc_html_e( 'Public', 'cgt' ); ?></span>
						</label>
						<label class="radio-label">
							<input type="radio" name="content_visibility" value="prive">
							<span><?php esc_html_e( 'Privé (réservé aux adhérents)', 'cgt' ); ?></span>
						</label>
					</div>
				</div>

				<button type="submit" class="btn btn-primary"><?php esc_html_e( 'Publier maintenant', 'cgt' ); ?></button>
			</form>
		</div>

		<!-- Recent Publications -->
		<div class="custom-panel">
			<h2><?php esc_html_e( 'Mes dernières publications', 'cgt' ); ?></h2>
			<?php if ( $user_posts->have_posts() ) : ?>
				<div class="publications-list">
					<?php
					while ( $user_posts->have_posts() ) :
						$user_posts->the_post();
						$status_label = get_post_status() === 'publish' ? '✅ Publié' : '🔒 Privé';
						?>
						<div class="publication-item">
							<div class="publication-item__content">
								<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
								<div class="publication-item__meta">
									<span class="publication-item__date"><?php echo esc_html( get_the_date( 'd/m/Y' ) ); ?></span>
									<span class="publication-item__separator">·</span>
									<span class="publication-item__type"><?php echo esc_html( get_post_type_object( get_post_type() )->labels->singular_name ); ?></span>
									<span class="publication-item__separator">·</span>
									<span class="publication-item__status"><?php echo esc_html( $status_label ); ?></span>
								</div>
							</div>
						</div>
					<?php endwhile; ?>
				</div>
			<?php else : ?>
				<p><?php esc_html_e( 'Vous n\'avez pas encore publié de contenu.', 'cgt' ); ?></p>
			<?php endif; ?>
			<?php wp_reset_postdata(); ?>
		</div>
	</section>
</main>

<?php
get_footer();
