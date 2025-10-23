<?php
/**
 * Script temporaire pour créer la page de connexion
 *
 * INSTRUCTIONS:
 * 1. Accédez à ce fichier via votre navigateur: http://votre-site.fr/create-connexion-page.php
 * 2. La page sera créée automatiquement
 * 3. Supprimez ce fichier après utilisation pour des raisons de sécurité
 */

// Charger WordPress
require_once __DIR__ . '/wp-load.php';

// Afficher en HTML
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Création de la page Connexion CGT</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #d00000;
            margin-bottom: 20px;
        }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 4px;
            border: 1px solid #c3e6cb;
            margin: 20px 0;
        }
        .info {
            background: #d1ecf1;
            color: #0c5460;
            padding: 15px;
            border-radius: 4px;
            border: 1px solid #bee5eb;
            margin: 20px 0;
        }
        .warning {
            background: #fff3cd;
            color: #856404;
            padding: 15px;
            border-radius: 4px;
            border: 1px solid #ffeeba;
            margin: 20px 0;
        }
        a {
            color: #d00000;
            text-decoration: none;
            font-weight: bold;
        }
        a:hover {
            text-decoration: underline;
        }
        code {
            background: #f8f9fa;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
        }
        .button {
            display: inline-block;
            background: #d00000;
            color: white;
            padding: 12px 24px;
            border-radius: 4px;
            margin-top: 20px;
            text-decoration: none;
        }
        .button:hover {
            background: #b00000;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔐 Création de la page Connexion CGT</h1>

<?php

// Vérifier si la page existe déjà
$existing_page = get_page_by_path('connexion');

if ($existing_page) {
    echo '<div class="info">';
    echo '<h2>✓ La page existe déjà</h2>';
    echo '<p><strong>ID de la page:</strong> ' . $existing_page->ID . '</p>';
    echo '<p><strong>URL:</strong> <a href="' . get_permalink($existing_page->ID) . '" target="_blank">' . get_permalink($existing_page->ID) . '</a></p>';

    // Mettre à jour le template si nécessaire
    $current_template = get_post_meta($existing_page->ID, '_wp_page_template', true);
    if ($current_template !== 'page-connexion.php') {
        update_post_meta($existing_page->ID, '_wp_page_template', 'page-connexion.php');
        echo '<p>✓ Template mis à jour vers <code>page-connexion.php</code></p>';
    } else {
        echo '<p>✓ Le template <code>page-connexion.php</code> est déjà assigné</p>';
    }
    echo '</div>';

    echo '<div class="success">';
    echo '<h3>Tout est prêt !</h3>';
    echo '<p>Votre page de connexion est accessible. Vous pouvez maintenant :</p>';
    echo '<ul>';
    echo '<li>Accéder à votre <a href="' . get_permalink($existing_page->ID) . '" target="_blank">page de connexion</a></li>';
    echo '<li>Gérer les adhésions dans <a href="' . admin_url('edit.php?post_type=cgt_adhesion') . '" target="_blank">l\'admin WordPress → Adhésions</a></li>';
    echo '<li>Modifier la page dans <a href="' . admin_url('post.php?post=' . $existing_page->ID . '&action=edit') . '" target="_blank">l\'éditeur WordPress</a></li>';
    echo '</ul>';
    echo '</div>';

    echo '<div class="warning">';
    echo '<h3>⚠️ Sécurité importante</h3>';
    echo '<p>Supprimez ce fichier <code>create-connexion-page.php</code> maintenant pour des raisons de sécurité !</p>';
    echo '</div>';
} else {
    // Créer la nouvelle page
    $page_data = array(
        'post_title'    => 'Connexion',
        'post_name'     => 'connexion',
        'post_content'  => '<!-- Cette page utilise le template personnalisé de connexion CGT -->',
        'post_status'   => 'publish',
        'post_type'     => 'page',
        'post_author'   => 1,
        'menu_order'    => 0,
        'comment_status' => 'closed',
        'ping_status'   => 'closed',
    );

    // Insérer la page
    $page_id = wp_insert_post($page_data);

    if (is_wp_error($page_id)) {
        echo '<div class="warning">';
        echo '<h2>✗ Erreur</h2>';
        echo '<p>Erreur lors de la création de la page: ' . esc_html($page_id->get_error_message()) . '</p>';
        echo '</div>';
    } else {
        // Assigner le template personnalisé
        update_post_meta($page_id, '_wp_page_template', 'page-connexion.php');

        echo '<div class="success">';
        echo '<h2>✓ Page créée avec succès !</h2>';
        echo '<p><strong>ID de la page:</strong> ' . $page_id . '</p>';
        echo '<p><strong>Titre:</strong> Connexion</p>';
        echo '<p><strong>Slug:</strong> connexion</p>';
        echo '<p><strong>Template:</strong> <code>page-connexion.php</code></p>';
        echo '<p><strong>URL:</strong> <a href="' . get_permalink($page_id) . '" target="_blank">' . get_permalink($page_id) . '</a></p>';
        echo '</div>';

        echo '<div class="info">';
        echo '<h3>📋 Prochaines étapes</h3>';
        echo '<ol>';
        echo '<li><strong>Accédez à votre page:</strong> <a href="' . get_permalink($page_id) . '" target="_blank" class="button">Voir la page de connexion</a></li>';
        echo '<li><strong>Gérez les adhésions:</strong> <a href="' . admin_url('edit.php?post_type=cgt_adhesion') . '" target="_blank">Admin WordPress → Adhésions</a></li>';
        echo '<li><strong>Testez le formulaire:</strong> Cliquez sur "Devenir adhérent" et remplissez le formulaire</li>';
        echo '<li><strong>Personnalisez si besoin:</strong> <a href="' . admin_url('post.php?post=' . $page_id . '&action=edit') . '" target="_blank">Modifier la page</a></li>';
        echo '</ol>';
        echo '</div>';

        echo '<div class="warning">';
        echo '<h3>⚠️ Sécurité importante</h3>';
        echo '<p>Maintenant que la page est créée, <strong>supprimez ce fichier</strong> <code>create-connexion-page.php</code> de votre serveur pour des raisons de sécurité !</p>';
        echo '<p>Vous pouvez le supprimer via FTP ou avec cette commande:</p>';
        echo '<code>rm create-connexion-page.php</code>';
        echo '</div>';
    }
}
?>

        <hr style="margin: 30px 0; border: none; border-top: 1px solid #ddd;">

        <h3>📧 Configuration email</h3>
        <div class="info">
            <p><strong>Les demandes d'adhésion seront envoyées à :</strong></p>
            <ul>
                <li>Email administrateur WordPress: <code><?php echo get_option('admin_email'); ?></code></li>
                <li>Email CGT: <code>admfsetud@cgt.fr</code></li>
            </ul>
            <p>Le demandeur recevra également un email de confirmation.</p>
        </div>

        <h3>🎨 Charte graphique</h3>
        <div class="info">
            <p>La page respecte la charte CGT avec :</p>
            <ul>
                <li>Couleur rouge CGT: <span style="display: inline-block; width: 20px; height: 20px; background: #d00000; vertical-align: middle; border-radius: 3px;"></span> <code>#d00000</code></li>
                <li>Police Manrope (comme le reste du site)</li>
                <li>Design moderne et responsive</li>
            </ul>
        </div>
    </div>
</body>
</html>
