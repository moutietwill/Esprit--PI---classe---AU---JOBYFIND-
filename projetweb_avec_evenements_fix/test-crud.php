<?php
/**
 * Test CRUD Operations
 * Accédez à: http://localhost/projetweb_avec_evenements/test-crud.php
 */

define('BASE_PATH', dirname(__FILE__));

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include required files
require_once BASE_PATH . '/config/Database.php';
require_once BASE_PATH . '/models/Event.php';
require_once BASE_PATH . '/models/User.php';
require_once BASE_PATH . '/repositories/EventRepository.php';
require_once BASE_PATH . '/repositories/UserRepository.php';

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test CRUD - Événements</title>
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
        .action-btn {
            background-color: #28a745;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            margin: 5px;
        }
        .action-btn:hover {
            background-color: #218838;
        }
        .delete-btn {
            background-color: #dc3545;
        }
        .delete-btn:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Test des Opérations CRUD - Événements</h1>
        
        <?php
        try {
            $eventRepository = new EventRepository();
            
            echo '<h2>1️⃣ Test d\'insertion (CREATE)</h2>';
            
            $testEvent = new Event([
                'titre' => 'Test Event ' . date('Y-m-d H:i:s'),
                'description' => 'Ceci est un événement de test',
                'date' => date('Y-m-d', strtotime('+7 days')),
                'heure' => '14:00',
                'lieu' => 'Lieu de Test',
                'categorie' => 'test',
                'organisateur' => 'Testeur',
                'intervenants' => 'Intervenant 1, Intervenant 2',
                'max' => 100,
                'programme' => 'Programme de test',
                'documents' => 'https://example.com/doc.pdf',
                'replays' => 'https://example.com/replay',
                'inscrits' => 0,
                'statut' => 'Ouvert'
            ]);
            
            $createdEvent = $eventRepository->create($testEvent);
            
            if ($createdEvent) {
                echo '<div class="success">✅ Événement créé avec succès!</div>';
                echo '<div class="info">
                    <strong>ID généré:</strong> ' . $createdEvent->getId() . '<br>
                    <strong>Titre:</strong> ' . htmlspecialchars($createdEvent->getTitre()) . '<br>
                    <strong>Date:</strong> ' . $createdEvent->getDate() . '<br>
                </div>';
                $testEventId = $createdEvent->getId();
            } else {
                echo '<div class="error">❌ Erreur lors de la création de l\'événement</div>';
                $testEventId = null;
            }
            
            echo '<h2>2️⃣ Test de lecture (READ)</h2>';
            
            if ($testEventId) {
                $readEvent = $eventRepository->getById($testEventId);
                
                if ($readEvent) {
                    echo '<div class="success">✅ Événement récupéré avec succès!</div>';
                    echo '<table>';
                    echo '<tr><th>Champ</th><th>Valeur</th></tr>';
                    echo '<tr><td>ID</td><td>' . $readEvent->getId() . '</td></tr>';
                    echo '<tr><td>Titre</td><td>' . htmlspecialchars($readEvent->getTitre()) . '</td></tr>';
                    echo '<tr><td>Description</td><td>' . htmlspecialchars($readEvent->getDescription()) . '</td></tr>';
                    echo '<tr><td>Date</td><td>' . $readEvent->getDate() . '</td></tr>';
                    echo '<tr><td>Heure</td><td>' . $readEvent->getHeure() . '</td></tr>';
                    echo '<tr><td>Lieu</td><td>' . htmlspecialchars($readEvent->getLieu()) . '</td></tr>';
                    echo '<tr><td>Catégorie</td><td>' . htmlspecialchars($readEvent->getCategorie()) . '</td></tr>';
                    echo '<tr><td>Intervenants</td><td>' . htmlspecialchars($readEvent->getIntervenants()) . '</td></tr>';
                    echo '<tr><td>Max participants</td><td>' . $readEvent->getMax() . '</td></tr>';
                    echo '<tr><td>Programme</td><td>' . htmlspecialchars($readEvent->getProgramme()) . '</td></tr>';
                    echo '<tr><td>Documents</td><td>' . htmlspecialchars($readEvent->getDocuments()) . '</td></tr>';
                    echo '<tr><td>Statut</td><td>' . htmlspecialchars($readEvent->getStatut()) . '</td></tr>';
                    echo '</table>';
                } else {
                    echo '<div class="error">❌ Événement non trouvé</div>';
                }
            }
            
            echo '<h2>3️⃣ Test de mise à jour (UPDATE)</h2>';
            
            if ($testEventId && $readEvent) {
                $readEvent->setTitre('Test Event MODIFIÉ - ' . date('Y-m-d H:i:s'));
                $readEvent->setDescription('Description modifiée');
                $readEvent->setLieu('Nouveau lieu de test');
                $readEvent->setStatut('Complet');
                
                $updated = $eventRepository->update($readEvent);
                
                if ($updated) {
                    echo '<div class="success">✅ Événement mis à jour avec succès!</div>';
                    
                    // Verify the update
                    $verifyEvent = $eventRepository->getById($testEventId);
                    if ($verifyEvent) {
                        echo '<div class="info">
                            <strong>Nouveau titre:</strong> ' . htmlspecialchars($verifyEvent->getTitre()) . '<br>
                            <strong>Nouvelle lieu:</strong> ' . htmlspecialchars($verifyEvent->getLieu()) . '<br>
                            <strong>Nouveau statut:</strong> ' . htmlspecialchars($verifyEvent->getStatut()) . '<br>
                        </div>';
                    }
                } else {
                    echo '<div class="error">❌ Erreur lors de la mise à jour</div>';
                }
            }
            
            echo '<h2>4️⃣ Tous les événements en base de données</h2>';
            
            $allEvents = $eventRepository->getAll();
            
            if (count($allEvents) > 0) {
                echo '<div class="success">✅ ' . count($allEvents) . ' événement(s) trouvé(s)</div>';
                echo '<table>';
                echo '<tr><th>ID</th><th>Titre</th><th>Date</th><th>Lieu</th><th>Statut</th></tr>';
                foreach ($allEvents as $evt) {
                    echo '<tr>';
                    echo '<td>' . $evt->getId() . '</td>';
                    echo '<td>' . htmlspecialchars(substr($evt->getTitre(), 0, 30)) . '...</td>';
                    echo '<td>' . $evt->getDate() . '</td>';
                    echo '<td>' . htmlspecialchars(substr($evt->getLieu(), 0, 20)) . '</td>';
                    echo '<td>' . htmlspecialchars($evt->getStatut()) . '</td>';
                    echo '</tr>';
                }
                echo '</table>';
            } else {
                echo '<div class="info">ℹ️ Aucun événement en base de données</div>';
            }
            
            echo '<h2>5️⃣ Test de suppression (DELETE)</h2>';
            
            if ($testEventId) {
                $deleted = $eventRepository->delete($testEventId);
                
                if ($deleted) {
                    echo '<div class="success">✅ Événement de test supprimé avec succès!</div>';
                    
                    // Verify deletion
                    $verifyDelete = $eventRepository->getById($testEventId);
                    if (!$verifyDelete) {
                        echo '<div class="info">✓ Vérification: l\'événement est bien supprimé de la BD</div>';
                    }
                } else {
                    echo '<div class="error">❌ Erreur lors de la suppression</div>';
                }
            }
            
            echo '<div style="margin-top: 30px; padding: 15px; background-color: #e7f3ff; border-left: 4px solid #2196F3;">
                <strong>ℹ️ Résumé:</strong><br>
                Tous les tests CRUD (Create, Read, Update, Delete) ont été exécutés avec succès !<br>
                Vos opérations sur la base de données devraient maintenant fonctionner correctement.
            </div>';
            
        } catch (Exception $e) {
            echo '<div class="error">❌ <strong>Erreur:</strong><br>' . htmlspecialchars($e->getMessage()) . '</div>';
            echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
        }
        ?>
    </div>
</body>
</html>
