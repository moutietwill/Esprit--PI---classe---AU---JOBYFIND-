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

// Statistiques par domaine
$statsDomaine = [];
$totalQuiz = 0;
foreach ($listQuizzes as $q) {
    $d = $q['domaine'];
    if (!isset($statsDomaine[$d])) $statsDomaine[$d] = 0;
    $statsDomaine[$d]++;
    $totalQuiz++;
}
arsort($statsDomaine);

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
        <div class="header-actions" style="display:flex; gap:12px;">
            <button class="btn-primary" style="background: linear-gradient(135deg, #a855f7, #6366f1); border:none; box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3); padding:8px 16px; border-radius:8px; color:white; font-weight:600; font-family:inherit; font-size:14px; display:flex; align-items:center; gap:8px; cursor:pointer;" onclick="openAIModal()">
                <i class="fas fa-magic"></i> Jobyfind AI Spark
            </button>
            <button style="background:#fff; color:#0b1f4b; border:1px solid #e2e8f0; padding:8px 16px; border-radius:8px; cursor:pointer; font-weight:600; font-family:inherit; font-size:14px; display:flex; align-items:center; gap:8px;" onclick="openStatsModal()">
                <i class="fas fa-chart-pie" style="color:#2d79ff;"></i> Statistiques
            </button>
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

