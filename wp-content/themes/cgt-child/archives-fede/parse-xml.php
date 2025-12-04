<?php
/**
 * Parser XML vers JSON pour les archives de la fédération
 *
 * Convertit un export WordPress XML (WXR) en fichier JSON optimisé
 * pour la recherche et le filtrage côté client.
 *
 * Usage: php parse-xml.php chemin/vers/export.xml
 */

if ( php_sapi_name() !== 'cli' ) {
	die( 'Ce script doit être exécuté en ligne de commande.' );
}

// Augmenter la mémoire pour la génération du gros JSON.
ini_set( 'memory_limit', '512M' );

if ( $argc < 2 ) {
	die( "Usage: php parse-xml.php chemin/vers/export.xml\n" );
}

$xml_file = $argv[1];

if ( ! file_exists( $xml_file ) ) {
	die( "Erreur: Le fichier {$xml_file} n'existe pas.\n" );
}

echo "Lecture du fichier XML...\n";

// Charger le XML avec DOMDocument (plus robuste)
libxml_use_internal_errors( true );

// Lire le contenu et nettoyer
$xml_content = file_get_contents( $xml_file );

// Remplacer les caractères invalides
$xml_content = preg_replace( '/[^\x{0009}\x{000a}\x{000d}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]+/u', '', $xml_content );

// Charger avec DOMDocument
$dom = new DOMDocument();
$dom->recover            = true;  // Mode de récupération
$dom->strictErrorChecking = false; // Désactiver les erreurs strictes

$loaded = $dom->loadXML( $xml_content, LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_NOENT | LIBXML_NOCDATA );

if ( ! $loaded ) {
	echo "⚠️  Le XML contient des erreurs mais on va essayer de récupérer les données...\n";
}

// Convertir en SimpleXML pour faciliter la navigation
$xml = simplexml_import_dom( $dom );

if ( $xml === false ) {
	echo "❌ Impossible de parser le XML.\n";
	libxml_clear_errors();
	die();
}

// Afficher les avertissements mais continuer
$errors = libxml_get_errors();
if ( ! empty( $errors ) ) {
	echo "⚠️  " . count( $errors ) . " avertissements trouvés (ignorés)\n";
}
libxml_clear_errors();

// Enregistrer les namespaces WordPress
$namespaces = $xml->getNamespaces( true );
$wp_ns      = isset( $namespaces['wp'] ) ? $namespaces['wp'] : 'http://wordpress.org/export/1.2/';
$content_ns = isset( $namespaces['content'] ) ? $namespaces['content'] : 'http://purl.org/rss/1.0/modules/content/';
$dc_ns      = isset( $namespaces['dc'] ) ? $namespaces['dc'] : 'http://purl.org/dc/elements/1.1/';

echo "Extraction des articles...\n";

// Charger un mapping ID -> URL des pièces jointes (CSV exporté)
$attachments  = array();
// Domaine du site source (à adapter si besoin)
$uploads_base = 'https://www.soc-etudes.cgt.fr/wp-content/uploads/';

/**
 * Normalise un chemin/URL vers une URL complète basée sur wp-content/uploads.
 */
function cgt_resolve_upload_url( $value, $uploads_base ) {
	if ( empty( $value ) ) {
		return '';
	}

	// Si c'est déjà une URL http/https.
	if ( strpos( $value, 'http' ) === 0 ) {
		return $value;
	}

	// Si c'est un chemin absolu du serveur, on récupère après /wp-content/uploads/
	if ( false !== ( $pos = strpos( $value, '/wp-content/uploads/' ) ) ) {
		$relative = substr( $value, $pos + strlen( '/wp-content/uploads/' ) );
		return rtrim( $uploads_base, '/' ) . '/' . ltrim( $relative, '/' );
	}

	// Sinon considérer que c'est un chemin relatif dans uploads.
	return rtrim( $uploads_base, '/' ) . '/' . ltrim( $value, '/' );
}

/**
 * Charge un CSV de posts/attachments et remplit $attachments.
 */
