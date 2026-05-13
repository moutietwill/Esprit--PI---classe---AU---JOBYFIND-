<?php
require_once(__DIR__ . '/../../config/session.php');
startAppSession();
require_once(__DIR__ . '/../../Controller/UtilisateurController.php');
require_once(__DIR__ . '/../../Controller/ProfileController.php');
require_once(__DIR__ . '/../../Model/Utilisateur.php');
require_once(__DIR__ . '/../../Model/Profile.php');

$error = "";

// Turnstile Secret Key (Real key)
define('TURNSTILE_SECRET_KEY', '1x0000000000000000000000000000000AA');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {

    // Honeypot check
    if (!empty($_POST['website'])) {
        die("Bot detected.");
    }

    // Turnstile validation
    $captchaOk = false;
    $turnstileResponse = $_POST['cf-turnstile-response'] ?? '';
    
    $isLocalhost = in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true);

    if ($isLocalhost) {
        $captchaOk = true;
    } elseif ($turnstileResponse) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://challenges.cloudflare.com/turnstile/v0/siteverify");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'secret'   => TURNSTILE_SECRET_KEY,
            'response' => $turnstileResponse,
            'remoteip' => $_SERVER['REMOTE_ADDR']
        ]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Localhost fix
        $res = curl_exec($ch);
        curl_close($ch);
        
        $resData = json_decode($res, true);
        if ($resData && $resData['success']) {
            $captchaOk = true;
        }
    }

    if (!$captchaOk) {
        $error = "La vérification intelligente a échoué. Veuillez réessayer.";
    } elseif (
        !empty($_POST["first_name"]) &&
        !empty($_POST["last_name"]) &&
        !empty($_POST["email"]) &&
        !empty($_POST["password"])
    ) {
        $userController = new UtilisateurController();
        $user = new Utilisateur([
            'first_name' => $_POST['first_name'],
            'last_name' => $_POST['last_name'],
            'username' => $_POST['username'] ?? null,
            'date_of_birth' => $_POST['date_of_birth'] ?? null,
            'phone' => $_POST['phone'] ?? null,
            'city' => $_POST['city'] ?? null,
            'email' => $_POST['email'],
            'password' => $_POST['password'],
            'role' => $_POST['role'] ?? 'Entrepreneur',
            'status' => 'Actif'
        ]);
        $userId = $userController->addUser($user);
        
        if ($userId === 'duplicate_email') {
            $error = "Cette adresse e-mail est déjà utilisée. <a href='signin.php' style='color:#b91c1c;font-weight:700'>Se connecter ?</a>";
        } elseif ($userId) {
            $profileController = new ProfileController();
            if (!$profileController->getProfileByUserId($userId)) {
                $profileController->addProfile(new Profile([
                    'Id_utilisateur' => $userId,
                    'bio' => 'Aucune biographie.',
                    'ville' => $_POST['city'] ?? 'Non specifiee',
                    'pays' => 'Tunisie'
                ]));
            }

            $dbUser = $userController->getUserById($userId);
            if ($dbUser) {
                $_SESSION['user_id'] = $dbUser['id'];
                $_SESSION['role']    = $dbUser['role'];
                session_write_close();

                // All users go to profile page first after registration
                header('Location: profile.php');
                exit();
            }
        } else {
            $error = "Une erreur est survenue lors de la création du compte.";
        }
    } else {
        $error = "Veuillez remplir tous les champs obligatoires.";
    }
}

