<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Jobyfind — Mon Profil</title>
  <link rel="icon" type="image/png" href="views/frontoffice/assets/images/jlog.png">
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Serif+Display&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <link rel="stylesheet" href="views/frontoffice/assets/css/styleprofile.css">
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
      <a class="btn-outline" href="index.php?action=profile" style="background:var(--blue);color:#fff">Mon profil</a>
      <a class="btn-solid" href="index.php?action=logout">Déconnexion</a>
    </div>
  </nav>

  <!-- MAIN -->
  <main>
    <?php if(isset($_SESSION['success'])): ?>
    <div style="grid-column: 1 / -1; padding:15px; background-color:#10b981; color:white; border-radius:8px; margin-bottom:5px; text-align:center;">
        <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
    </div>
    <?php endif; ?>

    <?php if(isset($_SESSION['error'])): ?>
    <div style="grid-column: 1 / -1; padding:15px; background-color:#ef4444; color:white; border-radius:8px; margin-bottom:5px; text-align:center;">
        <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
    </div>
    <?php endif; ?>

    <div style="display:flex; flex-direction:column; gap:20px;">
    <!-- LEFT: Profile Card -->
    <div class="profile-card">
      <div class="profile-banner"></div>

      <div class="avatar-wrap">
        <div class="avatar-img" id="avatar-display">
          <img id="avatar-preview" src="" alt="Photo de profil" style="display:none;">
          <span id="avatar-initials"><?php echo strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)); ?></span>
        </div>
        <label class="avatar-upload-btn" for="avatar-input" title="Changer la photo">
          <i class="fa fa-camera"></i>
        </label>
        <input type="file" id="avatar-input" accept="image/*">
      </div>

      <div class="profile-info">
        <p class="profile-name" id="display-name"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></p>
        <span class="profile-role-badge"><i class="fa fa-lightbulb"></i> <span id="display-role"><?php echo htmlspecialchars($user['role']); ?></span></span>
        <p class="profile-bio" id="display-bio"><?php echo htmlspecialchars($user['bio'] ?? 'Aucune biographie.'); ?></p>

        <div class="profile-stats">
          <div class="stat-item">
            <span class="stat-num">3</span>
            <span class="stat-label">Projets</span>
          </div>
          <div class="stat-item">
            <span class="stat-num">12</span>
            <span class="stat-label">Contacts</span>
          </div>
          <div class="stat-item">
            <span class="stat-num">5</span>
            <span class="stat-label">Formations</span>
          </div>
        </div>

        <div class="profile-links">
          <div class="profile-link-item"><i class="fa fa-envelope"></i> <span id="display-email"><?php echo htmlspecialchars($user['email']); ?></span></div>
          <?php if(!empty($user['phone'])): ?>
          <div class="profile-link-item"><i class="fa fa-phone"></i> <span id="display-phone"><?php echo htmlspecialchars($user['phone']); ?></span></div>
          <?php endif; ?>
          <?php if(!empty($user['city'])): ?>
          <div class="profile-link-item"><i class="fa fa-map-marker-alt"></i> <span id="display-city"><?php echo htmlspecialchars($user['city']); ?></span></div>
          <?php endif; ?>
          <?php if(!empty($user['linkedin_url'])): ?>
          <div class="profile-link-item">
            <i class="fab fa-linkedin"></i>
            <a href="<?php echo htmlspecialchars($user['linkedin_url']); ?>" id="display-linkedin" target="_blank">LinkedIn</a>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

      <!-- Action Button -->
      <form action="index.php?action=delete_profile" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer définitivement votre compte ? Vous allez être déconnecté.');">
        <button type="submit" class="btn-cancel" style="width:100%; display:flex; justify-content:center; gap:8px; background:#fff;">
          <i class="fa fa-trash"></i> Supprimer mon compte
        </button>
      </form>

    </div> <!-- CLOSING LEFT COLUMN -->

    <!-- RIGHT: Info sections -->
    <div class="right-col">

      <!-- Personal Info -->
      <div class="section-card">
        <div class="section-header">
          <h2 class="section-title">Informations personnelles</h2>
          <button class="edit-toggle" id="toggle-info" onclick="toggleSection('info')">
            <i class="fa fa-pen"></i> Modifier
          </button>
        </div>

        <!-- View mode -->
        <div id="view-info">
          <div class="form-grid">
            <div class="form-group">
              <label>Prénom</label>
              <div class="field-view" id="v-prenom"><?php echo htmlspecialchars($user['first_name']); ?></div>
            </div>
            <div class="form-group">
              <label>Nom</label>
              <div class="field-view" id="v-nom"><?php echo htmlspecialchars($user['last_name']); ?></div>
            </div>
            <div class="form-group">
              <label>E-mail</label>
              <div class="field-view" id="v-email"><?php echo htmlspecialchars($user['email']); ?></div>
            </div>
            <div class="form-group">
              <label>Téléphone</label>
              <div class="field-view" id="v-phone"><?php echo htmlspecialchars($user['phone'] ?? ''); ?></div>
            </div>
            <div class="form-group">
              <label>Rôle</label>
              <div class="field-view" id="v-role"><?php echo htmlspecialchars($user['role']); ?></div>
            </div>
            <div class="form-group">
              <label>Ville</label>
              <div class="field-view" id="v-city"><?php echo htmlspecialchars($user['city'] ?? ''); ?></div>
            </div>
            <div class="form-group full">
              <label>Biographie</label>
              <div class="field-view" id="v-bio"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></div>
            </div>
          </div>
        </div>

        <!-- Edit mode APP -->
        <div id="edit-info" style="display:none">
          <form action="index.php?action=profile" method="POST">
              <div class="form-grid">
                <div class="form-group">
                  <label for="f-prenom">Prénom *</label>
                  <input type="text" name="first_name" id="f-prenom" value="<?php echo htmlspecialchars($user['first_name']); ?>">
                </div>
                <div class="form-group">
                  <label for="f-nom">Nom *</label>
                  <input type="text" name="last_name" id="f-nom" value="<?php echo htmlspecialchars($user['last_name']); ?>">
                </div>
                <div class="form-group">
                  <label for="f-email">E-mail *</label>
                  <input type="text" name="email" id="f-email" value="<?php echo htmlspecialchars($user['email']); ?>">
                </div>
                <div class="form-group">
                  <label for="f-phone">Téléphone</label>
                  <input type="text" name="phone" id="f-phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                </div>
                <div class="form-group">
                  <label for="f-role">Rôle</label>
                  <select name="role" id="f-role">
                    <option value="Entrepreneur" <?php echo $user['role'] == 'Entrepreneur' ? 'selected' : ''; ?>>Entrepreneur</option>
                    <option value="Mentor" <?php echo $user['role'] == 'Mentor' ? 'selected' : ''; ?>>Mentor</option>
                    <option value="Entreprise" <?php echo $user['role'] == 'Entreprise' ? 'selected' : ''; ?>>Entreprise</option>
                  </select>
                </div>
                <div class="form-group">
                  <label for="f-city">Ville</label>
                  <input type="text" name="city" id="f-city" value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>">
                </div>
                <div class="form-group full">
                  <label for="f-bio">Biographie</label>
                  <textarea name="bio" id="f-bio"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                </div>
                <div class="form-group full">
                  <label for="f-linkedin">LinkedIn</label>
                  <input type="url" name="linkedin_url" id="f-linkedin" value="<?php echo htmlspecialchars($user['linkedin_url'] ?? ''); ?>">
                </div>
              </div>
              <div class="form-actions">
                <button type="button" class="btn-cancel" onclick="toggleSection('info')">Annuler</button>
                <button type="submit" class="btn-save"><i class="fa fa-check"></i> Enregistrer</button>
              </div>
          </form>
        </div>
      </div>

      <!-- Password Section -->
      <div class="section-card">
        <div class="section-header">
          <h2 class="section-title">Sécurité du compte</h2>
          <button class="edit-toggle" id="toggle-pwd" onclick="toggleSection('pwd')">
            <i class="fa fa-lock"></i> Changer le mot de passe
          </button>
        </div>

        <div id="view-pwd">
          <p style="font-size:13px;color:var(--muted)">Assurez-vous d'utiliser un mot de passe fort.</p>
        </div>

        <div id="edit-pwd" style="display:none">
          <form action="index.php?action=profile" method="POST">
             <input type="hidden" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>">
             <input type="hidden" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>">
             <input type="hidden" name="email" value="<?php echo htmlspecialchars($user['email']); ?>">
             
             <div class="form-grid">
               <div class="form-group full">
                 <label for="f-pwd-new">Nouveau mot de passe * (min 6 caractères)</label>
                 <div class="pwd-group" style="position:relative;">
                   <input type="password" name="password" id="f-pwd-new" placeholder="••••••••" style="width:100%; border:1px solid #e2e8f0; border-radius:6px; padding:10px;">
                 </div>
               </div>
             </div>
             <div class="form-actions">
               <button type="button" class="btn-cancel" onclick="toggleSection('pwd')">Annuler</button>
               <button type="submit" class="btn-save"><i class="fa fa-check"></i> Mettre à jour</button>
             </div>
          </form>
        </div>
      </div>

    </div>
  </main>

  <footer>
    &copy; 2025 Jobyfind. Tous droits réservés. &nbsp;·&nbsp;
    <a href="#" style="color:inherit">Confidentialité</a> &nbsp;·&nbsp;
    <a href="#" style="color:inherit">Conditions d'utilisation</a>
  </footer>

  <script src="views/frontoffice/assets/js/profile.js"></script>
  <script>
      function toggleSection(sec) {
          let view = document.getElementById('view-' + sec);
          let edit = document.getElementById('edit-' + sec);
          let btn = document.getElementById('toggle-' + sec);
          if(view.style.display === 'none') {
              view.style.display = 'block';
              edit.style.display = 'none';
          } else {
              view.style.display = 'none';
              edit.style.display = 'block';
          }
      }
  </script>
</body>
</html>
