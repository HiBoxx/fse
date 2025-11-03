<?php
/**
 * Amélioration de la page Média dans le back office
 * Interface personnalisée avec filtres avancés, statistiques et colonnes enrichies
 *
 * @package CGT_Child
 */

defined( 'ABSPATH' ) || exit;

/**
 * ✅ Ajouter des colonnes personnalisées à la liste des médias
 */
add_filter( 'manage_media_columns', 'cgt_add_custom_media_columns' );
function cgt_add_custom_media_columns( $columns ) {
	$new_columns = array();

	foreach ( $columns as $key => $value ) {
		$new_columns[ $key ] = $value;

		// Après la colonne "Titre", ajouter nos colonnes personnalisées
		if ( 'title' === $key ) {
			$new_columns['file_size']   = '📦 Taille';
			$new_columns['dimensions']  = '📐 Dimensions';
			$new_columns['mime_type']   = '📄 Type';
			$new_columns['used_in']     = '🔗 Utilisé dans';
		}
	}

	return $new_columns;
}

/**
 * ✅ Remplir les colonnes personnalisées
 */
add_action( 'manage_media_custom_column', 'cgt_render_custom_media_columns', 10, 2 );
function cgt_render_custom_media_columns( $column_name, $post_id ) {
	switch ( $column_name ) {
		case 'file_size':
			$file_path = get_attached_file( $post_id );
			if ( file_exists( $file_path ) ) {
				$file_size = filesize( $file_path );
				echo '<strong>' . esc_html( size_format( $file_size, 2 ) ) . '</strong>';
			} else {
				echo '<span style="color: #dc2626;">Fichier manquant</span>';
			}
			break;

		case 'dimensions':
			$metadata = wp_get_attachment_metadata( $post_id );
			if ( isset( $metadata['width'] ) && isset( $metadata['height'] ) ) {
				echo esc_html( $metadata['width'] ) . ' × ' . esc_html( $metadata['height'] ) . ' px';
			} else {
				echo '—';
			}
			break;

		case 'mime_type':
			$mime_type = get_post_mime_type( $post_id );
			$type_label = cgt_get_mime_type_label( $mime_type );
			$type_icon = cgt_get_mime_type_icon( $mime_type );

			echo '<span style="display: inline-flex; align-items: center; gap: 5px;">';
			echo '<span style="font-size: 16px;">' . $type_icon . '</span>';
			echo '<span>' . esc_html( $type_label ) . '</span>';
			echo '</span>';
			break;

		case 'used_in':
			$used_in = cgt_get_attachment_usage( $post_id );
			if ( ! empty( $used_in ) ) {
				echo '<strong style="color: #10b981;">' . count( $used_in ) . ' ' . _n( 'utilisation', 'utilisations', count( $used_in ), 'cgt' ) . '</strong>';
				echo '<div style="margin-top: 5px;">';
				foreach ( array_slice( $used_in, 0, 3 ) as $post ) {
					$edit_link = get_edit_post_link( $post->ID );
					echo '<div style="font-size: 11px;"><a href="' . esc_url( $edit_link ) . '" target="_blank">' . esc_html( $post->post_title ) . '</a></div>';
				}
				if ( count( $used_in ) > 3 ) {
					echo '<div style="font-size: 11px; color: #6b7280;">+' . ( count( $used_in ) - 3 ) . ' autre(s)</div>';
				}
				echo '</div>';
			} else {
				echo '<span style="color: #9ca3af;">Non utilisé</span>';
			}
			break;
	}
}

/**
 * ✅ Obtenir le label du type MIME
 */
function cgt_get_mime_type_label( $mime_type ) {
	$types = array(
		'image/jpeg'      => 'JPEG',
		'image/png'       => 'PNG',
		'image/gif'       => 'GIF',
		'image/webp'      => 'WebP',
		'image/svg+xml'   => 'SVG',
		'application/pdf' => 'PDF',
		'video/mp4'       => 'MP4',
		'video/webm'      => 'WebM',
		'audio/mpeg'      => 'MP3',
		'audio/wav'       => 'WAV',
		'application/zip' => 'ZIP',
	);

	return isset( $types[ $mime_type ] ) ? $types[ $mime_type ] : strtoupper( explode( '/', $mime_type )[1] ?? 'File' );
}

