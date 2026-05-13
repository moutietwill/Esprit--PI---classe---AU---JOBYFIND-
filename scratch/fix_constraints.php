<?php
include 'config.php';
try {
    $db = config::getConnexion();
    
    // 1. Drop the foreign key constraint
    $db->exec("ALTER TABLE inscription DROP FOREIGN KEY inscription_ibfk_1");
    
    // 2. Make idEvenement nullable
    $db->exec("ALTER TABLE inscription MODIFY idEvenement INT NULL");
    
    // 3. Optional: drop the unique key if it causes issues
    // $db->exec("ALTER TABLE inscription DROP INDEX unique_inscription");
    
    echo "Constraint fixed successfully.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
