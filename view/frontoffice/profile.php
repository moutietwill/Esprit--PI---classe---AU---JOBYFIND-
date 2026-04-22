<?php
session_start();
require_once(__DIR__ . '/../../Controller/UtilisateurController.php');
require_once(__DIR__ . '/../../Controller/ProfileController.php');
require_once(__DIR__ . '/../../Model/Utilisateur.php');
require_once(__DIR__ . '/../../Model/Profile.php');

$userController = new UtilisateurController();
$profileController = new ProfileController();


if (!isset($_SESSION['user_id'])) {
    header('Location: signin.php');
    exit();
}
$userId = $_SESSION['user_id'];


if (isset($_POST['delete_account'])) {
    $userController->deleteUser($userId);
    session_unset();
    session_destroy();
    header('Location: register.php?message=Compte supprimé');
    exit();
}

$user = $userController->getUserById($userId);
$profileData = $profileController->getProfileByUserId($userId);


if (!$profileData) {
    $newProfile = new Profile([
        'Id_utilisateur' => $userId,
        'bio' => 'Aucune biographie.',
        'ville' => $user['city'] ?? 'Non spécifiée',
        'pays' => 'Tunisie'
    ]);
    $profileController->addProfile($newProfile);
    $profileData = $profileController->getProfileByUserId($userId);
}

$profile = new Profile($profileData);


