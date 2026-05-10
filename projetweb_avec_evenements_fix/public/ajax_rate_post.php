<?php
session_start();
require_once __DIR__ . '/../controllers/BlogController.php';

header('Content-Type: application/json');

$postId = isset($_POST['post_id']) ? (int) $_POST['post_id'] : 0;
$rating = isset($_POST['rating']) ? (int) $_POST['rating'] : 0;
$userId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;

if ($postId <= 0 || $rating < 1 || $rating > 5) {
    echo json_encode(['success' => false, 'message' => 'Donnees invalides']);
    exit;
}

$controller = new BlogController();
$result = $controller->AddRating($postId, $userId, $rating);

echo json_encode([
    'success' => true,
    'avg' => $result['avg'],
    'count' => $result['count'],
    'user_rating' => $rating,
    'message' => 'Merci pour votre note !',
]);
?>
