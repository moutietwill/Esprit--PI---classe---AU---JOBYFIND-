<?php
require_once __DIR__ . "/../../config/Database.php";
require_once __DIR__ . "/../../model/quizz.php";
require_once __DIR__ . "/../../controller/quizzController.php";
require_once __DIR__ . "/../../controller/questionController.php";

$quizCtrl     = new QuizController();
$questionCtrl = new QuestionController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add' || $action === 'edit') {
        $quiz = new Quiz($_POST['titre'], $_POST['domaine'], $_POST['niveau']); // FIXED: was "Quizz"
        if ($action === 'add') {
            $quizCtrl->addQuiz($quiz);
            $msg = "success";
        } else {
            $quizCtrl->updateQuiz($quiz, (int)$_POST['id_quiz']);
            $msg = "updated";
        }
        header("Location: quiz-list-admin.php?msg=" . $msg);
        exit;
    } elseif ($action === 'delete') {
        $quizCtrl->deleteQuiz((int)$_POST['id_quiz']);
        header("Location: quiz-list-admin.php?msg=deleted");
        exit;
    }
}

$listQuizzes = [];
$result = $quizCtrl->listQuiz();
if ($result) $listQuizzes = $result->fetchAll(PDO::FETCH_ASSOC);

$selectedQuiz = null;
if (isset($_GET['edit'])) {
    $selectedQuiz = $quizCtrl->getQuiz((int)$_GET['edit']);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion Quiz - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="admin-theme.css">
</head>
<body>

<div class="sidebar">
    <div class="sidebar-logo">
        <div class="logo-icon"><i class="fas fa-brain"></i></div>
        <div class="logo-text">Joby<span>find</span> Admin</div>
    </div>
    
    <div class="sidebar-section">
        <div class="sidebar-section-label">Menu Principal</div>
        <a href="dashboard.php" class="sidebar-link"><i class="fas fa-chart-pie"></i> Dashboard</a>
        <a href="quiz-list-admin.php" class="sidebar-link active"><i class="fas fa-list"></i> Gestion Quiz</a>
        <a href="question-admin.php" class="sidebar-link"><i class="fas fa-question-circle"></i> Questions</a>
        <a href="reponse-admin.php" class="sidebar-link"><i class="fas fa-check-circle"></i> Réponses</a>
    </div>

    <div class="sidebar-section">
        <div class="sidebar-section-label">Résultats</div>
        <a href="submissions-admin.php" class="sidebar-link"><i class="fas fa-users-viewfinder"></i> Participations</a>
    </div>

    <div class="sidebar-section">
        <div class="sidebar-section-label">Externe</div>
        <a href="../frontoffice/quizzes-list.php" class="sidebar-link"><i class="fas fa-eye"></i> Voir le Site</a>
    </div>

    <div class="sidebar-footer">
        <div class="admin-profile">
            <div class="admin-avatar">AD</div>
            <div class="admin-info">
                <p>Administrateur</p>
                <span>Chef de projet</span>
            </div>
        </div>
    </div>
</div>

<div class="main">
    <div class="header">
        <div class="header-breadcrumb">
            <span>Pages</span> / <span class="current">Gestion Quiz</span>
        </div>
        <div class="header-search">
            <i class="fas fa-search"></i>
            <input type="text" id="searchInput" placeholder="Chercher par titre, domaine ou niveau..." onkeyup="filterQuizTable()">
        </div>
        <div class="header-actions">
            <button class="btn-primary" onclick="openAddModal()">
                <i class="fas fa-plus"></i> Nouveau Quiz
            </button>
        </div>
    </div>

    <div class="content">
        <?php if (isset($_GET['msg'])): ?>
            <?php 
                $alertClass = ($_GET['msg'] === 'deleted') ? 'badge-red' : 'badge-green';
                $msgText = match($_GET['msg']) {
                    'success' => 'Quiz ajouté avec succès !',
                    'updated' => 'Quiz mis à jour !',
                    'deleted' => 'Quiz supprimé !',
                    default => ''
                };
            ?>
        <?php endif; ?>
        <div class="table-card">
            <div class="table-header">
                <div>
                    <div class="table-title">Liste des Quiz</div>
                    <div class="table-subtitle">Consulter et gérer vos catégories de quiz</div>
                </div>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Titre</th>
                        <th>Domaine</th>
                        <th>Niveau</th>
                        <th>Questions</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($listQuizzes)): ?>
                        <tr><td colspan="6" class="empty-state">Aucun quiz créé pour le moment.</td></tr>
                    <?php else: ?>
                        <?php foreach ($listQuizzes as $q): ?>
                        <tr>
                            <td><?= htmlspecialchars($q['titre']) ?></td>
                            <td><span class="badge badge-blue"><?= htmlspecialchars($q['domaine']) ?></span></td>
                            <td><span class="badge badge-gray"><?= htmlspecialchars($q['niveau']) ?></span></td>
                            <td><?= $questionCtrl->countByQuiz($q['id_quiz']) ?></td>
                            <td><?= date("d/m/Y", strtotime($q['dateCreation'])) ?></td>
                            <td>
                                <div class="action-btns">
                                    <button onclick="openEditModal(<?= $q['id_quiz'] ?>, '<?= htmlspecialchars($q['titre'], ENT_QUOTES) ?>', '<?= htmlspecialchars($q['domaine'], ENT_QUOTES) ?>', '<?= htmlspecialchars($q['niveau'], ENT_QUOTES) ?>')" class="action-btn edit" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="action-btn del" onclick="openDeleteModal(<?= $q['id_quiz'] ?>, '<?= htmlspecialchars($q['titre'], ENT_QUOTES) ?>')" title="Supprimer">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL ADD/EDIT -->
