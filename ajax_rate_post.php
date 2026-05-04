<?php
header('Content-Type: application/json');
require_once __DIR__ . '/controller/BlogController.php';

// Valider les entrées
$postId = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;
$rating = isset($_POST['rating'])  ? (int)$_POST['rating']  : 0;
$userId = 1; // Utilisateur simulé (à remplacer par $_SESSION['user_id'] si auth)

if ($postId <= 0 || $rating < 1 || $rating > 5) {
    echo json_encode(['success' => false, 'message' => 'Données invalides']);
    exit;
}

$controller = new BlogController();
$result = $controller->AddRating($postId, $userId, $rating);

if ($result === false) {
    echo json_encode(['success' => false, 'message' => 'Erreur lors de la notation']);
} else {
    echo json_encode([
        'success'      => true,
        'avg'          => $result['avg'],
        'count'        => $result['count'],
        'user_rating'  => $rating,
        'message'      => 'Merci pour votre note !'
    ]);
}
?>
