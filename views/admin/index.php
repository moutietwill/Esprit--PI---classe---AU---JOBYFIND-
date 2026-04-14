<?php
if (realpath(__FILE__) === realpath($_SERVER['SCRIPT_FILENAME'])) {
    $requestUri = $_SERVER['REQUEST_URI'];
    $redirectUrl = preg_replace('@/views/admin(?:/.*)?$@', '/projetweb_avec_evenements/public/index.php/admin', $requestUri);
    header('Location: ' . $redirectUrl);
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Dashboard</title>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    :root {
      --blue: #2d79ff; --navy: #0b1f4b; --sidebar-w: 240px; --header-h: 60px;
      --bg: #f0f2f8; --surface: #ffffff; --border: #e2e8f0; --text: #374151;
      --muted: #9ca3af; --danger: #ef4444; --success: #22c55e; --warning: #f59e0b; --radius: 10px;
    }
    body { font-family: "DM Sans", sans-serif; background: var(--bg); color: var(--text); display: flex; min-height: 100vh; font-size: 14px; }
    .sidebar { width: var(--sidebar-w); background: var(--navy); min-height: 100vh; position: fixed; top: 0; left: 0; display: flex; flex-direction: column; z-index: 100; }
    .sidebar-logo { display: flex; align-items: center; gap: 10px; padding: 20px; border-bottom: 1px solid rgba(255,255,255,.08); }
    .sidebar-logo-icon { width: 34px; height: 34px; background: var(--blue); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 14px; font-weight: 600; }
    .sidebar-logo-text { font-family: "DM Serif Display", serif; color: #fff; font-size: 17px; }
    .sidebar-section { padding: 18px 12px; }
    .sidebar-link { display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: 8px; text-decoration: none; color: rgba(255,255,255,.55); cursor: pointer; transition: all .15s; border: none; background: transparent; width: 100%; text-align: left; font-size: 13px; }
    .sidebar-link:hover { background: rgba(255,255,255,.07); color: rgba(255,255,255,.85); }
    .sidebar-link.active { background: var(--blue); color: #fff; }
    .main { margin-left: var(--sidebar-w); flex: 1; display: flex; flex-direction: column; }
    .header { height: var(--header-h); background: var(--surface); border-bottom: 1px solid var(--border); display: flex; align-items: center; padding: 0 28px; gap: 16px; }
    .header-breadcrumb { display: flex; align-items: center; gap: 8px; font-size: 13px; color: var(--muted); }
    .header-breadcrumb .current { color: var(--navy); font-weight: 600; }
    .content { padding: 28px; flex: 1; }
    .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px; }
    .stat-card { background: var(--surface); border-radius: var(--radius); border: 1px solid var(--border); padding: 20px; }
    .stat-value { font-size: 28px; font-weight: 600; color: var(--navy); }
    .stat-label { font-size: 12px; color: var(--muted); margin-top: 8px; text-transform: uppercase; letter-spacing: .05em; }
    .table-card { background: var(--surface); border-radius: var(--radius); border: 1px solid var(--border); overflow: hidden; }
    .table-header { display: flex; justify-content: space-between; align-items: center; padding: 20px; border-bottom: 1px solid var(--border); }
    .btn-primary { background: var(--blue); color: #fff; border: none; padding: 10px 16px; border-radius: 8px; cursor: pointer; font-weight: 500; font-size: 13px; }
    .btn-primary:hover { background: #1e5ec8; }
    table { width: 100%; border-collapse: collapse; }
    th { background: var(--bg); padding: 12px 20px; text-align: left; font-size: 12px; font-weight: 600; color: var(--muted); text-transform: uppercase; letter-spacing: .05em; }
    td { padding: 14px 20px; border-top: 1px solid var(--border); }
    .action-btns { display: flex; gap: 8px; }
    .action-btn { width: 32px; height: 32px; border-radius: 6px; border: 1px solid var(--border); background: transparent; cursor: pointer; display: flex; align-items: center; justify-content: center; color: var(--muted); font-size: 12px; transition: all .15s; }
    .action-btn:hover { border-color: var(--blue); color: var(--blue); }
    .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,.5); z-index: 1000; align-items: center; justify-content: center; }
    .modal-overlay.open { display: flex; }
    .modal { background: var(--surface); border-radius: var(--radius); width: 90%; max-width: 600px; max-height: 90vh; overflow-y: auto; }
    .modal-header { display: flex; justify-content: space-between; align-items: center; padding: 20px; border-bottom: 1px solid var(--border); }
    .modal-title { font-size: 16px; font-weight: 600; color: var(--navy); }
    .modal-close { background: none; border: none; cursor: pointer; font-size: 18px; color: var(--muted); }
    .modal-body { padding: 20px; }
    .form-group { margin-bottom: 16px; }
    .form-label { display: block; font-size: 13px; font-weight: 500; color: var(--navy); margin-bottom: 6px; }
    .form-input { width: 100%; padding: 10px 12px; border: 1px solid var(--border); border-radius: 6px; font-family: "DM Sans", sans-serif; font-size: 13px; color: var(--text); outline: none; }
    .form-input:focus { border-color: var(--blue); box-shadow: 0 0 0 3px rgba(45,121,255,.1); }
    .modal-footer { display: flex; gap: 10px; justify-content: flex-end; padding: 20px; border-top: 1px solid var(--border); }
    .btn-cancel { background: var(--bg); color: var(--text); border: 1px solid var(--border); padding: 10px 16px; border-radius: 6px; cursor: pointer; font-weight: 500; }
    .toast-container { position: fixed; bottom: 20px; right: 20px; z-index: 2000; }
    .toast { background: var(--surface); border-left: 4px solid var(--success); padding: 14px 16px; border-radius: 6px; margin-bottom: 8px; display: flex; align-items: center; gap: 10px; box-shadow: 0 2px 8px rgba(0,0,0,.1); }
    .toast.error { border-left-color: var(--danger); }
  </style>
</head>
<body>
  <aside class="sidebar">
    <div class="sidebar-logo">
      <div class="sidebar-logo-icon">JF</div>
      <div class="sidebar-logo-text">Jobyfind</div>
    </div>
    <div class="sidebar-section">
      <button class="sidebar-link active" onclick="showPage('users', this)"><i class="fa fa-users"></i> Utilisateurs</button>
      <button class="sidebar-link" onclick="showPage('events', this)"><i class="fa fa-calendar"></i> Événements</button>
    </div>
  </aside>

  <div class="main">
    <header class="header">
      <div class="header-breadcrumb">
        <span>Admin</span> <span>›</span> <span class="current" id="page-title">Utilisateurs</span>
      </div>
    </header>

    <div class="content">
      <div id="users-content">
        <div class="stats-grid">
          <div class="stat-card"><div class="stat-value" id="user-total">0</div><div class="stat-label">Total Utilisateurs</div></div>
          <div class="stat-card"><div class="stat-value" id="event-total">0</div><div class="stat-label">Total Événements</div></div>
        </div>
        <p style="text-align: center; color: var(--muted); margin-top: 40px;">Page utilisateurs en développement</p>
      </div>

      <div id="events-content" style="display:none;">
        <div class="stats-grid">
          <div class="stat-card"><div class="stat-value" id="event-total-stats">0</div><div class="stat-label">Total Événements</div></div>
          <div class="stat-card"><div class="stat-value" id="event-upcoming">0</div><div class="stat-label">À Venir</div></div>
        </div>
        <div class="table-card">
          <div class="table-header">
            <div><div style="font-size: 16px; font-weight: 600; color: var(--navy);">Événements</div><div style="font-size: 12px; color: var(--muted); margin-top: 4px;" id="event-count">0 événements</div></div>
            <button class="btn-primary" onclick="openAddEventModal()"><i class="fa fa-plus"></i> Ajouter</button>
          </div>
          <table>
            <thead><tr><th>Titre</th><th>Date</th><th>Lieu</th><th>Organisateur ID</th><th>Actions</th></tr></thead>
            <tbody id="event-tbody"></tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <div class="modal-overlay" id="event-modal">
    <div class="modal">
      <div class="modal-header"><div class="modal-title" id="event-modal-title">Ajouter un événement</div><button class="modal-close" onclick="closeEventModal()"><i class="fa fa-times"></i></button></div>
      <form id="event-form">
        <div class="modal-body">
          <div class="form-group"><label class="form-label">Titre *</label><input type="text" name="titre" class="form-input"></div>
          <div class="form-group"><label class="form-label">Description</label><textarea name="description" class="form-input" rows="4" placeholder="Description de l'événement"></textarea></div>
          <div class="form-group"><label class="form-label">Date *</label><input type="text" name="date" class="form-input"></div>
          <div class="form-group"><label class="form-label">Lieu *</label><input type="text" name="lieu" class="form-input"></div>
          <div class="form-group"><label class="form-label">Organisateur ID *</label><input type="text" name="idOrganisateur" class="form-input" placeholder="ID utilisateur"></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn-cancel" onclick="closeEventModal()">Annuler</button><button type="submit" class="btn-primary">Enregistrer</button></div>
      </form>
    </div>
  </div>

  <div class="toast-container" id="toast-container"></div>

  <script>
    let eventsData = <?php echo json_encode(array_map(function($e) { return method_exists($e, 'toArray') ? $e->toArray() : (array)$e; }, is_array($events) ? $events : [])); ?>;
    let editingEventId = null;
    function showToast(msg, type = 'success') {
      const toast = document.createElement('div');
      toast.className = 'toast ' + type;
      toast.innerHTML = `<i class="fa fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i> ${msg}`;
      document.getElementById('toast-container').appendChild(toast);
      setTimeout(() => toast.remove(), 3000);
    }
    function getAdminBasePath() {
      const path = window.location.pathname;
      const indexPos = path.indexOf('/index.php/admin');
      if (indexPos !== -1) return path.slice(0, indexPos + '/index.php'.length) + '/admin';
      return '/index.php/admin';
    }
    function updateEventStats() {
      document.getElementById('event-total').textContent = eventsData.length;
      document.getElementById('event-total-stats').textContent = eventsData.length;
      document.getElementById('event-upcoming').textContent = eventsData.filter(e => new Date(e.date) > new Date()).length;
      document.getElementById('user-total').textContent = '0';
    }
    function renderEventTable() {
      const tbody = document.getElementById('event-tbody');
      tbody.innerHTML = eventsData.map(e => `<tr><td style="font-weight: 500">${e.titre}</td><td>${new Date(e.date).toLocaleDateString('fr-FR')}</td><td>${e.lieu}</td><td>${e.idOrganisateur}</td><td><div class="action-btns"><button class="action-btn" title="Modifier" onclick="openEditEventModal(${e.idEvenement || e.id})"><i class="fa fa-pen"></i></button><button class="action-btn" title="Supprimer" onclick="deleteEvent(${e.idEvenement || e.id})"><i class="fa fa-trash"></i></button></div></td></tr>`).join('');
      document.getElementById('event-count').textContent = eventsData.length + ' événement' + (eventsData.length !== 1 ? 's' : '');
    }
    function openAddEventModal() { editingEventId = null; document.getElementById('event-modal-title').textContent = 'Ajouter un événement'; document.getElementById('event-form').reset(); document.getElementById('event-modal').classList.add('open'); }
    function openEditEventModal(id) { editingEventId = id; const event = eventsData.find(e => (e.idEvenement || e.id) === id); if (!event) return; document.getElementById('event-modal-title').textContent = 'Modifier l\'événement'; document.querySelector('input[name="titre"]').value = event.titre; document.querySelector('textarea[name="description"]').value = event.description || ''; document.querySelector('input[name="date"]').value = event.date; document.querySelector('input[name="lieu"]').value = event.lieu; document.querySelector('input[name="idOrganisateur"]').value = event.idOrganisateur; document.getElementById('event-modal').classList.add('open'); }
    function closeEventModal() { document.getElementById('event-modal').classList.remove('open'); editingEventId = null; }
    function deleteEvent(id) { if (confirm('Voulez-vous vraiment supprimer cet événement ?')) { fetch(`${getAdminBasePath()}/deleteEvent/${id}`, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' } }).then(r => r.json()).then(data => { if (data.success) { eventsData = eventsData.filter(e => (e.idEvenement || e.id) !== id); renderEventTable(); updateEventStats(); showToast('Événement supprimé', 'success'); } else { showToast(data.error || 'Erreur', 'error'); } }); } }
    document.getElementById('event-form').addEventListener('submit', async (e) => { 
        e.preventDefault(); 
        const desc = document.querySelector('textarea[name="description"]').value.trim();
        if(desc.length < 10) {
            showToast("La description est obligatoire et doit comporter au moins 10 caractères.", 'error');
            return;
        }

        const fd = new FormData(e.target); 
        const url = editingEventId ? `${getAdminBasePath()}/updateEvent/${editingEventId}` : `${getAdminBasePath()}/storeEvent`; 
        try { 
            const resp = await fetch(url, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: new URLSearchParams(fd) }); 
            const data = await resp.json(); 
            if (data.success) { 
                if (editingEventId) { 
                    const idx = eventsData.findIndex(e => (e.idEvenement || e.id) === editingEventId); 
                    if (idx !== -1) { eventsData[idx] = { ...eventsData[idx], ...Object.fromEntries(fd) }; } 
                } else { 
                    eventsData.unshift(data.event || Object.fromEntries(fd)); 
                } 
                closeEventModal(); renderEventTable(); updateEventStats(); showToast(editingEventId ? 'Événement modifié' : 'Événement ajouté', 'success'); 
            } else { showToast(data.error || 'Erreur', 'error'); } 
        } catch (err) { showToast('Erreur: ' + err.message, 'error'); } 
    });
    function showPage(page) { if (page === 'events') { document.getElementById('users-content').style.display = 'none'; document.getElementById('events-content').style.display = 'block'; document.getElementById('page-title').textContent = 'Événements'; renderEventTable(); updateEventStats(); } else { document.getElementById('users-content').style.display = 'block'; document.getElementById('events-content').style.display = 'none'; document.getElementById('page-title').textContent = 'Utilisateurs'; } }
    updateEventStats();
    renderEventTable();

    // Activer Flatpickr
    flatpickr("input[name='date']", {
      dateFormat: "Y-m-d",
      allowInput: true
    });
  </script>
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
</body>
</html>
