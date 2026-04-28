<?php
require_once __DIR__ . "/../../config/Database.php";
require_once __DIR__ . "/../../controller/submissionController.php";

$submissionCtrl = new SubmissionController();
$listSubmissions = [];
$res = $submissionCtrl->listSubmissions();
if ($res) $listSubmissions = $res->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Participations - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="admin-theme.css">
    <style>
        th.sortable { cursor: pointer; user-select: none; transition: background 0.2s; white-space: nowrap; }
        th.sortable:hover { background-color: #f8fafc; color: #1d4ed8; }
        .sort-icon { margin-left: 5px; color: #cbd5e1; font-size: 12px; }
        .active-sort-asc i { color: #3b82f6; }
        .active-sort-desc i { color: #3b82f6; transform: rotate(180deg); }
        .sort-options { display: inline-flex; gap: 5px; margin-left:8px; }
        .sort-btn { background: none; border: none; cursor: pointer; color: #94a3b8; font-size: 14px; padding: 0 2px;}
        .sort-btn:hover, .sort-btn.active { color: #3b82f6; }
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
            <span>Résultats</span> / <span class="current">Participations</span>
        </div>
        <div class="header-search">
            <i class="fas fa-search"></i>
            <input type="text" id="searchInput" placeholder="Filtrer les participations..." onkeyup="filterTable()">
        </div>
    </div>

    <div class="content">
        <div class="table-card">
            <div class="table-header">
                <div>
                    <div class="table-title">Historique des participations</div>
                    <div class="table-subtitle">Toutes les soumissions de quiz enregistrées</div>
                </div>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>
                            Utilisateur
                            <span class="sort-options">
                                <button class="sort-btn" onclick="sortTable('prenom', this)" title="Trier par Prénom (A-Z)"><i class="fas fa-sort-alpha-down"></i></button>
                                <button class="sort-btn" onclick="sortTable('nom', this)" title="Trier par Nom (A-Z)"><i class="fas fa-sort-alpha-up"></i></button>
                            </span>
                        </th>
                        <th class="sortable" onclick="sortTable('quiz', this)">Quiz <i class="fas fa-sort sort-icon"></i></th>
                        <th>Score FINAL</th>
                        <th class="sortable" onclick="sortTable('date', this)">Date de passage <i class="fas fa-sort sort-icon"></i></th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($listSubmissions)): ?>
                        <tr><td colspan="5" class="empty-state">Aucune participation n'a été trouvée.</td></tr>
                    <?php else: ?>
                        <?php foreach ($listSubmissions as $s): ?>
                            <tr>
                                <td>
                                    <div class="user-cell">
                                        <div class="user-avatar"><?= substr($s['user_name'], 0, 1) ?></div>
                                        <div class="user-name"><?= htmlspecialchars($s['user_name']) ?></div>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($s['quiz_title']) ?></td>
                                <td data-score="<?= $s['max_score'] > 0 ? ($s['score'] / $s['max_score']) : 0 ?>">
                                    <span class="badge badge-blue">
                                        <?= $s['score'] ?> / <?= $s['max_score'] ?>
                                    </span>
                                </td>
                                <td data-date="<?= strtotime($s['date_submitted']) ?>"><?= date("d/m/Y H:i", strtotime($s['date_submitted'])) ?></td>
                                <td>
                                    <div class="action-btns">
                                        <a href="submission-view-admin.php?id=<?= $s['id_submission'] ?>" class="action-btn view" title="Voir Détails" style="text-decoration:none">
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

<script>
let sortDir = { prenom: 'asc', nom: 'asc', quiz: 'asc', date: 'asc' };

function filterTable() {
    let input = document.getElementById("searchInput").value.toLowerCase();
    let rows = document.querySelectorAll("tbody tr");
    rows.forEach(row => {
        if(row.querySelector(".empty-state")) return;
        
        let userName = row.cells[0].innerText.toLowerCase();
        let quizTitle = row.cells[1].innerText.toLowerCase();
        let datePassage = row.cells[3].innerText.toLowerCase();
        
        let textToSearch = userName + " " + quizTitle + " " + datePassage;
        row.style.display = textToSearch.includes(input) ? "" : "none";
    });
}

function sortTable(column, headerElem) {
    let tbody = document.querySelector("tbody");
    let rows = Array.from(tbody.querySelectorAll("tr"));
    if(rows.length === 0 || rows[0].querySelector(".empty-state")) return;

    document.querySelectorAll(".sortable").forEach(th => th.classList.remove("active-sort-asc", "active-sort-desc"));
    document.querySelectorAll(".sort-btn").forEach(btn => btn.classList.remove("active"));
    
    let dir = sortDir[column] === 'asc' ? 'desc' : 'asc';
    sortDir[column] = dir;
    
    if(column === 'prenom' || column === 'nom') {
        headerElem.classList.add("active");
        headerElem.querySelector('i').className = dir === 'asc' ? "fas fa-sort-alpha-down" : "fas fa-sort-alpha-down-alt";
    } else {
        headerElem.classList.add(dir === 'asc' ? "active-sort-asc" : "active-sort-desc");
    }

    rows.sort((a, b) => {
        let valA, valB;
        if(column === 'prenom' || column === 'nom') {
            let nameA = a.querySelector(".user-name").innerText.trim().toLowerCase();
            let nameB = b.querySelector(".user-name").innerText.trim().toLowerCase();
            let partsA = nameA.split(/\s+/);
            let partsB = nameB.split(/\s+/);
            
            if(column === 'prenom') {
                valA = partsA[0] || "";
                valB = partsB[0] || "";
            } else { // nom
                valA = partsA.length > 1 ? partsA[partsA.length - 1] : partsA[0];
                valB = partsB.length > 1 ? partsB[partsB.length - 1] : partsB[0];
            }
        } 
        else if (column === 'quiz') {
            valA = a.cells[1].innerText.trim().toLowerCase();
            valB = b.cells[1].innerText.trim().toLowerCase();
        } 
        else if (column === 'date') {
            valA = parseInt(a.cells[3].getAttribute('data-date')) || 0;
            valB = parseInt(b.cells[3].getAttribute('data-date')) || 0;
        }

        if(valA < valB) return dir === 'asc' ? -1 : 1;
        if(valA > valB) return dir === 'asc' ? 1 : -1;
        return 0;
    });

    rows.forEach(row => tbody.appendChild(row));
}
</script>

</body>
</html>
