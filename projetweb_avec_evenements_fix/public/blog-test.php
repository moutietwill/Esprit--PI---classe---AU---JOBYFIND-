<?php
/**
 * Fichier de test - Vérifier que l'intégration du blog fonctionne
 * Accédez à: http://localhost/projetweb_avec_evenements_fix/public/blog-test.php
 */

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../controllers/BlogController.php';
require_once __DIR__ . '/../controllers/CategoryController.php';

$errors = [];
$success = [];

try {
    // Test 1: Connexion à la base de données
    $db = Database::getInstance()->getConnection();
    if ($db) {
        $success[] = "✓ Connexion à la base de données réussie";
    }
} catch (Exception $e) {
    $errors[] = "✗ Erreur de connexion DB: " . $e->getMessage();
}

try {
    // Test 2: Vérifier les tables
    $db = Database::getInstance()->getConnection();
    
    $tables = ['categories', 'posts', 'comments', 'likes', 'stories'];
    foreach ($tables as $table) {
        $result = $db->query("SHOW TABLES LIKE '$table'");
        if ($result->rowCount() > 0) {
            $success[] = "✓ Table '$table' existe";
        } else {
            $errors[] = "✗ Table '$table' n'existe pas";
        }
    }
} catch (Exception $e) {
    $errors[] = "✗ Erreur lors de la vérification des tables: " . $e->getMessage();
}

try {
    // Test 3: Controllers
    $blog = new BlogController();
    $success[] = "✓ BlogController chargé avec succès";
    
    $category = new CategoryController();
    $success[] = "✓ CategoryController chargé avec succès";
} catch (Exception $e) {
    $errors[] = "✗ Erreur lors du chargement des controllers: " . $e->getMessage();
}

try {
    // Test 4: Données
    $blog = new BlogController();
    $posts = $blog->AfficherPosts();
    $success[] = "✓ Récupération des posts: " . count($posts) . " posts trouvés";
    
    $category = new CategoryController();
    $categories = $category->getCategories();
    $success[] = "✓ Récupération des catégories: " . count($categories) . " catégories trouvées";
} catch (Exception $e) {
    $errors[] = "✗ Erreur lors de la récupération des données: " . $e->getMessage();
}

// Vérifier les répertoires
if (is_dir(__DIR__ . '/uploads')) {
    $success[] = "✓ Répertoire uploads existe";
} else {
    $errors[] = "✗ Répertoire uploads manquant (créé automatiquement)";
    @mkdir(__DIR__ . '/uploads', 0777, true);
}

if (is_dir(__DIR__ . '/uploads/stories')) {
    $success[] = "✓ Répertoire uploads/stories existe";
} else {
    $errors[] = "⚠ Répertoire uploads/stories manquant (créé automatiquement)";
    @mkdir(__DIR__ . '/uploads/stories', 0777, true);
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test d'intégration du Blog</title>
    <link rel="stylesheet" href="assets/css/all.min.css">
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 20px;
            font-family: 'Arial', sans-serif;
        }
        
        .test-container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            padding: 40px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        }
        
        .test-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .test-header h1 {
            color: #333;
            font-size: 1.8em;
            margin-bottom: 10px;
        }
        
        .test-header p {
            color: #666;
        }
        
        .test-results {
            margin: 30px 0;
        }
        
        .test-item {
            padding: 12px 15px;
            margin: 10px 0;
            border-radius: 5px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.95em;
        }
        
        .test-success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }
        
        .test-error {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #f5222d;
        }
        
        .test-warning {
            background: #fff3cd;
            color: #856404;
            border-left: 4px solid #ffc107;
        }
        
        .test-item i {
            font-size: 1.2em;
            min-width: 20px;
        }
        
        .test-links {
            display: flex;
            gap: 10px;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 30px;
        }
        
        .test-link {
            display: inline-block;
            padding: 10px 20px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s;
        }
        
        .test-link:hover {
            background: #764ba2;
            text-decoration: none;
            color: white;
        }
        
        .summary {
            margin-top: 30px;
            padding: 15px;
            background: #f0f0f0;
            border-radius: 5px;
            text-align: center;
        }
        
        .summary p {
            margin: 5px 0;
            font-weight: bold;
        }
        
        .status-ok {
            color: #28a745;
        }
        
        .status-error {
            color: #f5222d;
        }
    </style>
</head>
<body>
    <div class="test-container">
        <div class="test-header">
            <h1>🧪 Test d'Intégration du Blog</h1>
            <p>Vérification de la configuration du système de blog</p>
        </div>
        
        <div class="test-results">
            <?php foreach ($success as $msg): ?>
                <div class="test-item test-success">
                    <i class="fas fa-check-circle"></i>
                    <span><?php echo $msg; ?></span>
                </div>
            <?php endforeach; ?>
            
            <?php foreach ($errors as $msg): ?>
                <div class="test-item test-error">
                    <i class="fas fa-times-circle"></i>
                    <span><?php echo $msg; ?></span>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="summary">
            <?php if (empty($errors)): ?>
                <p class="status-ok">✓ Tout fonctionne parfaitement!</p>
            <?php else: ?>
                <p class="status-error">✗ Des problèmes ont été détectés</p>
            <?php endif; ?>
            <p><?php echo count($success); ?> tests réussis, <?php echo count($errors); ?> erreurs</p>
        </div>
        
        <div class="test-links">
            <a href="blog-index.php" class="test-link">
                <i class="fas fa-newspaper"></i> Accéder au Blog
            </a>
            <a href="index.php" class="test-link">
                <i class="fas fa-home"></i> Retour à l'accueil
            </a>
        </div>
    </div>
</body>
</html>
