<?php
require 'app/config/Database.php';
require 'app/models/Event.php';
require 'app/repositories/EventRepository.php';

try {
    $repo = new EventRepository();
    $event = new Event([
        'titre' => 'Test Ajout Direct ' . time(),
        'description' => 'Test Database Insert',
        'date' => '2026-04-15',
        'lieu' => 'Paris',
        'idOrganisateur' => 1
    ]);
    
    $result = $repo->create($event);
    echo "Result: ";
    var_dump($result);
    
    if ($result) {
        echo "\n✓ Event created successfully with ID: " . $result->getId();
    } else {
        echo "\n✗ Failed to create event";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
