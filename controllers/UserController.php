<?php
require_once __DIR__ . '/../models/User.php';

class UserController {

    private $userModel;

    public function __construct() {
        $this->userModel = new User();
        // TEMPORARY: Auto-login pour faciliter le test frontoffice
        if (!isset($_SESSION['user_id'])) {
            $users = $this->userModel->readAll();
            if (!empty($users)) {
                $_SESSION['user_id'] = $users[0]['id'];
            } else {
                $_SESSION['error'] = 'Compte introuvable (Creez un compte d\'abord)';
                header('Location: index.php?action=register');
                exit;
            }
        }
    }

    public function showProfile() {
        $user_id = $_SESSION['user_id'];
        $user = $this->userModel->readOne($user_id);
        
        if (!$user) {
            session_destroy();
            session_start();
            $_SESSION['error'] = 'Compte introuvable';
            header('Location: index.php?action=register');
            exit;
        }

        require_once __DIR__ . '/../views/frontoffice/profile.php';
    }

    public function updateProfile() {
        $user_id = $_SESSION['user_id'];
        
        // Recharger le user courant
        $user = $this->userModel->readOne($user_id);
        if (!$user) exit;

        $first_name = trim($_POST['first_name'] ?? '');
        $last_name = trim($_POST['last_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $bio = trim($_POST['bio'] ?? '');
        $linkedin_url = trim($_POST['linkedin_url'] ?? '');
        $role = $_POST['role'] ?? $user['role'];
        $password = $_POST['password'] ?? '';

        // Validation PHP
        $errors = [];
        if (strlen($first_name) < 2) $errors[] = "Prénom invalide.";
        if (strlen($last_name) < 2) $errors[] = "Nom invalide.";
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Email invalide.";
        
        if (!empty($errors)) {
            $_SESSION['error'] = implode("<br>", $errors);
            header("Location: index.php?action=profile");
            exit;
        }

        // Si l'email a changé, verifier s'il n'est pas déjà pris
        if ($email !== $user['email'] && $this->userModel->emailExists($email)) {
            $_SESSION['error'] = "Cet email est déjà utilisé par un autre compte.";
            header("Location: index.php?action=profile");
            exit;
        }

        $this->userModel->id = $user_id;
        $this->userModel->first_name = $first_name;
        $this->userModel->last_name = $last_name;
        $this->userModel->email = $email;
        $this->userModel->phone = $phone;
        $this->userModel->city = $city;
        $this->userModel->bio = $bio;
        $this->userModel->linkedin_url = $linkedin_url;
        $this->userModel->role = $role;
        $this->userModel->status = $user['status'];
        $this->userModel->date_of_birth = $user['date_of_birth'];
        
        if (!empty($password)) {
            if (strlen($password) < 6) {
                $_SESSION['error'] = "Le mot de passe doit faire au moins 6 caractères.";
                header("Location: index.php?action=profile");
                exit;
            }
            $this->userModel->password = $password;
        }

        if ($this->userModel->update()) {
            $_SESSION['success'] = "Profil mis à jour avec succès.";
        } else {
            $_SESSION['error'] = "Erreur lors de la mise à jour.";
        }

        header("Location: index.php?action=profile");
        exit;
    }
    public function deleteProfile() {
        $user_id = $_SESSION['user_id'];
        
        if ($this->userModel->delete($user_id)) {
            session_destroy();
            session_start();
            $_SESSION['success'] = "Votre compte a été supprimé de manière définitive.";
            header("Location: index.php?action=register");
        } else {
            $_SESSION['error'] = "Une erreur s'est produite lors de la suppression de votre compte.";
            header("Location: index.php?action=profile");
        }
        exit;
    }
}
?>
