<?php
/**
 * Script pour flusher les permalinks manuellement
 *
 * Si vous avez des problèmes de page 404 avec les enquêtes,
 * allez dans WordPress Admin → Réglages → Permaliens
 * et cliquez simplement sur "Enregistrer les modifications"
 *
 * OU visitez cette page : votresite.com/wp-admin/options-permalink.php
 * et cliquez sur "Enregistrer"
 *
 * OU ajoutez ce code temporairement dans functions.php:
 *
 * add_action( 'init', function() {
 *     flush_rewrite_rules();
 * }, 999 );
 *
 * Puis rechargez n'importe quelle page, et supprimez le code.
 */

// Ce fichier sert de documentation uniquement
