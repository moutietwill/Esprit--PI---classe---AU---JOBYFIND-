<?php
require_once(__DIR__ . '/../config/Database.php');
require_once(__DIR__ . '/../Model/quizz.php');
require_once(__DIR__ . '/../Model/question.php');
require_once(__DIR__ . '/../controllers/Controller.php');
require_once(__DIR__ . '/../Model/reponse.php');

class QuizzController extends Controller {
    
    public function index() {
        $script = $_SERVER['SCRIPT_NAME'] ?? '';
        $baseDir = rtrim(str_replace('\\', '/', dirname($script)), '/');
        if (basename($baseDir) === 'public') { $baseDir = dirname($baseDir); }
        header('Location: ' . $baseDir . '/View/backoffice/quiz_management.php');
        exit;
    }
    
    public function createQuiz($titre, $domaine, $niveau, $id_user) {
        $quiz = new Quiz($titre, $domaine, $niveau, $id_user);
        if ($quiz->save()) {
            return $quiz->getIdQuiz();
        }
        return false;
    }

    public function addQuestion($id_quiz, $enonce, $type, $points) {
        $question = new Question($id_quiz, $enonce, $type, $points);
        if ($question->save()) {
            return $question->getIdQuestion();
        }
        return false;
    }

    public function addReponse($id_question, $texte, $est_correcte, $justification = "") {
        $reponse = new Reponse($id_question, $texte, $est_correcte, $justification);
        return $reponse->save();
    }

    public function getQuizDetails($id_quiz) {
        $quiz = Quiz::getById($id_quiz);
        if (!$quiz) return null;

        $questions = Question::getByQuizId($id_quiz);
        foreach ($questions as &$question) {
            $question['reponses'] = Reponse::getByQuestionId($question['id_question']);
        }
        $quiz['questions'] = $questions;
        return $quiz;
    }

    public function listAllQuizzes() {
        return Quiz::getAll();
    }

    public function submitParticipation($id_user, $id_quiz, $score) {
        $db = Database::getInstance()->getConnection();
        $sql = "INSERT INTO participation_quizz (id_user, id_quiz, score) VALUES (:id_user, :id_quiz, :score)";
        $stmt = $db->prepare($sql);
        return $stmt->execute([
            ':id_user' => $id_user,
            ':id_quiz' => $id_quiz,
            ':score' => $score
        ]);
    }

    public function getUserParticipations($id_user) {
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT p.*, q.titre FROM participation_quizz p 
                JOIN quizz q ON p.id_quiz = q.id_quiz 
                WHERE p.id_user = :id_user 
                ORDER BY p.date_participation DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute([':id_user' => $id_user]);
        return $stmt->fetchAll();
    }
}
?>
