<?php
require_once __DIR__ . '/../connexion.php';
require_once __DIR__ . '/../controller/BlogController.php';

header('Content-Type: application/json');

if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode(['error' => 'ID du post requis']);
    exit;
}

$postId = intval($_GET['id']);
$blogController = new BlogController();

try {
    // Récupérer le post
    $post = $blogController->RecupererPost($postId);
    
    if (!$post) {
        echo json_encode(['error' => 'Post non trouvé']);
        exit;
    }
    
    // Récupérer le nombre de likes
    $likesCount = $blogController->GetLikesCount($postId);
    
    // Récupérer les commentaires
    $comments = $blogController->GetCommentsByPost($postId);
    
    // Préparer la réponse
    $response = [
        'id' => $post['id'],
        'title' => $post['title'],
        'likes_count' => $likesCount,
        'comments' => $comments ? $comments : []
    ];
    
    echo json_encode($response);
} catch (Exception $e) {
    echo json_encode(['error' => 'Erreur: ' . $e->getMessage()]);
}
?>
