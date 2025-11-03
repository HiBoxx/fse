<?php
/**
 * Audit et optimisation des options autoload
 * ✅ PHASE 2 : Réduire la charge autoload pour améliorer les performances
 *
 * @package CGT_Child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Optimiser automatiquement les options CGT autoload
 * À exécuter une seule fois lors de la mise à jour
 */
function cgt_optimize_autoload_options() {
	global $wpdb;

	// ✅ 1. Désactiver autoload pour cgt_spam_log (option volum ineuse qui n'est pas nécessaire à chaque requête)
	$wpdb->query(
		$wpdb->prepare(
			"UPDATE {$wpdb->options} SET autoload = %s WHERE option_name = %s",
			'no',
			'cgt_spam_log'
		)
	);

	// ✅ 2. Désactiver autoload pour cgt_blocked_ips (chargé uniquement quand nécessaire)
	$wpdb->query(
		$wpdb->prepare(
			"UPDATE {$wpdb->options} SET autoload = %s WHERE option_name = %s",
			'no',
			'cgt_blocked_ips'
		)
	);

	// ✅ 3. Nettoyer les transients orphelins avec autoload (ne devrait jamais arriver)
	$wpdb->query(
		"UPDATE {$wpdb->options}
		SET autoload = 'no'
		WHERE autoload = 'yes'
		AND (option_name LIKE '_transient_%' OR option_name LIKE '_site_transient_%')"
	);

	error_log( 'CGT Autoload Optimization: Options optimisées avec succès' );

	return true;
}

/**
 * Auditer les options autoload (pour debug)
 * Retourne un rapport d'audit
 *
 * @return array Rapport d'audit
 */
function cgt_audit_autoload_options() {
	global $wpdb;

	$report = array();

	// Total d'options autoload
	$total_autoload = $wpdb->get_var(
		"SELECT COUNT(*) FROM {$wpdb->options} WHERE autoload = 'yes'"
	);
	$report['total_autoload'] = (int) $total_autoload;

	// Taille totale autoload
	$autoload_size = $wpdb->get_var(
		"SELECT SUM(LENGTH(option_value)) FROM {$wpdb->options} WHERE autoload = 'yes'"
	);
	$report['total_size_bytes'] = (int) $autoload_size;
	$report['total_size_mb']    = round( $autoload_size / 1024 / 1024, 2 );

	// Recommandation
	if ( $report['total_size_mb'] > 1 ) {
		$report['warning'] = 'La taille autoload dépasse 1 MB (recommandé: < 800 KB)';
	}

	// Top 20 options les plus volumineuses
	$large_options = $wpdb->get_results(
		"SELECT option_name, LENGTH(option_value) as size
		FROM {$wpdb->options}
		WHERE autoload = 'yes'
		ORDER BY size DESC
		LIMIT 20"
	);

	$report['large_options'] = array();
	foreach ( $large_options as $option ) {
		$report['large_options'][] = array(
			'name'    => $option->option_name,
			'size'    => (int) $option->size,
			'size_kb' => round( $option->size / 1024, 2 ),
		);
	}

	// Options CGT avec autoload
	$cgt_options = $wpdb->get_results(
		"SELECT option_name, LENGTH(option_value) as size
		FROM {$wpdb->options}
		WHERE autoload = 'yes'
		AND (option_name LIKE 'cgt_%' OR option_name LIKE '_cgt_%')
		ORDER BY size DESC"
	);

	$report['cgt_options'] = array();
	foreach ( $cgt_options as $option ) {
		$report['cgt_options'][] = array(
			'name'    => $option->option_name,
			'size'    => (int) $option->size,
			'size_kb' => round( $option->size / 1024, 2 ),
		);
	}

	// Transients avec autoload (ne devrait pas exister)
	$transients_autoload = $wpdb->get_var(
		"SELECT COUNT(*)
		FROM {$wpdb->options}
		WHERE autoload = 'yes'
		AND (option_name LIKE '_transient_%' OR option_name LIKE '_site_transient_%')"
	);
	$report['transients_autoload'] = (int) $transients_autoload;

	return $report;
}

/**
 * Afficher le rapport d'audit dans l'admin (pour développeurs)
 * Utilisation: Ajouter ?cgt_audit_autoload=1 à l'URL admin
 */
