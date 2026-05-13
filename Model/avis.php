<?php
class avis {
    private ?int $id_avis;
    private int $id_formation;
    private int $note;
    private ?string $commentaire;
    private ?string $date_avis;

    public function __construct(int $id_formation, int $note, ?string $commentaire = null, ?int $id_avis = null, ?string $date_avis = null) {
        $this->id_formation = $id_formation;
        $this->note = $note;
        $this->commentaire = $commentaire;
        $this->id_avis = $id_avis;
        $this->date_avis = $date_avis;
    }

    public function getIdAvis(): ?int { return $this->id_avis; }
    public function getIdFormation(): int { return $this->id_formation; }
    public function getNote(): int { return $this->note; }
    public function getCommentaire(): ?string { return $this->commentaire; }
    public function getDateAvis(): ?string { return $this->date_avis; }

    public function setIdAvis(?int $id_avis): void { $this->id_avis = $id_avis; }
    public function setIdFormation(int $id_formation): void { $this->id_formation = $id_formation; }
    public function setNote(int $note): void { $this->note = $note; }
    public function setCommentaire(?string $commentaire): void { $this->commentaire = $commentaire; }
    public function setDateAvis(?string $date_avis): void { $this->date_avis = $date_avis; }
}
?>
