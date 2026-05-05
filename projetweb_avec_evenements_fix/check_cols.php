<?php
require_once __DIR__ . '/config/Database.php';
try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->query("SELECT * FROM evenement LIMIT 1");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        echo "Columns: " . implode(", ", array_keys($row)) . "\n";
    } else {
        echo "Table is empty, trying DESCRIBE again...\n";
        $stmt = $db->query("DESCRIBE evenement");
        while($col = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo $col['Field'] . " (" . $col['Type'] . ")\n";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
