<?php
require_once(__DIR__ . '/../../config/session.php');
startAppSession();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Accès refusé']);
    exit();
}
require_once(__DIR__ . '/../../Controller/ProfileController.php');

if (isset($_GET['id'])) {
    $profileController = new ProfileController();
    $data = $profileController->getProfileWithUser($_GET['id']);
    
    if ($data) {
        echo json_encode($data);
    } else {
        echo json_encode(['error' => 'Profil non trouvé']);
    }
} else {
    echo json_encode(['error' => 'ID manquant']);
}
