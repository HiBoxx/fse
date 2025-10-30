<?php
/**
 * Page de test pour les connexions dédiées.
 *
 * @var array $route
 */

defined( 'ABSPATH' ) || exit;

$accounts = array(
	array(
		'label'    => __( 'Administration', 'departement-dashboard' ),
		'login'    => 'administration',
		'password' => get_user_meta( username_exists( 'administration' ), 'cgt_dd_temp_password', true ) ?: __( 'admin123', 'departement-dashboard' ),
		'url'      => home_url( '/dashboard/admin' ),
	),
	array(
		'label'    => __( 'Gestionnaire', 'departement-dashboard' ),
		'login'    => 'gestionnaire',
		'password' => get_user_meta( username_exists( 'gestionnaire' ), 'cgt_dd_temp_password', true ) ?: __( 'gestion123', 'departement-dashboard' ),
		'url'      => home_url( '/dashboard/gestionnaire' ),
	),
	array(
		'label'    => __( 'Assistante', 'departement-dashboard' ),
		'login'    => 'assistante',
		'password' => get_user_meta( username_exists( 'assistante' ), 'cgt_dd_temp_password', true ) ?: __( 'assist123', 'departement-dashboard' ),
		'url'      => home_url( '/dashboard/assistante' ),
	),
);

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<title><?php esc_html_e( 'Tester les accès', 'departement-dashboard' ); ?></title>
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'bg-gray-100' ); ?>>
	<div class="min-h-screen flex items-center justify-center">
		<div class="max-w-3xl w-full bg-white rounded-2xl shadow-xl p-8 space-y-6">
			<header class="text-center space-y-2">
				<h1 class="text-2xl font-bold text-gray-900"><?php esc_html_e( 'Tester les comptes dédiés', 'departement-dashboard' ); ?></h1>
				<p class="text-gray-600"><?php esc_html_e( 'Utilisez les identifiants temporaires ci-dessous pour vérifier les redirections et droits d’accès.', 'departement-dashboard' ); ?></p>
			</header>

			<div class="divide-y divide-gray-200">
				<?php foreach ( $accounts as $account ) : ?>
					<div class="py-4 grid gap-3 md:grid-cols-3 md:items-center">
						<div>
							<h2 class="text-lg font-semibold text-gray-900"><?php echo esc_html( $account['label'] ); ?></h2>
							<p class="text-sm text-gray-500"><?php echo esc_html( $account['url'] ); ?></p>
						</div>
						<div class="text-sm text-gray-600 space-y-1">
							<p><strong><?php esc_html_e( 'Identifiant :', 'departement-dashboard' ); ?></strong> <code><?php echo esc_html( $account['login'] ); ?></code></p>
							<p><strong><?php esc_html_e( 'Mot de passe :', 'departement-dashboard' ); ?></strong> <code><?php echo esc_html( $account['password'] ); ?></code></p>
						</div>
						<div class="md:text-right">
							<a href="<?php echo esc_url( wp_login_url( $account['url'] ) ); ?>" class="inline-flex items-center px-4 py-2 bg-red-600 text-white font-semibold rounded hover:bg-red-700 transition">
								<?php esc_html_e( 'Se connecter', 'departement-dashboard' ); ?>
							</a>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
<?php wp_footer(); ?>
</body>
</html>
