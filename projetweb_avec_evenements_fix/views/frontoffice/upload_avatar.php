<?php
session_start();
require_once(__DIR__ . '/../../controllers/UtilisateurController.php');
require_once(__DIR__ . '/../../controllers/ProfileController.php');
require_once(__DIR__ . '/../../models/Profile.php');

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non authentifié']);
    exit();
}

$userId = $_SESSION['user_id'];

// --- SECURITY CHECK: Status ---
$userController = new UtilisateurController();
$user = $userController->getUserById($userId);
if (!$user || $user['status'] === 'Suspendu') {
    echo json_encode(['success' => false, 'message' => 'Accès refusé']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['avatar'])) {
    $file = $_FILES['avatar'];
    
    // Strict extension validation
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
    if (!in_array($extension, $allowedExtensions)) {
        echo json_encode(['success' => false, 'message' => 'Extension non autorisée']);
        exit();
    }

    // MIME type validation
    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, $allowedTypes)) {
        echo json_encode(['success' => false, 'message' => 'Contenu du fichier invalide']);
        exit();
    }

    if ($file['size'] > 2 * 1024 * 1024) { // 2MB limit
        echo json_encode(['success' => false, 'message' => 'Fichier trop lourd (max 2Mo)']);
        exit();
    }

    $uploadDir = __DIR__ . '/assets/images/profiles/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $filename = 'avatar_' . $userId . '_' . time() . '.' . $extension;
    $targetPath = $uploadDir . $filename;
    $publicPath = 'assets/images/profiles/' . $filename;

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        $profileController = new ProfileController();
        $profileData = $profileController->getProfileByUserId($userId);
        
        if ($profileData) {
            $profile = new Profile($profileData);
            
            // Delete old photo if it exists and is not the default
            $oldPhoto = $profile->getPhoto_profil();
            if ($oldPhoto && file_exists(__DIR__ . '/' . $oldPhoto) && strpos($oldPhoto, 'default') === false) {
                unlink(__DIR__ . '/' . $oldPhoto);
            }

            $profile->setPhoto_profil($publicPath);
            $profileController->updateProfile($profile, $userId);
            
            echo json_encode(['success' => true, 'path' => $publicPath]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Profil non trouvé']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Erreur lors du déplacement du fichier']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Aucun fichier reçu']);
}
