<?php
require_once __DIR__ . "/../../config/Database.php";
require_once __DIR__ . "/../../controller/quizzController.php";
require_once __DIR__ . "/../../controller/questionController.php";
require_once __DIR__ . "/../../controller/reponseController.php";
require_once __DIR__ . "/../../controller/submissionController.php";

$quizId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$quizId) {
    header("Location: quizzes-list.php");
    exit;
}

$quizCtrl     = new QuizController();
$questionCtrl = new QuestionController();
$reponseCtrl  = new ReponseController();

$quiz = $quizCtrl->getQuiz($quizId);
if (!$quiz) {
    die("Quiz non trouvé");
}

$questions = $questionCtrl->getQuestionsByQuiz($quizId);
shuffle($questions); // Randomiser l'ordre des questions

$totalSeconds = count($questions) * 60; // 1 minute par question

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userName = trim($_POST['user_name'] ?? 'Anonyme');
    $score = 0;
    $maxScore = 0;

    foreach ($questions as $q) {
        $maxScore += $q['points'];
        $userAnswerId = $_POST['q_' . $q['id_question']] ?? null;
        
        if ($userAnswerId) {
            $correctReponse = $reponseCtrl->getCorrectReponseForQuestion($q['id_question']);
            if ($correctReponse && $correctReponse['id_reponse'] == $userAnswerId) {
                $score += $q['points'];
            }
        }
    }

    $submissionCtrl = new SubmissionController();
    $sub_id = $submissionCtrl->addSubmission($quizId, $userName, $score, $maxScore);

    // SAVE DETAILED ANSWERS
    if ($sub_id) {
        foreach ($questions as $q) {
            $userAnswerId = $_POST['q_' . $q['id_question']] ?? null;
            if ($userAnswerId) {
                $submissionCtrl->addSubmissionAnswer($sub_id, $q['id_question'], $userAnswerId);
            }
        }
    }

    // Redirect to results
    header("Location: quiz-result.php?score=$score&max=$maxScore&quiz=".urlencode($quiz['titre'])."&name=".urlencode($userName));
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($quiz['titre']) ?> - Jobyfind</title>
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
            padding-bottom: 50px;
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
            margin-bottom: 40px;
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

        .container { max-width: 800px; margin: 0 auto; padding: 0 20px; }

        .quiz-container { 
            background: var(--surface); 
            padding: 40px; 
            border-radius: var(--radius); 
            border: 1px solid var(--border);
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        }

        h1 { font-family: 'DM Serif Display', serif; color: var(--navy); margin-bottom: 10px; font-size: 32px; }
        .quiz-intro { color: var(--muted); margin-bottom: 30px; border-bottom: 1px solid var(--border); padding-bottom: 20px; }

        .form-group { margin-bottom: 30px; }
        .label-main { display: block; font-weight: 600; color: var(--navy); margin-bottom: 12px; font-size: 16px; }
        .input-text { width: 100%; padding: 12px 15px; border: 1px solid var(--border); border-radius: 8px; font-size: 15px; font-family: inherit; }
        .input-text:focus { outline: none; border-color: var(--blue); box-shadow: 0 0 0 3px rgba(45, 121, 255, 0.1); }

        .question-card { 
            background: #f8fafc; 
            padding: 25px; 
            border-radius: 12px; 
            margin-bottom: 20px; 
            border: 1px solid var(--border);
        }
        .question-text { font-size: 18px; font-weight: 600; color: var(--navy); margin-bottom: 20px; display: flex; gap: 12px; }
        .question-num { background: var(--blue); color: #fff; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 14px; flex-shrink: 0; }

        .options-list { display: flex; flex-direction: column; gap: 12px; }
        .option-item { 
            display: flex; 
            align-items: center; 
            padding: 12px 15px; 
            background: white; 
            border: 1px solid var(--border); 
            border-radius: 8px; 
            cursor: pointer; 
            transition: all 0.2s; 
        }
        .option-item:hover { border-color: var(--blue); background: #eff6ff; }
        .option-item input { margin-right: 15px; width: 18px; height: 18px; cursor: pointer; }
        .option-item span { font-size: 15px; color: var(--text); }

        .btn-submit { 
            width: 100%; 
            padding: 15px; 
            background: var(--blue); 
            color: white; 
            border: none; 
            border-radius: 8px; 
            font-size: 16px; 
            font-weight: 600; 
            cursor: pointer; 
            transition: background 0.2s; 
            margin-top: 20px;
        }
        .btn-submit:hover { background: #1e56bd; }

        .btn-secondary {
            display: block;
            text-align: center;
            padding: 12px;
            color: var(--muted);
            text-decoration: none;
            font-size: 14px;
            margin-top: 20px;
        }
        .btn-secondary:hover { background: #d1d5db; }

        .error-msg { color: #ef4444; font-size: 13px; margin-top: 5px; display: none; }

        /* ── PROGRESS BAR ── */
        .progress-sticky {
            position: sticky;
            top: 70px; /* Below Navbar */
            background: white;
            padding: 15px 40px;
            z-index: 900;
            border-bottom: 1px solid var(--border);
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 20px;
        }
        .progress-track {
            flex: 1;
            background: #e2e8f0;
            height: 10px;
            border-radius: 99px;
            overflow: hidden;
        }
        .progress-bar-fill {
            background: var(--blue);
            height: 100%;
            width: 0%;
            transition: width 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .progress-stats {
            font-size: 14px;
            font-weight: 600;
            color: var(--navy);
            min-width: 100px;
            text-align: right;
        }

        /* ── CHRONOMETER ── */
        .timer-box {
            display: flex;
            align-items: center;
            gap: 12px;
            background: #f1f5f9;
            padding: 10px 20px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 20px;
            color: var(--navy);
            border: 2px solid transparent;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        }
        
        /* Warning state (Orange) */
        .timer-box.warning {
            color: #f59e0b;
            background: #fffbeb;
            border-color: #f59e0b;
        }

        /* Critical state (Red + Pulse) */
        .timer-box.critical {
            color: #ef4444;
            background: #fef2f2;
            border-color: #ef4444;
            animation: pulse-danger 1s infinite;
        }

        @keyframes pulse-danger {
            0% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4); transform: scale(1); }
            50% { box-shadow: 0 0 0 10px rgba(239, 68, 68, 0); transform: scale(1.02); }
            100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); transform: scale(1); }
        }

        /* ── OVERLAY FIN DE TEMPS ── */
        .time-up-overlay {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(15, 23, 42, 0.9);
            z-index: 9999;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.5s;
        }
        .time-up-overlay.active {
            opacity: 1;
            pointer-events: auto;
        }
        .time-up-box {
            background: white;
            padding: 40px;
            border-radius: 20px;
            text-align: center;
            max-width: 400px;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);
            transform: scale(0.9);
            transition: transform 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        .time-up-overlay.active .time-up-box {
            transform: scale(1);
        }
        .time-up-icon {
            font-size: 60px;
            color: #ef4444;
            margin-bottom: 20px;
        }
        .time-up-title {
            font-family: 'DM Serif Display', serif;
            font-size: 28px;
            color: var(--navy);
            margin-bottom: 10px;
        }
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

    <div></div> <!-- Spacer for flexbox balance -->
</nav>

<div class="progress-sticky">
    <div id="timerDisplay" class="timer-box">
        <i class="fas fa-stopwatch"></i>
        <span id="timerText">--:--</span>
    </div>
    <div class="progress-track">
        <div id="progressBarFill" class="progress-bar-fill"></div>
    </div>
    <div id="progressText" class="progress-stats">0 / <?= count($questions) ?> répondus</div>
</div>

<div class="container">
    <div class="quiz-container">
        <h1><?= htmlspecialchars($quiz['titre']) ?></h1>
        <div class="quiz-intro">
            <p>Domaine: <?= htmlspecialchars($quiz['domaine']) ?> • <?= count($questions) ?> questions</p>
        </div>

        <form id="quizForm" method="POST" onsubmit="return validateForm(event)">
            <div class="form-group">
                <label class="label-main" for="user_name">Votre Nom Complet</label>
                <input type="text" id="user_name" name="user_name" class="input-text" placeholder="Ex: Jean Dupont">
                <span id="err-user" class="error-msg">Veuillez saisir votre nom.</span>
            </div>

            <?php if (empty($questions)): ?>
                <p>Ce quiz n'a pas encore de questions.</p>
            <?php else: ?>
                <?php foreach ($questions as $index => $q): ?>
                    <div class="question-card">
                        <div class="question-text">
                            <span class="question-num"><?= $index + 1 ?></span>
                            <?= htmlspecialchars($q['enonce']) ?>
                        </div>
                        <div class="options-list">
                            <?php 
                            $reponses = $reponseCtrl->getReponsesByQuestion($q['id_question']);
                            shuffle($reponses); // Randomiser l'ordre des réponses
                            foreach ($reponses as $r): 
                            ?>
                                <label class="option-item">
                                    <input type="radio" name="q_<?= $q['id_question'] ?>" value="<?= $r['id_reponse'] ?>" onchange="updateProgress()">
                                    <span><?= htmlspecialchars($r['texte']) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                        <span id="err-q-<?= $q['id_question'] ?>" class="error-msg">Veuillez choisir une réponse.</span>
                    </div>
                <?php endforeach; ?>
                
                <div style="display:flex; gap:15px;">
                    <a href="quizzes-list.php" class="btn-submit" style="background:#e2e8f0; color:#475569; text-decoration:none; text-align:center; flex:1;">Annuler</a>
                    <button type="submit" class="btn-submit" style="flex:2;">Terminer le Quiz</button>
                </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- OVERLAY DE TEMPS ÉCOULÉ -->
<div id="timeUpOverlay" class="time-up-overlay">
    <div class="time-up-box">
        <div class="time-up-icon"><i class="fas fa-hourglass-end"></i></div>
        <h2 class="time-up-title">Temps écoulé !</h2>
        <p style="color:var(--muted); margin-bottom:20px;">Le chronomètre est arrivé à zéro. Vos réponses vont être soumises automatiquement.</p>
        <div style="color:var(--blue); font-size:24px;"><i class="fas fa-spinner fa-spin"></i></div>
    </div>
</div>

<script>
let totalSeconds = <?= $totalSeconds ?>;
let isTimeUp = false;
let timerInterval;

function validateForm(e) {
    if (isTimeUp) return true; // Contourner les validations si le temps est écoulé

    let isValid = true;
    document.querySelectorAll('.error-msg').forEach(el => el.style.display = 'none');

    const userName = document.getElementById('user_name').value.trim();
    if (userName.length < 2) {
        document.getElementById('err-user').style.display = 'block';
        isValid = false;
    }

    <?php foreach ($questions as $q): ?>
    if (!document.querySelector('input[name="q_<?= $q['id_question'] ?>"]:checked')) {
        document.getElementById('err-q-<?= $q['id_question'] ?>').style.display = 'block';
        isValid = false;
    }
    <?php endforeach; ?>

    if (!isValid) {
        window.scrollTo({ top: 0, behavior: 'smooth' });
        return false;
    }
    return true;
}

function updateProgress() {
    const total = <?= count($questions) ?>;
    if (total === 0) return;

    // Count answered questions (groups with at least one radio checked)
    const answered = new Set();
    document.querySelectorAll('input[type="radio"]:checked').forEach(input => {
        if (input.name.startsWith('q_')) {
            answered.add(input.name);
        }
    });

    const count = answered.size;
    const percentage = Math.round((count / total) * 100);

    // Update UI
    document.getElementById('progressBarFill').style.width = percentage + '%';
    document.getElementById('progressText').textContent = `${count} / ${total} répondus`;
}

function startTimer() {
    updateTimerDisplay();
    timerInterval = setInterval(() => {
        totalSeconds--;
        updateTimerDisplay();

        if (totalSeconds <= 0) {
            clearInterval(timerInterval);
            handleTimeUp();
        }
    }, 1000);
}

function updateTimerDisplay() {
    const minutes = Math.floor(totalSeconds / 60);
    const seconds = totalSeconds % 60;
    const formattedTime = String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');
    
    const timerText = document.getElementById('timerText');
    const timerBox = document.getElementById('timerDisplay');
    
    timerText.textContent = formattedTime;

    // Gestion des états visuels
    timerBox.classList.remove('warning', 'critical');

    if (totalSeconds <= 20 && totalSeconds > 0) {
        timerBox.classList.add('critical'); // Rouge pulsant pour les dernières 20 secondes
    } else if (totalSeconds <= 60 && totalSeconds > 0) {
        timerBox.classList.add('warning'); // Orange pour la dernière minute
    }
}

function handleTimeUp() {
    isTimeUp = true;
    // Afficher l'overlay bloquant
    document.getElementById('timeUpOverlay').classList.add('active');
    
    // Attendre un peu pour que l'utilisateur lise le message, puis forcer la soumission
    setTimeout(() => {
        document.getElementById('quizForm').submit();
    }, 2500);
}

// Initial call to set progress if needed (on back button) and start timer
window.onload = function() {
    updateProgress();
    startTimer();
};
</script>

</body>
</html>