function cgt_load_csv_mapping( $file, &$attachments, $uploads_base ) {
	if ( ! file_exists( $file ) || ! ( $handle = fopen( $file, 'r' ) ) ) {
		return;
	}

	$header = fgetcsv( $handle, 0, ',', '"', '\\' );
	$cols   = array_flip( $header );

	$id_col   = isset( $cols['ID'] ) ? $cols['ID'] : null;
	$guid_col = isset( $cols['guid'] ) ? $cols['guid'] : null;
	$file_col = isset( $cols['attached_file'] ) ? $cols['attached_file'] : null;

	while ( ( $row = fgetcsv( $handle, 0, ',', '"', '\\' ) ) !== false ) {
		if ( null === $id_col ) {
			continue;
		}
		$att_id = (int) $row[ $id_col ];
		if ( $att_id <= 0 ) {
			continue;
		}

		$url = '';

		if ( null !== $guid_col && ! empty( $row[ $guid_col ] ) ) {
			$url = cgt_resolve_upload_url( $row[ $guid_col ], $uploads_base );
		}

		if ( empty( $url ) && null !== $file_col && ! empty( $row[ $file_col ] ) ) {
			$url = cgt_resolve_upload_url( $row[ $file_col ], $uploads_base );
		}

		if ( $url ) {
			$attachments[ $att_id ] = $url;
		}
	}
	fclose( $handle );
}

// Charger tous les CSV de posts/attachments présents (ex: scet43_posts.csv, scet43_posts (1).csv)
foreach ( glob( __DIR__ . '/scet43_posts*.csv' ) as $csv_file ) {
	cgt_load_csv_mapping( $csv_file, $attachments, $uploads_base );
}

$articles = array();
$count    = 0;
$errors   = 0;

