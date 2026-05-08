<?php
require_once __DIR__ . "/../../config/Database.php";
require_once __DIR__ . "/../../model/quizz.php";
require_once __DIR__ . "/../../controller/quizzController.php";

$quizController = new QuizController();

// 1. Suppression
if (isset($_GET['delete_id'])) {
  $quizController->deleteQuiz((int) $_GET['delete_id']);
  header("Location: quiz-admin.php?msg=deleted");
  exit();
}

// 2. Ajout ou Modification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_save'])) {
  $titre = trim($_POST['titre']);
  $domaine = $_POST['domaine'];
  $niveau = $_POST['niveau'];

  $newQuiz = new Quiz($titre, $domaine, $niveau);  // FIXED: was "Quizz"

  if (!empty($_POST['quiz_id'])) {
    $quizController->updateQuiz($newQuiz, (int) $_POST['quiz_id']);
  } else {
    $quizController->addQuiz($newQuiz);
  }
  header("Location: quiz-admin.php?msg=success");
  exit();
}

// 3. Récupération des données
$listQuizzes = $quizController->listQuiz()->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Jobyfind — Quiz Admin</title>
  <link
    href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Serif+Display&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <link rel="stylesheet" href="admin-theme.css">
</head>

<body>

  <aside class="sidebar">
    <div class="sidebar-logo">
      <div class="logo-icon" style="background:#7EB2FF">
        <i class="fa fa-graduation-cap" style="color:white;font-size:18px;padding:6px"></i>
      </div>
      <div class="logo-text">Joby<span>find</span></div>
      <span class="sidebar-badge">Admin</span>
    </div>

    <div class="sidebar-section">
      <p class="sidebar-section-label">Tableau de bord</p>
      <a class="sidebar-link" href="admin.html">
        <i class="fa-solid fa-users"></i><span>Utilisateurs</span>
      </a>
      <a class="sidebar-link">
        <i class="fa-solid fa-chart-line"></i><span>Statistiques</span>
      </a>
    </div>

    <div class="sidebar-section">
      <p class="sidebar-section-label">Gestion</p>
      <a class="sidebar-link active" href="quiz-admin.php">
        <i class="fa-solid fa-lightbulb"></i><span>Quiz</span>
        <span class="badge"><?= count($listQuizzes) ?></span>
      </a>
      <a class="sidebar-link" href="question-admin.php">
        <i class="fas fa-question-circle"></i> Questions
      </a>
    </div>

    <div class="sidebar-section">
      <p class="sidebar-section-label">Résultats</p>
      <a class="sidebar-link" href="submissions-admin.php">
        <i class="fas fa-users-viewfinder"></i><span>Participations</span>
      </a>
    </div>

    <div class="sidebar-footer">
      <div class="admin-profile">
        <div class="admin-avatar">SA</div>
        <div class="admin-info">
          <p>Super Admin</p>
          <span>admin@jobyfind.tn</span>
        </div>
      </div>
    </div>
  </aside>

  <div class="main">
    <header class="header">
      <div class="header-breadcrumb">
        <span>Admin</span>
        <i class="fa fa-chevron-right" style="font-size:9px"></i>
        <span class="current">Quiz</span>
      </div>
      <div class="header-search">
        <i class="fa fa-search"></i>
        <input type="text" placeholder="Rechercher un quiz..." id="search-input" oninput="filterTable()">
      </div>
      <div class="header-actions">
        <div class="icon-btn" title="Exporter CSV" onclick="exportCSV()">
          <i class="fa fa-download"></i>
        </div>
        <div class="icon-btn" title="Retour">
          <a href="../frontoffice/quizzes-list.php" style="color:inherit;display:flex">
            <i class="fa fa-arrow-left"></i>
          </a>
        </div>
      </div>
    </header>

    <div class="content">

      <!-- STATS -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon blue"><i class="fa fa-lightbulb"></i></div>
          <div>
            <p class="stat-label">Total quiz</p>
            <p class="stat-value"><?= count($listQuizzes) ?></p>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon purple"><i class="fa fa-layer-group"></i></div>
          <div>
            <p class="stat-label">Domaines couverts</p>
            <p class="stat-value"><?= count(array_unique(array_column($listQuizzes, 'domaine'))) ?></p>
          </div>
        </div>
      </div>

      <!-- TABLE -->
      <div class="table-card">
        <div class="table-header">
          <div>
            <p class="table-title">Gestion des quiz</p>
            <p class="table-subtitle"><?= count($listQuizzes) ?> quiz enregistrés</p>
          </div>
          <div class="table-controls">
            <select class="filter-select" id="domaine-filter" onchange="filterTable()">
              <option value="">Tous les domaines</option>
              <option>Marketing</option>
              <option>Finance</option>
              <option>Management</option>
              <option>Tech & Dev</option>
              <option>Tech</option>
              <option>RH</option>
              <option>Entrepreneuriat</option>
            </select>
            <select class="filter-select" id="niveau-filter" onchange="filterTable()">
              <option value="">Tous les niveaux</option>
              <option>Débutant</option>
              <option>Intermédiaire</option>
              <option>Avancé</option>
            </select>
            <button class="btn-primary" onclick="openAddModal()">
              <i class="fa fa-plus"></i> Nouveau quiz
            </button>
          </div>
        </div>

        <table id="quiz-table">
          <thead>
            <tr>
              <th>Titre</th>
              <th>Domaine</th>
              <th>Niveau</th>
              <th>Date de création</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="table-body">
            <?php foreach ($listQuizzes as $q): ?>
              <tr>
                <td>
                  <div class="user-cell">
                    <div class="user-avatar"
                      style="background:#dbeafe;color:#1d4ed8;width:30px;height:30px;font-size:11px">
                      <?= strtoupper(substr($q['domaine'], 0, 2)) ?>
                    </div>
                    <div>
                      <p class="user-name"><?= htmlspecialchars($q['titre']) ?></p>
                    </div>
                  </div>
                </td>
                <td><span class="badge badge-blue"><?= htmlspecialchars($q['domaine']) ?></span></td>
                <td><span class="badge badge-amber"><?= htmlspecialchars($q['niveau']) ?></span></td>
                <td style="color:var(--muted);font-size:12px"><?= htmlspecialchars($q['dateCreation']) ?></td>
                <td>
                  <div class="action-btns">
                    <div class="action-btn edit" title="Modifier" data-action="edit" data-id="<?= $q['id_quiz'] ?>"
                      data-titre="<?= htmlspecialchars($q['titre'], ENT_QUOTES) ?>"
                      data-domaine="<?= htmlspecialchars($q['domaine'], ENT_QUOTES) ?>"
                      data-niveau="<?= htmlspecialchars($q['niveau'], ENT_QUOTES) ?>">
                      <i class="fa fa-pen"></i>
                    </div>
                    <div class="action-btn del" title="Supprimer" data-action="delete" data-id="<?= $q['id_quiz'] ?>"
                      data-titre="<?= htmlspecialchars($q['titre'], ENT_QUOTES) ?>">
                      <i class="fa fa-trash"></i>
                    </div>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>

        <div class="empty-state" id="empty-state" style="display:none">
          <i class="fa fa-lightbulb"></i>
          <strong>Aucun quiz trouvé</strong>
          <p>Modifiez vos filtres ou ajoutez un nouveau quiz.</p>
        </div>
      </div>

    </div>
  </div>

  <!-- MODAL ADD/EDIT -->
  <div class="modal-overlay" id="quiz-modal">
    <div class="modal">
      <div class="modal-header">
        <p class="modal-title" id="modal-title">Nouveau quiz</p>
        <button class="modal-close" onclick="closeModal('quiz-modal')"><i class="fa fa-xmark"></i></button>
      </div>
      <form method="POST" action="" id="quiz-form" onsubmit="return validateForm(event)">
        <div class="modal-body">
          <input type="hidden" name="action_save" value="1">
          <input type="hidden" name="quiz_id" id="f-quiz-id" value="">

          <div class="form-group">
            <label class="form-label">Titre du quiz <span style="color:var(--danger)">*</span></label>
            <input class="form-input" id="f-titre" name="titre" type="text"
              placeholder="Ex: Introduction au Marketing Digital">
          </div>

          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Domaine <span style="color:var(--danger)">*</span></label>
              <select class="form-input" id="f-domaine" name="domaine">
                <option value="">— Choisir —</option>
                <option>Marketing</option>
                <option>Finance</option>
                <option>Management</option>
                <option>Tech & Dev</option>
                <option>Tech</option>
                <option>RH</option>
                <option>Entrepreneuriat</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">Niveau <span style="color:var(--danger)">*</span></label>
              <select class="form-input" id="f-niveau" name="niveau">
                <option value="">— Choisir —</option>
                <option>Débutant</option>
                <option>Intermédiaire</option>
                <option>Avancé</option>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn-cancel" onclick="closeModal('quiz-modal')">Annuler</button>
          <button type="submit" class="btn-primary" style="padding:8px 18px;font-size:13px">
            <i class="fa fa-floppy-disk"></i> Enregistrer
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- MODAL DELETE -->
  <div class="modal-overlay" id="delete-modal">
    <div class="modal">
      <div class="modal-header">
        <p class="modal-title" style="color:var(--danger)">
          <i class="fa fa-triangle-exclamation" style="margin-right:8px"></i>Confirmer la suppression
        </p>
        <button class="modal-close" onclick="closeModal('delete-modal')"><i class="fa fa-xmark"></i></button>
      </div>
      <div class="modal-body">
        <p style="font-size:14px;color:var(--text);line-height:1.6">
          Vous êtes sur le point de supprimer le quiz
          <strong id="delete-name" style="color:var(--navy)"></strong>.<br><br>
          Cette action est <strong style="color:var(--danger)">irréversible</strong>.
        </p>
      </div>
      <div class="modal-footer">
        <button class="btn-cancel" onclick="closeModal('delete-modal')">Annuler</button>
        <button class="btn-danger" onclick="confirmDelete()">
          <i class="fa fa-trash"></i> Supprimer
        </button>
      </div>
    </div>
  </div>

  <div class="toast-container" id="toast-container"></div>

  <script>
    let deleteTarget = null;

    function validateForm(e) {
      let ok = true;
      const titre = document.getElementById('f-titre').value.trim();
      const domaine = document.getElementById('f-domaine').value;
      const niveau = document.getElementById('f-niveau').value;

      if (titre.length < 2) {
        showToast('Le titre est trop court.', 'error', 'fa-triangle-exclamation');
        ok = false;
      } else if (!domaine) {
        showToast('Veuillez choisir un domaine.', 'error', 'fa-triangle-exclamation');
        ok = false;
      } else if (!niveau) {
        showToast('Veuillez choisir un niveau.', 'error', 'fa-triangle-exclamation');
        ok = false;
      }

      if (!ok) e.preventDefault();
      return ok;
    }

    function openAddModal() {
      document.getElementById('modal-title').textContent = 'Nouveau quiz';
      document.getElementById('f-quiz-id').value = '';
      document.getElementById('f-titre').value = '';
      document.getElementById('f-domaine').value = '';
      document.getElementById('f-niveau').value = '';
      document.getElementById('quiz-modal').classList.add('open');
    }

    function openEditModal(id, titre, domaine, niveau) {
      document.getElementById('modal-title').textContent = 'Modifier le quiz';
      document.getElementById('f-quiz-id').value = id;
      document.getElementById('f-titre').value = titre;
      document.getElementById('f-domaine').value = domaine;
      document.getElementById('f-niveau').value = niveau;
      document.getElementById('quiz-modal').classList.add('open');
    }

    function openDeleteModal(id, titre) {
      deleteTarget = id;
      document.getElementById('delete-name').textContent = `"${titre}"`;
      document.getElementById('delete-modal').classList.add('open');
    }

    function confirmDelete() {
      if (deleteTarget) window.location.href = '?delete_id=' + deleteTarget;
    }

    function closeModal(id) { document.getElementById(id).classList.remove('open'); }

    function showToast(msg, type = 'success', icon = 'fa-circle-check') {
      const t = document.createElement('div');
      t.className = `toast ${type}`;
      t.innerHTML = `<i class="fa ${icon}"></i> ${msg}`;
      document.getElementById('toast-container').appendChild(t);
      setTimeout(() => t.remove(), 3500);
    }

    function filterTable() {
      const search = document.getElementById('search-input').value.toLowerCase();
      const domaineF = document.getElementById('domaine-filter').value.toLowerCase();
      const niveauF = document.getElementById('niveau-filter').value.toLowerCase();
      const rows = document.querySelectorAll('#table-body tr');
      let count = 0;
      rows.forEach(row => {
        const cells = row.querySelectorAll('td');
        const titre = cells[0].textContent.toLowerCase();
        const domaine = cells[1].textContent.toLowerCase();
        const niveau = cells[2].textContent.toLowerCase();
        const show = (!search || titre.includes(search) || domaine.includes(search))
          && (!domaineF || domaine.includes(domaineF))
          && (!niveauF || niveau.includes(niveauF));
        row.style.display = show ? '' : 'none';
        if (show) count++;
      });
      document.getElementById('empty-state').style.display = count === 0 ? '' : 'none';
    }

    function exportCSV() {
      const rows = [['Titre', 'Domaine', 'Niveau', 'Date']];
      document.querySelectorAll('#table-body tr:not([style*="none"])').forEach(row => {
        const c = row.querySelectorAll('td');
        rows.push([c[0].textContent.trim(), c[1].textContent.trim(), c[2].textContent.trim(), c[3].textContent.trim()]);
      });
      const csv = rows.map(r => r.map(c => `"${c}"`).join(',')).join('\n');
      const a = document.createElement('a');
      a.href = URL.createObjectURL(new Blob([csv], { type: 'text/csv' }));
      a.download = 'jobyfind_quiz.csv';
      a.click();
      showToast('Export CSV téléchargé.', 'success', 'fa-download');
    }

    document.querySelectorAll('.modal-overlay').forEach(m => {
      m.addEventListener('click', e => { if (e.target === m) m.classList.remove('open'); });
    });

    document.addEventListener('click', function (e) {
      const btn = e.target.closest('.action-btn');
      if (!btn) return;
      const { action, id, titre, domaine, niveau } = btn.dataset;
      if (action === 'edit') openEditModal(id, titre, domaine, niveau);
      if (action === 'delete') openDeleteModal(id, titre);
    });

    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('msg') === 'success') showToast('Quiz enregistré avec succès.', 'success', 'fa-circle-check');
    if (urlParams.get('msg') === 'deleted') showToast('Quiz supprimé avec succès.', 'error', 'fa-trash');
  </script>
</body>

</html>