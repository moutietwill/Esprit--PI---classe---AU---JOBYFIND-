<?php
/**
 * Initialize Database - Execute Schema
 * Accédez à: http://localhost/projetweb_avec_evenements/init-db.php
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
    <title>Initialiser la Base de Données</title>
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
        .table-info {
            margin-top: 20px;
        }
        .column-list {
            background-color: #f9f9f9;
            padding: 10px;
            border-left: 3px solid #007bff;
            margin: 10px 0;
            font-family: monospace;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>⚙️ Initialiser la Base de Données</h1>
        
        <?php
        try {
            $db = Database::getInstance()->getConnection();
            
            // Read schema file
            $schemaFile = BASE_PATH . '/database/schema.sql';
            if (!file_exists($schemaFile)) {
                echo '<div class="error">❌ Fichier schema.sql non trouvé: ' . $schemaFile . '</div>';
                exit;
            }
            
            $schemaSQL = file_get_contents($schemaFile);
            
            // Split by semicolon
            $queries = array_filter(array_map('trim', explode(';', $schemaSQL)));
            
            echo '<h2>📋 Exécution du schéma</h2>';
            
            $executedCount = 0;
            foreach ($queries as $query) {
                if (empty($query) || strpos($query, '--') === 0) {
                    continue;
                }
                
                try {
                    $db->exec($query);
                    $executedCount++;
                } catch (Exception $e) {
                    echo '<div class="error">Erreur lors de l\'exécution: ' . htmlspecialchars($e->getMessage()) . '</div>';
                }
            }
            
            echo '<div class="success">✅ ' . $executedCount . ' requête(s) exécutée(s) avec succès</div>';
            
            // Check tables
            echo '<h2>📊 Tables créées</h2>';
            
            $tablesQuery = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'gestion_evenements'";
            $stmt = $db->prepare($tablesQuery);
            $stmt->execute();
            $tables = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($tables) > 0) {
                echo '<div class="success">✅ ' . count($tables) . ' table(s) trouvée(s)</div>';
                
                foreach ($tables as $table) {
                    $tableName = $table['TABLE_NAME'];
                    echo '<div class="table-info">';
                    echo '<h3>' . htmlspecialchars($tableName) . '</h3>';
                    
                    // Get columns
                    $columnsQuery = "SELECT COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_KEY FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'gestion_evenements' AND TABLE_NAME = ?";
                    $stmtCols = $db->prepare($columnsQuery);
                    $stmtCols->execute([$tableName]);
                    $columns = $stmtCols->fetchAll(PDO::FETCH_ASSOC);
                    
                    echo '<div class="column-list">';
                    foreach ($columns as $col) {
                        $key = $col['COLUMN_KEY'] ? ' [' . $col['COLUMN_KEY'] . ']' : '';
                        $nullable = $col['IS_NULLABLE'] === 'YES' ? '(NULL)' : '(NOT NULL)';
                        echo htmlspecialchars($col['COLUMN_NAME']) . ' : ' . htmlspecialchars($col['COLUMN_TYPE']) . ' ' . $nullable . $key . '<br>';
                    }
                    echo '</div>';
                    
                    // Count rows
                    $countQuery = "SELECT COUNT(*) as total FROM `$tableName`";
                    $stmtCount = $db->prepare($countQuery);
                    $stmtCount->execute();
                    $count = $stmtCount->fetch(PDO::FETCH_ASSOC);
                    echo '<p>📝 ' . $count['total'] . ' enregistrement(s)</p>';
                    echo '</div>';
                }
            } else {
                echo '<div class="error">❌ Aucune table trouvée</div>';
            }
            
            echo '<div style="margin-top: 30px; padding: 15px; background-color: #e7f3ff; border-left: 4px solid #2196F3;">
                <strong>✅ Base de données initialisée avec succès!</strong><br>
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
