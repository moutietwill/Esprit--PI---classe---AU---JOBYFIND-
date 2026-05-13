<?php
require_once(__DIR__ . '/../../config/session.php');
startAppSession();

if (isset($_SESSION['user_id'])) {
    // Check if it's our special admin account
    require_once(__DIR__ . '/../../Controller/UtilisateurController.php');
    $userController = new UtilisateurController();
    $user = $userController->getUserById($_SESSION['user_id']);
    
    if ($user && $user['email'] === 'admin@gmail.com') {
        header('Location: ../backoffice/admine.php');
    } else {
        header('Location: profile.php');
    }
    exit();
}
require_once(__DIR__ . '/../../Controller/UtilisateurController.php');

$error = "";
if (isset($_POST['email']) && isset($_POST['password'])) {
    $userController = new UtilisateurController();
    $user = $userController->login($_POST['email'], $_POST['password']);

    // Special bypass for admin@gmail.com / 0000 (as requested)
    if (!$user && $_POST['email'] === 'admin@gmail.com' && $_POST['password'] === '0000') {
        $user = $userController->getUserByEmail('admin@gmail.com');
        if (!$user) {
            // Force user structure if not in DB for testing - Role is Mentor
            $user = ['id' => 1, 'email' => 'admin@gmail.com', 'role' => 'Mentor', 'status' => 'Actif'];
        }
    }

    if ($user === 'suspended') {
        $error = "Votre compte a été suspendu. Contactez l'administrateur.";
    } elseif ($user && is_array($user)) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        
        // Final check on redirection
        if ($user['email'] === 'admin@gmail.com') {
            header('Location: ../backoffice/admine.php');
        } else {
            header('Location: profile.php');
        }
        exit();
    } else {
        $error = "Email ou mot de passe incorrect.";
    }
}

if (isset($_GET['error']) && $_GET['error'] === 'Securite') {
    $error = "Déconnexion de sécurité : Contenu inapproprié détecté par l'IA.";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Jobyfind — Connexion</title>
  <link rel="icon" type="image/png" href="assets/images/jlog.png">
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Serif+Display&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <link rel="stylesheet" href="assets/css/stylelogin.css">
</head>
<body>


  <nav>
    <a class="nav-logo" href="../../public/index.php">Joby<span>find</span></a>
    <ul class="nav-links">
      <li><a href="../../public/index.php">Accueil</a></li>
      <li><a href="../../public/index.php/blog" onclick="return checkAccess(event)">Blog</a></li>
      <li><a href="../../public/index.php/events" onclick="return checkAccess(event)">Événements</a></li>
      <?php
        $quiz_link = (isset($_SESSION['role']) && in_array($_SESSION['role'], ['Admin', 'Tutor', 'Mentor']))
            ? '/yassine/web/view/backoffice/quiz-list-admin.php'
            : '/yassine/web/view/frontoffice/quizzes-list.php';
      ?>
      <li><a href="<?= $quiz_link ?>" onclick="return checkAccess(event)">Quiz</a></li>
      <li><a href="../frontoffice.php" onclick="return checkAccess(event)">Formations</a></li>
      <li><a href="#">À propos</a></li>
      <li><a href="#">Contact</a></li>
    </ul>
    <div class="nav-actions">
    </div>
  </nav>


  <main>
    <div class="card">
      <p class="card-eyebrow">Connexion</p>
      <h1>Bon retour !</h1>
      <p style="margin-bottom: 24px; font-size: 14px; color: var(--muted);">Connectez-vous pour accéder à votre espace.</p>

      <?php if($error): ?>
        <div style="background:#fee2e2; color:#b91c1c; padding:10px; border-radius:6px; margin-bottom:15px; font-size:13px;">
          <?php echo htmlspecialchars($error); ?>
        </div>
      <?php endif; ?>

      <form action="signin.php" method="POST">
        <div class="form-group">
          <label>Adresse e-mail</label>
          <div class="input-icon-wrap">
            <i class="fa fa-envelope"></i>
            <input type="text" name="email" id="email" placeholder="vous@exemple.com">
          </div>
          <span id="error-email" class="controle-saisie"></span>
        </div>

        <div class="form-group">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
            <label style="margin-bottom: 0;">Mot de passe</label>
            <a href="forgot_password.php" class="forgot" style="font-size:12px;">Oublié ?</a>
          </div>
          <div class="input-icon-wrap" style="position: relative;">
            <i class="fa fa-lock" style="left:13px; position:absolute; top:50%; transform:translateY(-50%); color:var(--muted);"></i>
            <input type="password" name="password" id="login-password" placeholder="••••••••" style="padding-left:36px; padding-right: 40px;">
            <i class="fa fa-eye toggle-password" style="position: absolute; right: 15px; left: auto; top: 50%; transform: translateY(-50%); cursor: pointer; color: #94a3b8;" onclick="togglePasswordVisibility('login-password', this)"></i>
          </div>
          <span id="error-password" class="controle-saisie"></span>
        </div>

        <div class="row-between">
          <label class="remember">
            <input type="checkbox"> Rester connecté
          </label>
        </div>

        <button class="btn-submit" type="submit">Se connecter</button>
      </form>

      <div class="divider"><span>ou se connecter avec</span></div>

      <div class="social-row">
        <a class="btn-social" href="#">
          <i class="fab fa-google" style="color:#ea4335"></i> Google
        </a>
        <a class="btn-social" href="#">
          <i class="fab fa-linkedin" style="color:#0077b5"></i> LinkedIn
        </a>
      </div>

      <div class="card-footer-text">
        Nouveau sur Jobyfind ? <a href="register.php">Créer un compte</a>
      </div>
    </div>
  </main>


  <footer>
    &copy; 2025 Jobyfind. Tous droits réservés. &nbsp;·&nbsp;
    <a href="#" style="color:inherit">Confidentialité</a> &nbsp;·&nbsp;
    <a href="#" style="color:inherit">Conditions d'utilisation</a>
  </footer>
  
  <script src="assets/js/login.js"></script>
  <script>
    function checkAccess(event) {
        // If we are already on signin or register page, clicking these links should do nothing if not logged in
        // or redirect to self to be consistent.
        const isLoggedIn = <?php echo json_encode(isset($_SESSION['user_id'])); ?>;
        if (!isLoggedIn) {
            event.preventDefault();
            // Since we are already at the "gateway", we can just notify or stay here.
            // The user wants to "concentrate" on user space.
            return false;
        }
        return true;
    }
  </script>
</body>
</html>
