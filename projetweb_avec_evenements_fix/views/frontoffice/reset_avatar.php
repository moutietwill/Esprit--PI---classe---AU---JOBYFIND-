<?php
session_start();
require_once(__DIR__ . '/../../controllers/ProfileController.php');
require_once(__DIR__ . '/../../models/Profile.php');

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non authentifié']);
    exit();
}

$userId = $_SESSION['user_id'];
$profileController = new ProfileController();
$profileData = $profileController->getProfileByUserId($userId);

if ($profileData) {
    $profile = new Profile($profileData);
    
    // Delete old photo if it exists and is not the default
    $oldPhoto = $profile->getPhoto_profil();
    if ($oldPhoto && file_exists(__DIR__ . '/' . $oldPhoto) && strpos($oldPhoto, 'default') === false) {
        unlink(__DIR__ . '/' . $oldPhoto);
    }

    $profile->setPhoto_profil(''); // Set to empty to use initials
    $profileController->updateProfile($profile, $userId);
    
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Profil non trouvé']);
}
