<?php
/**
 * Blog Index - Page d'accueil du blog
 */

require_once __DIR__ . '/../controllers/BlogController.php';
require_once __DIR__ . '/../controllers/CategoryController.php';

$blog = new BlogController();
$categoryController = new CategoryController();

// Récupérer les paramètres
$page = $_GET['page'] ?? 1;
$page = max(1, (int)$page);

// Récupérer les posts publiés
$posts = $blog->Pagination($page, 9);
$totalPosts = $blog->NombreDesPosts();
$totalPages = ceil($totalPosts / 9);

// Récupérer les catégories
try {
    $categories = $categoryController->getCategories();
} catch (Exception $e) {
    $categories = [];
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog - Gestion des Événements</title>
    <link rel="stylesheet" href="assets/css/all.min.css">
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <style>
        .blog-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .blog-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 60px 20px;
            border-radius: 10px;
            margin-bottom: 40px;
            text-align: center;
        }
        
        .blog-header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
        }
        
        .blog-header p {
            font-size: 1.1em;
            opacity: 0.9;
        }
        
        .posts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }
        
        .post-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
            cursor: pointer;
        }
        
        .post-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
        }
        
        .post-image {
            width: 100%;
            height: 200px;
            background: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3em;
            color: #ccc;
            overflow: hidden;
        }
        
        .post-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .post-body {
            padding: 20px;
        }
        
        .post-category {
            display: inline-block;
            background: #667eea;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85em;
            margin-bottom: 10px;
        }
        
        .post-title {
            font-size: 1.3em;
            font-weight: bold;
            margin-bottom: 10px;
            color: #333;
        }
        
        .post-excerpt {
            color: #666;
            font-size: 0.95em;
            margin-bottom: 15px;
            line-height: 1.5;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .post-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.85em;
            color: #999;
            padding-top: 10px;
            border-top: 1px solid #eee;
        }
        
        .post-views {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .read-more {
            display: inline-block;
            color: #667eea;
            font-weight: bold;
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .read-more:hover {
            color: #764ba2;
            text-decoration: underline;
        }
        
        .pagination-container {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 40px;
        }
        
        .pagination-container a,
        .pagination-container span {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            text-decoration: none;
            color: #667eea;
            transition: all 0.3s;
        }
        
        .pagination-container a:hover {
            background: #667eea;
            color: white;
        }
        
        .pagination-container .active {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }
        
        .no-posts {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }
        
        .no-posts i {
            font-size: 3em;
            margin-bottom: 20px;
            color: #ddd;
        }
    </style>
</head>
<body>
    <div class="blog-container">
        <div class="blog-header">
            <h1>📚 Notre Blog</h1>
            <p>Découvrez les derniers articles et tutoriels</p>
        </div>
        
        <?php if (!empty($posts)): ?>
            <div class="posts-grid">
                <?php foreach ($posts as $post): ?>
                    <div class="post-card">
                        <div class="post-image">
                            <?php if (!empty($post['cover_image'])): ?>
                                <img src="uploads/<?php echo htmlspecialchars($post['cover_image']); ?>" 
                                     alt="<?php echo htmlspecialchars($post['title']); ?>">
                            <?php else: ?>
                                <i class="fas fa-newspaper"></i>
                            <?php endif; ?>
                        </div>
                        <div class="post-body">
                            <?php if (!empty($post['category'])): ?>
                                <span class="post-category"><?php echo htmlspecialchars($post['category']); ?></span>
                            <?php endif; ?>
                            <h3 class="post-title"><?php echo htmlspecialchars($post['title']); ?></h3>
                            <p class="post-excerpt"><?php echo htmlspecialchars(substr(strip_tags($post['content']), 0, 100)); ?></p>
                            <div class="post-meta">
                                <span class="post-views">
                                    <i class="fas fa-eye"></i>
                                    <?php echo $post['views_count'] ?? 0; ?> vues
                                </span>
                                <a href="blog/<?php echo $post['id']; ?>" class="read-more">Lire plus →</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <?php if ($totalPages > 1): ?>
                <div class="pagination-container">
                    <?php if ($page > 1): ?>
                        <a href="?page=1">« Première</a>
                        <a href="?page=<?php echo $page - 1; ?>">‹ Précédente</a>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                        <?php if ($i === $page): ?>
                            <span class="active"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?php echo $page + 1; ?>">Suivante ›</a>
                        <a href="?page=<?php echo $totalPages; ?>">Dernière »</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="no-posts">
                <i class="fas fa-inbox"></i>
                <h3>Aucun article pour le moment</h3>
                <p>Les articles seront bientôt disponibles. Revenez plus tard!</p>
            </div>
        <?php endif; ?>
    </div>
    
    <script src="assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
