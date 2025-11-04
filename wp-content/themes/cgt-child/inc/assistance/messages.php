<?php
/**
 * Gestion des messages dans l'espace assistance
 *
 * @package CGT_Child
 */

defined( 'ABSPATH' ) || exit;

// Query des messages
$paged = isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;
$per_page = 20;

$args = array(
	'post_type'      => 'cgt_contact',
	'post_status'    => array( 'pending', 'publish' ),
	'posts_per_page' => $per_page,
	'paged'          => $paged,
	'orderby'        => 'date',
	'order'          => 'DESC',
);

$query = new WP_Query( $args );
?>

<div class="cgt-messages-section">
	<div class="cgt-section-header">
		<div>
			<h2 class="cgt-section-title"><?php esc_html_e( 'Messages', 'cgt' ); ?></h2>
			<p class="cgt-section-description"><?php esc_html_e( 'Consultez les messages reçus via le formulaire de contact', 'cgt' ); ?></p>
		</div>
	</div>

	<div class="cgt-card">
		<div class="cgt-card-header">
			<h3 class="cgt-card-title">
				<?php
				/* translators: %d: number of messages */
				printf( esc_html__( '%d message(s)', 'cgt' ), $query->found_posts );
				?>
			</h3>
		</div>

		<?php if ( $query->have_posts() ) : ?>
		<table class="cgt-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Nom', 'cgt' ); ?></th>
					<th><?php esc_html_e( 'Email', 'cgt' ); ?></th>
					<th><?php esc_html_e( 'Téléphone', 'cgt' ); ?></th>
					<th><?php esc_html_e( 'Sujet', 'cgt' ); ?></th>
					<th><?php esc_html_e( 'Type', 'cgt' ); ?></th>
					<th><?php esc_html_e( 'Date', 'cgt' ); ?></th>
					<th><?php esc_html_e( 'Statut', 'cgt' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php
				while ( $query->have_posts() ) :
					$query->the_post();
					$post_id = get_the_ID();
					$name    = get_post_meta( $post_id, 'cgt_submission_name', true );
					$email   = get_post_meta( $post_id, 'cgt_submission_email', true );
					$phone   = get_post_meta( $post_id, 'cgt_submission_phone', true );
					$subject = get_post_meta( $post_id, 'cgt_submission_subject', true );
					$type    = get_post_meta( $post_id, 'cgt_contact_type', true );
					$status  = get_post_status();
					?>
				<tr>
					<td><strong><?php echo esc_html( $name ); ?></strong></td>
					<td><a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a></td>
					<td><?php echo $phone ? esc_html( $phone ) : '—'; ?></td>
					<td><?php echo esc_html( $subject ); ?></td>
					<td>
						<?php
						if ( 'question' === $type ) {
							echo esc_html__( 'Question adhérent', 'cgt' );
						} else {
							echo esc_html__( 'Contact', 'cgt' );
						}
						?>
					</td>
					<td><?php echo esc_html( get_the_date( 'd/m/Y' ) ); ?></td>
					<td>
						<?php
						$status_class = 'pending' === $status ? 'cgt-status-pending' : 'cgt-status-publish';
						$status_label = 'pending' === $status ? __( 'Non traité', 'cgt' ) : __( 'Traité', 'cgt' );
						?>
						<span class="cgt-status <?php echo esc_attr( $status_class ); ?>"><?php echo esc_html( $status_label ); ?></span>
					</td>
				</tr>
				<?php endwhile; ?>
			</tbody>
		</table>
		<?php else : ?>
		<div class="cgt-empty-state">
			<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
				<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
			</svg>
			<h3><?php esc_html_e( 'Aucun message', 'cgt' ); ?></h3>
			<p><?php esc_html_e( 'Aucun message reçu pour le moment.', 'cgt' ); ?></p>
		</div>
		<?php endif; ?>
		<?php wp_reset_postdata(); ?>
	</div>
</div>