add_action( 'admin_init', 'cgt_display_autoload_audit' );
function cgt_display_autoload_audit() {
	if ( ! isset( $_GET['cgt_audit_autoload'] ) || ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$report = cgt_audit_autoload_options();

	echo '<div style="background: #fff; padding: 20px; margin: 20px; border: 1px solid #ccc; font-family: monospace;">';
	echo '<h2>🔍 Audit des Options Autoload</h2>';

	echo '<h3>📊 Statistiques globales</h3>';
	echo '<ul>';
	echo '<li>Total options autoload: <strong>' . esc_html( $report['total_autoload'] ) . '</strong></li>';
	echo '<li>Taille totale: <strong>' . esc_html( number_format( $report['total_size_bytes'] ) ) . ' bytes (' . esc_html( $report['total_size_mb'] ) . ' MB)</strong></li>';
	if ( isset( $report['warning'] ) ) {
		echo '<li style="color: red;">⚠️ ' . esc_html( $report['warning'] ) . '</li>';
	}
	echo '</ul>';

	echo '<h3>🔝 Top 10 options les plus volumineuses</h3>';
	echo '<table border="1" cellpadding="5" style="border-collapse: collapse;">';
	echo '<tr><th>Option Name</th><th>Size (bytes)</th><th>Size (KB)</th></tr>';
	foreach ( array_slice( $report['large_options'], 0, 10 ) as $option ) {
		echo '<tr>';
		echo '<td>' . esc_html( $option['name'] ) . '</td>';
		echo '<td>' . esc_html( number_format( $option['size'] ) ) . '</td>';
		echo '<td>' . esc_html( $option['size_kb'] ) . '</td>';
		echo '</tr>';
	}
	echo '</table>';

	if ( ! empty( $report['cgt_options'] ) ) {
		echo '<h3>🎯 Options CGT avec autoload</h3>';
		echo '<table border="1" cellpadding="5" style="border-collapse: collapse;">';
		echo '<tr><th>Option Name</th><th>Size (bytes)</th><th>Size (KB)</th></tr>';
		foreach ( $report['cgt_options'] as $option ) {
			echo '<tr>';
			echo '<td>' . esc_html( $option['name'] ) . '</td>';
			echo '<td>' . esc_html( number_format( $option['size'] ) ) . '</td>';
			echo '<td>' . esc_html( $option['size_kb'] ) . '</td>';
			echo '</tr>';
		}
		echo '</table>';
	} else {
		echo '<p>✅ Aucune option CGT avec autoload="yes" trouvée</p>';
	}

	if ( $report['transients_autoload'] > 0 ) {
		echo '<p style="color: red;">⚠️ <strong>' . esc_html( $report['transients_autoload'] ) . '</strong> transients avec autoload="yes" détectés (problème !)</p>';
	}

	echo '<h3>💡 Actions</h3>';
	echo '<p><a href="' . esc_url( add_query_arg( 'cgt_optimize_autoload', '1' ) ) . '" class="button button-primary">Optimiser les options autoload</a></p>';

	echo '</div>';

	exit;
}

/**
 * Exécuter l'optimisation à la demande
 * Utilisation: Ajouter ?cgt_optimize_autoload=1 à l'URL admin
 */
add_action( 'admin_init', 'cgt_run_autoload_optimization' );
function cgt_run_autoload_optimization() {
	if ( ! isset( $_GET['cgt_optimize_autoload'] ) || ! current_user_can( 'manage_options' ) ) {
		return;
	}

	cgt_optimize_autoload_options();

	wp_redirect( admin_url( 'admin.php?cgt_audit_autoload=1&optimized=1' ) );
	exit;
}

/**
 * Exécuter l'optimisation automatiquement lors de l'activation du thème (une seule fois)
 */
add_action( 'after_switch_theme', 'cgt_auto_optimize_autoload' );
function cgt_auto_optimize_autoload() {
	// Vérifier si déjà exécuté
	if ( get_option( 'cgt_autoload_optimized', false ) ) {
		return;
	}

	// Exécuter l'optimisation
	cgt_optimize_autoload_options();

	// Marquer comme exécuté
	update_option( 'cgt_autoload_optimized', true, false );
}
