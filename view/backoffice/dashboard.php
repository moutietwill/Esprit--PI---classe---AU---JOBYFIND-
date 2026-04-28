<?php
require_once __DIR__ . "/../../config/Database.php";
require_once __DIR__ . "/../../controller/quizzController.php";
require_once __DIR__ . "/../../controller/questionController.php";
require_once __DIR__ . "/../../controller/submissionController.php";
$quizCtrl       = new QuizController();
$questionCtrl   = new QuestionController();
$submissionCtrl = new SubmissionController();

// Stats
$totalQuizzes     = 0;
$qRes = $quizCtrl->listQuiz();
if ($qRes) $totalQuizzes = $qRes->rowCount();

$totalQuestions   = 0;
$questRes = $questionCtrl->listQuestions();
if ($questRes) $totalQuestions = $questRes->rowCount();

$totalSubmissions = $submissionCtrl->countSubmissions();
$recentSubmissions = [];
$subRes = $submissionCtrl->listSubmissions();
if ($subRes) $recentSubmissions = $subRes->fetchAll(PDO::FETCH_ASSOC);

// --- EXPORT CSV LOGIC ---
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=submissions_jobyfind_' . date('Y-m-d') . '.csv');
    $output = fopen('php://output', 'w');
    // Header row
    fputcsv($output, ['ID', 'Utilisateur', 'Quiz', 'Score', 'Max Score', 'Date']);
    // Data rows
    foreach ($recentSubmissions as $row) {
        fputcsv($output, [
            $row['id_submission'],
            $row['user_name'],
            $row['quiz_title'],
            $row['score'],
            $row['max_score'],
            $row['date_submitted']
        ]);
    }
    fclose($output);
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord - Admin</title>
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
        <a href="dashboard.php" class="sidebar-link active"><i class="fas fa-chart-pie"></i> Dashboard</a>
        <a href="quiz-list-admin.php" class="sidebar-link"><i class="fas fa-list"></i> Gestion Quiz</a>
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
            <span>Pages</span> / <span class="current">Dashboard</span>
        </div>
        <div class="header-search">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Rechercher...">
        </div>
    </div>

    <div class="content">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon blue"><i class="fas fa-tasks"></i></div>
                <div>
                    <div class="stat-label">Total Quiz</div>
                    <div class="stat-value"><?= $totalQuizzes ?></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green"><i class="fas fa-question"></i></div>
                <div>
                    <div class="stat-label">Questions</div>
                    <div class="stat-value"><?= $totalQuestions ?></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon amber"><i class="fas fa-user-graduate"></i></div>
                <div>
                    <div class="stat-label">Participations</div>
                    <div class="stat-value"><?= $totalSubmissions ?></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon purple"><i class="fas fa-star"></i></div>
                <div>
                    <div class="stat-label">Moyenne</div>
                    <div class="stat-value">--</div>
                </div>
            </div>
        </div>

        <div class="table-card">
        <div class="table-header">
                <div>
                    <div class="table-title">Réponses Reçues (Récentes)</div>
                    <div class="table-subtitle">Liste des derniers utilisateurs ayant passé un quiz</div>
                </div>
                <button class="btn-outline-sm" onclick="window.location.href='?export=csv'">Tout exporter</button>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Utilisateur</th>
                        <th>Quiz</th>
                        <th>Score</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentSubmissions)): ?>
                        <tr><td colspan="5" class="empty-state">Aucune réponse reçue pour le moment.</td></tr>
                    <?php else: ?>
                        <?php foreach ($recentSubmissions as $sub): ?>
                            <?php 
                                $perc = round(($sub['score'] / ($sub['max_score']?:1)) * 100);
                                $badgeClass = ($perc >= 80) ? 'badge-green' : (($perc >= 50) ? 'badge-blue' : 'badge-amber');
                            ?>
                            <tr>
                                <td>
                                    <div class="user-cell">
                                        <div class="user-avatar" style="background:#e2e8f0; color:#475569;"><?= substr($sub['user_name'], 0, 1) ?></div>
                                        <div>
                                            <div class="user-name"><?= htmlspecialchars($sub['user_name']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($sub['quiz_title']) ?></td>
                                <td>
                                    <span class="badge <?= $badgeClass ?>">
                                        <?= $sub['score'] ?> / <?= $sub['max_score'] ?> (<?= $perc ?>%)
                                    </span>
                                </td>
                                <td><?= date("d/m/Y H:i", strtotime($sub['date_submitted'])) ?></td>
                                <td>
                                    <div class="action-btns">
                                        <a href="submission-view-admin.php?id=<?= $sub['id_submission'] ?>" class="action-btn view" title="Détails" style="text-decoration:none">
                                            <i class="fas fa-eye"></i>
                                        </a>
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
</div>

</body>
</html>
