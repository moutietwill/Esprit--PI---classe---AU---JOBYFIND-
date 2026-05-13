<?php
require_once __DIR__ . '/../controller/BlogController.php';
require_once __DIR__ . '/../controller/CategoryController.php';

$blogController = new BlogController();
$categoryController = new CategoryController();

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $cover_image = isset($_FILES['cover_image']) ? $_FILES['cover_image'] : null;

        if ($_POST['action'] === 'add') {
            $post = new PostModel(
                $_POST['title'],
                $_POST['content'],
                $_POST['category_id'],
                null,
                $_POST['status'],
                $_POST['excerpt'] ?? '',
                $_POST['instructor'] ?? '',
                $_POST['price'] ?? 0,
                $_POST['duration_hours'] ?? 0
            );
            $blogController->AjouterPost($post, $cover_image);
        } elseif ($_POST['action'] === 'edit' && !empty($_POST['id'])) {
            $post = new PostModel(
                $_POST['title'],
                $_POST['content'],
                $_POST['category_id'],
                $_POST['old_cover_image'] ?? null,
                $_POST['status'],
                $_POST['excerpt'] ?? '',
                $_POST['instructor'] ?? '',
                $_POST['price'] ?? 0,
                $_POST['duration_hours'] ?? 0
            );
            $blogController->ModifierPost($post, $_POST['id'], $cover_image);
        }
 elseif ($_POST['action'] === 'delete' && !empty($_POST['id'])) {
            $blogController->SupprimerPost($_POST['id']);
        }
        
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
            exit;
        }

        header("Location: posts.php");
        exit;
    }
}

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$postToEdit = null;

if ($action === 'edit' && isset($_GET['id'])) {
    $postToEdit = $blogController->RecupererPost($_GET['id']);
}

