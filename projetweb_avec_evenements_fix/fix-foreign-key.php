<?php
/**
 * Script pour corriger l'erreur de contrainte de clé étrangère
 */

require_once 'config/Database.php';

function getForeignKeys(PDO $conn, string $table, string $referencedTable): array {
    $sql = "SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table AND REFERENCED_TABLE_NAME = :ref";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':table' => $table, ':ref' => $referencedTable]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function dropForeignKey(PDO $conn, string $table, string $fkName): bool {
    try {
        $conn->exec("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$fkName}`");
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

function dropColumnIfExists(PDO $conn, string $table, string $column): bool {
    $sql = "SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table AND COLUMN_NAME = :column";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':table' => $table, ':column' => $column]);
    if ((int) $stmt->fetchColumn() === 0) {
        return false;
    }
    $conn->exec("ALTER TABLE `{$table}` DROP COLUMN `{$column}`");
    return true;
}

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();

    echo "<h2>Correction de la contrainte de clé étrangère</h2>";
    $conn->exec("SET FOREIGN_KEY_CHECKS=0");

    echo "<h3>Étape 1: Suppression de la contrainte de clé étrangère sur evenement</h3>\n";
    $fkNames = getForeignKeys($conn, 'evenement', 'utilisateur');
    if (empty($fkNames)) {
        echo "✓ Aucune contrainte de clé étrangère vers utilisateur trouvée sur evenement<br>\n";
    } else {
        foreach ($fkNames as $fkName) {
            if (dropForeignKey($conn, 'evenement', $fkName)) {
                echo "✓ Contrainte '{$fkName}' supprimée<br>\n";
            } else {
                echo "✗ Impossible de supprimer la contrainte '{$fkName}'<br>\n";
            }
        }
    }

    echo "<h3>Étape 2: Mise à jour du type de colonne idOrganisateur</h3>\n";
    try {
        $conn->exec("ALTER TABLE `evenement` MODIFY `idOrganisateur` VARCHAR(100) DEFAULT NULL");
        echo "✓ Colonne idOrganisateur modifiée en VARCHAR(100)<br>\n";
    } catch (PDOException $e) {
        echo "✗ Erreur lors de la modification de idOrganisateur: " . htmlspecialchars($e->getMessage()) . "<br>\n";
    }

    echo "<h3>Étape 3: Suppression de la colonne idUtilisateur de inscription</h3>\n";
    if (dropColumnIfExists($conn, 'inscription', 'idUtilisateur')) {
        echo "✓ Colonne idUtilisateur supprimée dans inscription<br>\n";
    } else {
        echo "✓ Colonne idUtilisateur non présente dans inscription<br>\n";
    }

    $conn->exec("SET FOREIGN_KEY_CHECKS=1");
    echo "<h2 style='color: green;'>✓ Correction terminée!</h2>\n";
    echo "<p><a href='index.php?route=/admin/events'>Retour à la gestion des événements</a></p>\n";

} catch (Exception $e) {
    echo "<h2 style='color: red;'>✗ Erreur: " . htmlspecialchars($e->getMessage()) . "</h2>\n";
    echo "<p><a href='index.php'>Retour</a></p>\n";
}
?>
