<?php
class Reponse {
    private $id_reponse;
    private $id_question;
    private $texte;
    private $est_correcte;
    private $justification;
    private $dateCreation;

    public function __construct($id_question, $texte, $est_correcte = false, $justification = "") {
        $this->id_question   = $id_question;
        $this->texte         = $texte;
        $this->est_correcte  = $est_correcte;
        $this->justification = $justification;
        $this->dateCreation  = date("Y-m-d");
    }

    public function getIdReponse()   { return $this->id_reponse; }
    public function getIdQuestion()  { return $this->id_question; }
    public function getTexte()       { return $this->texte; }
    public function getEstCorrecte() { return $this->est_correcte; }
    public function getJustification(){ return $this->justification; }
    public function getDateCreation(){ return $this->dateCreation; }

    public function setIdReponse($id)          { $this->id_reponse = $id; }
    public function setTexte($texte)           { $this->texte = $texte; }
    public function setEstCorrecte($correcte)  { $this->est_correcte = $correcte; }
    public function setJustification($just)    { $this->justification = $just; }

    // Database methods
    private static function getDb() {
        require_once __DIR__ . '/../config/Database.php';
        return Database::getInstance()->getConnection();
    }

    public function save() {
        $db = self::getDb();
        if ($this->id_reponse) {
            $sql = "UPDATE reponse SET texte = :texte, est_correcte = :est_correcte, justification = :justification WHERE id_reponse = :id";
            $stmt = $db->prepare($sql);
            return $stmt->execute([
                ':texte' => $this->texte,
                ':est_correcte' => $this->est_correcte ? 1 : 0,
                ':justification' => $this->justification,
                ':id' => $this->id_reponse
            ]);
        } else {
            $sql = "INSERT INTO reponse (id_question, texte, est_correcte, justification, dateCreation) VALUES (:id_question, :texte, :est_correcte, :justification, :date)";
            $stmt = $db->prepare($sql);
            $success = $stmt->execute([
                ':id_question' => $this->id_question,
                ':texte' => $this->texte,
                ':est_correcte' => $this->est_correcte ? 1 : 0,
                ':justification' => $this->justification,
                ':date' => $this->dateCreation
            ]);
            if ($success) {
                $this->id_reponse = $db->lastInsertId();
                return true;
            }
            return false;
        }
    }

    public static function getByQuestionId($id_question) {
        $db = self::getDb();
        $stmt = $db->prepare("SELECT * FROM reponse WHERE id_question = :id_question");
        $stmt->execute([':id_question' => $id_question]);
        return $stmt->fetchAll();
    }
}
?>
