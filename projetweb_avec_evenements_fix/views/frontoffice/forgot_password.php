<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: profile.php');
    exit();
}

require_once(__DIR__ . '/../../controllers/UtilisateurController.php');
require_once(__DIR__ . '/../../controllers/PasswordResetController.php');

$error   = "";
$success = "";

// Turnstile Secret Key (Test key: always passes)
define('TURNSTILE_SECRET_KEY', '1x0000000000000000000000000000000AA');

// ── Handle form submission ────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Honeypot check (simple but effective for bots)
    if (!empty($_POST['website'])) {
        die("Bot detected.");
    }

    // Turnstile validation
    $captchaOk = false;
    $turnstileResponse = $_POST['cf-turnstile-response'] ?? '';
    
    if ($turnstileResponse) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://challenges.cloudflare.com/turnstile/v0/siteverify");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'secret'   => TURNSTILE_SECRET_KEY,
            'response' => $turnstileResponse,
            'remoteip' => $_SERVER['REMOTE_ADDR']
        ]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Fix for localhost without CA bundle
        $res = curl_exec($ch);
        curl_close($ch);
        
        $resData = json_decode($res, true);
        if ($resData['success']) {
            $captchaOk = true;
        }
    }

    if (!$captchaOk) {
        $error = "La verification intelligente a echoue. Veuillez reessayer.";
    } else {
        $email = trim($_POST['email'] ?? '');

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Veuillez entrer une adresse e-mail valide.";
        } else {
            $userController = new UtilisateurController();
            $user = $userController->getUserByEmail($email);

            if ($user) {
                $resetController = new PasswordResetController();
                $sent = $resetController->sendResetCode($email, $user['first_name']);
                if (!$sent) {
                    $error = "Impossible d'envoyer l'e-mail. Verifiez la configuration sendmail dans XAMPP.";
                } else {
                    $_SESSION['reset_email'] = $email;
                    header('Location: verify_code.php');
                    exit();
                }
            } else {
                // Do not reveal whether the email exists — redirect anyway for security
                $_SESSION['reset_email'] = $email;
                header('Location: verify_code.php');
                exit();
            }
        }
    }
}

$turnstileSiteKey = "1x00000000000000000000AA";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Jobyfind - Mot de passe oublie</title>
  <link rel="icon" type="image/png" href="assets/images/jlog.png">
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Serif+Display&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <link rel="stylesheet" href="assets/css/stylelogin.css">
  <style>
    .turnstile-wrap {
      margin-bottom: 20px;
      display: flex;
      justify-content: center;
    }
    .honeypot {
      display: none;
    }
    .error-box {
      background: #fee2e2;
      color: #b91c1c;
      padding: 10px 14px;
      border-radius: 6px;
      margin-bottom: 16px;
      font-size: 13px;
    }
    .controle-saisie {
      color: #ef4444;
      font-size: 12px;
      display: block;
      margin-top: 4px;
    }
  </style>
</head>
<body>

  <nav>
    <a class="nav-logo" href="signin.php">Joby<span>find</span></a>
    <ul class="nav-links">
      <li><a href="#">Accueil</a></li>
      <li><a href="#">Formations</a></li>
      <li><a href="/blog">Blog</a></li>
      <li><a href="#">A propos</a></li>
      <li><a href="#">Contact</a></li>
    </ul>
    <div class="nav-actions">
      <a class="btn-outline" href="signin.php">Connexion</a>
      <a class="btn-solid" href="register.php">S'inscrire</a>
    </div>
  </nav>
  
  <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>

  <main>
    <div class="card">
      <p class="card-eyebrow">Securite</p>
      <h1>Mot de passe oublie ?</h1>
      <p style="margin-bottom:24px; font-size:14px; color:var(--muted);">
        Entrez votre adresse e-mail et nous vous enverrons un code de verification a 6 chiffres.
      </p>

      <?php if ($error): ?>
        <div class="error-box">
          <i class="fa fa-circle-exclamation"></i>
          <?php echo htmlspecialchars($error); ?>
        </div>
      <?php endif; ?>

      <form action="forgot_password.php" method="POST" id="forgot-form">

        <!-- Honeypot -->
        <input type="text" name="website" class="honeypot" tabindex="-1" autocomplete="off">

        <div class="form-group">
          <label for="email">Adresse e-mail</label>
          <div class="input-icon-wrap">
            <i class="fa fa-envelope"></i>
            <input type="text" name="email" id="email" placeholder="vous@exemple.com"
                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
          </div>
          <span id="error-email" class="controle-saisie"></span>
        </div>

        <!-- Cloudflare Turnstile (Intelligent Verification) -->
        <div class="turnstile-wrap">
          <div class="cf-turnstile" data-sitekey="<?php echo $turnstileSiteKey; ?>" data-theme="light"></div>
        </div>

        <button class="btn-submit" type="submit">Envoyer le code</button>
      </form>

      <div class="card-footer-text">
        Vous vous souvenez de votre mot de passe ? <a href="signin.php">Se connecter</a>
      </div>
    </div>
  </main>

  <footer>
    &copy; 2025 Jobyfind. Tous droits reserves. &nbsp;&middot;&nbsp;
    <a href="#" style="color:inherit">Confidentialite</a> &nbsp;&middot;&nbsp;
    <a href="#" style="color:inherit">Conditions d'utilisation</a>
  </footer>

  <script>
    document.getElementById('forgot-form').addEventListener('submit', function(e) {
      var email = document.getElementById('email').value.trim();
      var span  = document.getElementById('error-email');
      var re    = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!re.test(email)) {
        e.preventDefault();
        span.textContent = 'Veuillez entrer une adresse e-mail valide.';
      } else {
        span.textContent = '';
      }
    });
  </script>
</body>
</html>
