<?php
/**
 * Newsletter Subscription Management
 *
 * @package CGT_Child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Create newsletter subscriptions table on theme activation
 */
function cgt_create_newsletter_table() {
	global $wpdb;
	$table_name      = $wpdb->prefix . 'cgt_newsletter';
	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE IF NOT EXISTS $table_name (
		id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
		prenom varchar(100) NOT NULL,
		nom varchar(100) NOT NULL,
		email varchar(255) NOT NULL,
		date_inscription datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
		PRIMARY KEY (id),
		UNIQUE KEY email (email)
	) $charset_collate;";

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $sql );
}
add_action( 'after_switch_theme', 'cgt_create_newsletter_table' );

/**
 * Ensure table exists on init (useful after restores/imports).
 */
add_action(
	'init',
	function () {
		global $wpdb;
		$table_name = $wpdb->prefix . 'cgt_newsletter';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$exists = $wpdb->get_var(
			$wpdb->prepare(
				'SHOW TABLES LIKE %s',
				$wpdb->esc_like( $table_name )
			)
		);
		if ( $exists !== $table_name ) {
			cgt_create_newsletter_table();
		}
	}
);

/**
 * Add newsletter management page to admin menu
 */
add_action( 'admin_menu', 'cgt_add_newsletter_admin_page' );
function cgt_add_newsletter_admin_page() {
	$hook = add_menu_page(
		__( 'Liste de Diffusion', 'cgt' ),
		__( 'Liste de Diffusion', 'cgt' ),
		'manage_options',
		'cgt-newsletter',
		'cgt_render_newsletter_admin_page',
		'dashicons-email',
		25
	);

	// Enqueue admin script only on this page
	add_action( 'admin_print_scripts-' . $hook, 'cgt_enqueue_newsletter_admin_scripts' );
}

/**
 * Enqueue admin scripts for newsletter page
 */
function cgt_enqueue_newsletter_admin_scripts() {
	wp_enqueue_script(
		'cgt-admin-newsletter',
		get_stylesheet_directory_uri() . '/assets/js/admin-newsletter.js',
		array( 'jquery' ),
		CGT_CHILD_VERSION,
		true
	);
}

/**
 * Render the newsletter admin page
 */
