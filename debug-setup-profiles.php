<?php
// TEMP SETUP - DELETE AFTER USE
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once(__DIR__ . '/config.php');

$db = config::getConnexion();

$sql = "CREATE TABLE IF NOT EXISTS `profiles` (
    `id`               INT(11) NOT NULL AUTO_INCREMENT,
    `Id_utilisateur`   INT(11) NOT NULL,
    `photo_profil`     VARCHAR(255) DEFAULT NULL,
    `bio`              TEXT DEFAULT NULL,
    `ville`            VARCHAR(100) DEFAULT NULL,
    `pays`             VARCHAR(100) DEFAULT 'Tunisie',
    `profession`       VARCHAR(150) DEFAULT NULL,
    `competences`      TEXT DEFAULT NULL,
    `linkedin`         VARCHAR(255) DEFAULT NULL,
    `date_creation`    DATETIME DEFAULT NOW(),
    `date_modification` DATETIME DEFAULT NOW(),
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_user` (`Id_utilisateur`),
    CONSTRAINT `fk_profile_user` FOREIGN KEY (`Id_utilisateur`) REFERENCES `utilisateurs`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

try {
    $db->exec($sql);
    echo "<p style='color:green; font-family:sans-serif; font-size:18px'>✓ Table <strong>profiles</strong> créée avec succès !</p>";
    echo "<p style='font-family:sans-serif'>Vous pouvez maintenant <a href='View/frontoffice/register.php'>aller sur la page d'inscription</a> et créer votre compte.</p>";
    echo "<p style='color:red; font-family:sans-serif; font-size:12px'>⚠ Supprimez ce fichier après utilisation : <code>C:\\xampp\\htdocs\\JobyFind\\debug-setup-profiles.php</code></p>";
} catch (Exception $e) {
    echo "<p style='color:red'>✗ Erreur: " . $e->getMessage() . "</p>";
}
?>
