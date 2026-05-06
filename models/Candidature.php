<?php
class Candidature {
    private $id_candidature;
    private $id_offre;
    private $nom_candidat;
    private $prenom_candidat;
    private $email_candidat;
    private $telephone;
    private $cv_fichier;
    private $lettre_motivation;
    private $date_candidature;
    private $statut;

    // Getters
    public function getIdCandidature() { return $this->id_candidature; }
    public function getIdOffre() { return $this->id_offre; }
    public function getNomCandidat() { return $this->nom_candidat; }
    public function getPrenomCandidat() { return $this->prenom_candidat; }
    public function getEmailCandidat() { return $this->email_candidat; }
    public function getTelephone() { return $this->telephone; }
    public function getCvFichier() { return $this->cv_fichier; }
    public function getLettreMotivation() { return $this->lettre_motivation; }
    public function getDateCandidature() { return $this->date_candidature; }
    public function getStatut() { return $this->statut; }

    // Setters
    public function setIdCandidature($id) { $this->id_candidature = $id; }
    public function setIdOffre($id) { $this->id_offre = $id; }
    public function setNomCandidat($nom) { $this->nom_candidat = $nom; }
    public function setPrenomCandidat($prenom) { $this->prenom_candidat = $prenom; }
    public function setEmailCandidat($email) { $this->email_candidat = $email; }
    public function setTelephone($tel) { $this->telephone = $tel; }
    public function setCvFichier($cv) { $this->cv_fichier = $cv; }
    public function setLettreMotivation($lettre) { $this->lettre_motivation = $lettre; }
    public function setDateCandidature($date) { $this->date_candidature = $date; }
    public function setStatut($statut) { $this->statut = $statut; }
}
?>
