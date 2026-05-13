<?php
class Quiz {
    private $id_quiz;
    private $titre;
    private $domaine;
    private $niveau;
    private $dateCreation;
    private $id_createur;

    public function __construct($titre, $domaine, $niveau, $id_createur = null) {
        $this->titre        = $titre;
        $this->domaine      = $domaine;
        $this->niveau       = $niveau;
        $this->dateCreation = date("Y-m-d");
        $this->id_createur  = $id_createur;
    }

    public function getIdQuiz()      { return $this->id_quiz; }
    public function getTitre()       { return $this->titre; }
    public function getDomaine()     { return $this->domaine; }
    public function getNiveau()      { return $this->niveau; }
    public function getDateCreation(){ return $this->dateCreation; }
    public function getIdCreateur()  { return $this->id_createur; }

    public function setIdQuiz($id)         { $this->id_quiz = $id; }
    public function setTitre($titre)       { $this->titre = $titre; }
    public function setDomaine($domaine)   { $this->domaine = $domaine; }
    public function setNiveau($niveau)     { $this->niveau = $niveau; }
    public function setIdCreateur($id)     { $this->id_createur = $id; }

    // Database methods
    private static function getDb() {
        require_once __DIR__ . '/../config/Database.php';
        return Database::getInstance()->getConnection();
    }

    public function save() {
        $db = self::getDb();
        if ($this->id_quiz) {
            $sql = "UPDATE quizz SET titre = :titre, domaine = :domaine, niveau = :niveau, id_createur = :id_createur WHERE id_quiz = :id";
            $stmt = $db->prepare($sql);
            return $stmt->execute([
                ':titre' => $this->titre,
                ':domaine' => $this->domaine,
                ':niveau' => $this->niveau,
                ':id_createur' => $this->id_createur,
                ':id' => $this->id_quiz
            ]);
        } else {
            $sql = "INSERT INTO quizz (titre, domaine, niveau, dateCreation, id_createur) VALUES (:titre, :domaine, :niveau, :date, :id_createur)";
            $stmt = $db->prepare($sql);
            $success = $stmt->execute([
                ':titre' => $this->titre,
                ':domaine' => $this->domaine,
                ':niveau' => $this->niveau,
                ':date' => $this->dateCreation,
                ':id_createur' => $this->id_createur
            ]);
            if ($success) {
                $this->id_quiz = $db->lastInsertId();
                return true;
            }
            return false;
        }
    }

    public static function getAll() {
        $db = self::getDb();
        $stmt = $db->query("SELECT q.*, (SELECT COUNT(*) FROM question qn WHERE qn.id_quiz = q.id_quiz) as question_count FROM quizz q ORDER BY q.id_quiz DESC");
        return $stmt->fetchAll();
    }

    public static function getById($id) {
        $db = self::getDb();
        $stmt = $db->prepare("SELECT * FROM quizz WHERE id_quiz = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }
}
?>
