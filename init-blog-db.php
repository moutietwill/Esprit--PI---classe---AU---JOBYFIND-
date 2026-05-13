<?php
/**
 * Initialisation de la base de données pour le blog
 * Ce script crée les tables nécessaires pour le blog
 * À exécuter une seule fois après la première installation
 */

define('BASE_PATH', __DIR__);
require_once BASE_PATH . '/core/Autoloader.php';
Autoloader::register(BASE_PATH);

$db = Database::getInstance()->getConnection();

try {
    echo "Initialisation de la base de données blog...\n\n";

    // Lire le fichier SQL
    $sqlFile = BASE_PATH . '/database/blog_schema.sql';
    if (!file_exists($sqlFile)) {
        throw new Exception("Fichier SQL non trouvé: $sqlFile");
    }

    $sql = file_get_contents($sqlFile);
    
    // Supprimer les commentaires SQL
    $sql = preg_replace('/--.*$/m', '', $sql);
    $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
    
    // Diviser les requêtes SQL
    $queries = array_filter(array_map('trim', explode(';', $sql)));
    
    $count = 0;
    foreach ($queries as $query) {
        if (!empty($query)) {
            try {
                $db->exec($query);
                $count++;
                echo "✓ Requête $count exécutée\n";
            } catch (PDOException $e) {
                echo "✗ Erreur: " . $e->getMessage() . "\n";
            }
        }
    }

    echo "\n✓ Initialisation complète! ($count requêtes exécutées)\n";
    echo "\nTables créées:\n";
    echo "- categories\n";
    echo "- posts\n";
    echo "- comments\n";
    echo "- post_ratings\n";
    echo "- post_likes\n";

} catch (Exception $e) {
    echo "✗ Erreur d'initialisation: " . $e->getMessage() . "\n";
    exit(1);
}
