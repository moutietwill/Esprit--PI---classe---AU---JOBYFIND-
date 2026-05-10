<?php
require_once 'config/Database.php';
$db = Database::getInstance()->getConnection();
try {
    $stmt = $db->query("DESCRIBE utilisateur");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($columns, JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
