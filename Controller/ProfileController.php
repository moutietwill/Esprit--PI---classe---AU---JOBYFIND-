<?php
require_once(__DIR__ . '/../config.php');
require_once(__DIR__ . '/../Model/Profile.php');

class ProfileController
{
    public function getProfileByUserId($userId)
    {
        $sql = "SELECT * FROM profiles WHERE Id_utilisateur = :userId";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute(['userId' => $userId]);
            return $query->fetch();
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    public function getProfileWithUser($userId)
    {
        $sql = "SELECT u.*, p.* FROM utilisateurs u 
                LEFT JOIN profiles p ON u.id = p.Id_utilisateur 
                WHERE u.id = :userId";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute(['userId' => $userId]);
            return $query->fetch();
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    public function addProfile($profile)
    {
        $sql = "INSERT INTO profiles (Id_utilisateur, photo_profil, bio, ville, pays, profession, competences, linkedin, date_creation, date_modification) 
                VALUES (:Id_utilisateur, :photo_profil, :bio, :ville, :pays, :profession, :competences, :linkedin, NOW(), NOW())";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'Id_utilisateur' => $profile->getId_utilisateur(),
                'photo_profil' => $profile->getPhoto_profil(),
                'bio' => $profile->getBio(),
                'ville' => $profile->getVille(),
                'pays' => $profile->getPays(),
                'profession' => $profile->getProfession(),
                'competences' => $profile->getCompetences(),
                'linkedin' => $profile->getLinkedin()
            ]);
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    public function updateProfile($profile, $userId)
    {
        $sql = "UPDATE profiles SET 
                    photo_profil = :photo_profil, 
                    bio = :bio, 
                    ville = :ville, 
                    pays = :pays, 
                    profession = :profession, 
                    competences = :competences, 
                    linkedin = :linkedin, 
                    date_modification = NOW() 
                WHERE Id_utilisateur = :userId";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'photo_profil' => $profile->getPhoto_profil(),
                'bio' => $profile->getBio(),
                'ville' => $profile->getVille(),
                'pays' => $profile->getPays(),
                'profession' => $profile->getProfession(),
                'competences' => $profile->getCompetences(),
                'linkedin' => $profile->getLinkedin(),
                'userId' => $userId
            ]);
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }
}
?>
