<?php
require_once __DIR__ . '/config/QRCode.php';
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/models/Event.php';

// Récupérer un événement de test
$db = Database::getInstance();
$connection = $db->getConnection();

// Récupérer le premier événement
$query = "SELECT * FROM evenements LIMIT 1";
$result = $connection->query($query);
$eventData = $result->fetch();

if ($eventData) {
    $event = new Event($eventData);
    $eventUrl = QRCode::getEventUrl($event->getId());
    $regUrl = QRCode::getEventUrl($event->getId()); // Pour l'inscription
    
    echo "<h1>Test URL QR Code</h1>";
    echo "<p><strong>Événement:</strong> " . htmlspecialchars($event->getTitre()) . "</p>";
    echo "<p><strong>ID:</strong> " . $event->getId() . "</p>";
    echo "<hr>";
    echo "<h2>URL Événement:</h2>";
    echo "<pre>" . htmlspecialchars($eventUrl) . "</pre>";
    echo "<p><a href='" . htmlspecialchars($eventUrl) . "' target='_blank'>Tester l'URL</a></p>";
    echo "<hr>";
    echo "<h2>Server Info:</h2>";
    echo "<pre>";
    echo "HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? 'N/A') . "\n";
    echo "SERVER_ADDR: " . ($_SERVER['SERVER_ADDR'] ?? 'N/A') . "\n";
    echo "SERVER_PORT: " . ($_SERVER['SERVER_PORT'] ?? 'N/A') . "\n";
    echo "SCRIPT_NAME: " . ($_SERVER['SCRIPT_NAME'] ?? 'N/A') . "\n";
    echo "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'N/A') . "\n";
    echo "</pre>";
} else {
    echo "<p>Aucun événement trouvé</p>";
}
?>
