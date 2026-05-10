<?php
require_once 'config/Database.php';

$db = Database::getInstance()->getConnection();

try {
    // Drop tables to recreate with correct structure
    $db->exec("SET FOREIGN_KEY_CHECKS = 0;");
    $db->exec("DROP TABLE IF EXISTS `profiles`;");
    $db->exec("DROP TABLE IF EXISTS `password_resets`;");
    $db->exec("DROP TABLE IF EXISTS `utilisateurs`;");
    $db->exec("SET FOREIGN_KEY_CHECKS = 1;");

    // 1. Create utilisateurs table
    $sql1 = "CREATE TABLE `utilisateurs` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `first_name` varchar(100) NOT NULL,
        `last_name` varchar(100) NOT NULL,
        `username` varchar(100) DEFAULT NULL,
        `date_of_birth` date DEFAULT NULL,
        `phone` varchar(20) DEFAULT NULL,
        `city` varchar(100) DEFAULT NULL,
        `email` varchar(150) NOT NULL UNIQUE,
        `password` varchar(255) NOT NULL,
        `role` varchar(50) NOT NULL DEFAULT 'Entrepreneur',
        `status` varchar(50) NOT NULL DEFAULT 'Actif',
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `last_login` timestamp NULL DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    $db->exec($sql1);
    echo "Table 'utilisateurs' created.\n";

    // 2. Create profiles table
    $sql2 = "CREATE TABLE `profiles` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `Id_utilisateur` int(11) NOT NULL,
        `photo_profil` varchar(255) DEFAULT NULL,
        `bio` text DEFAULT NULL,
        `ville` varchar(100) DEFAULT NULL,
        `pays` varchar(100) DEFAULT NULL,
        `profession` varchar(100) DEFAULT NULL,
        `competences` text DEFAULT NULL,
        `linkedin` varchar(255) DEFAULT NULL,
        `date_creation` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `date_modification` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `Id_utilisateur` (`Id_utilisateur`),
        CONSTRAINT `profiles_ibfk_1` FOREIGN KEY (`Id_utilisateur`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    $db->exec($sql2);
    echo "Table 'profiles' created.\n";

    // 3. Create password_resets table
    $sql3 = "CREATE TABLE `password_resets` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `email` varchar(150) NOT NULL,
        `code` varchar(10) NOT NULL,
        `expires_at` timestamp NOT NULL,
        `used` tinyint(1) NOT NULL DEFAULT 0,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    $db->exec($sql3);
    echo "Table 'password_resets' created.\n";

} catch (PDOException $e) {
    die("Error creating tables: " . $e->getMessage());
}