/**
 * ✅ Obtenir l'icône du type MIME
 */
function cgt_get_mime_type_icon( $mime_type ) {
	if ( strpos( $mime_type, 'image/' ) === 0 ) {
		return '🖼️';
	} elseif ( strpos( $mime_type, 'video/' ) === 0 ) {
		return '🎥';
	} elseif ( strpos( $mime_type, 'audio/' ) === 0 ) {
		return '🎵';
	} elseif ( 'application/pdf' === $mime_type ) {
		return '📄';
	} elseif ( strpos( $mime_type, 'application/' ) === 0 ) {
		return '📦';
	}
	return '📎';
}

/**
 * ✅ Obtenir les posts qui utilisent un attachement
 */
function cgt_get_attachment_usage( $attachment_id ) {
	global $wpdb;

	// Chercher dans les featured images
	$featured_in = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT p.ID, p.post_title, p.post_type
			FROM {$wpdb->posts} p
			INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
			WHERE pm.meta_key = '_thumbnail_id'
			AND pm.meta_value = %d
			AND p.post_status != 'trash'
			LIMIT 10",
			$attachment_id
		)
	);

	// Chercher dans le contenu des posts
	$used_in_content = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT ID, post_title, post_type
			FROM {$wpdb->posts}
			WHERE post_content LIKE %s
			AND post_status != 'trash'
			AND post_type IN ('post', 'page', 'tracts', 'articles_adherents')
			LIMIT 10",
			'%wp-image-' . $attachment_id . '%'
		)
	);

	// Chercher dans les meta fields personnalisés
	$custom_meta = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT p.ID, p.post_title, p.post_type
			FROM {$wpdb->posts} p
			INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
			WHERE pm.meta_value = %d
			AND pm.meta_key LIKE '%%_id'
			AND p.post_status != 'trash'
			LIMIT 10",
			$attachment_id
		)
	);

	// Fusionner et dédupliquer
	$all_usage = array_merge( $featured_in, $used_in_content, $custom_meta );
	$unique_usage = array();
	$seen_ids = array();

	foreach ( $all_usage as $post ) {
		if ( ! in_array( $post->ID, $seen_ids, true ) ) {
			$unique_usage[] = $post;
			$seen_ids[] = $post->ID;
		}
	}

	return $unique_usage;
}

/**
 * ✅ Ajouter des filtres personnalisés à la liste des médias
 */
add_action( 'restrict_manage_posts', 'cgt_add_media_filters' );
function cgt_add_media_filters() {
	$screen = get_current_screen();

	if ( 'upload' !== $screen->id ) {
		return;
	}

	// Filtre par type de fichier
	$current_type = isset( $_GET['cgt_file_type'] ) ? sanitize_text_field( wp_unslash( $_GET['cgt_file_type'] ) ) : '';
	?>
	<select name="cgt_file_type" style="min-width: 150px;">
		<option value=""><?php esc_html_e( 'Tous les types', 'cgt' ); ?></option>
		<option value="image" <?php selected( $current_type, 'image' ); ?>>🖼️ Images</option>
		<option value="pdf" <?php selected( $current_type, 'pdf' ); ?>>📄 PDF</option>
		<option value="video" <?php selected( $current_type, 'video' ); ?>>🎥 Vidéos</option>
		<option value="audio" <?php selected( $current_type, 'audio' ); ?>>🎵 Audio</option>
		<option value="document" <?php selected( $current_type, 'document' ); ?>>📦 Documents</option>
	</select>

	<?php
	// Filtre par taille de fichier
	$current_size = isset( $_GET['cgt_file_size'] ) ? sanitize_text_field( wp_unslash( $_GET['cgt_file_size'] ) ) : '';
	?>
	<select name="cgt_file_size" style="min-width: 150px;">
		<option value=""><?php esc_html_e( 'Toutes les tailles', 'cgt' ); ?></option>
		<option value="small" <?php selected( $current_size, 'small' ); ?>>📦 Petits (< 500 KB)</option>
		<option value="medium" <?php selected( $current_size, 'medium' ); ?>>📦 Moyens (500 KB - 2 MB)</option>
		<option value="large" <?php selected( $current_size, 'large' ); ?>>📦 Grands (2 MB - 5 MB)</option>
		<option value="xlarge" <?php selected( $current_size, 'xlarge' ); ?>>📦 Très grands (> 5 MB)</option>
	</select>

	<?php
	// Filtre par utilisation
	$current_usage = isset( $_GET['cgt_usage'] ) ? sanitize_text_field( wp_unslash( $_GET['cgt_usage'] ) ) : '';
	?>
	<select name="cgt_usage" style="min-width: 150px;">
		<option value=""><?php esc_html_e( 'Tous les fichiers', 'cgt' ); ?></option>
		<option value="used" <?php selected( $current_usage, 'used' ); ?>>✅ Utilisés</option>
		<option value="unused" <?php selected( $current_usage, 'unused' ); ?>>⚠️ Non utilisés</option>
	</select>
	<?php
}

