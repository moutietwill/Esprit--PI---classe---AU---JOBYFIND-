<?php
require_once(__DIR__ . '/../config.php');
require_once(__DIR__ . '/../Model/Profile.php');

class ProfileController
{
    public function getProfileByUserId($userId)
    {
        $db = config::getConnexion();

        // Auto-create the profiles table if it doesn't exist
        $db->exec("CREATE TABLE IF NOT EXISTS `profiles` (
            `id`                INT(11) NOT NULL AUTO_INCREMENT,
            `Id_utilisateur`    INT(11) NOT NULL,
            `photo_profil`      VARCHAR(255) DEFAULT NULL,
            `bio`               TEXT DEFAULT NULL,
            `ville`             VARCHAR(100) DEFAULT NULL,
            `pays`              VARCHAR(100) DEFAULT 'Tunisie',
            `profession`        VARCHAR(150) DEFAULT NULL,
            `competences`       TEXT DEFAULT NULL,
            `linkedin`          VARCHAR(255) DEFAULT NULL,
            `date_creation`     DATETIME DEFAULT NULL,
            `date_modification` DATETIME DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `unique_user` (`Id_utilisateur`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $sql = "SELECT * FROM profiles WHERE Id_utilisateur = :userId";
        try {
            $query = $db->prepare($sql);
            $query->execute(['userId' => $userId]);
            return $query->fetch();
        } catch (Exception $e) {
            return false;
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
        $db = config::getConnexion();

        // Auto-create the table if it doesn't exist
        $db->exec("CREATE TABLE IF NOT EXISTS `profiles` (
            `id`                INT(11) NOT NULL AUTO_INCREMENT,
            `Id_utilisateur`    INT(11) NOT NULL,
            `photo_profil`      VARCHAR(255) DEFAULT NULL,
            `bio`               TEXT DEFAULT NULL,
            `ville`             VARCHAR(100) DEFAULT NULL,
            `pays`              VARCHAR(100) DEFAULT 'Tunisie',
            `profession`        VARCHAR(150) DEFAULT NULL,
            `competences`       TEXT DEFAULT NULL,
            `linkedin`          VARCHAR(255) DEFAULT NULL,
            `date_creation`     DATETIME DEFAULT NULL,
            `date_modification` DATETIME DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `unique_user` (`Id_utilisateur`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $sql = "INSERT INTO profiles (Id_utilisateur, photo_profil, bio, ville, pays, profession, competences, linkedin, date_creation, date_modification) 
                VALUES (:Id_utilisateur, :photo_profil, :bio, :ville, :pays, :profession, :competences, :linkedin, NOW(), NOW())";
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'Id_utilisateur' => $profile->getId_utilisateur(),
                'photo_profil'   => $profile->getPhoto_profil(),
                'bio'            => $profile->getBio(),
                'ville'          => $profile->getVille(),
                'pays'           => $profile->getPays(),
                'profession'     => $profile->getProfession(),
                'competences'    => $profile->getCompetences(),
                'linkedin'       => $profile->getLinkedin()
            ]);
        } catch (Exception $e) {
            // Profile creation is non-fatal — log but don't die
            error_log('Profile creation failed: ' . $e->getMessage());
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
