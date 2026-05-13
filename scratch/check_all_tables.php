<?php
include 'config.php';
try {
    $db = config::getConnexion();
    
    // Check avis table
    echo "--- Table: avis ---\n";
    $stmt = $db->query("DESCRIBE avis");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach($columns as $col) { echo $col['Field'] . " (" . $col['Type'] . ")\n"; }
    
    // Check formation table
    echo "\n--- Table: formation ---\n";
    $stmt = $db->query("DESCRIBE formation");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach($columns as $col) { echo $col['Field'] . " (" . $col['Type'] . ")\n"; }

    // Check inscription table
    echo "\n--- Table: inscription ---\n";
    $stmt = $db->query("DESCRIBE inscription");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach($columns as $col) { echo $col['Field'] . " (" . $col['Type'] . ")\n"; }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
