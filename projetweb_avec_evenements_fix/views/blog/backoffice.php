<?php
require_once __DIR__ . '/../controller/BlogController.php';
require_once __DIR__ . '/../controller/CategoryController.php';
require_once __DIR__ . '/../controller/CommentController.php';
require_once __DIR__ . '/../controller/StoryController.php';
require_once __DIR__ . '/../mailer.php';

$blogController = new BlogController();
$catController = new CategoryController();
$commentController = new CommentController();
$storyController = new StoryController();

$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

// Traitement des requêtes POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($page === 'categories') {
        if ($_POST['action'] === 'add' && !empty($_POST['name'])) {
            $catController->addCategory($_POST['name']);
        } elseif ($_POST['action'] === 'edit' && !empty($_POST['id']) && !empty($_POST['name'])) {
            $catController->updateCategory($_POST['id'], $_POST['name']);
        } elseif ($_POST['action'] === 'delete' && !empty($_POST['id'])) {
            $catController->deleteCategory($_POST['id']);
        }
        $isAjax = !empty($_POST['ajax']) || (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
            exit;
        }
        header("Location: backoffice.php?page=categories");
        exit;
    } elseif ($page === 'posts') {
        $coverImage = isset($_FILES['cover_image']) ? $_FILES['cover_image'] : null;
        if ($_POST['action'] === 'add') {
            $post = new PostModel(
                $_POST['title'], $_POST['content'],
                $_POST['category_id'], null, $_POST['status']
            );
            $blogController->AjouterPost($post, $coverImage);
            
            // Notification Email
            $cat = $catController->getCategory($_POST['category_id']);
            $categoryName = $cat ? $cat['name'] : 'Général';
            Mailer::notifyNewPost($_POST['title'], $_POST['content'], $categoryName, $_POST['status']);
        } elseif ($_POST['action'] === 'edit' && !empty($_POST['id'])) {
            $post = new PostModel(
                $_POST['title'], $_POST['content'],
                $_POST['category_id'], $_POST['old_cover_image'], $_POST['status']
            );
            $blogController->ModifierPost($post, $_POST['id'], $coverImage);
        } elseif ($_POST['action'] === 'delete' && !empty($_POST['id'])) {
            $blogController->SupprimerPost($_POST['id']);
        }
        $isAjax = !empty($_POST['ajax']) || (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
            exit;
        }
        header("Location: backoffice.php?page=posts");
        exit;
    } elseif ($page === 'stories') {
        $storyFile = isset($_FILES['media_image']) ? $_FILES['media_image'] : null;
        try {
            if ($_POST['action'] === 'add') {
                $story = new StoryModel(
                    $_POST['title'] ?? '',
                    $_POST['content'] ?? '',
                    $_POST['post_id'] ?? null,
                    $_POST['cta_label'] ?? 'Lire le blog',
                    null,
                    $_POST['status'] ?? 'published',
                    $_POST['starts_at'] ?? '',
                    $_POST['expires_at'] ?? ''
                );
                $storyController->AjouterStory($story, $storyFile);
            } elseif ($_POST['action'] === 'edit' && !empty($_POST['id'])) {
                $story = new StoryModel(
                    $_POST['title'] ?? '',
                    $_POST['content'] ?? '',
                    $_POST['post_id'] ?? null,
                    $_POST['cta_label'] ?? 'Lire le blog',
                    $_POST['old_media_image'] ?? null,
                    $_POST['status'] ?? 'published',
                    $_POST['starts_at'] ?? '',
                    $_POST['expires_at'] ?? ''
                );
                $storyController->ModifierStory($story, $_POST['id'], $storyFile);
            } elseif ($_POST['action'] === 'delete' && !empty($_POST['id'])) {
                $storyController->SupprimerStory($_POST['id']);
            }
        } catch (Exception $e) {
            $message = addslashes($e->getMessage());
            echo "<script>alert('Erreur story: {$message}'); window.location.href='backoffice.php?page=stories';</script>";
            exit;
        }

        header("Location: backoffice.php?page=stories");
        exit;
    } elseif ($page === 'comments') {
        $badWords = ['merde', 'con', 'putain', 'salope', 'idiot', 'connard', 'bâtard', 'stupide'];
        $hasBadWords = false;
        
        if (isset($_POST['content'])) {
            foreach ($badWords as $word) {
                if (preg_match('/\b' . preg_quote($word, '/') . '\b/i', $_POST['content'])) {
                    $hasBadWords = true;
                    break;
                }
            }
        }

        if ($_POST['action'] === 'add' && !empty($_POST['post_id']) && !empty($_POST['content']) && !empty($_POST['user_name'])) {
            if (!$hasBadWords) {
                $comment = new CommentModel($_POST['post_id'], $_POST['user_name'], $_POST['content']);
                $commentController->addComment($comment);
                
                // Notification Email
                $post = $blogController->RecupererPost($_POST['post_id']);
                $postTitle = $post ? $post['title'] : 'Post inconnu';
                Mailer::notifyNewComment($_POST['user_name'], $_POST['content'], $postTitle);
            } else {
                echo "<script>alert('Le commentaire contient des mots inappropriés et a été bloqué.'); window.location.href='backoffice.php?page=comments';</script>";
                exit;
            }
        } elseif ($_POST['action'] === 'edit' && !empty($_POST['id']) && !empty($_POST['content']) && !empty($_POST['user_name'])) {
            if (!$hasBadWords) {
                $commentController->updateComment($_POST['id'], $_POST['content'], $_POST['user_name']);
            } else {
                echo "<script>alert('Le commentaire contient des mots inappropriés et a été bloqué.'); window.location.href='backoffice.php?page=comments';</script>";
                exit;
            }
        } elseif ($_POST['action'] === 'delete' && !empty($_POST['id'])) {
            $commentController->deleteComment($_POST['id']);
        }
        header("Location: backoffice.php?page=comments");
        exit;
    }
}

// Données Globales
$categoriesList = $catController->getCategories();
$commentsList = $commentController->getComments();
$commentsTotal = $commentController->countComments();
$storiesList = $storyController->AfficherStories();
$activeStoriesTotal = $storyController->CountActiveStories();
$storyViewsTotal = $storyController->GetTotalStoryViews();
$topStories = $storyController->GetTopStories(5);

