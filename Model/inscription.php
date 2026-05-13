<?php
class inscription {
    private int $id;
    private string $nom;
    private string $prenom;
    private string $email;
    private string $telephone;
    private string $methode_paiement;
    private int $id_formation;

    public function __construct(string $nom, string $prenom, string $email, string $telephone, string $methode_paiement, int $id_formation, int $id = 0) {
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->email = $email;
        $this->telephone = $telephone;
        $this->methode_paiement = $methode_paiement;
        $this->id_formation = $id_formation;
        $this->id = $id;
    }

    public function getId(): int { return $this->id; }
    public function getNom(): string { return $this->nom; }
    public function getPrenom(): string { return $this->prenom; }
    public function getEmail(): string { return $this->email; }
    public function getTelephone(): string { return $this->telephone; }
    public function getMethodePaiement(): string { return $this->methode_paiement; }
    public function getIdFormation(): int { return $this->id_formation; }

    public function setId(int $id): void { $this->id = $id; }
    public function setNom(string $nom): void { $this->nom = $nom; }
    public function setPrenom(string $prenom): void { $this->prenom = $prenom; }
    public function setEmail(string $email): void { $this->email = $email; }
    public function setTelephone(string $telephone): void { $this->telephone = $telephone; }
    public function setMethodePaiement(string $methode_paiement): void { $this->methode_paiement = $methode_paiement; }
    public function setIdFormation(int $id_formation): void { $this->id_formation = $id_formation; }
}
?>
