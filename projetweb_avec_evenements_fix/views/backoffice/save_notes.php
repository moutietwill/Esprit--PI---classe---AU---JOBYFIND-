<?php
session_start();
require_once(__DIR__ . '/../../controllers/UtilisateurController.php');
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') exit();

$userId = $_POST['id'] ?? null;
$notes = $_POST['notes'] ?? '';

if ($userId) {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("UPDATE utilisateurs SET violation_notes = :notes WHERE id = :id");
    $stmt->execute(['notes' => $notes, 'id' => $userId]);
    echo json_encode(['success' => true]);
}
