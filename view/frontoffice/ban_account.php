<?php
require_once(__DIR__ . '/../../config/session.php');
startAppSession();
require_once(__DIR__ . '/../../Controller/UtilisateurController.php');

if (!isset($_SESSION['user_id'])) {
    exit('Non autorisé');
}

$userId = $_SESSION['user_id'];
$reason = $_POST['reason'] ?? 'Violation des conditions d\'utilisation (Image inappropriée détectée par l\'IA)';

$db = config::getConnexion();

try {
    // 1. Get current count
    $stmt = $db->prepare("SELECT violations_count FROM utilisateurs WHERE id = :id");
    $stmt->execute(['id' => $userId]);
    $currentCount = $stmt->fetchColumn();
    $newCount = $currentCount + 1;

    // 2. Decide: Suspend or Delete?
    if ($newCount > 3) {
        // --- PEINE DE MORT : DELETE ACCOUNT ---
        // 1. Delete associated data first
        $db->prepare("DELETE FROM password_resets WHERE email = (SELECT email FROM utilisateurs WHERE id = :id)")->execute(['id' => $userId]);
        $db->prepare("DELETE FROM profiles WHERE Id_utilisateur = :id")->execute(['id' => $userId]);
        
        // 2. Delete user
        $stmtUser = $db->prepare("DELETE FROM utilisateurs WHERE id = :id");
        $stmtUser->execute(['id' => $userId]);
        
        $newStatus = 'Supprimé (Récidive)';
        error_log("USER DELETED: ID $userId. Violated terms 4 times.");
    } else {
        // --- SUSPENSION NORMALE ---
        $newStatus = 'Suspendu';
        $sql = "UPDATE utilisateurs SET status = :status, violations_count = :count WHERE id = :id";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            'status' => $newStatus,
            'count'  => $newCount,
            'id'     => $userId
        ]);
        error_log("USER VIOLATION: ID $userId. New Count: $newCount. Status: $newStatus");
    }
    
    // Destroy session
    session_unset();
    session_destroy();
    
    echo json_encode(['success' => true, 'new_status' => $newStatus, 'count' => $newCount]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