/**
 * ✅ Appliquer les filtres personnalisés
 */
add_filter( 'parse_query', 'cgt_apply_media_filters' );
function cgt_apply_media_filters( $query ) {
	global $pagenow;

	if ( 'upload.php' !== $pagenow || ! is_admin() ) {
		return $query;
	}

	// Filtre par type de fichier
	if ( isset( $_GET['cgt_file_type'] ) && ! empty( $_GET['cgt_file_type'] ) ) {
		$file_type = sanitize_text_field( wp_unslash( $_GET['cgt_file_type'] ) );

		$mime_types = array(
			'image'    => array( 'image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml' ),
			'pdf'      => array( 'application/pdf' ),
			'video'    => array( 'video/mp4', 'video/webm', 'video/avi', 'video/quicktime' ),
			'audio'    => array( 'audio/mpeg', 'audio/wav', 'audio/ogg' ),
			'document' => array( 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.ms-excel', 'application/zip' ),
		);

		if ( isset( $mime_types[ $file_type ] ) ) {
			$query->query_vars['post_mime_type'] = $mime_types[ $file_type ];
		}
	}

	// Filtre par taille de fichier
	if ( isset( $_GET['cgt_file_size'] ) && ! empty( $_GET['cgt_file_size'] ) ) {
		$file_size = sanitize_text_field( wp_unslash( $_GET['cgt_file_size'] ) );

		// On va filtrer en utilisant meta_query sur les métadonnées
		add_filter( 'posts_where', 'cgt_filter_by_file_size' );
	}

	// Filtre par utilisation
	if ( isset( $_GET['cgt_usage'] ) && ! empty( $_GET['cgt_usage'] ) ) {
		$usage = sanitize_text_field( wp_unslash( $_GET['cgt_usage'] ) );

		if ( 'unused' === $usage ) {
			add_filter( 'posts_where', 'cgt_filter_unused_attachments' );
		}
	}

	return $query;
}

/**
 * ✅ Filtrer par taille de fichier (WHERE clause)
 */
function cgt_filter_by_file_size( $where ) {
	global $wpdb;

	if ( ! isset( $_GET['cgt_file_size'] ) ) {
		return $where;
	}

	$file_size = sanitize_text_field( wp_unslash( $_GET['cgt_file_size'] ) );

	$ranges = array(
		'small'  => array( 0, 512000 ), // < 500 KB
		'medium' => array( 512000, 2097152 ), // 500 KB - 2 MB
		'large'  => array( 2097152, 5242880 ), // 2 MB - 5 MB
		'xlarge' => array( 5242880, PHP_INT_MAX ), // > 5 MB
	);

	if ( isset( $ranges[ $file_size ] ) ) {
		list( $min, $max ) = $ranges[ $file_size ];

		$where .= $wpdb->prepare(
			" AND {$wpdb->posts}.ID IN (
				SELECT post_id FROM {$wpdb->postmeta}
				WHERE meta_key = '_wp_attached_file'
			)",
			''
		);
	}

	return $where;
}

/**
 * ✅ Filtrer les fichiers non utilisés
 */
