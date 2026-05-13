<?php
/**
 * Script de migration pour ajouter les colonnes nom, prenom et email à la table inscription
 * Exécutez ce script une seule fois pour migrer votre base de données
 */

require_once 'config/Database.php';

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();

    echo "Démarrage de la migration...\n";

    // Vérifier et ajouter les colonnes
    $alterStatements = [
        "ALTER TABLE `inscription` ADD COLUMN IF NOT EXISTS `nom` VARCHAR(100) NOT NULL DEFAULT ''",
        "ALTER TABLE `inscription` ADD COLUMN IF NOT EXISTS `prenom` VARCHAR(100) NOT NULL DEFAULT ''",
        "ALTER TABLE `inscription` ADD COLUMN IF NOT EXISTS `email` VARCHAR(255) NOT NULL DEFAULT ''",
        "ALTER TABLE `inscription` ADD INDEX IF NOT EXISTS `idx_email` (`email`)"
    ];

    foreach ($alterStatements as $statement) {
        if ($conn->query($statement)) {
            echo "✓ Exécution réussie: " . trim($statement) . "\n";
        } else {
            echo "✗ Erreur: " . $conn->error . "\n";
        }
    }

    // Remplir les colonnes avec les données de la table utilisateur
    echo "\nRemplissage des colonnes avec les données utilisateur...\n";
    
    $updateQuery = "
        UPDATE `inscription` i
        JOIN `utilisateur` u ON i.`idUtilisateur` = u.`idUtilisateur`
        SET 
            i.`nom` = u.`nom`,
            i.`prenom` = u.`prenom`,
            i.`email` = u.`email`
        WHERE i.`nom` = '' OR i.`prenom` = '' OR i.`email` = ''
    ";

    if ($conn->query($updateQuery)) {
        echo "✓ Colonnes remplies avec succès!\n";
    } else {
        echo "✗ Erreur lors du remplissage: " . $conn->error . "\n";
    }

    echo "\n✓ Migration terminée avec succès!\n";
    
} catch (Exception $e) {
    echo "✗ Erreur: " . $e->getMessage() . "\n";
}
?>
