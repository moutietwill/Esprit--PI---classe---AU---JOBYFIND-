<?php
require_once __DIR__ . "/../../config/Database.php";
require_once __DIR__ . "/../../model/reponse.php";
require_once __DIR__ . "/../../controller/reponseController.php";
require_once __DIR__ . "/../../controller/questionController.php";

$reponseCtrl  = new ReponseController();
$questionCtrl = new QuestionController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add' || $action === 'edit') {
        $reponse = new Reponse(
            (int)$_POST['id_question'],
            trim($_POST['texte']),
            isset($_POST['est_correcte']) ? 1 : 0,
            trim($_POST['justification'] ?? '')
        );
        if ($action === 'add') {
            $reponseCtrl->addReponse($reponse);
            $msg = "success";
        } else {
            $reponseCtrl->updateReponse($reponse, (int)$_POST['id_reponse']);
            $msg = "updated";
        }
        header("Location: reponse-admin.php?msg=" . $msg);
        exit;
    } elseif ($action === 'delete') {
        $reponseCtrl->deleteReponse((int)$_POST['id_reponse']);
        header("Location: reponse-admin.php?msg=deleted");
        exit;
    }
}

$listReponses = [];
$result = $reponseCtrl->listReponsesWithContext();
if ($result) $listReponses = $result->fetchAll(PDO::FETCH_ASSOC);

// Récupérer toutes les questions pour le select
$allQuestions = [];
$qResult = $questionCtrl->listQuestions();
if ($qResult) $allQuestions = $qResult->fetchAll(PDO::FETCH_ASSOC);

