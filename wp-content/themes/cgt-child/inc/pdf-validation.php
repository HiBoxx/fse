<?php
/**
 * Validation sécurisée des uploads PDF
 * ✅ Empêche l'upload de malwares déguisés en PDF via validation magic bytes
 *
 * @package CGT_Child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Valider un fichier PDF avant upload
 * ✅ SÉCURITÉ : Vérification magic bytes pour éviter les faux PDF
 *
 * @param array $file Tableau du fichier ($_FILES)
 * @return true|WP_Error True si valide, WP_Error sinon
 */
function cgt_validate_pdf_upload( $file ) {
	// ✅ 1. Vérifier l'extension
	$allowed_extensions = array( 'pdf' );
	$file_ext = strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );

	if ( ! in_array( $file_ext, $allowed_extensions, true ) ) {
		return new WP_Error(
			'invalid_extension',
			__( 'Seuls les fichiers PDF sont autorisés.', 'cgt' )
		);
	}

	// ✅ 2. Vérifier la taille (15 MB max)
	$max_size = 15 * 1024 * 1024; // 15 MB en bytes
	if ( $file['size'] > $max_size ) {
		return new WP_Error(
			'file_too_large',
			sprintf(
				__( 'Fichier trop volumineux. Taille maximale : %s MB', 'cgt' ),
				number_format( $max_size / ( 1024 * 1024 ), 0 )
			)
		);
	}

	// ✅ 3. Vérifier que le fichier existe et est lisible
	if ( ! file_exists( $file['tmp_name'] ) || ! is_readable( $file['tmp_name'] ) ) {
		return new WP_Error(
			'file_not_readable',
			__( 'Impossible de lire le fichier uploadé.', 'cgt' )
		);
	}

	// ✅ 4. Vérifier que c'est bien un fichier (pas un dossier)
	if ( ! is_file( $file['tmp_name'] ) ) {
		return new WP_Error(
			'not_a_file',
			__( 'Le fichier uploadé est invalide.', 'cgt' )
		);
	}

	// ✅ 5. CRITIQUE : Vérifier le type MIME réel (magic bytes)
	if ( function_exists( 'finfo_open' ) ) {
		$finfo = finfo_open( FILEINFO_MIME_TYPE );
		$mime = finfo_file( $finfo, $file['tmp_name'] );
		finfo_close( $finfo );

		if ( 'application/pdf' !== $mime ) {
			error_log( sprintf(
				'CGT PDF Validation: MIME type invalide détecté - Attendu: application/pdf, Reçu: %s, Fichier: %s',
				$mime,
				$file['name']
			) );

			return new WP_Error(
				'fake_pdf',
				__( 'Le fichier n\'est pas un PDF valide. Type détecté : ', 'cgt' ) . esc_html( $mime )
			);
		}
	}

	// ✅ 6. Vérifier la signature PDF (magic bytes header)
	$handle = fopen( $file['tmp_name'], 'rb' );
	if ( false === $handle ) {
		return new WP_Error(
			'cannot_open_file',
			__( 'Impossible d\'ouvrir le fichier pour validation.', 'cgt' )
		);
	}

	$header = fread( $handle, 5 );
	fclose( $handle );

	// Les PDF commencent toujours par "%PDF-"
	if ( '%PDF-' !== $header ) {
		error_log( sprintf(
			'CGT PDF Validation: Header signature invalide - Attendu: %%PDF-, Reçu: %s, Fichier: %s',
			bin2hex( $header ),
			$file['name']
		) );

		return new WP_Error(
			'corrupted_pdf',
			__( 'Fichier PDF corrompu ou invalide.', 'cgt' )
		);
	}

	// ✅ 7. Vérifier le footer PDF (optionnel mais recommandé)
	$file_size = filesize( $file['tmp_name'] );
	$handle = fopen( $file['tmp_name'], 'rb' );

	if ( false !== $handle && $file_size > 10 ) {
		// Lire les 10 derniers bytes
		fseek( $handle, -10, SEEK_END );
		$footer = fread( $handle, 10 );
		fclose( $handle );

		// Les PDF valides se terminent généralement par "%%EOF"
		if ( false === strpos( $footer, '%%EOF' ) ) {
			error_log( sprintf(
				'CGT PDF Validation: AVERTISSEMENT - Footer %%EOF manquant pour %s (peut être normal pour certains PDF)',
				$file['name']
			) );
			// Ne pas bloquer, juste un warning
		}
	}

	// ✅ Toutes les validations passées
	return true;
}

/**
 * Hook WordPress pour valider les PDF avant upload
 *
 * @param array $file Fichier à uploader
 * @return array Fichier (ou avec erreur)
 */
function cgt_validate_pdf_on_upload( $file ) {
	// Vérifier si c'est un PDF
	$file_ext = strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );

	if ( 'pdf' !== $file_ext ) {
		// Pas un PDF, laisser WordPress gérer
		return $file;
	}

	// Valider le PDF
	$validation = cgt_validate_pdf_upload( $file );

	if ( is_wp_error( $validation ) ) {
		$file['error'] = $validation->get_error_message();

		// Logger l'échec de validation
		error_log( sprintf(
			'CGT PDF Validation ÉCHEC: %s - Fichier: %s, Taille: %s bytes',
			$validation->get_error_message(),
			$file['name'],
			$file['size']
		) );
	} else {
		// Logger le succès
		error_log( sprintf(
			'CGT PDF Validation SUCCÈS: Fichier: %s, Taille: %s bytes',
			$file['name'],
			$file['size']
		) );
	}

	return $file;
}
add_filter( 'wp_handle_upload_prefilter', 'cgt_validate_pdf_on_upload' );
