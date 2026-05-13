<?php
header('Content-Type: application/json');
require_once __DIR__ . '/controller/BlogController.php';

// Valider les entrées
$postId = isset($_POST['post_id']) ? (int) $_POST['post_id'] : 0;
$rating = isset($_POST['rating']) ? (int) $_POST['rating'] : 0;
session_start();
$userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;

// For testing purposes: if no session, we can use IP to identify guests, 
// but if you want separate accounts to work, they MUST have a session id.
if ($userId === 0) {
    // Optionally allow guests by using a unique simulated ID (like 0) and IP
    // For now, let's just use the session ID if available or stick to the session user_id
}

if ($postId <= 0 || $rating < 1 || $rating > 5) {
    exit;
}

$controller = new BlogController();
try {
    $result = $controller->AddRating($postId, $userId, $rating);
    if ($result === false) {
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la notation']);
    } else {
        echo json_encode([
            'success'     => true,
            'avg'         => $result['avg'],
            'count'       => $result['count'],
            'user_rating' => $rating,
            'message'     => 'Merci pour votre note !'
        ]);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>