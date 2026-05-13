<?php
class Inscription {
    private $idInscription;
    private $idEvenement;
    private $titreEvenement;
    private $nom;
    private $prenom;
    private $email;
    private $dateInscription;
    private $statut;

    public function __construct($data = []) {
        $this->idInscription   = $data['idInscription']   ?? null;
        $this->idEvenement     = $data['idEvenement']     ?? null;
        $this->titreEvenement  = $data['titreEvenement']  ?? ($data['titre_evenement'] ?? '');
        $this->nom             = $data['nom']             ?? '';
        $this->prenom          = $data['prenom']          ?? '';
        $this->email           = $data['email']           ?? '';
        $this->dateInscription = $data['dateInscription'] ?? date('Y-m-d');
        $this->statut          = $data['statut']          ?? 'confirmée';
    }

    // GETTERS
    public function getId()               { return $this->idInscription; }
    public function getIdEvenement()      { return $this->idEvenement; }
    public function getTitreEvenement()   { return $this->titreEvenement; }
    public function getNom()              { return $this->nom; }
    public function getPrenom()           { return $this->prenom; }
    public function getEmail()            { return $this->email; }
    public function getDateInscription()  { return $this->dateInscription; }
    public function getStatut()           { return $this->statut; }

    // SETTERS
    public function setId($v)               { $this->idInscription = $v; }
    public function setIdEvenement($v)      { $this->idEvenement = $v; }
    public function setTitreEvenement($v)   { $this->titreEvenement = $v; }
    public function setNom($v)              { $this->nom = $v; }
    public function setPrenom($v)           { $this->prenom = $v; }
    public function setEmail($v)            { $this->email = $v; }
    public function setDateInscription($v)  { $this->dateInscription = $v; }
    public function setStatut($v)           { $this->statut = $v; }

    public function toArray() {
        return [
            'idInscription'   => $this->idInscription,
            'idEvenement'     => $this->idEvenement,
            'titreEvenement'  => $this->titreEvenement,
            'nom'             => $this->nom,
            'prenom'          => $this->prenom,
            'email'           => $this->email,
            'dateInscription' => $this->dateInscription,
            'statut'          => $this->statut,
        ];
    }
}
?>
