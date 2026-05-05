<?php
/**
 * Test script for adding an event via the admin form
 */

// Simulate POST request
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST = [
    'titre' => 'Événement Test',
    'description' => 'Ceci est une description de test pour vérifier le fonctionnement',
    'date' => date('Y-m-d', strtotime('+1 day')),
    'lieu' => 'Paris',
    'idOrganisateur' => '1'
];

// Mock $_FILES if needed
$_FILES = [];

// Autoload
require_once __DIR__ . '/core/Autoloader.php';
$autoloader = new Autoloader(__DIR__);
$autoloader->register();

// Load config
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/models/Event.php';
require_once __DIR__ . '/controllers/Controller.php';
require_once __DIR__ . '/controllers/AdminController.php';

// Test the storeEvent method
try {
    $admin = new AdminController();
    echo "Testing storeEvent()...\n";
    $admin->storeEvent();
    echo "Test completed.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
?>
