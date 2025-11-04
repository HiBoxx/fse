<?php
/**
 * Interface Médiathèque Moderne CGT
 * Design complet sur mesure avec ergonomie optimale
 *
 * @package CGT_Child
 */

defined( 'ABSPATH' ) || exit;

/**
 * ✅ Ajouter la page personnalisée dans le menu Admin
 */
add_action( 'admin_menu', 'cgt_add_modern_media_page' );
function cgt_add_modern_media_page() {
	add_menu_page(
		'Médiathèque CGT',
		'Médiathèque',
		'upload_files',
		'cgt-media-library',
		'cgt_render_modern_media_page',
		'dashicons-images-alt2',
		11
	);
}

/**
 * ✅ Enregistrer les styles et scripts
 */
add_action( 'admin_enqueue_scripts', 'cgt_enqueue_media_assets' );
function cgt_enqueue_media_assets( $hook ) {
	if ( 'toplevel_page_cgt-media-library' !== $hook ) {
		return;
	}

	// Enqueue media uploader
	wp_enqueue_media();

	// Custom CSS
	wp_add_inline_style( 'wp-admin', cgt_get_media_custom_css() );

	// Custom JS
	wp_add_inline_script( 'jquery', cgt_get_media_custom_js() );
}

/**
 * ✅ Rendu de la page moderne
 */
