<?php
require_once __DIR__ . "/../../config/Database.php";
require_once __DIR__ . "/../../model/quizz.php";
require_once __DIR__ . "/../../controller/quizzController.php";

$quizController = new QuizController();


// 1. Suppression
if (isset($_GET['delete_id'])) {
    $quizController->deleteQuiz($_GET['delete_id']);
    header("Location: quiz-admin.php?msg=deleted"); // Redirection
    exit();
}

// 2. Ajout ou Modification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_save'])) {
    $titre = $_POST['titre'];
    $domaine = $_POST['domaine'];
    $niveau = $_POST['niveau'];
    
    $newQuiz = new Quiz($titre, $domaine, $niveau);

    if (!empty($_POST['quiz_id'])) {
        $quizController->updateQuiz($newQuiz, $_POST['quiz_id']);
    } else {
        $quizController->addQuiz($newQuiz);
    }
    header("Location: quiz-admin.php?msg=success");
    exit();
}

// 3. Récupération des données pour l'affichage
$listQuizzes = $quizController->listQuiz()->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Jobyfind — Quiz</title>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Serif+Display&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

  <!-- ✅ Shared admin theme -->
  <link rel="stylesheet" href="admin-theme.css">

  <!-- Page-specific overrides (none needed — everything is in admin-theme.css) -->