<!-- STATS MODAL -->
<style>
@keyframes slideInUp {
    from { opacity: 0; transform: translateY(15px); }
    to { opacity: 1; transform: translateY(0); }
}
@keyframes fillBar {
    from { width: 0%; }
    to { width: var(--final-width); }
}
</style>
<div id="statsModal" class="modal-overlay">
    <div class="modal" style="max-width: 480px; padding:0; overflow:hidden; border:none; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);">
        <div class="modal-header" style="background: linear-gradient(135deg, #2d79ff, #624bfe); color: white; border-bottom: none; padding: 24px; position:relative;">
            <div style="display:flex; justify-content:space-between; align-items:flex-start; width:100%; position:relative; z-index:10;">
                <div>
                    <h2 class="modal-title" style="color: white; margin:0; font-size: 20px; display:flex; align-items:center; gap: 10px; font-weight:700;">
                        <i class="fas fa-chart-pie" style="color: white; opacity:0.9;"></i> Statistiques Globale
                    </h2>
                    <p style="color: rgba(255,255,255,0.8); font-size:13.5px; margin: 6px 0 0 0; font-weight:400;">Aperçu de la répartition de vos quiz par domaine</p>
                </div>
                <button class="modal-close" style="color:white; opacity:0.8; background:rgba(255,255,255,0.15); border-radius:50%; width:32px; height:32px; display:flex; align-items:center; justify-content:center; border:none; cursor:pointer; transition:all 0.2s; position:static;" onmouseover="this.style.background='rgba(255,255,255,0.3)'" onmouseout="this.style.background='rgba(255,255,255,0.15)'" onclick="closeStatsModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <!-- Floating aesthetic shapes -->
            <div style="position:absolute; right:-20px; bottom:-20px; opacity:0.1; font-size:100px; line-height:1; pointer-events:none; z-index:0;"><i class="fas fa-chart-bar"></i></div>
        </div>
        <div class="modal-body" style="padding: 24px 28px; background: #fff;">
            
            <div style="display:flex; justify-content:space-between; margin-bottom: 28px; padding:18px; background:#f8fafc; border-radius:12px; border:1px solid #e2e8f0; box-shadow:0 2px 4px rgba(0,0,0,0.02);">
                 <div style="text-align:center; flex:1; border-right:1px solid #e2e8f0;">
                     <div style="font-size:26px; font-weight:700; color:#0b1f4b; line-height:1; margin-bottom:4px;"><?= $totalQuiz ?></div>
                     <div style="font-size:11px; color:#64748b; font-weight:700; text-transform:uppercase; letter-spacing:0.8px;">Total Quiz</div>
                 </div>
                 <div style="text-align:center; flex:1;">
                     <div style="font-size:26px; font-weight:700; color:#2d79ff; line-height:1; margin-bottom:4px;"><?= count($statsDomaine) ?></div>
                     <div style="font-size:11px; color:#64748b; font-weight:700; text-transform:uppercase; letter-spacing:0.8px;">Domaines</div>
                 </div>
            </div>

            <div style="display:flex; flex-direction:column; gap:20px;">
                <?php $delay = 0; foreach($statsDomaine as $dom => $count): ?>
                    <?php 
                        $percent = $totalQuiz > 0 ? round(($count / $totalQuiz) * 100) : 0; 
                        $domLower = strtolower($dom);
                        // Default theme styling
                        $color1 = '#3b82f6'; $color2 = '#60a5fa'; $bg = '#eff6ff'; $icon = 'fa-folder';
                        if (str_contains($domLower, 'mark')) { $color1 = '#8b5cf6'; $color2 = '#a78bfa'; $bg = '#f5f3ff'; $icon='fa-bullhorn'; }
                        elseif (str_contains($domLower, 'tech') || str_contains($domLower, 'dev')) { $color1 = '#10b981'; $color2 = '#34d399'; $bg = '#ecfdf5'; $icon='fa-microchip'; }
                        elseif (str_contains($domLower, 'fin')) { $color1 = '#f59e0b'; $color2 = '#fbbf24'; $bg = '#fffbeb'; $icon='fa-vault'; }
                        elseif (str_contains($domLower, 'rh')) { $color1 = '#ec4899'; $color2 = '#f472b6'; $bg = '#fdf2f8'; $icon='fa-users'; }
                        elseif (str_contains($domLower, 'manag')) { $color1 = '#6366f1'; $color2 = '#818cf8'; $bg = '#eef2ff'; $icon='fa-briefcase'; }
                    ?>
                    <div style="animation: slideInUp 0.5s ease-out forwards; animation-delay: <?= $delay ?>s; opacity:0; transform:translateY(15px);">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                            <span style="font-weight:600; font-size:14.5px; color:#0b1f4b; display:flex; align-items:center; gap:10px;">
                                <span style="display:flex; align-items:center; justify-content:center; width:32px; height:32px; border-radius:8px; background:<?= $bg ?>; color:<?= $color1 ?>; font-size:14px;">
                                    <i class="fas <?= $icon ?>"></i>
                                </span>
                                <?= htmlspecialchars($dom) ?>
                            </span>
                            <span style="font-weight:700; font-size:14px; color:#1e293b;"><?= $count ?> <span style="font-weight:500; color:#94a3b8; font-size:12px; margin-left:4px;">(<?= $percent ?>%)</span></span>
                        </div>
                        <div style="width: 100%; height: 8px; background: #f1f5f9; border-radius: 99px; overflow: hidden; position:relative;">
                            <div style="position:absolute; left:0; top:0; height: 100%; width: 0%; background: linear-gradient(90deg, <?= $color1 ?>, <?= $color2 ?>); border-radius: 99px; box-shadow: 0 2px 4px <?= str_replace(')', ', 0.3)', str_replace('rgb', 'rgba', hex2rgb($color1) ?? 'rgba(0,0,0,0.1)')) ?>; animation: fillBar 1.2s cubic-bezier(0.16, 1, 0.3, 1) forwards; animation-delay: <?= $delay + 0.1 ?>s; --final-width: <?= $percent ?>%;"></div>
                        </div>
                    </div>
                    <?php $delay += 0.08; endforeach; ?>
                    
                    <?php if(empty($statsDomaine)): ?>
                        <div style="text-align:center; padding:40px 0; color:#64748b;">
                            <div style="width:64px; height:64px; background:#f1f5f9; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 16px auto;">
                                <i class="fas fa-inbox" style="font-size:28px; color:#cbd5e1;"></i>
                            </div>
                            <p style="margin:0; font-weight:500; font-size:15px; color:#475569;">Aucune donnée disponible</p>
                            <p style="margin:6px 0 0 0; font-size:13px; color:#94a3b8;">Créez un quiz pour voir ses statistiques ici.</p>
                        </div>
                    <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php 
// Helper if hex2rgb doesn't exist natively
function hex2rgb($hex) {
    $hex = str_replace("#", "", $hex);
    if(strlen($hex) == 3) {
        $r = hexdec(substr($hex,0,1).substr($hex,0,1));
        $g = hexdec(substr($hex,1,1).substr($hex,1,1));
        $b = hexdec(substr($hex,2,1).substr($hex,2,1));
    } else {
        $r = hexdec(substr($hex,0,2));
        $g = hexdec(substr($hex,2,2));
        $b = hexdec(substr($hex,4,2));
    }
    return "rgba($r, $g, $b, 0.3)";
}
?>

