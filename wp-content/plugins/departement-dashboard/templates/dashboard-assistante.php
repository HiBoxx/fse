<?php
/**
 * Tableau de bord Assistante.
 *
 * @var array   $adhesions
 * @var WP_User $user
 */

defined( 'ABSPATH' ) || exit;

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<title><?php esc_html_e( 'Tableau de bord Assistante', 'departement-dashboard' ); ?></title>
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'bg-gray-50' ); ?>>
<div class="min-h-screen flex flex-col">
	<header class="bg-blue-800 text-white py-6 shadow">
		<div class="max-w-6xl mx-auto px-6 flex items-center justify-between">
			<div>
				<h1 class="text-2xl font-bold"><?php esc_html_e( 'Tableau de bord — Assistante', 'departement-dashboard' ); ?></h1>
				<p class="text-sm opacity-90"><?php printf( esc_html__( 'Connecté·e en tant que %s', 'departement-dashboard' ), esc_html( $user->display_name ) ); ?></p>
			</div>
			<a href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>" class="bg-white text-blue-800 px-4 py-2 rounded font-semibold"><?php esc_html_e( 'Déconnexion', 'departement-dashboard' ); ?></a>
		</div>
	</header>

	<main class="flex-1 max-w-6xl mx-auto px-6 py-10 space-y-8">
		<section class="bg-white shadow rounded-xl p-6">
			<div class="flex items-center justify-between mb-4">
				<h2 class="text-xl font-semibold flex items-center gap-2"><span>📄</span><?php esc_html_e( 'Liste des adhésions', 'departement-dashboard' ); ?></h2>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<?php wp_nonce_field( 'cgt_dd_export_adhesions' ); ?>
					<input type="hidden" name="action" value="cgt_dd_export_adhesions">
					<button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-800 text-white font-semibold rounded hover:bg-blue-700 transition"><?php esc_html_e( 'Télécharger en PDF', 'departement-dashboard' ); ?></button>
				</form>
			</div>

			<?php if ( ! empty( $adhesions ) ) : ?>
				<div class="grid gap-3">
					<?php foreach ( $adhesions as $item ) : ?>
						<div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
							<div class="flex flex-wrap justify-between gap-2">
								<div>
									<p class="font-semibold text-gray-900 text-lg"><?php echo esc_html( $item['title'] ); ?></p>
									<p class="text-sm text-gray-600"><?php printf( esc_html__( 'Adhésion du %s', 'departement-dashboard' ), esc_html( $item['date'] ) ); ?></p>
								</div>
								<div class="text-right">
									<p class="text-sm text-gray-600"><?php echo esc_html( $item['email'] ); ?></p>
									<span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full <?php echo 'publish' === $item['status'] ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
										<?php echo esc_html( $item['status'] ); ?>
									</span>
								</div>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			<?php else : ?>
				<p class="text-gray-600"><?php esc_html_e( 'Aucune adhésion pour le moment.', 'departement-dashboard' ); ?></p>
			<?php endif; ?>
		</section>
	</main>
</div>
<?php wp_footer(); ?>
</body>
</html>
