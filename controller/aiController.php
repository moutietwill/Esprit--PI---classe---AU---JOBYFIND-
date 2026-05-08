<?php
require_once __DIR__ . "/../config/Database.php";
require_once __DIR__ . "/../model/quizz.php";
require_once __DIR__ . "/../model/question.php";
require_once __DIR__ . "/../model/reponse.php";
require_once __DIR__ . "/quizzController.php";
require_once __DIR__ . "/questionController.php";
require_once __DIR__ . "/reponseController.php";

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'generate_quiz') {
    
    $sujet   = trim($_POST['sujet']);
    $domaine = trim($_POST['domaine']);
    $niveau  = trim($_POST['niveau']);
    $nb      = (int)($_POST['nb_questions'] ?? 3);

    // MOCK: Simulation of an AI API call latency
    sleep(3);

    try {
        $quizCtrl = new QuizController();
        $questCtrl = new QuestionController();
        $repCtrl = new ReponseController();

        // 1. Create Quiz from AI Request00
        $titleGen = "Quiz IA : " . ucfirst($sujet);
        $newQuiz = new Quiz($titleGen, $domaine, $niveau);
        $id_quiz = $quizCtrl->addQuiz($newQuiz);

        if (!$id_quiz) {
            throw new Exception("Erreur lors de la création du quiz");
        }

        // 2. Generate and Insert Questions/Answers
        for ($i = 1; $i <= $nb; $i++) {
            // Mock realistic-looking question wording based on domain
            $enonce = "Quelle affirmation est vraie concernant '$sujet' (Question AI #$i) ?";
            if (strtolower($niveau) === 'débutant') {
                $enonce = "Qu'est-ce que '$sujet' ? (Introduction #$i)";
            } elseif (strtolower($niveau) === 'avancé') {
                $enonce = "Analysez l'impact de '$sujet' dans un contexte complexe de $domaine (Cas #$i). Que se passe-t-il si...";
            }

            // Type QCM has point value
            $newQuestion = new Question($id_quiz, $enonce, 'QCM', rand(1, 3));
            $id_question = $questCtrl->addQuestion($newQuestion);

            if ($id_question) {
                // Generate 4 answers for the QCM (1 correct, 3 wrong)
                $correctIndex = rand(1, 4);
                
                for ($j = 1; $j <= 4; $j++) {
                    $isCorrect = ($j === $correctIndex);
                    
                    if ($isCorrect) {
                        $texteReq = "C'est la définition correcte et fondamentale de $sujet.";
                        $justif = "Généré par l'IA : Ceci est un fait avéré dans le domaine : $domaine.";
                    } else {
                        $texteReq = "Distracteur généré par IA : Fausse interprétation de '$sujet' ($j).";
                        $justif = "";
                    }

                    $newReponse = new Reponse($id_question, $texteReq, $isCorrect, $justif);
                    $repCtrl->addReponse($newReponse);
                }
            }
        }

        echo json_encode(['status' => 'success', 'message' => 'Quiz généré avec succès !']);

    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Action invalide']);
