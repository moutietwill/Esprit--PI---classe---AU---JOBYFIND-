<?php
require_once __DIR__ . "/../config/Database.php";

class SubmissionController {

    // SAVE SUBMISSION
    public function addSubmission($id_quiz, $user_name, $score, $max_score) {
        try {
            $sql = "INSERT INTO submissions (id_quiz, user_name, score, max_score, date_submitted)
                    VALUES (:id_quiz, :user_name, :score, :max_score, :date)";
            $db    = Database::getConnection();
            $query = $db->prepare($sql);
            $query->execute([
                'id_quiz'   => $id_quiz,
                'user_name' => $user_name,
                'score'     => $score,
                'max_score' => $max_score,
                'date'      => date("Y-m-d H:i:s")
            ]);
            return $db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Erreur addSubmission: " . $e->getMessage());
            return false;
        }
    }

    // LIST ALL SUBMISSIONS
    public function listSubmissions() {
        try {
            $sql = "SELECT s.*, q.titre as quiz_title 
                    FROM submissions s 
                    JOIN quiz q ON s.id_quiz = q.id_quiz 
                    ORDER BY s.date_submitted DESC";
            $db  = Database::getConnection();
            return $db->query($sql);
        } catch (PDOException $e) {
            error_log("Erreur listSubmissions: " . $e->getMessage());
            return null;
        }
    }

    // SAVE SUBMISSION ANSWER
    public function addSubmissionAnswer($idSub, $idQ, $idR) {
        try {
            $sql = "INSERT INTO submission_answers (id_submission, id_question, id_reponse)
                    VALUES (:id_sub, :id_q, :id_r)";
            $db    = Database::getConnection();
            $query = $db->prepare($sql);
            return $query->execute([
                'id_sub' => $idSub,
                'id_q'   => $idQ,
                'id_r'   => $idR
            ]);
        } catch (PDOException $e) {
            error_log("Erreur addSubmissionAnswer: " . $e->getMessage());
            return false;
        }
    }

    // GET SUBMISSION DETAILS (Responses for a submission)
    public function getSubmissionDetails($idSub) {
        try {
            $sql = "SELECT sa.*, q.enonce as question_text, r.texte as reponse_text, r.est_correcte
                    FROM submission_answers sa
                    JOIN question q ON sa.id_question = q.id_question
                    JOIN reponse r ON sa.id_reponse = r.id_reponse
                    WHERE sa.id_submission = :id_sub";
            $db    = Database::getConnection();
            $query = $db->prepare($sql);
            $query->execute(['id_sub' => $idSub]);
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur getSubmissionDetails: " . $e->getMessage());
            return [];
        }
    }

    // GET SINGLE SUBMISSION INFO
    public function getSubmission($id) {
        try {
            $sql = "SELECT s.*, q.titre as quiz_title 
                    FROM submissions s 
                    JOIN quiz q ON s.id_quiz = q.id_quiz 
                    WHERE s.id_submission = :id";
            $db  = Database::getConnection();
            $req = $db->prepare($sql);
            $req->execute(['id' => $id]);
            return $req->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return null;
        }
    }

    // COUNT TOTAL SUBMISSIONS
    public function countSubmissions() {
        try {
            $sql = "SELECT COUNT(*) as count FROM submissions";
            $db  = Database::getConnection();
            $res = $db->query($sql)->fetch();
            return $res['count'] ?? 0;
        } catch (PDOException $e) {
            return 0;
        }
    }
}
?>