foreach ( $xml->channel->item as $item ) {
	try {
		// Récupérer le type de post
		$post_type = (string) $item->children( $wp_ns )->post_type;

		// Ignorer les pages, attachments, etc. - ne garder que les posts
		if ( $post_type !== 'post' ) {
			continue;
		}

		// Récupérer le statut
		$status = (string) $item->children( $wp_ns )->status;

		// Ne garder que les articles publiés
		if ( $status !== 'publish' ) {
			continue;
		}

		$count++;

		// ID de l'article
		$post_id = (int) $item->children( $wp_ns )->post_id;

		// Titre
		$title = (string) $item->title;
		if ( empty( $title ) ) {
			$title = "Article sans titre (ID: {$post_id})";
		}

		// Date
		$date_pub = (string) $item->children( $wp_ns )->post_date;
		if ( ! empty( $date_pub ) && strtotime( $date_pub ) ) {
			$date = date( 'Y-m-d', strtotime( $date_pub ) );
			$year = date( 'Y', strtotime( $date_pub ) );
		} else {
			$date = date( 'Y-m-d' );
			$year = date( 'Y' );
		}

		// Contenu
		$raw_content = (string) $item->children( $content_ns )->encoded;
		$content     = strip_tags( $raw_content ); // Retirer les balises HTML pour la recherche
		$content     = preg_replace( '/\s+/', ' ', $content ); // Normaliser les espaces

		// Extrait
		$excerpt = (string) $item->children( $wp_ns )->post_excerpt;
		if ( empty( $excerpt ) && ! empty( $content ) ) {
			$excerpt = mb_substr( $content, 0, 300 ) . '...';
		}

		// Catégories
		$categories = array();
		$tags       = array();
		$branches   = array();

		foreach ( $item->category as $cat ) {
			$domain   = (string) $cat['domain'];
			$nicename = (string) $cat['nicename'];
			$name     = (string) $cat;

			if ( $domain === 'category' ) {
				$categories[] = array(
					'slug' => $nicename,
					'name' => $name,
				);
			} elseif ( $domain === 'post_tag' ) {
				$tags[] = array(
					'slug' => $nicename,
					'name' => $name,
				);
			} elseif ( $domain === 'branche' || $domain === 'branches' ) {
				// Le WXR utilise le domaine "branches" (pluriel) : on accepte les deux pour compatibilité.
				$branches[] = array(
					'slug' => $nicename,
					'name' => $name,
				);
			}
		}

		// Lien PDF (chercher dans les postmeta)
		$pdf_url = '';
		foreach ( $item->children( $wp_ns )->postmeta as $meta ) {
			$meta_key   = (string) $meta->meta_key;
			$meta_value = (string) $meta->meta_value;

			// Chercher les meta keys qui pourraient contenir un PDF
			if ( strpos( $meta_key, 'pdf' ) !== false || strpos( $meta_key, 'file' ) !== false ) {
				if ( strpos( $meta_value, '.pdf' ) !== false ) {
					$pdf_url = $meta_value;
					break;
				}
			}
		}

		// Si rien trouvé, tenter de résoudre les shortcodes/blocs WPFD avec le mapping CSV.
		if ( empty( $pdf_url ) ) {
			// Shortcode classique.
			if ( preg_match_all( '/\\[wpfd_single_file[^\\]]*id\\s*=\\s*\"?(\\d+)\"?[^\\]]*\\]/i', $raw_content, $matches ) ) {
				foreach ( $matches[1] as $sid ) {
					$sid = (int) $sid;
					if ( isset( $attachments[ $sid ] ) ) {
						$pdf_url = $attachments[ $sid ];
						break;
					}
				}
			}
		}

		if ( empty( $pdf_url ) ) {
			// Blocs Gutenberg WPFD : selectedFileId":"12345
			if ( preg_match_all( '/selectedFileId\\\"?:\\\"?(\\d+)\\\"?/i', $raw_content, $matches ) ) {
				foreach ( $matches[1] as $sid ) {
					$sid = (int) $sid;
					if ( isset( $attachments[ $sid ] ) ) {
						$pdf_url = $attachments[ $sid ];
						break;
					}
				}
			}
		}

		if ( empty( $pdf_url ) ) {
			// Dernier recours : chercher un nombre qui correspond à un ID connu après le mot wpfd.
			if ( preg_match_all( '/wpfd[^\\d]{0,30}(\\d{3,})/i', $raw_content, $matches ) ) {
				foreach ( $matches[1] as $sid ) {
					$sid = (int) $sid;
					if ( isset( $attachments[ $sid ] ) ) {
						$pdf_url = $attachments[ $sid ];
						break;
					}
				}
			}
		}

		// Construire l'objet article
		$article = array(
			'id'         => $post_id,
			'title'      => $title,
			'date'       => $date,
			'year'       => $year,
			'excerpt'    => $excerpt,
			'content'    => $content,
			'categories' => $categories,
			'tags'       => $tags,
			'branches'   => $branches,
			'pdf_url'    => $pdf_url,
		);

		$articles[] = $article;

		if ( $count % 100 === 0 ) {
			echo "  {$count} articles traités...\n";
		}
	} catch ( Exception $e ) {
		$errors++;
		// Continuer même en cas d'erreur
		continue;
	}
}

echo "Total: {$count} articles extraits\n";
if ( $errors > 0 ) {
	echo "⚠️  {$errors} articles ignorés (erreurs de parsing)\n";
}

// Trier par date décroissante
usort( $articles, function( $a, $b ) {
	return strcmp( $b['date'], $a['date'] );
} );

// Générer le JSON
echo "Génération du fichier JSON...\n";

$json_data = array(
	'generated_at' => date( 'Y-m-d H:i:s' ),
	'total'        => $count,
	'articles'     => $articles,
);

$json = json_encode( $json_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );

if ( $json === false ) {
	die( "Erreur lors de la génération du JSON: " . json_last_error_msg() . "\n" );
}

// Sauvegarder le fichier
$output_file = __DIR__ . '/data.json';
file_put_contents( $output_file, $json );

echo "✅ Fichier généré: {$output_file}\n";
echo "   Taille: " . number_format( filesize( $output_file ) / 1024 / 1024, 2 ) . " MB\n";
echo "\n";
