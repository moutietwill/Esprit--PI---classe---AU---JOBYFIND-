<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Jobyfind — Admin CRUD PHP</title>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Serif+Display&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <link rel="stylesheet" href="views/backoffice/assets/css/styleadmin.css">
</head>
<body>

  <!-- SIDEBAR -->
  <aside class="sidebar">
    <div class="sidebar-logo">
      <div class="logo-icon" style="background-color: #7EB2FF;"><img src="views/backoffice/assets/images/jlog.png" style="width: 30px; height: 30px;" onerror="this.style.display='none'"></div>
      <div class="logo-text">Joby<span>find</span></div>
      <span class="sidebar-badge">Admin</span>
    </div>
    <div class="sidebar-section">
      <p class="sidebar-section-label">Tableau de bord</p>
      <a class="sidebar-link active" href="index.php?action=admin">
        <i class="fa-solid fa-users"></i>
        <span>Utilisateurs</span>
        <span class="badge"><?php echo count($users ?? []); ?></span>
      </a>
      <a class="sidebar-link">
        <i class="fa-solid fa-chart-line"></i>
        <span>Statistiques</span>
      </a>
    </div>
    <div class="sidebar-footer">
      <div class="admin-profile">
        <div class="admin-avatar">A</div>
        <div class="admin-info">
          <p>Admin</p>
        </div>
        <button class="logout-btn" title="Déconnexion" onclick="window.location.href='index.php?action=logout'">
          <i class="fa fa-right-from-bracket"></i>
        </button>
      </div>
    </div>
  </aside>

  <!-- MAIN -->
  <div class="main">

    <!-- HEADER -->
    <header class="header">
      <div class="header-breadcrumb">
        <span>Admin</span> <i class="fa fa-chevron-right" style="font-size:9px"></i> <span class="current">Utilisateurs</span>
      </div>
      <div class="header-search">
        <i class="fa fa-search"></i>
        <input type="text" placeholder="Rechercher un utilisateur..." id="search-input" onkeyup="filterTableCustom()">
      </div>
      <div class="header-actions">
        <div class="icon-btn" title="Retour Client" onclick="window.location.href='index.php?action=login'">
            <i class="fa fa-arrow-left"></i>
        </div>
      </div>
    </header>

    <!-- CONTENT -->
    <div class="content">

      <?php if(isset($_SESSION['success'])): ?>
      <div style="padding:15px; background-color:#10b981; color:white; border-radius:8px; margin-bottom:20px;">
          <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
      </div>
      <?php endif; ?>

      <?php if(isset($_SESSION['error'])): ?>
      <div style="padding:15px; background-color:#ef4444; color:white; border-radius:8px; margin-bottom:20px;">
          <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
      </div>
      <?php endif; ?>

      <!-- TABLE -->
      <div class="table-card">
        <div class="table-header">
          <div>
            <p class="table-title">Gestion des utilisateurs</p>
            <p class="table-subtitle"><?php echo count($users ?? []); ?> utilisateurs enregistrés</p>
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
              <th>Rôle</th>
              <th>Statut</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="table-body">
            <?php if(!empty($users)): ?>
                <?php foreach($users as $user): ?>
                <tr>
                    <td>#<?php echo htmlspecialchars($user['id']); ?></td>
                    <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><span class="badge-role"><?php echo htmlspecialchars($user['role']); ?></span></td>
                    <td><span class="badge-status <?php echo strtolower(str_replace(' ','', $user['status'])); ?>"><?php echo htmlspecialchars($user['status']); ?></span></td>
                    <td>
                        <button class="btn-icon" title="Consulter le profil" style="background:transparent;border:none;cursor:pointer;color:var(--success, #10b981)" onclick='viewUserPHP(<?php echo htmlspecialchars(json_encode($user), ENT_QUOTES, "UTF-8"); ?>)'><i class="fa fa-eye"></i></button>
                        <button class="btn-icon" style="background:transparent;border:none;cursor:pointer;color:var(--blue)" onclick='editUserPHP(<?php echo htmlspecialchars(json_encode($user), ENT_QUOTES, "UTF-8"); ?>)'><i class="fa fa-pen"></i></button>
                        <button class="btn-icon" style="background:transparent;border:none;cursor:pointer;color:var(--danger)" onclick='confirmDeletePHP(<?php echo $user['id']; ?>, "<?php echo addslashes($user['first_name'] . " " . $user['last_name']); ?>")'><i class="fa fa-trash"></i></button>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="6" style="text-align:center;">Aucun utilisateur trouvé.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- MODAL: VIEW PROFIL -->
  <div class="modal-overlay" id="user-view-modal-php" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; justify-content:center; align-items:center;">
    <div class="modal" style="background:#fff; border-radius:12px; width:500px; max-width:90%; padding:24px;">
      <div class="modal-header" style="display:flex; justify-content:space-between; margin-bottom:20px;">
        <p class="modal-title" style="font-weight:bold; font-size:18px;"><i class="fa fa-address-card"></i> Profil de l'utilisateur</p>
        <button style="border:none; background:transparent; font-size:18px; cursor:pointer;" onclick="closeModalPHP('user-view-modal-php')"><i class="fa fa-xmark"></i></button>
      </div>
      <div style="line-height:2; font-size:14px;">
        <p><strong>Nom complet:</strong> <span id="view-fullname"></span></p>
        <p><strong>Email:</strong> <span id="view-email"></span></p>
        <p><strong>Téléphone:</strong> <span id="view-phone"></span></p>
        <p><strong>Ville:</strong> <span id="view-city"></span></p>
        <p><strong>Date de naissance:</strong> <span id="view-dob"></span></p>
        <p><strong>Rôle:</strong> <span id="view-role"></span></p>
        <p><strong>Statut:</strong> <span id="view-status"></span></p>
      </div>
      <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:20px;">
        <button type="button" style="padding:10px 15px; border:none; background:#eee; border-radius:6px; cursor:pointer;" onclick="closeModalPHP('user-view-modal-php')">Fermer</button>
      </div>
    </div>
  </div>

  <!-- MODAL: EDIT / ADD -->
  <div class="modal-overlay" id="user-modal-php" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; justify-content:center; align-items:center;">
    <div class="modal" style="background:#fff; border-radius:12px; width:500px; max-width:90%; padding:24px;">
      <div class="modal-header" style="display:flex; justify-content:space-between; margin-bottom:20px;">
        <p class="modal-title" id="modal-title-php" style="font-weight:bold; font-size:18px;">Ajouter un utilisateur</p>
        <button style="border:none; background:transparent; font-size:18px; cursor:pointer;" onclick="closeModalPHP('user-modal-php')"><i class="fa fa-xmark"></i></button>
      </div>
      
      <form action="index.php?action=admin" method="POST">
          <input type="hidden" name="id" id="php-id">
          <div class="form-row" style="display:flex; gap:15px; margin-bottom:15px;">
              <div style="flex:1;">
                  <label style="display:block; font-size:13px; margin-bottom:5px;">Prénom</label>
                  <input type="text" name="first_name" id="php-first_name" required style="width:100%; padding:8px; border:1px solid #ccc; border-radius:6px;">
              </div>
              <div style="flex:1;">
                  <label style="display:block; font-size:13px; margin-bottom:5px;">Nom</label>
                  <input type="text" name="last_name" id="php-last_name" required style="width:100%; padding:8px; border:1px solid #ccc; border-radius:6px;">
              </div>
          </div>
          
          <div style="margin-bottom:15px;">
              <label style="display:block; font-size:13px; margin-bottom:5px;">Email</label>
              <input type="text" name="email" id="php-email" required style="width:100%; padding:8px; border:1px solid #ccc; border-radius:6px;">
          </div>
          
          <div class="form-row" style="display:flex; gap:15px; margin-bottom:15px;">
              <div style="flex:1;">
                  <label style="display:block; font-size:13px; margin-bottom:5px;">Téléphone</label>
                  <input type="text" name="phone" id="php-phone" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:6px;">
              </div>
              <div style="flex:1;">
                  <label style="display:block; font-size:13px; margin-bottom:5px;">Ville</label>
                  <input type="text" name="city" id="php-city" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:6px;">
              </div>
              <div style="flex:1;">
                  <label style="display:block; font-size:13px; margin-bottom:5px;">Date de naissance</label>
                  <input type="date" name="date_of_birth" id="php-date_of_birth" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:6px;">
              </div>
          </div>
          
          <div class="form-row" style="display:flex; gap:15px; margin-bottom:20px;">
              <div style="flex:1;">
                  <label style="display:block; font-size:13px; margin-bottom:5px;">Rôle</label>
                  <select name="role" id="php-role" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:6px;">
                      <option value="Entrepreneur">Entrepreneur</option>
                      <option value="Mentor">Mentor</option>
                      <option value="Entreprise">Entreprise</option>
                      <option value="Admin">Admin</option>
                  </select>
              </div>
              <div style="flex:1;">
                  <label style="display:block; font-size:13px; margin-bottom:5px;">Statut</label>
                  <select name="status" id="php-status" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:6px;">
                      <option value="Actif">Actif</option>
                      <option value="En attente">En attente</option>
                      <option value="Suspendu">Suspendu</option>
                  </select>
              </div>
          </div>
          
          <div style="display:flex; justify-content:flex-end; gap:10px;">
              <button type="button" style="padding:10px 15px; border:none; background:#eee; border-radius:6px; cursor:pointer;" onclick="closeModalPHP('user-modal-php')">Annuler</button>
              <button type="submit" style="padding:10px 15px; border:none; background:var(--blue, #2563eb); color:white; border-radius:6px; cursor:pointer;">Enregistrer</button>
          </div>
      </form>
    </div>
  </div>

  <!-- MODAL: DELETE CONFIRM -->
  <div class="modal-overlay" id="delete-modal-php" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; justify-content:center; align-items:center;">
    <div class="modal" style="background:#fff; border-radius:12px; width:400px; padding:24px;">
      <div class="modal-header" style="display:flex; justify-content:space-between; margin-bottom:15px;">
        <p class="modal-title" style="color:#ef4444; font-weight:bold;"><i class="fa fa-triangle-exclamation"></i> Supprimer</p>
        <button style="border:none; background:transparent; font-size:18px; cursor:pointer;" onclick="closeModalPHP('delete-modal-php')"><i class="fa fa-xmark"></i></button>
      </div>
      <p style="margin-bottom:20px;">Voulez-vous vraiment supprimer l'utilisateur <strong id="delete-name-php"></strong> ?</p>
      
      <form action="index.php?action=admin" method="POST" style="display:flex; justify-content:flex-end; gap:10px;">
          <input type="hidden" name="delete_id" id="php-delete-id">
          <button type="button" style="padding:10px 15px; border:none; background:#eee; border-radius:6px; cursor:pointer;" onclick="closeModalPHP('delete-modal-php')">Annuler</button>
          <button type="submit" style="padding:10px 15px; border:none; background:#ef4444; color:white; border-radius:6px; cursor:pointer;">Supprimer</button>
      </form>
    </div>
  </div>

  <script>
    function viewUserPHP(user) {
        document.getElementById('view-fullname').innerText = user.first_name + ' ' + user.last_name;
        document.getElementById('view-email').innerText = user.email || 'N/A';
        document.getElementById('view-phone').innerText = user.phone || 'N/A';
        document.getElementById('view-city').innerText = user.city || 'N/A';
        document.getElementById('view-dob').innerText = user.date_of_birth || 'N/A';
        document.getElementById('view-role').innerText = user.role || 'N/A';
        document.getElementById('view-status').innerText = user.status || 'N/A';
        document.getElementById('user-view-modal-php').style.display = 'flex';
    }

    function openAddModalPHP() {
        document.getElementById('php-id').value = '';
        document.getElementById('php-first_name').value = '';
        document.getElementById('php-last_name').value = '';
        document.getElementById('php-email').value = '';
        document.getElementById('php-phone').value = '';
        document.getElementById('php-city').value = '';
        document.getElementById('php-date_of_birth').value = '';
        document.getElementById('php-role').value = 'Entrepreneur';
        document.getElementById('php-status').value = 'Actif';
        document.getElementById('modal-title-php').innerText = "Ajouter un utilisateur";
        document.getElementById('user-modal-php').style.display = 'flex';
    }

    function editUserPHP(user) {
        document.getElementById('php-id').value = user.id;
        document.getElementById('php-first_name').value = user.first_name;
        document.getElementById('php-last_name').value = user.last_name;
        document.getElementById('php-email').value = user.email;
        document.getElementById('php-phone').value = user.phone;
        document.getElementById('php-city').value = user.city;
        document.getElementById('php-date_of_birth').value = user.date_of_birth || '';
        document.getElementById('php-role').value = user.role;
        document.getElementById('php-status').value = user.status;
        document.getElementById('modal-title-php').innerText = "Modifier l'utilisateur";
        document.getElementById('user-modal-php').style.display = 'flex';
    }

    function confirmDeletePHP(id, name) {
        document.getElementById('php-delete-id').value = id;
        document.getElementById('delete-name-php').innerText = name;
        document.getElementById('delete-modal-php').style.display = 'flex';
    }

    function closeModalPHP(id) {
        document.getElementById(id).style.display = 'none';
    }

    function filterTableCustom() {
        let input = document.getElementById('search-input').value.toLowerCase();
        let table = document.getElementById('table-body');
        let tr = table.getElementsByTagName('tr');
        for (let i = 0; i < tr.length; i++) {
            let tdName = tr[i].getElementsByTagName('td')[1];
            let tdEmail = tr[i].getElementsByTagName('td')[2];
            if (tdName || tdEmail) {
                let nameValue = tdName.textContent || tdName.innerText;
                let emailValue = tdEmail.textContent || tdEmail.innerText;
                if (nameValue.toLowerCase().indexOf(input) > -1 || emailValue.toLowerCase().indexOf(input) > -1) {
                    tr[i].style.display = "";
                } else {
                    tr[i].style.display = "none";
                }
            }       
        }
    }
  </script>
</body>
</html>
