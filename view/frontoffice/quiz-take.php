<?php
require_once __DIR__ . "/../../config/Database.php";
require_once __DIR__ . "/../../model/quizz.php";
require_once __DIR__ . "/../../controller/quizzController.php";

$quizController = new QuizController();

// Vérifier si un ID de quiz est fourni
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: quiz.php");
    exit();
}

$quizId = (int)$_GET['id'];

// Ici vous pouvez ajouter la logique pour récupérer les questions du quiz
// Pour l'instant, on affiche juste les informations du quiz

$listQuizzes = $quizController->listQuiz()->fetchAll(PDO::FETCH_ASSOC);
$currentQuiz = null;

foreach ($listQuizzes as $quiz) {
    if ($quiz['id_quiz'] == $quizId) {
        $currentQuiz = $quiz;
        break;
    }
}

if (!$currentQuiz) {
    header("Location: quiz.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($currentQuiz['titre']); ?> - Jobyfind</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        .quiz-container {
            max-width: 800px;
            margin: 50px auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .quiz-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }

        .quiz-title {
            font-size: 28px;
            margin-bottom: 10px;
        }

        .quiz-meta {
            opacity: 0.9;
            font-size: 16px;
        }

        .quiz-content {
            padding: 40px;
            text-align: center;
        }

        .coming-soon {
            font-size: 24px;
            color: #64748b;
            margin-bottom: 20px;
        }

        .quiz-info {
            background: #f8fafc;
            border-radius: 10px;
            padding: 30px;
            margin: 30px 0;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .info-item {
            text-align: center;
        }

        .info-item i {
            font-size: 32px;
            color: #2563eb;
            margin-bottom: 10px;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: #2563eb;
            color: white;
            padding: 12px 24px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }

        .back-btn:hover {
            background: #1d4ed8;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>

    <nav class="navbar">
        <div class="logo">Joby<span>find</span></div>
        <ul class="nav-links">
            <li><a href="quiz.php">Quiz</a></li>
            <li><a href="#">Formations</a></li>
            <li><a href="#">À propos</a></li>
            <li><a href="#">Contact</a></li>
        </ul>
        <div class="nav-auth">
            <button class="btn-outline">Connexion</button>
            <button class="btn-filled">S'inscrire</button>
        </div>
    </nav>

    <div class="quiz-container">
        <div class="quiz-header">
            <h1 class="quiz-title"><?php echo htmlspecialchars($currentQuiz['titre']); ?></h1>
            <div class="quiz-meta">
                <span><?php echo htmlspecialchars($currentQuiz['domaine']); ?> • <?php echo htmlspecialchars($currentQuiz['niveau']); ?></span>
            </div>
        </div>

        <div class="quiz-content">
            <h2 class="coming-soon">🚧 Fonctionnalité en développement</h2>
            <p>Le système de quiz interactif est actuellement en cours de développement.</p>

            <div class="quiz-info">
                <h3>Informations sur ce quiz</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <i class="fa fa-clock"></i>
                        <h4>Durée</h4>
                        <p>15 minutes</p>
                    </div>
                    <div class="info-item">
                        <i class="fa fa-question-circle"></i>
                        <h4>Questions</h4>
                        <p>10 questions</p>
                    </div>
                    <div class="info-item">
                        <i class="fa fa-trophy"></i>
                        <h4>Certification</h4>
                        <p>Incluse</p>
                    </div>
                    <div class="info-item">
                        <i class="fa fa-calendar"></i>
                        <h4>Créé le</h4>
                        <p><?php echo date('d/m/Y', strtotime($currentQuiz['dateCreation'])); ?></p>
                    </div>
                </div>
            </div>

            <a href="quiz.php" class="back-btn">
                <i class="fa fa-arrow-left"></i>
                Retour aux quiz
            </a>
        </div>
    </div>

</body>
</html>