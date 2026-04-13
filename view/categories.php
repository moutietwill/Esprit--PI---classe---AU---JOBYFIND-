<?php
require_once __DIR__ . '/../controller/CategoryController.php';

$controller = new CategoryController();

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add' && !empty($_POST['name'])) {
            $controller->addCategory($_POST['name']);
        } elseif ($_POST['action'] === 'edit' && !empty($_POST['id']) && !empty($_POST['name'])) {
            $controller->updateCategory($_POST['id'], $_POST['name']);
        } elseif ($_POST['action'] === 'delete' && !empty($_POST['id'])) {
            $controller->deleteCategory($_POST['id']);
        }
        
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
            exit;
        }

        header("Location: categories.php");
        exit;
    }
}

$categories = $controller->getCategories();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Catégories - Admin</title>
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
    .user-email { font-size: 10px; color: #9ca3af; }
    /* Main Content */
    .main-content { margin-left: var(--sidebar-w); padding: 32px 40px; flex: 1; }
    .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px; }
    .page-title { font-size: 24px; font-weight: 700; margin-bottom: 8px; }
    .page-subtitle { color: var(--text-muted); font-size: 14px; }
    .btn { background: var(--blue); color: #fff; padding: 10px 16px; border-radius: var(--radius); text-decoration: none; font-size: 13px; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; cursor: pointer; border: none; }
    .btn-danger { background: #ef4444; }
    /* Table Section */
    .table-section { background: var(--surface); border-radius: var(--radius); box-shadow: 0 1px 2px rgba(0,0,0,0.05); overflow: hidden; margin-bottom: 30px; }
    .table-header { padding: 20px 24px; font-size: 16px; font-weight: 700; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; }
    table { width: 100%; border-collapse: collapse; }
    th { text-align: left; padding: 12px 24px; font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; background: #f9fafb; border-bottom: 1px solid var(--border); }
    td { padding: 16px 24px; font-size: 13px; font-weight: 500; border-bottom: 1px solid var(--border); }
    tr:last-child td { border-bottom: none; }
    .actions { display: flex; gap: 8px; }
    .action-btn { padding: 6px 10px; border-radius: 4px; font-size: 12px; font-weight: 600; text-decoration: none; cursor: pointer; border: none; }
    .btn-edit { background: #eff6ff; color: var(--blue); }
    .btn-delete { background: #fef2f2; color: #ef4444; }
    /* Forms */
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; font-size: 12px; font-weight: 600; margin-bottom: 6px; color: var(--text-muted); }
    .form-group input { width: 100%; padding: 10px 14px; border: 1px solid var(--border); border-radius: 6px; font-family: 'DM Sans'; font-size: 13px; outline: none; }
    .form-group input:focus { border-color: var(--blue); }
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
      <a href="backoffice.php" class="nav-link">
        <i class="fas fa-chart-line"></i> Vue d'ensemble
      </a>
    </div>
    <div class="nav-section">
      <div class="nav-label">GESTION</div>
      <a href="categories.php" class="nav-link active">
        <i class="fas fa-folder"></i> Catégories
      </a>
      <a href="posts.php" class="nav-link">
        <i class="fas fa-book"></i> Formations
      </a>
      <a href="#" class="nav-link">
        <i class="fas fa-users"></i> Inscriptions
      </a>
    </div>
    <div class="user-profile">
      <div class="user-avatar">SA</div>
      <div class="user-info"><span class="user-name">Super Admin</span><span class="user-email">admin@projetweb.tn</span></div>
    </div>
  </aside>

  <!-- MAIN CONTENT -->
  <main class="main-content">
    <div class="page-header">
      <div>
        <h1 class="page-title">Gestion des Catégories</h1>
        <p class="page-subtitle">Ajouter, modifier ou supprimer des catégories.</p>
      </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 30px;">
      <!-- FORM -->
      <div class="table-section">
        <?php 
        $editMode = false;
        $editCat = null;
        if(isset($_GET['edit'])) {
            $editCat = $controller->getCategory($_GET['edit']);
            if($editCat) $editMode = true;
        }
        ?>
        <div class="table-header"><?= $editMode ? "Modifier la Catégorie" : "Ajouter une Catégorie" ?></div>
        <div style="padding: 24px;">
            <form method="POST" id="categoryForm">
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
                    <a href="categories.php" class="btn" style="background:#e5e7eb; color:#374151; margin-left:10px;">Annuler</a>
                <?php endif; ?>
            </form>
        </div>
      </div>

      <!-- LIST -->
      <div class="table-section">
        <div class="table-header">Liste des Catégories</div>
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>NOM</th>
              <th>ACTIONS</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($categories as $cat): ?>
            <tr>
              <td>#<?= $cat['id'] ?></td>
              <td><?= htmlspecialchars($cat['name']) ?></td>
              <td class="actions">
                <a href="categories.php?edit=<?= $cat['id'] ?>" class="action-btn btn-edit"><i class="fas fa-edit"></i> Modifier</a>
                <form method="POST" style="display:inline;" onsubmit="return confirm('Supprimer cette catégorie ?');">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                    <button type="submit" class="action-btn btn-delete"><i class="fas fa-trash"></i></button>
                </form>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>

  <script src="assets/js/validation.js"></script>
</body>
</html>