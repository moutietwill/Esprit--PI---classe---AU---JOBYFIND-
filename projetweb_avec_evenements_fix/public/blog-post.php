<?php
/**
 * Page de détail d'un post du blog
 */

require_once __DIR__ . '/../controllers/BlogController.php';

$blogController = new BlogController();

// Récupérer l'ID du post depuis l'URL
$postId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($postId <= 0) {
    header('HTTP/1.0 404 Not Found');
    echo "<h1>Post non trouvé</h1>";
    exit;
}

// Récupérer le post
try {
    $post = $blogController->RecupererPost($postId);
} catch (Exception $e) {
    header('HTTP/1.0 404 Not Found');
    echo "<h1>Post non trouvé</h1>";
    exit;
}

if (!$post) {
    header('HTTP/1.0 404 Not Found');
    echo "<h1>Post non trouvé</h1>";
    exit;
}

// Incrémenter les vues
$blogController->IncrementPostView($postId);

// Récupérer les commentaires
$comments = $blogController->GetCommentsByPost($postId);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($post['title']); ?> - Blog</title>
    <link rel="stylesheet" href="../assets/css/all.min.css">
    <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            line-height: 1.6;
        }
        
        .post-detail-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .post-header {
            margin-bottom: 30px;
        }
        
        .post-title {
            font-size: 2.5em;
            font-weight: bold;
            color: #333;
            margin-bottom: 15px;
            line-height: 1.3;
        }
        
        .post-meta {
            display: flex;
            gap: 20px;
            align-items: center;
            flex-wrap: wrap;
            color: #666;
            font-size: 0.95em;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .meta-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .post-category {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 6px 15px;
            border-radius: 20px;
            font-size: 0.9em;
        }
        
        .post-image {
            width: 100%;
            margin: 20px 0 30px 0;
            border-radius: 10px;
            overflow: hidden;
            max-height: 400px;
            object-fit: cover;
        }
        
        .post-content {
            font-size: 1.05em;
            color: #333;
            margin: 30px 0;
            line-height: 1.8;
        }
        
        .post-content p {
            margin-bottom: 15px;
        }
        
        .post-content h2 {
            font-size: 1.8em;
            margin: 30px 0 15px 0;
            color: #333;
        }
        
        .post-content h3 {
            font-size: 1.4em;
            margin: 25px 0 12px 0;
            color: #444;
        }
        
        .post-content ul,
        .post-content ol {
            margin: 15px 0 15px 20px;
        }
        
        .post-content li {
            margin: 8px 0;
        }
        
        .post-content code {
            background: #f5f5f5;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
        }
        
        .post-content pre {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
            margin: 15px 0;
        }
        
        .post-footer {
            padding-top: 20px;
            border-top: 2px solid #f0f0f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .post-views {
            color: #666;
            font-size: 0.95em;
        }
        
        .back-to-blog {
            display: inline-block;
            background: #667eea;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            transition: background 0.3s;
        }
        
        .back-to-blog:hover {
            background: #764ba2;
            text-decoration: none;
            color: white;
        }
        
        .comments-section {
            margin-top: 50px;
            padding-top: 30px;
            border-top: 3px solid #f0f0f0;
        }
        
        .comments-title {
            font-size: 1.5em;
            font-weight: bold;
            margin-bottom: 20px;
            color: #333;
        }
        
        .comment {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 15px;
            border-left: 4px solid #667eea;
        }
        
        .comment-author {
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }
        
        .comment-date {
            font-size: 0.85em;
            color: #999;
            margin-bottom: 10px;
        }
        
        .comment-content {
            color: #555;
            line-height: 1.6;
        }
        
        .no-comments {
            text-align: center;
            padding: 20px;
            color: #999;
        }
        
        @media (max-width: 768px) {
            .post-title {
                font-size: 1.8em;
            }
            
            .post-detail-container {
                margin: 20px 10px;
            }
            
            .post-meta {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    <div class="post-detail-container">
        <div class="post-header">
            <h1 class="post-title"><?php echo htmlspecialchars($post['title']); ?></h1>
            
            <div class="post-meta">
                <?php if (!empty($post['category'])): ?>
                    <span class="post-category"><?php echo htmlspecialchars($post['category']); ?></span>
                <?php endif; ?>
                
                <div class="meta-item">
                    <i class="fas fa-calendar"></i>
                    <?php 
                    $date = new DateTime($post['created_at']);
                    echo $date->format('d/m/Y H:i'); 
                    ?>
                </div>
                
                <?php if (!empty($post['instructor'])): ?>
                    <div class="meta-item">
                        <i class="fas fa-user"></i>
                        <?php echo htmlspecialchars($post['instructor']); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if (!empty($post['cover_image'])): ?>
            <img src="../uploads/<?php echo htmlspecialchars($post['cover_image']); ?>" 
                 alt="<?php echo htmlspecialchars($post['title']); ?>" 
                 class="post-image">
        <?php endif; ?>
        
        <div class="post-content">
            <?php echo $post['content']; ?>
        </div>
        
        <div class="post-footer">
            <div class="post-views">
                <i class="fas fa-eye"></i>
                <?php echo $post['views_count'] ?? 0; ?> vues
            </div>
            <a href="blog-index.php" class="back-to-blog">← Retour au blog</a>
        </div>
        
        <?php if (!empty($comments)): ?>
            <div class="comments-section">
                <h2 class="comments-title">
                    <i class="fas fa-comments"></i>
                    Commentaires (<?php echo count($comments); ?>)
                </h2>
                
                <div class="comments-list">
                    <?php foreach ($comments as $comment): ?>
                        <div class="comment">
                            <div class="comment-author">
                                <?php echo htmlspecialchars($comment['user_name']); ?>
                            </div>
                            <div class="comment-date">
                                <?php 
                                $commentDate = new DateTime($comment['created_at']);
                                echo $commentDate->format('d/m/Y H:i'); 
                                ?>
                            </div>
                            <div class="comment-content">
                                <?php echo htmlspecialchars($comment['content']); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="comments-section">
                <h2 class="comments-title">
                    <i class="fas fa-comments"></i>
                    Commentaires
                </h2>
                <div class="no-comments">
                    <p>Aucun commentaire pour le moment.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
