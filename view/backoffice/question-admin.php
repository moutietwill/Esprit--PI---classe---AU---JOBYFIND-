<?php
require_once(__DIR__ . '/../../config/session.php');
startAppSession();

require_once(__DIR__ . '/../../config/Database.php');
require_once(__DIR__ . '/../../Model/question.php');
require_once(__DIR__ . '/../../Controller/QuizzController.php');

$role = $_SESSION['role'] ?? 'Guest';
$isTutor = ($role === 'Tutor' || $role === 'Admin');

if (!$isTutor) {
    header("Location: quiz-admin.php");
    exit();
}

$db = Database::getInstance()->getConnection();
$listQuizz = $db->query("SELECT id_quiz, titre FROM quizz ORDER BY titre")->fetchAll(PDO::FETCH_ASSOC);

// 1. Delete
if (isset($_GET['delete_id'])) {
    $stmt = $db->prepare("DELETE FROM question WHERE id_question = :id");
    $stmt->execute(['id' => $_GET['delete_id']]);
    header("Location: question-admin.php?msg=deleted");
    exit();
}

// 2. Save/Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_save'])) {
    $contenu = $_POST['contenu'];
    $difficulte = $_POST['difficulte'];
    $id_quiz = $_POST['id_quiz'];
    
    if (!empty($_POST['question_id'])) {
        $stmt = $db->prepare("UPDATE question SET enonce = :enonce, type = :type, id_quiz = :id_quiz WHERE id_question = :id");
        $stmt->execute([
            'enonce' => $contenu,
            'type' => $difficulte,
            'id_quiz' => $id_quiz,
            'id' => $_POST['question_id']
        ]);
    } else {
        $stmt = $db->prepare("INSERT INTO question (id_quiz, enonce, type, dateCreation) VALUES (:id_quiz, :enonce, :type, NOW())");
        $stmt->execute([
            'id_quiz' => $id_quiz,
            'enonce' => $contenu,
            'type' => $difficulte
        ]);
    }
    header("Location: question-admin.php?msg=success");
    exit();
}

// 3. Fetch Data
$listQuestions = $db->query("SELECT q.*, qz.titre as titre_quiz FROM question q LEFT JOIN quizz qz ON q.id_quiz = qz.id_quiz ORDER BY q.id_question DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Jobyfind — Questions</title>
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
      <a class="sidebar-link active" href="question-admin.php"><i class="fa-solid fa-circle-question"></i><span>Questions</span></a>
      <a class="sidebar-link" href="reponse-admin.php"><i class="fa-solid fa-check-double"></i><span>Réponses</span></a>
    </div>
  </aside>

  <div class="main">
    <header class="header">
      <div class="header-breadcrumb"><span>Admin</span> <i class="fa fa-chevron-right" style="font-size:9px"></i> <span class="current">Questions</span></div>
      <div class="header-search"><i class="fa fa-search"></i> <input type="text" placeholder="Rechercher..." oninput="filterTable()" id="search-input"></div>
    </header>

    <div class="content">
      <div class="table-card">
        <div class="table-header">
          <div><p class="table-title">Gestion des questions</p></div>
          <div class="table-controls"><button class="btn-primary" onclick="openAddModal()"><i class="fa fa-plus"></i> Nouvelle question</button></div>
        </div>
        <table>
          <thead><tr><th>Contenu</th><th>Difficulté</th><th>Quiz</th><th>Actions</th></tr></thead>
          <tbody id="table-body">
            <?php foreach ($listQuestions as $q): ?>
            <tr>
              <td><?= htmlspecialchars($q['enonce']) ?></td>
              <td><span class="badge badge-blue"><?= htmlspecialchars($q['type']) ?></span></td>
              <td><span class="badge badge-amber"><?= htmlspecialchars($q['titre_quiz']) ?></span></td>
              <td>
                <div class="action-btns">
                  <div class="action-btn edit" onclick="openEditModal(<?= $q['id_question'] ?>, '<?= addslashes($q['enonce']) ?>', '<?= addslashes($q['type']) ?>', <?= $q['id_quiz'] ?>)"><i class="fa fa-pen"></i></div>
                  <div class="action-btn del" onclick="openDeleteModal(<?= $q['id_question'] ?>, '<?= addslashes($q['enonce']) ?>')"><i class="fa fa-trash"></i></div>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="modal-overlay" id="question-modal">
    <div class="modal">
      <div class="modal-header"><p class="modal-title" id="modal-title">Question</p><button class="modal-close" onclick="closeModal('question-modal')"><i class="fa fa-xmark"></i></button></div>
      <form method="POST" action="question-admin.php">
        <div class="modal-body">
          <input type="hidden" name="action_save" value="1">
          <input type="hidden" name="question_id" id="f-question-id" value="">
          <div class="form-group"><label class="form-label">Contenu *</label><textarea class="form-input" id="f-contenu" name="contenu" required></textarea></div>
          <div class="form-row">
            <div class="form-group"><label class="form-label">Difficulté *</label><select class="form-input" id="f-difficulte" name="difficulte" required><option>Facile</option><option>Moyen</option><option>Difficile</option></select></div>
            <div class="form-group"><label class="form-label">Quiz *</label><select class="form-input" id="f-id_quiz" name="id_quiz" required><?php foreach($listQuizz as $qz): ?><option value="<?= $qz['id_quiz'] ?>"><?= htmlspecialchars($qz['titre']) ?></option><?php endforeach; ?></select></div>
          </div>
        </div>
        <div class="modal-footer"><button type="button" class="btn-cancel" onclick="closeModal('question-modal')">Annuler</button><button type="submit" class="btn-primary">Enregistrer</button></div>
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
    function openAddModal() { document.getElementById('modal-title').textContent='Nouvelle question'; document.getElementById('f-question-id').value=''; document.getElementById('f-contenu').value=''; document.getElementById('question-modal').classList.add('open'); }
    function openEditModal(id, c, d, q) { document.getElementById('modal-title').textContent='Modifier'; document.getElementById('f-question-id').value=id; document.getElementById('f-contenu').value=c; document.getElementById('f-difficulte').value=d; document.getElementById('f-id_quiz').value=q; document.getElementById('question-modal').classList.add('open'); }
    function openDeleteModal(id, c) { deleteTarget=id; document.getElementById('delete-name').textContent=c; document.getElementById('delete-modal').classList.add('open'); }
    function confirmDelete() { if(deleteTarget) window.location.href='?delete_id='+deleteTarget; }
    function closeModal(id) { document.getElementById(id).classList.remove('open'); }
    function filterTable() { const s=document.getElementById('search-input').value.toLowerCase(); document.querySelectorAll('#table-body tr').forEach(r=>{ r.style.display=r.innerText.toLowerCase().includes(s)?'':'none'; }); }
  </script>
</body>
</html>
