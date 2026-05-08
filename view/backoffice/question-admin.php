<?php
require_once __DIR__ . "/../../config/Database.php";
require_once __DIR__ . "/../../model/question.php";
require_once __DIR__ . "/../../controller/questionController.php";
require_once __DIR__ . "/../../controller/quizzController.php";
require_once __DIR__ . "/../../controller/reponseController.php";
require_once __DIR__ . "/../../model/reponse.php";

$questionCtrl = new QuestionController();
$quizCtrl = new QuizController();
$reponseCtrl = new ReponseController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add' || $action === 'edit') {
        $question = new Question(
            (int) $_POST['id_quiz'],
            trim($_POST['enonce']),
            $_POST['type'],
            (int) $_POST['points']
        );
        if ($action === 'add') {
            $id_new_question = $questionCtrl->addQuestion($question);

            // Handle dynamic answers if provided
            if ($id_new_question) {
                if ($_POST['type'] === 'QCM' && isset($_POST['options']) && is_array($_POST['options'])) {
                    foreach ($_POST['options'] as $index => $texte) {
                        if (!empty(trim($texte))) {
                            $is_correct = (isset($_POST['is_correct']) && (int) $_POST['is_correct'] === $index) ? 1 : 0;
                            $reponse = new Reponse((int) $id_new_question, trim($texte), $is_correct);
                            $reponseCtrl->addReponse($reponse);
                        }
                    }
                } elseif ($_POST['type'] === 'Vrai/Faux' && isset($_POST['vf_correct'])) {
                    $correct_val = (int) $_POST['vf_correct'];
                    $vrai = new Reponse((int) $id_new_question, "Vrai", ($correct_val === 1 ? 1 : 0));
                    $faux = new Reponse((int) $id_new_question, "Faux", ($correct_val === 0 ? 1 : 0));
                    $reponseCtrl->addReponse($vrai);
                    $reponseCtrl->addReponse($faux);
                }
            }
            $msg = "success";
        } else {
            $questionCtrl->updateQuestion($question, (int) $_POST['id_question']);
            $id_question = (int) $_POST['id_question'];

            // Update answers logic
            $existingAnswers = $reponseCtrl->getReponsesByQuestion($id_question);
            $existingAnsIds = array_column($existingAnswers, 'id_reponse');
            $submittedAnsIds = [];

            if ($_POST['type'] === 'QCM' && isset($_POST['options']) && is_array($_POST['options'])) {
                foreach ($_POST['options'] as $index => $texte) {
                    if (!empty(trim($texte))) {
                        $is_correct = (isset($_POST['is_correct']) && (int) $_POST['is_correct'] === $index) ? 1 : 0;
                        $reponse = new Reponse($id_question, trim($texte), $is_correct);
                        $id_reponse = isset($_POST['id_options'][$index]) ? (int) $_POST['id_options'][$index] : 0;

                        if ($id_reponse > 0 && in_array($id_reponse, $existingAnsIds)) {
                            $reponseCtrl->updateReponse($reponse, $id_reponse);
                            $submittedAnsIds[] = $id_reponse;
                        } else {
                            $new_id = $reponseCtrl->addReponse($reponse);
                            if ($new_id)
                                $submittedAnsIds[] = $new_id;
                        }
                    }
                }
            } elseif ($_POST['type'] === 'Vrai/Faux' && isset($_POST['vf_correct'])) {
                $correct_val = (int) $_POST['vf_correct'];

                $id_vrai = isset($_POST['id_options'][0]) ? (int) $_POST['id_options'][0] : 0;
                $vrai = new Reponse($id_question, "Vrai", ($correct_val === 1 ? 1 : 0));
                if ($id_vrai > 0 && in_array($id_vrai, $existingAnsIds)) {
                    $reponseCtrl->updateReponse($vrai, $id_vrai);
                    $submittedAnsIds[] = $id_vrai;
                } else {
                    $new_id = $reponseCtrl->addReponse($vrai);
                    if ($new_id)
                        $submittedAnsIds[] = $new_id;
                }

                $id_faux = isset($_POST['id_options'][1]) ? (int) $_POST['id_options'][1] : 0;
                $faux = new Reponse($id_question, "Faux", ($correct_val === 0 ? 1 : 0));
                if ($id_faux > 0 && in_array($id_faux, $existingAnsIds)) {
                    $reponseCtrl->updateReponse($faux, $id_faux);
                    $submittedAnsIds[] = $id_faux;
                } else {
                    $new_id = $reponseCtrl->addReponse($faux);
                    if ($new_id)
                        $submittedAnsIds[] = $new_id;
                }
            }

            foreach ($existingAnsIds as $oldId) {
                if (!in_array((int) $oldId, $submittedAnsIds)) {
                    $reponseCtrl->deleteReponse($oldId);
                }
            }

            $msg = "updated";
        }
        header("Location: question-admin.php?msg=" . $msg);
        exit;
    } elseif ($action === 'delete') {
        $questionCtrl->deleteQuestion((int) $_POST['id_question']);
        header("Location: question-admin.php?msg=deleted");
        exit;
    }
}