$posts = $blogController->AfficherPosts();
$categories = $categoryController->getCategories();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Formations - Admin</title>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    :root { --blue: #2d79ff; --navy: #192135; --bg: #f4f6fa; --surface: #ffffff; --text-main: #111827; --text-muted: #6b7280; --border: #e5e7eb; --radius: 8px; --sidebar-w: 240px; }
    body { font-family: 'DM Sans', sans-serif; background: var(--bg); display: flex; min-height: 100vh; color: var(--text-main); }
    /* Sidebar */
    .sidebar { width: var(--sidebar-w); background: var(--navy); display: flex; flex-direction: column; position: fixed; left: 0; top: 0; bottom: 0; color: #fff; }
    .brand { display: flex; align-items: center; gap: 12px; padding: 24px; font-size: 18px; font-weight: 700; color: #fff; text-decoration: none; }
    .brand i { color: var(--blue); font-size: 20px; }
    .nav-section { padding: 16px 20px; }
    .nav-label { font-size: 10px; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 12px; }
    .nav-link { display: flex; align-items: center; gap: 12px; padding: 10px 16px; border-radius: 8px; color: #9ca3af; text-decoration: none; font-size: 13px; font-weight: 500; margin-bottom: 4px; transition: all 0.2s; }
    .nav-link:hover { color: #fff; background: rgba(255,255,255,0.05); }
    .nav-link.active { background: var(--blue); color: #fff; }
    .user-profile { margin-top: auto; padding: 12px; margin: 20px; background: #252d43; border-radius: 8px; display: flex; align-items: center; gap: 10px; }
    .user-avatar { width: 32px; height: 32px; background: var(--blue); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 12px; }
    .user-info { display: flex; flex-direction: column; }
    .user-name { font-size: 12px; font-weight: 700; }
    /* Main Content */
    .main-content { margin-left: var(--sidebar-w); padding: 32px 40px; flex: 1; }
    .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px; }
    .page-title { font-size: 24px; font-weight: 700; margin-bottom: 8px; }
    .page-subtitle { color: var(--text-muted); font-size: 14px; }
    .btn { background: var(--blue); color: #fff; padding: 10px 16px; border-radius: var(--radius); text-decoration: none; font-size: 13px; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; cursor: pointer; border: none; }
    /* Table Section */
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
    /* Badge */
    .status-badge { display: inline-block; padding: 4px 10px; border-radius: 4px; font-size: 10px; font-weight: 700; background: #dcfce7; color: #166534; text-transform: uppercase; }
    .status-draft { background: #f3f4f6; color: #4b5563; }
    /* Forms */
    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    .form-group { margin-bottom: 15px; }
    .form-group.full { grid-column: span 2; }
    .form-group label { display: block; font-size: 12px; font-weight: 600; margin-bottom: 6px; color: var(--text-muted); }
    .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 10px 14px; border: 1px solid var(--border); border-radius: 6px; font-family: 'DM Sans'; font-size: 13px; outline: none; }
    .form-group textarea { resize: vertical; min-height: 100px; }
  </style>
</head>
<body>

  <aside class="sidebar">
    <a href="frontoffice.php" class="brand">
      <i class="fas fa-play-circle"></i> JobyFind
    </a>
    <div class="nav-section">
      <div class="nav-label">TABLEAU DE BORD</div>
      <a href="backoffice.php" class="nav-link">
        <i class="fas fa-chart-line"></i> Vue d'ensemble
      </a>
    </div>
    <div class="nav-section">
      <div class="nav-label">GESTION</div>
      <a href="categories.php" class="nav-link">
        <i class="fas fa-folder"></i> Catégories
      </a>
      <a href="posts.php" class="nav-link active">
        <i class="fas fa-book"></i> Formations
      </a>
      <a href="#" class="nav-link">
        <i class="fas fa-users"></i> Inscriptions
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

  <main class="main-content">
    <div class="page-header">
      <div>
        <h1 class="page-title">Gestion des Formations</h1>
        <p class="page-subtitle">Gérez le catalogue de vos cours en ligne.</p>
      </div>
      <?php if ($action === 'list'): ?>
        <a href="posts.php?action=add" class="btn"><i class="fas fa-plus"></i> Nouvelle Publication</a>
      <?php endif; ?>
    </div>

    <?php if ($action === 'add' || $action === 'edit'): ?>
        <div class="table-section">
            <div class="table-header">
                <?= $action === 'edit' ? "Modifier la Formation" : "Créer une Formation" ?>
                <a href="posts.php" class="btn" style="background:#e5e7eb; color:#374151;"><i class="fas fa-arrow-left"></i> Retour</a>
            </div>
            <div style="padding: 24px;">
                <form method="POST" enctype="multipart/form-data" id="formationForm">
                    <input type="hidden" name="action" value="<?= $action ?>">
                    <?php if ($action === 'edit'): ?>
                        <input type="hidden" name="id" value="<?= $postToEdit['id'] ?>">
                        <input type="hidden" name="old_cover_image" value="<?= $postToEdit['cover_image'] ?>">
                    <?php endif; ?>

                    <div class="form-grid">
                        <div class="form-group full">
                            <label>Titre de la formation *</label>
                            <input type="text" name="title" id="titleInput" value="<?= $postToEdit ? htmlspecialchars($postToEdit['title']) : '' ?>">
                            <div id="titleError" style="color: #ef4444; font-size: 12px; margin-top: 5px; display: none;"></div>
                        </div>
                        
                        <div class="form-group">
                            <label>Catégorie *</label>
                            <select name="category_id" id="categoryInput">
                                <option value="">Choisir une catégorie...</option>
                                <?php foreach($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" <?= ($postToEdit && $postToEdit['category_id'] == $cat['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div id="categoryError" style="color: #ef4444; font-size: 12px; margin-top: 5px; display: none;"></div>
                        </div>

                        <div class="form-group full">
                            <label>Extrait (Excerpt) *</label>
                            <input type="text" name="excerpt" value="<?= $postToEdit ? htmlspecialchars($postToEdit['excerpt']) : '' ?>">
                        </div>

                        <div class="form-group">
                            <label>Auteur *</label>
                            <input type="text" name="instructor" value="<?= $postToEdit ? htmlspecialchars($postToEdit['instructor']) : '' ?>">
                        </div>

                        <div class="form-group">
                            <label>Prix (TND) *</label>
                            <input type="number" name="price" step="0.01" value="<?= $postToEdit ? htmlspecialchars($postToEdit['price']) : '0' ?>">
                        </div>

                        <div class="form-group">
                            <label>Estimation Lecture (min) *</label>
                            <input type="number" name="duration_hours" value="<?= $postToEdit ? htmlspecialchars($postToEdit['duration_hours']) : '0' ?>">
                        </div>

                        <div class="form-group full">
                            <label>Description *</label>
                            <textarea name="content" id="contentInput"><?= $postToEdit ? htmlspecialchars($postToEdit['content']) : '' ?></textarea>
                            <div id="contentError" style="color: #ef4444; font-size: 12px; margin-top: 5px; display: none;"></div>
                        </div>

                        <div class="form-group">
                            <label>Statut</label>
                            <select name="status">
                                <option value="published" <?= ($postToEdit && $postToEdit['status'] == 'published') ? 'selected' : '' ?>>Publié</option>
                                <option value="draft" <?= ($postToEdit && $postToEdit['status'] == 'draft') ? 'selected' : '' ?>>Brouillon</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Image de couverture</label>
                            <input type="file" name="cover_image" accept="image/*">
                            <?php if($postToEdit && $postToEdit['cover_image']): ?>
                                <br><small>Image actuelle: <?= htmlspecialchars($postToEdit['cover_image']) ?></small>
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
            <div class="table-header">Toutes les formations</div>
            <table>
                <thead>
                    <tr>
                        <th>FORMATION</th>
                        <th>CATÉGORIE</th>
                        <th>PRIX</th>
                        <th>STATUT</th>
                        <th>ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($posts)): ?>
                        <tr><td colspan="5" style="text-align:center;">Aucune formation trouvée.</td></tr>
                    <?php else: ?>
                        <?php foreach($posts as $post): ?>
                        <tr>
                            <td style="display: flex; align-items: center; gap: 12px; border-bottom: none; padding-top: 10px; padding-bottom: 10px;">
                                <?php if (isset($post['cover_image']) && $post['cover_image']): ?>
                                    <img src="../uploads/<?= htmlspecialchars($post['cover_image']) ?>" style="width: 48px; height: 48px; border-radius: 6px; object-fit: cover; flex-shrink: 0;">
                                <?php else: ?>
                                    <div style="width: 48px; height: 48px; border-radius: 6px; background: #e5e7eb; display: flex; align-items: center; justify-content: center; flex-shrink: 0;"><i class="fas fa-book" style="color:#9ca3af;"></i></div>
                                <?php endif; ?>
                                <div>
                                    <strong style="color:var(--text-main); display:block;"><?= htmlspecialchars($post['title']) ?></strong>
                                    <small style="color:var(--text-muted);"><?= htmlspecialchars($post['instructor'] ?? 'Inconnu') ?></small>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($post['category'] ?? 'N/A') ?></td>
                            <td><?= isset($post['price']) ? htmlspecialchars($post['price']) . ' TND' : 'Gratuit' ?></td>
                            <td>
                                <span class="status-badge <?= $post['status'] == 'draft' ? 'status-draft' : '' ?>">
                                    <?= $post['status'] == 'published' ? 'PUBLIÉ' : 'BROUILLON' ?>
                                </span>
                            </td>
                            <td class="actions">
                                <a href="posts.php?action=edit&id=<?= $post['id'] ?>" class="action-btn btn-edit"><i class="fas fa-edit"></i></a>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('Confirmer la suppression de cette formation ?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $post['id'] ?>">
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
  </main>

  <script src="assets/js/validation.js"></script>
</body>
</html>
