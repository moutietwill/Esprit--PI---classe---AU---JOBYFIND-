
<?php
if (realpath(__FILE__) === realpath($_SERVER['SCRIPT_FILENAME'])) {
    $requestUri = $_SERVER['REQUEST_URI'];
    $redirectUrl = preg_replace('@/views/admin(?:/.*)?$@', '/projetweb_avec_evenements/public/index.php/admin/inscriptions', $requestUri);
    header('Location: ' . $redirectUrl);
    exit;
}

$inscriptions = $inscriptions ?? [];
$users = $users ?? [];
$events = $events ?? [];
$statuses = $statuses ?? ['Confirmee', 'Present', 'Absent', 'Annulee'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin - Inscriptions</title>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <style>
    :root { --blue:#2d79ff; --navy:#0b1f4b; --bg:#f0f2f8; --surface:#fff; --border:#e2e8f0; --text:#374151; --muted:#6b7280; --danger:#ef4444; }
    * { box-sizing:border-box; margin:0; padding:0; }
    body { font-family:"DM Sans",sans-serif; background:var(--bg); color:var(--text); display:flex; min-height:100vh; }
    .sidebar { width:240px; background:var(--navy); min-height:100vh; position:fixed; left:0; top:0; }
    .sidebar-logo { display:flex; align-items:center; gap:10px; padding:20px; border-bottom:1px solid rgba(255,255,255,.08); color:#fff; text-decoration:none; font-weight:600; }
    .sidebar-nav { padding:10px; }
    .sidebar-link { display:flex; gap:8px; align-items:center; padding:9px 10px; border-radius:8px; color:rgba(255,255,255,.7); text-decoration:none; }
    .sidebar-link.active { background:var(--blue); color:#fff; }
    .main { flex:1; margin-left:240px; }
    .header { height:60px; background:#fff; border-bottom:1px solid var(--border); display:flex; align-items:center; padding:0 28px; }
    .content { padding:20px 28px; }
    .card { background:var(--surface); border:1px solid var(--border); border-radius:10px; overflow:hidden; }
    .card-header { display:flex; justify-content:space-between; align-items:center; padding:16px; border-bottom:1px solid var(--border); }
    .card-title { font-size:24px; color:var(--navy); font-weight:600; }
    .card-subtitle { font-size:13px; color:var(--muted); margin-top:4px; }
    .btn-primary { border:none; background:var(--blue); color:#fff; border-radius:6px; padding:9px 14px; cursor:pointer; }
    table { width:100%; border-collapse:collapse; }
    th, td { padding:12px 16px; border-bottom:1px solid var(--border); text-align:left; font-size:13px; }
    thead { background:#f8f9fb; }
    .action-btns { display:flex; gap:6px; }
    .action-btn { width:30px; height:30px; border:1px solid var(--border); background:#fff; border-radius:6px; cursor:pointer; }
    .action-btn.del:hover { border-color:var(--danger); color:var(--danger); }
    .action-btn.edit:hover { border-color:var(--blue); color:var(--blue); }
    .status-select { border:1px solid var(--border); border-radius:6px; padding:6px 8px; }
    .modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.45); z-index:99; align-items:center; justify-content:center; }
    .modal-overlay.open { display:flex; }
    .modal { width:480px; max-width:94vw; background:#fff; border-radius:10px; overflow:hidden; }
    .modal-header { padding:16px; border-bottom:1px solid var(--border); display:flex; justify-content:space-between; align-items:center; }
    .modal-body { padding:16px; }
    .modal-footer { padding:16px; border-top:1px solid var(--border); display:flex; gap:8px; justify-content:flex-end; }
    .btn-secondary { border:1px solid var(--border); background:#fff; padding:8px 12px; border-radius:6px; cursor:pointer; }
    .form-group { margin-bottom:12px; }
    .form-group label { display:block; margin-bottom:5px; font-size:12px; color:var(--navy); font-weight:600; }
    .form-group select { width:100%; border:1px solid var(--border); border-radius:6px; padding:9px 10px; }
    .form-group input[type="datetime-local"] { width:100%; border:1px solid var(--border); border-radius:6px; padding:9px 10px; }
    .alert { margin-bottom:12px; border-radius:8px; padding:10px 12px; font-size:13px; }
    .alert.error { background:#fee2e2; color:#991b1b; border:1px solid #fecaca; }
    .alert.success { background:#dcfce7; color:#166534; border:1px solid #bbf7d0; }
  </style>
</head>
<body>
  <aside class="sidebar">
    <a href="/projetweb_avec_evenements/public/index.php/admin" class="sidebar-logo">
      <i class="fa fa-cogs"></i> Admin
    </a>
    <nav class="sidebar-nav">
      <a href="/projetweb_avec_evenements/public/index.php/admin/events" class="sidebar-link"><i class="fa fa-calendar"></i> Evenements</a>
      <a href="/projetweb_avec_evenements/public/index.php/admin/inscriptions" class="sidebar-link active"><i class="fa fa-clipboard"></i> Inscriptions</a>
    </nav>
  </aside>

  <main class="main">
    <div class="header">Admin / Inscriptions</div>
    <div class="content">
      <div id="alert-root"></div>
      <div class="card">
        <div class="card-header">
          <div>
            <div class="card-title">Gestion des Inscriptions</div>
            <div class="card-subtitle" id="table-count">0 inscriptions trouvees</div>
          </div>
          <button class="btn-primary" onclick="openCreateModal()"><i class="fa fa-plus"></i> Ajouter</button>
        </div>
        <table>
          <thead>
            <tr>
              <th>Utilisateur</th>
              <th>Evenement</th>
              <th>Date Inscription</th>
              <th>Statut</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="inscriptions-tbody">
            <?php foreach ($inscriptions as $inscription): ?>
              <tr data-id="<?php echo (int) $inscription->idInscription; ?>">
                <td>
                  <strong><?php echo htmlspecialchars(trim(($inscription->prenom ?? '') . ' ' . ($inscription->nom ?? ''))); ?></strong><br>
                  <span style="font-size:11px;color:var(--muted)"><?php echo htmlspecialchars($inscription->email ?? ''); ?></span>
                </td>
                <td><?php echo htmlspecialchars($inscription->titre_evenement ?? 'N/A'); ?></td>
                <td><?php echo htmlspecialchars(date('d/m/Y', strtotime($inscription->dateInscription))); ?></td>
                <td>
                  <select class="status-select" onchange="updateStatus(<?php echo (int) $inscription->idInscription; ?>, this.value)">
                    <?php foreach ($statuses as $status): ?>
                      <?php $selected = (Inscription::normalizeStatus($inscription->statut ?? '') === $status) ? 'selected' : ''; ?>
                      <option value="<?php echo htmlspecialchars($status); ?>" <?php echo $selected; ?>><?php echo htmlspecialchars($status); ?></option>
                    <?php endforeach; ?>
                  </select>
                </td>
                <td>
                  <div class="action-btns">
                    <button class="action-btn edit" onclick="openEditModal(<?php echo (int) $inscription->idInscription; ?>)" title="Modifier"><i class="fa fa-edit"></i></button>
                    <button class="action-btn del" onclick="deleteInscription(<?php echo (int) $inscription->idInscription; ?>)" title="Supprimer"><i class="fa fa-trash"></i></button>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>

  <div class="modal-overlay" id="inscription-modal">
    <div class="modal">
      <div class="modal-header">
        <h3 id="modal-title">Ajouter une inscription</h3>
        <button class="action-btn" onclick="closeModal()"><i class="fa fa-times"></i></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="inscription-id">
        <div class="form-group">
          <label>Utilisateur</label>
          <select id="idUtilisateur">
            <option value="">Selectionner un utilisateur</option>
            <?php foreach ($users as $user): ?>
              <option value="<?php echo (int) $user->getId(); ?>">
                <?php echo htmlspecialchars($user->getPrenom() . ' ' . $user->getNom() . ' (' . $user->getEmail() . ')'); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label>Evenement</label>
          <select id="idEvenement">
            <option value="">Selectionner un evenement</option>
            <?php foreach ($events as $event): ?>
              <option value="<?php echo (int) $event->getId(); ?>"><?php echo htmlspecialchars($event->getTitre()); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label>Date Inscription</label>
          <input type="datetime-local" id="dateInscription">
        </div>
        <div class="form-group">
          <label>Statut</label>
          <select id="statut">
            <?php foreach ($statuses as $status): ?>
              <option value="<?php echo htmlspecialchars($status); ?>"><?php echo htmlspecialchars($status); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn-secondary" onclick="closeModal()">Annuler</button>
        <button class="btn-primary" onclick="submitInscription()">Enregistrer</button>
      </div>
    </div>
  </div>

  <script>
    const statuses = <?php echo json_encode(array_values($statuses)); ?>;
    const usersData = <?php
      $u = [];
      foreach ($users as $user) {
        $u[] = [
          'id' => $user->getId(),
          'name' => $user->getPrenom() . ' ' . $user->getNom(),
          'email' => $user->getEmail()
        ];
      }
      echo json_encode($u);
    ?>;
    const eventsData = <?php
      $e = [];
      foreach ($events as $event) {
        $e[] = ['id' => $event->getId(), 'title' => $event->getTitre()];
      }
      echo json_encode($e);
    ?>;
    const inscriptionsData = <?php echo json_encode($inscriptions); ?>;

    function getBaseIndexPath() {
      const path = window.location.pathname;
      const indexPos = path.indexOf('/index.php');
      return indexPos !== -1 ? path.slice(0, indexPos + '/index.php'.length) : '/index.php';
    }

    function showAlert(message, type = 'success') {
      const root = document.getElementById('alert-root');
      root.innerHTML = `<div class="alert ${type}">${message}</div>`;
      setTimeout(() => { root.innerHTML = ''; }, 2500);
    }

    function updateTableCount() {
      const count = document.querySelectorAll('#inscriptions-tbody tr').length;
      document.getElementById('table-count').textContent = `${count} inscription${count > 1 ? 's' : ''} trouvee${count > 1 ? 's' : ''}`;
    }

    function openCreateModal() {
      document.getElementById('modal-title').textContent = 'Ajouter une inscription';
      document.getElementById('inscription-id').value = '';
      document.getElementById('idUtilisateur').value = '';
      document.getElementById('idEvenement').value = '';
      document.getElementById('dateInscription').value = new Date().toISOString().slice(0, 16);
      document.getElementById('statut').value = statuses[0] || 'Confirmee';
      document.getElementById('inscription-modal').classList.add('open');
    }

    function normalizeStatusValue(value) {
      const s = String(value || '').toLowerCase();
      if (s.includes('present')) return 'Present';
      if (s.includes('absent')) return 'Absent';
      if (s.includes('annul')) return 'Annulee';
      return 'Confirmee';
    }

    function openEditModal(id) {
      const item = inscriptionsData.find(i => Number(i.idInscription) === Number(id));
      if (!item) {
        showAlert('Inscription introuvable', 'error');
        return;
      }
      document.getElementById('modal-title').textContent = 'Modifier une inscription';
      document.getElementById('inscription-id').value = item.idInscription;
      document.getElementById('idUtilisateur').value = item.idUtilisateur;
      document.getElementById('idEvenement').value = item.idEvenement;
      // Convert date format from 'YYYY-MM-DD HH:MM:SS' to 'YYYY-MM-DDTHH:MM'
      const dateStr = (item.dateInscription || '').replace(' ', 'T').substring(0, 16);
      document.getElementById('dateInscription').value = dateStr;
      document.getElementById('statut').value = normalizeStatusValue(item.statut);
      document.getElementById('inscription-modal').classList.add('open');
    }

    function closeModal() {
      document.getElementById('inscription-modal').classList.remove('open');
    }

    function validateInscriptionForm() {
      const idUtilisateur = document.getElementById('idUtilisateur').value.trim();
      const idEvenement = document.getElementById('idEvenement').value.trim();
      const dateInscription = document.getElementById('dateInscription').value.trim();
      const statut = document.getElementById('statut').value.trim();
      
      // Vérifier utilisateur
      if (!idUtilisateur) {
        showAlert('❌ Veuillez sélectionner un utilisateur', 'error');
        return false;
      }
      
      // Vérifier événement
      if (!idEvenement) {
        showAlert('❌ Veuillez sélectionner un événement', 'error');
        return false;
      }
      
      // Vérifier date
      if (!dateInscription) {
        showAlert('❌ Veuillez entrer une date d\'inscription', 'error');
        return false;
      }
      
      // Vérifier format et validité de la date
      const dateRegex = /^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/;
      if (!dateRegex.test(dateInscription)) {
        showAlert('❌ Format de date invalide', 'error');
        return false;
      }
      
      // Vérifier que la date est valide
      const dateObj = new Date(dateInscription);
      if (isNaN(dateObj.getTime())) {
        showAlert('❌ La date n\'est pas valide', 'error');
        return false;
      }
      
      // Vérifier statut
      if (!statut) {
        showAlert('❌ Veuillez sélectionner un statut', 'error');
        return false;
      }
      
      // Vérifier que le statut est valide
      const validStatuses = ['Confirmee', 'Present', 'Absent', 'Annulee'];
      if (!validStatuses.includes(statut)) {
        showAlert('❌ Statut invalide', 'error');
        return false;
      }
      
      return true;
    }

    function submitInscription() {
      // Valider le formulaire
      if (!validateInscriptionForm()) {
        return;
      }
      
      const id = document.getElementById('inscription-id').value;
      const dateValue = document.getElementById('dateInscription').value;
      // Convert from 'YYYY-MM-DDTHH:MM' to 'YYYY-MM-DD HH:MM:SS'
      const dateInscription = dateValue ? dateValue.replace('T', ' ') + ':00' : new Date().toISOString().slice(0, 19).replace('T', ' ');
      const payload = {
        id: id || undefined,
        idUtilisateur: Number(document.getElementById('idUtilisateur').value),
        idEvenement: Number(document.getElementById('idEvenement').value),
        dateInscription: dateInscription,
        statut: document.getElementById('statut').value
      };

      const endpoint = id ? '/inscriptions/update' : '/inscriptions/create';
      fetch(getBaseIndexPath() + endpoint, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      })
      .then(r => r.json())
      .then(data => {
        if (!data.success) {
          showAlert(data.error || 'Operation echouee', 'error');
          return;
        }
        closeModal();
        showAlert('✅ ' + (data.message || 'Succes'));
        window.location.reload();
      })
      .catch(() => showAlert('❌ Erreur reseau', 'error'));
    }

    function updateStatus(id, status) {
      fetch(getBaseIndexPath() + '/inscriptions/updateStatus', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id, statut: status })
      })
      .then(r => r.json())
      .then(data => {
        if (!data.success) {
          showAlert(data.error || 'Erreur de mise a jour', 'error');
          window.location.reload();
          return;
        }
        showAlert('Statut mis a jour');
      })
      .catch(() => showAlert('Erreur reseau', 'error'));
    }

    function deleteInscription(id) {
      if (!confirm('Supprimer cette inscription ?')) {
        return;
      }
      fetch(getBaseIndexPath() + '/inscriptions/delete?id=' + id, {
        method: 'DELETE'
      })
      .then(r => r.json())
      .then(data => {
        if (!data.success) {
          showAlert(data.error || 'Suppression impossible', 'error');
          return;
        }
        const row = document.querySelector(`tr[data-id="${id}"]`);
        if (row) row.remove();
        updateTableCount();
        showAlert('Inscription supprimee');
      })
      .catch(() => showAlert('Erreur reseau', 'error'));
    }

    updateTableCount();
  </script>
</body>
</html>
