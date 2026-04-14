<?php
class Quiz {
    private $id_quiz;
    private $titre;
    private $domaine;
    private $niveau;
    private $dateCreation;

    public function __construct($titre, $domaine, $niveau) {
        $this->titre = $titre;
        $this->domaine = $domaine;
        $this->niveau = $niveau;
        $this->dateCreation = date("Y-m-d");
    }

    // Getters
    public function getTitre() { return $this->titre; }
    public function getDomaine() { return $this->domaine; }
    public function getNiveau() { return $this->niveau; }

    // Setters
    public function setTitre($titre) { $this->titre = $titre; }
}
?>