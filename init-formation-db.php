<?php
require_once __DIR__ . '/config/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    $sql = "CREATE TABLE IF NOT EXISTS `formation` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `titre` varchar(255) NOT NULL,
        `prix` float NOT NULL,
        `date` date NOT NULL,
        `duree` varchar(100) NOT NULL,
        `description` text NOT NULL,
        `categorie` varchar(100) NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    $db->exec($sql);
    echo "Table 'formation' created or already exists.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