$turnstileSiteKey = "1x00000000000000000000AA";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Jobyfind — Inscription</title>
  <link rel="icon" type="image/png" href="assets/images/jlog.png">
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Serif+Display&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <link rel="stylesheet" href="assets/css/styleregister.css">
  <style>
    .error-box { background:#fee2e2; color:#b91c1c; padding:12px; border-radius:8px; margin-bottom:16px; font-size:14px; display:flex; align-items:center; }
    .error-box i { margin-right:6px; }
    .strength-bar { display:flex; gap:6px; margin-top:8px; }
    .strength-segment { height:4px; flex:1; background:#e2e8f0; border-radius:2px; }
    .strength-label { font-size:11px; margin-top:6px; color:#64748b; }
    .turnstile-wrap { margin: 20px 0; display: flex; justify-content: center; }
    .honeypot { display: none; }
  </style>
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

  <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>

  <main>
    <div class="card">
      <p class="card-eyebrow">Créer un compte</p>
      <h1>Rejoignez Jobyfind</h1>

      <?php if($error): ?>
        <div class="error-box">
          <i class="fa fa-circle-exclamation"></i> <?php echo $error; ?>
        </div>
      <?php endif; ?>

      <form action="register.php" method="POST" id="register-form">
          
          <!-- Honeypot -->
          <input type="text" name="website" class="honeypot" tabindex="-1" autocomplete="off">

          <!-- Role Tabs -->
          <div class="role-tabs">
            <button type="button" class="role-tab active" onclick="setRoleTab(this, 'Entrepreneur')">
              <i class="fa fa-lightbulb"></i> Entrepreneur
            </button>
            <button type="button" class="role-tab" onclick="setRoleTab(this, 'Mentor')">
              <i class="fa fa-chalkboard-teacher"></i> Mentor
            </button>
            <button type="button" class="role-tab" onclick="setRoleTab(this, 'Entreprise')">
              <i class="fa fa-building"></i> Entreprise
            </button>
          </div>

          <input type="hidden" name="role" id="role-input" value="Entrepreneur">

          <div class="form-row">
            <div class="form-group">
              <label>Prénom *</label>
              <input type="text" name="first_name" id="first_name" placeholder="Mohamed">
              <span id="error-first_name" class="controle-saisie"></span>
            </div>
            <div class="form-group">
              <label>Nom *</label>
              <input type="text" name="last_name" id="last_name" placeholder="Ben Ali">
              <span id="error-last_name" class="controle-saisie"></span>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Nom d'utilisateur</label>
              <div class="input-icon-wrap">
                <i class="fa fa-at"></i>
                <input type="text" name="username" id="username" placeholder="mohamedbenali">
              </div>
              <span id="error-username" class="controle-saisie"></span>
            </div>

            <div class="form-group">
              <label>Date de naissance</label>
              <div class="input-icon-wrap">
                <i class="fa fa-calendar"></i>
                <input type="date" name="date_of_birth" id="date_of_birth">
              </div>
              <span id="error-date_of_birth" class="controle-saisie"></span>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Téléphone</label>
              <div class="input-icon-wrap">
                <i class="fa fa-phone"></i>
                <input type="text" name="phone" id="phone" placeholder="+216 12 345 678">
              </div>
              <span id="error-phone" class="controle-saisie"></span>
            </div>
            <div class="form-group">
              <label>Ville</label>
              <div class="input-icon-wrap">
                <i class="fa fa-city"></i>
                <input type="text" name="city" id="city" placeholder="Tunis">
              </div>
              <span id="error-city" class="controle-saisie"></span>
            </div>
          </div>

          <div class="form-group">
            <label>Adresse e-mail *</label>
            <div class="input-icon-wrap">
              <i class="fa fa-envelope"></i>
              <input type="text" name="email" id="email" placeholder="vous@exemple.com">
            </div>
            <span id="error-email" class="controle-saisie"></span>
          </div>

          <div class="form-group">
            <label>Mot de passe * <small style="color:#94a3b8">(min. 8 caractères)</small></label>
            <div class="input-icon-wrap" style="position: relative;">
              <input type="password" name="password" id="pwd-input" placeholder="••••••••" oninput="updateStrength(this.value)" style="padding-right: 40px;">
              <i class="fa fa-eye toggle-password" style="position: absolute; right: 15px; left: auto; top: 50%; transform: translateY(-50%); cursor: pointer; color: #94a3b8;" onclick="togglePasswordVisibility('pwd-input', this)"></i>
            </div>
            <span id="error-password" class="controle-saisie"></span>
            <div class="strength-bar">
              <div class="strength-segment" id="s1"></div>
              <div class="strength-segment" id="s2"></div>
              <div class="strength-segment" id="s3"></div>
              <div class="strength-segment" id="s4"></div>
            </div>
            <p class="strength-label" id="strength-label">Entrez un mot de passe</p>
          </div>

          <label class="terms">
            <input type="checkbox" name="terms" id="terms">
            J'accepte les <a href="#">Conditions d'utilisation</a> et la <a href="#">Politique de confidentialité</a> de Jobyfind.
            <span id="error-terms" class="controle-saisie" style="display:block; width:100%"></span>
          </label>

          <!-- Cloudflare Turnstile -->
          <div class="turnstile-wrap">
            <div class="cf-turnstile" data-sitekey="<?php echo $turnstileSiteKey; ?>" data-theme="light"></div>
          </div>

          <button class="btn-submit" type="submit">Créer mon compte</button>
      </form>

      <div class="divider"><span>ou s'inscrire avec</span></div>

      <div class="social-row">
        <a class="btn-social" href="#">
          <i class="fab fa-google" style="color:#ea4335"></i> Google
        </a>
        <a class="btn-social" href="#">
          <i class="fab fa-linkedin" style="color:#0077b5"></i> LinkedIn
        </a>
      </div>

      <p class="card-footer-text">
        Déjà un compte ? <a href="signin.php">Se connecter</a>
      </p>
    </div>
  </main>

  <footer>
    &copy; 2025 Jobyfind. Tous droits réservés. &nbsp;·&nbsp;
    <a href="#" style="color:inherit">Confidentialité</a> &nbsp;·&nbsp;
    <a href="#" style="color:inherit">Conditions d'utilisation</a>
  </footer>

  <script src="assets/js/register.js"></script>
  <script>
    function checkAccess(event) {
        const isLoggedIn = <?php echo json_encode(isset($_SESSION['user_id'])); ?>;
        if (!isLoggedIn) {
            event.preventDefault();
            return false;
        }
        return true;
    }
  </script>
</body>
</html>
