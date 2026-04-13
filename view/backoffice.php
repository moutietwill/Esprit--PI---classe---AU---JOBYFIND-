<?php
require_once __DIR__ . '/../controller/BlogController.php';
require_once __DIR__ . '/../controller/CategoryController.php';

$blogController = new BlogController();
$catController = new CategoryController();

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
    }
}

// Données Globales
$categoriesList = $catController->getCategories();
$postsList = $blogController->AfficherPosts();
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
      <a href="#" class="nav-link">
        <i class="fas fa-users"></i> Inscriptions
        <span class="badge-red">0</span>
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
          <div class="stat-icon orange"><i class="fas fa-users"></i></div>
          <div class="stat-info">
            <span class="stat-label">Inscriptions</span>
            <span class="stat-value">0</span>
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
          <a href="backoffice.php?page=posts&action=add" class="btn"><i class="fas fa-plus"></i> Nouvelle Formation</a>
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
              <div class="table-header">Toutes les blogs</div>
              <table>
                  <thead>
                      <tr>
                          <th>BLOG</th>
                          <th>CATÉGORIE</th>
                          
                          <th>STATUT</th>
                          <th>ACTIONS</th>
                      </tr>
                  </thead>
                  <tbody>
                      <?php if (empty($postsList)): ?>
                          <tr><td colspan="4" style="text-align:center;">Aucune blog trouvée.</td></tr>
                      <?php else: foreach($postsList as $p): ?>
                          <tr>
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
          </div>
      <?php endif; ?>

    <?php else: ?>
      <div class="page-header">
        <h1 class="page-title">Page non trouvée</h1>
      </div>
    <?php endif; ?>

  </main>
  <script src="assets/js/validation.js"></script>
</body>
</html>
