<?php
class Event {
    private $idEvenement;
    private $titre;
    private $description;
    private $date;
    private $lieu;
    private $idOrganisateur;
    public $image;

    public function __construct($data = []) {
        $this->idEvenement = $data['idEvenement'] ?? null;
        $this->titre = $data['titre'] ?? '';
        $this->description = $data['description'] ?? '';
        $this->date = $data['date'] ?? '';
        $this->lieu = $data['lieu'] ?? '';
        $this->idOrganisateur = $data['idOrganisateur'] ?? 0;
        $this->image = $data['image'] ?? 'public/assets/images/event/e1.png';
    }

    // GETTERS
    public function getId() { return $this->idEvenement; }
    public function getTitre() { return $this->titre; }
    public function getDescription() { return $this->description; }
    public function getDate() { return $this->date; }
    public function getLieu() { return $this->lieu; }
    public function getIdOrganisateur() { return $this->idOrganisateur; }

    // SETTERS
    public function setTitre($v) { $this->titre = $v; }
    public function setDescription($v) { $this->description = $v; }
    public function setDate($v) { $this->date = $v; }
    public function setLieu($v) { $this->lieu = $v; }
    public function setIdOrganisateur($v) { $this->idOrganisateur = $v; }
    public function setId($id) { $this->idEvenement = $id; }

    public function toArray() {
        return [
            'idEvenement' => $this->idEvenement,
            'titre' => $this->titre,
            'description' => $this->description,
            'date' => $this->date,
            'lieu' => $this->lieu,
            'idOrganisateur' => $this->idOrganisateur,
            'image' => $this->image,
        ];
    }
    public function setImage($img) { $this->image = $img; }
    public function getImage() { return $this->image; }
}