function cgt_render_modern_media_page() {
	// Paramètres
	$paged           = isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;
	$per_page        = 24;
	$search          = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
	$filter_type     = isset( $_GET['filter_type'] ) ? sanitize_text_field( wp_unslash( $_GET['filter_type'] ) ) : '';
	$filter_size     = isset( $_GET['filter_size'] ) ? sanitize_text_field( wp_unslash( $_GET['filter_size'] ) ) : '';
	$filter_category = isset( $_GET['filter_category'] ) ? absint( $_GET['filter_category'] ) : 0;

	// Query
	$args = array(
		'post_type'      => 'attachment',
		'post_status'    => 'inherit',
		'posts_per_page' => $per_page,
		'paged'          => $paged,
		's'              => $search,
	);

	// Filtre par type
	if ( $filter_type ) {
		$mime_map = array(
			'images'    => array( 'image/jpeg', 'image/png', 'image/gif', 'image/webp' ),
			'pdf'       => array( 'application/pdf' ),
			'videos'    => array( 'video/mp4', 'video/webm' ),
			'documents' => array( 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' ),
		);
		if ( isset( $mime_map[ $filter_type ] ) ) {
			$args['post_mime_type'] = $mime_map[ $filter_type ];
		}
	}

	// ✅ Filtre par catégorie
	if ( $filter_category ) {
		$args['tax_query'] = array(
			array(
				'taxonomy' => 'category',
				'field'    => 'term_id',
				'terms'    => $filter_category,
			),
		);
	}

	$query = new WP_Query( $args );

	// Statistiques
	$stats = cgt_get_media_stats();

	// ✅ Récupérer les catégories
	$categories = get_categories( array(
		'taxonomy'   => 'category',
		'hide_empty' => false,
		'orderby'    => 'name',
		'order'      => 'ASC',
	) );

	?>
	<div class="wrap cgt-media-modern">

		<!-- Header -->
		<div class="cgt-media-header">
			<div class="cgt-media-title">
				<h1>
					<span class="dashicons dashicons-images-alt2"></span>
					Médiathèque CGT
				</h1>
				<p class="subtitle" style="color:white">Gérez vos images, PDF et documents</p>
			</div>
			<div class="cgt-media-actions">
				<button class="button button-primary button-large cgt-upload-btn" onclick="cgtOpenMediaUploader()">
					<span class="dashicons dashicons-upload"></span>
					Ajouter des fichiers
				</button>
			</div>
		</div>

		<!-- Statistiques -->
		<div class="cgt-stats-grid">
			<div class="cgt-stat-card">
				<div class="stat-icon">📊</div>
				<div class="stat-content">
					<div class="stat-value"><?php echo esc_html( number_format( $stats['total'] ) ); ?></div>
					<div class="stat-label">Fichiers totaux</div>
				</div>
			</div>
			<div class="cgt-stat-card">
				<div class="stat-icon">💾</div>
				<div class="stat-content">
					<div class="stat-value"><?php echo esc_html( $stats['size'] ); ?></div>
					<div class="stat-label">Espace utilisé</div>
				</div>
			</div>
			<div class="cgt-stat-card">
				<div class="stat-icon">🖼️</div>
				<div class="stat-content">
					<div class="stat-value"><?php echo esc_html( number_format( $stats['images'] ) ); ?></div>
					<div class="stat-label">Images</div>
				</div>
			</div>
			<div class="cgt-stat-card">
				<div class="stat-icon">📄</div>
				<div class="stat-content">
					<div class="stat-value"><?php echo esc_html( number_format( $stats['pdf'] ) ); ?></div>
					<div class="stat-label">Documents PDF</div>
				</div>
			</div>
		</div>

		<!-- Conteneur principal -->
		<div class="cgt-media-container">

			<!-- Sidebar Filtres -->
			<aside class="cgt-media-sidebar">
				<div class="cgt-filter-section">
					<h3>🔍 Rechercher</h3>
					<form method="get" action="">
						<input type="hidden" name="page" value="cgt-media-library">
						<div class="cgt-search-box">
							<input
								type="text"
								name="s"
								value="<?php echo esc_attr( $search ); ?>"
								placeholder="Rechercher un fichier..."
								class="cgt-search-input"
							>
							<button type="submit" class="cgt-search-btn">
								<span class="dashicons dashicons-search"></span>
							</button>
						</div>
					</form>
				</div>

				<div class="cgt-filter-section">
					<h3>📁 Type de fichier</h3>
					<div class="cgt-filter-list">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=cgt-media-library' ) ); ?>"
						   class="cgt-filter-item <?php echo empty( $filter_type ) ? 'active' : ''; ?>">
							<span class="filter-icon">📦</span>
							<span class="filter-label">Tous les fichiers</span>
							<span class="filter-count"><?php echo esc_html( $stats['total'] ); ?></span>
						</a>
						<a href="<?php echo esc_url( add_query_arg( array( 'page' => 'cgt-media-library', 'filter_type' => 'images' ), admin_url( 'admin.php' ) ) ); ?>"
						   class="cgt-filter-item <?php echo 'images' === $filter_type ? 'active' : ''; ?>">
							<span class="filter-icon">🖼️</span>
							<span class="filter-label">Images</span>
							<span class="filter-count"><?php echo esc_html( $stats['images'] ); ?></span>
						</a>
						<a href="<?php echo esc_url( add_query_arg( array( 'page' => 'cgt-media-library', 'filter_type' => 'pdf' ), admin_url( 'admin.php' ) ) ); ?>"
						   class="cgt-filter-item <?php echo 'pdf' === $filter_type ? 'active' : ''; ?>">
							<span class="filter-icon">📄</span>
							<span class="filter-label">PDF</span>
							<span class="filter-count"><?php echo esc_html( $stats['pdf'] ); ?></span>
						</a>
						<a href="<?php echo esc_url( add_query_arg( array( 'page' => 'cgt-media-library', 'filter_type' => 'videos' ), admin_url( 'admin.php' ) ) ); ?>"
						   class="cgt-filter-item <?php echo 'videos' === $filter_type ? 'active' : ''; ?>">
							<span class="filter-icon">🎥</span>
							<span class="filter-label">Vidéos</span>
							<span class="filter-count"><?php echo esc_html( $stats['videos'] ); ?></span>
						</a>
					</div>
				</div>

				<div class="cgt-filter-section">
					<h3>📏 Taille</h3>
					<div class="cgt-filter-list">
						<a href="<?php echo esc_url( add_query_arg( array( 'page' => 'cgt-media-library', 'filter_size' => 'small' ), admin_url( 'admin.php' ) ) ); ?>"
						   class="cgt-filter-item <?php echo 'small' === $filter_size ? 'active' : ''; ?>">
							<span class="filter-label">Petits fichiers</span>
							<span class="filter-sublabel">< 500 KB</span>
						</a>
						<a href="<?php echo esc_url( add_query_arg( array( 'page' => 'cgt-media-library', 'filter_size' => 'medium' ), admin_url( 'admin.php' ) ) ); ?>"
						   class="cgt-filter-item <?php echo 'medium' === $filter_size ? 'active' : ''; ?>">
							<span class="filter-label">Moyens</span>
							<span class="filter-sublabel">500 KB - 2 MB</span>
						</a>
						<a href="<?php echo esc_url( add_query_arg( array( 'page' => 'cgt-media-library', 'filter_size' => 'large' ), admin_url( 'admin.php' ) ) ); ?>"
						   class="cgt-filter-item <?php echo 'large' === $filter_size ? 'active' : ''; ?>">
							<span class="filter-label">Grands</span>
							<span class="filter-sublabel">> 2 MB</span>
						</a>
					</div>
				</div>

				<!-- ✅ NOUVEAU : Filtre par catégories -->
				<div class="cgt-filter-section">
					<h3>📂 Catégories</h3>
					<div class="cgt-filter-list">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=cgt-media-library' ) ); ?>"
						   class="cgt-filter-item <?php echo empty( $filter_category ) ? 'active' : ''; ?>">
							<span class="filter-label">Toutes les catégories</span>
						</a>
						<?php if ( ! empty( $categories ) ) : ?>
							<?php foreach ( $categories as $category ) : ?>
								<a href="<?php echo esc_url( add_query_arg( array( 'page' => 'cgt-media-library', 'filter_category' => $category->term_id ), admin_url( 'admin.php' ) ) ); ?>"
								   class="cgt-filter-item <?php echo $filter_category === $category->term_id ? 'active' : ''; ?>">
									<span class="filter-icon">📁</span>
									<span class="filter-label"><?php echo esc_html( $category->name ); ?></span>
									<span class="filter-count"><?php echo esc_html( $category->count ); ?></span>
								</a>
							<?php endforeach; ?>
						<?php endif; ?>
					</div>
				</div>

				<div class="cgt-filter-section">
					<button class="button button-secondary button-block" onclick="window.location.href='<?php echo esc_url( admin_url( 'admin.php?page=cgt-media-library' ) ); ?>'">
						<span class="dashicons dashicons-update"></span>
						Réinitialiser les filtres
					</button>
				</div>
			</aside>

			<!-- Zone de contenu -->
			<main class="cgt-media-content">

				<?php if ( $query->have_posts() ) : ?>

					<!-- Grille de médias -->
					<div class="cgt-media-grid">
						<?php while ( $query->have_posts() ) : $query->the_post();
							$attachment_id = get_the_ID();
							$file_url = wp_get_attachment_url( $attachment_id );
							$file_path = get_attached_file( $attachment_id );
							$file_size = file_exists( $file_path ) ? size_format( filesize( $file_path ), 1 ) : 'N/A';
							$mime_type = get_post_mime_type( $attachment_id );
							$metadata = wp_get_attachment_metadata( $attachment_id );
							$is_image = strpos( $mime_type, 'image/' ) === 0;
							$thumbnail = $is_image ? wp_get_attachment_image_url( $attachment_id, 'medium' ) : '';
							?>

							<div class="cgt-media-card" data-id="<?php echo esc_attr( $attachment_id ); ?>">

								<!-- Preview -->
								<div class="media-preview">
									<?php if ( $is_image && $thumbnail ) : ?>
										<img src="<?php echo esc_url( $thumbnail ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>">
									<?php elseif ( 'application/pdf' === $mime_type ) : ?>
										<div class="media-icon pdf">
											<span class="dashicons dashicons-pdf"></span>
											<span class="icon-label">PDF</span>
										</div>
									<?php elseif ( strpos( $mime_type, 'video/' ) === 0 ) : ?>
										<div class="media-icon video">
											<span class="dashicons dashicons-video-alt3"></span>
											<span class="icon-label">VIDEO</span>
										</div>
									<?php else : ?>
										<div class="media-icon document">
											<span class="dashicons dashicons-media-document"></span>
											<span class="icon-label">DOC</span>
										</div>
									<?php endif; ?>
								</div>

								<!-- Info -->
								<div class="media-info">
									<h4 class="media-title" title="<?php echo esc_attr( get_the_title() ); ?>">
										<?php echo esc_html( wp_trim_words( get_the_title(), 5, '...' ) ); ?>
									</h4>
									<div class="media-meta">
										<span class="meta-item">
											<span class="dashicons dashicons-database"></span>
											<?php echo esc_html( $file_size ); ?>
										</span>
										<?php if ( isset( $metadata['width'] ) ) : ?>
											<span class="meta-item">
												<span class="dashicons dashicons-admin-customizer"></span>
												<?php echo esc_html( $metadata['width'] . '×' . $metadata['height'] ); ?>
											</span>
										<?php endif; ?>
									</div>
								</div>

								<!-- Actions -->
								<div class="media-actions">
									<a href="<?php echo esc_url( $file_url ); ?>" target="_blank" class="media-btn" title="Voir">
										<span class="dashicons dashicons-visibility"></span>
									</a>
									<a href="<?php echo esc_url( get_edit_post_link( $attachment_id ) ); ?>" class="media-btn" title="Modifier">
										<span class="dashicons dashicons-edit"></span>
									</a>
									<button onclick="cgtCopyMediaUrl('<?php echo esc_js( $file_url ); ?>')" class="media-btn" title="Copier l'URL">
										<span class="dashicons dashicons-admin-links"></span>
									</button>
									<button onclick="cgtDeleteMedia(<?php echo esc_js( $attachment_id ); ?>)" class="media-btn danger" title="Supprimer">
										<span class="dashicons dashicons-trash"></span>
									</button>
								</div>

							</div>

						<?php endwhile; ?>
					</div>

					<!-- Pagination -->
					<?php if ( $query->max_num_pages > 1 ) : ?>
						<div class="cgt-pagination">
							<?php
							echo paginate_links( array(
								'base'      => add_query_arg( 'paged', '%#%' ),
								'format'    => '',
								'current'   => $paged,
								'total'     => $query->max_num_pages,
								'prev_text' => '<span class="dashicons dashicons-arrow-left-alt2"></span> Précédent',
								'next_text' => 'Suivant <span class="dashicons dashicons-arrow-right-alt2"></span>',
							) );
							?>
						</div>
					<?php endif; ?>

				<?php else : ?>

					<!-- Empty State -->
					<div class="cgt-empty-state">
						<div class="empty-icon">📭</div>
						<h3>Aucun fichier trouvé</h3>
						<p>Ajoutez votre premier fichier ou modifiez vos filtres</p>
						<button class="button button-primary" onclick="cgtOpenMediaUploader()">
							<span class="dashicons dashicons-upload"></span>
							Ajouter des fichiers
						</button>
					</div>

				<?php endif; ?>
				<?php wp_reset_postdata(); ?>

			</main>

		</div>

	</div>

	<script>
	function cgtOpenMediaUploader() {
		var mediaUploader = wp.media({
			title: 'Ajouter des fichiers',
			button: { text: 'Ajouter' },
			multiple: true
		});
		mediaUploader.on('select', function() {
			location.reload();
		});
		mediaUploader.open();
	}

	function cgtCopyMediaUrl(url) {
		navigator.clipboard.writeText(url).then(function() {
			alert('✅ URL copiée dans le presse-papier !');
		});
	}

	function cgtDeleteMedia(id) {
		if (!confirm('Êtes-vous sûr de vouloir supprimer ce fichier ?')) {
			return;
		}

		jQuery.post(ajaxurl, {
			action: 'delete-post',
			id: id,
			_ajax_nonce: '<?php echo wp_create_nonce( "delete-post_" . get_the_ID() ); ?>'
		}, function() {
			location.reload();
		});
	}
	</script>
	<?php
}

/**
 * ✅ Obtenir les statistiques
 */
function cgt_get_media_stats() {
	$attachments = get_posts( array(
		'post_type'      => 'attachment',
		'post_status'    => 'inherit',
		'posts_per_page' => -1,
		'fields'         => 'ids',
	) );

	$total = count( $attachments );
	$images = 0;
	$pdf = 0;
	$videos = 0;
	$total_size = 0;

	foreach ( $attachments as $attachment_id ) {
		$mime_type = get_post_mime_type( $attachment_id );
		$file_path = get_attached_file( $attachment_id );

		if ( strpos( $mime_type, 'image/' ) === 0 ) {
			$images++;
		} elseif ( 'application/pdf' === $mime_type ) {
			$pdf++;
		} elseif ( strpos( $mime_type, 'video/' ) === 0 ) {
			$videos++;
		}

		if ( file_exists( $file_path ) ) {
			$total_size += filesize( $file_path );
		}
	}

	return array(
		'total'  => $total,
		'images' => $images,
		'pdf'    => $pdf,
		'videos' => $videos,
		'size'   => size_format( $total_size, 2 ),
	);
}

/**
 * ✅ CSS Personnalisé
 */
function cgt_get_media_custom_css() {
	return "
	.cgt-media-modern {
		background: #f8fafc;
		margin: -20px -20px -10px;
		padding: 0;
	}

	.cgt-media-header {
		background: linear-gradient(135deg, #e30613 0%, #b8050f 100%);
		padding: 30px 40px;
		color: white;
		display: flex;
		justify-content: space-between;
		align-items: center;
		box-shadow: 0 4px 6px rgba(0,0,0,0.1);
	}

	.cgt-media-title h1 {
		margin: 0;
		font-size: 32px;
		font-weight: 600;
		display: flex;
		align-items: center;
		gap: 15px;
		color: white;
	}

	.cgt-media-title .subtitle {
		margin: 5px 0 0 0;
		opacity: 0.9;
		font-size: 14px;
	}

	.cgt-upload-btn {
		background: white !important;
		color: #e30613 !important;
		border: none !important;
		padding: 12px 24px !important;
		font-size: 15px !important;
		font-weight: 600 !important;
		display: inline-flex !important;
		align-items: center !important;
		gap: 8px !important;
		border-radius: 8px !important;
		box-shadow: 0 2px 4px rgba(0,0,0,0.1) !important;
		cursor: pointer !important;
		transition: all 0.3s ease !important;
	}

	.cgt-upload-btn:hover {
		transform: translateY(-2px);
		box-shadow: 0 4px 8px rgba(0,0,0,0.15) !important;
	}

	.cgt-stats-grid {
		display: grid;
		grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
		gap: 20px;
		padding: 30px 40px;
		background: white;
		border-bottom: 1px solid #e5e7eb;
	}

	.cgt-stat-card {
		background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
		border: 1px solid #e5e7eb;
		border-radius: 12px;
		padding: 20px;
		display: flex;
		align-items: center;
		gap: 15px;
		transition: all 0.3s ease;
	}

	.cgt-stat-card:hover {
		transform: translateY(-3px);
		box-shadow: 0 8px 16px rgba(0,0,0,0.08);
		border-color: #e30613;
	}

	.stat-icon {
		font-size: 32px;
		width: 50px;
		height: 50px;
		display: flex;
		align-items: center;
		justify-content: center;
		background: white;
		border-radius: 10px;
		box-shadow: 0 2px 4px rgba(0,0,0,0.05);
	}

	.stat-value {
		font-size: 28px;
		font-weight: 700;
		color: #1e293b;
		line-height: 1;
	}

	.stat-label {
		font-size: 13px;
		color: #64748b;
		margin-top: 5px;
	}

	.cgt-media-container {
		display: grid;
		grid-template-columns: 280px 1fr;
		gap: 0;
		min-height: calc(100vh - 400px);
	}

	.cgt-media-sidebar {
		background: white;
		padding: 30px 20px;
		border-right: 1px solid #e5e7eb;
	}

	.cgt-filter-section {
		margin-bottom: 30px;
	}

	.cgt-filter-section h3 {
		font-size: 14px;
		font-weight: 600;
		color: #1e293b;
		margin: 0 0 15px 0;
		display: flex;
		align-items: center;
		gap: 8px;
	}

	.cgt-search-box {
		display: flex;
		gap: 5px;
	}

	.cgt-search-input {
		flex: 1;
		padding: 10px 15px;
		border: 2px solid #e5e7eb;
		border-radius: 8px;
		font-size: 14px;
		transition: border-color 0.3s ease;
	}

	.cgt-search-input:focus {
		border-color: #e30613;
		outline: none;
	}

	.cgt-search-btn {
		background: #e30613;
		color: white;
		border: none;
		padding: 10px 15px;
		border-radius: 8px;
		cursor: pointer;
		transition: background 0.3s ease;
	}

	.cgt-search-btn:hover {
		background: #b8050f;
	}

	.cgt-filter-list {
		display: flex;
		flex-direction: column;
		gap: 5px;
	}

	.cgt-filter-item {
		display: flex;
		align-items: center;
		gap: 10px;
		padding: 12px 15px;
		border-radius: 8px;
		text-decoration: none;
		color: #64748b;
		transition: all 0.2s ease;
		border: 1px solid transparent;
	}

	.cgt-filter-item:hover {
		background: #f8fafc;
		color: #e30613;
	}

	.cgt-filter-item.active {
		background: #fee2e2;
		color: #e30613;
		font-weight: 600;
		border-color: #fca5a5;
	}

	.filter-icon {
		font-size: 18px;
	}

	.filter-label {
		flex: 1;
		font-size: 14px;
	}

	.filter-sublabel {
		font-size: 11px;
		color: #94a3b8;
		display: block;
		margin-top: 2px;
	}

	.filter-count {
		background: #f1f5f9;
		color: #64748b;
		padding: 2px 8px;
		border-radius: 12px;
		font-size: 12px;
		font-weight: 600;
	}

	.cgt-filter-item.active .filter-count {
		background: #e30613;
		color: white;
	}

	.button-block {
		width: 100%;
		justify-content: center;
	}

	.cgt-media-content {
		background: #f8fafc;
		padding: 30px 40px;
	}

	.cgt-media-grid {
		display: grid;
		grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
		gap: 20px;
		margin-bottom: 30px;
	}

	.cgt-media-card {
		background: white;
		border-radius: 12px;
		overflow: hidden;
		box-shadow: 0 1px 3px rgba(0,0,0,0.08);
		transition: all 0.3s ease;
		border: 1px solid #e5e7eb;
	}

	.cgt-media-card:hover {
		transform: translateY(-5px);
		box-shadow: 0 8px 20px rgba(0,0,0,0.12);
		border-color: #e30613;
	}

	.media-preview {
		width: 100%;
		height: 180px;
		background: #f8fafc;
		display: flex;
		align-items: center;
		justify-content: center;
		overflow: hidden;
	}

	.media-preview img {
		width: 100%;
		height: 100%;
		object-fit: cover;
	}

	.media-icon {
		display: flex;
		flex-direction: column;
		align-items: center;
		gap: 10px;
		color: #94a3b8;
	}

	.media-icon .dashicons {
		font-size: 48px;
		width: 48px;
		height: 48px;
	}

	.media-icon.pdf { color: #ef4444; }
	.media-icon.video { color: #8b5cf6; }
	.media-icon.document { color: #3b82f6; }

	.icon-label {
		font-size: 11px;
		font-weight: 600;
		letter-spacing: 1px;
	}

	.media-info {
		padding: 15px;
	}

	.media-title {
		margin: 0 0 8px 0;
		font-size: 14px;
		font-weight: 600;
		color: #1e293b;
		line-height: 1.4;
	}

	.media-meta {
		display: flex;
		flex-wrap: wrap;
		gap: 10px;
		font-size: 12px;
		color: #64748b;
	}

	.meta-item {
		display: flex;
		align-items: center;
		gap: 4px;
	}

	.meta-item .dashicons {
		font-size: 14px;
		width: 14px;
		height: 14px;
	}

	.media-actions {
		display: flex;
		border-top: 1px solid #e5e7eb;
		background: #f8fafc;
	}

	.media-btn {
		flex: 1;
		display: flex;
		align-items: center;
		justify-content: center;
		padding: 10px;
		border: none;
		background: transparent;
		color: #64748b;
		cursor: pointer;
		transition: all 0.2s ease;
		text-decoration: none;
		border-right: 1px solid #e5e7eb;
	}

	.media-btn:last-child {
		border-right: none;
	}

	.media-btn:hover {
		background: white;
		color: #e30613;
	}

	.media-btn.danger:hover {
		color: #ef4444;
		background: #fef2f2;
	}

	.cgt-pagination {
		display: flex;
		justify-content: center;
		gap: 5px;
		margin-top: 30px;
	}

	.cgt-pagination a,
	.cgt-pagination span {
		padding: 10px 15px;
		background: white;
		border: 1px solid #e5e7eb;
		border-radius: 8px;
		text-decoration: none;
		color: #64748b;
		transition: all 0.2s ease;
		display: inline-flex;
		align-items: center;
		gap: 5px;
	}

	.cgt-pagination a:hover {
		background: #e30613;
		color: white;
		border-color: #e30613;
	}

	.cgt-pagination .current {
		background: #e30613;
		color: white;
		border-color: #e30613;
		font-weight: 600;
	}

	.cgt-empty-state {
		text-align: center;
		padding: 80px 20px;
	}

	.empty-icon {
		font-size: 64px;
		margin-bottom: 20px;
	}

	.cgt-empty-state h3 {
		font-size: 24px;
		color: #1e293b;
		margin: 0 0 10px 0;
	}

	.cgt-empty-state p {
		color: #64748b;
		margin-bottom: 25px;
	}
	";
}

/**
 * ✅ JavaScript
 */
function cgt_get_media_custom_js() {
	return "";
}