function cgt_render_newsletter_admin_page() {
	global $wpdb;
	$table_name = $wpdb->prefix . 'cgt_newsletter';

	// Handle delete action
	if ( isset( $_GET['action'] ) && 'delete' === $_GET['action'] && isset( $_GET['id'] ) && isset( $_GET['_wpnonce'] ) ) {
		if ( wp_verify_nonce( $_GET['_wpnonce'], 'delete_newsletter_' . $_GET['id'] ) ) {
			$wpdb->delete( $table_name, array( 'id' => intval( $_GET['id'] ) ), array( '%d' ) );
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Inscription supprimée avec succès.', 'cgt' ) . '</p></div>';
		}
	}

	// Handle bulk delete action
	if ( isset( $_POST['action'] ) && 'bulk_delete' === $_POST['action'] && isset( $_POST['newsletter_ids'] ) && isset( $_POST['_wpnonce'] ) ) {
		if ( wp_verify_nonce( $_POST['_wpnonce'], 'bulk_delete_newsletter' ) ) {
			$ids = array_map( 'intval', $_POST['newsletter_ids'] );
			foreach ( $ids as $id ) {
				$wpdb->delete( $table_name, array( 'id' => $id ), array( '%d' ) );
			}
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html( sprintf( __( '%d inscription(s) supprimée(s) avec succès.', 'cgt' ), count( $ids ) ) ) . '</p></div>';
		}
	}

	// Get all subscriptions
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery
	$subscriptions = $wpdb->get_results( "SELECT * FROM {$table_name} ORDER BY date_inscription DESC" );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery
	$total_count   = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table_name}" );

	?>
	<div class="wrap">
		<h1 class="wp-heading-inline"><?php esc_html_e( 'Liste de Diffusion', 'cgt' ); ?></h1>
		<p class="description"><?php echo esc_html( sprintf( __( 'Total : %d inscription(s)', 'cgt' ), $total_count ) ); ?></p>

		<?php if ( ! empty( $subscriptions ) ) : ?>
			<form method="post">
				<?php wp_nonce_field( 'bulk_delete_newsletter', '_wpnonce' ); ?>
				<input type="hidden" name="action" value="bulk_delete">

				<div class="tablenav top">
					<div class="alignleft actions bulkactions">
						<button type="submit" class="button action" onclick="return confirm('<?php esc_attr_e( 'Êtes-vous sûr de vouloir supprimer les inscriptions sélectionnées ?', 'cgt' ); ?>')">
							<?php esc_html_e( 'Supprimer la sélection', 'cgt' ); ?>
						</button>
					</div>
					<div class="alignleft actions">
						<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=cgt-newsletter&export=csv' ), 'cgt_export_newsletter' ) ); ?>" class="button">
							<?php esc_html_e( 'Exporter en CSV', 'cgt' ); ?>
						</a>
					</div>
				</div>

				<table class="wp-list-table widefat fixed striped">
					<thead>
						<tr>
							<td class="manage-column column-cb check-column">
								<input type="checkbox" id="cb-select-all">
							</td>
							<th class="manage-column"><?php esc_html_e( 'Prénom', 'cgt' ); ?></th>
							<th class="manage-column"><?php esc_html_e( 'Nom', 'cgt' ); ?></th>
							<th class="manage-column"><?php esc_html_e( 'Email', 'cgt' ); ?></th>
							<th class="manage-column"><?php esc_html_e( 'Date d\'inscription', 'cgt' ); ?></th>
							<th class="manage-column"><?php esc_html_e( 'Actions', 'cgt' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $subscriptions as $subscription ) : ?>
							<tr>
								<th scope="row" class="check-column">
									<input type="checkbox" name="newsletter_ids[]" value="<?php echo esc_attr( $subscription->id ); ?>">
								</th>
								<td><?php echo esc_html( $subscription->prenom ); ?></td>
								<td><?php echo esc_html( $subscription->nom ); ?></td>
								<td><a href="mailto:<?php echo esc_attr( $subscription->email ); ?>"><?php echo esc_html( $subscription->email ); ?></a></td>
								<td><?php echo esc_html( wp_date( 'd/m/Y H:i', strtotime( $subscription->date_inscription ) ) ); ?></td>
								<td>
									<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=cgt-newsletter&action=delete&id=' . $subscription->id ), 'delete_newsletter_' . $subscription->id ) ); ?>"
									   class="button button-small"
									   onclick="return confirm('<?php esc_attr_e( 'Êtes-vous sûr de vouloir supprimer cette inscription ?', 'cgt' ); ?>')">
										<?php esc_html_e( 'Supprimer', 'cgt' ); ?>
									</a>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</form>
		<?php else : ?>
			<p><?php esc_html_e( 'Aucune inscription pour le moment.', 'cgt' ); ?></p>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Handle CSV export
 */
add_action( 'admin_init', 'cgt_handle_newsletter_export' );
function cgt_handle_newsletter_export() {
	if ( isset( $_GET['page'] ) && 'cgt-newsletter' === $_GET['page'] && isset( $_GET['export'] ) && 'csv' === $_GET['export'] ) {
		// Vérifier le nonce
		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'cgt_export_newsletter' ) ) {
			wp_die( esc_html__( 'Action non autorisée.', 'cgt' ) );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		global $wpdb;
		$table_name    = $wpdb->prefix . 'cgt_newsletter';
		$subscriptions = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM %i ORDER BY date_inscription DESC", $table_name ), ARRAY_A );

		// Set headers for CSV download
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=liste-diffusion-' . gmdate( 'Y-m-d' ) . '.csv' );

		// Create file pointer
		$output = fopen( 'php://output', 'w' );

		// Add BOM for Excel UTF-8 support
		fprintf( $output, chr( 0xEF ) . chr( 0xBB ) . chr( 0xBF ) );

		// Add headers
		fputcsv( $output, array( 'Prénom', 'Nom', 'Email', 'Date d\'inscription' ), ';' );

		// Add data
		foreach ( $subscriptions as $subscription ) {
			fputcsv(
				$output,
				array(
					$subscription['prenom'],
					$subscription['nom'],
					$subscription['email'],
					wp_date( 'd/m/Y H:i', strtotime( $subscription['date_inscription'] ) ),
				),
				';'
			);
		}

		fclose( $output );
		exit;
	}
}

