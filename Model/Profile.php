<?php
class Profile
{
    private ?int $Id_profil = null;
    private ?int $Id_utilisateur = null;
    private ?string $photo_profil = null;
    private ?string $bio = null;
    private ?string $ville = null;
    private ?string $pays = null;
    private ?string $profession = null;
    private ?string $competences = null;
    private ?string $linkedin = null;
    private ?string $date_creation = null;
    private ?string $date_modification = null;

    public function __construct(array $data = [])
    {
        if (!empty($data)) {
            $this->Id_profil = $data['Id_profil'] ?? null;
            $this->Id_utilisateur = $data['Id_utilisateur'] ?? null;
            $this->photo_profil = $data['photo_profil'] ?? null;
            $this->bio = $data['bio'] ?? null;
            $this->ville = $data['ville'] ?? null;
            $this->pays = $data['pays'] ?? null;
            $this->profession = $data['profession'] ?? null;
            $this->competences = $data['competences'] ?? null;
            $this->linkedin = $data['linkedin'] ?? null;
            $this->date_creation = $data['date_creation'] ?? null;
            $this->date_modification = $data['date_modification'] ?? null;
        }
    }

    
    public function getId_profil() { return $this->Id_profil; }
    public function getId_utilisateur() { return $this->Id_utilisateur; }
    public function getPhoto_profil() { return $this->photo_profil; }
    public function getBio() { return $this->bio; }
    public function getVille() { return $this->ville; }
    public function getPays() { return $this->pays; }
    public function getProfession() { return $this->profession; }
    public function getCompetences() { return $this->competences; }
    public function getLinkedin() { return $this->linkedin; }
    public function getDate_creation() { return $this->date_creation; }
    public function getDate_modification() { return $this->date_modification; }

    
    public function setId_profil($id) { $this->Id_profil = $id; }
    public function setId_utilisateur($id) { $this->Id_utilisateur = $id; }
    public function setPhoto_profil($photo) { $this->photo_profil = $photo; }
    public function setBio($bio) { $this->bio = $bio; }
    public function setVille($ville) { $this->ville = $ville; }
    public function setPays($pays) { $this->pays = $pays; }
    public function setProfession($profession) { $this->profession = $profession; }
    public function setCompetences($competences) { $this->competences = $competences; }
    public function setLinkedin($linkedin) { $this->linkedin = $linkedin; }
    public function setDate_creation($date) { $this->date_creation = $date; }
    public function setDate_modification($date) { $this->date_modification = $date; }
}
?>
