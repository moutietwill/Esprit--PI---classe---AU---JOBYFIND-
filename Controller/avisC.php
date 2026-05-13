<?php
include_once __DIR__ . '/../config.php';

class avisC
{
    public function addAvis($avis)
    {
        $db = config::getConnexion();
        try {
            $req = $db->prepare('
                INSERT INTO avis (id_formation, note, commentaire) 
                VALUES (:id_formation, :note, :commentaire)
            ');

            $req->execute([
                'id_formation' => $avis->getIdFormation(),
                'note' => $avis->getNote(),
                'commentaire' => $avis->getCommentaire()
            ]);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function getAvisByFormation($id_formation)
    {
        $db = config::getConnexion();
        try {
            $req = $db->prepare("SELECT * FROM avis WHERE id_formation = :id_formation ORDER BY date_avis DESC");
            $req->execute(['id_formation' => $id_formation]);
            return $req->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    public function getAverageRating($id_formation)
    {
        $db = config::getConnexion();
        try {
            $req = $db->prepare("SELECT AVG(note) as moyenne, COUNT(note) as count FROM avis WHERE id_formation = :id_formation");
            $req->execute(['id_formation' => $id_formation]);
            $result = $req->fetch();
            return [
                'moyenne' => $result['moyenne'] ? round($result['moyenne'], 1) : 0,
                'count' => $result['count']
            ];
        } catch (Exception $e) {
            return ['moyenne' => 0, 'count' => 0];
        }
    }
}
?>