if (isset($_POST['first_name'])) {
    $updatedUser = new Utilisateur([
        'first_name' => $_POST['first_name'],
        'last_name' => $_POST['last_name'],
        'email' => $_POST['email'],
        'phone' => $_POST['phone'] ?? null,
        'city' => $_POST['city'] ?? null,
        'username' => $user['username'], 
        'password' => $user['password'], 
        'role' => $_POST['role'] ?? $user['role'],
        'status' => $user['status']
    ]);
    $userController->updateUser($updatedUser, $userId);

    $updatedProfile = new Profile([
        'Id_utilisateur' => $userId,
        'bio' => $_POST['bio'] ?? $profile->getBio(),
        'linkedin' => $_POST['linkedin_url'] ?? $profile->getLinkedin(),
        'photo_profil' => $profile->getPhoto_profil(),
        'ville' => $_POST['city'] ?? $profile->getVille(),
        'pays' => $_POST['pays'] ?? $profile->getPays(),
        'competences' => $_POST['competences'] ?? $profile->getCompetences(),
        'profession' => $_POST['role'] ?? $profile->getProfession()
    ]);
    $profileController->updateProfile($updatedProfile, $userId);

    header('Location: profile.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Jobyfind — Mon Profil</title>
  <link rel="icon" type="image/png" href="assets/images/jlog.png">
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Serif+Display&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <link rel="stylesheet" href="assets/css/styleprofile.css">
  <script src="assets/js/profile.js"></script>
</head>
<body>

  
  <nav>
    <a class="nav-logo" href="signin.php">Joby<span>find</span></a>
    <div class="nav-actions">
      <a class="btn-outline" href="profile.php" style="background:var(--blue);color:#fff">Mon profil</a>
      <a class="btn-solid" href="logout.php">Déconnexion</a>
    </div>
  </nav>

  
  <main>
    <div style="display:flex; flex-direction:column; gap:20px;">
    
    <div class="profile-card">
      <div class="profile-banner"></div>

      <div class="avatar-wrap">
        <div class="avatar-img" id="avatar-display">
          <?php if($profile->getPhoto_profil()): ?>
            <img id="avatar-preview" src="<?php echo htmlspecialchars($profile->getPhoto_profil()); ?>" alt="Photo de profil">
          <?php else: ?>
            <span id="avatar-initials"><?php echo strtoupper(substr($user['first_name'],0,1).substr($user['last_name'],0,1)); ?></span>
          <?php endif; ?>
        </div>
        <label class="avatar-upload-btn" for="avatar-input" title="Changer la photo">
          <i class="fa fa-camera"></i>
        </label>
        <input type="file" id="avatar-input" accept="image/*">
      </div>

      <div class="profile-info">
        <p class="profile-name" id="display-name"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></p>
        <span class="profile-role-badge"><i class="fa fa-lightbulb"></i> <span id="display-role"><?php echo htmlspecialchars($user['role']); ?></span></span>
        <p class="profile-bio" id="display-bio"><?php echo htmlspecialchars($profile->getBio()); ?></p>

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
          <div class="profile-link-item"><i class="fa fa-phone"></i> <span id="display-phone"><?php echo htmlspecialchars($user['phone'] ?? 'Non spécifié'); ?></span></div>
          <div class="profile-link-item"><i class="fa fa-map-marker-alt"></i> <span id="display-city"><?php echo htmlspecialchars($user['city'] ?? 'Non spécifiée'); ?></span>, <span id="display-country"><?php echo htmlspecialchars($profile->getPays() ?? 'Tunisie'); ?></span></div>
          <div class="profile-link-item"><i class="fa fa-code"></i> <strong>Compétences:</strong> <span id="display-skills"><?php echo htmlspecialchars($profile->getCompetences() ?? 'Aucune'); ?></span></div>
          <?php if($profile->getLinkedin()): ?>
          <div class="profile-link-item">
            <i class="fab fa-linkedin"></i>
            <a href="<?php echo htmlspecialchars($profile->getLinkedin()); ?>" id="display-linkedin" target="_blank">LinkedIn</a>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

      
      <form action="profile.php" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer définitivement votre compte ?');">
        <input type="hidden" name="delete_account" value="1">
        <button type="submit" class="btn-cancel" style="width:100%; display:flex; justify-content:center; gap:8px; background:#fff;">
          <i class="fa fa-trash"></i> Supprimer mon compte
        </button>
      </form>

    </div>

    
    <div class="right-col">

      
      <div class="section-card">
        <div class="section-header">
          <h2 class="section-title">Informations personnelles</h2>
          <button class="edit-toggle" id="toggle-info" onclick="toggleSection('info')">
            <i class="fa fa-pen"></i> Modifier
          </button>
        </div>

        
        <div id="view-info">
          <div class="form-grid">
            <div class="form-group">
              <label>Prénom</label>
              <div class="field-view"><?php echo htmlspecialchars($user['first_name']); ?></div>
            </div>
            <div class="form-group">
              <label>Nom</label>
              <div class="field-view"><?php echo htmlspecialchars($user['last_name']); ?></div>
            </div>
            <div class="form-group">
              <label>E-mail</label>
              <div class="field-view"><?php echo htmlspecialchars($user['email']); ?></div>
            </div>
            <div class="form-group">
              <label>Téléphone</label>
              <div class="field-view"><?php echo htmlspecialchars($user['phone'] ?? 'Non spécifié'); ?></div>
            </div>
            <div class="form-group">
              <label>Rôle</label>
              <div class="field-view"><?php echo htmlspecialchars($user['role']); ?></div>
            </div>
            <div class="form-group">
              <label>Ville</label>
              <div class="field-view"><?php echo htmlspecialchars($user['city'] ?? 'Non spécifiée'); ?></div>
            </div>
            <div class="form-group full">
              <label>Biographie</label>
              <div class="field-view"><?php echo htmlspecialchars($profile->getBio()); ?></div>
            </div>
          </div>
        </div>

        
        <div id="edit-info" style="display:none">
          <form action="profile.php" method="POST">
              <div class="form-grid">
                <div class="form-group">
                  <label for="f-prenom">Prénom *</label>
                  <input type="text" name="first_name" id="f-prenom" value="<?php echo htmlspecialchars($user['first_name']); ?>">
                  <span id="error-f-prenom" class="controle-saisie"></span>
                </div>
                <div class="form-group">
                  <label for="f-nom">Nom *</label>
                  <input type="text" name="last_name" id="f-nom" value="<?php echo htmlspecialchars($user['last_name']); ?>">
                  <span id="error-f-nom" class="controle-saisie"></span>
                </div>
                <div class="form-group">
                  <label for="f-email">E-mail *</label>
                  <input type="text" name="email" id="f-email" value="<?php echo htmlspecialchars($user['email']); ?>">
                  <span id="error-f-email" class="controle-saisie"></span>
                </div>
                <div class="form-group">
                  <label for="f-phone">Téléphone</label>
                  <input type="text" name="phone" id="f-phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                  <span id="error-f-phone" class="controle-saisie"></span>
                </div>
                <div class="form-group">
                  <label for="f-role">Rôle</label>
                  <select name="role" id="f-role">
                    <option value="Entrepreneur" <?php if($user['role'] == 'Entrepreneur') echo 'selected'; ?>>Entrepreneur</option>
                    <option value="Mentor" <?php if($user['role'] == 'Mentor') echo 'selected'; ?>>Mentor</option>
                    <option value="Entreprise" <?php if($user['role'] == 'Entreprise') echo 'selected'; ?>>Entreprise</option>
                  </select>
                </div>
                <div class="form-group">
                  <label for="f-city">Ville</label>
                  <input type="text" name="city" id="f-city" value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>">
                  <span id="error-f-city" class="controle-saisie"></span>
                </div>
                <div class="form-group">
                  <label for="f-pays">Pays</label>
                  <input type="text" name="pays" id="f-pays" value="<?php echo htmlspecialchars($profile->getPays() ?? 'Tunisie'); ?>">
                  <span id="error-f-pays" class="controle-saisie"></span>
                </div>
                <div class="form-group full">
                  <label for="f-bio">Biographie</label>
                  <textarea name="bio" id="f-bio"><?php echo htmlspecialchars($profile->getBio()); ?></textarea>
                  <span id="error-f-bio" class="controle-saisie"></span>
                </div>
                <div class="form-group full">
                  <label for="f-competences">Compétences (séparées par des virgules)</label>
                  <input type="text" name="competences" id="f-competences" value="<?php echo htmlspecialchars($profile->getCompetences() ?? ''); ?>">
                  <span id="error-f-competences" class="controle-saisie"></span>
                </div>
                <div class="form-group full">
                  <label for="f-linkedin">LinkedIn</label>
                  <input type="text" name="linkedin_url" id="f-linkedin" value="<?php echo htmlspecialchars($profile->getLinkedin() ?? ''); ?>">
                  <span id="error-f-linkedin" class="controle-saisie"></span>
                </div>
              </div>
              <div class="form-actions">
                <button type="button" class="btn-cancel" onclick="toggleSection('info')">Annuler</button>
                <button type="submit" class="btn-save"><i class="fa fa-check"></i> Enregistrer</button>
              </div>
          </form>
        </div>
      </div>

      
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
          <form action="profile.php" method="POST">
             <div class="form-grid">
               <div class="form-group full">
                 <label for="f-pwd-new">Nouveau mot de passe * (min 6 caractères)</label>
                 <div class="pwd-group" style="position:relative;">
                   <input type="password" name="password" id="f-pwd-new" placeholder="••••••••" style="width:100%; border:1px solid #e2e8f0; border-radius:6px; padding:10px;">
                 </div>
                 <span id="error-f-pwd-new" class="controle-saisie"></span>
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

</body>
</html>
