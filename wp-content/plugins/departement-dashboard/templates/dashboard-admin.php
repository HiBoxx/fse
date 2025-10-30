<?php
/**
 * Tableau de bord Administration.
 *
 * @var array $adhesions
 * @var array $mandats
 * @var WP_User $user
 */

use function CGT\Dashboard\ensure_mandat_dir;

defined( 'ABSPATH' ) || exit;

wp_enqueue_media();

$upload_dir = ensure_mandat_dir();
$mandat_url_base = str_replace( wp_normalize_path( WP_CONTENT_DIR ), content_url(), wp_normalize_path( $upload_dir ) );

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<title><?php esc_html_e( 'Tableau de bord Administration', 'departement-dashboard' ); ?></title>
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'bg-gray-100' ); ?>>
<div class="min-h-screen flex flex-col">
	<header class="bg-red-700 text-white py-6 shadow">
		<div class="max-w-6xl mx-auto px-6 flex items-center justify-between">
			<div>
				<h1 class="text-2xl font-bold"><?php esc_html_e( 'Tableau de bord — Administration', 'departement-dashboard' ); ?></h1>
				<p class="text-sm opacity-90"><?php printf( esc_html__( 'Connecté·e en tant que %s', 'departement-dashboard' ), esc_html( $user->display_name ) ); ?></p>
			</div>
			<a href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>" class="bg-white text-red-700 px-4 py-2 rounded font-semibold"><?php esc_html_e( 'Déconnexion', 'departement-dashboard' ); ?></a>
		</div>
	</header>

	<main class="flex-1 max-w-6xl mx-auto px-6 py-10 space-y-10">
		<section class="bg-white shadow rounded-xl p-6">
			<h2 class="text-xl font-semibold mb-4 flex items-center gap-2">
				<span>👥</span> <?php esc_html_e( 'Adhésions', 'departement-dashboard' ); ?>
			</h2>
			<?php if ( ! empty( $adhesions ) ) : ?>
				<div class="overflow-x-auto">
					<table class="min-w-full divide-y divide-gray-200">
						<thead class="bg-gray-50">
							<tr>
								<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?php esc_html_e( 'Nom', 'departement-dashboard' ); ?></th>
								<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?php esc_html_e( 'Date', 'departement-dashboard' ); ?></th>
								<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?php esc_html_e( 'Email', 'departement-dashboard' ); ?></th>
								<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?php esc_html_e( 'Statut', 'departement-dashboard' ); ?></th>
								<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"></th>
							</tr>
						</thead>
						<tbody class="bg-white divide-y divide-gray-200">
							<?php foreach ( $adhesions as $item ) : ?>
								<tr>
									<td class="px-4 py-4 font-medium text-gray-900"><?php echo esc_html( $item['title'] ); ?></td>
									<td class="px-4 py-4 text-gray-600"><?php echo esc_html( $item['date'] ); ?></td>
									<td class="px-4 py-4 text-gray-600"><a href="mailto:<?php echo esc_attr( $item['email'] ); ?>" class="text-red-600 hover:underline"><?php echo esc_html( $item['email'] ); ?></a></td>
									<td class="px-4 py-4">
										<span class="px-2 py-1 inline-flex text-xs font-semibold rounded-full <?php echo 'publish' === $item['status'] ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
											<?php echo esc_html( $item['status'] ); ?>
										</span>
									</td>
									<td class="px-4 py-4 text-right">
										<a href="<?php echo esc_url( $item['permalink'] ); ?>" class="text-sm font-semibold text-red-600 hover:underline"><?php esc_html_e( 'Modifier', 'departement-dashboard' ); ?></a>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			<?php else : ?>
				<p class="text-gray-600"><?php esc_html_e( 'Aucune adhésion enregistrée pour le moment.', 'departement-dashboard' ); ?></p>
			<?php endif; ?>
		</section>

		<section class="bg-white shadow rounded-xl p-6" x-data="{ openTab: 'banque' }">
			<div class="flex gap-6 border-b mb-6">
				<button class="pb-2 border-b-2" :class="openTab === 'banque' ? 'border-red-600 text-red-600 font-semibold' : 'border-transparent text-gray-500'" @click="openTab = 'banque'">
					<?php esc_html_e( 'Banque', 'departement-dashboard' ); ?>
				</button>
				<button class="pb-2 border-b-2" :class="openTab === 'mandats' ? 'border-red-600 text-red-600 font-semibold' : 'border-transparent text-gray-500'" @click="openTab = 'mandats'">
					<?php esc_html_e( 'Mandats enregistrés', 'departement-dashboard' ); ?>
				</button>
			</div>

			<div x-show="openTab === 'banque'">
				<h2 class="text-xl font-semibold mb-4 flex items-center gap-2"><span>🏦</span><?php esc_html_e( 'Enregistrer un mandat bancaire', 'departement-dashboard' ); ?></h2>
				<form method="post" enctype="multipart/form-data" class="grid gap-4" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<?php wp_nonce_field( 'cgt_dd_save_mandat' ); ?>
					<input type="hidden" name="action" value="cgt_dd_save_mandat">
					<div>
						<label class="block text-sm font-medium text-gray-700"><?php esc_html_e( 'Nom et prénom', 'departement-dashboard' ); ?></label>
						<input type="text" name="mandat_name" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-red-500 focus:border-red-500">
					</div>
					<div>
						<label class="block text-sm font-medium text-gray-700"><?php esc_html_e( 'RIB', 'departement-dashboard' ); ?></label>
						<textarea name="mandat_rib" rows="3" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-red-500 focus:border-red-500"></textarea>
					</div>
					<div>
						<label class="block text-sm font-medium text-gray-700"><?php esc_html_e( 'Mandat signé (PDF)', 'departement-dashboard' ); ?></label>
						<input type="file" name="mandat_pdf" accept="application/pdf" required class="mt-1 block">
						<p class="text-sm text-gray-500"><?php esc_html_e( 'PDF uniquement, taille maximale 10 Mo.', 'departement-dashboard' ); ?></p>
					</div>
					<div>
						<button type="submit" class="inline-flex items-center px-5 py-2 bg-red-600 text-white font-semibold rounded shadow hover:bg-red-700 transition">
							<?php esc_html_e( 'Enregistrer le mandat', 'departement-dashboard' ); ?>
						</button>
					</div>
				</form>
			</div>

			<div x-show="openTab === 'mandats'" x-cloak>
				<h2 class="text-xl font-semibold mb-4 flex items-center gap-2"><span>📁</span><?php esc_html_e( 'Mandats enregistrés', 'departement-dashboard' ); ?></h2>
				<?php if ( ! empty( $mandats ) ) : ?>
					<ul class="space-y-4">
						<?php foreach ( $mandats as $mandat ) : ?>
							<li class="border rounded-lg p-4 bg-gray-50 flex items-center justify-between flex-wrap gap-4">
								<div>
									<p class="font-semibold text-gray-900"><?php echo esc_html( $mandat['title'] ); ?></p>
									<p class="text-sm text-gray-600"><?php echo esc_html( sprintf( __( 'Enregistré le %s', 'departement-dashboard' ), $mandat['date'] ) ); ?></p>
								</div>
								<div class="flex gap-3">
									<?php if ( ! empty( $mandat['mandat'] ) ) : ?>
										<a class="inline-flex items-center px-3 py-1 bg-red-600 text-white rounded font-semibold hover:bg-red-700 transition" href="<?php echo esc_url( $mandat['mandat'] ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Télécharger le mandat', 'departement-dashboard' ); ?></a>
									<?php endif; ?>
								</div>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php else : ?>
					<p class="text-gray-600"><?php esc_html_e( 'Aucun mandat enregistré pour le moment.', 'departement-dashboard' ); ?></p>
				<?php endif; ?>
			</div>
		</section>
	</main>
</div>
<?php wp_footer(); ?>
</body>
</html>
