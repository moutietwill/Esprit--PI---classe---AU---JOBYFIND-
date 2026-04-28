<?php
require_once __DIR__ . "/../config/Database.php";
require_once __DIR__ . "/../model/reponse.php";

class ReponseController {

    // CREATE
    public function addReponse($reponse) {
        try {
            $sql = "INSERT INTO reponse (id_question, texte, est_correcte, justification, dateCreation)
                    VALUES (:id_question, :texte, :est_correcte, :justification, :date)";
            $db    = Database::getConnection();
            $query = $db->prepare($sql);
            $result = $query->execute([
                'id_question'  => $reponse->getIdQuestion(),
                'texte'        => $reponse->getTexte(),
                'est_correcte' => $reponse->getEstCorrecte() ? 1 : 0,
                'justification'=> $reponse->getJustification(),
                'date'         => date("Y-m-d")
            ]);
            return $result ? $db->lastInsertId() : null;
        } catch (PDOException $e) {
            error_log("Erreur addReponse: " . $e->getMessage());
            return null;
        }
    }

    // READ ALL
    public function listReponses() {
        try {
            $sql = "SELECT * FROM reponse ORDER BY dateCreation DESC";
            $db  = Database::getConnection();
            return $db->query($sql);
        } catch (PDOException $e) {
            error_log("Erreur listReponses: " . $e->getMessage());
            return null;
        }
    }

    // READ ALL WITH QUIZ CONTEXT
    public function listReponsesWithContext() {
        try {
            $sql = "SELECT r.*, q.titre as quiz_title, qn.enonce as question_text
                    FROM reponse r
                    JOIN question qn ON r.id_question = qn.id_question
                    JOIN quiz q ON qn.id_quiz = q.id_quiz
                    ORDER BY r.dateCreation DESC";
            $db  = Database::getConnection();
            return $db->query($sql);
        } catch (PDOException $e) {
            error_log("Erreur listReponsesWithContext: " . $e->getMessage());
            return null;
        }
    }

    // READ ONE
    public function getReponse($id) {
        try {
            $sql = "SELECT * FROM reponse WHERE id_reponse = :id";
            $db  = Database::getConnection();
            $req = $db->prepare($sql);
            $req->execute(['id' => $id]);
            return $req->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur getReponse: " . $e->getMessage());
            return null;
        }
    }

    // READ BY QUESTION
    public function getReponsesByQuestion($id_question) {
        try {
            $sql = "SELECT * FROM reponse WHERE id_question = :id_question ORDER BY dateCreation DESC";
            $db  = Database::getConnection();
            $req = $db->prepare($sql);
            $req->execute(['id_question' => $id_question]);
            return $req->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur getReponsesByQuestion: " . $e->getMessage());
            return [];
        }
    }

    // UPDATE
    public function updateReponse($reponse, $id) {
        try {
            $sql = "UPDATE reponse
                    SET texte=:texte, est_correcte=:est_correcte, justification=:justification
                    WHERE id_reponse=:id";
            $db    = Database::getConnection();
            $query = $db->prepare($sql);
            return $query->execute([
                'id'           => $id,
                'texte'        => $reponse->getTexte(),
                'est_correcte' => $reponse->getEstCorrecte() ? 1 : 0,
                'justification'=> $reponse->getJustification()
            ]);
        } catch (PDOException $e) {
            error_log("Erreur updateReponse: " . $e->getMessage());
            return false;
        }
    }

    // DELETE
    public function deleteReponse($id) {
        try {
            $sql = "DELETE FROM reponse WHERE id_reponse = :id";
            $db  = Database::getConnection();
            $req = $db->prepare($sql);
            return $req->execute(['id' => $id]);
        } catch (PDOException $e) {
            error_log("Erreur deleteReponse: " . $e->getMessage());
            return false;
        }
    }

    // DELETE BY QUESTION
    public function deleteByQuestion($id_question) {
        try {
            $sql = "DELETE FROM reponse WHERE id_question = :id_question";
            $db  = Database::getConnection();
            $req = $db->prepare($sql);
            return $req->execute(['id_question' => $id_question]);
        } catch (PDOException $e) {
            error_log("Erreur deleteByQuestion: " . $e->getMessage());
            return false;
        }
    }

    // COUNT BY QUESTION
    public function countByQuestion($id_question) {
        try {
            $sql = "SELECT COUNT(*) as count FROM reponse WHERE id_question = :id_question";
            $db  = Database::getConnection();
            $req = $db->prepare($sql);
            $req->execute(['id_question' => $id_question]);
            $result = $req->fetch(PDO::FETCH_ASSOC);
            return $result['count'] ?? 0;
        } catch (PDOException $e) {
            error_log("Erreur countByQuestion: " . $e->getMessage());
            return 0;
        }
    }
    // GET CORRECT REPONSE FOR QUESTION
    public function getCorrectReponseForQuestion($id_question) {
        try {
            $sql = "SELECT * FROM reponse WHERE id_question = :id_question AND est_correcte = 1 LIMIT 1";
            $db  = Database::getConnection();
            $req = $db->prepare($sql);
            $req->execute(['id_question' => $id_question]);
            return $req->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur getCorrectReponseForQuestion: " . $e->getMessage());
            return null;
        }
    }
}
?>
