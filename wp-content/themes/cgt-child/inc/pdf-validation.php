<?php
/**
 * Validation sécurisée des fichiers PDF
 *
 * ✅ SÉCURITÉ :
 * - Validation magic bytes (header PDF)
 * - Validation type MIME réel
 * - Validation taille fichier
 * - Protection contre malware déguisé
 *
 * @package CGT_Child
 * @since 1.2.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Valider un fichier PDF de manière sécurisée
 *
 * @param array $file Fichier à valider ($_FILES array).
 *
 * @return true|WP_Error True si valide, WP_Error sinon.
 */
function cgt_validate_pdf_upload( $file ) {
	// ✅ 1. Vérifier que le fichier existe
	if ( empty( $file['tmp_name'] ) || ! file_exists( $file['tmp_name'] ) ) {
		return new WP_Error( 'file_not_found', __( 'Fichier introuvable.', 'cgt' ) );
	}

	// ✅ 2. Vérifier la taille (15 MB max)
	$max_size = 15 * 1024 * 1024; // 15 MB
	if ( $file['size'] > $max_size ) {
		return new WP_Error(
			'file_too_large',
			sprintf(
				__( 'Fichier trop volumineux. Taille maximale : %s MB.', 'cgt' ),
				number_format( $max_size / 1024 / 1024, 0 )
			)
		);
	}

	// ✅ 3. Vérifier l'extension
	$allowed_ext = array( 'pdf' );
	$file_ext = strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );

	if ( ! in_array( $file_ext, $allowed_ext, true ) ) {
		return new WP_Error(
			'invalid_extension',
			__( 'Extension de fichier non autorisée. Seuls les PDF sont acceptés.', 'cgt' )
		);
	}

	// ✅ 4. Vérifier le type MIME déclaré
	if ( ! in_array( $file['type'], array( 'application/pdf' ), true ) ) {
		return new WP_Error(
			'invalid_mime_type',
			__( 'Type MIME invalide. Seuls les PDF sont acceptés.', 'cgt' )
		);
	}

	// ✅ 5. CRITIQUE : Vérifier le type MIME réel (magic bytes)
	if ( function_exists( 'finfo_open' ) ) {
		$finfo = finfo_open( FILEINFO_MIME_TYPE );
		$mime = finfo_file( $finfo, $file['tmp_name'] );
		finfo_close( $finfo );

		if ( 'application/pdf' !== $mime ) {
			error_log( sprintf(
				'CGT PDF Validation: Type MIME réel incorrect - Déclaré: %s, Réel: %s, Fichier: %s',
				$file['type'],
				$mime,
				$file['name']
			) );

			return new WP_Error(
				'fake_pdf',
				__( 'Le fichier n\'est pas un PDF valide (type MIME incorrect).', 'cgt' )
			);
		}
	}

	// ✅ 6. Vérifier la signature PDF (magic bytes header)
	$handle = fopen( $file['tmp_name'], 'rb' );
	if ( false === $handle ) {
		return new WP_Error( 'cannot_read_file', __( 'Impossible de lire le fichier.', 'cgt' ) );
	}

	$header = fread( $handle, 5 );
	fclose( $handle );

	// PDF doit commencer par "%PDF-"
	if ( '%PDF-' !== $header ) {
		error_log( sprintf(
			'CGT PDF Validation: Magic bytes invalides - Header: %s, Fichier: %s',
			bin2hex( $header ),
			$file['name']
		) );

		return new WP_Error(
			'corrupted_pdf',
			__( 'Fichier PDF corrompu ou invalide (signature incorrecte).', 'cgt' )
		);
	}

	// ✅ 7. Vérifier que le fichier contient "%%EOF" (fin de PDF valide)
	$handle = fopen( $file['tmp_name'], 'rb' );
	if ( false !== $handle ) {
		// Lire les derniers 1024 bytes
		fseek( $handle, -1024, SEEK_END );
		$footer = fread( $handle, 1024 );
		fclose( $handle );

		if ( false === strpos( $footer, '%%EOF' ) ) {
			error_log( sprintf(
				'CGT PDF Validation: EOF manquant - Fichier potentiellement tronqué: %s',
				$file['name']
			) );

			return new WP_Error(
				'incomplete_pdf',
				__( 'Fichier PDF incomplet ou corrompu.', 'cgt' )
			);
		}
	}

	// ✅ 8. Optionnel : Scanner avec ClamAV si disponible
	if ( function_exists( 'clamav_scan_file' ) ) {
		$scan_result = clamav_scan_file( $file['tmp_name'] );
		if ( 0 !== $scan_result ) {
			error_log( sprintf(
				'CGT PDF Validation: Virus détecté - Code: %d, Fichier: %s',
				$scan_result,
				$file['name']
			) );

			return new WP_Error(
				'virus_detected',
				__( 'Fichier malveillant détecté. Upload refusé.', 'cgt' )
			);
		}
	}

	// ✅ Tous les tests passés
	return true;
}

/**
 * Hook pour valider les PDF lors de l'upload
 *
 * @param array $file Fichier uploadé.
 *
 * @return array Fichier avec erreur si validation échoue.
 */
function cgt_validate_pdf_on_upload( $file ) {
	// Vérifier uniquement les PDFs
	$file_type = wp_check_filetype( $file['name'] );

	if ( 'pdf' === $file_type['ext'] ) {
		$validation = cgt_validate_pdf_upload( $file );

		if ( is_wp_error( $validation ) ) {
			$file['error'] = $validation->get_error_message();
		}
	}

	return $file;
}
add_filter( 'wp_handle_upload_prefilter', 'cgt_validate_pdf_on_upload' );
