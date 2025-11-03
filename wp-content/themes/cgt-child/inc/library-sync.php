<?php
/**
 * Synchronisation automatique des PDF vers la bibliothèque
 * Quand un PDF est ajouté à un article ou tract, il est automatiquement ajouté à la bibliothèque
 *
 * @package CGT_Child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Synchroniser automatiquement les PDF des articles/tracts vers la bibliothèque
 *
 * @param int $post_id Post ID
 * @param WP_Post $post Post object
 * @param bool $update Whether this is an update or new post
 */
function cgt_sync_pdf_to_library( $post_id, $post, $update ) {
	// Vérifier si c'est un article ou tract
	if ( ! in_array( $post->post_type, array( 'post', 'tracts' ), true ) ) {
		return false;
	}

	// Ne pas exécuter lors de l'autosave
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return false;
	}

	// Ne pas exécuter lors des révisions
	if ( wp_is_post_revision( $post_id ) ) {
		return false;
	}

	// Vérifier les permissions
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return false;
	}

	// ✅ FIX RACE CONDITION : Acquérir un lock transient pour éviter les doublons
	$lock_key = 'cgt_sync_lock_' . $post_id;

	if ( false !== get_transient( $lock_key ) ) {
		error_log( sprintf( 'CGT PDF Sync: Lock actif pour post %d, synchronisation annulée', $post_id ) );
		return false;
	}

	// Définir le lock pour 30 secondes
	set_transient( $lock_key, 1, 30 );

	// ✅ GESTION D'ERREURS : try/catch pour libérer le lock même en cas d'erreur
	try {

	// Récupérer l'ID du PDF depuis les meta
	$pdf_url = get_post_meta( $post_id, 'cgt_fichier_pdf', true );
	$pdf_id = $pdf_url ? attachment_url_to_postid( $pdf_url ) : 0;

	if ( ! $pdf_id ) {
		// Pas de PDF, supprimer l'entrée de bibliothèque si elle existe
		$existing_lib = get_post_meta( $post_id, '_cgt_library_sync_id', true );
		if ( $existing_lib ) {
			wp_delete_post( $existing_lib, true );
			delete_post_meta( $post_id, '_cgt_library_sync_id' );
		}
		// Libérer le lock avant de sortir
		delete_transient( $lock_key );
		return false;
	}

	// Vérifier si une entrée bibliothèque existe déjà
	$library_id = get_post_meta( $post_id, '_cgt_library_sync_id', true );

	// Récupérer les catégories du post
	$categories = array();
	if ( 'post' === $post->post_type ) {
		$categories = wp_get_post_categories( $post_id );
	} elseif ( 'tracts' === $post->post_type ) {
		$category_terms = wp_get_post_terms( $post_id, 'thematique' );
		if ( ! is_wp_error( $category_terms ) && ! empty( $category_terms ) ) {
			$categories = wp_list_pluck( $category_terms, 'term_id' );
		}
	}

	// Créer le titre pour la bibliothèque
	$library_title = $post->post_title . ' (PDF)';

	if ( $library_id && get_post( $library_id ) ) {
		// Mettre à jour l'entrée existante
		wp_update_post( array(
			'ID'         => $library_id,
			'post_title' => $library_title,
		) );

		// Mettre à jour le PDF
		update_post_meta( $library_id, '_cgt_pdf_file_id', $pdf_id );

		// Synchroniser les catégories
		if ( ! empty( $categories ) ) {
			wp_set_post_categories( $library_id, $categories, false );
		}
	} else {
		// Créer une nouvelle entrée dans la bibliothèque
		$library_id = wp_insert_post( array(
			'post_type'   => 'cgt_pdf_library',
			'post_title'  => $library_title,
			'post_status' => 'publish',
			'post_author' => $post->post_author,
		) );

		if ( $library_id && ! is_wp_error( $library_id ) ) {
			// Associer le PDF
			update_post_meta( $library_id, '_cgt_pdf_file_id', $pdf_id );

			// Associer les catégories
			if ( ! empty( $categories ) ) {
				wp_set_post_categories( $library_id, $categories, false );
			}

			// Sauvegarder la relation
			update_post_meta( $post_id, '_cgt_library_sync_id', $library_id );

			// Marquer comme auto-synchronisé
			update_post_meta( $library_id, '_cgt_auto_synced', 1 );
			update_post_meta( $library_id, '_cgt_source_post_id', $post_id );
			update_post_meta( $library_id, '_cgt_source_post_type', $post->post_type );
		}
	}

		// ✅ Libérer le lock après succès
		delete_transient( $lock_key );
		return true;

	} catch ( Exception $e ) {
		// ✅ ERREUR : Logger et libérer le lock
		error_log( sprintf(
			'CGT PDF Sync ERREUR [Post:%d, Type:%s]: %s',
			$post_id,
			$post->post_type,
			$e->getMessage()
		) );

		// Libérer le lock même en cas d'erreur
		delete_transient( $lock_key );
		return false;
	}
}
// Priorité 100 pour s'assurer que toutes les metas sont enregistrées
add_action( 'save_post', 'cgt_sync_pdf_to_library', 100, 3 );

