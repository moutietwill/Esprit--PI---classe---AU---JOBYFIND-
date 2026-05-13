<?php
/**
 * UNIFY INSCRIPTION TABLE
 * This script ensures the 'inscription' table supports both Events and Formations.
 */

require_once __DIR__ . '/config/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    echo "Starting migration...\n";

    // 1. Get current columns
    $stmt = $db->query("DESCRIBE inscription");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // 2. Handle Primary Key Rename if necessary (Events uses idInscription, Formation uses id)
    if (in_array('idInscription', $columns) && !in_array('id', $columns)) {
        echo "Renaming idInscription to id...\n";
        $db->exec("ALTER TABLE inscription CHANGE idInscription id INT NOT NULL AUTO_INCREMENT");
    }

    // 3. Add Formation-specific columns if missing
    $toAdd = [
        'telephone' => "VARCHAR(20) NULL AFTER email",
        'methode_paiement' => "VARCHAR(50) NULL AFTER telephone",
        'id_formation' => "INT NULL AFTER methode_paiement"
    ];

    foreach ($toAdd as $col => $definition) {
        if (!in_array($col, $columns)) {
            echo "Adding column $col...\n";
            $db->exec("ALTER TABLE inscription ADD COLUMN $col $definition");
        }
    }

    // 4. Make Event-specific columns NULLABLE if they aren't
    if (in_array('idEvenement', $columns)) {
        echo "Making idEvenement nullable...\n";
        // Drop foreign key first if it exists (to avoid issues during modify)
        try {
            $db->exec("ALTER TABLE inscription DROP FOREIGN KEY inscription_ibfk_1");
            echo "Dropped foreign key inscription_ibfk_1\n";
        } catch (Exception $e) {
            // Might not exist or have different name
        }
        
        $db->exec("ALTER TABLE inscription MODIFY idEvenement INT NULL");
    }

    // 5. Ensure id_formation has a foreign key if table 'formation' exists
    $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    if (in_array('formation', $tables)) {
        try {
            $db->exec("ALTER TABLE inscription ADD CONSTRAINT fk_inscription_formation FOREIGN KEY (id_formation) REFERENCES formation(id) ON DELETE CASCADE");
            echo "Added foreign key fk_inscription_formation\n";
        } catch (Exception $e) {
            echo "Foreign key fk_inscription_formation might already exist or data mismatch.\n";
        }
    }

    echo "Migration completed successfully!\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>
