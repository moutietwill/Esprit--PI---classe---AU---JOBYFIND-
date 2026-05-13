<?php
require_once(__DIR__ . '/../../config/session.php');
startAppSession();

require_once(__DIR__ . '/../../config/Database.php');
require_once(__DIR__ . '/../../Model/reponse.php');
require_once(__DIR__ . '/../../Controller/QuizzController.php');

$role = $_SESSION['role'] ?? 'Guest';
$isTutor = ($role === 'Tutor' || $role === 'Admin');

if (!$isTutor) {
    header("Location: quiz-admin.php");
    exit();
}

$db = Database::getInstance()->getConnection();
$listQuestions = $db->query("SELECT id_question, enonce FROM question ORDER BY id_question DESC")->fetchAll(PDO::FETCH_ASSOC);

// 1. Delete
if (isset($_GET['delete_id'])) {
    $stmt = $db->prepare("DELETE FROM reponse WHERE id_reponse = :id");
    $stmt->execute(['id' => $_GET['delete_id']]);
    header("Location: reponse-admin.php?msg=deleted");
    exit();
}

// 2. Save/Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_save'])) {
    $contenu = $_POST['contenu'];
    $estCorrecte = isset($_POST['estCorrecte']) ? 1 : 0;
    $id_question = $_POST['id_question'];
    
    if (!empty($_POST['reponse_id'])) {
        $stmt = $db->prepare("UPDATE reponse SET texte = :texte, est_correcte = :est_correcte, id_question = :id_question WHERE id_reponse = :id");
        $stmt->execute([
            'texte' => $contenu,
            'est_correcte' => $estCorrecte,
            'id_question' => $id_question,
            'id' => $_POST['reponse_id']
        ]);
    } else {
        $stmt = $db->prepare("INSERT INTO reponse (id_question, texte, est_correcte, dateCreation) VALUES (:id_question, :texte, :est_correcte, NOW())");
        $stmt->execute([
            'id_question' => $id_question,
            'texte' => $contenu,
            'est_correcte' => $estCorrecte
        ]);
    }
    header("Location: reponse-admin.php?msg=success");
    exit();
}