$selectedReponse = null;
if (isset($_GET['edit'])) {
    $selectedReponse = $reponseCtrl->getReponse((int)$_GET['edit']);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion Réponses - Admin</title>
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
        <a href="quiz-list-admin.php" class="sidebar-link"><i class="fas fa-list"></i> Gestion Quiz</a>
        <a href="question-admin.php" class="sidebar-link"><i class="fas fa-question-circle"></i> Questions</a>
        <a href="reponse-admin.php" class="sidebar-link active"><i class="fas fa-check-circle"></i> Réponses</a>
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
            <span>Pages</span> / <span class="current">Gestion Réponses</span>
        </div>
        <div class="header-actions">
            <button class="btn-primary" onclick="openAddModal()">
                <i class="fas fa-plus"></i> Nouvelle Réponse
            </button>
        </div>
    </div>
    <div class="content">

    <?php if (isset($_GET['msg'])): ?>
        <?php 
            $alertClass = ($_GET['msg'] === 'deleted') ? 'badge-red' : 'badge-green';
            $msgText = match($_GET['msg']) {
                'success' => 'Réponse ajoutée avec succès !',
                'updated' => 'Réponse mise à jour !',
                'deleted' => 'Réponse supprimée !',
                default => ''
            };
        ?>
        <div class="badge <?= $alertClass ?>" style="padding:10px; width:100%; margin-bottom:20px; justify-content:center;">
            <i class="fas fa-info-circle"></i> <?= $msgText ?>
        </div>
    <?php endif; ?>

    <div class="table-card">
        <div class="table-header">
            <div>
                <div class="table-title">Liste des Réponses</div>
                <div class="table-subtitle">Gérez les options pour vos questions de quiz</div>
            </div>
        </div>
        <table>
            <thead>
                <tr><th>Réponse</th><th>Correcte</th><th>Question</th><th>Justification</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php if (empty($listReponses)): ?>
                    <tr><td colspan="5" class="empty-state">Aucune réponse trouvée.</td></tr>
                <?php else: ?>
                    <?php foreach ($listReponses as $r): ?>
                    <tr>
                        <td><span style="font-weight:500; color:#1e293b;"><?= htmlspecialchars($r['texte']) ?></span></td>
                        <td>
                            <?php if ($r['est_correcte']): ?>
                                <span class="badge badge-green"><i class="fas fa-check"></i> Oui</span>
                            <?php else: ?>
                                <span class="badge badge-red"><i class="fas fa-times"></i> Non</span>
                            <?php endif; ?>
                        </td>
                        <td><span class="badge badge-blue">#<?= $r['id_question'] ?> - <?= htmlspecialchars(substr($r['question_enonce'] ?? '', 0, 30)) ?>...</span></td>
                        <td><span style="font-size:12px; color:#64748b;"><?= htmlspecialchars($r['justification'] ?? '-') ?></span></td>
                        <td>
                            <div class="action-btns">
                                <button onclick="openEditModal(<?= $r['id_reponse'] ?>, <?= $r['id_question'] ?>, '<?= htmlspecialchars($r['texte'], ENT_QUOTES) ?>', '<?= htmlspecialchars($r['justification'] ?? '', ENT_QUOTES) ?>', <?= $r['est_correcte'] ? 'true' : 'false' ?>)" class="action-btn edit" title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="action-btn del" onclick="openDeleteModal(<?= $r['id_reponse'] ?>, '<?= htmlspecialchars(substr($r['texte'], 0, 30), ENT_QUOTES) ?>')" title="Supprimer">
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
            <h2 class="modal-title" id="modalTitle">Nouvelle Réponse</h2>
            <button class="modal-close" onclick="closeModal()"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" id="reponseForm" onsubmit="return validateForm(event)">
            <div class="modal-body">
                <input type="hidden" name="action" id="form-action" value="add">
                <input type="hidden" name="id_reponse" id="form-id" value="">

                <div class="form-group">
                    <label class="form-label">Question Associée *</label>
                    <select name="id_question" id="id_question" class="form-input">
                        <option value="">-- Sélectionner une question --</option>
                        <?php foreach ($allQuestions as $q): ?>
                        <option value="<?= $q['id_question'] ?>">#<?= $q['id_question'] ?> - <?= htmlspecialchars(substr($q['enonce'], 0, 80)) ?>...</option>
                        <?php endforeach; ?>
                    </select>
                    <span id="err-id_question" class="error" style="color:red; font-size:12px;"></span>
                </div>

                <div class="form-group">
                    <label class="form-label">Texte de la réponse *</label>
                    <textarea name="texte" id="texte" class="form-input" rows="3" placeholder="Saisissez la réponse..."></textarea>
                    <span id="err-texte" class="error" style="color:red; font-size:12px;"></span>
                </div>

                <div class="form-group">
                    <label class="form-label">Justification (Optionnel)</label>
                    <textarea name="justification" id="justification" class="form-input" rows="2" placeholder="Pourquoi cette réponse est correcte/fausse ?"></textarea>
                </div>

                <div style="display: flex; align-items: center; gap: 10px; margin-top: 15px; padding: 12px; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0;">
                    <input type="checkbox" name="est_correcte" id="est_correcte" style="width:16px; height:16px; cursor:pointer;">
                    <label for="est_correcte" style="font-weight:600; font-size:13px; color:#1e293b; cursor:pointer; margin:0;">Cette réponse est correcte</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeModal()">Annuler</button>
                <button type="submit" class="btn-primary">Enregistrer</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL DELETE -->
<div id="deleteModal" class="modal-overlay">
    <div class="modal" style="max-width: 400px;">
        <div class="modal-header">
            <h2 class="modal-title">Supprimer la Réponse</h2>
            <button class="modal-close" onclick="closeDeleteModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <p>Êtes-vous sûr de vouloir supprimer cette réponse ?</p>
            <p style="margin-top:10px; font-weight:600; color:#1e293b;" id="delete-msg"></p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-cancel" onclick="closeDeleteModal()">Annuler</button>
            <button type="button" class="btn-danger" onclick="confirmDelete()">Supprimer définitivement</button>
        </div>
    </div>
</div>

<script>
let deleteId = null;

function validateForm(e) {
    let ok = true;
    document.querySelectorAll('.error').forEach(el => el.textContent = '');
    
    if (!document.getElementById('id_question').value) { document.getElementById('err-id_question').textContent='Question requise'; ok=false; }
    if (document.getElementById('texte').value.trim().length < 1) { document.getElementById('err-texte').textContent='Le texte est requis'; ok=false; }
    
    if (!ok) e.preventDefault();
    return ok;
}

function openAddModal() {
    document.getElementById('modalOverlay').classList.add('open');
    document.getElementById('modalTitle').textContent = 'Nouvelle Réponse';
    document.getElementById('form-action').value = 'add';
    document.getElementById('form-id').value = '';
    document.getElementById('reponseForm').reset();
}

function openEditModal(id, id_question, texte, justification, est_correcte) {
    document.getElementById('modalOverlay').classList.add('open');
    document.getElementById('modalTitle').textContent = 'Modifier la Réponse';
    document.getElementById('form-action').value = 'edit';
    document.getElementById('form-id').value = id;
    document.getElementById('id_question').value = id_question;
    document.getElementById('texte').value = texte;
    document.getElementById('justification').value = justification;
    document.getElementById('est_correcte').checked = est_correcte;
}

function closeModal() { document.getElementById('modalOverlay').classList.remove('open'); }

function openDeleteModal(id, texte) {
    deleteId = id;
    document.getElementById('delete-msg').textContent = '"' + texte + '..."';
    document.getElementById('deleteModal').classList.add('open');
}

function closeDeleteModal() { document.getElementById('deleteModal').classList.remove('open'); }

function confirmDelete() {
    const form = document.createElement('form');
    form.method = 'POST';
    form.innerHTML = '<input type="hidden" name="action" value="delete"><input type="hidden" name="id_reponse" value="' + deleteId + '">';
    document.body.appendChild(form);
    form.submit();
}

<?php if ($selectedReponse): ?>
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('modalOverlay').classList.add('open');
    document.getElementById('modalTitle').textContent = 'Modifier la Réponse';
    document.getElementById('form-action').value = 'edit';
    document.getElementById('form-id').value = '<?= $selectedReponse['id_reponse'] ?>';
    document.getElementById('id_question').value = '<?= $selectedReponse['id_question'] ?>';
    document.getElementById('texte').value = '<?= htmlspecialchars($selectedReponse['texte'], ENT_QUOTES) ?>';
    document.getElementById('justification').value = '<?= htmlspecialchars($selectedReponse['justification'] ?? '', ENT_QUOTES) ?>';
    document.getElementById('est_correcte').checked = <?= $selectedReponse['est_correcte'] ? 'true' : 'false' ?>;
});
<?php endif; ?>
</script>
</body>
</html>
