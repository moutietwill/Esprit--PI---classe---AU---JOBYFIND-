<?php
session_start();

if (!isset($_SESSION['reset_email']) || !isset($_SESSION['reset_verified'])) {
    header('Location: forgot_password.php');
    exit();
}

require_once(__DIR__ . '/../../Controller/PasswordResetController.php');
require_once(__DIR__ . '/../../Controller/UtilisateurController.php');

$error   = "";
$success = false;
$email   = $_SESSION['reset_email'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password  = isset($_POST['password'])  ? $_POST['password']  : '';
    $password2 = isset($_POST['password2']) ? $_POST['password2'] : '';

    if (strlen($password) < 8) {
        $error = "Le mot de passe doit contenir au moins 8 caracteres.";
    } elseif ($password !== $password2) {
        $error = "Les mots de passe ne correspondent pas.";
    } else {
        $userController  = new UtilisateurController();
        $resetController = new PasswordResetController();

        $user = $userController->getUserByEmail($email);
        if ($user) {
            $userController->updatePassword($user['id'], $password);
            $resetController->invalidateCodes($email);

            unset($_SESSION['reset_email'], $_SESSION['reset_verified']);
            $success = true;
        } else {
            $error = "Utilisateur introuvable.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Jobyfind - Nouveau mot de passe</title>
  <link rel="icon" type="image/png" href="assets/images/jlog.png">
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Serif+Display&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <link rel="stylesheet" href="assets/css/stylelogin.css">
  <style>
    .strength-bar {
      display: flex;
      gap: 6px;
      margin-top: 8px;
    }
    .strength-segment {
      height: 4px;
      flex: 1;
      background: #e2e8f0;
      border-radius: 2px;
      transition: background .3s;
    }
    .strength-label {
      font-size: 11px;
      margin-top: 6px;
      color: #64748b;
    }
    .success-box {
      background: #ecfdf5;
      border: 1px solid #10b981;
      color: #065f46;
      padding: 20px;
      border-radius: 10px;
      text-align: center;
      margin-bottom: 20px;
    }
    .success-box i {
      font-size: 32px;
      color: #10b981;
      display: block;
      margin-bottom: 10px;
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
    .pwd-wrap {
      position: relative;
    }
    .pwd-wrap input {
      padding-left: 36px;
      padding-right: 40px;
      width: 100%;
    }
    .pwd-wrap .icon-left {
      position: absolute;
      left: 13px;
      top: 50%;
      transform: translateY(-50%);
      color: #9ca3af;
    }
    .pwd-wrap .icon-right {
      position: absolute;
      right: 15px;
      top: 50%;
      transform: translateY(-50%);
      cursor: pointer;
      color: #94a3b8;
    }
  </style>
</head>
<body>

  <nav>
    <a class="nav-logo" href="signin.php">Joby<span>find</span></a>
    <ul class="nav-links">
      <li><a href="#">Accueil</a></li>
      <li><a href="#">Formations</a></li>
      <li><a href="#">A propos</a></li>
      <li><a href="#">Contact</a></li>
    </ul>
    <div class="nav-actions">
      <a class="btn-outline" href="signin.php">Connexion</a>
      <a class="btn-solid" href="register.php">S'inscrire</a>
    </div>
  </nav>

  <main>
    <div class="card">
      <p class="card-eyebrow">Securite</p>
      <h1>Nouveau mot de passe</h1>
      <p style="margin-bottom:24px; font-size:14px; color:var(--muted);">
        Choisissez un mot de passe fort pour securiser votre compte.
      </p>

      <?php if ($success): ?>

        <div class="success-box">
          <i class="fa fa-circle-check"></i>
          <strong>Mot de passe mis a jour avec succes !</strong>
          <p style="font-size:13px; margin-top:8px;">
            Vous pouvez maintenant vous connecter avec votre nouveau mot de passe.
          </p>
        </div>
        <a href="signin.php" class="btn-submit"
           style="display:block; text-align:center; text-decoration:none; line-height:1.5;">
          <i class="fa fa-arrow-right-to-bracket"></i> Se connecter
        </a>

      <?php else: ?>

        <?php if ($error): ?>
          <div class="error-box">
            <i class="fa fa-circle-exclamation"></i>
            <?php echo htmlspecialchars($error); ?>
          </div>
        <?php endif; ?>

        <form action="reset_password.php" method="POST" id="reset-form">

          <div class="form-group">
            <label for="pwd-new">
              Nouveau mot de passe
              <small style="color:#94a3b8;">(min. 8 caracteres)</small>
            </label>
            <div class="pwd-wrap">
              <i class="fa fa-lock icon-left"></i>
              <input type="password" name="password" id="pwd-new" placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;"
                     onkeyup="updateStrength(this.value)">
              <i class="fa fa-eye icon-right" id="toggle-new" onclick="togglePwd('pwd-new', 'toggle-new')"></i>
            </div>
            <div class="strength-bar">
              <div class="strength-segment" id="s1"></div>
              <div class="strength-segment" id="s2"></div>
              <div class="strength-segment" id="s3"></div>
              <div class="strength-segment" id="s4"></div>
            </div>
            <p class="strength-label" id="strength-label">Entrez un mot de passe</p>
            <span id="error-pwd" class="controle-saisie"></span>
          </div>

          <div class="form-group">
            <label for="pwd-confirm">Confirmer le mot de passe</label>
            <div class="pwd-wrap">
              <i class="fa fa-lock icon-left"></i>
              <input type="password" name="password2" id="pwd-confirm" placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;">
              <i class="fa fa-eye icon-right" id="toggle-confirm" onclick="togglePwd('pwd-confirm', 'toggle-confirm')"></i>
            </div>
            <span id="error-confirm" class="controle-saisie"></span>
          </div>

          <button class="btn-submit" type="submit">Mettre a jour le mot de passe</button>
        </form>

      <?php endif; ?>

      <div class="card-footer-text" style="margin-top:16px;">
        <a href="signin.php">
          <i class="fa fa-arrow-left" style="font-size:11px;"></i> Retour a la connexion
        </a>
      </div>
    </div>
  </main>

  <footer>
    &copy; 2025 Jobyfind. Tous droits reserves. &nbsp;&middot;&nbsp;
    <a href="#" style="color:inherit">Confidentialite</a> &nbsp;&middot;&nbsp;
    <a href="#" style="color:inherit">Conditions d'utilisation</a>
  </footer>

  <script>
    function togglePwd(inputId, iconId) {
      var input = document.getElementById(inputId);
      var icon  = document.getElementById(iconId);
      if (input.type === 'password') {
        input.type = 'text';
        icon.className = icon.className.replace('fa-eye', 'fa-eye-slash');
      } else {
        input.type = 'password';
        icon.className = icon.className.replace('fa-eye-slash', 'fa-eye');
      }
    }

    function updateStrength(val) {
      var segs   = [
        document.getElementById('s1'),
        document.getElementById('s2'),
        document.getElementById('s3'),
        document.getElementById('s4')
      ];
      var label  = document.getElementById('strength-label');
      var colors = ['#ef4444', '#f97316', '#eab308', '#22c55e'];
      var labels = ['Tres faible', 'Faible', 'Moyen', 'Fort'];

      var score = 0;
      if (val.length >= 8)                    score++;
      if (/[A-Z]/.test(val))                  score++;
      if (/[0-9]/.test(val))                  score++;
      if (/[^A-Za-z0-9]/.test(val))           score++;

      for (var i = 0; i < segs.length; i++) {
        segs[i].style.background = (i < score) ? colors[score - 1] : '#e2e8f0';
      }
      label.textContent = (val.length === 0) ? 'Entrez un mot de passe' : (labels[score - 1] || 'Tres faible');
    }

    document.getElementById('reset-form').addEventListener('submit', function(e) {
      var pwd  = document.getElementById('pwd-new').value;
      var pwd2 = document.getElementById('pwd-confirm').value;
      var ok   = true;

      if (pwd.length < 8) {
        document.getElementById('error-pwd').textContent = 'Minimum 8 caracteres.';
        ok = false;
      } else {
        document.getElementById('error-pwd').textContent = '';
      }

      if (pwd !== pwd2) {
        document.getElementById('error-confirm').textContent = 'Les mots de passe ne correspondent pas.';
        ok = false;
      } else {
        document.getElementById('error-confirm').textContent = '';
      }

      if (!ok) e.preventDefault();
    });
  </script>
</body>
</html>