<div id="modalOverlay" class="modal-overlay">
    <div class="modal">
        <div class="modal-header">
            <h2 class="modal-title" id="modalTitle">Nouveau Quiz</h2>
            <button class="modal-close" onclick="closeModal()"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" id="quizForm" onsubmit="return validateForm(event)">
            <div class="modal-body">
                <input type="hidden" name="action" id="form-action" value="add">
                <input type="hidden" name="id_quiz" id="form-id" value="">
                
                <div class="form-group">
                    <label class="form-label">Titre *</label>
                    <input type="text" name="titre" id="titre" class="form-input" placeholder="Titre du quiz">
                    <span id="err-titre" class="error" style="color:red; font-size:12px;"></span>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Domaine *</label>
                        <select name="domaine" id="domaine" class="form-input">
                            <option value="">-- Sélectionner --</option>
                            <option>Marketing</option><option>Finance</option>
                            <option>Management</option><option>Tech & Dev</option>
                            <option>RH</option><option>Entrepreneuriat</option>
                        </select>
                        <span id="err-domaine" class="error" style="color:red; font-size:12px;"></span>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Niveau *</label>
                        <select name="niveau" id="niveau" class="form-input">
                            <option value="">-- Sélectionner --</option>
                            <option>Débutant</option>
                            <option>Intermédiaire</option>
                            <option>Avancé</option>
                        </select>
                        <span id="err-niveau" class="error" style="color:red; font-size:12px;"></span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeModal()">Annuler</button>
                <button type="submit" class="btn-primary">Enregistrer</button>
            </div>
        </form>
    </div>
</div>

<!-- DELETE MODAL -->
<div id="deleteModal" class="modal-overlay">
    <div class="modal" style="max-width: 400px;">
        <div class="modal-header">
            <h2 class="modal-title">Supprimer le Quiz</h2>
            <button class="modal-close" onclick="closeDeleteModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <p>Êtes-vous sûr de vouloir supprimer le quiz "<strong id="delete-name"></strong>" ?</p>
            <p style="margin-top:10px; font-size:12px; color:#ef4444;">Cette action supprimera également toutes les questions associées.</p>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id_quiz" id="delete-id">
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeDeleteModal()">Annuler</button>
                <button type="submit" class="btn-danger">Supprimer définitivement</button>
            </div>
        </form>
    </div>
</div>

<script>
let deleteId = null;

function validateForm(e) {
    let ok = true;
    document.querySelectorAll('.error').forEach(el => el.textContent = '');
    
    if (document.getElementById('titre').value.trim().length < 2) {
        document.getElementById('err-titre').textContent = 'Titre trop court';
        ok = false;
    }
    if (!document.getElementById('domaine').value) {
        document.getElementById('err-domaine').textContent = 'Domaine requis';
        ok = false;
    }
    if (!document.getElementById('niveau').value) {
        document.getElementById('err-niveau').textContent = 'Niveau requis';
        ok = false;
    }
    
    if (!ok) e.preventDefault();
    return ok;
}

function filterQuizTable() {
    let input = document.getElementById("searchInput").value.toLowerCase();
    let rows = document.querySelectorAll("tbody tr");
    rows.forEach(row => {
        if(row.querySelector(".empty-state")) return;
        
        let text = row.innerText.toLowerCase();
        
        if (text.includes(input)) {
            row.style.display = "";
        } else {
            row.style.display = "none";
        }
    });
}

function openAddModal() {
    document.getElementById('modalOverlay').classList.add('open');
    document.getElementById('modalTitle').textContent = 'Nouveau Quiz';
    document.getElementById('form-action').value = 'add';
    document.getElementById('form-id').value = '';
    document.getElementById('quizForm').reset();
}

function openEditModal(id, titre, domaine, niveau) {
    document.getElementById('modalOverlay').classList.add('open');
    document.getElementById('modalTitle').textContent = 'Modifier le Quiz';
    document.getElementById('form-action').value = 'edit';
    document.getElementById('form-id').value = id;
    document.getElementById('titre').value = titre;
    document.getElementById('domaine').value = domaine;
    document.getElementById('niveau').value = niveau;
}

function closeModal() {
    document.getElementById('modalOverlay').classList.remove('open');
}

function openDeleteModal(id, name) {
    deleteId = id;
    document.getElementById('deleteModal').classList.add('open');
    document.getElementById('delete-id').value = id;
    document.getElementById('delete-name').innerText = name;
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.remove('open');
}

<?php if ($selectedQuiz): ?>
document.addEventListener('DOMContentLoaded', function() {
    openEditModal(
        '<?= $selectedQuiz['id_quiz'] ?>',
        '<?= htmlspecialchars($selectedQuiz['titre'], ENT_QUOTES) ?>',
        '<?= htmlspecialchars($selectedQuiz['domaine'], ENT_QUOTES) ?>',
        '<?= htmlspecialchars($selectedQuiz['niveau'], ENT_QUOTES) ?>'
    );
});
<?php endif; ?>
</script>
</body>
</html>
