<?php
require_once __DIR__ . '/controller/BlogController.php';
require_once __DIR__ . '/mailer.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $postId = isset($data['post_id']) ? (int)$data['post_id'] : (isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0);
    $content = isset($data['content']) ? trim($data['content']) : (isset($_POST['content']) ? trim($_POST['content']) : '');
    $userName = isset($data['user_name']) ? trim($data['user_name']) : (isset($_POST['user_name']) ? trim($_POST['user_name']) : 'Anonyme');

    // Contrôle de saisie backend
    if (empty($content)) {
        echo json_encode(['success' => false, 'message' => 'Le commentaire ne peut pas être vide.']);
        exit;
    }

    if (strlen($content) > 500) {
        echo json_encode(['success' => false, 'message' => 'Le commentaire est trop long.']);
        exit;
    }

    // Contrôle des bad words
    $badWords = ['merde', 'con', 'putain', 'salope', 'idiot', 'connard', 'bâtard', 'stupide'];
    $foundWords = [];
    foreach ($badWords as $word) {
        if (preg_match('/\b' . preg_quote($word, '/') . '\b/i', $content)) {
            $foundWords[] = $word;
        }
    }

    if (!empty($foundWords)) {
        echo json_encode([
            'success' => false, 
            'message' => 'Le commentaire contient des mots inappropriés (' . implode(', ', $foundWords) . '). Veuillez les supprimer.'
        ]);
        exit;
    }

    if ($postId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Post non valide.']);
        exit;
    }

    $controller = new BlogController();
    $result = $controller->AddComment($postId, $content, $userName);

    if ($result) {
        // Récupérer le titre du post pour la notification
        $post = $controller->RecupererPost($postId);
        $postTitle = $post ? $post['title'] : 'Post inconnu';

        // Envoyer la notification par email
        require_once __DIR__ . '/mailer.php';
        Mailer::notifyNewComment($userName, $content, $postTitle);

        echo json_encode([
            'success' => true, 
            'comment' => [
                'text' => htmlspecialchars($content),
                'timestamp' => date('d/m/Y H:i:s'),
                'author' => htmlspecialchars($userName)
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'enregistrement dans la base de données.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Requête invalide.']);
}