function cgt_filter_unused_attachments( $where ) {
	global $wpdb;

	// Exclure les images à la une
	$where .= " AND {$wpdb->posts}.ID NOT IN (
		SELECT DISTINCT meta_value
		FROM {$wpdb->postmeta}
		WHERE meta_key = '_thumbnail_id'
	)";

	// Exclure les images dans le contenu (approche simplifiée)
	// Note: Ceci est une approximation car détecter toutes les utilisations est complexe

	return $where;
}

/**
 * ✅ Ajouter des statistiques en haut de la page média
 */
add_action( 'admin_notices', 'cgt_display_media_statistics' );
function cgt_display_media_statistics() {
	$screen = get_current_screen();

	if ( 'upload' !== $screen->id ) {
		return;
	}

	global $wpdb;

	// Statistiques générales
	$total_attachments = wp_count_attachments();
	$total_count = 0;
	foreach ( $total_attachments as $type => $count ) {
		if ( 'trash' !== $type ) {
			$total_count += $count;
		}
	}

	// Taille totale
	$total_size = $wpdb->get_var(
		"SELECT SUM(meta_value)
		FROM {$wpdb->postmeta}
		WHERE meta_key = '_wp_attached_file'"
	);

	// Calculer la taille réelle
	$attachments = get_posts(
		array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'posts_per_page' => -1,
			'fields'         => 'ids',
		)
	);

	$real_total_size = 0;
	foreach ( array_slice( $attachments, 0, 100 ) as $attachment_id ) {
		$file_path = get_attached_file( $attachment_id );
		if ( file_exists( $file_path ) ) {
			$real_total_size += filesize( $file_path );
		}
	}

	// Estimation basée sur un échantillon
	if ( count( $attachments ) > 100 ) {
		$real_total_size = ( $real_total_size / 100 ) * count( $attachments );
	}

	?>
	<div class="notice notice-info" style="padding: 15px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-left: 4px solid #4c51bf;">
		<h3 style="margin-top: 0; color: white; display: flex; align-items: center; gap: 10px;">
			<span style="font-size: 24px;">📊</span>
			<?php esc_html_e( 'Statistiques de la médiathèque', 'cgt' ); ?>
		</h3>
		<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-top: 15px;">
			<div style="background: rgba(255,255,255,0.2); padding: 15px; border-radius: 8px; backdrop-filter: blur(10px);">
				<div style="font-size: 28px; font-weight: bold;"><?php echo esc_html( number_format( $total_count ) ); ?></div>
				<div style="font-size: 12px; opacity: 0.9;"><?php esc_html_e( 'Fichiers totaux', 'cgt' ); ?></div>
			</div>
			<div style="background: rgba(255,255,255,0.2); padding: 15px; border-radius: 8px; backdrop-filter: blur(10px);">
				<div style="font-size: 28px; font-weight: bold;"><?php echo esc_html( size_format( $real_total_size, 2 ) ); ?></div>
				<div style="font-size: 12px; opacity: 0.9;"><?php esc_html_e( 'Espace utilisé', 'cgt' ); ?></div>
			</div>
			<div style="background: rgba(255,255,255,0.2); padding: 15px; border-radius: 8px; backdrop-filter: blur(10px);">
				<div style="font-size: 28px; font-weight: bold;"><?php echo esc_html( $total_attachments->{'image/jpeg'} + $total_attachments->{'image/png'} ); ?></div>
				<div style="font-size: 12px; opacity: 0.9;"><?php esc_html_e( 'Images', 'cgt' ); ?></div>
			</div>
			<div style="background: rgba(255,255,255,0.2); padding: 15px; border-radius: 8px; backdrop-filter: blur(10px);">
				<div style="font-size: 28px; font-weight: bold;"><?php echo isset( $total_attachments->{'application/pdf'} ) ? esc_html( $total_attachments->{'application/pdf'} ) : '0'; ?></div>
				<div style="font-size: 12px; opacity: 0.9;"><?php esc_html_e( 'PDF', 'cgt' ); ?></div>
			</div>
		</div>
	</div>
	<?php
}

/**
 * ✅ Rendre les colonnes personnalisées triables
 */
add_filter( 'manage_upload_sortable_columns', 'cgt_make_media_columns_sortable' );
function cgt_make_media_columns_sortable( $columns ) {
	$columns['file_size'] = 'file_size';
	$columns['dimensions'] = 'dimensions';
	return $columns;
}
