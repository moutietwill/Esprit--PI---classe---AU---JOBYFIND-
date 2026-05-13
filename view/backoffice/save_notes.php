<?php
require_once(__DIR__ . '/../../config/session.php');
startAppSession();
require_once(__DIR__ . '/../../Controller/UtilisateurController.php');
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') exit();

$userId = $_POST['id'] ?? null;
$notes = $_POST['notes'] ?? '';

if ($userId) {
    $db = config::getConnexion();
    $stmt = $db->prepare("UPDATE utilisateurs SET violation_notes = :notes WHERE id = :id");
    $stmt->execute(['notes' => $notes, 'id' => $userId]);
    echo json_encode(['success' => true]);
}
