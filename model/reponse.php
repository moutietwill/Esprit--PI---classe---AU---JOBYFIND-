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
}
?>
