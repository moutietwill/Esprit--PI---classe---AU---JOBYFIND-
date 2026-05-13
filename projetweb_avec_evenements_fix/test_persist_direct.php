<?php
require_once __DIR__ . '/controllers/Controller.php';
require_once __DIR__ . '/controllers/AdminController.php';
require_once __DIR__ . '/models/Event.php';

class TestController extends AdminController {
    public function testPersist() {
        $event = new Event([
            'titre' => 'Test Event ' . time(),
            'description' => 'This is a test event description long enough.',
            'date' => date('Y-m-d'),
            'lieu' => 'Test Location',
            'idOrganisateur' => '1',
            'image' => 'public/assets/images/event/e1.png'
        ]);
        
        try {
            $result = $this->persistEvent($event);
            if ($result) {
                echo "Success! ID: " . $event->getId() . "\n";
            } else {
                echo "Failed to persist event.\n";
            }
        } catch (Exception $e) {
            echo "Exception: " . $e->getMessage() . "\n";
        }
    }
}

$test = new TestController();
$test->testPersist();
