<?php
require_once __DIR__ . "/../../config/Database.php";
require_once __DIR__ . "/../../model/quizz.php";
require_once __DIR__ . "/../../controller/quizzController.php";
require_once __DIR__ . "/../../controller/questionController.php";

$quizCtrl     = new QuizController();
$questionCtrl = new QuestionController();

$listQuizzes = [];
$result = $quizCtrl->listQuiz();
if ($result) $listQuizzes = $result->fetchAll(PDO::FETCH_ASSOC);

$counts = [];
foreach ($listQuizzes as &$quiz) {
    $quiz['question_count'] = $questionCtrl->countByQuiz($quiz['id_quiz']);
    $domaine = $quiz['domaine'];
    if (!isset($counts[$domaine])) $counts[$domaine] = 0;
    $counts[$domaine]++;
}
unset($quiz);
$totalCount = count($listQuizzes);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jobyfind — Quiz</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Serif+Display&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --blue: #2d79ff;
            --navy: #0b1f4b;
            --bg: #f8fafc;
            --surface: #ffffff;
            --border: #e2e8f0;
            --text: #1e293b;
            --muted: #64748b;
            --radius: 12px;
        }

        * { margin:0; padding:0; box-sizing:border-box; }
        body { 
            font-family: 'DM Sans', sans-serif; 
            background: var(--bg); 
            color: var(--text);
            line-height: 1.5;
        }

        /* ── NAVBAR ── */
        .navbar {
            background: var(--surface);
            height: 70px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 40px;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .logo {
            font-family: 'DM Serif Display', serif;
            font-size: 24px;
            color: var(--navy);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .logo span { color: var(--blue); }

        .nav-links {
            display: flex;
            gap: 30px;
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
        }
        .nav-link {
            text-decoration: none;
            color: var(--text);
            font-weight: 500;
            font-size: 15px;
            transition: color 0.2s;
        }
        .nav-link:hover, .nav-link.active { color: var(--blue); }

        .nav-actions {
            display: flex;
            gap: 12px;
            align-items: center;
        }
        .btn-nav {
            padding: 9px 20px;
            border-radius: 99px;
            font-weight: 600;
            font-size: 14px;
            text-decoration: none;
            transition: all 0.2s;
        }
        .btn-outline { border: 1px solid var(--blue); color: var(--blue); }
        .btn-outline:hover { background: var(--blue); color: #fff; }
        .btn-filled { background: var(--blue); color: #fff; }
        .btn-filled:hover { opacity: 0.9; }
        .btn-admin { background: var(--navy); color: #fff; }

        .container { max-width: 1200px; margin: 40px auto; padding: 0 20px; }

        /* ── FILTERS ── */
        .filters {
            display: flex;
            gap: 12px;
            margin-bottom: 40px;
            flex-wrap: wrap;
            justify-content: center;
        }
        .filter-pill {
            background: var(--surface);
            padding: 10px 20px;
            border-radius: 99px;
            border: 1px solid var(--border);
            color: var(--text);
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }
        .filter-pill .count {
            background: #f1f5f9;
            color: var(--muted);
            padding: 2px 8px;
            border-radius: 99px;
            font-size: 11px;
        }
        .filter-pill:hover, .filter-pill.active {
            background: var(--blue);
            color: #fff;
            border-color: var(--blue);
            box-shadow: 0 4px 12px rgba(45, 121, 255, 0.3);
        }
        .filter-pill.active .count { background: rgba(255,255,255,0.2); color: #fff; }

        /* ── GRID & CARDS ── */
        .quiz-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 30px;
        }

        .quiz-card {
            background: var(--surface);
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05), 0 2px 4px -1px rgba(0,0,0,0.03);
            transition: transform 0.3s, box-shadow 0.3s;
            display: flex;
            flex-direction: column;
            border: 1px solid var(--border);
        }
        .quiz-card:hover { transform: translateY(-5px); box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); }

        .card-banner {
            height: 180px;
            background: #e2e8f0;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card-banner i { font-size: 64px; color: rgba(0,0,0,0.1); }
        
        .card-tag {
            position: absolute;
            top: 15px; left: 15px;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .tag-marketing { background: #dbeafe; color: #1e4ed8; }
        .tag-tech { background: #dcfce7; color: #15803d; }
        .tag-finance { background: #fef3c7; color: #92400e; }
        .tag-rh { background: #fdf2f8; color: #be185d; }
        .tag-default { background: #f1f5f9; color: #475569; }

        .card-status {
            position: absolute;
            top: 15px; right: 15px;
            padding: 4px 10px;
            background: rgba(0,0,0,0.6);
            backdrop-filter: blur(4px);
            color: #fff;
            border-radius: 8px;
            font-size: 11px;
            font-weight: 600;
        }

        .card-content { padding: 25px; flex-grow: 1; display: flex; flex-direction: column; }
        .card-title { font-size: 20px; font-weight: 600; color: var(--navy); margin-bottom: 15px; }
        
        .card-meta { display: flex; flex-direction: column; gap: 10px; margin-bottom: 20px; }
        .meta-item { display: flex; align-items: center; gap: 10px; color: var(--muted); font-size: 13.5px; }
        .meta-item i { width: 14px; text-align: center; color: var(--blue); }

        .card-footer {
            padding: 20px 25px;
            border-top: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .card-price { font-size: 18px; font-weight: 700; color: var(--blue); }
        .btn-start {
            padding: 10px 24px;
            background: var(--blue);
            color: #fff;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: background 0.2s;
        }
        .btn-start:hover { background: #1e56bd; }

        .empty-state {
            text-align: center;
            padding: 100px 0;
            background: #fff;
            border-radius: var(--radius);
            border: 1px dashed var(--border);
        }

        @media (max-width: 900px) {
            .nav-links { display: none; }
            .navbar { padding: 0 20px; }
        }
    </style>
</head>
<body>

<nav class="navbar">
    <a href="#" class="logo">Joby<span>find</span></a>
    
    <div class="nav-links">
        <a href="#" class="nav-link">Formations</a>
        <a href="quizzes-list.php" class="nav-link active">Quiz</a>
        <a href="#" class="nav-link">À propos</a>
        <a href="#" class="nav-link">Contact</a>
    </div>

    <div class="nav-actions">
        <a href="#" class="btn-nav btn-outline">Connexion</a>
        <a href="#" class="btn-nav btn-filled">S'inscrire</a>
        <a href="../backoffice/dashboard.php" class="btn-nav btn-admin"><i class="fas fa-lock"></i> Admin</a>
    </div>
</nav>

<div class="container">
    <div class="filters">
        <button class="filter-pill active" onclick="filterByDomain(this,'')">
            <i class="fas fa-folder-open"></i> Tout voir <span class="count"><?= $totalCount ?></span>
        </button>
        <?php foreach ($counts as $domaine => $count): ?>
            <?php 
                $icon = 'fa-tag';
                if ($domaine == 'Marketing') $icon = 'fa-chart-line';
                if ($domaine == 'Tech' || $domaine == 'Tech & Dev') $icon = 'fa-laptop-code';
                if ($domaine == 'Finance') $icon = 'fa-hand-holding-dollar';
            ?>
            <button class="filter-pill" onclick="filterByDomain(this, '<?= $domaine ?>')">
                <i class="fas <?= $icon ?>"></i> <?= htmlspecialchars($domaine) ?> <span class="count"><?= $count ?></span>
            </button>
        <?php endforeach; ?>
    </div>

    <?php if (empty($listQuizzes)): ?>
        <div class="empty-state">
            <i class="fas fa-search" style="font-size: 48px; color: #cbd5e1; margin-bottom: 20px;"></i>
            <h2 style="color: var(--navy);">Aucun quiz disponible</h2>
            <p style="color: var(--muted);">Revenez bientôt pour de nouveaux tests !</p>
        </div>
    <?php else: ?>
        <div class="quiz-grid" id="quiz-grid">
            <?php foreach ($listQuizzes as $quiz): ?>
                <?php 
                    $dom = strtolower($quiz['domaine']);
                    $tagClass = 'tag-default';
                    if (str_contains($dom, 'mark')) $tagClass = 'tag-marketing';
                    elseif (str_contains($dom, 'tech') || str_contains($dom, 'dev')) $tagClass = 'tag-tech';
                    elseif (str_contains($dom, 'fin')) $tagClass = 'tag-finance';
                    elseif (str_contains($dom, 'rh')) $tagClass = 'tag-rh';

                    $bannerIcon = 'fa-brain';
                    if ($tagClass == 'tag-marketing') $bannerIcon = 'fa-bullhorn';
                    if ($tagClass == 'tag-tech') $bannerIcon = 'fa-code';
                    if ($tagClass == 'tag-finance') $bannerIcon = 'fa-coins';
                ?>
                <div class="quiz-card" data-domain="<?= htmlspecialchars($quiz['domaine']) ?>">
                    <div class="card-banner" style="background: <?= ($tagClass == 'tag-marketing' ? '#eff6ff' : ($tagClass == 'tag-tech' ? '#f0fdf4' : '#fffbeb')) ?>;">
                        <span class="card-tag <?= $tagClass ?>">
                            <i class="fas <?= ($tagClass == 'tag-marketing' ? 'fa-chart-pie' : ($tagClass == 'tag-tech' ? 'fa-microchip' : 'fa-vault')) ?>"></i>
                            <?= htmlspecialchars($quiz['domaine']) ?>
                        </span>
                        <span class="card-status">En ligne</span>
                        <i class="fas <?= $bannerIcon ?>"></i>
                    </div>
                    <div class="card-content">
                        <h3 class="card-title"><?= htmlspecialchars($quiz['titre']) ?></h3>
                        <div class="card-meta">
                            <div class="meta-item">
                                <i class="fas fa-graduation-cap"></i>
                                <span>Niveau : <?= htmlspecialchars($quiz['niveau']) ?></span>
                            </div>
                            <div class="meta-item">
                                <i class="fas fa-list-check"></i>
                                <span><?= $quiz['question_count'] ?> Questions interactives</span>
                            </div>
                            <div class="meta-item">
                                <i class="fas fa-clock"></i>
                                <span>Durée estimée : 10-15 min</span>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="card-price">Gratuit</div>
                        <a href="quiz-take.php?id=<?= $quiz['id_quiz'] ?>" class="btn-start">Commencer <i class="fas fa-arrow-right" style="font-size: 11px; margin-left: 5px;"></i></a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
function filterByDomain(btn, domain) {
    document.querySelectorAll('.filter-pill').forEach(p => p.classList.remove('active'));
    btn.classList.add('active');
    
    document.querySelectorAll('.quiz-card').forEach(card => {
        if (!domain || card.dataset.domain === domain) {
            card.style.display = 'flex';
        } else {
            card.style.display = 'none';
        }
    });
}
</script>

</body>
</html>
