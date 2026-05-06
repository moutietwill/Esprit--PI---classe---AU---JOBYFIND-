<?php
require_once __DIR__ . '/../models/Offre.php';

class FrontController {
    
    public function index() {
        require_once __DIR__ . '/../config/Database.php';
        $db = Database::getInstance();
        $stmt = $db->query("SELECT * FROM offre ORDER BY datePublication DESC");
        $offres = $stmt->fetchAll();
        require_once __DIR__ . '/../views/front/offres/index.php';
    }
}
?>
