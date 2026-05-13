<?php
require_once __DIR__ . '/controller/StoryController.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Requete invalide.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$storyId = isset($data['story_id']) ? (int) $data['story_id'] : (isset($_POST['story_id']) ? (int) $_POST['story_id'] : 0);

if ($storyId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Story non valide.']);
    exit;
}

$controller = new StoryController();
$viewsCount = $controller->IncrementStoryView($storyId);

echo json_encode([
    'success' => true,
    'views_count' => $viewsCount
]);
?>
