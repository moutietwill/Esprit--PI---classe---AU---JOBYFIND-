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
}
?>