$catId = isset($_GET['cat_id']) ? $_GET['cat_id'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : '';
$order = isset($_GET['order']) ? $_GET['order'] : 'ASC';
$search = isset($_GET['search']) ? $_GET['search'] : '';

if (!empty($search)) {
    $postsList = $blogController->RecherchePost($search);
} elseif (!empty($catId)) {
    $postsList = $blogController->RechercheParCategorie($catId);
} elseif (!empty($sort)) {
    $postsList = $blogController->TrierPosts($sort, $order);
} else {
    $postsList = $blogController->AfficherPosts();
}
$allPostsForSelect = $blogController->AfficherPosts();

$advancedStats = $blogController->GetAdvancedStats(7, 5);
$totalViews = $advancedStats['total_views'];
$totalLikes = $advancedStats['total_likes'];
$topViewedPosts = $advancedStats['top_viewed'];
$topLikedPosts = $advancedStats['top_liked'];
$topCommentedPosts = $advancedStats['top_commented'];
$commentsEvolution = $advancedStats['comments_evolution'];
$maxDailyComments = 1;
foreach ($commentsEvolution as $dayStats) {
    $maxDailyComments = max($maxDailyComments, (int) $dayStats['comments_count']);
}

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Jobyfind — Admin</title>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    :root { 
      --blue: #2d79ff; --navy: #192135; --bg: #f4f6fa; --surface: #ffffff; 
      --text-main: #111827; --text-muted: #6b7280; --border: #e5e7eb; 
      --radius: 8px; --sidebar-w: 240px; 
    }
    body { font-family: 'DM Sans', sans-serif; background: var(--bg); display: flex; min-height: 100vh; color: var(--text-main); }
    
    /* ── SIDEBAR ── */
    .sidebar { width: var(--sidebar-w); background: var(--navy); display: flex; flex-direction: column; position: fixed; left: 0; top: 0; bottom: 0; color: #fff; }
    .brand { display: flex; align-items: center; gap: 12px; padding: 24px; font-size: 18px; font-weight: 700; color: #fff; text-decoration: none; }
    .brand i { color: var(--blue); font-size: 20px; }
    .nav-section { padding: 16px 20px; }
    .nav-label { font-size: 10px; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 12px; }
    .nav-link { display: flex; align-items: center; gap: 12px; padding: 10px 16px; border-radius: 8px; color: #9ca3af; text-decoration: none; font-size: 13px; font-weight: 500; margin-bottom: 4px; transition: all 0.2s; }
    .nav-link:hover { color: #fff; background: rgba(255,255,255,0.05); }
    .nav-link.active { background: var(--blue); color: #fff; }
    .badge-red { margin-left: auto; background: #ef4444; color: #fff; font-size: 10px; font-weight: 700; padding: 2px 6px; border-radius: 4px; }
    
    .user-profile { margin-top: auto; padding: 12px; margin: 20px; background: #252d43; border-radius: 8px; display: flex; align-items: center; gap: 10px; }
    .user-avatar { width: 32px; height: 32px; background: var(--blue); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 12px; }
    .user-info { display: flex; flex-direction: column; }
    .user-name { font-size: 12px; font-weight: 700; }
    .user-email { font-size: 10px; color: #9ca3af; }

    /* ── MAIN CONTENT ── */
    .main-content { margin-left: var(--sidebar-w); padding: 32px 40px; flex: 1; }
    .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px; }
    .page-title { font-size: 24px; font-weight: 700; margin-bottom: 8px; }
    .page-subtitle { color: var(--text-muted); font-size: 14px; }
    
    /* Boutons */
    .btn { background: var(--blue); color: #fff; padding: 10px 16px; border-radius: var(--radius); text-decoration: none; font-size: 13px; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; cursor: pointer; border: none; }
    .btn:hover { opacity: 0.9; }
    .btn-danger { background: #ef4444; }
    .btn-light { background: #e5e7eb; color: #374151; }
    
    /* Stats */
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 24px; margin-bottom: 32px; }
    .stat-card { background: var(--surface); border-radius: var(--radius); padding: 24px; display: flex; align-items: center; gap: 20px; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
    .stat-icon { font-size: 28px; width: 48px; height: 48px; display: flex; align-items:center; justify-content:center; border-radius: 8px; }
    .stat-icon.blue { color: var(--blue); background: #eff6ff; }
    .stat-icon.green { color: #10b981; background: #ecfdf5; }
    .stat-icon.orange { color: #f59e0b; background: #fffbeb; }
    .stat-icon.pink { color: #e11d48; background: #fff1f2; }
    .stat-icon.purple { color: #7c3aed; background: #f5f3ff; }
    .stat-info { display: flex; flex-direction: column; }
    .stat-label { font-size: 12px; color: var(--text-muted); font-weight: 500; margin-bottom: 2px; }
    .stat-value { font-size: 24px; font-weight: 700; color: var(--text-main); line-height: 1; }
    .export-actions { display: flex; gap: 10px; flex-wrap: wrap; }
    .analytics-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 24px; margin-bottom: 30px; }
    .analytics-card { background: var(--surface); border-radius: var(--radius); box-shadow: 0 1px 2px rgba(0,0,0,0.05); overflow: hidden; }
    .analytics-card.full { grid-column: 1 / -1; }
    .analytics-card-header { padding: 18px 20px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; font-size: 15px; font-weight: 700; }
    .analytics-list { padding: 8px 0; }
    .analytics-row { display: grid; grid-template-columns: 1fr auto; gap: 16px; align-items: center; padding: 12px 20px; border-bottom: 1px solid var(--border); }
    .analytics-row:last-child { border-bottom: none; }
    .analytics-title { font-size: 13px; font-weight: 600; color: var(--text-main); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .analytics-value { font-size: 13px; font-weight: 700; color: var(--blue); white-space: nowrap; }
    .chart-bars { display: grid; grid-template-columns: repeat(7, 1fr); gap: 12px; align-items: end; min-height: 190px; padding: 22px 24px 18px; }
    .chart-bar-item { display: flex; flex-direction: column; align-items: center; gap: 8px; height: 160px; justify-content: flex-end; }
    .chart-bar { width: 100%; max-width: 44px; min-height: 6px; border-radius: 6px 6px 2px 2px; background: linear-gradient(180deg, var(--blue), #60a5fa); }
    .chart-value { font-size: 12px; font-weight: 700; color: var(--text-main); }
    .chart-label { font-size: 11px; color: var(--text-muted); white-space: nowrap; }
    .empty-analytics { padding: 20px; color: var(--text-muted); font-size: 13px; text-align: center; }
    @media (max-width: 980px) { .analytics-grid { grid-template-columns: 1fr; } }

    /* Tables */
    .table-section { background: var(--surface); border-radius: var(--radius); box-shadow: 0 1px 2px rgba(0,0,0,0.05); overflow: hidden; margin-bottom: 30px; }
    .table-header { padding: 20px 24px; font-size: 16px; font-weight: 700; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; }
    table { width: 100%; border-collapse: collapse; }
    th { text-align: left; padding: 12px 24px; font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; background: #f9fafb; border-bottom: 1px solid var(--border); }
    td { padding: 16px 24px; font-size: 13px; font-weight: 500; border-bottom: 1px solid var(--border); }
    tr:last-child td { border-bottom: none; }
    
    .actions { display: flex; gap: 8px; }
    .action-btn { padding: 6px 10px; border-radius: 4px; font-size: 12px; font-weight: 600; text-decoration: none; cursor: pointer; border: none; display: inline-block; }
    .btn-edit { background: #eff6ff; color: var(--blue); }
    .btn-delete { background: #fef2f2; color: #ef4444; }
    
    /* Badges */
    .status-badge { display: inline-block; padding: 4px 10px; border-radius: 4px; font-size: 10px; font-weight: 700; background: #dcfce7; color: #166534; text-transform: uppercase; }
    .status-draft { background: #f3f4f6; color: #4b5563; }
    .status-expired { background: #fee2e2; color: #991b1b; }
    .status-scheduled { background: #dbeafe; color: #1d4ed8; }
    .story-thumb { width: 54px; height: 54px; border-radius: 8px; object-fit: cover; background: #eff6ff; color: var(--blue); display: inline-flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0; }
    .story-cell { display: flex; align-items: center; gap: 12px; min-width: 220px; }
    .story-title { color: var(--text-main); display: block; font-weight: 700; }
    .story-desc { color: var(--text-muted); display: block; max-width: 360px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

    /* Forms */
    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    .form-group { margin-bottom: 15px; }
    .form-group.full { grid-column: span 2; }
    .form-group label { display: block; font-size: 12px; font-weight: 600; margin-bottom: 6px; color: var(--text-muted); }
    .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 10px 14px; border: 1px solid var(--border); border-radius: 6px; font-family: 'DM Sans'; font-size: 13px; outline: none; }
    .form-group input:focus, .form-group select:focus, .form-group textarea:focus { border-color: var(--blue); }
    .form-group textarea { resize: vertical; min-height: 100px; }
  </style>
</head>
<body>

  <!-- SIDEBAR -->
  <aside class="sidebar">
    <a href="frontoffice.php" class="brand">
      <i class="fas fa-play-circle"></i> JobyFind
    </a>

    <div class="nav-section">
      <div class="nav-label">TABLEAU DE BORD</div>
      <a href="backoffice.php?page=dashboard" class="nav-link <?= $page === 'dashboard' ? 'active' : '' ?>">
        <i class="fas fa-chart-line"></i> Vue d'ensemble
      </a>
    </div>

    <div class="nav-section">
      <div class="nav-label">GESTION</div>
      <a href="backoffice.php?page=categories" class="nav-link <?= $page === 'categories' ? 'active' : '' ?>">
        <i class="fas fa-folder"></i> Catégories
      </a>
      <a href="backoffice.php?page=posts" class="nav-link <?= $page === 'posts' ? 'active' : '' ?>">
        <i class="fas fa-book"></i> Blogs
      </a>
      <a href="backoffice.php?page=stories" class="nav-link <?= $page === 'stories' ? 'active' : '' ?>">
        <i class="fas fa-circle-play"></i> Stories
        <span class="badge-red"><?= $activeStoriesTotal ?></span>
      </a>
      <a href="backoffice.php?page=comments" class="nav-link <?= $page === 'comments' ? 'active' : '' ?>">
        <i class="fas fa-comments"></i> Commentaires
        <span class="badge-red"><?= $commentsTotal ?></span>
      </a>
    </div>

    <div class="nav-section">
      <div class="nav-label">OUTILS IA</div>
      <a href="backoffice.php?page=posts&action=add" class="nav-link <?= ($page === 'posts' && $action === 'add') ? 'active' : '' ?>" style="<?= ($page === 'posts' && $action === 'add') ? '' : 'background:linear-gradient(135deg,rgba(139,92,246,0.18),rgba(29,78,216,0.12));color:#a78bfa;' ?>">
        <i class="fas fa-wand-magic-sparkles"></i> Générer un Blog ✨
      </a>
    </div>

    <div class="user-profile">
      <div class="user-avatar">SA</div>
      <div class="user-info">
        <span class="user-name">Super Admin</span>
        <span class="user-email">admin@projetweb.tn</span>
      </div>
    </div>
  </aside>

  <!-- MAIN CONTENT -->
  <main class="main-content">

    <?php if ($page === 'dashboard'): ?>
      <!-- ============================================== -->
      <!-- VUE : TABLEAU DE BORD                          -->
      <!-- ============================================== -->
      <div class="page-header">
        <div>
          <h1 class="page-title">Tableau de Bord</h1>
          <p class="page-subtitle">Bienvenue sur votre espace d'administration</p>
        </div>
        <div class="export-actions">
          <a href="../export_stats_pdf.php" class="btn" target="_blank"><i class="fas fa-file-pdf"></i> Export PDF</a>
          <a href="../export_stats_excel.php" class="btn btn-light"><i class="fas fa-file-excel"></i> Export Excel</a>
        </div>
      </div>

      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon blue"><i class="fas fa-book"></i></div>
          <div class="stat-info">
            <span class="stat-label">Blogs</span>
            <span class="stat-value"><?= count($postsList) ?></span>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon green"><i class="fas fa-folder"></i></div>
          <div class="stat-info">
            <span class="stat-label">Catégories</span>
            <span class="stat-value"><?= count($categoriesList) ?></span>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon blue"><i class="fas fa-circle-play"></i></div>
          <div class="stat-info">
            <span class="stat-label">Stories actives</span>
            <span class="stat-value"><?= $activeStoriesTotal ?></span>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon purple"><i class="fas fa-eye"></i></div>
          <div class="stat-info">
            <span class="stat-label">Vues</span>
            <span class="stat-value"><?= $totalViews ?></span>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon pink"><i class="fas fa-heart"></i></div>
          <div class="stat-info">
            <span class="stat-label">Likes</span>
            <span class="stat-value"><?= $totalLikes ?></span>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon orange"><i class="fas fa-comments"></i></div>
          <div class="stat-info">
            <span class="stat-label">Commentaires</span>
            <span class="stat-value"><?= $commentsTotal ?></span>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon purple"><i class="fas fa-bolt"></i></div>
          <div class="stat-info">
            <span class="stat-label">Vues stories</span>
            <span class="stat-value"><?= $storyViewsTotal ?></span>
          </div>
        </div>
      </div>

      <div class="analytics-grid">
        <div class="analytics-card">
          <div class="analytics-card-header">
            <span><i class="fas fa-eye" style="color: var(--blue); margin-right: 8px;"></i>Nombre de vues par post</span>
          </div>
          <div class="analytics-list">
            <?php if (empty($topViewedPosts)): ?>
              <div class="empty-analytics">Aucune donnée de vues.</div>
            <?php else: foreach ($topViewedPosts as $postStat): ?>
              <div class="analytics-row">
                <span class="analytics-title"><?= htmlspecialchars($postStat['title']) ?></span>
                <span class="analytics-value"><?= (int) $postStat['views_count'] ?> vues</span>
              </div>
            <?php endforeach; endif; ?>
          </div>
        </div>

        <div class="analytics-card">
          <div class="analytics-card-header">
            <span><i class="fas fa-heart" style="color: #e11d48; margin-right: 8px;"></i>Posts les plus likés</span>
          </div>
          <div class="analytics-list">
            <?php if (empty($topLikedPosts)): ?>
              <div class="empty-analytics">Aucun like pour le moment.</div>
            <?php else: foreach ($topLikedPosts as $postStat): ?>
              <div class="analytics-row">
                <span class="analytics-title"><?= htmlspecialchars($postStat['title']) ?></span>
                <span class="analytics-value"><?= (int) $postStat['likes_count'] ?> likes</span>
              </div>
            <?php endforeach; endif; ?>
          </div>
        </div>

        <div class="analytics-card">
          <div class="analytics-card-header">
            <span><i class="fas fa-circle-play" style="color: #7c3aed; margin-right: 8px;"></i>Stories les plus vues</span>
          </div>
          <div class="analytics-list">
            <?php if (empty($topStories)): ?>
              <div class="empty-analytics">Aucune story pour le moment.</div>
            <?php else: foreach ($topStories as $storyStat): ?>
              <div class="analytics-row">
                <span class="analytics-title"><?= htmlspecialchars($storyStat['title']) ?></span>
                <span class="analytics-value"><?= (int) $storyStat['views_count'] ?> vues</span>
              </div>
            <?php endforeach; endif; ?>
          </div>
        </div>

        <div class="analytics-card">
          <div class="analytics-card-header">
            <span><i class="fas fa-comments" style="color: #f59e0b; margin-right: 8px;"></i>Formations les plus commentées</span>
          </div>
          <div class="analytics-list">
            <?php if (empty($topCommentedPosts)): ?>
              <div class="empty-analytics">Aucun commentaire pour le moment.</div>
            <?php else: foreach ($topCommentedPosts as $postStat): ?>
              <div class="analytics-row">
                <span class="analytics-title"><?= htmlspecialchars($postStat['title']) ?></span>
                <span class="analytics-value"><?= (int) $postStat['comments_count'] ?> commentaires</span>
              </div>
            <?php endforeach; endif; ?>
          </div>
        </div>

        <div class="analytics-card">
          <div class="analytics-card-header">
            <span><i class="fas fa-chart-column" style="color: #10b981; margin-right: 8px;"></i>Evolution des commentaires</span>
            <span style="font-size: 12px; color: var(--text-muted);">7 derniers jours</span>
          </div>
          <div class="chart-bars">
            <?php foreach ($commentsEvolution as $dayStats): 
              $barHeight = max(6, round(((int) $dayStats['comments_count'] / $maxDailyComments) * 120));
            ?>
              <div class="chart-bar-item">
                <span class="chart-value"><?= (int) $dayStats['comments_count'] ?></span>
                <div class="chart-bar" style="height: <?= $barHeight ?>px;"></div>
                <span class="chart-label"><?= htmlspecialchars($dayStats['label']) ?></span>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <div class="table-section">
        <div class="table-header">Dernières blogs</div>
        <table>
          <thead>
            <tr>
              <th>BLOGS</th>
              <th>CRÉÉE LE</th>
              <th>STATUT</th>
            </tr>
          </thead>
          <tbody>
            <?php 
            $recentPosts = array_slice($postsList, 0, 5);
            if (empty($recentPosts)): ?>
              <tr><td colspan="3" style="text-align:center;">Aucune blogs.</td></tr>
            <?php else: foreach($recentPosts as $p): ?>
              <tr>
                <td><?= htmlspecialchars($p['title']) ?></td>
                <td><?= date('d/m/Y', strtotime($p['created_at'])) ?></td>
                <td><span class="status-badge <?= $p['status'] == 'draft' ? 'status-draft' : '' ?>">
                  <?= $p['status'] == 'published' ? 'PUBLIÉ' : 'BROUILLON' ?>
                </span></td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>


    <?php elseif ($page === 'categories'): ?>
      <!-- ============================================== -->
      <!-- VUE : CATÉGORIES                               -->
      <!-- ============================================== -->
      <div class="page-header">
        <div>
          <h1 class="page-title">Gestion des Catégories</h1>
          <p class="page-subtitle">Ajouter, modifier ou supprimer des catégories.</p>
        </div>
      </div>

      <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 30px;">
        <div class="table-section">
          <?php 
          $editMode = false; $editCat = null;
          if(isset($_GET['edit'])) {
              $editCat = $catController->getCategory($_GET['edit']);
              if($editCat) $editMode = true;
          }
          ?>
          <div class="table-header"><?= $editMode ? "Modifier la Catégorie" : "Nouvelle Catégorie" ?></div>
          <div style="padding: 24px;">
              <form method="POST" action="backoffice.php?page=categories" id="categoryForm">
                  <input type="hidden" name="action" value="<?= $editMode ? 'edit' : 'add' ?>">
                  <?php if($editMode): ?>
                      <input type="hidden" name="id" value="<?= $editCat['id'] ?>">
                  <?php endif; ?>
                  
                  <div class="form-group">
                      <label>Nom de la catégorie</label>
                      <input type="text" name="name" id="catNameInput" value="<?= $editMode ? htmlspecialchars($editCat['name']) : '' ?>" placeholder="Ex: Développement">
                      <div id="catNameError" style="color: #ef4444; font-size: 12px; margin-top: 5px; display: none;"></div>
                  </div>
                  <button type="submit" class="btn">
                      <i class="fas <?= $editMode ? 'fa-save' : 'fa-plus' ?>"></i>
                      <?= $editMode ? 'Enregistrer' : 'Ajouter' ?>
                  </button>
                  <?php if($editMode): ?>
                      <a href="backoffice.php?page=categories" class="btn btn-light" style="margin-left:10px;">Annuler</a>
                  <?php endif; ?>
              </form>
          </div>
        </div>

        <div class="table-section">
          <div class="table-header">Liste des Catégories</div>
          <table>
            <thead>
              <tr><th>ID</th><th>NOM</th><th>ACTIONS</th></tr>
            </thead>
            <tbody>
              <?php if(empty($categoriesList)): ?>
                <tr><td colspan="3" style="text-align:center;">Aucune catégorie.</td></tr>
              <?php else: foreach($categoriesList as $cat): ?>
              <tr>
                <td>#<?= $cat['id'] ?></td>
                <td><?= htmlspecialchars($cat['name']) ?></td>
                <td class="actions">
                  <a href="backoffice.php?page=categories&edit=<?= $cat['id'] ?>" class="action-btn btn-edit"><i class="fas fa-edit"></i> Modifier</a>
                  <form method="POST" action="backoffice.php?page=categories" style="display:inline;" onsubmit="return confirm('Supprimer cette catégorie ?');">
                      <input type="hidden" name="action" value="delete">
                      <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                      <button type="submit" class="action-btn btn-delete"><i class="fas fa-trash"></i></button>
                  </form>
                </td>
              </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
      </div>


    <?php elseif ($page === 'posts'): ?>
      <!-- ============================================== -->
      <!-- VUE : FORMATIONS                               -->
      <!-- ============================================== -->
      <div class="page-header">
        <div>
          <h1 class="page-title">Gestion des blogs</h1>
          <p class="page-subtitle">Gérez le catalogue de vos cours en ligne.</p>
        </div>
        <?php if ($action === 'list'): ?>
          <a href="backoffice.php?page=posts&action=add" class="btn"><i class="fas fa-plus"></i> Nouvelle Publication</a>
        <?php endif; ?>
      </div>

      <?php if ($action === 'add' || $action === 'edit'): ?>



          <?php if ($action === 'add'): ?>
          <div id="aiAssistantPanel" style="
              background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #312e81 100%);
              border-radius: 16px;
              padding: 35px;
              margin-bottom: 30px;
              box-shadow: 0 10px 40px rgba(0,0,0,0.3);
              position: relative;
              border: 1px solid rgba(255,255,255,0.1);
          ">
              <div style="position:relative; z-index:2;">
                  <div style="display:flex; align-items:center; gap:15px; margin-bottom:25px;">
                      <div style="width:50px; height:50px; background:rgba(255,255,255,0.1); border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:24px;">✨</div>
                      <div>
                          <h2 style="color:#fff; font-size:20px; font-weight:700; margin:0;">Générateur de Blog Intelligent</h2>
                          <p style="color:rgba(255,255,255,0.6); font-size:14px; margin:5px 0 0;">L'IA s'occupe de tout : titre, contenu et publication immédiate.</p>
                      </div>
                  </div>

                  <div style="display:grid; grid-template-columns: 1fr 200px 180px; gap:15px; align-items:flex-end;">
                      <div>
                          <label style="display:block; color:rgba(255,255,255,0.7); font-size:12px; font-weight:600; text-transform:uppercase; margin-bottom:10px;">Quel est le sujet du blog ?</label>
                          <input type="text" id="aiThemeInput" placeholder="Ex: Les secrets de l'IA en 2025..." 
                                 style="width:100%; padding:14px 18px; border-radius:10px; border:1px solid rgba(255,255,255,0.1); background:rgba(255,255,255,0.05); color:#fff; font-size:15px; outline:none; transition:0.3s;">
                      </div>
                      <div>
                          <label style="display:block; color:rgba(255,255,255,0.7); font-size:12px; font-weight:600; text-transform:uppercase; margin-bottom:10px;">Catégorie</label>
                          <select id="aiCategoryInput" style="width:100%; padding:14px; border-radius:10px; border:1px solid rgba(255,255,255,0.1); background:rgba(255,255,255,0.1); color:#fff; outline:none; cursor:pointer;">
                              <?php foreach($categoriesList as $cat): ?>
                              <option value="<?= $cat['id'] ?>" style="background:#1e1b4b;"><?= htmlspecialchars($cat['name']) ?></option>
                              <?php endforeach; ?>
                          </select>
                      </div>
                      <button type="button" id="aiGenerateBtn" onclick="generateAndPublish()" 
                              style="width:100%; padding:14px; background:#6366f1; color:#fff; border:none; border-radius:10px; font-weight:700; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:10px; transition:0.3s;">
                          <i class="fas fa-magic"></i> GÉNÉRER
                      </button>
                  </div>

                  <!-- Overlay de chargement -->
                  <div id="aiOverlay" style="display:none; margin-top:25px; background:rgba(255,255,255,0.05); border-radius:12px; padding:20px; text-align:center;">
                      <div class="ai-loader" style="display:inline-block; width:30px; height:30px; border:3px solid rgba(255,255,255,0.1); border-top-color:#6366f1; border-radius:50%; animation:spin 1s linear infinite;"></div>
                      <p style="color:#fff; margin-top:10px; font-size:14px;">Création de l'article en cours...</p>
                  </div>

                  <!-- Succès -->
                  <div id="aiSuccess" style="display:none; margin-top:25px; background:rgba(16,185,129,0.1); border:1px solid rgba(16,185,129,0.3); border-radius:12px; padding:20px;">
                      <div style="display:flex; align-items:flex-start; gap:20px;">
                          <div id="aiImagePreview" style="width:120px; height:80px; background:#000; border-radius:8px; overflow:hidden; border:2px solid rgba(255,255,255,0.1); flex-shrink:0;">
                              <img src="" style="width:100%; height:100%; object-fit:cover;">
                          </div>
                          <div style="flex:1;">
                              <h4 style="color:#fff; margin:0; font-size:16px;">Article & Image publiés !</h4>
                              <p id="aiSuccessMsg" style="color:rgba(255,255,255,0.6); font-size:13px; margin:5px 0 0;"></p>
                              <div style="display:flex; gap:10px; margin-top:15px;">
                                  <a href="backoffice.php?page=posts" class="btn btn-light" style="padding:6px 12px; font-size:11px;">Catalogue</a>
                                  <a id="aiEditLink" href="#" class="btn" style="padding:6px 12px; font-size:11px; background:#6366f1;">Détails</a>
                              </div>
                          </div>
                      </div>
                  </div>
              </div>
          </div>

          <script>
          function generateAndPublish() {
              const theme = document.getElementById('aiThemeInput').value.trim();
              const catId = document.getElementById('aiCategoryInput').value;
              const btn = document.getElementById('aiGenerateBtn');
              const overlay = document.getElementById('aiOverlay');
              const success = document.getElementById('aiSuccess');

              if (!theme) { alert('Veuillez saisir un thème.'); return; }

              btn.disabled = true;
              btn.style.opacity = '0.5';
              overlay.style.display = 'block';
              success.style.display = 'none';

              const formData = new FormData();
              formData.append('theme', theme);
              formData.append('category_id', catId);
              formData.append('status', 'published');

              fetch('../ajax_generate_blog.php', { method: 'POST', body: formData })
                  .then(r => r.json())
                  .then(data => {
                      btn.disabled = false;
                      btn.style.opacity = '1';
                      overlay.style.display = 'none';

                      if (data.success) {
                          // Mise à jour de l'image
                          const img = document.querySelector('#aiImagePreview img');
                          img.src = data.image;
                          
                          document.getElementById('aiSuccessMsg').textContent = `"${data.title}" a été publié avec une image personnalisée.`;
                          document.getElementById('aiEditLink').href = `backoffice.php?page=posts&action=edit&id=${data.id}`;
                          success.style.display = 'block';
                          document.getElementById('aiThemeInput').value = '';
                      } else {
                          alert('Erreur: ' + data.message);
                      }
                  })
                  .catch(err => {
                      btn.disabled = false;
                      btn.style.opacity = '1';
                      overlay.style.display = 'none';
                      alert('Erreur serveur.');
                  });
          }
          </script>

          <style>
          @keyframes spin { to { transform: rotate(360deg); } }
          </style>
          <?php endif; ?>



      <?php else: ?>
          <div class="table-section">
              <div class="table-header" style="flex-wrap: wrap; gap: 15px;">
                  <div>Toutes les blogs</div>
                  <div style="display: flex; gap: 10px; align-items: center; font-size: 13px; flex-wrap: wrap;">
                      <!-- Recherche dynamique (standalone) -->
                      <div style="position: relative;">
                        <i class="fas fa-search" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 12px;"></i>
                        <input type="text" id="blogSearchInput" placeholder="Rechercher par nom..." 
                               style="padding: 8px 12px 8px 32px; border-radius: 6px; border: 1px solid var(--border); outline: none; font-family: 'DM Sans'; font-size: 13px; width: 200px; transition: border-color 0.2s, box-shadow 0.2s;"
                               onfocus="this.style.borderColor='var(--blue)'; this.style.boxShadow='0 0 0 3px rgba(45,121,255,0.1)'"
                               onblur="this.style.borderColor='var(--border)'; this.style.boxShadow='none'">
                      </div>

                      <!-- Filtres serveur (catégorie, tri) -->
                      <form method="GET" action="backoffice.php" style="display: flex; gap: 10px; align-items: center;">
                          <input type="hidden" name="page" value="posts">
                      
                          <select name="cat_id" style="padding: 8px; border-radius: 6px; border: 1px solid var(--border); outline: none; font-family: 'DM Sans'; font-size: 13px;">
                              <option value="">Toutes les catégories</option>
                              <?php foreach($categoriesList as $c): ?>
                                  <option value="<?= $c['id'] ?>" <?= (isset($_GET['cat_id']) && $_GET['cat_id'] == $c['id']) ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option>
                              <?php endforeach; ?>
                          </select>

                          <select name="sort" style="padding: 8px; border-radius: 6px; border: 1px solid var(--border); outline: none; font-family: 'DM Sans'; font-size: 13px;">
                              <option value="">Trier par...</option>
                              <option value="title" <?= (isset($_GET['sort']) && $_GET['sort'] == 'title') ? 'selected' : '' ?>>Titre</option>
                              <option value="created_at" <?= (isset($_GET['sort']) && $_GET['sort'] == 'created_at') ? 'selected' : '' ?>>Date</option>
                          </select>

                          <select name="order" style="padding: 8px; border-radius: 6px; border: 1px solid var(--border); outline: none; font-family: 'DM Sans'; font-size: 13px;">
                              <option value="ASC" <?= (isset($_GET['order']) && $_GET['order'] == 'ASC') ? 'selected' : '' ?>>↑ Croissant</option>
                              <option value="DESC" <?= (isset($_GET['order']) && $_GET['order'] == 'DESC') ? 'selected' : '' ?>>↓ Décroissant</option>
                          </select>

                          <button type="submit" class="btn" style="padding: 8px 14px;"><i class="fas fa-filter"></i> Filtrer</button>
                          <a href="backoffice.php?page=posts" class="btn btn-light" style="padding: 8px 14px;" title="Réinitialiser"><i class="fas fa-sync-alt"></i></a>
                      </form>
                  </div>
              </div>
              <table id="blogsTable">
                  <thead>
                      <tr>
                          <th>BLOG</th>
                          <th>CATÉGORIE</th>
                          <th>PRIX</th>
                          <th>STATUT</th>
                          <th>ACTIONS</th>
                      </tr>
                  </thead>
                  <tbody id="blogsTableBody">
                      <?php if (empty($postsList)): ?>
                          <tr class="no-blogs-row"><td colspan="5" style="text-align:center;">Aucune blog trouvée.</td></tr>
                      <?php else: foreach($postsList as $p): ?>
                          <tr class="blog-row">
                              <td>
                                  <strong style="color:var(--text-main); display:block;"><?= htmlspecialchars($p['title']) ?></strong>
                                  <small style="color:var(--text-muted);"><?= htmlspecialchars($p['instructor']) ?></small>
                              </td>
                              <td><?= htmlspecialchars($p['category']) ?></td>
                              <td><?= $p['price'] > 0 ? htmlspecialchars($p['price']) . ' TND' : 'Gratuit' ?></td>
                              <td>
                                  <span class="status-badge <?= $p['status'] == 'draft' ? 'status-draft' : '' ?>">
                                      <?= $p['status'] == 'published' ? 'PUBLIÉ' : 'BROUILLON' ?>
                                  </span>
                              </td>
                              <td class="actions">
                                  <a href="../export_post_pdf.php?id=<?= $p['id'] ?>" class="action-btn" style="background: #fff1f2; color: #e11d48;" title="Exporter en PDF" target="_blank"><i class="fas fa-file-pdf"></i></a>
                                  <button type="button" class="action-btn" style="background: #f0f9ff; color: #0284c7; cursor: pointer;" onclick="showPostDetails(<?= $p['id'] ?>)" title="Voir les commentaires et likes"><i class="fas fa-eye"></i></button>
                                  <a href="backoffice.php?page=posts&action=edit&id=<?= $p['id'] ?>" class="action-btn btn-edit"><i class="fas fa-edit"></i></a>
                                  <form method="POST" action="backoffice.php?page=posts" style="display:inline;" onsubmit="return confirm('Confirmer la suppression ?');">
                                      <input type="hidden" name="action" value="delete">
                                      <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                      <button type="submit" class="action-btn btn-delete"><i class="fas fa-trash"></i></button>
                                  </form>
                              </td>
                          </tr>
                          <?php endforeach; ?>
                      <?php endif; ?>
                  </tbody>
              </table>
              <!-- Message quand aucun résultat de recherche blog -->
              <div id="noBlogSearchResults" style="display: none; text-align: center; padding: 24px; color: var(--text-muted); font-size: 14px;">
                <i class="fas fa-search" style="font-size: 24px; margin-bottom: 8px; display: block; opacity: 0.5;"></i>
                Aucun blog ne correspond à votre recherche.
              </div>
          </div>
      <?php endif; ?>

    <?php elseif ($page === 'stories'): ?>
      <!-- ============================================== -->
      <!-- VUE : STORIES                                  -->
      <!-- ============================================== -->
      <div class="page-header">
        <div>
          <h1 class="page-title">Gestion des Stories</h1>
          <p class="page-subtitle">Mettez en avant vos blogs avec des stories temporaires.</p>
        </div>
        <?php if ($action === 'list'): ?>
          <a href="backoffice.php?page=stories&action=add" class="btn"><i class="fas fa-plus"></i> Nouvelle Story</a>
        <?php endif; ?>
      </div>

      <?php if ($action === 'add' || $action === 'edit'): ?>
          <div class="table-section">
              <?php
              $editStory = null;
              if ($action === 'edit' && isset($_GET['id'])) {
                  $editStory = $storyController->RecupererStory($_GET['id']);
              }
              $defaultStartsAt = date('Y-m-d\TH:i');
              $defaultExpiresAt = date('Y-m-d\TH:i', strtotime('+24 hours'));
              $startsValue = $editStory ? date('Y-m-d\TH:i', strtotime($editStory['starts_at'])) : $defaultStartsAt;
              $expiresValue = $editStory ? date('Y-m-d\TH:i', strtotime($editStory['expires_at'])) : $defaultExpiresAt;
              ?>
              <div class="table-header">
                  <?= $action === 'edit' ? "Modifier la Story" : "Créer une Story" ?>
                  <a href="backoffice.php?page=stories" class="btn btn-light"><i class="fas fa-arrow-left"></i> Retour</a>
              </div>
              <div style="padding: 24px;">
                  <form method="POST" action="backoffice.php?page=stories" enctype="multipart/form-data" id="storyForm">
                      <input type="hidden" name="action" value="<?= $action ?>">
                      <?php if ($action === 'edit' && $editStory): ?>
                          <input type="hidden" name="id" value="<?= $editStory['id'] ?>">
                          <input type="hidden" name="old_media_image" value="<?= htmlspecialchars($editStory['media_image'] ?? '') ?>">
                      <?php endif; ?>

                      <div class="form-grid">
                          <div class="form-group full">
                              <label>Titre de la story *</label>
                              <input type="text" name="title" id="storyTitleInput" value="<?= $editStory ? htmlspecialchars($editStory['title']) : '' ?>" maxlength="180">
                              <div id="storyTitleError" style="color: #ef4444; font-size: 12px; margin-top: 5px; display: none;"></div>
                          </div>

                          <div class="form-group full">
                              <label>Texte court *</label>
                              <textarea name="content" id="storyContentInput" maxlength="600" style="min-height: 110px;"><?= $editStory ? htmlspecialchars($editStory['content']) : '' ?></textarea>
                              <div id="storyContentError" style="color: #ef4444; font-size: 12px; margin-top: 5px; display: none;"></div>
                          </div>

                          <div class="form-group">
                              <label>Blog associé</label>
                              <select name="post_id" id="storyPostInput">
                                  <option value="">Aucun lien</option>
                                  <?php foreach($allPostsForSelect as $p): ?>
                                      <option value="<?= $p['id'] ?>" <?= ($editStory && $editStory['post_id'] == $p['id']) ? 'selected' : '' ?>>
                                          <?= htmlspecialchars($p['title']) ?>
                                      </option>
                                  <?php endforeach; ?>
                              </select>
                          </div>

                          <div class="form-group">
                              <label>Libellé du bouton</label>
                              <input type="text" name="cta_label" id="storyCtaInput" value="<?= $editStory ? htmlspecialchars($editStory['cta_label']) : 'Voir le blog' ?>" maxlength="80">
                          </div>

                          <div class="form-group">
                              <label>Date de début *</label>
                              <input type="datetime-local" name="starts_at" id="storyStartsInput" value="<?= htmlspecialchars($startsValue) ?>">
                              <div id="storyDateError" style="color: #ef4444; font-size: 12px; margin-top: 5px; display: none;"></div>
                          </div>

                          <div class="form-group">
                              <label>Date de fin *</label>
                              <input type="datetime-local" name="expires_at" id="storyExpiresInput" value="<?= htmlspecialchars($expiresValue) ?>">
                          </div>

                          <div class="form-group">
                              <label>Statut</label>
                              <select name="status">
                                  <option value="published" <?= ($editStory && $editStory['status'] == 'published') ? 'selected' : '' ?>>Publié</option>
                                  <option value="draft" <?= ($editStory && $editStory['status'] == 'draft') ? 'selected' : '' ?>>Brouillon</option>
                              </select>
                          </div>

                          <div class="form-group">
                              <label>Image story</label>
                              <input type="file" name="media_image" accept="image/*">
                              <?php if($editStory && $editStory['media_image']): ?>
                                  <br><small>Image actuelle: <?= htmlspecialchars($editStory['media_image']) ?></small>
                              <?php endif; ?>
                          </div>
                      </div>

                      <div style="margin-top: 20px; text-align: right;">
                          <button type="submit" class="btn" style="padding: 12px 24px; font-size: 14px;"><i class="fas fa-save"></i> Enregistrer</button>
                      </div>
                  </form>
              </div>
          </div>

      <?php else: ?>
          <div class="table-section">
              <div class="table-header" style="flex-wrap: wrap; gap: 15px;">
                  <div>Toutes les stories</div>
                  <div style="font-size: 13px; color: var(--text-muted);">
                      <?= $activeStoriesTotal ?> active(s) · <?= $storyViewsTotal ?> vue(s)
                  </div>
              </div>
              <table>
                  <thead>
                      <tr>
                          <th>STORY</th>
                          <th>BLOG LIÉ</th>
                          <th>PÉRIODE</th>
                          <th>VUES</th>
                          <th>STATUT</th>
                          <th>ACTIONS</th>
                      </tr>
                  </thead>
                  <tbody>
                      <?php if (empty($storiesList)): ?>
                          <tr><td colspan="6" style="text-align:center;">Aucune story trouvée.</td></tr>
                      <?php else: foreach($storiesList as $s): ?>
                          <?php
                          $now = time();
                          $isDraft = $s['status'] === 'draft';
                          $isExpired = strtotime($s['expires_at']) < $now;
                          $isScheduled = strtotime($s['starts_at']) > $now;
                          if ($isDraft) {
                              $storyStatusLabel = 'BROUILLON';
                              $storyStatusClass = 'status-draft';
                          } elseif ($isExpired) {
                              $storyStatusLabel = 'EXPIRÉE';
                              $storyStatusClass = 'status-expired';
                          } elseif ($isScheduled) {
                              $storyStatusLabel = 'PROGRAMMÉE';
                              $storyStatusClass = 'status-scheduled';
                          } else {
                              $storyStatusLabel = 'ACTIVE';
                              $storyStatusClass = '';
                          }
                          ?>
                          <tr>
                              <td>
                                  <div class="story-cell">
                                      <?php if (!empty($s['media_image'])): ?>
                                          <img class="story-thumb" src="../uploads/stories/<?= htmlspecialchars($s['media_image']) ?>" alt="<?= htmlspecialchars($s['title']) ?>">
                                      <?php else: ?>
                                          <span class="story-thumb"><i class="fas fa-circle-play"></i></span>
                                      <?php endif; ?>
                                      <span>
                                          <span class="story-title"><?= htmlspecialchars($s['title']) ?></span>
                                          <small class="story-desc"><?= htmlspecialchars($s['content'] ?? '') ?></small>
                                      </span>
                                  </div>
                              </td>
                              <td><?= htmlspecialchars($s['post_title'] ?? 'Aucun lien') ?></td>
                              <td style="white-space: nowrap;">
                                  <?= date('d/m/Y H:i', strtotime($s['starts_at'])) ?><br>
                                  <small style="color: var(--text-muted);">jusqu'au <?= date('d/m/Y H:i', strtotime($s['expires_at'])) ?></small>
                              </td>
                              <td><strong><?= (int) $s['views_count'] ?></strong></td>
                              <td>
                                  <span class="status-badge <?= $storyStatusClass ?>"><?= $storyStatusLabel ?></span>
                              </td>
                              <td class="actions">
                                  <a href="backoffice.php?page=stories&action=edit&id=<?= $s['id'] ?>" class="action-btn btn-edit"><i class="fas fa-edit"></i></a>
                                  <form method="POST" action="backoffice.php?page=stories" style="display:inline;" onsubmit="return confirm('Supprimer cette story ?');">
                                      <input type="hidden" name="action" value="delete">
                                      <input type="hidden" name="id" value="<?= $s['id'] ?>">
                                      <button type="submit" class="action-btn btn-delete"><i class="fas fa-trash"></i></button>
                                  </form>
                              </td>
                          </tr>
                      <?php endforeach; endif; ?>
                  </tbody>
              </table>
          </div>
      <?php endif; ?>

    <?php elseif ($page === 'comments'): ?>
      <!-- ============================================== -->
      <!-- VUE : COMMENTAIRES                             -->
      <!-- ============================================== -->
      <div class="page-header">
        <div>
          <h1 class="page-title">Gestion des Commentaires</h1>
          <p class="page-subtitle">Ajouter, modifier ou supprimer des commentaires.</p>
        </div>
      </div>

      <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 30px;">
        <!-- Formulaire Ajouter / Modifier -->
        <div class="table-section">
          <?php 
          $editCommentMode = false; $editComment = null;
          if(isset($_GET['edit'])) {
              $editComment = $commentController->getComment($_GET['edit']);
              if($editComment) $editCommentMode = true;
          }
          ?>
          <div class="table-header"><?= $editCommentMode ? "Modifier le Commentaire" : "Nouveau Commentaire" ?></div>
          <div style="padding: 24px;">
              <form method="POST" action="backoffice.php?page=comments" id="commentForm">
                  <input type="hidden" name="action" value="<?= $editCommentMode ? 'edit' : 'add' ?>">
                  <?php if($editCommentMode): ?>
                      <input type="hidden" name="id" value="<?= $editComment['id'] ?>">
                  <?php endif; ?>
                  
                  <?php if(!$editCommentMode): ?>
                  <div class="form-group">
                      <label>Blog associé *</label>
                      <select name="post_id" id="commentPostInput">
                          <option value="">Choisir un blog...</option>
                          <?php foreach($postsList as $p): ?>
                              <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['title']) ?></option>
                          <?php endforeach; ?>
                      </select>
                      <div id="commentPostError" style="color: #ef4444; font-size: 12px; margin-top: 5px; display: none;"></div>
                  </div>
                  <?php else: ?>
                  <div class="form-group">
                      <label>Blog associé</label>
                      <input type="text" value="<?= htmlspecialchars($editComment['post_title']) ?>" disabled style="background: #f3f4f6;">
                  </div>
                  <?php endif; ?>

                  <div class="form-group">
                      <label>Nom d'utilisateur *</label>
                      <input type="text" name="user_name" id="commentUserInput" value="<?= $editCommentMode ? htmlspecialchars($editComment['user_name']) : '' ?>" placeholder="Ex: Jean Dupont">
                      <div id="commentUserError" style="color: #ef4444; font-size: 12px; margin-top: 5px; display: none;"></div>
                  </div>

                  <div class="form-group">
                      <label>Contenu du commentaire *</label>
                      <textarea name="content" id="commentContentInput" placeholder="Écrivez votre commentaire..." style="min-height: 100px;"><?= $editCommentMode ? htmlspecialchars($editComment['content']) : '' ?></textarea>
                      <div id="commentContentError" style="color: #ef4444; font-size: 12px; margin-top: 5px; display: none;"></div>
                  </div>

                  <button type="submit" class="btn">
                      <i class="fas <?= $editCommentMode ? 'fa-save' : 'fa-plus' ?>"></i>
                      <?= $editCommentMode ? 'Enregistrer' : 'Ajouter' ?>
                  </button>
                  <?php if($editCommentMode): ?>
                      <a href="backoffice.php?page=comments" class="btn btn-light" style="margin-left:10px;">Annuler</a>
                  <?php endif; ?>
              </form>
          </div>
        </div>

        <!-- Liste des commentaires -->
        <div class="table-section">
          <div class="table-header" style="flex-wrap: wrap; gap: 15px;">
            <div>Liste des Commentaires (<?= $commentsTotal ?>)</div>
            <div style="display: flex; gap: 10px; align-items: center;">
              <!-- Recherche dynamique -->
              <div style="position: relative;">
                <i class="fas fa-search" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 12px;"></i>
                <input type="text" id="commentSearchInput" placeholder="Rechercher un commentaire..." 
                       style="padding: 8px 12px 8px 32px; border-radius: 6px; border: 1px solid var(--border); outline: none; font-family: 'DM Sans'; font-size: 13px; width: 220px; transition: border-color 0.2s, box-shadow 0.2s;"
                       onfocus="this.style.borderColor='var(--blue)'; this.style.boxShadow='0 0 0 3px rgba(45,121,255,0.1)'"
                       onblur="this.style.borderColor='var(--border)'; this.style.boxShadow='none'">
              </div>
              <!-- Tri par date -->
              <button type="button" id="commentSortDateBtn" class="btn btn-light" style="padding: 8px 14px; font-size: 13px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;" title="Trier par date">
                <i class="fas fa-calendar-alt"></i> Date
                <i class="fas fa-sort-down" id="commentSortIcon"></i>
              </button>
            </div>
          </div>
          <table id="commentsTable">
            <thead>
              <tr>
                <th>UTILISATEUR</th>
                <th>COMMENTAIRE</th>
                <th>BLOG</th>
                <th>DATE</th>
                <th>ACTIONS</th>
              </tr>
            </thead>
            <tbody id="commentsTableBody">
              <?php if(empty($commentsList)): ?>
                <tr class="no-comments-row"><td colspan="5" style="text-align:center;">Aucun commentaire.</td></tr>
              <?php else: foreach($commentsList as $c): ?>
              <tr class="comment-row" data-date="<?= $c['created_at'] ?>">
                <td><strong><?= htmlspecialchars($c['user_name']) ?></strong></td>
                <td style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"><?= htmlspecialchars($c['content']) ?></td>
                <td><span style="color: var(--blue); font-weight: 500;"><?= htmlspecialchars($c['post_title'] ?? 'Post supprimé') ?></span></td>
                <td style="white-space: nowrap;"><?= date('d/m/Y H:i', strtotime($c['created_at'])) ?></td>
                <td class="actions">
                  <a href="backoffice.php?page=comments&edit=<?= $c['id'] ?>" class="action-btn btn-edit"><i class="fas fa-edit"></i></a>
                  <form method="POST" action="backoffice.php?page=comments" style="display:inline;" onsubmit="return confirm('Supprimer ce commentaire ?');">
                      <input type="hidden" name="action" value="delete">
                      <input type="hidden" name="id" value="<?= $c['id'] ?>">
                      <button type="submit" class="action-btn btn-delete"><i class="fas fa-trash"></i></button>
                  </form>
                </td>
              </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
          <!-- Message quand aucun résultat de recherche -->
          <div id="noSearchResults" style="display: none; text-align: center; padding: 24px; color: var(--text-muted); font-size: 14px;">
            <i class="fas fa-search" style="font-size: 24px; margin-bottom: 8px; display: block; opacity: 0.5;"></i>
            Aucun commentaire ne correspond à votre recherche.
          </div>
        </div>
      </div>

    <?php else: ?>
      <div class="page-header">
        <h1 class="page-title">Page non trouvée</h1>
      </div>
    <?php endif; ?>

  </main>

  <!-- Modal pour voir les likes et commentaires -->
  <div id="detailsModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;">
    <div style="background: white; border-radius: 8px; max-width: 600px; width: 90%; max-height: 80vh; overflow-y: auto; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
      <div style="padding: 24px; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; background: white;">
        <h2 id="modalTitle" style="font-size: 18px; font-weight: 700; margin: 0;">Détails du Post</h2>
        <button onclick="closeModal()" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #6b7280;">&times;</button>
      </div>
      
      <div style="padding: 24px;">
        <!-- Liked Count -->
        <div style="margin-bottom: 24px;">
          <h3 style="font-size: 14px; font-weight: 700; color: #111827; margin-bottom: 8px;"><i class="fas fa-heart" style="color: #ef4444; margin-right: 8px;"></i> Likes</h3>
          <p id="likesCount" style="font-size: 16px; color: #2d79ff; font-weight: 600;">Chargement...</p>
        </div>

        <!-- Comments Section -->
        <div>
          <h3 style="font-size: 14px; font-weight: 700; color: #111827; margin-bottom: 12px;"><i class="fas fa-comments" style="color: #2d79ff; margin-right: 8px;"></i> Commentaires (<span id="commentsCount">0</span>)</h3>
          <div id="commentsList" style="display: flex; flex-direction: column; gap: 12px;">
            <p style="color: #6b7280;">Chargement des commentaires...</p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="assets/js/validation.js?v=<?= time() ?>"></script>
  <script>
    function showPostDetails(postId) {
      const modal = document.getElementById('detailsModal');
      modal.style.display = 'flex';
      
      // Charger les données
      const xhr = new XMLHttpRequest();
      xhr.open('GET', 'get_post_details.php?id=' + postId, true);
      xhr.onload = function() {
        if (xhr.status === 200) {
          try {
            const data = JSON.parse(xhr.responseText);
            
            // Mettre à jour le titre
            document.getElementById('modalTitle').textContent = 'Détails du Post: ' + data.title;
            
            // Afficher les likes
            document.getElementById('likesCount').textContent = data.likes_count + ' personnes ont aimé ce post';
            
            // Afficher les commentaires
            const commentsList = document.getElementById('commentsList');
            if (data.comments.length === 0) {
              commentsList.innerHTML = '<p style="color: #6b7280;">Aucun commentaire pour le moment.</p>';
            } else {
              commentsList.innerHTML = data.comments.map(comment => `
                <div style="background: #f9fafb; padding: 12px; border-radius: 6px; border-left: 3px solid #2d79ff;">
                  <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                    <strong style="color: #111827;">${escapeHtml(comment.user_name)}</strong>
                    <small style="color: #6b7280;">${new Date(comment.created_at).toLocaleDateString('fr-FR', {year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit'})}</small>
                  </div>
                  <p style="margin: 0; color: #374151; font-size: 13px;">${escapeHtml(comment.content)}</p>
                </div>
              `).join('');
            }
            
            // Mettre à jour le compteur de commentaires
            document.getElementById('commentsCount').textContent = data.comments.length;
          } catch(e) {
            console.error('Erreur lors du parsing JSON:', e);
            commentsList.innerHTML = '<p style="color: #ef4444;">Erreur lors du chargement des données.</p>';
          }
        } else {
          document.getElementById('commentsList').innerHTML = '<p style="color: #ef4444;">Erreur lors du chargement des données.</p>';
        }
      };
      xhr.onerror = function() {
        document.getElementById('commentsList').innerHTML = '<p style="color: #ef4444;">Erreur de connexion.</p>';
      };
      xhr.send();
    }

    function closeModal() {
      document.getElementById('detailsModal').style.display = 'none';
    }

    // Fermer la modal en cliquant en dehors
    document.getElementById('detailsModal').addEventListener('click', function(e) {
      if (e.target === this) {
        closeModal();
      }
    });

    // Fonction pour échapper les caractères HTML
    function escapeHtml(unsafe) {
      return unsafe
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
    }
  </script>
</body>
</html>
