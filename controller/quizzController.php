<?php
require_once __DIR__ . "/../config/Database.php";
require_once __DIR__ . "/../model/quizz.php";

class QuizController {

    // CREATE
    public function addQuiz($quiz) {
        $sql = "INSERT INTO quiz (titre, domaine, niveau, dateCreation)
                VALUES (:titre, :domaine, :niveau, :date)";
        $db = Database::getConnection();
        $query = $db->prepare($sql);

        $query->execute([
            'titre' => $quiz->getTitre(),
            'domaine' => $quiz->getDomaine(),
            'niveau' => $quiz->getNiveau(),
            'date' => date("Y-m-d")
        ]);
    }

    // READ
    public function listQuiz() {
        $sql = "SELECT * FROM quiz";
        $db = Database::getConnection();
        return $db->query($sql);
    }

    // DELETE
    public function deleteQuiz($id) {
        $sql = "DELETE FROM quiz WHERE id_quiz = :id";
        $db = Database::getConnection();
        $req = $db->prepare($sql);
        $req->execute(['id' => $id]);
    }

    // UPDATE
    public function updateQuiz($quiz, $id) {
        $sql = "UPDATE quiz 
                SET titre=:titre, domaine=:domaine, niveau=:niveau
                WHERE id_quiz=:id";
        $db = Database::getConnection();
        $query = $db->prepare($sql);

        $query->execute([
            'id' => $id,
            'titre' => $quiz->getTitre(),
            'domaine' => $quiz->getDomaine(),
            'niveau' => $quiz->getNiveau()
        ]);
    }
}
?>