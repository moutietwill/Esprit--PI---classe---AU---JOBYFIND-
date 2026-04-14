<?php
/**
 * Test Database Connection
 * Accédez à: http://localhost/projetweb_avec_evenements/test-connection.php
 */

// Define base path
define('BASE_PATH', dirname(__FILE__));

// Auto-load classes
spl_autoload_register(function ($class) {
    $file = BASE_PATH . '/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Connexion Base de Données</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 600px;
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
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        table, th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #007bff;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔒 Test Connexion Base de Données</h1>
        
        <?php
        try {
            // Get database instance
            $db = config\Database::getInstance();
            
            echo '<div class="success">✅ <strong>Connexion réussie!</strong> La base de données est connectée.</div>';
            
            // Get connection details
            echo '<div class="info">';
            echo '<strong>Détails de la connexion:</strong><br>';
            echo '• Base de données: <strong>gestion_evenements</strong><br>';
            echo '• Serveur: <strong>localhost</strong><br>';
            echo '• Utilisateur: <strong>root</strong><br>';
            echo '</div>';
            
            // Test database queries
            echo '<h2>📊 Test des Tables</h2>';
            
            // Check if tables exist
            $tables = $db->fetchAll("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'gestion_evenements'");
            
            if (count($tables) > 0) {
                echo '<div class="success">✅ <strong>Tables trouvées:</strong></div>';
                echo '<table>';
                echo '<tr><th>Nom de la Table</th><th>Nombre d\'enregistrements</th></tr>';
                
                foreach ($tables as $table) {
                    $tableName = $table['TABLE_NAME'];
                    $count = $db->fetch("SELECT COUNT(*) as total FROM `$tableName`");
                    echo '<tr>';
                    echo '<td><strong>' . htmlspecialchars($tableName) . '</strong></td>';
                    echo '<td>' . $count['total'] . '</td>';
                    echo '</tr>';
                }
                
                echo '</table>';
            } else {
                echo '<div class="info">ℹ️ Aucune table trouvée. Exécutez le fichier schema.sql pour créer les tables.</div>';
            }
            
            // Test writing to database
            echo '<h2>✏️ Test d\'insertion</h2>';
            
            try {
                $testQuery = "INSERT INTO `utilisateur` (prenom, nom, email, role, status) VALUES (?, ?, ?, ?, ?)";
                $db->execute($testQuery, ['Test', 'User', 'test@example.com', 'Entrepreneur', 'Actif']);
                echo '<div class="success">✅ Test d\'insertion réussi!</div>';
                
                // Delete test record
                $db->execute("DELETE FROM utilisateur WHERE email = ?", ['test@example.com']);
            } catch (Exception $e) {
                echo '<div class="error">⚠️ ' . htmlspecialchars($e->getMessage()) . '</div>';
            }
            
        } catch (Exception $e) {
            echo '<div class="error">❌ <strong>Erreur de connexion:</strong><br>' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        ?>
        
        <h2>📝 Informations Utiles</h2>
        <div class="info">
            <strong>PhpMyAdmin:</strong> <a href="http://localhost/phpmyadmin/" target="_blank">http://localhost/phpmyadmin/</a><br>
            <strong>Base de données:</strong> gestion_evenements<br>
            <strong>Fichier de configuration:</strong> app/config/Database.php
        </div>
    </div>
</body>
</html>
