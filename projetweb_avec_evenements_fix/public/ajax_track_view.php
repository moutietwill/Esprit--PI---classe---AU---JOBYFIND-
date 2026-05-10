<?php
require_once __DIR__ . '/../controllers/BlogController.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Requête invalide.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$postId = isset($data['post_id']) ? (int) $data['post_id'] : (isset($_POST['post_id']) ? (int) $_POST['post_id'] : 0);

if ($postId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Post non valide.']);
    exit;
}

$controller = new BlogController();
$post = $controller->RecupererPost($postId);

if (!$post) {
    echo json_encode(['success' => false, 'message' => 'Post introuvable.']);
    exit;
}

$viewsCount = $controller->IncrementPostView($postId);

echo json_encode([
    'success' => true,
    'views_count' => $viewsCount
]);
?>
