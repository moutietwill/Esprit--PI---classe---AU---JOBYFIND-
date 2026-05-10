<?php
session_start();
require_once(__DIR__ . '/../../controllers/PasswordResetController.php');
require_once(__DIR__ . '/../../controllers/UtilisateurController.php');

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non authentifié']);
    exit();
}

$userId = $_SESSION['user_id'];
$userController = new UtilisateurController();
$user = $userController->getUserById($userId);

if ($user) {
    $email = $user['email'];
    $resetController = new PasswordResetController();
    $sent = $resetController->sendResetCode($email, $user['first_name']);
    
    if ($sent) {
        $_SESSION['reset_email'] = $email;
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erreur d\'envoi. Vérifiez sendmail.ini']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Utilisateur non trouvé']);
}
