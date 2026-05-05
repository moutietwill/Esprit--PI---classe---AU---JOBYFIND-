<?php
require_once __DIR__ . '/config/Database.php';
$db = Database::getInstance()->getConnection();
$stmt = $db->query("SELECT idEvenement, titre, image FROM evenement LIMIT 5");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
header('Content-Type: application/json');
echo json_encode($rows, JSON_PRETTY_PRINT);
