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

if ( $argc < 2 ) {
	die( "Usage: php parse-xml.php chemin/vers/export.xml\n" );
}

$xml_file = $argv[1];

if ( ! file_exists( $xml_file ) ) {
	die( "Erreur: Le fichier {$xml_file} n'existe pas.\n" );
}

echo "Lecture du fichier XML...\n";

// Charger le XML
libxml_use_internal_errors( true );
$xml = simplexml_load_file( $xml_file );

if ( $xml === false ) {
	echo "Erreur lors du chargement du XML:\n";
	foreach ( libxml_get_errors() as $error ) {
		echo "  - {$error->message}";
	}
	libxml_clear_errors();
	die();
}

// Enregistrer les namespaces WordPress
$namespaces = $xml->getNamespaces( true );
$wp_ns      = isset( $namespaces['wp'] ) ? $namespaces['wp'] : 'http://wordpress.org/export/1.2/';
$content_ns = isset( $namespaces['content'] ) ? $namespaces['content'] : 'http://purl.org/rss/1.0/modules/content/';
$dc_ns      = isset( $namespaces['dc'] ) ? $namespaces['dc'] : 'http://purl.org/dc/elements/1.1/';

echo "Extraction des articles...\n";

$articles = array();
$count    = 0;

foreach ( $xml->channel->item as $item ) {
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

	// Date
	$date_pub = (string) $item->children( $wp_ns )->post_date;
	$date     = date( 'Y-m-d', strtotime( $date_pub ) );
	$year     = date( 'Y', strtotime( $date_pub ) );

	// Contenu
	$content = (string) $item->children( $content_ns )->encoded;
	$content = strip_tags( $content ); // Retirer les balises HTML pour la recherche

	// Extrait
	$excerpt = (string) $item->children( $wp_ns )->post_excerpt;
	if ( empty( $excerpt ) ) {
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
		} elseif ( $domain === 'branche' ) {
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
}

echo "Total: {$count} articles extraits\n";

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
