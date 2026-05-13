<?php
include 'config.php';
try {
    $db = config::getConnexion();
    
    // Add missing columns if they don't exist
    $db->exec("ALTER TABLE inscription ADD COLUMN IF NOT EXISTS telephone VARCHAR(20) AFTER email");
    $db->exec("ALTER TABLE inscription ADD COLUMN IF NOT EXISTS methode_paiement VARCHAR(50) AFTER telephone");
    $db->exec("ALTER TABLE inscription ADD COLUMN IF NOT EXISTS id_formation INT AFTER methode_paiement");
    
    // Check if 'id' exists, if not rename 'idInscription' to 'id' or add it
    $stmt = $db->query("DESCRIBE inscription");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('id', $columns)) {
        if (in_array('idInscription', $columns)) {
            $db->exec("ALTER TABLE inscription CHANGE idInscription id INT NOT NULL AUTO_INCREMENT");
        } else {
            $db->exec("ALTER TABLE inscription ADD COLUMN id INT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST");
        }
    }
    
    echo "Migration completed successfully.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
