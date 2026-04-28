<?php
require_once __DIR__ . "/../config/Database.php";
require_once __DIR__ . "/../model/question.php";

class QuestionController {

    // CREATE
    public function addQuestion($question) {
        try {
            $sql = "INSERT INTO question (id_quiz, enonce, type, points, dateCreation)
                    VALUES (:id_quiz, :enonce, :type, :points, :date)";
            $db    = Database::getConnection();
            $query = $db->prepare($sql);
            $result = $query->execute([
                'id_quiz' => $question->getIdQuiz(),
                'enonce'  => $question->getEnonce(),
                'type'    => $question->getType(),
                'points'  => $question->getPoints(),
                'date'    => date("Y-m-d")
            ]);
            return $result ? $db->lastInsertId() : null;
        } catch (PDOException $e) {
            error_log("Erreur addQuestion: " . $e->getMessage());
            return null;
        }
    }

    // READ ALL
    public function listQuestions() {
        try {
            $sql = "SELECT * FROM question ORDER BY dateCreation DESC";
            $db  = Database::getConnection();
            return $db->query($sql);
        } catch (PDOException $e) {
            error_log("Erreur listQuestions: " . $e->getMessage());
            return null;
        }
    }

    // READ ONE
    public function getQuestion($id) {
        try {
            $sql = "SELECT * FROM question WHERE id_question = :id";
            $db  = Database::getConnection();
            $req = $db->prepare($sql);
            $req->execute(['id' => $id]);
            return $req->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur getQuestion: " . $e->getMessage());
            return null;
        }
    }

    // READ BY QUIZ
    public function getQuestionsByQuiz($id_quiz) {
        try {
            $sql = "SELECT * FROM question WHERE id_quiz = :id_quiz ORDER BY dateCreation DESC";
            $db  = Database::getConnection();
            $req = $db->prepare($sql);
            $req->execute(['id_quiz' => $id_quiz]);
            return $req->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur getQuestionsByQuiz: " . $e->getMessage());
            return [];
        }
    }

    // UPDATE
    public function updateQuestion($question, $id) {
        try {
            $sql = "UPDATE question
                    SET enonce=:enonce, type=:type, points=:points
                    WHERE id_question=:id";
            $db    = Database::getConnection();
            $query = $db->prepare($sql);
            return $query->execute([
                'id'     => $id,
                'enonce' => $question->getEnonce(),
                'type'   => $question->getType(),
                'points' => $question->getPoints()
            ]);
        } catch (PDOException $e) {
            error_log("Erreur updateQuestion: " . $e->getMessage());
            return false;
        }
    }

    // DELETE
    public function deleteQuestion($id) {
        try {
            $sql = "DELETE FROM question WHERE id_question = :id";
            $db  = Database::getConnection();
            $req = $db->prepare($sql);
            return $req->execute(['id' => $id]);
        } catch (PDOException $e) {
            error_log("Erreur deleteQuestion: " . $e->getMessage());
            return false;
        }
    }

    // COUNT BY QUIZ
    public function countByQuiz($id_quiz) {
        try {
            $sql = "SELECT COUNT(*) as count FROM question WHERE id_quiz = :id_quiz";
            $db  = Database::getConnection();
            $req = $db->prepare($sql);
            $req->execute(['id_quiz' => $id_quiz]);
            $result = $req->fetch(PDO::FETCH_ASSOC);
            return $result['count'] ?? 0;
        } catch (PDOException $e) {
            error_log("Erreur countByQuiz: " . $e->getMessage());
            return 0;
        }
    }
}
?>