/**
 * Handle AJAX newsletter subscription
 */
add_action( 'wp_ajax_cgt_subscribe_newsletter', 'cgt_handle_newsletter_subscription' );
add_action( 'wp_ajax_nopriv_cgt_subscribe_newsletter', 'cgt_handle_newsletter_subscription' );

function cgt_handle_newsletter_subscription() {
	// Verify nonce
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'cgt_newsletter_nonce' ) ) {
		wp_send_json_error( array( 'message' => __( 'Erreur de sécurité.', 'cgt' ) ) );
	}

	// Rate limiting - Protection contre le spam
	$user_ip       = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	$transient_key = 'newsletter_submit_' . md5( $user_ip );

	if ( get_transient( $transient_key ) ) {
		wp_send_json_error( array( 'message' => __( 'Veuillez patienter avant de soumettre une nouvelle inscription.', 'cgt' ) ) );
	}

	// Définir un transient de 60 secondes
	set_transient( $transient_key, true, 60 );

	// Sanitize inputs
	$prenom = isset( $_POST['prenom'] ) ? sanitize_text_field( wp_unslash( $_POST['prenom'] ) ) : '';
	$nom    = isset( $_POST['nom'] ) ? sanitize_text_field( wp_unslash( $_POST['nom'] ) ) : '';
	$email  = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';

	// Validate
	if ( empty( $prenom ) || empty( $nom ) || empty( $email ) ) {
		wp_send_json_error( array( 'message' => __( 'Tous les champs sont obligatoires.', 'cgt' ) ) );
	}

	if ( ! is_email( $email ) ) {
		wp_send_json_error( array( 'message' => __( 'Email invalide.', 'cgt' ) ) );
	}

	// Bloquer les emails jetables (disposable email domains)
	$disposable_domains = array(
		'tempmail.com',
		'guerrillamail.com',
		'10minutemail.com',
		'mailinator.com',
		'throwaway.email',
		'temp-mail.org',
		'getnada.com',
		'trashmail.com',
	);

	$email_parts  = explode( '@', $email );
	$email_domain = isset( $email_parts[1] ) ? strtolower( $email_parts[1] ) : '';

	if ( in_array( $email_domain, $disposable_domains, true ) ) {
		wp_send_json_error( array( 'message' => __( 'Les adresses email jetables ne sont pas autorisées.', 'cgt' ) ) );
	}

	global $wpdb;
	$table_name = $wpdb->prefix . 'cgt_newsletter';

	// Check if email already exists
	$existing = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table_name} WHERE email = %s", $email ) );

	if ( $existing ) {
		wp_send_json_error( array( 'message' => __( 'Cette adresse email est déjà inscrite.', 'cgt' ) ) );
	}

	// Insert into database
	$inserted = $wpdb->insert(
		$table_name,
		array(
			'prenom' => $prenom,
			'nom'    => $nom,
			'email'  => $email,
		),
		array( '%s', '%s', '%s' )
	);

	if ( $inserted ) {
		wp_send_json_success( array( 'message' => __( 'Inscription réussie ! Merci de vous être inscrit·e à notre liste de diffusion.', 'cgt' ) ) );
	} else {
		wp_send_json_error(
			array(
				'message' => __( 'Une erreur est survenue. Veuillez réessayer.', 'cgt' ),
			)
		);
	}
}
