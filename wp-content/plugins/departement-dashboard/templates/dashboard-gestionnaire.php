<?php
/**
 * Tableau de bord Gestionnaire.
 *
 * @var array    $post_types
 * @var WP_User  $user
 */

defined( 'ABSPATH' ) || exit;

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<title><?php esc_html_e( 'Tableau de bord Gestionnaire', 'departement-dashboard' ); ?></title>
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'bg-gray-100' ); ?>>
<div class="min-h-screen flex flex-col">
	<header class="bg-gray-900 text-white py-6 shadow">
		<div class="max-w-4xl mx-auto px-6 flex items-center justify-between">
			<div>
				<h1 class="text-2xl font-bold"><?php esc_html_e( 'Publier un contenu', 'departement-dashboard' ); ?></h1>
				<p class="text-sm opacity-80"><?php printf( esc_html__( 'Gestionnaire connecté·e : %s', 'departement-dashboard' ), esc_html( $user->display_name ) ); ?></p>
			</div>
			<a href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>" class="bg-white text-gray-900 px-4 py-2 rounded font-semibold"><?php esc_html_e( 'Déconnexion', 'departement-dashboard' ); ?></a>
		</div>
	</header>

	<main class="flex-1 max-w-4xl mx-auto px-6 py-10">
		<section class="bg-white rounded-xl shadow p-8">
			<h2 class="text-xl font-semibold mb-6 flex items-center gap-2"><span>📝</span><?php esc_html_e( 'Créer une nouvelle publication', 'departement-dashboard' ); ?></h2>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="space-y-5">
				<?php wp_nonce_field( 'cgt_dd_create_content' ); ?>
				<input type="hidden" name="action" value="cgt_dd_create_content">

				<div>
					<label class="block text-sm font-medium text-gray-700 mb-1"><?php esc_html_e( 'Type de contenu', 'departement-dashboard' ); ?></label>
					<select name="content_type" required class="block w-full rounded-md border-gray-300 shadow-sm focus:ring-red-500 focus:border-red-500">
						<option value=""><?php esc_html_e( 'Sélectionnez', 'departement-dashboard' ); ?></option>
						<?php foreach ( $post_types as $type => $label ) : ?>
							<option value="<?php echo esc_attr( $type ); ?>"><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>

				<div>
					<label class="block text-sm font-medium text-gray-700 mb-1"><?php esc_html_e( 'Titre', 'departement-dashboard' ); ?></label>
					<input type="text" name="content_title" required class="block w-full rounded-md border-gray-300 shadow-sm focus:ring-red-500 focus:border-red-500">
				</div>

				<div>
					<label class="block text-sm font-medium text-gray-700 mb-1"><?php esc_html_e( 'Contenu', 'departement-dashboard' ); ?></label>
					<?php
					wp_editor(
						'',
						'cgt_dd_content_editor',
						array(
							'textarea_name' => 'content_body',
							'media_buttons' => true,
							'textarea_rows' => 10,
							'editor_height' => 260,
						)
					);
					?>
				</div>

				<div class="flex items-center gap-3">
					<input type="checkbox" id="content_private" name="content_private" value="1" class="h-4 w-4 text-red-600 border-gray-300 rounded">
					<label for="content_private" class="text-sm text-gray-700"><?php esc_html_e( 'Marquer comme contenu privé (réservé aux adhérents)', 'departement-dashboard' ); ?></label>
				</div>

				<div class="pt-4">
					<button type="submit" class="inline-flex items-center px-5 py-2 bg-gray-900 text-white font-semibold rounded shadow hover:bg-gray-700 transition">
						<?php esc_html_e( 'Publier', 'departement-dashboard' ); ?>
					</button>
				</div>
			</form>
		</section>
	</main>
</div>
<?php wp_footer(); ?>
</body>
</html>
