<?php
require_once __DIR__ . '/../controller/BlogController.php';
require_once __DIR__ . '/../controller/CategoryController.php';
require_once __DIR__ . '/../controller/CommentController.php';
require_once __DIR__ . '/../mailer.php';

$blogController = new BlogController();
$catController = new CategoryController();
$commentController = new CommentController();

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
    .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; margin-bottom: 32px; }
    .stat-card { background: var(--surface); border-radius: var(--radius); padding: 24px; display: flex; align-items: center; gap: 20px; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
    .stat-icon { font-size: 28px; width: 48px; height: 48px; display: flex; align-items:center; justify-content:center; border-radius: 8px; }
    .stat-icon.blue { color: var(--blue); background: #eff6ff; }
    .stat-icon.green { color: #10b981; background: #ecfdf5; }
    .stat-icon.orange { color: #f59e0b; background: #fffbeb; }
    .stat-info { display: flex; flex-direction: column; }
    .stat-label { font-size: 12px; color: var(--text-muted); font-weight: 500; margin-bottom: 2px; }
    .stat-value { font-size: 24px; font-weight: 700; color: var(--text-main); line-height: 1; }

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
      <a href="backoffice.php?page=comments" class="nav-link <?= $page === 'comments' ? 'active' : '' ?>">
        <i class="fas fa-comments"></i> Commentaires
        <span class="badge-red"><?= $commentsTotal ?></span>
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
          <div class="stat-icon orange"><i class="fas fa-comments"></i></div>
          <div class="stat-info">
            <span class="stat-label">Commentaires</span>
            <span class="stat-value"><?= $commentsTotal ?></span>
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
          <div class="table-section">
              <?php
              $editPost = null;
              if ($action === 'edit' && isset($_GET['id'])) {
                  $editPost = $blogController->RecupererPost($_GET['id']);
              }
              ?>
              <div class="table-header">
                  <?= $action === 'edit' ? "Modifier la Formation" : "Créer une Formation" ?>
                  <a href="backoffice.php?page=posts" class="btn btn-light"><i class="fas fa-arrow-left"></i> Retour</a>
              </div>
              <div style="padding: 24px;">
                  <form method="POST" action="backoffice.php?page=posts" enctype="multipart/form-data" id="formationForm">
                      <input type="hidden" name="action" value="<?= $action ?>">
                      <?php if ($action === 'edit'): ?>
                          <input type="hidden" name="id" value="<?= $editPost['id'] ?>">
                          <input type="hidden" name="old_cover_image" value="<?= $editPost['cover_image'] ?>">
                      <?php endif; ?>

                      <div class="form-grid">
                          <div class="form-group full">
                              <label>Titre de la formation *</label>
                              <input type="text" name="title" id="titleInput" value="<?= $editPost ? htmlspecialchars($editPost['title']) : '' ?>">
                              <div id="titleError" style="color: #ef4444; font-size: 12px; margin-top: 5px; display: none;"></div>
                          </div>
                          
                          <div class="form-group">
                              <label>Catégorie *</label>
                              <select name="category_id" id="categoryInput">
                                  <option value="">Choisir une catégorie...</option>
                                  <?php foreach($categoriesList as $cat): ?>
                                      <option value="<?= $cat['id'] ?>" <?= ($editPost && $editPost['category_id'] == $cat['id']) ? 'selected' : '' ?>>
                                          <?= htmlspecialchars($cat['name']) ?>
                                      </option>
                                  <?php endforeach; ?>
                              </select>
                              <div id="categoryError" style="color: #ef4444; font-size: 12px; margin-top: 5px; display: none;"></div>
                          </div>

                          <div class="form-group full">
                              <label>Description *</label>
                              <textarea name="content" id="contentInput"><?= $editPost ? htmlspecialchars($editPost['content']) : '' ?></textarea>
                              <div id="contentError" style="color: #ef4444; font-size: 12px; margin-top: 5px; display: none;"></div>
                          </div>

                          <div class="form-group">
                              <label>Statut</label>
                              <select name="status">
                                  <option value="published" <?= ($editPost && $editPost['status'] == 'published') ? 'selected' : '' ?>>Publié</option>
                                  <option value="draft" <?= ($editPost && $editPost['status'] == 'draft') ? 'selected' : '' ?>>Brouillon</option>
                              </select>
                          </div>

                          <div class="form-group">
                              <label>Image de couverture</label>
                              <input type="file" name="cover_image" accept="image/*">
                              <?php if($editPost && $editPost['cover_image']): ?>
                                  <br><small>Image actuelle: <?= htmlspecialchars($editPost['cover_image']) ?></small>
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
