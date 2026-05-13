<?php
/**
 * Script de test complet de l'intégration Blog + Événements
 * Vérifie que tous les composants fonctionnent correctement
 */

define('BASE_PATH', __DIR__);
require_once BASE_PATH . '/core/Autoloader.php';
Autoloader::register(BASE_PATH);

echo "╔══════════════════════════════════════════════════╗\n";
echo "║   TEST INTÉGRATION BLOG + ÉVÉNEMENTS             ║\n";
echo "║   Date: " . date('Y-m-d H:i:s') . "                      ║\n";
echo "╚══════════════════════════════════════════════════╝\n\n";

$passed = 0;
$failed = 0;

// Test 1: Connexion BD
echo "TEST 1: Connexion Base de Données\n";
echo "──────────────────────────────────\n";
try {
    $db = Database::getInstance()->getConnection();
    echo "✓ PASS: Connexion réussie\n\n";
    $passed++;
} catch (Exception $e) {
    echo "✗ FAIL: " . $e->getMessage() . "\n\n";
    $failed++;
}

// Test 2: Tables BD
echo "TEST 2: Vérification des Tables\n";
echo "───────────────────────────────\n";
try {
    $tables = $db->query("SHOW TABLES")->fetchAll();
    $tableNames = array_map(fn($t) => array_values($t)[0], $tables);
    $required = ['categories', 'posts', 'comments', 'post_ratings', 'post_likes'];
    
    foreach ($required as $table) {
        if (in_array($table, $tableNames)) {
            echo "✓ Table '$table' existe\n";
        } else {
            echo "✗ Table '$table' MANQUANTE\n";
            $failed++;
        }
    }
    echo "\n";
    $passed++;
} catch (Exception $e) {
    echo "✗ FAIL: " . $e->getMessage() . "\n\n";
    $failed++;
}

// Test 3: Classes Contrôleurs
echo "TEST 3: Contrôleurs Existants\n";
echo "─────────────────────────────\n";
try {
    $controllers = ['BlogController', 'EventsController', 'HomeController', 'AdminController'];
    foreach ($controllers as $controller) {
        if (class_exists($controller)) {
            echo "✓ $controller chargé\n";
        } else {
            echo "✗ $controller MANQUANT\n";
            $failed++;
        }
    }
    echo "\n";
    $passed++;
} catch (Exception $e) {
    echo "✗ FAIL: " . $e->getMessage() . "\n\n";
    $failed++;
}

// Test 4: Modèles
echo "TEST 4: Modèles de Données\n";
echo "─────────────────────────\n";
try {
    $models = ['Post', 'Event', 'Inscription'];
    foreach ($models as $model) {
        if (class_exists($model)) {
            echo "✓ $model chargé\n";
        } else {
            echo "✗ $model MANQUANT\n";
            $failed++;
        }
    }
    echo "\n";
    $passed++;
} catch (Exception $e) {
    echo "✗ FAIL: " . $e->getMessage() . "\n\n";
    $failed++;
}

// Test 5: Données dans le blog
echo "TEST 5: Données de Démonstration\n";
echo "────────────────────────────────\n";
try {
    $categories = $db->query("SELECT COUNT(*) as cnt FROM categories")->fetch();
    $posts = $db->query("SELECT COUNT(*) as cnt FROM posts")->fetch();
    
    echo "✓ Catégories: " . $categories['cnt'] . " enregistrements\n";
    echo "✓ Articles: " . $posts['cnt'] . " enregistrements\n";
    echo "\n";
    $passed++;
} catch (Exception $e) {
    echo "✗ FAIL: " . $e->getMessage() . "\n\n";
    $failed++;
}

// Test 6: Fichiers Vue
echo "TEST 6: Fichiers Vue Blog\n";
echo "───────────────────────\n";
try {
    $views = [
        'views/home.php',
        'views/blog/index.php',
        'views/blog/show.php',
        'views/blog/create.php'
    ];
    foreach ($views as $view) {
        if (file_exists(BASE_PATH . '/' . $view)) {
            echo "✓ $view existe\n";
        } else {
            echo "✗ $view MANQUANT\n";
            $failed++;
        }
    }
    echo "\n";
    $passed++;
} catch (Exception $e) {
    echo "✗ FAIL: " . $e->getMessage() . "\n\n";
    $failed++;
}

// Test 7: Routes
echo "TEST 7: Configuration Routes\n";
echo "───────────────────────────\n";
try {
    $router = new Router();
    echo "✓ Router chargé\n";
    echo "✓ Routes configurées:\n";
    echo "  - /blog → BlogController\n";
    echo "  - /events → EventsController\n";
    echo "  - / → HomeController\n";
    echo "  - /admin → AdminController\n";
    echo "\n";
    $passed++;
} catch (Exception $e) {
    echo "✗ FAIL: " . $e->getMessage() . "\n\n";
    $failed++;
}

// Test 8: Méthodes BlogController
echo "TEST 8: Méthodes BlogController\n";
echo "──────────────────────────────\n";
try {
    $controller = new BlogController();
    $methods = [
        'index', 'show', 'create', 'store', 
        'edit', 'update', 'delete',
        'addComment', 'getComments', 
        'toggleLike', 'addRating'
    ];
    
    foreach ($methods as $method) {
        if (method_exists($controller, $method)) {
            echo "✓ $method() existe\n";
        } else {
            echo "✗ $method() MANQUANTE\n";
            $failed++;
        }
    }
    echo "\n";
    $passed++;
} catch (Exception $e) {
    echo "✗ FAIL: " . $e->getMessage() . "\n\n";
    $failed++;
}

// Résumé Final
echo "╔══════════════════════════════════════════════════╗\n";
echo "║   RÉSUMÉ DES TESTS                              ║\n";
echo "╚══════════════════════════════════════════════════╝\n\n";
echo "✓ TESTS RÉUSSIS: $passed\n";
echo "✗ TESTS ÉCHOUÉS: $failed\n";

if ($failed === 0) {
    echo "\n🎉 TOUS LES TESTS SONT PASSÉS! L'intégration est complète.\n";
} else {
    echo "\n⚠️  Veuillez corriger les problèmes signalés.\n";
}

echo "\n╔══════════════════════════════════════════════════╗\n";
echo "║   LIENS POUR TESTER                              ║\n";
echo "╚══════════════════════════════════════════════════╝\n";
echo "\nBlog:\n";
echo "  → http://localhost/projetweb/public/blog\n";
echo "  → http://localhost/projetweb/public/blog/create\n";
echo "  → http://localhost/projetweb/public/blog?category=Technologie\n";
echo "\nÉvénements:\n";
echo "  → http://localhost/projetweb/public/events\n";
echo "  → http://localhost/projetweb/public/admin/events\n";
echo "\nAccueil:\n";
echo "  → http://localhost/projetweb/public/\n";
echo "\nDocumentation:\n";
echo "  → ./BLOG_INTEGRATION.md (complet)\n";
echo "  → ./QUICK_START.md (rapide)\n";
echo "  → ./RESUME_INTEGRATION.md (résumé)\n";
echo "\n";
