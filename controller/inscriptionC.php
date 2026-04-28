<?php
include_once __DIR__ . '/../config.php';

class inscriptionC
{
    public function addInscription($inscription)
    {
        $db = config::getConnexion();
        try {
            $req = $db->prepare('
            INSERT INTO inscription 
            (nom, prenom, email, telephone, methode_paiement, id_formation) 
            VALUES (:nom, :prenom, :email, :telephone, :methode_paiement, :id_formation)
        ');

            $req->execute([
                'nom' => $inscription->getNom(),
                'prenom' => $inscription->getPrenom(),
                'email' => $inscription->getEmail(),
                'telephone' => $inscription->getTelephone(),
                'methode_paiement' => $inscription->getMethodePaiement(),
                'id_formation' => $inscription->getIdFormation()
            ]);
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function listeInscription()
    {
        $db = config::getConnexion();
        try {
            $sql = "SELECT inscription.*, formation.titre AS formation_titre 
                    FROM inscription 
                    INNER JOIN formation ON inscription.id_formation = formation.id
                    ORDER BY inscription.id DESC";
            $liste = $db->query($sql);
            return $liste->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function deleteInscription($id)
    {
        $db = config::getConnexion();
        try {
            $req = $db->prepare("DELETE FROM inscription WHERE id=:id");
            $req->execute(['id' => $id]);
        } catch (Exception $e) {
            die("Error: " . $e->getMessage());
        }
    }

    public function getInscriptionById($id)
    {
        $db = config::getConnexion();
        try {
            $req = $db->prepare("SELECT inscription.*, formation.titre AS formation_titre 
                                 FROM inscription 
                                 INNER JOIN formation ON inscription.id_formation = formation.id 
                                 WHERE inscription.id=:id");
            $req->execute(['id' => $id]);
            $inscription = $req->fetch();
            return $inscription;
        } catch (Exception $e) {
            die("Error: " . $e->getMessage());
        }
    }

    public function updateInscription($id, $nom, $prenom, $email, $telephone, $methode_paiement, $id_formation)
    {
        $db = config::getConnexion();
        try {
            $sql = "UPDATE inscription SET nom = :nom, prenom = :prenom, email = :email, telephone = :telephone, methode_paiement = :methode_paiement, id_formation = :id_formation WHERE id = :id";
            
            $req = $db->prepare($sql);
            $req->execute([
                'id' => $id,
                'nom' => $nom,
                'prenom' => $prenom,
                'email' => $email,
                'telephone' => $telephone,
                'methode_paiement' => $methode_paiement,
                'id_formation' => $id_formation
            ]);
        } catch (Exception $e) {
            die("Error: " . $e->getMessage());
        }
    }
}
?>
