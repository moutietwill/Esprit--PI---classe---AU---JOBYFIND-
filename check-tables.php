<?php
define('BASE_PATH', __DIR__);
require_once BASE_PATH . '/core/Autoloader.php';
Autoloader::register(BASE_PATH);

try {
    $db = Database::getInstance()->getConnection();
    $tables = $db->query("SHOW TABLES")->fetchAll();
    
    echo "✓ Connexion à la base de données réussie!\n\n";
    echo "Tables dans la base 'gestion_evenements':\n";
    echo "==========================================\n";
    
    foreach($tables as $t) {
        $tableName = array_values($t)[0];
        echo "✓ $tableName\n";
    }
    
    echo "\n✓ Total: " . count($tables) . " tables\n";
    
} catch (Exception $e) {
    echo "✗ Erreur: " . $e->getMessage() . "\n";
}
