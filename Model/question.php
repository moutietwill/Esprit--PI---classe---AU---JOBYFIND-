<?php
class Question {
    private $id_question;
    private $id_quiz;
    private $enonce;
    private $type;
    private $points;
    private $dateCreation;

    public function __construct($id_quiz, $enonce, $type, $points) {
        $this->id_quiz      = $id_quiz;
        $this->enonce       = $enonce;
        $this->type         = $type;
        $this->points       = $points;
        $this->dateCreation = date("Y-m-d");
    }

    public function getIdQuestion()  { return $this->id_question; }
    public function getIdQuiz()      { return $this->id_quiz; }
    public function getEnonce()      { return $this->enonce; }
    public function getType()        { return $this->type; }
    public function getPoints()      { return $this->points; }
    public function getDateCreation(){ return $this->dateCreation; }

    public function setIdQuestion($id)     { $this->id_question = $id; }
    public function setEnonce($enonce)     { $this->enonce = $enonce; }
    public function setType($type)         { $this->type = $type; }
    public function setPoints($points)     { $this->points = $points; }

    // Database methods
    private static function getDb() {
        require_once __DIR__ . '/../config/Database.php';
        return Database::getInstance()->getConnection();
    }

    public function save() {
        $db = self::getDb();
        if ($this->id_question) {
            $sql = "UPDATE question SET enonce = :enonce, type = :type, points = :points WHERE id_question = :id";
            $stmt = $db->prepare($sql);
            return $stmt->execute([
                ':enonce' => $this->enonce,
                ':type' => $this->type,
                ':points' => $this->points,
                ':id' => $this->id_question
            ]);
        } else {
            $sql = "INSERT INTO question (id_quiz, enonce, type, points, dateCreation) VALUES (:id_quiz, :enonce, :type, :points, :date)";
            $stmt = $db->prepare($sql);
            $success = $stmt->execute([
                ':id_quiz' => $this->id_quiz,
                ':enonce' => $this->enonce,
                ':type' => $this->type,
                ':points' => $this->points,
                ':date' => $this->dateCreation
            ]);
            if ($success) {
                $this->id_question = $db->lastInsertId();
                return true;
            }
            return false;
        }
    }

    public static function getByQuizId($id_quiz) {
        $db = self::getDb();
        $stmt = $db->prepare("SELECT * FROM question WHERE id_quiz = :id_quiz");
        $stmt->execute([':id_quiz' => $id_quiz]);
        return $stmt->fetchAll();
    }
}
?>
