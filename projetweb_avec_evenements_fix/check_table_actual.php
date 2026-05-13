<?php
require_once __DIR__ . '/config/Database.php';
try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->query("DESCRIBE evenement");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Table: evenement\n";
    print_r($columns);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