// 3. Fetch Data
$listReponses = $db->query("SELECT r.*, q.enonce as enonce_question FROM reponse r LEFT JOIN question q ON r.id_question = q.id_question ORDER BY r.id_reponse DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Jobyfind — Réponses</title>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Serif+Display&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <link rel="stylesheet" href="admin-theme.css">
</head>
<body>
  <aside class="sidebar">
    <div class="sidebar-logo">
        <div class="logo-icon" style="background:#2d79ff"><i class="fas fa-rocket" style="color:white"></i></div>
        <div class="logo-text">Joby<span>find</span></div>
        <span class="sidebar-badge"><?= htmlspecialchars($role) ?></span>
    </div>
    <div class="sidebar-section">
      <p class="sidebar-section-label">Module Quiz</p>
      <a class="sidebar-link" href="quiz-admin.php"><i class="fa-solid fa-lightbulb"></i><span>Liste des Quiz</span></a>
      <a class="sidebar-link" href="question-admin.php"><i class="fa-solid fa-circle-question"></i><span>Questions</span></a>
      <a class="sidebar-link active" href="reponse-admin.php"><i class="fa-solid fa-check-double"></i><span>Réponses</span></a>
    </div>
  </aside>

  <div class="main">
    <header class="header">
      <div class="header-breadcrumb"><span>Admin</span> <i class="fa fa-chevron-right" style="font-size:9px"></i> <span class="current">Réponses</span></div>
      <div class="header-search"><i class="fa fa-search"></i> <input type="text" placeholder="Rechercher..." oninput="filterTable()" id="search-input"></div>
    </header>

    <div class="content">
      <div class="table-card">
        <div class="table-header">
          <div><p class="table-title">Gestion des réponses</p></div>
          <div class="table-controls"><button class="btn-primary" onclick="openAddModal()"><i class="fa fa-plus"></i> Nouvelle réponse</button></div>
        </div>
        <table>
          <thead><tr><th>Contenu</th><th>Correcte?</th><th>Question</th><th>Actions</th></tr></thead>
          <tbody id="table-body">
            <?php foreach ($listReponses as $r): ?>
            <tr>
              <td><?= htmlspecialchars($r['texte']) ?></td>
              <td><span class="badge <?= $r['est_correcte'] ? 'badge-green' : 'badge-red' ?>"><?= $r['est_correcte'] ? 'Oui' : 'Non' ?></span></td>
              <td><?= htmlspecialchars(mb_substr($r['enonce_question'], 0, 40)) ?>...</td>
              <td>
                <div class="action-btns">
                  <div class="action-btn edit" onclick="openEditModal(<?= $r['id_reponse'] ?>, '<?= addslashes($r['texte']) ?>', <?= $r['est_correcte'] ?>, <?= $r['id_question'] ?>)"><i class="fa fa-pen"></i></div>
                  <div class="action-btn del" onclick="openDeleteModal(<?= $r['id_reponse'] ?>, '<?= addslashes($r['texte']) ?>')"><i class="fa fa-trash"></i></div>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="modal-overlay" id="reponse-modal">
    <div class="modal">
      <div class="modal-header"><p class="modal-title" id="modal-title">Réponse</p><button class="modal-close" onclick="closeModal('reponse-modal')"><i class="fa fa-xmark"></i></button></div>
      <form method="POST" action="reponse-admin.php">
        <div class="modal-body">
          <input type="hidden" name="action_save" value="1">
          <input type="hidden" name="reponse_id" id="f-reponse-id" value="">
          <div class="form-group"><label class="form-label">Texte *</label><textarea class="form-input" id="f-contenu" name="contenu" required></textarea></div>
          <div class="form-row">
            <div class="form-group"><label class="form-label">Question *</label><select class="form-input" id="f-id_question" name="id_question" required><?php foreach($listQuestions as $q): ?><option value="<?= $q['id_question'] ?>"><?= htmlspecialchars(substr($q['enonce'],0,50)) ?>...</option><?php endforeach; ?></select></div>
            <div class="form-group" style="padding-top:30px"><label><input type="checkbox" id="f-estCorrecte" name="estCorrecte"> Correcte ?</label></div>
          </div>
        </div>
        <div class="modal-footer"><button type="button" class="btn-cancel" onclick="closeModal('reponse-modal')">Annuler</button><button type="submit" class="btn-primary">Enregistrer</button></div>
      </form>
    </div>
  </div>

  <div class="modal-overlay" id="delete-modal">
    <div class="modal">
      <div class="modal-header"><p class="modal-title" style="color:var(--danger)">Suppression</p><button class="modal-close" onclick="closeModal('delete-modal')"><i class="fa fa-xmark"></i></button></div>
      <div class="modal-body"><p>Supprimer <strong id="delete-name"></strong> ?</p></div>
      <div class="modal-footer"><button class="btn-cancel" onclick="closeModal('delete-modal')">Annuler</button><button class="btn-danger" onclick="confirmDelete()">Supprimer</button></div>
    </div>
  </div>

  <script>
    let deleteTarget = null;
    function openAddModal() { document.getElementById('modal-title').textContent='Nouvelle réponse'; document.getElementById('f-reponse-id').value=''; document.getElementById('f-contenu').value=''; document.getElementById('f-estCorrecte').checked=false; document.getElementById('reponse-modal').classList.add('open'); }
    function openEditModal(id, t, ec, q) { document.getElementById('modal-title').textContent='Modifier'; document.getElementById('f-reponse-id').value=id; document.getElementById('f-contenu').value=t; document.getElementById('f-estCorrecte').checked=!!ec; document.getElementById('f-id_question').value=q; document.getElementById('reponse-modal').classList.add('open'); }
    function openDeleteModal(id, t) { deleteTarget=id; document.getElementById('delete-name').textContent=t; document.getElementById('delete-modal').classList.add('open'); }
    function confirmDelete() { if(deleteTarget) window.location.href='?delete_id='+deleteTarget; }
    function closeModal(id) { document.getElementById(id).classList.remove('open'); }
    function filterTable() { const s=document.getElementById('search-input').value.toLowerCase(); document.querySelectorAll('#table-body tr').forEach(r=>{ r.style.display=r.innerText.toLowerCase().includes(s)?'':'none'; }); }
  </script>
</body>
</html>
