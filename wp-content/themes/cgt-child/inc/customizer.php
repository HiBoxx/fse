<?php
/**
 * Paramètres Customizer pour l'identité visuelle.
 *
 * @package CGT_Child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Enregistre les options du Customizer pour les assets de marque.
 *
 * @param WP_Customize_Manager $wp_customize Customizer instance.
 */
function cgt_child_customize_register( $wp_customize ) {
	$wp_customize->add_section(
		'cgt_branding',
		array(
			'title'       => __( 'Identité visuelle', 'cgt' ),
			'description' => __( 'Définissez le logo et l’icône du site utilisés sur l’ensemble des pages.', 'cgt' ),
			'priority'    => 35,
		)
	);

	// Logo principal.
	$wp_customize->add_setting(
		'cgt_logo_url',
		array(
			'default'           => CGT_CHILD_DEFAULT_LOGO_URL,
			'sanitize_callback' => 'esc_url_raw',
		)
	);

	$wp_customize->add_control(
		new WP_Customize_Image_Control(
			$wp_customize,
			'cgt_logo_url',
			array(
				'label'       => __( 'Logo principal', 'cgt' ),
				'section'     => 'cgt_branding',
				'description' => __( 'Affiché dans l’en-tête du site et les zones de navigation.', 'cgt' ),
			)
		)
	);

	// Favicon.
	$wp_customize->add_setting(
		'cgt_favicon_url',
		array(
			'default'           => CGT_CHILD_DEFAULT_FAVICON_URL,
			'sanitize_callback' => 'esc_url_raw',
		)
	);

	$wp_customize->add_control(
		new WP_Customize_Image_Control(
			$wp_customize,
			'cgt_favicon_url',
			array(
				'label'       => __( 'Favicon / Icône', 'cgt' ),
				'section'     => 'cgt_branding',
				'description' => __( 'Utilisé dans les onglets navigateur, l’admin et l’écran de connexion.', 'cgt' ),
			)
		)
	);

	// Image par défaut.
	$wp_customize->add_setting(
		'cgt_default_image_url',
		array(
			'default'           => CGT_CHILD_DEFAULT_IMAGE_URL,
			'sanitize_callback' => 'esc_url_raw',
		)
	);

	$wp_customize->add_control(
		new WP_Customize_Image_Control(
			$wp_customize,
			'cgt_default_image_url',
			array(
				'label'       => __( 'Image par défaut', 'cgt' ),
				'section'     => 'cgt_branding',
				'description' => __( 'Affichée lorsque aucun visuel n’est défini pour un article, un tract ou une actualité.', 'cgt' ),
			)
		)
	);
}
add_action( 'customize_register', 'cgt_child_customize_register' );
