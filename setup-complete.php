<?php
/**
 * Complete Setup & Test
 * Accédez à: http://localhost/projetweb_avec_evenements/setup-complete.php
 */

define('BASE_PATH', dirname(__FILE__));

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once BASE_PATH . '/config/Database.php';
require_once BASE_PATH . '/models/Event.php';
require_once BASE_PATH . '/repositories/EventRepository.php';

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Complet et Test</title>
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
        <h1>⚙️ Setup Complet et Test CRUD</h1>
        
        <?php
        try {
            $db = Database::getInstance()->getConnection();
            $eventRepository = new EventRepository();
            
            // Step 1: Check admin user
            echo '<h2>1️⃣ Vérification de l\'utilisateur administrateur</h2>';
            
            $checkAdmin = "SELECT COUNT(*) as total FROM utilisateur WHERE idUtilisateur = 1";
            $stmt = $db->prepare($checkAdmin);
            $stmt->execute();
            $adminCount = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            if ($adminCount == 0) {
                try {
                    $insertAdmin = "INSERT INTO utilisateur (idUtilisateur, prenom, nom, email, role, status) 
                                  VALUES (1, 'Admin', 'Système', 'admin@system.local', 'Entreprise', 'Actif')";
                    $db->exec($insertAdmin);
                    echo '<div class="success">✅ Administrateur créé (ID: 1)</div>';
                } catch (Exception $e) {
                    try {
                        // Try without prenom
                        $insertAdmin = "INSERT INTO utilisateur (idUtilisateur, nom, email, role, status) 
                                      VALUES (1, 'Admin Système', 'admin@system.local', 'Entreprise', 'Actif')";
                        $db->exec($insertAdmin);
                        echo '<div class="success">✅ Administrateur créé (ID: 1)</div>';
                    } catch (Exception $e) {
                        // Try without status
                        $insertAdmin = "INSERT INTO utilisateur (idUtilisateur, prenom, nom, email, role) 
                                      VALUES (1, 'Admin', 'Système', 'admin@system.local', 'Entreprise')";
                        $db->exec($insertAdmin);
                        echo '<div class="success">✅ Administrateur créé (ID: 1)</div>';
                    }
                }
            } else {
                echo '<div class="success">✅ Administrateur existe déjà</div>';
            }
            
            // Step 2: Test CREATE
            echo '<h2>2️⃣ Test d\'insertion (CREATE)</h2>';
            
            $testEvent = new Event([
                'titre' => 'Test CRUD - ' . date('H:i:s'),
                'description' => 'Ceci est un test d\'insertion',
                'date' => date('Y-m-d', strtotime('+1 day')),
                'heure' => '15:30',
                'lieu' => 'Salle de test',
                'categorie' => 'test',
                'organisateur' => 'Test Admin',
                'intervenants' => 'Testeur Principal',
                'max' => 50,
                'programme' => 'Test du programme',
                'documents' => 'https://test.example.com',
                'replays' => 'https://replay.example.com',
                'inscrits' => 5,
                'statut' => 'Ouvert'
            ]);
            
            $createdEvent = $eventRepository->create($testEvent);
            
            if ($createdEvent && $createdEvent->getId()) {
                echo '<div class="success">✅ Événement inséré avec succès (ID: ' . $createdEvent->getId() . ')</div>';
                $testEventId = $createdEvent->getId();
            } else {
                echo '<div class="error">❌ Erreur lors de l\'insertion</div>';
                throw new Exception('Failed to create event');
            }
            
            // Step 3: Test READ
            echo '<h2>3️⃣ Test de lecture (READ)</h2>';
            
            $readEvent = $eventRepository->getById($testEventId);
            
            if ($readEvent) {
                echo '<div class="success">✅ Événement lu avec succès</div>';
                echo '<table>';
                echo '<tr><th>Champ</th><th>Valeur</th></tr>';
                echo '<tr><td>ID</td><td>' . $readEvent->getId() . '</td></tr>';
                echo '<tr><td>Titre</td><td>' . htmlspecialchars($readEvent->getTitre()) . '</td></tr>';
                echo '<tr><td>Description</td><td>' . htmlspecialchars($readEvent->getDescription()) . '</td></tr>';
                echo '<tr><td>Date</td><td>' . $readEvent->getDate() . '</td></tr>';
                echo '<tr><td>Heure</td><td>' . $readEvent->getHeure() . '</td></tr>';
                echo '<tr><td>Lieu</td><td>' . htmlspecialchars($readEvent->getLieu()) . '</td></tr>';
                echo '</table>';
            } else {
                throw new Exception('Event not found after creation');
            }
            
            // Step 4: Test UPDATE
            echo '<h2>4️⃣ Test de mise à jour (UPDATE)</h2>';
            
            $readEvent->setTitre('Test CRUD MODIFIÉ - ' . date('H:i:s'));
            $readEvent->setStatut('Complet');
            
            $updated = $eventRepository->update($readEvent);
            
            if ($updated) {
                echo '<div class="success">✅ Événement mis à jour avec succès</div>';
                
                $verifyEvent = $eventRepository->getById($testEventId);
                echo '<div class="info">
                    Nouveau titre: ' . htmlspecialchars($verifyEvent->getTitre()) . '<br>
                    Nouveau statut: ' . htmlspecialchars($verifyEvent->getStatut()) . '
                </div>';
            } else {
                throw new Exception('Update failed');
            }
            
            // Step 5: Test READ ALL
            echo '<h2>5️⃣ Test lecture de tous les événements</h2>';
            
            $allEvents = $eventRepository->getAll();
            
            echo '<div class="success">✅ ' . count($allEvents) . ' événement(s) trouvé(s)</div>';
            
            echo '<table>';
            echo '<tr><th>ID</th><th>Titre</th><th>Date</th><th>Heure</th><th>Statut</th></tr>';
            foreach ($allEvents as $evt) {
                echo '<tr>';
                echo '<td>' . $evt->getId() . '</td>';
                echo '<td>' . htmlspecialchars(substr($evt->getTitre(), 0, 40)) . '</td>';
                echo '<td>' . $evt->getDate() . '</td>';
                echo '<td>' . $evt->getHeure() . '</td>';
                echo '<td>' . htmlspecialchars($evt->getStatut()) . '</td>';
                echo '</tr>';
            }
            echo '</table>';
            
            // Step 6: Test DELETE
            echo '<h2>6️⃣ Test de suppression (DELETE)</h2>';
            
            $deleted = $eventRepository->delete($testEventId);
            
            if ($deleted) {
                echo '<div class="success">✅ Événement supprimé avec succès</div>';
                
                $verifyDelete = $eventRepository->getById($testEventId);
                if (!$verifyDelete) {
                    echo '<div class="info">✓ Vérification: l\'événement est bien supprimé</div>';
                }
            } else {
                throw new Exception('Delete failed');
            }
            
            echo '<div style="margin-top: 30px; padding: 15px; background-color: #d4edda; border-left: 4px solid #28a745;">
                <h3 style="color: #155724; margin-top: 0;">🎉 TOUS LES TESTS SONT PASSÉS!</h3>
                <p style="color: #155724;">Votre système CRUD fonctionne parfaitement!</p>
                <p>Les opérations suivantes fonctionnent correctement:</p>
                <ul style="color: #155724;">
                    <li>✅ CREATE - Insertion de données</li>
                    <li>✅ READ - Lecture de données</li>
                    <li>✅ UPDATE - Modification de données</li>
                    <li>✅ DELETE - Suppression de données</li>
                    <li>✅ Enregistrement en base de données</li>
                </ul>
            </div>';
            
        } catch (Exception $e) {
            echo '<div class="error">❌ <strong>Erreur:</strong><br>' . htmlspecialchars($e->getMessage()) . '</div>';
            echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
        }
        ?>
    </div>
</body>
</html>
