<?php
require_once __DIR__ . '/../models/User.php';

class AdminController {

    private $userModel;

    public function __construct() {
        // En vrai ici on verifierait si l'admin est connecté
        // if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') { header('Location: index.php?action=login'); exit; }
        $this->userModel = new User();
    }

    public function index() {
        // Récupérer tout
        $users = $this->userModel->readAll();
        
        // Passer data à la vue
        require_once __DIR__ . '/../views/backoffice/admin.php';
    }

    public function saveUser() {
        // Ajouter ou Modifier un utilisateur (venant du modal admin)
        
        $id = $_POST['id'] ?? null;
        
        // Contrôle de saisie PHP
        $first_name = trim($_POST['first_name'] ?? '');
        $last_name = trim($_POST['last_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $role = $_POST['role'] ?? 'Entrepreneur';
        $status = $_POST['status'] ?? 'Actif';
        $date_of_birth = $_POST['date_of_birth'] ?? null;
        
        // Validation simple
        if(empty($first_name) || empty($last_name) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = "Données invalides. L'email, le nom et le prénom sont requis.";
            header("Location: index.php?action=admin");
            exit;
        }

        $this->userModel->first_name = $first_name;
        $this->userModel->last_name = $last_name;
        $this->userModel->email = $email;
        $this->userModel->phone = $phone;
        $this->userModel->city = $city;
        $this->userModel->role = $role;
        $this->userModel->status = $status;
        $this->userModel->date_of_birth = $date_of_birth;

        if ($id) {
            // Update
            $this->userModel->id = $id;
            if ($this->userModel->update()) {
                $_SESSION['success'] = "Utilisateur mis à jour.";
            } else {
                $_SESSION['error'] = "Erreur lors de la mise à jour.";
            }
        } else {
            // Create
            // Mot de passe par défaut pour création admin
            $this->userModel->password = 'password123'; 
            if ($this->userModel->emailExists($email)) {
                $_SESSION['error'] = "L'adresse email existe déjà.";
            } else {
                if ($this->userModel->create()) {
                    $_SESSION['success'] = "Utilisateur créé avec succès (MDP par défaut : password123).";
                } else {
                    $_SESSION['error'] = "Erreur lors de la création.";
                }
            }
        }
        
        header("Location: index.php?action=admin");
        exit;
    }

    public function deleteUser() {
        $id = $_POST['delete_id'] ?? null;
        if ($id) {
            if ($this->userModel->delete($id)) {
                $_SESSION['success'] = "Utilisateur supprimé.";
            } else {
                $_SESSION['error'] = "Erreur lors de la suppression.";
            }
        }
        header("Location: index.php?action=admin");
        exit;
    }
}
?>
