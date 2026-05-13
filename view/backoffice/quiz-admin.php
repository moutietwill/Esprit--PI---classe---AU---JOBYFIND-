<?php
require_once(__DIR__ . '/../../config/session.php');
startAppSession();

require_once(__DIR__ . '/../../config/Database.php');
require_once(__DIR__ . '/../../Model/quizz.php');
require_once(__DIR__ . '/../../Controller/QuizzController.php');

$quizController = new QuizzController();

// Role detection
$role = $_SESSION['role'] ?? 'Guest';
$isTutor = ($role === 'Tutor' || $role === 'Admin'); 
$isRestricted = ($role === 'Entrepreneur' || $role === 'Student');

// --- FORM PROCESSING LOGIC ---

if (isset($_GET['delete_id']) && $isTutor) {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("DELETE FROM quizz WHERE id_quiz = :id");
    $stmt->execute(['id' => $_GET['delete_id']]);
    header("Location: quiz-admin.php?msg=deleted");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_save']) && $isTutor) {
    $titre = $_POST['titre'];
    $domaine = $_POST['domaine'];
    $niveau = $_POST['niveau'];
    $id_user = $_SESSION['user_id'] ?? null;
    
    $newQuiz = new Quiz($titre, $domaine, $niveau, $id_user);
    if (!empty($_POST['quiz_id'])) { $newQuiz->setIdQuiz($_POST['quiz_id']); }
    if ($newQuiz->save()) { header("Location: quiz-admin.php?msg=success"); exit(); }
}

$listQuizzes = Quiz::getAll();