<!-- AI MODAL -->
<div id="aiModal" class="modal-overlay">
    <div class="modal" style="max-width: 500px; padding:0; overflow:hidden; border:none; box-shadow:0 25px 50px -12px rgba(99, 102, 241, 0.4);">
        <div class="modal-header" style="background: linear-gradient(135deg, #a855f7, #6366f1); color: white; border-bottom: none; padding: 24px; position:relative;">
            <div style="display:flex; justify-content:space-between; align-items:flex-start; width:100%; position:relative; z-index:10;">
                <div>
                    <h2 class="modal-title" style="color: white; margin:0; font-size: 20px; display:flex; align-items:center; gap: 10px; font-weight:700;">
                        <i class="fas fa-sparkles" style="color: #fde047;"></i> Jobyfind AI Spark
                    </h2>
                    <p style="color: rgba(255,255,255,0.9); font-size:13.5px; margin: 6px 0 0 0; font-weight:400;">Générez des quiz entiers en quelques secondes de magie.</p>
                </div>
                <button class="modal-close" style="color:white; opacity:0.8; background:rgba(255,255,255,0.2); border-radius:50%; width:32px; height:32px; display:flex; align-items:center; justify-content:center; border:none; cursor:pointer;" onclick="closeAIModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        <div class="modal-body" style="padding: 24px 28px; background: #fff;" id="ai-form-container">
            <form id="aiForm" onsubmit="generateAIQuiz(event)">
                <div class="form-group" style="margin-bottom:16px;">
                    <label class="form-label" style="font-weight:600; color:#0b1f4b; display:block; margin-bottom:8px;">Sujet du quiz *</label>
                    <input type="text" id="ai_sujet" class="form-input" style="width:100%; padding:10px; border:1px solid #e2e8f0; border-radius:8px;" placeholder="Ex: Les bases de la cybersécurité">
                    <span id="err-ai-sujet" class="error-msg" style="color:#ef4444; font-size:12px; display:none;">Le sujet est obligatoire.</span>
                </div>
                
                <div style="display:flex; gap:16px; margin-bottom:16px;">
                    <div class="form-group" style="flex:1;">
                        <label class="form-label" style="font-weight:600; color:#0b1f4b; display:block; margin-bottom:8px;">Domaine *</label>
                        <select id="ai_domaine" class="form-input" style="width:100%; padding:10px; border:1px solid #e2e8f0; border-radius:8px;">
                            <option value="">Sélectionner</option>
                            <option>Tech & Dev</option><option>Marketing</option>
                            <option>Finance</option><option>RH</option>
                            <option>Management</option><option>Entrepreneuriat</option>
                        </select>
                        <span id="err-ai-domaine" class="error-msg" style="color:#ef4444; font-size:12px; display:none;">Requis.</span>
                    </div>
                    <div class="form-group" style="flex:1;">
                        <label class="form-label" style="font-weight:600; color:#0b1f4b; display:block; margin-bottom:8px;">Niveau *</label>
                        <select id="ai_niveau" class="form-input" style="width:100%; padding:10px; border:1px solid #e2e8f0; border-radius:8px;">
                            <option value="">Sélectionner</option>
                            <option>Débutant</option><option>Intermédiaire</option><option>Avancé</option>
                        </select>
                        <span id="err-ai-niveau" class="error-msg" style="color:#ef4444; font-size:12px; display:none;">Requis.</span>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom:24px;">
                    <label class="form-label" style="font-weight:600; color:#0b1f4b; display:block; margin-bottom:8px;">Nombre de questions *</label>
                    <input type="number" id="ai_nb" class="form-input" style="width:100%; padding:10px; border:1px solid #e2e8f0; border-radius:8px;" min="1" max="10" value="5">
                    <span id="err-ai-nb" class="error-msg" style="color:#ef4444; font-size:12px; display:none;">Doit être > 0.</span>
                </div>

                <div style="display:flex; justify-content:flex-end; gap:12px;">
                    <button type="button" class="btn-cancel" style="padding:10px 16px; background:#f1f5f9; border:none; border-radius:8px; cursor:pointer; font-weight:600;" onclick="closeAIModal()">Annuler</button>
                    <button type="submit" class="btn-primary" style="padding:10px 24px; background:linear-gradient(135deg, #a855f7, #6366f1); border:none; border-radius:8px; color:white; font-weight:600; cursor:pointer; display:flex; align-items:center; gap:8px;">
                        <i class="fas fa-magic"></i> Lancer la machine
                    </button>
                </div>
            </form>
        </div>

        <div id="ai-loading-container" style="display:none; padding: 40px 28px; background: #fff; text-align:center;">
            <div style="margin:0 auto 20px auto; width:64px; height:64px; border-radius:50%; background: #e0e7ff; display:flex; align-items:center; justify-content:center; animation: pulse 1.5s infinite;">
                <i class="fas fa-brain" style="font-size:32px; color:#6366f1;"></i>
            </div>
            <h3 style="color:#0b1f4b; margin:0 0 8px 0; font-size:18px;">L'IA crée votre quiz...</h3>
            <p id="ai-loading-text" style="color:#64748b; margin:0; font-size:14px;"></p>
        </div>
    </div>
