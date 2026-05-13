<?php
class Formation {
    private $id;
    private $titre;
    private $prix;
    private $date;
    private $duree;
    private $description;
    private $categorie;

    public function __construct($data = []) {
        $this->id = $data['id'] ?? null;
        $this->titre = $data['titre'] ?? '';
        $this->prix = $data['prix'] ?? 0.0;
        $this->date = $data['date'] ?? '';
        $this->duree = $data['duree'] ?? '';
        $this->description = $data['description'] ?? '';
        $this->categorie = $data['categorie'] ?? '';
    }

    // GETTERS
    public function getId() { return $this->id; }
    public function getTitre() { return $this->titre; }
    public function getPrix() { return $this->prix; }
    public function getDate() { return $this->date; }
    public function getDuree() { return $this->duree; }
    public function getDescription() { return $this->description; }
    public function getCategorie() { return $this->categorie; }

    // SETTERS
    public function setId($id) { $this->id = $id; }
    public function setTitre($v) { $this->titre = $v; }
    public function setPrix($v) { $this->prix = $v; }
    public function setDate($v) { $this->date = $v; }
    public function setDuree($v) { $this->duree = $v; }
    public function setDescription($v) { $this->description = $v; }
    public function setCategorie($v) { $this->categorie = $v; }

    public function toArray() {
        return [
            'id' => $this->id,
            'titre' => $this->titre,
            'prix' => $this->prix,
            'date' => $this->date,
            'duree' => $this->duree,
            'description' => $this->description,
            'categorie' => $this->categorie,
        ];
    }
}
