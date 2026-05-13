<?php
require_once(__DIR__ . '/../../config/session.php');
startAppSession();
require_once(__DIR__ . '/../../Controller/UtilisateurController.php');
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') exit();

$userId = $_POST['id'] ?? null;
if ($userId) {
    $db = config::getConnexion();
    
    // Get current status
    $stmt = $db->prepare("SELECT status FROM utilisateurs WHERE id = :id");
    $stmt->execute(['id' => $userId]);
    $current = $stmt->fetchColumn();
    
    // Cycle: Actif -> En attente -> Suspendu -> Actif
    $next = 'Actif';
    if ($current === 'Actif') $next = 'En attente';
    elseif ($current === 'En attente') $next = 'Suspendu';
    
    $stmt = $db->prepare("UPDATE utilisateurs SET status = :status WHERE id = :id");
    $stmt->execute(['status' => $next, 'id' => $userId]);
    
    echo json_encode(['success' => true, 'next' => $next]);
}
