<?php
session_start();
require_once(__DIR__ . '/../../Controller/UtilisateurController.php');
require_once(__DIR__ . '/../../Controller/ProfileController.php');
require_once(__DIR__ . '/../../Model/Utilisateur.php');
require_once(__DIR__ . '/../../Model/Profile.php');


if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header('Location: ../frontoffice/signin.php');
    exit();
}

$userController = new UtilisateurController();
$profileController = new ProfileController();

$message = "";
$messageType = ""; 


if (isset($_POST['delete_id'])) {
    try {
        $userController->deleteUser($_POST['delete_id']);
        header('Location: admine.php?success=Utilisateur supprimé');
        exit();
    } catch (Exception $e) {
        $message = "Erreur lors de la suppression.";
        $messageType = "error";
    }
}

// Handle user reactivation
if (isset($_POST['reactivate_id'])) {
    try {
        $db = config::getConnexion();
        $sql = "UPDATE utilisateurs SET status = 'Actif' WHERE id = :id";
        $stmt = $db->prepare($sql);
        $stmt->execute(['id' => $_POST['reactivate_id']]);
        header('Location: admine.php?success=Compte réactivé avec succès');
        exit();
    } catch (Exception $e) {
        $message = "Erreur lors de la réactivation.";
        $messageType = "error";
    }
}


if (isset($_POST['first_name']) && isset($_POST['last_name']) && isset($_POST['email'])) {
    try {
        $user = new Utilisateur([
            'first_name' => $_POST['first_name'],
            'last_name' => $_POST['last_name'],
            'username' => $_POST['username'] ?? null,
            'date_of_birth' => $_POST['date_of_birth'] ?? null,
            'phone' => $_POST['phone'] ?? null,
            'city' => $_POST['city'] ?? null,
            'email' => $_POST['email'],
            'password' => $_POST['password'] ?? 'default123',
            'role' => $_POST['role'] ?? 'Entrepreneur',
            'status' => $_POST['status'] ?? 'Actif'
        ]);

        if (!empty($_POST['id'])) {
            $userController->updateUser($user, $_POST['id']);
            

            $profile = new Profile([
                'Id_utilisateur' => $_POST['id'],
                'bio' => $_POST['bio'] ?? '',
                'linkedin' => $_POST['linkedin'] ?? '',
                'competences' => $_POST['competences'] ?? '',
                'ville' => $_POST['city'] ?? '',
                'pays' => $_POST['pays'] ?? 'Tunisie',
                'profession' => $_POST['role'] ?? 'Entrepreneur'
            ]);
            $profileController->updateProfile($profile, $_POST['id']);
            
            header('Location: admine.php?success=Utilisateur et profil mis à jour');
        } else {
            $userController->addUser($user);
            header('Location: admine.php?success=Utilisateur ajouté');
        }
        exit();
    } catch (Exception $e) {
        $message = "Une erreur est survenue.";
        $messageType = "error";
    }
}

$sort = $_GET['sort'] ?? null;
$order = $_GET['order'] ?? 'ASC';
$users = $userController->listUsers($sort, $order);

$activeStats = $userController->getMonthlyActiveUsersStats();
$entrepreneurStats = $userController->getMonthlyEntrepreneurStats();

