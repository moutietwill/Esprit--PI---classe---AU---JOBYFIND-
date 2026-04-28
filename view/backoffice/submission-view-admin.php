<?php
require_once __DIR__ . "/../../config/Database.php";
require_once __DIR__ . "/../../controller/submissionController.php";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$submissionCtrl = new SubmissionController();
$submission = $submissionCtrl->getSubmission($id);

if (!$submission) {
    header("Location: submissions-admin.php");
    exit;
}

$details = $submissionCtrl->getSubmissionDetails($id);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails Participation - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="admin-theme.css">
    <style>
        .submission-header { background: white; padding: 25px; border-radius: 12px; margin-bottom: 25px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; }
        .header-item .label { color: #64748b; font-size: 13px; margin-bottom: 5px; display: block; }
        .header-item .value { color: #1e293b; font-weight: 600; font-size: 16px; }
        .score-big { font-size: 24px; color: #3b82f6; }
        .q-card { background: white; padding: 20px; border-radius: 12px; margin-bottom: 15px; border-left: 5px solid #e2e8f0; }
        .q-card.correct { border-left-color: #22c55e; }
        .q-card.wrong { border-left-color: #ef4444; }
        .q-text { font-weight: 600; margin-bottom: 10px; color: #1e293b; }
        .user-ans { display: flex; align-items: center; gap: 10px; padding: 10px; border-radius: 8px; background: #f8fafc; }
        .status-icon { width: 20px; }
    </style>
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
        <a href="reponse-admin.php" class="sidebar-link"><i class="fas fa-check-circle"></i> Réponses</a>
    </div>

    <div class="sidebar-section">
        <div class="sidebar-section-label">Résultats</div>
        <a href="submissions-admin.php" class="sidebar-link active"><i class="fas fa-users-viewfinder"></i> Participations</a>
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
            <span>Résultats</span> / <a href="submissions-admin.php" style="text-decoration:none; color:inherit;">Participations</a> / <span class="current">Détails</span>
        </div>
        <div class="header-actions">
            <a href="submissions-admin.php" class="btn-outline-sm" style="text-decoration:none">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
    </div>

    <div class="content">
        <div class="submission-header">
            <div class="header-item">
                <span class="label">Utilisateur</span>
                <span class="value"><?= htmlspecialchars($submission['user_name']) ?></span>
            </div>
            <div class="header-item">
                <span class="label">Quiz passé</span>
                <span class="value"><?= htmlspecialchars($submission['quiz_title']) ?></span>
            </div>
            <div class="header-item">
                <span class="label">Date</span>
                <span class="value"><?= date("d/m/Y H:i", strtotime($submission['date_submitted'])) ?></span>
            </div>
            <div class="header-item">
                <span class="label">Score</span>
                <span class="value score-big"><?= $submission['score'] ?> / <?= $submission['max_score'] ?></span>
            </div>
        </div>

        <h3 style="margin-bottom: 20px; color: #1e293b;">Détail des réponses</h3>

        <?php foreach ($details as $d): ?>
            <?php $isCorrect = (bool)$d['est_correcte']; ?>
            <div class="q-card <?= $isCorrect ? 'correct' : 'wrong' ?>">
                <div class="q-text"><?= htmlspecialchars($d['question_text']) ?></div>
                <div class="user-ans">
                    <div class="status-icon">
                        <?= $isCorrect ? '<i class="fas fa-check-circle" style="color:#22c55e"></i>' : '<i class="fas fa-times-circle" style="color:#ef4444"></i>' ?>
                    </div>
                    <div>
                        <span style="font-size:12px; color:#64748b; display:block">Réponse donnée :</span>
                        <span style="font-weight:500"><?= htmlspecialchars($d['reponse_text']) ?></span>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

</body>
</html>
