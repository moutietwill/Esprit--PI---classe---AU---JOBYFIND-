<?php
session_start();
require_once __DIR__ . '/../controllers/BlogController.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Requete invalide.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$postId = isset($data['post_id']) ? (int) $data['post_id'] : (int) ($_POST['post_id'] ?? 0);
$content = trim((string) ($data['content'] ?? ($_POST['content'] ?? '')));
$userName = trim((string) ($data['user_name'] ?? ($_POST['user_name'] ?? '')));

try {
    $controller = new BlogController();
    $controller->AddComment($postId, $content, $userName);

    $comments = $controller->GetCommentsByPost($postId);
    $latest = end($comments);

    echo json_encode([
        'success' => true,
        'comment' => [
            'text' => htmlspecialchars($latest['contenu'] ?? $content, ENT_QUOTES, 'UTF-8'),
            'timestamp' => isset($latest['date_creation']) ? date('d/m/Y H:i:s', strtotime($latest['date_creation'])) : date('d/m/Y H:i:s'),
            'author' => htmlspecialchars((string) (($latest['auteur_nom'] ?? '') !== '' ? $latest['auteur_nom'] : (($latest['auteur_username'] ?? '') !== '' ? $latest['auteur_username'] : ($latest['nom'] ?? 'Anonyme'))), ENT_QUOTES, 'UTF-8'),
        ],
        'count' => count($comments),
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
