<?php
/**
 * Script de migration pour adapter la table inscription sans table utilisateur
 * Exécutez ce script pour migrer votre base de données
 */

require_once 'config/Database.php';

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();

    echo "<h2>Migration de la table inscription</h2>";
    echo "<p>Suppression de la dépendance à la table utilisateur...</p>\n";

    // Étape 1: Créer une nouvelle table d'inscription sans idUtilisateur
    echo "<h3>Étape 1: Création de la nouvelle structure</h3>\n";
    
    $sql = "CREATE TABLE IF NOT EXISTS `inscription_new` (
        `idInscription` int(11) NOT NULL AUTO_INCREMENT,
        `idEvenement` int(11) NOT NULL,
        `nom` varchar(100) NOT NULL,
        `prenom` varchar(100) NOT NULL,
        `email` varchar(255) NOT NULL,
        `dateInscription` date NOT NULL,
        `statut` varchar(50) NOT NULL DEFAULT 'confirmée',
        PRIMARY KEY (`idInscription`),
        KEY `idEvenement` (`idEvenement`),
        KEY `idx_email` (`email`),
        UNIQUE KEY `unique_inscription` (`idEvenement`, `email`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if ($conn->query($sql)) {
        echo "✓ Nouvelle table créée<br>\n";
    } else {
        echo "✗ Erreur: " . $conn->error . "<br>\n";
    }

    // Étape 2: Copier les données de l'ancienne table (si elle existe)
    echo "<h3>Étape 2: Migration des données existantes</h3>\n";
    
    $checkTable = $conn->query("SHOW TABLES LIKE 'inscription'")->fetchAll();
    if (count($checkTable) > 0) {
        // Vérifier si la table a déjà les colonnes nom, prenom, email
        $checkColumns = $conn->query("SHOW COLUMNS FROM `inscription` LIKE 'nom'")->fetchAll();
        
        if (count($checkColumns) > 0) {
            // Les colonnes existent déjà, migrer les données
            $sql = "INSERT IGNORE INTO `inscription_new` (idInscription, idEvenement, nom, prenom, email, dateInscription, statut)
                    SELECT idInscription, idEvenement, nom, prenom, email, dateInscription, statut FROM `inscription`";
            
            if ($conn->query($sql)) {
                echo "✓ Données migrées avec succès<br>\n";
            } else {
                echo "✗ Erreur lors de la migration: " . $conn->error . "<br>\n";
            }
        } else {
            echo "⚠ La table inscription n'a pas les colonnes nom, prenom, email<br>\n";
            echo "  Insérez manuellement les données ou contactez votre administrateur<br>\n";
        }
    }

    // Étape 3: Supprimer l'ancienne table
    echo "<h3>Étape 3: Nettoyage</h3>\n";
    
    $sql = "DROP TABLE IF EXISTS `inscription`";
    if ($conn->query($sql)) {
        echo "✓ Ancienne table supprimée<br>\n";
    } else {
        echo "✗ Erreur: " . $conn->error . "<br>\n";
    }

    // Étape 4: Renommer la nouvelle table
    echo "<h3>Étape 4: Finalisation</h3>\n";
    
    $sql = "RENAME TABLE `inscription_new` TO `inscription`";
    if ($conn->query($sql)) {
        echo "✓ Nouvelle table renommée<br>\n";
    } else {
        echo "✗ Erreur: " . $conn->error . "<br>\n";
    }

    echo "<h2 style='color: green;'>✓ Migration terminée avec succès!</h2>\n";
    echo "<p>La table <strong>inscription</strong> est maintenant indépendante de la table utilisateur.</p>\n";
    echo "<p><a href='index.php'>Retour à l'accueil</a></p>\n";
    
} catch (Exception $e) {
    echo "<h2 style='color: red;'>✗ Erreur: " . $e->getMessage() . "</h2>\n";
}
?>
