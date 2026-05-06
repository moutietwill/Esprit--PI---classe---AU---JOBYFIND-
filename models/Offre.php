<?php
class Offre {
    private $id_offre;
    private $titre;
    private $description;
    private $datePublication;
    private $statut;
    private $type;

    // Getters
    public function getIdOffre() { return $this->id_offre; }
    public function getTitre() { return $this->titre; }
    public function getDescription() { return $this->description; }
    public function getDatePublication() { return $this->datePublication; }
    public function getStatut() { return $this->statut; }
    public function getType() { return $this->type; }

    // Setters
    public function setIdOffre($id_offre) { $this->id_offre = $id_offre; }
    public function setTitre($titre) { $this->titre = $titre; }
    public function setDescription($description) { $this->description = $description; }
    public function setDatePublication($datePublication) { $this->datePublication = $datePublication; }
    public function setStatut($statut) { $this->statut = $statut; }
    public function setType($type) { $this->type = $type; }
}
?>
