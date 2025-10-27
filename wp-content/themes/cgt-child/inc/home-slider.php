<?php
/**
 * Home Slider Settings
 *
 * @package CGT_Child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Add slider settings page to admin menu
 */
add_action( 'admin_menu', 'cgt_add_slider_settings_page' );
function cgt_add_slider_settings_page() {
	add_options_page(
		__( 'Slider Page d\'Accueil', 'cgt' ),
		__( 'Slider Accueil', 'cgt' ),
		'manage_options',
		'cgt-home-slider',
		'cgt_render_slider_settings_page'
	);
}

/**
 * Register settings
 */
add_action( 'admin_init', 'cgt_register_slider_settings' );
function cgt_register_slider_settings() {
	register_setting( 'cgt_home_slider_group', 'cgt_home_slider_images' );
}

/**
 * Render the settings page
 */
function cgt_render_slider_settings_page() {
	$slider_images = get_option( 'cgt_home_slider_images', array() );

	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Slider de la Page d\'Accueil', 'cgt' ); ?></h1>
		<p><?php esc_html_e( 'Gérez les images qui s\'affichent dans le slider de la section "Rejoignez-nous" sur la page d\'accueil.', 'cgt' ); ?></p>

		<form method="post" action="options.php">
			<?php settings_fields( 'cgt_home_slider_group' ); ?>
			<?php do_settings_sections( 'cgt_home_slider_group' ); ?>

			<table class="form-table">
				<tr valign="top">
					<th scope="row"><?php esc_html_e( 'Images du slider', 'cgt' ); ?></th>
					<td>
						<div id="cgt-slider-images-container">
							<?php
							if ( ! empty( $slider_images ) && is_array( $slider_images ) ) {
								foreach ( $slider_images as $index => $image_id ) {
									if ( $image_id ) {
										$image_url = wp_get_attachment_image_url( $image_id, 'thumbnail' );
										echo '<div class="cgt-slider-image-item" data-index="' . esc_attr( $index ) . '">';
										echo '<img src="' . esc_url( $image_url ) . '" style="max-width: 150px; height: auto; display: block; margin-bottom: 10px;">';
										echo '<input type="hidden" name="cgt_home_slider_images[]" value="' . esc_attr( $image_id ) . '">';
										echo '<button type="button" class="button cgt-remove-slider-image">' . esc_html__( 'Supprimer', 'cgt' ) . '</button>';
										echo '<button type="button" class="button cgt-move-slider-image-up" style="margin-left: 5px;">' . esc_html__( '↑', 'cgt' ) . '</button>';
										echo '<button type="button" class="button cgt-move-slider-image-down" style="margin-left: 5px;">' . esc_html__( '↓', 'cgt' ) . '</button>';
										echo '</div>';
									}
								}
							}
							?>
						</div>

						<p>
							<button type="button" class="button button-primary" id="cgt-add-slider-image">
								<?php esc_html_e( 'Ajouter une image', 'cgt' ); ?>
							</button>
						</p>

						<p class="description">
							<?php esc_html_e( 'Format recommandé : Portrait (ratio 3:4 ou 2:3). Taille recommandée : 600x800 pixels minimum.', 'cgt' ); ?>
						</p>
					</td>
				</tr>
			</table>

			<?php submit_button(); ?>
		</form>
	</div>

	<style>
		.cgt-slider-image-item {
			display: inline-block;
			margin: 10px 10px 10px 0;
			padding: 15px;
			background: #f9f9f9;
			border: 1px solid #ddd;
			border-radius: 4px;
			vertical-align: top;
		}
		.cgt-slider-image-item img {
			border: 1px solid #ddd;
			border-radius: 4px;
		}
		#cgt-slider-images-container {
			margin-bottom: 15px;
		}
	</style>

	<script>
	jQuery(document).ready(function($) {
		var mediaUploader;

		// Add image
		$('#cgt-add-slider-image').on('click', function(e) {
			e.preventDefault();

			if (mediaUploader) {
				mediaUploader.open();
				return;
			}

			mediaUploader = wp.media({
				title: '<?php esc_html_e( 'Choisir une image', 'cgt' ); ?>',
				button: {
					text: '<?php esc_html_e( 'Ajouter au slider', 'cgt' ); ?>'
				},
				multiple: true
			});

			mediaUploader.on('select', function() {
				var attachments = mediaUploader.state().get('selection').toJSON();

				attachments.forEach(function(attachment) {
					var imageItem = $('<div class="cgt-slider-image-item">');
					imageItem.append('<img src="' + attachment.sizes.thumbnail.url + '" style="max-width: 150px; height: auto; display: block; margin-bottom: 10px;">');
					imageItem.append('<input type="hidden" name="cgt_home_slider_images[]" value="' + attachment.id + '">');
					imageItem.append('<button type="button" class="button cgt-remove-slider-image"><?php esc_html_e( 'Supprimer', 'cgt' ); ?></button>');
					imageItem.append('<button type="button" class="button cgt-move-slider-image-up" style="margin-left: 5px;">↑</button>');
					imageItem.append('<button type="button" class="button cgt-move-slider-image-down" style="margin-left: 5px;">↓</button>');
					$('#cgt-slider-images-container').append(imageItem);
				});
			});

			mediaUploader.open();
		});

		// Remove image
		$(document).on('click', '.cgt-remove-slider-image', function() {
			$(this).closest('.cgt-slider-image-item').remove();
		});

		// Move image up
		$(document).on('click', '.cgt-move-slider-image-up', function() {
			var item = $(this).closest('.cgt-slider-image-item');
			var prev = item.prev('.cgt-slider-image-item');
			if (prev.length) {
				item.insertBefore(prev);
			}
		});

		// Move image down
		$(document).on('click', '.cgt-move-slider-image-down', function() {
			var item = $(this).closest('.cgt-slider-image-item');
			var next = item.next('.cgt-slider-image-item');
			if (next.length) {
				item.insertAfter(next);
			}
		});
	});
	</script>
	<?php
}
