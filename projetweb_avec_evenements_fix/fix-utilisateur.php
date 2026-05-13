<?php
/**
 * Fix Utilisateur Table Schema
 * Accédez à: http://localhost/projetweb_avec_evenements/fix-utilisateur.php
 */

define('BASE_PATH', dirname(__FILE__));

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once BASE_PATH . '/config/Database.php';

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Corriger Table Utilisateur</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .success {
            color: #155724;
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            padding: 12px;
            border-radius: 4px;
            margin: 10px 0;
        }
        .error {
            color: #721c24;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 12px;
            border-radius: 4px;
            margin: 10px 0;
        }
        .info {
            color: #004085;
            background-color: #d1ecf1;
            border: 1px solid #bee5eb;
            padding: 12px;
            border-radius: 4px;
            margin: 10px 0;
        }
        h1 {
            color: #333;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
        }
        h2 {
            color: #555;
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Corriger la Table Utilisateur</h1>
        
        <?php
        try {
            $db = Database::getInstance()->getConnection();
            
            echo '<h2>1️⃣ Vérification des colonnes existantes</h2>';
            
            // Check existing columns
            $columnsQuery = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'gestion_evenements' AND TABLE_NAME = 'utilisateur'";
            $stmt = $db->prepare($columnsQuery);
            $stmt->execute();
            $existingColumns = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            echo '<div class="info">Colonnes actuelles: ' . implode(', ', $existingColumns) . '</div>';
            
            // Define required columns
            $requiredColumns = ['idUtilisateur', 'prenom', 'nom', 'email', 'role', 'status', 'date_creation', 'date_modification', 'date_derniere_activite'];
            
            // Find missing columns
            $missingColumns = array_diff($requiredColumns, $existingColumns);
            
            if (count($missingColumns) === 0) {
                echo '<div class="success">✅ Toutes les colonnes existent!</div>';
            } else {
                echo '<div class="error">❌ ' . count($missingColumns) . ' colonne(s) manquante(s): ' . implode(', ', $missingColumns) . '</div>';
                
                echo '<h2>2️⃣ Ajout des colonnes manquantes</h2>';
                
                // Add missing columns
                $alterStatements = [
                    'prenom' => "ALTER TABLE `utilisateur` ADD COLUMN `prenom` VARCHAR(100) AFTER `idUtilisateur`",
                    'status' => "ALTER TABLE `utilisateur` ADD COLUMN `status` VARCHAR(50) NOT NULL DEFAULT 'Actif' AFTER `role`",
                    'date_creation' => "ALTER TABLE `utilisateur` ADD COLUMN `date_creation` TIMESTAMP DEFAULT CURRENT_TIMESTAMP",
                    'date_modification' => "ALTER TABLE `utilisateur` ADD COLUMN `date_modification` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP",
                    'date_derniere_activite' => "ALTER TABLE `utilisateur` ADD COLUMN `date_derniere_activite` TIMESTAMP NULL"
                ];
                
                $successCount = 0;
                foreach ($missingColumns as $colName) {
                    if (isset($alterStatements[$colName])) {
                        try {
                            $db->exec($alterStatements[$colName]);
                            echo '<div class="success">✅ Colonne ' . htmlspecialchars($colName) . ' ajoutée</div>';
                            $successCount++;
                        } catch (Exception $e) {
                            echo '<div class="error">❌ Erreur pour ' . htmlspecialchars($colName) . ': ' . htmlspecialchars($e->getMessage()) . '</div>';
                        }
                    }
                }
                
                echo '<div class="success"><strong>' . $successCount . ' colonne(s) ajoutée(s)</strong></div>';
            }
            
            // Verify final structure
            echo '<h2>3️⃣ Vérification finale</h2>';
            
            $finalColumnsQuery = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'gestion_evenements' AND TABLE_NAME = 'utilisateur' ORDER BY ORDINAL_POSITION";
            $stmt = $db->prepare($finalColumnsQuery);
            $stmt->execute();
            $finalColumns = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            echo '<div class="success">✅ Total de colonnes: ' . count($finalColumns) . '</div>';
            echo '<div class="info">Colonnes finales: ' . implode(', ', $finalColumns) . '</div>';
            
            echo '<div style="margin-top: 30px; padding: 15px; background-color: #e7f3ff; border-left: 4px solid #2196F3;">
                <strong>✅ Table utilisateur corrigée!</strong><br>
                Vous pouvez maintenant accéder à: <a href="setup-complete.php">setup-complete.php</a>
            </div>';
            
        } catch (Exception $e) {
            echo '<div class="error">❌ <strong>Erreur:</strong><br>' . htmlspecialchars($e->getMessage()) . '</div>';
            echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
        }
        ?>
    </div>
</body>
</html>
