<?php
$score    = isset($_GET['score']) ? (int)$_GET['score'] : 0;
$max      = isset($_GET['max']) ? (int)$_GET['max'] : 0;
$quizName = isset($_GET['quiz']) ? $_GET['quiz'] : "Quiz";

$percentage = ($max > 0) ? round(($score / $max) * 100) : 0;

$message = "Bien joué !";
$icon = "fa-trophy";
$color = "#22c55e"; // Success green

if ($percentage < 50) {
    $message = "Continuez vos efforts !";
    $icon = "fa-redo";
    $color = "#f59e0b"; // Warning amber
} elseif ($percentage >= 80) {
    $message = "Excellent travail !";
    $icon = "fa-award";
    $color = "#2d79ff"; // Brand blue
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Résultat - <?= htmlspecialchars($quizName) ?> - Jobyfind</title>
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
            --radius: 16px;
        }

        * { margin:0; padding:0; box-sizing:border-box; }
        body { 
            font-family: 'DM Sans', sans-serif; 
            background: var(--bg); 
            display: flex; 
            flex-direction: column;
            min-height: 100vh;
        }

        /* ── NAVBAR ── */
        .navbar {
            background: var(--surface);
            height: 70px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 40px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .logo {
            font-family: 'DM Serif Display', serif;
            font-size: 24px;
            color: var(--navy);
            text-decoration: none;
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
        }
        .nav-link.active { color: var(--blue); }

        .main-content {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }

        .result-card { 
            background: var(--surface); 
            padding: 50px; 
            border-radius: var(--radius); 
            box-shadow: 0 20px 40px rgba(11, 31, 75, 0.08); 
            max-width: 550px; 
            width: 100%; 
            text-align: center; 
            border: 1px solid var(--border);
        }

        .icon-container { 
            width: 100px; 
            height: 100px; 
            background: <?= $color ?>; 
            color: white; 
            border-radius: 50%; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            font-size: 48px; 
            margin: 0 auto 30px; 
            box-shadow: 0 10px 20px <?= $color ?>44; 
        }

        h1 { font-family: 'DM Serif Display', serif; margin: 0 0 10px; color: var(--navy); font-size: 36px; }
        .quiz-title { color: var(--muted); font-size: 18px; margin-bottom: 40px; }

        .score-box {
            background: #f8fafc;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 30px;
            border: 1px solid var(--border);
        }
        .score-display { font-size: 72px; font-weight: 800; color: <?= $color ?>; line-height: 1; }
        .score-total { font-size: 24px; color: var(--muted); margin-top: 10px; }

        .percentage-container { margin-bottom: 40px; text-align: left; }
        .percentage-label { font-size: 14px; font-weight: 600; color: var(--navy); margin-bottom: 8px; display: flex; justify-content: space-between; }
        .percentage-bar { background: #e2e8f0; height: 12px; border-radius: 99px; overflow: hidden; }
        .percentage-fill { background: <?= $color ?>; height: 100%; width: <?= $percentage ?>%; transition: width 1s ease-out; }

        .btn-home { 
            display: inline-flex; 
            align-items: center; 
            gap: 10px;
            padding: 16px 40px; 
            background: var(--navy); 
            color: white; 
            text-decoration: none; 
            border-radius: 12px; 
            font-weight: 600; 
            transition: all 0.2s; 
        }
        .btn-home:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(11, 31, 75, 0.2); }
    </style>
</head>
<body>

<nav class="navbar">
    <a href="quizzes-list.php" class="logo">Joby<span>find</span></a>
    
    <div class="nav-links">
        <a href="#" class="nav-link">Formations</a>
        <a href="quizzes-list.php" class="nav-link active">Quiz</a>
        <a href="#" class="nav-link">À propos</a>
        <a href="#" class="nav-link">Contact</a>
    </div>

    <div></div>
</nav>

<div class="main-content">
    <div class="result-card">
        <div class="icon-container">
            <i class="fas <?= $icon ?>"></i>
        </div>
        <h1><?= $message ?></h1>
        <p class="quiz-title"><?= htmlspecialchars($quizName) ?></p>

        <div class="score-box">
            <div class="score-display"><?= $score ?></div>
            <div class="score-total">Points sur <?= $max ?></div>
        </div>

        <div class="percentage-container">
            <div class="percentage-label">
                <span>Progression</span>
                <span><?= $percentage ?>%</span>
            </div>
            <div class="percentage-bar">
                <div class="percentage-fill"></div>
            </div>
        </div>

        <a href="quizzes-list.php" class="btn-home"><i class="fas fa-arrow-left"></i> Explorer d'autres Quiz</a>
    </div>
</div>

</body>
</html>