</head>
<body>

  <!-- ══════════════════════════════════════════
       SIDEBAR  (identical shell across all pages)
       ══════════════════════════════════════════ -->
  <aside class="sidebar">
    <div class="sidebar-logo">
      <div class="logo-icon" style="background:#7EB2FF">
        <img src="../../assets/images/all-img/jlog.png" alt="logo">
      </div>
      <div class="logo-text">Joby<span>find</span></div>
      <span class="sidebar-badge">Admin</span>
    </div>

    <div class="sidebar-section">
      <p class="sidebar-section-label">Tableau de bord</p>
      <a class="sidebar-link" href="admin.html">
        <i class="fa-solid fa-users"></i><span>Utilisateurs</span>
        <span class="badge">248</span>
      </a>
      <a class="sidebar-link">
        <i class="fa-solid fa-chart-line"></i><span>Statistiques</span>
      </a>
      <a class="sidebar-link">
        <i class="fa-solid fa-shield-halved"></i><span>Rôles & Accès</span>
      </a>
    </div>

    <div class="sidebar-section">
      <p class="sidebar-section-label">Gestion</p>
      <a class="sidebar-link active" href="quiz-admin.html">
        <i class="fa-solid fa-lightbulb"></i><span>Quiz</span>
        <span class="badge">14</span>
      </a>
      <a class="sidebar-link"><i class="fa-solid fa-book"></i><span>Formations</span></a>
      <a class="sidebar-link"><i class="fa-solid fa-calendar-days"></i><span>Evenements</span></a>
      <a class="sidebar-link"><i class="fa-solid fa-briefcase"></i><span>Offres</span></a>
      <a class="sidebar-link">
        <i class="fa fa-flag"></i>Signalements
        <span class="badge" style="background:#ef4444">3</span>
      </a>
      <a class="sidebar-link"><i class="fa fa-gear"></i>Paramètres</a>
    </div>

    <div class="sidebar-footer">
      <div class="admin-profile">
        <div class="admin-avatar">SA</div>
        <div class="admin-info">
          <p>Super Admin</p>
          <span>admin@jobyfind.tn</span>
        </div>
        <button class="logout-btn" title="Déconnexion"
                onclick="showToast('Déconnexion...','error','fa-right-from-bracket')">
          <i class="fa fa-right-from-bracket"></i>
        </button>
      </div>
    </div>
  </aside>

  <!-- ══════════════════════════════════════════
       MAIN
       ══════════════════════════════════════════ -->
  <div class="main">

    <!-- HEADER -->
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
        <div class="icon-btn" title="Notifications">
          <i class="fa fa-bell"></i><span class="dot"></span>
        </div>
        <div class="icon-btn" title="Exporter CSV" onclick="exportCSV()">
          <i class="fa fa-download"></i>
        </div>
        <div class="icon-btn" title="Retour">
          <a href="admin.html" style="color:inherit;display:flex">
            <i class="fa fa-arrow-left"></i>
          </a>
        </div>
      </div>
    </header>

    <!-- CONTENT -->
    <div class="content">

      <!-- STATS -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon blue"><i class="fa fa-lightbulb"></i></div>
          <div>
            <p class="stat-label">Total quiz</p>
            <p class="stat-value" id="stat-total"><?= count($listQuizzes) ?></p>
            <p class="stat-change up"><i class="fa fa-arrow-up" style="font-size:9px"></i> Mis à jour</p>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon green"><i class="fa fa-circle-check"></i></div>
          <div>
            <p class="stat-label">Publiés</p>
            <p class="stat-value" id="stat-publie"><?= count(array_filter($listQuizzes, function($q) { return $q['statut'] ?? false; })) ?></p>
            <p class="stat-change up"><i class="fa fa-arrow-up" style="font-size:9px"></i> Actifs</p>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon amber"><i class="fa fa-pencil"></i></div>
          <div>
            <p class="stat-label">Brouillons</p>
            <p class="stat-value" id="stat-brouillon">0</p>
            <p class="stat-change down"><i class="fa fa-arrow-down" style="font-size:9px"></i> À traiter</p>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon purple"><i class="fa fa-layer-group"></i></div>
          <div>
            <p class="stat-label">Domaines couverts</p>
            <p class="stat-value" id="stat-domaines"><?= count(array_unique(array_column($listQuizzes, 'domaine'))) ?></p>
            <p class="stat-change up"><i class="fa fa-arrow-up" style="font-size:9px"></i> Variété</p>
          </div>
        </div>
      </div>

      <!-- TABLE CARD -->
      <div class="table-card">
        <div class="table-header">
          <div>
            <p class="table-title">Gestion des quiz</p>
            <p class="table-subtitle" id="table-count"><?= count($listQuizzes) ?> quiz enregistrés</p>
          </div>
          <div class="table-controls">
            <select class="filter-select" id="domaine-filter" onchange="filterTable()">
              <option value="">Tous les domaines</option>
              <option>Marketing</option>
              <option>Finance</option>
              <option>Management</option>
              <option>Tech & Dev</option>
              <option>RH</option>
              <option>Entrepreneuriat</option>
            </select>
            <select class="filter-select" id="niveau-filter" onchange="filterTable()">
              <option value="">Tous les niveaux</option>
              <option>Débutant</option>
              <option>Intermédiaire</option>
              <option>Avancé</option>
            </select>
            <select class="filter-select" id="statut-filter" onchange="filterTable()">
              <option value="">Tous les statuts</option>
              <option>Publié</option>
              <option>Brouillon</option>
            </select>
            <button class="btn-primary" onclick="openAddModal()">
              <i class="fa fa-plus"></i> Nouveau quiz
            </button>
          </div>
        </div>

        <table id="quiz-table">
          <thead>
            <tr>
              <th><input type="checkbox" id="select-all" onchange="toggleAll(this)"></th>
              <th>Titre</th>
              <th>Domaine</th>
              <th>Niveau</th>
              <th>Statut</th>
              <th>Date de création</th>
              <th>Nb. questions</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="table-body">
              <?php foreach ($listQuizzes as $q): ?>
              <tr>
                  <td><input type="checkbox" class="row-check"></td>
                  <td>
                      <div class="user-cell">
                          <div class="user-avatar" style="background:#dbeafe;color:#1d4ed8;width:30px;height:30px;font-size:11px">
                              <?= strtoupper(substr($q['domaine'], 0, 2)) ?>
                          </div>
                          <div>
                              <p class="user-name"><?= htmlspecialchars($q['titre']) ?></p>
                          </div>
                      </div>
                  </td>
                  <td><span class="badge badge-blue"><?= $q['domaine'] ?></span></td>
                  <td><span class="badge badge-amber"><?= $q['niveau'] ?></span></td>
                  <td><span class="badge badge-green">Publié</span></td>
                  <td style="color:var(--muted);font-size:12px"><?= $q['dateCreation'] ?></td>
                  <td style="text-align:center"><span class="badge badge-blue">0</span></td>
                  <td>
                      <div class="action-btns">
                          <div class="action-btn view" title="Voir" data-action="view" data-id="<?= $q['id_quiz'] ?>" data-titre="<?= htmlspecialchars($q['titre'], ENT_QUOTES) ?>" data-domaine="<?= htmlspecialchars($q['domaine'], ENT_QUOTES) ?>" data-niveau="<?= htmlspecialchars($q['niveau'], ENT_QUOTES) ?>" data-date="<?= $q['dateCreation'] ?>"><i class="fa fa-eye"></i></div>
                          <div class="action-btn edit" title="Modifier" data-action="edit" data-id="<?= $q['id_quiz'] ?>" data-titre="<?= htmlspecialchars($q['titre'], ENT_QUOTES) ?>" data-domaine="<?= htmlspecialchars($q['domaine'], ENT_QUOTES) ?>" data-niveau="<?= htmlspecialchars($q['niveau'], ENT_QUOTES) ?>"><i class="fa fa-pen"></i></div>
                          <div class="action-btn del" title="Supprimer" data-action="delete" data-id="<?= $q['id_quiz'] ?>" data-titre="<?= htmlspecialchars($q['titre'], ENT_QUOTES) ?>"><i class="fa fa-trash"></i></div>
                      </div>
                  </td>
              </tr>
              <?php endforeach; ?>
          </tbody>
        </table>

        <!-- Empty state (shown when no results) -->
        <div class="empty-state" id="empty-state" style="display:none">
          <i class="fa fa-lightbulb"></i>
          <strong>Aucun quiz trouvé</strong>
          <p>Essayez de modifier vos filtres ou ajoutez un nouveau quiz.</p>
        </div>

        <div class="pagination">
          <p class="pagination-info" id="pagination-info">Affichage 1–10 sur 14</p>
          <div class="pagination-btns" id="pagination-btns"></div>
        </div>
      </div>

    </div><!-- /content -->
  </div><!-- /main -->


  <!-- ══════════════════════════════════════════
       MODAL — ADD / EDIT QUIZ
       ══════════════════════════════════════════ -->
  <div class="modal-overlay" id="quiz-modal">
    <div class="modal">
      <div class="modal-header">
        <p class="modal-title" id="modal-title">Nouveau quiz</p>
        <button class="modal-close" onclick="closeModal('quiz-modal')"><i class="fa fa-xmark"></i></button>
      </div>
      <form method="POST" action="" id="quiz-form">
        <div class="modal-body">

          <input type="hidden" name="action_save" value="1">
          <input type="hidden" name="quiz_id" id="f-quiz-id" value="">

          <div class="form-group">
            <label class="form-label">Titre du quiz <span style="color:var(--danger)">*</span></label>
            <input class="form-input" id="f-titre" name="titre" type="text" placeholder="Ex: Introduction au Marketing Digital" required>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Domaine <span style="color:var(--danger)">*</span></label>
              <select class="form-input" id="f-domaine" name="domaine" required>
                <option value="">— Choisir —</option>
                <option>Marketing</option>
                <option>Finance</option>
                <option>Management</option>
                <option>Tech & Dev</option>
                <option>RH</option>
                <option>Entrepreneuriat</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">Niveau <span style="color:var(--danger)">*</span></label>
              <select class="form-input" id="f-niveau" name="niveau" required>
                <option value="">— Choisir —</option>
                <option>Débutant</option>
                <option>Intermédiaire</option>
                <option>Avancé</option>
              </select>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Statut</label>
              <select class="form-input" id="f-statut" name="statut">
                <option>Brouillon</option>
                <option>Publié</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">Nombre de questions</label>
              <input class="form-input" id="f-questions" name="questions" type="number" min="1" max="100" placeholder="10">
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Description</label>
            <textarea class="form-input" id="f-description" name="description" placeholder="Décrivez brièvement ce quiz..."></textarea>
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

  <!-- ══════════════════════════════════════════
       MODAL — DELETE CONFIRM
       ══════════════════════════════════════════ -->
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

  <!-- ══════════════════════════════════════════
       MODAL — VIEW QUIZ DETAILS
       ══════════════════════════════════════════ -->
  <div class="modal-overlay" id="view-modal">
    <div class="modal">
      <div class="modal-header">
        <p class="modal-title">
          <i class="fa fa-eye" style="margin-right:8px"></i>Détails du quiz
        </p>
        <button class="modal-close" onclick="closeModal('view-modal')"><i class="fa fa-xmark"></i></button>
      </div>
      <div class="modal-body">
        <div class="quiz-details">
          <div class="detail-row">
            <label class="detail-label">Titre:</label>
            <span class="detail-value" id="view-titre"></span>
          </div>
          <div class="detail-row">
            <label class="detail-label">Domaine:</label>
            <span class="detail-value" id="view-domaine"></span>
          </div>
          <div class="detail-row">
            <label class="detail-label">Niveau:</label>
            <span class="detail-value" id="view-niveau"></span>
          </div>
          <div class="detail-row">
            <label class="detail-label">Date de création:</label>
            <span class="detail-value" id="view-date"></span>
          </div>
          <div class="detail-row">
            <label class="detail-label">Statut:</label>
            <span class="detail-value" id="view-statut">Publié</span>
          </div>
          <div class="detail-row">
            <label class="detail-label">Nombre de questions:</label>
            <span class="detail-value" id="view-questions">0</span>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn-primary" onclick="closeModal('view-modal')">
          <i class="fa fa-times"></i> Fermer
        </button>
      </div>
    </div>
  </div>

  <!-- TOAST -->
  <div class="toast-container" id="toast-container"></div>


  <!-- ══════════════════════════════════════════
       JAVASCRIPT
       ══════════════════════════════════════════ -->
  <script>
    let deleteTarget = null;

    /* ── Add modal ── */
    function openAddModal() {
      document.getElementById('modal-title').textContent='Nouveau quiz';
      document.getElementById('f-quiz-id').value='';
      ['f-titre','f-description'].forEach(id=>document.getElementById(id).value='');
      document.getElementById('f-domaine').value='';
      document.getElementById('f-niveau').value='';
      document.getElementById('f-statut').value='Brouillon';
      document.getElementById('f-questions').value='';
      document.getElementById('quiz-form').action='';
      document.getElementById('quiz-modal').classList.add('open');
    }

    /* ── Edit modal ── */
    function openEditModal(id, titre, domaine, niveau) {
      document.getElementById('modal-title').textContent='Modifier le quiz';
      document.getElementById('f-quiz-id').value=id;
      document.getElementById('f-titre').value=titre;
      document.getElementById('f-domaine').value=domaine;
      document.getElementById('f-niveau').value=niveau;
      document.getElementById('f-statut').value='Publié';
      document.getElementById('f-questions').value='';
      document.getElementById('f-description').value='';
      document.getElementById('quiz-modal').classList.add('open');
    }

    /* ── Delete ── */
    function openDeleteModal(id, titre) {
      deleteTarget=id;
      document.getElementById('delete-name').textContent=`"${titre}"`;
      document.getElementById('delete-modal').classList.add('open');
    }

    function confirmDelete() {
      if (deleteTarget) {
        window.location.href='?delete_id='+deleteTarget;
      }
    }

    function viewQuiz(id, titre, domaine, niveau, date) {
      document.getElementById('view-titre').textContent = titre;
      document.getElementById('view-domaine').textContent = domaine;
      document.getElementById('view-niveau').textContent = niveau;
      document.getElementById('view-date').textContent = date;
      document.getElementById('view-modal').classList.add('open');
    }

    /* ── Utilities ── */
    function closeModal(id)     { document.getElementById(id).classList.remove('open'); }
    function showToast(msg,type='success',icon='fa-circle-check') {
      const t=document.createElement('div');
      t.className=`toast ${type}`;
      t.innerHTML=`<i class="fa ${icon}"></i> ${msg}`;
      document.getElementById('toast-container').appendChild(t);
      setTimeout(()=>t.remove(),3500);
    }

    function filterTable() {
      const search  = document.getElementById('search-input').value.toLowerCase();
      const domaineF = document.getElementById('domaine-filter').value;
      const niveauF  = document.getElementById('niveau-filter').value;

      const rows = document.querySelectorAll('#table-body tr');
      let visibleCount = 0;

      rows.forEach(row => {
        const cells = row.querySelectorAll('td');
        const titre = cells[1].textContent.toLowerCase();
        const domaine = cells[2].textContent.toLowerCase();
        const niveau = cells[3].textContent.toLowerCase();

        const matchSearch = !search || titre.includes(search) || domaine.includes(search);
        const matchDomaine = !domaineF || domaine.includes(domaineF.toLowerCase());
        const matchNiveau = !niveauF || niveau.includes(niveauF.toLowerCase());

        const show = matchSearch && matchDomaine && matchNiveau;
        row.style.display = show ? '' : 'none';
        if (show) visibleCount++;
      });

      document.getElementById('empty-state').style.display = visibleCount === 0 ? '' : 'none';
    }

    function toggleAll(cb) { document.querySelectorAll('.row-check').forEach(c=>c.checked=cb.checked); }

    function exportCSV() {
      const rows=[['Titre','Domaine','Niveau','Statut','Date','Nb Questions']];
      const tableRows = document.querySelectorAll('#table-body tr:not([style*="display: none"])');
      tableRows.forEach(row => {
        const cells = row.querySelectorAll('td');
        rows.push([
          cells[1].textContent.trim(),
          cells[2].textContent.trim(),
          cells[3].textContent.trim(),
          cells[4].textContent.trim(),
          cells[5].textContent.trim(),
          cells[6].textContent.trim()
        ]);
      });
      const csv=rows.map(r=>r.map(c=>`"${c}"`).join(',')).join('\n');
      const a=document.createElement('a');
      a.href=URL.createObjectURL(new Blob([csv],{type:'text/csv'}));
      a.download='jobyfind_quiz.csv';
      a.click();
      showToast('Export CSV téléchargé.','success','fa-download');
    }

    /* ── Close modal on backdrop click ── */
    document.querySelectorAll('.modal-overlay').forEach(m=>{
      m.addEventListener('click',e=>{ if(e.target===m) m.classList.remove('open'); });
    });

    /* ── Event delegation for action buttons ── */
    document.addEventListener('click', function(e) {
      const btn = e.target.closest('.action-btn');
      if (!btn) return;

      const action = btn.dataset.action;
      const id = btn.dataset.id;
      const titre = btn.dataset.titre;
      const domaine = btn.dataset.domaine;
      const niveau = btn.dataset.niveau;

      if (action === 'view') {
        viewQuiz(id, titre, domaine, niveau, btn.dataset.date);
      } else if (action === 'edit') {
        openEditModal(id, titre, domaine, niveau);
      } else if (action === 'delete') {
        openDeleteModal(id, titre);
      }
    });

    /* ── Handle form submission ── */
    document.getElementById('quiz-form').addEventListener('submit', function(e) {
      const titre = document.getElementById('f-titre').value.trim();
      const domaine = document.getElementById('f-domaine').value;
      const niveau = document.getElementById('f-niveau').value;

      if (!titre || !domaine || !niveau) {
        e.preventDefault();
        showToast('Veuillez remplir tous les champs obligatoires.','error','fa-circle-exclamation');
        return;
      }
    });

    /* ── Show success message if redirected ── */
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('msg') === 'success') {
      showToast('Quiz enregistré avec succès.','success','fa-circle-check');
    } else if (urlParams.get('msg') === 'deleted') {
      showToast('Quiz supprimé avec succès.','error','fa-trash');
    }
  </script>
</body>
</html>