$domainClasses = [
    'Marketing' => 'marketing',
    'Finance' => 'finance',
    'Tech & Dev' => 'tech',
    'RH' => 'rh'
];
$domainIcons = [
    'Marketing' => 'fa-bullhorn',
    'Finance' => 'fa-coins',
    'Tech & Dev' => 'fa-code',
    'RH' => 'fa-users'
];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Jobyfind — Quiz Library</title>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Serif+Display&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <link rel="stylesheet" href="admin-theme.css">
</head>
<body style="background-color: #f8fafc;">

  <aside class="sidebar">
    <div class="sidebar-logo">
      <div class="logo-icon" style="background:#2d79ff"><i class="fas fa-rocket" style="color:white"></i></div>
      <div class="logo-text">Joby<span>find</span></div>
      <span class="sidebar-badge"><?= htmlspecialchars($role) ?></span>
    </div>

    <div class="sidebar-section">
      <p class="sidebar-section-label">Navigation</p>
      <a class="sidebar-link" href="../../public/index.php"><i class="fa-solid fa-house"></i><span>Accueil</span></a>
      <?php if ($role === 'Admin'): ?><a class="sidebar-link" href="admine.php"><i class="fa-solid fa-users"></i><span>Utilisateurs</span></a><?php endif; ?>
    </div>

    <div class="sidebar-section">
      <p class="sidebar-section-label">Module Quiz</p>
      <a class="sidebar-link active" href="quiz-admin.php"><i class="fa-solid fa-lightbulb"></i><span>Bibliothèque</span></a>
      <?php if ($isTutor): ?>
      <a class="sidebar-link" href="question-admin.php"><i class="fa-solid fa-circle-question"></i><span>Questions</span></a>
      <a class="sidebar-link" href="reponse-admin.php"><i class="fa-solid fa-check-double"></i><span>Réponses</span></a>
      <?php endif; ?>
    </div>

    <div class="sidebar-footer">
      <div class="admin-profile">
        <div class="admin-avatar"><?= substr($_SESSION['role'] ?? 'U', 0, 1) ?></div>
        <div class="admin-info"><p>Utilisateur #<?= $_SESSION['user_id'] ?? '?' ?></p><span><?= htmlspecialchars($role) ?></span></div>
        <a href="../frontoffice/logout.php" class="logout-btn"><i class="fa fa-right-from-bracket"></i></a>
      </div>
    </div>
  </aside>

  <div class="main">
    <header class="header">
      <div class="header-breadcrumb"><span>JobyFind</span> <i class="fa fa-chevron-right" style="font-size:9px"></i> <span class="current">Quiz Library</span></div>
      <div class="header-search"><i class="fa fa-search"></i> <input type="text" placeholder="Rechercher un quiz..." id="search-input" oninput="filterGrid()"></div>
    </header>

    <div class="content">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <div>
          <h2 style="font-family:'DM Sans'; font-weight:700; color:#1e293b; margin:0">Nos Quiz Interactifs</h2>
          <p style="color:#64748b; font-size:14px">Développez vos compétences avec nos évaluations certifiantes.</p>
        </div>
        <?php if ($isTutor): ?>
        <button class="btn-primary" onclick="openAddModal()"><i class="fa fa-plus"></i> Créer un Quiz</button>
        <?php endif; ?>
      </div>

      <div class="quiz-grid" id="quiz-grid">
        <?php foreach ($listQuizzes as $q): 
            $dClass = $domainClasses[$q['domaine']] ?? '';
            $dIcon  = $domainIcons[$q['domaine']] ?? 'fa-brain';
        ?>
        <div class="quiz-card" data-title="<?= strtolower($q['titre']) ?>">
            <div class="quiz-card-header <?= $dClass ?>">
                <div class="category-badge">
                    <i class="fas <?= $dIcon ?>"></i>
                    <?= htmlspecialchars($q['domaine']) ?>
                </div>
                <div class="online-pill">En ligne</div>
                <i class="fas fa-brain brain-pattern"></i>
                <div style="text-align:center;">
                   <i class="fas <?= $dIcon ?>" style="font-size: 64px; opacity: 0.2;"></i>
                </div>

                <?php if ($isTutor): ?>
                <div class="quiz-admin-actions">
                    <div class="admin-action-circle" onclick="openEditModal(<?= $q['id_quiz'] ?>, '<?= addslashes($q['titre']) ?>', '<?= addslashes($q['domaine']) ?>', '<?= addslashes($q['niveau']) ?>')" title="Modifier">
                        <i class="fa fa-pen"></i>
                    </div>
                    <div class="admin-action-circle del" onclick="openDeleteModal(<?= $q['id_quiz'] ?>, '<?= addslashes($q['titre']) ?>')" title="Supprimer">
                        <i class="fa fa-trash"></i>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <div class="quiz-card-content">
                <h3 class="quiz-title"><?= htmlspecialchars($q['titre']) ?></h3>
                <ul class="quiz-info-list">
                    <li class="quiz-info-item"><i class="fas fa-graduation-cap"></i> Niveau : <?= htmlspecialchars($q['niveau']) ?></li>
                    <li class="quiz-info-item"><i class="fas fa-list-check"></i> <?= $q['question_count'] ?> Questions interactives</li>
                    <li class="quiz-info-item"><i class="fas fa-clock"></i> Durée estimée : 10-15 min</li>
                </ul>
            </div>

            <div class="quiz-card-footer">
                <div class="price-tag">Gratuit</div>
                <button class="btn-start" onclick="window.location.href='start-quiz.php?id=<?= $q['id_quiz'] ?>'">
                    Commencer <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- MODALS (Hidden by default) -->
  <?php if ($isTutor): ?>
  <div class="modal-overlay" id="quiz-modal">
    <div class="modal">
      <div class="modal-header">
        <p class="modal-title" id="modal-title">Action Quiz</p>
        <button class="modal-close" onclick="closeModal('quiz-modal')"><i class="fa fa-xmark"></i></button>
      </div>
      <form method="POST" action="quiz-admin.php">
        <div class="modal-body">
          <input type="hidden" name="action_save" value="1">
          <input type="hidden" name="quiz_id" id="f-quiz-id" value="">
          <div class="form-group"><label class="form-label">Titre du quiz *</label><input class="form-input" id="f-titre" name="titre" type="text" required></div>
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Domaine *</label>
              <select class="form-input" id="f-domaine" name="domaine" required>
                <option value="">— Choisir —</option>
                <?php foreach($domainClasses as $d => $c): ?><option><?= $d ?></option><?php endforeach; ?>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">Niveau *</label>
              <select class="form-input" id="f-niveau" name="niveau" required>
                <option value="">— Choisir —</option><option>Débutant</option><option>Intermédiaire</option><option>Avancé</option>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer"><button type="button" class="btn-cancel" onclick="closeModal('quiz-modal')">Annuler</button><button type="submit" class="btn-primary">Enregistrer</button></div>
      </form>
    </div>
  </div>

  <div class="modal-overlay" id="delete-modal">
    <div class="modal">
      <div class="modal-header"><p class="modal-title" style="color:var(--danger)">Suppression</p><button class="modal-close" onclick="closeModal('delete-modal')"><i class="fa fa-xmark"></i></button></div>
      <div class="modal-body"><p>Confirmer la suppression de <strong id="delete-name"></strong> ?</p></div>
      <div class="modal-footer"><button class="btn-cancel" onclick="closeModal('delete-modal')">Annuler</button><button class="btn-danger" onclick="confirmDelete()">Supprimer</button></div>
    </div>
  </div>
  <?php endif; ?>

  <div class="toast-container" id="toast-container"></div>

  <script>
    let deleteTarget = null;
    function openAddModal() { document.getElementById('modal-title').textContent='Nouveau quiz'; document.getElementById('f-quiz-id').value=''; document.getElementById('f-titre').value=''; document.getElementById('quiz-modal').classList.add('open'); }
    function openEditModal(id, t, d, n) { document.getElementById('modal-title').textContent='Modifier le quiz'; document.getElementById('f-quiz-id').value=id; document.getElementById('f-titre').value=t; document.getElementById('f-domaine').value=d; document.getElementById('f-niveau').value=n; document.getElementById('quiz-modal').classList.add('open'); }
    function openDeleteModal(id, t) { deleteTarget=id; document.getElementById('delete-name').textContent=t; document.getElementById('delete-modal').classList.add('open'); }
    function confirmDelete() { if(deleteTarget) window.location.href='?delete_id='+deleteTarget; }
    function closeModal(id) { document.getElementById(id).classList.remove('open'); }
    function filterGrid() {
      const q = document.getElementById('search-input').value.toLowerCase();
      document.querySelectorAll('.quiz-card').forEach(card => {
        card.style.display = card.dataset.title.includes(q) ? '' : 'none';
      });
    }
  </script>
</body>
</html>
