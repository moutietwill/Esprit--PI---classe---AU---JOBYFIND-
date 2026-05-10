<?php
/**
 * Blog Initialization Script
 * Crée les tables du blog dans la base de données gestion_evenements
 */

require_once __DIR__ . '/config/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    // Lire et exécuter le schéma SQL
    $sql = file_get_contents(__DIR__ . '/database/blog-schema.sql');
    
    // Diviser par ; pour exécuter les requêtes une par une
    $queries = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($queries as $query) {
        if (!empty($query)) {
            $db->exec($query);
            echo "✓ Requête exécutée\n";
        }
    }
    
    echo "\n✅ Tables du blog créées avec succès!\n";
    echo "Les tables suivantes ont été créées:\n";
    echo "  - categories\n";
    echo "  - posts\n";
    echo "  - comments\n";
    echo "  - likes\n";
    echo "  - stories\n";
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    die(1);
}
?>
