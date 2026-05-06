<?php
// index.php - Routeur principal MVC

$action = isset($_GET['action']) ? $_GET['action'] : 'front_offres';

switch ($action) {
    // --- FRONT OFFICE ---
    case 'front_offres':
        require_once 'controllers/FrontController.php';
        $controller = new FrontController();
        $controller->index();
        break;

    // --- BACK OFFICE OFFRES ---
    case 'list_offres':
        require_once 'controllers/OffreController.php';
        $controller = new OffreController();
        $controller->index();
        break;
    case 'add_offre':
        require_once 'controllers/OffreController.php';
        $controller = new OffreController();
        $controller->create();
        break;
    case 'edit_offre':
        require_once 'controllers/OffreController.php';
        $controller = new OffreController();
        $controller->edit();
        break;
    case 'delete_offre':
        require_once 'controllers/OffreController.php';
        $controller = new OffreController();
        $controller->delete();
        break;

    // --- BACK OFFICE CANDIDATURES ---
    case 'list_candidatures':
        require_once 'controllers/CandidatureController.php';
        $controller = new CandidatureController();
        $controller->index();
        break;
    case 'list_candidatures_offre':
        require_once 'controllers/CandidatureController.php';
        $controller = new CandidatureController();
        $controller->indexByOffre();
        break;
    case 'add_candidature':
        require_once 'controllers/CandidatureController.php';
        $controller = new CandidatureController();
        $controller->create();
        break;
    case 'edit_candidature':
        require_once 'controllers/CandidatureController.php';
        $controller = new CandidatureController();
        $controller->edit();
        break;
    case 'delete_candidature':
        require_once 'controllers/CandidatureController.php';
        $controller = new CandidatureController();
        $controller->delete();
        break;

    case 'stats_offres':
        require_once 'controllers/OffreController.php';
        $controller = new OffreController();
        $controller->stats();
        break;
    case 'pdf_offres':
        require_once 'controllers/OffreController.php';
        $controller = new OffreController();
        $controller->exportPdf();
        break;

    case 'stats_candidatures':
        require_once 'controllers/CandidatureController.php';
        $controller = new CandidatureController();
        $controller->stats();
        break;

    case 'candidature_success':
        require_once __DIR__ . '/views/front/candidatures/success.php';
        break;

    default:
        echo "Action non reconnue.";
        break;
}
?>