if (isset($_GET['success'])) {
    $message = $_GET['success'];
    $messageType = "success";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Jobyfind — Admin CRUD PHP</title>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Serif+Display&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <link rel="stylesheet" href="assets/css/styleadmin.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    .alert { padding: 12px 20px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; display: flex; align-items: center; gap: 10px; }
    .alert-success { background: #ecfdf5; color: #059669; border: 1px solid #10b981; }
    .alert-error { background: #fef2f2; color: #dc2626; border: 1px solid #f87171; }
    .profile-advanced-info { background: #f8fafc; border-radius: 8px; padding: 15px; margin-top: 15px; border-left: 4px solid #2563eb; }
    .profile-advanced-info p { margin-bottom: 8px; }
    
    .view-section { display: none; }
    .view-section.active { display: block; }
    
    .charts-container { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px; }
    .chart-card { background: white; padding: 25px; border-radius: 16px; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); height: 450px; display: flex; flex-direction: column; }
    .chart-title { font-weight: 600; margin-bottom: 20px; color: #1e293b; display: flex; align-items: center; gap: 10px; font-size: 16px; }
    .chart-wrapper { flex: 1; position: relative; min-height: 0; }
    
    .sort-link { color: inherit; text-decoration: none; display: flex; align-items: center; gap: 5px; }
    .sort-link:hover { color: #2563eb; }
  </style>
  <script src="assets/js/admin.js?v=1.1"></script>
</head>
<body>


  <aside class="sidebar">
    <div class="sidebar-logo">
      <div class="logo-icon" style="background-color: #7EB2FF;"><img src="assets/images/jlog.png" style="width: 30px; height: 30px;" onerror="this.style.display='none'"></div>
      <div class="logo-text">Joby<span>find</span></div>
      <span class="sidebar-badge">Admin</span>
    </div>
    <div class="sidebar-section">
      <p class="sidebar-section-label">Tableau de bord</p>
      <a class="sidebar-link active" href="#" onclick="showSection('users-section', this)">
        <i class="fa-solid fa-users"></i>
        <span>Utilisateurs</span>
        <span class="badge"><?php echo count($users); ?></span>
      </a>
      <a class="sidebar-link" href="#" onclick="showSection('stats-section', this)">
        <i class="fa-solid fa-chart-line"></i>
        <span>Statistiques</span>
      </a>
      <a class="sidebar-link" href="#" onclick="showSection('roles-section', this)">
        <i class="fa-solid fa-shield-halved"></i>
        <span>Rôles & Accès</span>
        <?php 
          $violationCount = count(array_filter($users, fn($u) => ($u['violations_count'] ?? 0) > 0));
          if ($violationCount > 0): 
        ?>
          <span class="badge" style="background:var(--danger)"><?php echo $violationCount; ?></span>
        <?php endif; ?>
      </a>
    </div>

    <div class="sidebar-section">
      <p class="sidebar-section-label">Gestion</p>
      <a class="sidebar-link" href="#">
        <i class="fa-solid fa-graduation-cap"></i>
        <span>Formations</span>
      </a>
      <a class="sidebar-link" href="#">
        <i class="fa-solid fa-blog"></i>
        <span>Blogs</span>
      </a>
      <a class="sidebar-link" href="#">
        <i class="fa-solid fa-calendar-star"></i>
        <span>Événements</span>
      </a>
      <a class="sidebar-link" href="#">
        <i class="fa-solid fa-briefcase"></i>
        <span>Offres</span>
      </a>
      <a class="sidebar-link" href="#">
        <i class="fa-solid fa-flag"></i>
        <span>Signalements</span>
        <span class="badge" style="background:var(--danger)">3</span>
      </a>
      <a class="sidebar-link" href="#">
        <i class="fa-solid fa-gear"></i>
        <span>Paramètres</span>
      </a>
    </div>
    <div class="sidebar-footer">
      <div class="admin-profile">
        <div class="admin-avatar">A</div>
        <div class="admin-info">
          <p>Admin</p>
        </div>
        <button class="logout-btn" title="Déconnexion" onclick="window.location.href='logout.php'">
          <i class="fa fa-right-from-bracket"></i>
        </button>
      </div>
    </div>
  </aside>


  <div class="main">


    <header class="header">
      <div class="header-breadcrumb">
        <span>Admin</span> <i class="fa fa-chevron-right" style="font-size:9px"></i> <span class="current">Utilisateurs</span>
      </div>
      <div class="header-search">
        <i class="fa fa-search"></i>
        <input type="text" placeholder="Rechercher un utilisateur..." id="search-input" onkeyup="filterTableCustom()">
      </div>
      <div class="header-actions">
        <div class="icon-btn" title="Retour Client" onclick="window.location.href='../frontoffice/signin.php'">
            <i class="fa fa-arrow-left"></i>
        </div>
      </div>
    </header>


    <div class="content">

      <?php if($message): ?>
      <script>
        document.addEventListener('DOMContentLoaded', () => {
          showToast("<?php echo addslashes($message); ?>", "<?php echo $messageType; ?>");
        });
      </script>
      <?php endif; ?>

      <?php
        $totalUsers = count($users);
        $actifs = count(array_filter($users, fn($u) => $u['status'] === 'Actif'));
        $attente = count(array_filter($users, fn($u) => $u['status'] === 'En attente'));
        $suspendus = count(array_filter($users, fn($u) => $u['status'] === 'Suspendu'));
      ?>




      <div id="users-section" class="view-section active">
        <div class="table-card">
          <div class="table-header">
            <div>
              <p class="table-title">Gestion des utilisateurs</p>
              <p class="table-subtitle"><?php echo count($users); ?> utilisateurs enregistrés</p>
            </div>
            <div class="table-controls">
              <button class="btn-primary" onclick="openAddModalPHP()">
                <i class="fa fa-plus"></i> Ajouter
              </button>
            </div>
          </div>

          <table id="user-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Utilisateur</th>
                <th>Email</th>
                <th>
                  <a href="?sort=role&order=<?php echo ($sort === 'role' && $order === 'ASC') ? 'DESC' : 'ASC'; ?>" class="sort-link">
                    Rôle <i class="fa fa-sort<?php echo $sort === 'role' ? ($order === 'ASC' ? '-up' : '-down') : ''; ?>"></i>
                  </a>
                </th>
                <th>
                  <a href="?sort=status&order=<?php echo ($sort === 'status' && $order === 'ASC') ? 'DESC' : 'ASC'; ?>" class="sort-link">
                    Statut <i class="fa fa-sort<?php echo $sort === 'status' ? ($order === 'ASC' ? '-up' : '-down') : ''; ?>"></i>
                  </a>
                </th>
                <th>Membre depuis</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody id="table-body">
              <?php if (empty($users)): ?>
              <tr>
                <td colspan="7" style="text-align:center;">Aucun utilisateur trouvé.</td>
              </tr>
              <?php else: ?>
                <?php foreach ($users as $u): ?>
                <tr>
                  <td><?php echo $u['id']; ?></td>
                  <td>
                    <div class="user-cell">
                      <div class="user-avatar" style="background:#dbeafe; color:#2563eb;"><?php echo strtoupper(substr($u['first_name']??'',0,1).substr($u['last_name']??'',0,1)); ?></div>
                      <div>
                        <p class="user-name"><?php echo htmlspecialchars(($u['first_name']??'') . ' ' . ($u['last_name']??'')); ?></p>
                        <p class="user-email">@<?php echo htmlspecialchars($u['username']??''); ?></p>
                      </div>
                    </div>
                  </td>
                  <td><?php echo htmlspecialchars($u['email']??''); ?></td>
                  <td><span class="badge badge-blue"><?php echo htmlspecialchars($u['role']??''); ?></span></td>
                  <td>
                    <?php 
                      $statusClass = 'badge-gray';
                      $status = $u['status'] ?? 'Inconnu';
                      if ($status === 'Actif') $statusClass = 'badge-green';
                      elseif ($status === 'Suspendu') $statusClass = 'badge-red';
                      elseif ($status === 'En attente') $statusClass = 'badge-amber';
                    ?>
                    <span class="badge <?php echo $statusClass; ?>"><?php echo htmlspecialchars($status); ?></span>
                  </td>
                  <td><span style="font-size:12px;color:var(--muted);"><?php echo isset($u['created_at']) ? date('d/m/Y', strtotime($u['created_at'])) : 'N/A'; ?></span></td>
                  <td>
                    <div class="action-btns">
                      <?php if ($u['status'] === 'Suspendu'): ?>
                        <button class="action-btn" onclick="confirmReactivatePHP(<?php echo $u['id']; ?>, '<?php echo htmlspecialchars(($u['first_name']??'').' '.($u['last_name']??'')); ?>')" title="Réactiver le compte" style="color:#10b981; background:#ecfdf5; border-color:#10b981;">
                          <i class="fa fa-unlock"></i>
                        </button>
                      <?php endif; ?>
                      <button class="action-btn view" onclick='viewFullProfilePHP(<?php echo $u['id']; ?>)' title="Voir Profil Complet"><i class="fa fa-eye"></i></button>
                      <button class="action-btn edit" onclick='editUserPHP(<?php echo json_encode($u); ?>)' title="Modifier"><i class="fa fa-pen"></i></button>
                      <button class="action-btn del" onclick="confirmDeletePHP(<?php echo $u['id']; ?>, '<?php echo htmlspecialchars(($u['first_name']??'').' '.($u['last_name']??'')); ?>')" title="Supprimer"><i class="fa fa-trash"></i></button>
                    </div>
                  </td>
                </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <div id="stats-section" class="view-section">
        <div class="stats-grid" style="margin-bottom: 30px;">
          <div class="stat-card">
            <div class="stat-icon blue"><i class="fa fa-users"></i></div>
            <div>
              <p class="stat-label">Total Utilisateurs</p>
              <p class="stat-value"><?php echo $totalUsers; ?></p>
            </div>
          </div>
          <div class="stat-card">
            <div class="stat-icon green"><i class="fa fa-check-circle"></i></div>
            <div>
              <p class="stat-label">Comptes Actifs</p>
              <p class="stat-value"><?php echo $actifs; ?></p>
            </div>
          </div>
          <div class="stat-card">
            <div class="stat-icon amber"><i class="fa fa-clock"></i></div>
            <div>
              <p class="stat-label">En attente</p>
              <p class="stat-value"><?php echo $attente; ?></p>
            </div>
          </div>
          <div class="stat-card">
            <div class="stat-icon red"><i class="fa fa-ban"></i></div>
            <div>
              <p class="stat-label">Suspendus</p>
              <p class="stat-value"><?php echo $suspendus; ?></p>
            </div>
          </div>
        </div>

        <div class="charts-container">
          <div class="chart-card">
            <p class="chart-title"><i class="fa fa-money-bill-trend-up" style="color:#10b981"></i> Revenu estimé (Personnes Actives)</p>
            <div class="chart-wrapper">
                <canvas id="revenueChart"></canvas>
            </div>
          </div>
          <div class="chart-card">
            <p class="chart-title"><i class="fa fa-user-tie" style="color:#2563eb"></i> Entrepreneurs Actifs</p>
            <div class="chart-wrapper">
                <canvas id="entrepreneurChart"></canvas>
            </div>
          </div>
        </div>
      </div>

      <!-- Rôles & Accès (Violations) Section -->
      <div id="roles-section" class="view-section">
        <div class="table-card">
          <div class="table-header">
            <div>
              <p class="table-title"><i class="fa fa-shield-halved"></i> Surveillance des Violations</p>
              <p class="table-subtitle">Utilisateurs ayant enfreint les règles de la plateforme</p>
            </div>
          </div>

          <table id="roles-table">
            <thead>
              <tr>
                <th>Utilisateur</th>
                <th>Fautes</th>
                <th>Niveau de Risque</th>
                <th style="text-align:center;">Statut (cliquer pour changer)</th>
                <th>Notes de surveillance</th>
              </tr>
            </thead>
            <tbody>
              <?php 
                $riskyUsers = array_filter($users, fn($u) => ($u['violations_count'] ?? 0) > 0);
                if (empty($riskyUsers)): 
              ?>
                <tr><td colspan="5" style="text-align:center;">Aucune violation enregistrée.</td></tr>
              <?php else: ?>
                <?php foreach ($riskyUsers as $u): ?>
                  <tr>
                    <td>
                      <div class="user-cell">
                        <div class="user-avatar" style="background:#fef2f2; color:#ef4444;"><?php echo strtoupper(substr($u['first_name']??'',0,1).substr($u['last_name']??'',0,1)); ?></div>
                        <div>
                          <p class="user-name"><?php echo htmlspecialchars($u['first_name'].' '.$u['last_name']); ?></p>
                          <p class="user-email"><?php echo htmlspecialchars($u['email']); ?></p>
                        </div>
                      </div>
                    </td>
                    <td style="font-weight:600;"><?php echo $u['violations_count']; ?> / 3</td>
                    <td>
                      <?php 
                        $count = $u['violations_count'];
                        if ($count == 1) echo '<span class="badge" style="background:#fef3c7; color:#92400e;">🟡 Risque Faible</span>';
                        elseif ($count == 2) echo '<span class="badge" style="background:#ffedd5; color:#9a3412;">🟠 Risque Élevé</span>';
                        else echo '<span class="badge" style="background:#fee2e2; color:#991b1b;">🔴 DANGER CRITIQUE</span>';
                      ?>
                    </td>
                    <td style="text-align:center;">
                      <span class="badge status-toggle <?php echo ($u['status']==='Suspendu')?'badge-red':(($u['status']==='Actif')?'badge-green':'badge-amber'); ?>" 
                            onclick="toggleStatus(this, <?php echo $u['id']; ?>)" 
                            style="cursor:pointer; transition: transform 0.2s; display:inline-block; user-select:none;"
                            onmouseover="this.style.transform='scale(1.1)'"
                            onmouseout="this.style.transform='scale(1)'">
                        <?php echo $u['status']; ?>
                      </span>
                    </td>
                    <td>
                      <input type="text" class="form-input" 
                             placeholder="Ajouter une note..." 
                             value="<?php echo htmlspecialchars($u['violation_notes'] ?? ''); ?>"
                             onchange="saveNote(<?php echo $u['id']; ?>, this.value)"
                             style="width:100%; height:32px; font-size:12px; border-style:dashed;">
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <script>
        const activeStatsData = <?php echo json_encode($activeStats); ?>;
        const entrepreneurStatsData = <?php echo json_encode($entrepreneurStats); ?>;
        
        let chartsObj = {
            revenue: null,
            entrepreneur: null
        };

        function showSection(sectionId, element) {
            document.querySelectorAll('.view-section').forEach(s => s.classList.remove('active'));
            document.getElementById(sectionId).classList.add('active');
            
            document.querySelectorAll('.sidebar-link').forEach(l => l.classList.remove('active'));
            element.classList.add('active');
            
            const currentBreadcrumb = document.querySelector('.header-breadcrumb .current');
            currentBreadcrumb.innerText = element.querySelector('span').innerText;

            if (sectionId === 'stats-section') {
                // Short delay to ensure the section is visible for Chart.js
                setTimeout(() => {
                    if (typeof initCharts === 'function') {
                        const newCharts = initCharts(activeStatsData, entrepreneurStatsData, chartsObj);
                        if (newCharts) chartsObj = newCharts;
                    } else {
                        console.error("initCharts function not found in admin.js");
                    }
                }, 200);
            }
        }

        // Initialize if stats is default section (unlikely but possible)
        document.addEventListener('DOMContentLoaded', () => {
            if (document.getElementById('stats-section').classList.contains('active')) {
                setTimeout(() => {
                    if (typeof initCharts === 'function') {
                        chartsObj = initCharts(activeStatsData, entrepreneurStatsData, chartsObj);
                    }
                }, 200);
            }
        });

        async function toggleStatus(badge, userId) {
            badge.style.opacity = '0.5';
            const formData = new FormData();
            formData.append('id', userId);

            try {
                const res = await fetch('toggle_status.php', { method: 'POST', body: formData });
                const data = await res.json();
                if (data.success) {
                    badge.innerText = data.next;
                    badge.className = 'badge status-toggle ' + 
                                     (data.next === 'Suspendu' ? 'badge-red' : 
                                     (data.next === 'Actif' ? 'badge-green' : 'badge-amber'));
                    showToast("Statut mis à jour : " + data.next);
                }
            } catch (e) {
                showToast("Erreur de mise à jour", "error");
            } finally {
                badge.style.opacity = '1';
            }
        }

        async function saveNote(userId, notes) {
            const formData = new FormData();
            formData.append('id', userId);
            formData.append('notes', notes);

            try {
                const res = await fetch('save_notes.php', { method: 'POST', body: formData });
                const data = await res.json();
                if (data.success) {
                    showToast("Note enregistrée.");
                }
            } catch (e) {
                showToast("Erreur lors de l'enregistrement de la note", "error");
            }
        }
      </script>
    </div>
  </div>


  <div class="modal-overlay" id="user-view-modal-php">
    <div class="modal" style="width: 600px;">
      <div class="modal-header">
        <p class="modal-title"><i class="fa fa-address-card"></i> Profil Complet</p>
        <button class="modal-close" onclick="closeModalPHP('user-view-modal-php')"><i class="fa fa-xmark"></i></button>
      </div>
      <div class="modal-body" id="view-modal-content">
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 15px; font-size: 14px;">
            <p><strong>Nom complet:</strong> <span id="view-fullname"></span></p>
            <p><strong>Email:</strong> <span id="view-email"></span></p>
            <p><strong>Téléphone:</strong> <span id="view-phone"></span></p>
            <p><strong>Ville:</strong> <span id="view-city"></span></p>
            <p><strong>Date de naissance:</strong> <span id="view-dob"></span></p>
            <p><strong>Rôle:</strong> <span id="view-role"></span></p>
            <p><strong>Statut:</strong> <span id="view-status"></span></p>
            <p><strong>Membre depuis:</strong> <span id="view-created-at"></span></p>
            <p><strong>Dernière connexion:</strong> <span id="view-last-login"></span></p>
        </div>
        
        <div class="profile-advanced-info" id="advanced-profile-section">
            <p><strong>Bio:</strong> <span id="view-bio">Chargement...</span></p>
            <p><strong>LinkedIn:</strong> <a href="#" id="view-linkedin" target="_blank" style="display:none;">Voir le profil</a><span id="no-linkedin">N/A</span></p>
            <p><strong>Profession:</strong> <span id="view-profession"></span></p>
            <p><strong>Compétences:</strong> <span id="view-skills"></span></p>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn-cancel" onclick="closeModalPHP('user-view-modal-php')">Fermer</button>
      </div>
    </div>
  </div>


  <div class="modal-overlay" id="user-modal-php">
    <div class="modal">
      <div class="modal-header">
        <p class="modal-title" id="modal-title-php">Ajouter un utilisateur</p>
        <button class="modal-close" onclick="closeModalPHP('user-modal-php')"><i class="fa fa-xmark"></i></button>
      </div>
      
      <form action="admine.php" method="POST">
        <div class="modal-body">
          <input type="hidden" name="id" id="php-id">
          <div class="form-row">
              <div class="form-group">
                  <label class="form-label">Prénom</label>
                  <input type="text" name="first_name" id="php-first_name" class="form-input">
                  <span id="error-php-first_name" class="controle-saisie"></span>
              </div>
              <div class="form-group">
                  <label class="form-label">Nom</label>
                  <input type="text" name="last_name" id="php-last_name" class="form-input">
                  <span id="error-php-last_name" class="controle-saisie"></span>
              </div>
          </div>
          
          <div class="form-group">
              <label class="form-label">Email</label>
              <input type="text" name="email" id="php-email" class="form-input">
              <span id="error-php-email" class="controle-saisie"></span>
          </div>

          <div class="form-group">
              <label class="form-label">Nom d'utilisateur</label>
              <input type="text" name="username" id="php-username" class="form-input">
              <span id="error-php-username" class="controle-saisie"></span>
          </div>
          
          <div class="form-row">
              <div class="form-group">
                  <label class="form-label">Téléphone</label>
                  <input type="text" name="phone" id="php-phone" class="form-input">
                  <span id="error-php-phone" class="controle-saisie"></span>
              </div>
              <div class="form-group">
                  <label class="form-label">Ville</label>
                  <input type="text" name="city" id="php-city" class="form-input">
                  <span id="error-php-city" class="controle-saisie"></span>
              </div>
          </div>
          
          <div class="form-group">
              <label class="form-label">Date de naissance</label>
              <input type="date" name="date_of_birth" id="php-date_of_birth" class="form-input">
              <span id="error-php-date_of_birth" class="controle-saisie"></span>
          </div>

          <div class="form-group">
              <label class="form-label">Nouveau mot de passe (laisser vide pour ne pas changer)</label>
              <input type="password" name="password" id="php-password" class="form-input" placeholder="••••••••">
              <span id="error-php-password" class="controle-saisie"></span>
          </div>
          
          <div class="form-row">
              <div class="form-group">
                  <label class="form-label">Rôle</label>
                  <select name="role" id="php-role" class="form-input">
                      <option value="Entrepreneur">Entrepreneur</option>
                      <option value="Mentor">Mentor</option>
                      <option value="Entreprise">Entreprise</option>
                      <option value="Admin">Admin</option>
                  </select>
              </div>
              <div class="form-group">
                  <label class="form-label">Statut</label>
                  <select name="status" id="php-status" class="form-input">
                      <option value="Actif">Actif</option>
                      <option value="En attente">En attente</option>
                      <option value="Suspendu">Suspendu</option>
                  </select>
              </div>
          </div>


          <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #eee;">
              <p style="font-weight: 600; font-size: 14px; margin-bottom: 10px; color: #2563eb;">Informations de Profil</p>
              <div class="form-group">
                  <label class="form-label">Biographie</label>
                  <textarea name="bio" id="php-bio" class="form-input" style="height: 60px;"></textarea>
              </div>
              <div class="form-row">
                  <div class="form-group">
                      <label class="form-label">LinkedIn (URL)</label>
                      <input type="text" name="linkedin" id="php-linkedin" class="form-input">
                  </div>
                  <div class="form-group">
                      <label class="form-label">Pays</label>
                      <input type="text" name="pays" id="php-pays" class="form-input" value="Tunisie">
                  </div>
              </div>
              <div class="form-group">
                  <label class="form-label">Compétences (séparées par des virgules)</label>
                  <input type="text" name="competences" id="php-competences" class="form-input">
              </div>
          </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-cancel" onclick="closeModalPHP('user-modal-php')">Annuler</button>
            <button type="submit" class="btn-primary">Enregistrer</button>
        </div>
      </form>
    </div>
  </div>


  <div class="modal-overlay" id="delete-modal-php">
    <div class="modal" style="width:400px;">
      <div class="modal-header">
        <p class="modal-title" style="color:#ef4444;"><i class="fa fa-triangle-exclamation"></i> Supprimer</p>
        <button class="modal-close" onclick="closeModalPHP('delete-modal-php')"><i class="fa fa-xmark"></i></button>
      </div>
      <div class="modal-body">
        <p>Voulez-vous vraiment supprimer l'utilisateur <strong id="delete-name-php"></strong> ?</p>
      </div>
      <form action="admine.php" method="POST" class="modal-footer">
          <input type="hidden" name="delete_id" id="php-delete-id">
          <button type="button" class="btn-cancel" onclick="closeModalPHP('delete-modal-php')">Annuler</button>
          <button type="submit" class="btn-danger">Supprimer</button>
      </form>
    </div>
  </div>
  <!-- Reactivate Modal -->
  <div class="modal-overlay" id="reactivate-modal-php">
    <div class="modal" style="width:400px;">
      <div class="modal-header">
        <p class="modal-title" style="color:#10b981;"><i class="fa fa-unlock"></i> Réactiver</p>
        <button class="modal-close" onclick="closeModalPHP('reactivate-modal-php')"><i class="fa fa-xmark"></i></button>
      </div>
      <div class="modal-body">
        <p>Voulez-vous vraiment réactiver le compte de <strong id="reactivate-name-php"></strong> ?</p>
      </div>
      <form action="admine.php" method="POST" class="modal-footer">
          <input type="hidden" name="reactivate_id" id="php-reactivate-id">
          <button type="button" class="btn-cancel" onclick="closeModalPHP('reactivate-modal-php')">Annuler</button>
          <button type="submit" class="btn-primary" style="background:#10b981;">Confirmer la réactivation</button>
      </form>
    </div>
  </div>

  <div class="toast-container" id="toast-container"></div>

</body>
</html>