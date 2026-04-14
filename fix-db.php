<?php
/**
 * Fix Database Schema - Add Missing Columns
 * Accédez à: http://localhost/projetweb_avec_evenements/fix-db.php
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
    <title>Corriger la Base de Données</title>
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
        <h1>🔧 Corriger la Base de Données</h1>
        
        <?php
        try {
            $db = Database::getInstance()->getConnection();
            
            echo '<h2>1️⃣ Vérification des colonnes manquantes</h2>';
            
            // Check existing columns
            $columnsQuery = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'gestion_evenements' AND TABLE_NAME = 'evenement'";
            $stmt = $db->prepare($columnsQuery);
            $stmt->execute();
            $existingColumns = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            echo '<div class="info">Colonnes actuelles: ' . implode(', ', $existingColumns) . '</div>';
            
            // Define all required columns
            $requiredColumns = [
                'idEvenement', 'titre', 'description', 'date', 'heure', 'lieu', 
                'categorie', 'organisateur', 'idOrganisateur', 'intervenants', 
                'inscrits', 'max', 'statut', 'programme', 'documents', 'replays',
                'date_creation', 'date_modification'
            ];
            
            // Find missing columns
            $missingColumns = array_diff($requiredColumns, $existingColumns);
            
            if (count($missingColumns) === 0) {
                echo '<div class="success">✅ Toutes les colonnes existent!</div>';
            } else {
                echo '<div class="error">❌ ' . count($missingColumns) . ' colonne(s) manquante(s): ' . implode(', ', $missingColumns) . '</div>';
                
                echo '<h2>2️⃣ Ajout des colonnes manquantes</h2>';
                
                // Add missing columns
                $alterStatements = [
                    'heure' => "ALTER TABLE `evenement` ADD COLUMN `heure` TIME AFTER `date`",
                    'categorie' => "ALTER TABLE `evenement` ADD COLUMN `categorie` VARCHAR(50) COMMENT 'tech, emploi, culture, formation' AFTER `lieu`",
                    'organisateur' => "ALTER TABLE `evenement` ADD COLUMN `organisateur` VARCHAR(255) AFTER `idOrganisateur`",
                    'intervenants' => "ALTER TABLE `evenement` ADD COLUMN `intervenants` TEXT COMMENT 'Liste des intervenants' AFTER `organisateur`",
                    'inscrits' => "ALTER TABLE `evenement` ADD COLUMN `inscrits` INT DEFAULT 0 AFTER `intervenants`",
                    'max' => "ALTER TABLE `evenement` ADD COLUMN `max` INT DEFAULT 0 COMMENT 'Nombre maximum de participants' AFTER `inscrits`",
                    'statut' => "ALTER TABLE `evenement` ADD COLUMN `statut` VARCHAR(50) DEFAULT 'Ouvert' COMMENT 'Ouvert, Complet, Annulé' AFTER `max`",
                    'programme' => "ALTER TABLE `evenement` ADD COLUMN `programme` TEXT COMMENT 'Programme détaillé' AFTER `statut`",
                    'documents' => "ALTER TABLE `evenement` ADD COLUMN `documents` TEXT COMMENT 'URLs des documents' AFTER `programme`",
                    'replays' => "ALTER TABLE `evenement` ADD COLUMN `replays` TEXT COMMENT 'URLs des replays vidéo' AFTER `documents`",
                    'date_creation' => "ALTER TABLE `evenement` ADD COLUMN `date_creation` TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER `replays`",
                    'date_modification' => "ALTER TABLE `evenement` ADD COLUMN `date_modification` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `date_creation`"
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
            
            // Insert sample data if needed
            echo '<h2>3️⃣ Vérification des données</h2>';
            
            $countQuery = "SELECT COUNT(*) as total FROM evenement";
            $stmt = $db->prepare($countQuery);
            $stmt->execute();
            $count = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo '<div class="info">Nombre d\'événements: ' . $count['total'] . '</div>';
            
            if ($count['total'] === 0) {
                echo '<h3>Insertion des données d\'exemple</h3>';
                
                $insertSQL = "INSERT IGNORE INTO `evenement` (titre, description, date, heure, lieu, categorie, organisateur, idOrganisateur, intervenants, inscrits, max, statut, programme) VALUES
                ('Tunisia Tech Summit 2026', 'Une conférence majeure sur les dernières technologies', '2026-05-08', '09:00', 'Palais des Congrès, Tunis', 'tech', 'Mohamed Ben Ali', NULL, 'Ahmed Trabelsi, Leila Khazri', 380, 500, 'Ouvert', 'Accueil, Conférences IA, Pause, Workshops'),
                ('Hackathon Innovation Sociale', 'Un hackathon pour développer des solutions sociales', '2026-05-15', '10:00', 'Hub Sfax, Sfax', 'tech', 'Leila Khazri', NULL, 'Rami Ben Fraj', 120, 150, 'Ouvert', 'Présentation, Hack, Dîner, Présentation projets'),
                ('Journée Portes Ouvertes Emploi', 'Rencontrez les meilleures entreprises tunisiennes', '2026-05-22', '08:30', 'Palais des Expositions, Tunis', 'emploi', 'Jobyfind', NULL, 'Entreprises partenaires', 610, 800, 'Ouvert', 'Accueil, Stands entreprises, CV déposés, Entretiens'),
                ('Festival de Cinéma Arabe', 'Festival de cinéma avec films et débats', '2026-06-01', '18:00', 'Cité de la Culture, Tunis', 'culture', 'Sonia Mansour', NULL, 'Réalisateurs invités', 200, 200, 'Complet', 'Ouverture, Projections, Débat, Clôture')";
                
                try {
                    $db->exec($insertSQL);
                    echo '<div class="success">✅ Données d\'exemple insérées</div>';
                } catch (Exception $e) {
                    echo '<div class="error">❌ Erreur: ' . htmlspecialchars($e->getMessage()) . '</div>';
                }
            }
            
            // Final verification
            echo '<h2>4️⃣ Vérification finale</h2>';
            
            $finalColumnsQuery = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'gestion_evenements' AND TABLE_NAME = 'evenement' ORDER BY ORDINAL_POSITION";
            $stmt = $db->prepare($finalColumnsQuery);
            $stmt->execute();
            $finalColumns = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            echo '<div class="success">✅ Total de colonnes: ' . count($finalColumns) . '</div>';
            echo '<div class="info">Colonnes finales: ' . implode(', ', $finalColumns) . '</div>';
            
            echo '<div style="margin-top: 30px; padding: 15px; background-color: #e7f3ff; border-left: 4px solid #2196F3;">
                <strong>✅ Base de données corrigée!</strong><br>
                Vous pouvez maintenant accéder à: <a href="test-crud.php">test-crud.php</a>
            </div>';
            
        } catch (Exception $e) {
            echo '<div class="error">❌ <strong>Erreur:</strong><br>' . htmlspecialchars($e->getMessage()) . '</div>';
            echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
        }
        ?>
    </div>
</body>
</html>