$listQuestions = [];
$result = $questionCtrl->listQuestions();
if ($result)
    $listQuestions = $result->fetchAll(PDO::FETCH_ASSOC);

// Récupérer tous les quiz pour le select
$allQuizzes = [];
$qResult = $quizCtrl->listQuiz();
if ($qResult)
    $allQuizzes = $qResult->fetchAll(PDO::FETCH_ASSOC);

$selectedQuestion = null;
$selectedAnswers = [];
if (isset($_GET['edit'])) {
    $selectedQuestion = $questionCtrl->getQuestion((int) $_GET['edit']);
    if ($selectedQuestion) {
        $selectedAnswers = $reponseCtrl->getReponsesByQuestion($selectedQuestion['id_question']);
        usort($selectedAnswers, function ($a, $b) {
            return $a['id_reponse'] <=> $b['id_reponse']; });
    }
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion Questions - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="admin-theme.css">
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
            <a href="question-admin.php" class="sidebar-link active"><i class="fas fa-question-circle"></i>
                Questions</a>
            <a href="reponse-admin.php" class="sidebar-link"><i class="fas fa-check-circle"></i> Réponses</a>
        </div>

        <div class="sidebar-section">
            <div class="sidebar-section-label">Résultats</div>
            <a href="submissions-admin.php" class="sidebar-link"><i class="fas fa-users-viewfinder"></i>
                Participations</a>
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
                <span>Pages</span> / <span class="current">Gestion Questions</span>
            </div>
            <div class="header-actions">
                <button class="btn-primary" onclick="openAddModal()">
                    <i class="fas fa-plus"></i> Nouvelle Question
                </button>
            </div>
        </div>
        <div class="content">

            <?php if (isset($_GET['msg'])): ?>
                <?php
                $alertClass = ($_GET['msg'] === 'deleted') ? 'badge-red' : 'badge-green';
                $msgText = match ($_GET['msg']) {
                    'success' => 'Question ajoutée avec succès !',
                    'updated' => 'Question mise à jour !',
                    'deleted' => 'Question supprimée !',
                    default => ''
                };
                ?>
                <div class="badge <?= $alertClass ?>"
                    style="padding:10px; width:100%; margin-bottom:20px; justify-content:center;">
                    <i class="fas fa-info-circle"></i> <?= $msgText ?>
                </div>
            <?php endif; ?>

            <div class="table-card">
                <div class="table-header">
                    <div>
                        <div class="table-title">Liste des Questions</div>
                        <div class="table-subtitle">Gérez les questions pour vos quiz</div>
                    </div>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Énoncé</th>
                            <th>Type</th>
                            <th>Points</th>
                            <th>Quiz</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($listQuestions)): ?>
                            <tr>
                                <td colspan="6" class="empty-state">Aucune question créée pour le moment.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($listQuestions as $q): ?>
                                <tr>
                                    <td><?= htmlspecialchars(substr($q['enonce'], 0, 60)) ?><?= strlen($q['enonce']) > 60 ? '...' : '' ?>
                                    </td>
                                    <td><span class="badge badge-purple"><?= htmlspecialchars($q['type']) ?></span></td>
                                    <td><span class="badge badge-amber"><?= $q['points'] ?> pts</span></td>
                                    <td><span class="badge badge-blue">#<?= $q['id_quiz'] ?></span></td>
                                    <td><?= date("d/m/Y", strtotime($q['dateCreation'])) ?></td>
                                    <td>
                                        <div class="action-btns">
                                            <a href="?edit=<?= $q['id_question'] ?>" class="action-btn edit" title="Modifier"
                                                style="text-decoration:none"><i class="fas fa-edit"></i></a>
                                            <button class="action-btn del"
                                                onclick="openDeleteModal(<?= $q['id_question'] ?>, '<?= htmlspecialchars(substr($q['enonce'], 0, 30), ENT_QUOTES) ?>')"
                                                title="Supprimer">
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
        <div class="modal" style="width: 600px;">
            <div class="modal-header">
                <h2 class="modal-title" id="modalTitle">Nouvelle Question</h2>
                <button class="modal-close" onclick="closeModal()"><i class="fas fa-times"></i></button>
            </div>
            <form method="POST" id="questionForm" onsubmit="return validateForm(event)">
                <div class="modal-body">
                    <input type="hidden" name="action" id="form-action" value="add">
                    <input type="hidden" name="id_question" id="form-id" value="">

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Quiz *</label>
                            <select name="id_quiz" id="id_quiz" class="form-input">
                                <option value="">-- Sélectionner un quiz --</option>
                                <?php foreach ($allQuizzes as $quiz): ?>
                                    <option value="<?= $quiz['id_quiz'] ?>"><?= htmlspecialchars($quiz['titre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <span id="err-id_quiz" class="error" style="color:red; font-size:12px;"></span>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Points *</label>
                            <input type="text" name="points" id="points" class="form-input" placeholder="Ex: 2">
                            <span id="err-points" class="error" style="color:red; font-size:12px;"></span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Énoncé *</label>
                        <textarea name="enonce" id="enonce" class="form-input" rows="3"
                            placeholder="Saisissez la question..."></textarea>
                        <span id="err-enonce" class="error" style="color:red; font-size:12px;"></span>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Type *</label>
                        <select name="type" id="type" class="form-input" onchange="toggleOptions(this.value)">
                            <option value="">-- Sélectionner --</option>
                            <option value="QCM">QCM</option>
                            <option value="Texte">Texte</option>
                            <option value="Vrai/Faux">Vrai/Faux</option>
                        </select>
                        <span id="err-type" class="error" style="color:red; font-size:12px;"></span>
                    </div>

                    <!-- DYNAMIC OPTIONS FOR QCM -->
                    <div id="qcm-options"
                        style="display:none; padding:15px; background:#f8fafc; border-radius:10px; margin-top:15px; border:1px solid #e2e8f0;">
                        <label class="form-label">Options de Réponses (Minimum 2, Maximum 4)</label>
                        <div id="options-container"
                            style="display:flex; flex-direction:column; gap:10px; margin-bottom:15px;">
                            <div class="option-row" style="display:flex; gap:10px; align-items:center;">
                                <input type="hidden" name="id_options[]" value="">
                                <input type="radio" name="is_correct" value="0" checked>
                                <input type="text" name="options[]" class="form-input" placeholder="Option 1">
                            </div>
                            <div class="option-row" style="display:flex; gap:10px; align-items:center;">
                                <input type="hidden" name="id_options[]" value="">
                                <input type="radio" name="is_correct" value="1">
                                <input type="text" name="options[]" class="form-input" placeholder="Option 2">
                            </div>
                        </div>
                        <button type="button" class="btn-outline-sm btn-add-option" onclick="addOptionRow()"><i
                                class="fas fa-plus"></i> Ajouter une option</button>
                    </div>

                    <!-- DYNAMIC OPTIONS FOR VRAI/FAUX -->
                    <div id="vf-options"
                        style="display:none; padding:15px; background:#f8fafc; border-radius:10px; margin-top:15px; border:1px solid #e2e8f0;">
                        <label class="form-label">Sélectionnez la bonne réponse</label>
                        <div style="display: flex; gap: 20px;">
                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                <input type="radio" name="vf_correct" value="1" checked> Vrai
                            </label>
                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                <input type="radio" name="vf_correct" value="0"> Faux
                            </label>
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

    <!-- MODAL DELETE -->
    <div id="deleteModal" class="modal-overlay">
        <div class="modal" style="max-width: 400px;">
            <div class="modal-header">
                <h2 class="modal-title">Supprimer la Question</h2>
                <button class="modal-close" onclick="closeDeleteModal()"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <p>Êtes-vous sûr de vouloir supprimer la question :</p>
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

            if (!document.getElementById('id_quiz').value) { document.getElementById('err-id_quiz').textContent = 'Quiz requis'; ok = false; }
            if (document.getElementById('enonce').value.trim().length < 5) { document.getElementById('err-enonce').textContent = 'Énoncé trop court (5 car. min)'; ok = false; }

            const type = document.getElementById('type').value;
            if (!type) { document.getElementById('err-type').textContent = 'Type requis'; ok = false; }

            if (type === 'QCM') {
                const options = document.querySelectorAll('input[name="options[]"]');
                let filledOptions = 0;
                options.forEach(opt => { if (opt.value.trim().length > 0) filledOptions++; });
                if (filledOptions < 2) {
                    document.getElementById('err-type').textContent = 'Veuillez remplir au moins 2 options pour un QCM';
                    ok = false;
                }
            }

            const pointsStr = document.getElementById('points').value.trim();
            if (!pointsStr) {
                document.getElementById('err-points').textContent = 'Points requis';
                ok = false;
            } else {
                const pointsNum = parseInt(pointsStr, 10);
                if (isNaN(pointsNum) || pointsNum < 1 || pointsNum > 10) {
                    document.getElementById('err-points').textContent = 'Doit être un nombre entre 1 et 10';
                    ok = false;
                }
            }

            if (!ok) e.preventDefault();
            return ok;
        }

        function openAddModal() {
            document.getElementById('modalOverlay').classList.add('open');
            document.getElementById('modalTitle').textContent = 'Nouvelle Question';
            document.getElementById('form-action').value = 'add';
            document.getElementById('form-id').value = '';
            document.getElementById('questionForm').reset();
            document.getElementById('qcm-options').style.display = 'none';
            document.getElementById('vf-options').style.display = 'none';

            document.getElementById('options-container').innerHTML = `
        <div class="option-row" style="display:flex; gap:10px; align-items:center;">
            <input type="hidden" name="id_options[]" value="">
            <input type="radio" name="is_correct" value="0" checked>
            <input type="text" name="options[]" class="form-input" placeholder="Option 1">
        </div>
        <div class="option-row" style="display:flex; gap:10px; align-items:center;">
            <input type="hidden" name="id_options[]" value="">
            <input type="radio" name="is_correct" value="1">
            <input type="text" name="options[]" class="form-input" placeholder="Option 2">
        </div>
    `;
            updateAddButtonVisibility();
        }

        function toggleOptions(type) {
            document.getElementById('qcm-options').style.display = (type === 'QCM') ? 'block' : 'none';
            document.getElementById('vf-options').style.display = (type === 'Vrai/Faux') ? 'block' : 'none';
        }

        function addOptionRow() {
            const container = document.getElementById('options-container');
            const index = container.children.length;
            if (index >= 4) return;

            const row = document.createElement('div');
            row.className = 'option-row';
            row.style.display = 'flex';
            row.style.gap = '10px';
            row.style.alignItems = 'center';
            row.innerHTML = `
        <input type="hidden" name="id_options[]" value="">
        <input type="radio" name="is_correct" value="${index}">
        <input type="text" name="options[]" class="form-input" placeholder="Option ${index + 1}">
        <button type="button" class="action-btn del" onclick="this.parentElement.remove(); updateAddButtonVisibility();" title="Retirer"><i class="fas fa-times"></i></button>
    `;
            container.appendChild(row);
            updateAddButtonVisibility();
        }

        function updateAddButtonVisibility() {
            const count = document.getElementById('options-container').children.length;
            document.querySelector('.btn-add-option').style.display = (count >= 4) ? 'none' : 'block';
        }

        function closeModal() { document.getElementById('modalOverlay').classList.remove('open'); }

        function openDeleteModal(id, enonce) {
            deleteId = id;
            document.getElementById('delete-msg').textContent = enonce + '...';
            document.getElementById('deleteModal').classList.add('open');
        }

        function closeDeleteModal() { document.getElementById('deleteModal').classList.remove('open'); }

        function confirmDelete() {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = '<input type="hidden" name="action" value="delete"><input type="hidden" name="id_question" value="' + deleteId + '">';
            document.body.appendChild(form);
            form.submit();
        }

        <?php if ($selectedQuestion): ?>
            document.addEventListener('DOMContentLoaded', function () {
                document.getElementById('modalOverlay').classList.add('open');
                document.getElementById('modalTitle').textContent = 'Modifier la Question';
                document.getElementById('form-action').value = 'edit';
                document.getElementById('form-id').value = '<?= $selectedQuestion['id_question'] ?>';
                document.getElementById('id_quiz').value = '<?= $selectedQuestion['id_quiz'] ?>';
                document.getElementById('enonce').value = '<?= htmlspecialchars($selectedQuestion['enonce'], ENT_QUOTES) ?>';
                document.getElementById('type').value = '<?= htmlspecialchars($selectedQuestion['type'], ENT_QUOTES) ?>';
                document.getElementById('points').value = '<?= $selectedQuestion['points'] ?>';

                // Population des reponses
                const originalType = '<?= $selectedQuestion['type'] ?>';
                const currentAnswers = <?= json_encode($selectedAnswers) ?>;

                if (originalType === 'QCM') {
                    const container = document.getElementById('options-container');
                    container.innerHTML = '';
                    currentAnswers.forEach((ans, idx) => {
                        const row = document.createElement('div');
                        row.className = 'option-row';
                        row.style.display = 'flex';
                        row.style.gap = '10px';
                        row.style.alignItems = 'center';
                        row.innerHTML = `
                <input type="hidden" name="id_options[]" value="${ans.id_reponse}">
                <input type="radio" name="is_correct" value="${idx}" ${ans.est_correcte == 1 ? 'checked' : ''}>
                <input type="text" name="options[]" class="form-input" value="${ans.texte.replace(/"/g, '&quot;')}">
                ${idx >= 2 ? '<button type="button" class="action-btn del" onclick="this.parentElement.remove(); updateAddButtonVisibility();"><i class="fas fa-times"></i></button>' : ''}
            `;
                    container.appendChild(row);
                });
                updateAddButtonVisibility();
            } else if (originalType === 'Vrai/Faux') {
                let vraiCorrect = 1;
                let idVrai = "";
                let idFaux = "";
                currentAnswers.forEach(ans => {
                    if (ans.texte.toLowerCase() === 'vrai') {
                        idVrai = ans.id_reponse;
                        if (ans.est_correcte == 1) vraiCorrect = 1;
                    } else if (ans.texte.toLowerCase() === 'faux') {
                        idFaux = ans.id_reponse;
                        if (ans.est_correcte == 1) vraiCorrect = 0;
                    }
                });
                document.querySelector('input[name="vf_correct"][value="1"]').checked = (vraiCorrect === 1);
                document.querySelector('input[name="vf_correct"][value="0"]').checked = (vraiCorrect === 0);

                let vfDiv = document.getElementById('vf-options');
                vfDiv.querySelectorAll('input[type="hidden"]').forEach(e => e.remove());
                vfDiv.insertAdjacentHTML('afterbegin', `<input type="hidden" name="id_options[]" value="${idVrai}"><input type="hidden" name="id_options[]" value="${idFaux}">`);
            }

            toggleOptions(originalType);
        });
        <?php endif; ?>
    </script>
</body>

</html>