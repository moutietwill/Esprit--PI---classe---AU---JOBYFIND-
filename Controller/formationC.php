<?php
include_once __DIR__ . '/../config.php';

class formationC
{
    public function addFormation($formation)
    {
        $db = config::getConnexion();
        try {
            $req = $db->prepare('
            INSERT INTO formation 
            (titre, prix, date, duree, description, categorie) 
            VALUES (:titre, :prix, :date, :duree, :description, :categorie)
        ');

            $req->execute([
                'titre' => $formation->getTitre(),
                'prix' => $formation->getPrix(),
                'date' => $formation->getDate(),
                'duree' => $formation->getDuree(),
                'description' => $formation->getDescription(),
                'categorie' => $formation->getCategorie()
            ]);
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function listeFormation()
    {
        $db = config::getConnexion();
        try {
            $sql = "SELECT * FROM formation";
            $liste = $db->query($sql);
            return $liste->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function deleteFormation($id)
    {
        $db = config::getConnexion();
        try {
            $req = $db->prepare("DELETE FROM formation WHERE id=:id");
            $req->execute(['id' => $id]);
        } catch (Exception $e) {
            die("Error: " . $e->getMessage());
        }
    }

    public function getFormationById($id)
    {
        $db = config::getConnexion();
        try {
            $req = $db->prepare("SELECT * FROM formation WHERE id=:id");
            $req->execute(['id' => $id]);
            $formation = $req->fetch(); // fetch a single row
            return $formation;
        } catch (Exception $e) {
            die("Error: " . $e->getMessage());
        }
    }

    public function updateFormation($id, $titre, $prix, $date, $duree, $description, $categorie)
    {
        $db = config::getConnexion();
        try {
            $sql = "UPDATE formation SET titre = :titre, prix = :prix, date = :date, duree = :duree, description = :description, categorie = :categorie WHERE id = :id";
            
            $req = $db->prepare($sql);
            $req->execute([
                'id' => $id,
                'titre' => $titre,
                'prix' => $prix,
                'date' => $date,
                'duree' => $duree,
                'description' => $description,
                'categorie' => $categorie
            ]);
        } catch (Exception $e) {
            die("Error: " . $e->getMessage());
        }
    }

    
}
?>