/**
 * Supprimer l'entrée bibliothèque quand un article/tract est supprimé
 *
 * @param int $post_id Post ID
 */
function cgt_delete_synced_library_entry( $post_id ) {
	$post_type = get_post_type( $post_id );

	if ( ! in_array( $post_type, array( 'post', 'tracts' ), true ) ) {
		return;
	}

	// Supprimer l'entrée bibliothèque associée
	$library_id = get_post_meta( $post_id, '_cgt_library_sync_id', true );
	if ( $library_id ) {
		wp_delete_post( $library_id, true );
	}
}
add_action( 'before_delete_post', 'cgt_delete_synced_library_entry' );

/**
 * Filtrer la médiathèque pour afficher uniquement les images (pas les PDF)
 * DÉSACTIVÉ pour le moment - À réactiver plus tard si nécessaire
 */
/*
function cgt_filter_media_library( $query ) {
	// Uniquement dans l'admin
	if ( ! is_admin() ) {
		return $query;
	}

	// Ne pas filtrer si on demande explicitement des PDF
	if ( isset( $query['post_mime_type'] ) && 'application/pdf' === $query['post_mime_type'] ) {
		return $query;
	}

	// Filtrer pour n'afficher que les images dans la médiathèque standard
	if ( isset( $query['post_type'] ) && 'attachment' === $query['post_type'] ) {
		$query['post_mime_type'] = 'image';
	}

	return $query;
}
add_filter( 'ajax_query_attachments_args', 'cgt_filter_media_library' );
*/

/**
 * Ajouter une colonne dans la liste des articles/tracts pour voir si un PDF est synchronisé
 *
 * @param array $columns Columns array
 * @return array
 */
function cgt_add_pdf_sync_column( $columns ) {
	$new_columns = array();
	foreach ( $columns as $key => $label ) {
		$new_columns[ $key ] = $label;
		if ( 'title' === $key ) {
			$new_columns['pdf_library'] = '📚 Bibliothèque PDF';
		}
	}
	return $new_columns;
}
add_filter( 'manage_posts_columns', 'cgt_add_pdf_sync_column' );
add_filter( 'manage_tracts_posts_columns', 'cgt_add_pdf_sync_column' );

/**
 * Afficher le statut de synchronisation dans la colonne
 *
 * @param string $column Column name
 * @param int $post_id Post ID
 */
function cgt_render_pdf_sync_column( $column, $post_id ) {
	if ( 'pdf_library' !== $column ) {
		return;
	}

	$pdf_url = get_post_meta( $post_id, 'cgt_fichier_pdf', true );
	$library_id = get_post_meta( $post_id, '_cgt_library_sync_id', true );

	if ( $pdf_url && $library_id ) {
		$edit_link = admin_url( 'post.php?post=' . $library_id . '&action=edit' );
		echo '<span style="color: #10b981;">✓ Synchronisé</span> ';
		echo '<a href="' . esc_url( $edit_link ) . '" target="_blank" title="Voir dans la bibliothèque">📄</a>';
	} elseif ( $pdf_url ) {
		echo '<span style="color: #f59e0b;">⚠ Non synchronisé</span>';
	} else {
		echo '<span style="color: #9ca3af;">— Pas de PDF</span>';
	}
}
add_action( 'manage_posts_custom_column', 'cgt_render_pdf_sync_column', 10, 2 );
add_action( 'manage_tracts_posts_custom_column', 'cgt_render_pdf_sync_column', 10, 2 );
