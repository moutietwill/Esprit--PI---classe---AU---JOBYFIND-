<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Jobyfind — Connexion</title>
  <link rel="icon" type="image/png" href="views/frontoffice/assets/images/jlog.png">
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Serif+Display&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <link rel="stylesheet" href="views/frontoffice/assets/css/stylelogin.css">
</head>
<body>

  <!-- NAV -->
  <nav>
    <a class="nav-logo" href="index.php">Joby<span>find</span></a>
    <ul class="nav-links">
      <li><a href="#">Accueil</a></li>
      <li><a href="#">Formations</a></li>
      <li><a href="#">À propos</a></li>
      <li><a href="#">Contact</a></li>
    </ul>
    <div class="nav-actions">
      <a class="btn-solid" style="background:#10b981; border:none;" href="index.php?action=profile"><i class="fa fa-user"></i> Voir Profil</a>
      <a class="btn-outline" href="index.php?action=login">Connexion</a>
      <a class="btn-solid" href="index.php?action=register">S'inscrire</a>
      <a class="btn-outline" href="index.php?action=admin">backoffice</a>
    </div>
  </nav>

  <!-- MAIN -->
  <main>
    <div class="card">
      <p class="card-eyebrow">Bienvenue</p>
      <h1>Connectez-vous à votre compte</h1>

      <?php if(isset($_SESSION['success'])): ?>
      <div style="padding:10px; background-color:#10b981; color:white; border-radius:5px; margin-bottom:15px;">
          <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
      </div>
      <?php endif; ?>

      <?php if(isset($_SESSION['error'])): ?>
      <div style="padding:10px; background-color:#ef4444; color:white; border-radius:5px; margin-bottom:15px;">
          <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
      </div>
      <?php endif; ?>

      <form action="index.php?action=login" method="POST">
          <!-- Form -->
          <div class="form-group">
            <label>Adresse e-mail</label>
            <div class="input-icon-wrap">
              <i class="fa fa-envelope"></i>
              <!-- We omit type="email" and required here specifically to force the PHP validation per teacher requirements -->
              <input type="text" name="email" placeholder="vous@exemple.com">
            </div>
          </div>

          <div class="form-group">
            <label>Mot de passe</label>
            <div class="input-icon-wrap" style="position: relative;">
              <input type="password" name="password" id="login-password" placeholder="••••••••" style="padding-right: 40px;">
              <i class="fa fa-eye toggle-password" style="position: absolute; right: 15px; left: auto; top: 50%; transform: translateY(-50%); cursor: pointer; color: #94a3b8;" onclick="togglePasswordVisibility('login-password', this)"></i>
            </div>
          </div>

          <div class="row-between">
            <label class="remember">
              <input type="checkbox"> Se souvenir de moi
            </label>
            <a class="forgot" href="#">Mot de passe oublié ?</a>
          </div>

          <button type="submit" class="btn-submit">Se connecter</button>
      </form>

      <div class="divider"><span>ou continuer avec</span></div>

      <div class="social-row">
        <a class="btn-social" href="#">
          <i class="fab fa-google" style="color:#ea4335"></i> Google
        </a>
        <a class="btn-social" href="#">
          <i class="fab fa-linkedin" style="color:#0077b5"></i> LinkedIn
        </a>
      </div>

      <p class="card-footer-text">
        Pas encore de compte ? <a href="index.php?action=register">S'inscrire maintenant</a>
      </p>
    </div>
  </main>

  <!-- FOOTER -->
  <footer>
    &copy; 2025 Jobyfind. Tous droits réservés. &nbsp;·&nbsp;
    <a href="#" style="color:inherit">Confidentialité</a> &nbsp;·&nbsp;
    <a href="#" style="color:inherit">Conditions d'utilisation</a>
  </footer>
  <script src="views/frontoffice/assets/js/login.js"></script>
  <script>
    function togglePasswordVisibility(inputId, icon) {
      const input = document.getElementById(inputId);
      if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
      } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
      }
    }
  </script>
</body>
</html>
