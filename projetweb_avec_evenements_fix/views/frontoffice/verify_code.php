<?php
session_start();

if (!isset($_SESSION['reset_email'])) {
    header('Location: forgot_password.php');
    exit();
}

require_once(__DIR__ . '/../../controllers/PasswordResetController.php');

$error = "";
$email = $_SESSION['reset_email'];
$resetController = new PasswordResetController();
$remainingSeconds = $resetController->getRemainingSeconds($email);

// Handle resend request
if (isset($_GET['resend'])) {
    require_once(__DIR__ . '/../../controllers/UtilisateurController.php');
    $userController = new UtilisateurController();
    $user = $userController->getUserByEmail($email);
    if ($user) {
        $resetController = new PasswordResetController();
        $resetController->sendResetCode($email, $user['first_name']);
    }
    header('Location: verify_code.php?sent=1');
    exit();
}

// Handle code submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim(isset($_POST['code']) ? $_POST['code'] : '');

    if ($code === '' || !preg_match('/^[0-9]{6}$/', $code)) {
        $error = "Veuillez entrer le code a 6 chiffres recu par e-mail.";
    } else {
        $resetController = new PasswordResetController();
        $result = $resetController->verifyCode($email, $code);

        if ($result === true) {
            $_SESSION['reset_verified'] = true;
            header('Location: reset_password.php');
            exit();
        } elseif ($result === 'expired') {
            $error = "Ce code a expire.";
        } else {
            $error = "Code incorrect. Verifiez votre e-mail et reessayez.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Jobyfind - Verification du code</title>
  <link rel="icon" type="image/png" href="assets/images/jlog.png">
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Serif+Display&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <link rel="stylesheet" href="assets/css/stylelogin.css">
  <style>
    .code-inputs {
      display: flex;
      gap: 10px;
      justify-content: center;
      margin-bottom: 24px;
    }
    .code-inputs input {
      width: 48px;
      height: 56px;
      text-align: center;
      font-size: 22px;
      font-weight: 700;
      border: 2px solid #e2e8f0;
      border-radius: 10px;
      outline: none;
      transition: border-color .2s;
      color: #0b1f4b;
      padding: 0;
      background: #fff;
      font-family: 'DM Sans', sans-serif;
    }
    .code-inputs input:focus {
      border-color: #2d79ff;
    }
    .code-inputs input.filled {
      border-color: #2d79ff;
      background: #eff6ff;
    }
    .email-hint {
      background: #eff6ff;
      border: 1px solid #bfdbfe;
      border-radius: 8px;
      padding: 12px 16px;
      font-size: 13px;
      color: #1d4ed8;
      margin-bottom: 24px;
    }
    .resend-row {
      text-align: center;
      font-size: 13px;
      color: #9ca3af;
      margin-top: 16px;
    }
    .resend-row a {
      color: #2d79ff;
      font-weight: 600;
      text-decoration: none;
    }
    .resend-row a:hover {
      text-decoration: underline;
    }
    .timer {
      font-weight: 600;
      color: #0b1f4b;
    }
    .error-box {
      background: #fee2e2;
      color: #b91c1c;
      padding: 10px 14px;
      border-radius: 6px;
      margin-bottom: 16px;
      font-size: 13px;
    }
    .success-box {
      background: #ecfdf5;
      color: #059669;
      padding: 10px 14px;
      border-radius: 6px;
      margin-bottom: 16px;
      font-size: 13px;
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
        <li><a href="/blog">Blog</a></li>
    </ul>
    <div class="nav-actions">
      <a class="btn-outline" href="signin.php">Connexion</a>
      <a class="btn-solid" href="register.php">S'inscrire</a>
    </div>
  </nav>

  <main>
    <div class="card">
      <p class="card-eyebrow">Verification</p>
      <h1>Entrez votre code</h1>

      <div class="email-hint">
        <i class="fa fa-envelope-open-text"></i>
        Un code a 6 chiffres a ete envoye a
        <strong><?php echo htmlspecialchars($email); ?></strong>
      </div>

      <?php if (isset($_GET['sent'])): ?>
        <div class="success-box">
          <i class="fa fa-check-circle"></i> Un nouveau code a ete envoye.
        </div>
      <?php endif; ?>

      <?php if ($error): ?>
        <div class="error-box">
          <i class="fa fa-circle-exclamation"></i>
          <?php echo htmlspecialchars($error); ?>
          <?php if (strpos($error, 'expire') !== false): ?>
            &mdash; <a href="forgot_password.php" style="color:#b91c1c; font-weight:600;">Demander un nouveau code</a>
          <?php endif; ?>
        </div>
      <?php endif; ?>

      <form action="verify_code.php" method="POST" id="verify-form">
        <!-- Hidden field that holds the assembled 6-digit code -->
        <input type="hidden" name="code" id="full-code">

        <div class="code-inputs">
          <input type="text" maxlength="1" class="digit-input" autocomplete="off">
          <input type="text" maxlength="1" class="digit-input" autocomplete="off">
          <input type="text" maxlength="1" class="digit-input" autocomplete="off">
          <input type="text" maxlength="1" class="digit-input" autocomplete="off">
          <input type="text" maxlength="1" class="digit-input" autocomplete="off">
          <input type="text" maxlength="1" class="digit-input" autocomplete="off">
        </div>

        <button class="btn-submit" type="submit" id="verify-btn" disabled="disabled">
          Verifier le code
        </button>
      </form>

      <div class="resend-row">
        Vous n'avez pas recu le code ?
        <span id="resend-area">
          Renvoyer dans <span class="timer" id="countdown">60</span>s
        </span>
      </div>

      <div class="card-footer-text" style="margin-top:16px;">
        <a href="forgot_password.php">
          <i class="fa fa-arrow-left" style="font-size:11px;"></i> Retour
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
    var digits    = document.querySelectorAll('.digit-input');
    var fullCode  = document.getElementById('full-code');
    var verifyBtn = document.getElementById('verify-btn');

    function updateFullCode() {
      var code = '';
      for (var i = 0; i < digits.length; i++) {
        code += digits[i].value;
      }
      fullCode.value = code;
      verifyBtn.disabled = (code.length < 6);
    }

    for (var i = 0; i < digits.length; i++) {
      (function(index) {
        digits[index].addEventListener('input', function() {
          // Strip non-digits
          this.value = this.value.replace(/[^0-9]/g, '');
          if (this.value) {
            this.className = 'digit-input filled';
            if (index < digits.length - 1) {
              digits[index + 1].focus();
            }
          } else {
            this.className = 'digit-input';
          }
          updateFullCode();
        });

        digits[index].addEventListener('keydown', function(e) {
          if (e.key === 'Backspace' && this.value === '' && index > 0) {
            digits[index - 1].focus();
            digits[index - 1].value = '';
            digits[index - 1].className = 'digit-input';
            updateFullCode();
          }
        });

        digits[index].addEventListener('paste', function(e) {
          e.preventDefault();
          var pasted = '';
          if (e.clipboardData) {
            pasted = e.clipboardData.getData('text').replace(/[^0-9]/g, '');
          }
          for (var j = 0; j < pasted.length && j < digits.length; j++) {
            digits[j].value = pasted[j];
            digits[j].className = 'digit-input filled';
          }
          updateFullCode();
          var next = Math.min(pasted.length, digits.length - 1);
          digits[next].focus();
        });
      })(i);
    }

    // Auto-focus first box
    digits[0].focus();

    // Countdown timer for resend link
    var seconds     = 60;
    var countdownEl = document.getElementById('countdown');
    var resendArea  = document.getElementById('resend-area');

    var timer = setInterval(function() {
      seconds--;
      if (seconds <= 0) {
        clearInterval(timer);
        resendArea.innerHTML = '<a href="verify_code.php?resend=1">Renvoyer le code</a>';
      } else {
        countdownEl.textContent = seconds;
      }
    }, 1000);

  </script>
</body>
</html>
