<?php
session_start();
require_once __DIR__ . '/../controllers/BlogController.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Requete invalide.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$postId = isset($data['post_id']) ? (int) $data['post_id'] : 0;
$userId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;

if ($postId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Post non valide.']);
    exit;
}

$controller = new BlogController();
$liked = $controller->ToggleLike($postId, $userId);
$count = $controller->GetLikesCount($postId);

echo json_encode([
    'success' => true,
    'liked' => $liked,
    'count' => $count,
]);
?>
