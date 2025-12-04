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
	add_menu_page(
		__( 'Ads', 'cgt' ),
		__( 'Ads', 'cgt' ),
		'manage_options',
		'cgt-home-slider',
		'cgt_render_slider_settings_page',
		'dashicons-images-alt2',
		26
	);
}

/**
 * Register settings
 */
add_action( 'admin_init', 'cgt_register_slider_settings' );
function cgt_register_slider_settings() {
	register_setting( 'cgt_home_slider_group', 'cgt_home_slider_images' );
	register_setting( 'cgt_home_slider_group', 'cgt_home_slider_links' );
}

/**
 * Render the settings page
 */
function cgt_render_slider_settings_page() {
	// Enqueue WordPress media uploader
	wp_enqueue_media();

	$slider_images = get_option( 'cgt_home_slider_images', array() );
	$slider_links  = get_option( 'cgt_home_slider_links', array() );

	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Ads - Slider Page d\'Accueil', 'cgt' ); ?></h1>
		<p><?php esc_html_e( 'Gérez les images publicitaires qui s\'affichent dans le slider de la section "Rejoignez-nous" sur la page d\'accueil.', 'cgt' ); ?></p>

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
										$link_url  = isset( $slider_links[ $index ] ) ? $slider_links[ $index ] : '';
										echo '<div class="cgt-slider-image-item" data-index="' . esc_attr( $index ) . '">';
										echo '<img src="' . esc_url( $image_url ) . '" style="max-width: 150px; height: auto; display: block; margin-bottom: 10px;">';
										echo '<input type="hidden" name="cgt_home_slider_images[]" value="' . esc_attr( $image_id ) . '">';
										echo '<label style="display: block; margin: 10px 0 5px 0; font-weight: 600;">🔗 ' . esc_html__( 'Lien de redirection', 'cgt' ) . '</label>';
										echo '<input type="url" name="cgt_home_slider_links[]" value="' . esc_url( $link_url ) . '" placeholder="https://example.com" style="width: 100%; margin-bottom: 10px;" class="regular-text">';
										echo '<div style="margin-top: 10px;">';
										echo '<button type="button" class="button cgt-remove-slider-image">' . esc_html__( 'Supprimer', 'cgt' ) . '</button>';
										echo '<button type="button" class="button cgt-move-slider-image-up" style="margin-left: 5px;">' . esc_html__( '↑', 'cgt' ) . '</button>';
										echo '<button type="button" class="button cgt-move-slider-image-down" style="margin-left: 5px;">' . esc_html__( '↓', 'cgt' ) . '</button>';
										echo '</div>';
										echo '</div>';
									}
								}
							} else {
								echo '<p class="cgt-no-images-message" style="padding: 20px; background: #f0f0f1; border-left: 4px solid #72aee6; margin: 10px 0;">';
								echo '<strong>ℹ️ ' . esc_html__( 'Aucune image ajoutée', 'cgt' ) . '</strong><br>';
								echo esc_html__( 'Cliquez sur le bouton "Ajouter une image" ci-dessous pour commencer à créer votre slider d\'ads.', 'cgt' );
								echo '</p>';
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
			background: #fff;
			border: 2px solid #e5e7eb;
			border-radius: 8px;
			vertical-align: top;
			transition: all 0.3s ease;
			box-shadow: 0 1px 3px rgba(0,0,0,0.1);
			max-width: 280px;
		}
		.cgt-slider-image-item:hover {
			border-color: #2271b1;
			box-shadow: 0 4px 12px rgba(0,0,0,0.15);
			transform: translateY(-2px);
		}
		.cgt-slider-image-item img {
			border: 1px solid #ddd;
			border-radius: 4px;
			background: #f9f9f9;
		}
		.cgt-slider-image-item label {
			color: #2271b1;
			font-size: 13px;
		}
		.cgt-slider-image-item input[type="url"] {
			font-size: 13px;
			padding: 6px 10px;
			border: 1px solid #ddd;
			border-radius: 4px;
		}
		.cgt-slider-image-item input[type="url"]:focus {
			border-color: #2271b1;
			box-shadow: 0 0 0 1px #2271b1;
			outline: none;
		}
		#cgt-slider-images-container {
			margin-bottom: 15px;
			min-height: 50px;
		}
		#cgt-add-slider-image {
			font-size: 16px;
			padding: 8px 20px;
			height: auto;
		}
		.cgt-no-images-message {
			animation: fadeIn 0.5s ease;
		}
		@keyframes fadeIn {
			from { opacity: 0; transform: translateY(-10px); }
			to { opacity: 1; transform: translateY(0); }
		}
		.button.cgt-remove-slider-image {
			background: #dc3545;
			border-color: #dc3545;
			color: white;
		}
		.button.cgt-remove-slider-image:hover {
			background: #c82333;
			border-color: #bd2130;
		}
	</style>

	<script>
	jQuery(document).ready(function($) {
		console.log('CGT Slider Admin Script chargé');

		// Vérifier que wp.media est disponible
		if (typeof wp === 'undefined' || typeof wp.media === 'undefined') {
			console.error('wp.media n\'est pas disponible');
			alert('Erreur: La médiathèque WordPress n\'est pas chargée. Veuillez recharger la page.');
			return;
		}

		var mediaUploader;

		// Add image
		$('#cgt-add-slider-image').on('click', function(e) {
			e.preventDefault();
			console.log('Bouton Ajouter image cliqué');

			// Si l'uploader existe déjà, on l'ouvre
			if (mediaUploader) {
				mediaUploader.open();
				return;
			}

			// Créer le media uploader
			mediaUploader = wp.media({
				title: '<?php esc_html_e( 'Choisir une image pour le slider', 'cgt' ); ?>',
				button: {
					text: '<?php esc_html_e( 'Ajouter au slider', 'cgt' ); ?>'
				},
				library: {
					type: 'image'
				},
				multiple: true
			});

			// Quand une image est sélectionnée
			mediaUploader.on('select', function() {
				console.log('Image(s) sélectionnée(s)');
				var attachments = mediaUploader.state().get('selection').toJSON();
				console.log('Nombre d\'images:', attachments.length);

				// Supprimer le message "Aucune image" si présent
				$('.cgt-no-images-message').remove();

				attachments.forEach(function(attachment) {
					console.log('Ajout image ID:', attachment.id);

					// Utiliser thumbnail ou full selon disponibilité
					var imageUrl = attachment.sizes && attachment.sizes.thumbnail
						? attachment.sizes.thumbnail.url
						: attachment.url;

					var imageItem = $('<div class="cgt-slider-image-item">');
					imageItem.append('<img src="' + imageUrl + '" style="max-width: 150px; height: auto; display: block; margin-bottom: 10px;">');
					imageItem.append('<input type="hidden" name="cgt_home_slider_images[]" value="' + attachment.id + '">');
					imageItem.append('<label style="display: block; margin: 10px 0 5px 0; font-weight: 600;">🔗 <?php esc_html_e( 'Lien de redirection', 'cgt' ); ?></label>');
					imageItem.append('<input type="url" name="cgt_home_slider_links[]" value="" placeholder="https://example.com" style="width: 100%; margin-bottom: 10px;" class="regular-text">');
					imageItem.append('<div style="margin-top: 10px;">');
					imageItem.append('<button type="button" class="button cgt-remove-slider-image"><?php esc_html_e( 'Supprimer', 'cgt' ); ?></button>');
					imageItem.append('<button type="button" class="button cgt-move-slider-image-up" style="margin-left: 5px;">↑</button>');
					imageItem.append('<button type="button" class="button cgt-move-slider-image-down" style="margin-left: 5px;">↓</button>');
					imageItem.append('</div>');
					$('#cgt-slider-images-container').append(imageItem);
				});

				console.log('Images ajoutées au conteneur');
			});

			// Ouvrir le media uploader
			mediaUploader.open();
		});

		// Remove image
		$(document).on('click', '.cgt-remove-slider-image', function(e) {
			e.preventDefault();
			if (confirm('<?php esc_html_e( 'Voulez-vous vraiment supprimer cette image ?', 'cgt' ); ?>')) {
				$(this).closest('.cgt-slider-image-item').remove();
				console.log('Image supprimée');
			}
		});

		// Move image up
		$(document).on('click', '.cgt-move-slider-image-up', function(e) {
			e.preventDefault();
			var item = $(this).closest('.cgt-slider-image-item');
			var prev = item.prev('.cgt-slider-image-item');
			if (prev.length) {
				item.insertBefore(prev);
				console.log('Image déplacée vers le haut');
			}
		});

		// Move image down
		$(document).on('click', '.cgt-move-slider-image-down', function(e) {
			e.preventDefault();
			var item = $(this).closest('.cgt-slider-image-item');
			var next = item.next('.cgt-slider-image-item');
			if (next.length) {
				item.insertAfter(next);
				console.log('Image déplacée vers le bas');
			}
		});

		console.log('Tous les événements sont attachés');
	});
	</script>
	<?php
}
