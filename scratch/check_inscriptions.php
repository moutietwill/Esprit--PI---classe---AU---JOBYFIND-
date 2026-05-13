<?php
include 'config.php';
try {
    $db = config::getConnexion();
    $stmt = $db->query("DESCRIBE inscription");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach($columns as $col) {
        echo $col['Field'] . " (" . $col['Type'] . ")\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
