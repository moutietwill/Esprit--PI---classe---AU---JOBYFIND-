<?php
require_once __DIR__ . '/../models/User.php';

class AuthController {
    
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    // ─── Pages ───────────────────────────────────────────────────────────────

    public function loginPage() {
        require_once __DIR__ . '/../views/frontoffice/login.php';
    }

    public function registerPage() {
        require_once __DIR__ . '/../views/frontoffice/register.php';
    }

    // ─── Connexion ────────────────────────────────────────────────────────────

    public function loginSubmit() {
        $email    = trim($_POST['email']    ?? '');
        $password = $_POST['password'] ?? '';

        // Contrôle de saisie PHP (pas de HTML5)
        if (empty($email) || empty($password)) {
            $_SESSION['error'] = "Veuillez remplir tous les champs.";
            header("Location: index.php?action=login");
            exit;
        }

        $user = $this->userModel->login($email, $password);

        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role']    = $user['role'];
            $_SESSION['status']  = $user['status'];

            if ($user['status'] !== 'Actif' && $user['role'] !== 'Admin') {
                $_SESSION['error'] = "Votre compte n'est pas encore actif. Attendez la validation de l'administrateur.";
                session_destroy();
                header("Location: index.php?action=login");
                exit;
            }

            if ($user['role'] === 'Admin') {
                header("Location: index.php?action=admin");
            } else {
                header("Location: index.php?action=profile");
            }
        } else {
            $_SESSION['error'] = "Email ou mot de passe incorrect.";
            header("Location: index.php?action=login");
        }
        exit;
    }

    // ─── Inscription ──────────────────────────────────────────────────────────

    public function registerSubmit() {
        $first_name = trim($_POST['first_name'] ?? '');
        $last_name  = trim($_POST['last_name']  ?? '');
        $username   = trim($_POST['username']   ?? '');
        $email      = trim($_POST['email']      ?? '');
        $phone      = trim($_POST['phone']      ?? '');
        $city       = trim($_POST['city']       ?? '');
        $date_of_birth = trim($_POST['date_of_birth'] ?? '');
        $password   = $_POST['password']        ?? '';
        $role       = $_POST['role']            ?? 'Entrepreneur';
        $terms      = isset($_POST['terms']);

        // Sauvegarder la saisie pour repopuler le formulaire en cas d'erreur
        $_SESSION['old_input'] = compact('first_name', 'last_name', 'username', 'email', 'phone', 'city', 'date_of_birth', 'role');

        // === CONTRÔLE DE SAISIE CÔTÉ SERVEUR (HTML5 interdit par le prof) ===
        $errors = [];

        // Prénom
        if (empty($first_name))
            $errors[] = "Le prénom est obligatoire.";
        elseif (strlen($first_name) < 2)
            $errors[] = "Le prénom doit avoir au moins 2 caractères.";
        elseif (!preg_match('/^[a-zA-ZÀ-ÿ\s\-]+$/', $first_name))
            $errors[] = "Le prénom ne doit contenir que des lettres.";

        // Nom
        if (empty($last_name))
            $errors[] = "Le nom est obligatoire.";
        elseif (strlen($last_name) < 2)
            $errors[] = "Le nom doit avoir au moins 2 caractères.";
        elseif (!preg_match('/^[a-zA-ZÀ-ÿ\s\-]+$/', $last_name))
            $errors[] = "Le nom ne doit contenir que des lettres.";

        // Nom d'utilisateur
        if (empty($username))
            $errors[] = "Le nom d'utilisateur est obligatoire.";
        elseif (strlen($username) < 4)
            $errors[] = "Le nom d'utilisateur doit avoir au moins 4 caractères.";
        elseif (!preg_match('/^[a-zA-Z0-9_\.]+$/', $username))
            $errors[] = "Le nom d'utilisateur ne peut contenir que des lettres, chiffres, _ ou .";

        // Email
        if (empty($email))
            $errors[] = "L'adresse e-mail est obligatoire.";
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL))
            $errors[] = "L'adresse e-mail n'est pas valide.";

        // Mot de passe
        if (empty($password))
            $errors[] = "Le mot de passe est obligatoire.";
        elseif (strlen($password) < 8)
            $errors[] = "Le mot de passe doit faire au moins 8 caractères.";

        // Conditions d'utilisation
        if (!$terms)
            $errors[] = "Vous devez accepter les conditions d'utilisation.";

        // Téléphone
        if (!empty($phone)) {
            if (!preg_match('/^[259]/', $phone)) {
                $errors[] = "Le numéro de téléphone doit commencer par 2, 5 ou 9.";
            } elseif (!preg_match('/^[0-9\+\-\s]{8,15}$/', $phone)) {
                $errors[] = "Le format du téléphone n'est pas valide.";
            }
        }

        // Date de naissance
        if (!empty($date_of_birth)) {
            $d = DateTime::createFromFormat('Y-m-d', $date_of_birth);
            if (!$d || $d->format('Y-m-d') !== $date_of_birth) {
                $errors[] = "La date de naissance n'est pas valide.";
            } elseif ($d >= new DateTime('today')) {
                $errors[] = "La date de naissance doit être strictement inférieure à la date d'aujourd'hui.";
            }
        }

        // Unicité (seulement si pas d'autres erreurs de format)
        if (empty($errors)) {
            if ($this->userModel->emailExists($email))
                $errors[] = "Cette adresse e-mail est déjà utilisée par un autre compte.";
            if ($this->userModel->usernameExists($username))
                $errors[] = "Ce nom d'utilisateur est déjà pris, choisissez-en un autre.";
        }

        // Si erreurs → retour au formulaire avec les messages
        if (!empty($errors)) {
            $_SESSION['error'] = implode("<br>", $errors);
            header("Location: index.php?action=register");
            exit;
        }

        // === INSERTION EN BASE DE DONNÉES =====================================
        $this->userModel->first_name = $first_name;
        $this->userModel->last_name  = $last_name;
        $this->userModel->username   = $username;
        $this->userModel->email      = $email;
        $this->userModel->phone      = $phone;
        $this->userModel->city       = $city;
        $this->userModel->date_of_birth = $date_of_birth ?: null;
        $this->userModel->password   = $password;
        $this->userModel->role       = in_array($role, ['Entrepreneur', 'Mentor', 'Entreprise']) ? $role : 'Entrepreneur';
        $this->userModel->status     = 'En attente'; // Validation obligatoire par l'admin

        if ($this->userModel->create()) {
            unset($_SESSION['old_input']);
            $_SESSION['success'] = "Compte créé avec succès ! En attente de validation par un administrateur.";
            header("Location: index.php?action=login");
        } else {
            $_SESSION['error'] = "Une erreur s'est produite lors de la création. Veuillez réessayer.";
            header("Location: index.php?action=register");
        }
        exit;
    }

    // ─── Déconnexion ─────────────────────────────────────────────────────────

    public function logout() {
        session_destroy();
        header("Location: index.php?action=login");
        exit;
    }
}
?>
