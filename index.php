<?php
session_start();

$action = $_GET['action'] ?? 'home';

switch ($action) {
    case 'home':
        // On redirige vers le routeur login
        header('Location: index.php?action=login');
        break;

    // ----- AUTHENTIFICATION (Front) -----
    case 'login':
        require_once 'controllers/AuthController.php';
        $controller = new AuthController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->loginSubmit();
        } else {
            $controller->loginPage();
        }
        break;

    case 'register':
        require_once 'controllers/AuthController.php';
        $controller = new AuthController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->registerSubmit();
        } else {
            $controller->registerPage();
        }
        break;

    case 'logout':
        require_once 'controllers/AuthController.php';
        $controller = new AuthController();
        $controller->logout();
        break;

    // ----- PROFIL (Front) -----
    case 'profile':
        require_once 'controllers/UserController.php';
        $controller = new UserController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->updateProfile();
        } else {
            $controller->showProfile();
        }
        break;

    case 'delete_profile':
        require_once 'controllers/UserController.php';
        $controller = new UserController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->deleteProfile();
        }
        break;

    // ----- ADMIN (Back) -----
    case 'admin':
        require_once 'controllers/AdminController.php';
        $controller = new AdminController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['delete_id'])) {
                $controller->deleteUser();
            } else {
                $controller->saveUser(); // Create or Update
            }
        } else {
            $controller->index();
        }
        break;

    default:
        echo "404 Not Found";
        break;
}
?>