</div>

<style>
@keyframes pulse {
    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(99, 102, 241, 0.7); }
    70% { transform: scale(1); box-shadow: 0 0 0 15px rgba(99, 102, 241, 0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(99, 102, 241, 0); }
}
</style>

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

function openStatsModal() {
    document.getElementById('statsModal').classList.add('open');
}

function closeStatsModal() {
    document.getElementById('statsModal').classList.remove('open');
}

function openAIModal() {
    document.getElementById('aiModal').classList.add('open');
    document.getElementById('ai-form-container').style.display = 'block';
    document.getElementById('ai-loading-container').style.display = 'none';
    document.getElementById('aiForm').reset();
}

function closeAIModal() {
    document.getElementById('aiModal').classList.remove('open');
}

function generateAIQuiz(e) {
    e.preventDefault();
    
    // JS Validation strict (No HTML5 validation allowed)
    let isValid = true;
    let sujet = document.getElementById('ai_sujet').value.trim();
    let domaine = document.getElementById('ai_domaine').value;
    let niveau = document.getElementById('ai_niveau').value;
    let nb = document.getElementById('ai_nb').value;

    document.querySelectorAll('#aiForm .error-msg').forEach(el => el.style.display = 'none');

    if(sujet.length < 2) { document.getElementById('err-ai-sujet').style.display = 'block'; isValid = false; }
    if(domaine === "") { document.getElementById('err-ai-domaine').style.display = 'block'; isValid = false; }
    if(niveau === "") { document.getElementById('err-ai-niveau').style.display = 'block'; isValid = false; }
    if(nb < 1) { document.getElementById('err-ai-nb').style.display = 'block'; isValid = false; }

    if(!isValid) return false;

    document.getElementById('ai-form-container').style.display = 'none';
    document.getElementById('ai-loading-container').style.display = 'block';
    
    let loadingTexts = [
        "Analyse de la thématique...",
        "Génération des questions pièges...",
        "Validation des réponses correctes...",
        "Formatage final en base de données..."
    ];
    let i = 0;
    document.getElementById('ai-loading-text').innerText = loadingTexts[0];
    let textInterval = setInterval(() => {
        i = (i + 1) % loadingTexts.length;
        document.getElementById('ai-loading-text').innerText = loadingTexts[i];
    }, 1000);

    let formData = new FormData();
    formData.append('action', 'generate_quiz');
    formData.append('sujet', sujet);
    formData.append('domaine', domaine);
    formData.append('niveau', niveau);
    formData.append('nb_questions', nb);

    fetch('../../controller/aiController.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        clearInterval(textInterval);
        if(data.status === 'success') {
            window.location.href = 'quiz-list-admin.php?msg=success';
        } else {
            alert("Erreur IA : " + data.message);
            closeAIModal();
        }
    })
    .catch(err => {
        clearInterval(textInterval);
        alert("Erreur réseau ou d'API.");
        closeAIModal();
    });
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
