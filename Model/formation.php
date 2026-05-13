   <?php
class formation {
    private string $titre;
    private float $prix;
    private string $date;
    private string $duree;
    private string $description;
    private string $categorie;
    private int $id;

    public function __construct(string $titre, float $prix, string $date, string $duree, string $description, string $categorie, int $id = 0) {
        $this->titre = $titre;
        $this->prix = $prix;
        $this->date = $date;
        $this->duree = $duree;
        $this->description = $description;
        $this->categorie = $categorie;
        $this->id = $id;
    }

    public function getTitre(): string { return $this->titre; }
    public function getPrix(): float { return $this->prix; }
    public function getDate(): string { return $this->date; }
    public function getDuree(): string { return $this->duree; }
    public function getDescription(): string { return $this->description; }
    public function getCategorie(): string { return $this->categorie; }
    public function getId(): int { return $this->id; }

    public function setTitre(string $titre): void { $this->titre = $titre; }
    public function setPrix(float $prix): void { $this->prix = $prix; }
    public function setDate(string $date): void { $this->date = $date; }
    public function setDuree(string $duree): void { $this->duree = $duree; }
    public function setDescription(string $description): void { $this->description = $description; }
    public function setCategorie(string $categorie): void { $this->categorie = $categorie; }
    public function setId(int $id): void { $this->id = $id; }

   
}
?>
