<?php
// Récupérer les anciennes valeurs saisies (repopulation du formulaire)
$old = $_SESSION['old_input'] ?? [];
unset($_SESSION['old_input']);
$old_first_name = htmlspecialchars($old['first_name'] ?? '');
$old_last_name  = htmlspecialchars($old['last_name']  ?? '');
$old_username   = htmlspecialchars($old['username']   ?? '');
$old_email      = htmlspecialchars($old['email']      ?? '');
$old_phone      = htmlspecialchars($old['phone']      ?? '');
$old_city       = htmlspecialchars($old['city']       ?? '');
$old_date_of_birth = htmlspecialchars($old['date_of_birth'] ?? '');
$old_role       = $old['role'] ?? 'Entrepreneur';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Jobyfind — Inscription</title>
  <link rel="icon" type="image/png" href="views/frontoffice/assets/images/jlog.png">
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Serif+Display&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <link rel="stylesheet" href="views/frontoffice/assets/css/styleregister.css">
  <style>
    .error-box { background:#fef2f2; border:1px solid #fca5a5; color:#dc2626; padding:12px 16px; border-radius:8px; margin-bottom:16px; font-size:14px; line-height:1.7; }
    .error-box i { margin-right:6px; }
  </style>
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
      <p class="card-eyebrow">Créer un compte</p>
      <h1>Rejoignez Jobyfind</h1>

      <?php if(isset($_SESSION['error'])): ?>
      <div class="error-box">
          <i class="fa fa-circle-exclamation"></i>
          <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
      </div>
      <?php endif; ?>

      <form action="index.php?action=register" method="POST" id="register-form">

          <!-- Role Tabs -->
          <div class="role-tabs">
            <button type="button" class="role-tab <?php echo ($old_role === 'Entrepreneur' || $old_role === '') ? 'active' : ''; ?>" onclick="setRoleTab(this, 'Entrepreneur')">
              <i class="fa fa-lightbulb"></i> Entrepreneur
            </button>
            <button type="button" class="role-tab <?php echo $old_role === 'Mentor' ? 'active' : ''; ?>" onclick="setRoleTab(this, 'Mentor')">
              <i class="fa fa-chalkboard-teacher"></i> Mentor
            </button>
            <button type="button" class="role-tab <?php echo $old_role === 'Entreprise' ? 'active' : ''; ?>" onclick="setRoleTab(this, 'Entreprise')">
              <i class="fa fa-building"></i> Entreprise
            </button>
          </div>

          <input type="hidden" name="role" id="role-input" value="<?php echo $old_role ?: 'Entrepreneur'; ?>">

          <div class="form-row">
            <div class="form-group">
              <label>Prénom *</label>
              <input type="text" name="first_name" placeholder="Mohamed" value="<?php echo $old_first_name; ?>">
            </div>
            <div class="form-group">
              <label>Nom *</label>
              <input type="text" name="last_name" placeholder="Ben Ali" value="<?php echo $old_last_name; ?>">
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Nom d'utilisateur *</label>
              <div class="input-icon-wrap">
                <i class="fa fa-at"></i>
                <input type="text" name="username" placeholder="mohamedbenali" value="<?php echo $old_username; ?>">
              </div>
            </div>

            <div class="form-group">
              <label>Date de naissance</label>
              <div class="input-icon-wrap">
                <i class="fa fa-calendar"></i>
                <input type="date" name="date_of_birth" value="<?php echo $old_date_of_birth; ?>">
              </div>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Téléphone</label>
              <div class="input-icon-wrap">
                <i class="fa fa-phone"></i>
                <input type="text" name="phone" placeholder="+216 12 345 678" value="<?php echo $old_phone; ?>">
              </div>
            </div>
            <div class="form-group">
              <label>Ville</label>
              <div class="input-icon-wrap">
                <i class="fa fa-city"></i>
                <input type="text" name="city" placeholder="Tunis" value="<?php echo $old_city; ?>">
              </div>
            </div>
          </div>

          <div class="form-group">
            <label>Adresse e-mail *</label>
            <div class="input-icon-wrap">
              <i class="fa fa-envelope"></i>
              <input type="text" name="email" placeholder="vous@exemple.com" value="<?php echo $old_email; ?>">
            </div>
          </div>

          <div class="form-group">
            <label>Mot de passe * <small style="color:#94a3b8">(min. 8 caractères)</small></label>
            <div class="input-icon-wrap" style="position: relative;">
              <input type="password" name="password" id="pwd-input" placeholder="••••••••" oninput="updateStrength(this.value)" style="padding-right: 40px;">
              <i class="fa fa-eye toggle-password" style="position: absolute; right: 15px; left: auto; top: 50%; transform: translateY(-50%); cursor: pointer; color: #94a3b8;" onclick="togglePasswordVisibility('pwd-input', this)"></i>
            </div>
            <div class="strength-bar">
              <div class="strength-segment" id="s1"></div>
              <div class="strength-segment" id="s2"></div>
              <div class="strength-segment" id="s3"></div>
              <div class="strength-segment" id="s4"></div>
            </div>
            <p class="strength-label" id="strength-label">Entrez un mot de passe</p>
          </div>

          <label class="terms">
            <input type="checkbox" name="terms">
            J'accepte les <a href="#">Conditions d'utilisation</a> et la <a href="#">Politique de confidentialité</a> de Jobyfind.
          </label>

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
        Déjà un compte ? <a href="index.php?action=login">Se connecter</a>
      </p>
    </div>
  </main>

  <!-- FOOTER -->
  <footer>
    &copy; 2025 Jobyfind. Tous droits réservés. &nbsp;·&nbsp;
    <a href="#" style="color:inherit">Confidentialité</a> &nbsp;·&nbsp;
    <a href="#" style="color:inherit">Conditions d'utilisation</a>
  </footer>
  <script src="views/frontoffice/assets/js/register.js"></script>
  <script>
      function setRoleTab(btn, roleValue) {
          document.querySelectorAll('.role-tab').forEach(b => b.classList.remove('active'));
          btn.classList.add('active');
          document.getElementById('role-input').value = roleValue;
      }

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
