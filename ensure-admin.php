<?php
/**
 * Ensure Admin User Exists
 * Accédez à: http://localhost/projetweb_avec_evenements/ensure-admin.php
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
    <title>Vérifier l'Admin</title>
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
    </style>
</head>
<body>
    <div class="container">
        <h1>👨‍💼 Vérifier l'Administrateur</h1>
        
        <?php
        try {
            $db = Database::getInstance()->getConnection();
            
            // Check if user with ID 1 exists
            $checkQuery = "SELECT * FROM utilisateur WHERE idUtilisateur = 1";
            $stmt = $db->prepare($checkQuery);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                echo '<div class="success">✅ Administrateur trouvé!</div>';
                echo '<div class="info">
                    <strong>ID:</strong> ' . $user['idUtilisateur'] . '<br>
                    <strong>Prénom:</strong> ' . htmlspecialchars($user['prenom']) . '<br>
                    <strong>Nom:</strong> ' . htmlspecialchars($user['nom']) . '<br>
                    <strong>Email:</strong> ' . htmlspecialchars($user['email']) . '<br>
                    <strong>Rôle:</strong> ' . htmlspecialchars($user['role']) . '<br>
                </div>';
            } else {
                echo '<div class="error">❌ Pas d\'administrateur trouvé (ID 1)</div>';
                echo '<p>Création d\'un administrateur par défaut...</p>';
                
                $insertQuery = "INSERT INTO utilisateur (idUtilisateur, prenom, nom, email, role, status) 
                              VALUES (1, 'Admin', 'Système', 'admin@system.local', 'Entreprise', 'Actif')";
                
                try {
                    $db->exec($insertQuery);
                    echo '<div class="success">✅ Administrateur créé!</div>';
                } catch (Exception $e) {
                    echo '<div class="error">Erreur: ' . htmlspecialchars($e->getMessage()) . '</div>';
                }
            }
            
            // Show all users
            echo '<h2>📋 Tous les utilisateurs</h2>';
            
            $allQuery = "SELECT * FROM utilisateur ORDER BY idUtilisateur";
            $stmt = $db->prepare($allQuery);
            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($users) > 0) {
                echo '<div class="success">✅ ' . count($users) . ' utilisateur(s) trouvé(s)</div>';
                echo '<table style="width:100%; border-collapse:collapse;">';
                echo '<tr style="background-color:#007bff;color:white;"><th style="padding:8px;border:1px solid #ddd;">ID</th><th style="padding:8px;border:1px solid #ddd;">Prénom</th><th style="padding:8px;border:1px solid #ddd;">Nom</th><th style="padding:8px;border:1px solid #ddd;">Email</th><th style="padding:8px;border:1px solid #ddd;">Rôle</th></tr>';
                foreach ($users as $u) {
                    echo '<tr>';
                    echo '<td style="padding:8px;border:1px solid #ddd;">' . $u['idUtilisateur'] . '</td>';
                    echo '<td style="padding:8px;border:1px solid #ddd;">' . htmlspecialchars($u['prenom']) . '</td>';
                    echo '<td style="padding:8px;border:1px solid #ddd;">' . htmlspecialchars($u['nom']) . '</td>';
                    echo '<td style="padding:8px;border:1px solid #ddd;">' . htmlspecialchars($u['email']) . '</td>';
                    echo '<td style="padding:8px;border:1px solid #ddd;">' . htmlspecialchars($u['role']) . '</td>';
                    echo '</tr>';
                }
                echo '</table>';
            } else {
                echo '<div class="info">Aucun utilisateur</div>';
            }
            
            echo '<div style="margin-top: 30px; padding: 15px; background-color: #e7f3ff; border-left: 4px solid #2196F3;">
                <strong>✅ Vérification terminée!</strong><br>
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
