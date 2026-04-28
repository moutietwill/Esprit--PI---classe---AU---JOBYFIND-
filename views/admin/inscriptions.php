<?php
if (realpath(__FILE__) === realpath($_SERVER['SCRIPT_FILENAME'])) {
    $requestUri = $_SERVER['REQUEST_URI'];
    $redirectUrl = preg_replace('@/views/admin(?:/.*)?$@', '/projetweb_avec_evenements/public/index.php/admin/inscriptions', $requestUri);
    header('Location: ' . $redirectUrl);
    exit;
}

$scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
$baseDir = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
if ($baseDir === '.' || $baseDir === '/') {
    $baseDir = '';
}
$indexBase = $baseDir . '/index.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Jobyfind - Gestion Inscriptions</title>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Serif+Display&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --blue: #2d79ff;
      --navy: #0b1f4b;
      --sidebar-w: 240px;
      --header-h: 60px;
      --bg: #f0f2f8;
      --surface: #ffffff;
      --border: #e2e8f0;
      --text: #374151;
      --muted: #9ca3af;
      --danger: #ef4444;
      --success: #22c55e;
      --warning: #f59e0b;
      --radius: 10px;
    }

    body {
      font-family: "DM Sans", sans-serif;
      background: var(--bg);
      color: var(--text);
      display: flex;
      min-height: 100vh;
      font-size: 14px;
    }

    .sidebar {
      width: var(--sidebar-w);
      background: var(--navy);
      min-height: 100vh;
      position: fixed;
      top: 0; left: 0;
      display: flex;
      flex-direction: column;
      z-index: 100;
    }

    .sidebar-logo {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 20px 20px 16px;
      border-bottom: 1px solid rgba(255,255,255,.08);
    }
    .sidebar-logo .logo-icon {
      width: 34px; height: 34px;
      background: var(--blue);
      border-radius: 8px;
      display: flex; align-items: center; justify-content: center;
      color: #fff; font-size: 14px; font-weight: 600;
    }
    .sidebar-logo .logo-text {
      font-family: "DM Serif Display", serif;
      color: #fff;
      font-size: 17px;
    }
    .sidebar-logo .logo-text span { color: #7aabff; }

    .sidebar-section {
      padding: 18px 12px 6px;
    }
    .sidebar-section-label {
      font-size: 10px;
      font-weight: 600;
      letter-spacing: .1em;
      text-transform: uppercase;
      color: rgba(255,255,255,.3);
      padding: 0 8px;
      margin-bottom: 6px;
    }

    .sidebar-link {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 9px 10px;
      border-radius: 8px;
      text-decoration: none;
      color: rgba(255,255,255,.55);
      cursor: pointer;
      transition: all .15s;
      font-size: 13px;
    }
    .sidebar-link:hover { background: rgba(255,255,255,.08); color: rgba(255,255,255,.75); }
    .sidebar-link.active { background: var(--blue); color: #fff; }
    .sidebar-link i { font-size: 13px; }

    .sidebar-footer {
      margin-top: auto;
      padding: 14px;
      border-top: 1px solid rgba(255,255,255,.08);
    }
    .admin-profile {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 8px 10px;
      border-radius: 8px;
      background: rgba(255,255,255,.08);
    }
    .admin-avatar {
      width: 32px; height: 32px;
      border-radius: 50%;
      background: var(--blue);
      display: flex; align-items: center; justify-content: center;
      color: #fff; font-size: 12px; font-weight: 600;
      flex-shrink: 0;
    }
    .admin-info {
      flex: 1; min-width: 0;
    }
    .admin-info p {
      font-size: 12px;
      font-weight: 500;
      color: #fff;
      white-space: nowrap;
      text-overflow: ellipsis;
      overflow: hidden;
    }
    .admin-info span {
      font-size: 10px;
      color: rgba(255,255,255,.5);
    }

    .main {
      flex: 1;
      margin-left: var(--sidebar-w);
      display: flex;
      flex-direction: column;
    }

    .header {
      height: var(--header-h);
      background: var(--surface);
      border-bottom: 1px solid var(--border);
      display: flex;
      align-items: center;
      padding: 0 28px;
      justify-content: space-between;
      gap: 20px;
    }
    .header-breadcrumb {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 12px;
      color: var(--muted);
    }
    .header-breadcrumb .current { color: var(--navy); font-weight: 600; }
    .header-breadcrumb a { color: var(--muted); cursor: pointer; text-decoration: none; }
    .header-breadcrumb a:hover { color: var(--blue); }

    .header-search {
      flex: 1;
      max-width: 250px;
      position: relative;
    }
    .header-search i {
      position: absolute;
      left: 11px; top: 50%;
      transform: translateY(-50%);
      color: var(--muted); font-size: 12px;
    }
    .header-search input {
      width: 100%;
      padding: 7px 12px 7px 32px;
      border: 1px solid var(--border);
      border-radius: 7px;
      font-family: "DM Sans", sans-serif;
      font-size: 12px;
      color: var(--text);
      outline: none;
    }
    .header-search input:focus { border-color: var(--blue); }

    .content { padding: 28px; }

    .table-card {
      background: var(--surface);
      border-radius: var(--radius);
      border: 1px solid var(--border);
      overflow: hidden;
    }

    .table-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 18px 22px;
      border-bottom: 1px solid var(--border);
    }
    .table-title { font-size: 15px; font-weight: 600; color: var(--navy); }
    .table-subtitle { font-size: 12px; color: var(--muted); margin-top: 1px; }

    .table-controls { display: flex; gap: 8px; align-items: center; }

    .btn-primary {
      padding: 7px 14px;
      background: var(--blue);
      color: #fff;
      border: none;
      border-radius: 7px;
      font-family: "DM Sans", sans-serif;
      font-size: 12px;
      font-weight: 600;
      cursor: pointer;
      display: flex; align-items: center; gap: 6px;
      transition: opacity .15s;
    }
    .btn-primary:hover { opacity: .88; }

    .btn-back {
      padding: 7px 14px;
      background: transparent;
      color: var(--blue);
      border: 1.5px solid var(--blue);
      border-radius: 7px;
      font-family: "DM Sans", sans-serif;
      font-size: 12px;
      font-weight: 600;
      cursor: pointer;
      display: flex; align-items: center; gap: 6px;
      text-decoration: none;
    }
    .btn-back:hover { background: #dbeafe; }

    table { width: 100%; border-collapse: collapse; }
    thead th {
      padding: 11px 22px;
      text-align: left;
      font-size: 11px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: .07em;
      color: var(--muted);
      border-bottom: 1px solid var(--border);
      background: #fafbfd;
    }
    tbody tr {
      border-bottom: 1px solid var(--border);
      transition: background .12s;
    }
    tbody tr:last-child { border-bottom: none; }
    tbody tr:hover { background: #f8faff; }
    tbody td { padding: 13px 22px; vertical-align: middle; }

    .badge {
      display: inline-flex;
      align-items: center;
      gap: 4px;
      padding: 3px 9px;
      border-radius: 99px;
      font-size: 11px;
      font-weight: 600;
    }
    .badge-green { background: #dcfce7; color: #15803d; }
    .badge-amber { background: #fef3c7; color: #92400e; }
    .badge-gray { background: #f1f5f9; color: #64748b; }

    .action-btns { display: flex; gap: 6px; }
    .action-btn {
      width: 28px; height: 28px;
      border-radius: 6px;
      border: 1.5px solid var(--border);
      background: var(--surface);
      display: flex; align-items: center; justify-content: center;
      color: var(--muted);
      cursor: pointer;
      font-size: 11px;
      transition: all .12s;
    }
    .action-btn:hover.edit { border-color: var(--blue); color: var(--blue); background: #dbeafe; }
    .action-btn:hover.del { border-color: #ef4444; color: #ef4444; background: #fee2e2; }

    .modal-overlay {
      display: none;
      position: fixed; inset: 0;
      background: rgba(11,31,75,.5);
      z-index: 200;
      align-items: center;
      justify-content: center;
    }
    .modal-overlay.open { display: flex; }
    .modal {
      background: var(--surface);
      border-radius: 14px;
      width: 500px;
      max-width: 95vw;
      max-height: 90vh;
      overflow-y: auto;
    }
    .modal-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 20px 24px 16px;
      border-bottom: 1px solid var(--border);
    }
    .modal-title { font-size: 16px; font-weight: 600; color: var(--navy); }
    .modal-close {
      background: none; border: none;
      color: var(--muted); cursor: pointer;
      font-size: 14px; padding: 4px;
      transition: color .12s;
    }
    .modal-close:hover { color: #ef4444; }
    .modal-body { padding: 20px 24px; }
    .modal-footer {
      padding: 16px 24px;
      border-top: 1px solid var(--border);
      display: flex; gap: 10px; justify-content: flex-end;
    }

    .form-group { margin-bottom: 16px; }
    .form-label { display: block; font-size: 12px; font-weight: 600; color: var(--navy); margin-bottom: 6px; }
    .form-input {
      width: 100%;
      padding: 9px 12px;
      border: 1.5px solid var(--border);
      border-radius: 7px;
      font-family: "DM Sans", sans-serif;
      font-size: 13px;
      color: var(--text);
      background: var(--surface);
      outline: none;
      transition: border-color .2s;
    }
    .form-input:focus { border-color: var(--blue); }

    .form-input.success {
      border-color: var(--success) !important;
      background-color: rgba(34, 197, 94, 0.05);
    }
    .form-group.has-error .form-input {
      border-color: #ef4444;
      background-color: rgba(239, 68, 68, 0.05);
    }
    .form-error {
      color: #ef4444;
      font-size: 12px;
      margin-top: 4px;
      display: none;
    }
    .form-error.show {
      display: block;
    }

    .btn-danger {
      padding: 8px 16px;
      background: #ef4444;
      color: #fff;
      border: none;
      border-radius: 7px;
      font-family: "DM Sans", sans-serif;
      font-size: 13px;
      font-weight: 600;
      cursor: pointer;
      transition: opacity .15s;
    }
    .btn-danger:hover { opacity: .88; }
    .btn-cancel {
      padding: 8px 16px;
      background: transparent;
      color: var(--text);
      border: 1.5px solid var(--border);
      border-radius: 7px;
      font-family: "DM Sans", sans-serif;
      font-size: 13px;
      font-weight: 500;
      cursor: pointer;
      transition: border-color .15s;
    }
    .btn-cancel:hover { border-color: var(--muted); }

    .toast-container {
      position: fixed;
      bottom: 24px; right: 24px;
      z-index: 999;
      display: flex;
      flex-direction: column;
      gap: 8px;
    }
    .toast {
      background: var(--navy);
      color: #fff;
      padding: 12px 18px;
      border-radius: 9px;
      font-size: 13px;
      font-weight: 500;
      display: flex;
      align-items: center;
      gap: 10px;
      animation: slideIn .25s ease;
      box-shadow: 0 8px 24px rgba(0,0,0,.15);
    }
    .toast i { font-size: 14px; }
    .toast.success i { color: var(--success); }
    .toast.error i { color: #ef4444; }
    @keyframes slideIn {
      from { transform: translateX(40px); opacity: 0; }
      to { transform: translateX(0); opacity: 1; }
    }

    .empty-state {
      text-align: center;
      padding: 60px 20px;
      color: var(--muted);
    }
    .empty-state i {
      font-size: 48px;
      margin-bottom: 16px;
      opacity: 0.5;
    }
    .empty-state p {
      font-size: 14px;
    }
  </style>
</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar">
  <div class="sidebar-logo">
    <div class="logo-icon">JF</div>
    <div class="logo-text">Joby<span>find</span></div>
  </div>
  <div class="sidebar-section">
    <p class="sidebar-section-label">Tableau de bord</p>
    <a class="sidebar-link" href="<?php echo htmlspecialchars($indexBase . '/admin/events'); ?>">
      <i class="fa-solid fa-calendar-days"></i>
      <span>Evenement</span>
    </a>
    <a class="sidebar-link active" href="<?php echo htmlspecialchars($indexBase . '/admin/inscriptions'); ?>">
      <i class="fa-solid fa-clipboard-list"></i>
      <span>Inscriptions</span>
    </a>
  </div>
  <div class="sidebar-footer">
    <div class="admin-profile">
      <div class="admin-avatar">SA</div>
      <div class="admin-info">
        <p>Super Admin</p>
        <span>admin@jobyfind.tn</span>
      </div>
    </div>
  </div>
</aside>

<!-- MAIN -->
<div class="main">

  <!-- HEADER -->
  <header class="header">
    <div class="header-breadcrumb">
      <a href="<?php echo htmlspecialchars($indexBase . '/admin/events'); ?>">Admin</a>
      <span>â€º</span>
      <span class="current">Inscriptions <?php echo isset($event) ? '(' . htmlspecialchars($event->getTitre()) . ')' : ''; ?></span>
    </div>
    <div class="header-search">
      <i class="fa fa-magnifying-glass"></i>
      <input type="text" id="search-input" placeholder="Rechercher une inscription..." onkeyup="filterInscriptionTable()">
    </div>
  </header>

  <!-- CONTENT -->
  <div class="content">

    <!-- TABLE -->
    <div class="table-card">
      <div class="table-header">
        <div>
          <div class="table-title">Gestion des Inscriptions</div>
          <div class="table-subtitle" id="table-count">0 inscriptions trouvées</div>
        </div>
        <div class="table-controls">
          <a class="btn-back" href="<?php echo htmlspecialchars($indexBase . '/admin/events'); ?>">
            <i class="fa fa-arrow-left"></i> Retour
          </a>
        </div>
      </div>

      <table id="inscriptions-table">
        <thead>
          <tr>
            <th>Événement</th>
            <th>Nom</th>
            <th>Prénom</th>
            <th>Email</th>
            <th>Date</th>
            <th>Statut</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="inscriptions-tbody">
          <?php if (isset($inscriptions) && is_array($inscriptions)): ?>
            <?php if (count($inscriptions) > 0): ?>
              <?php foreach ($inscriptions as $insc): ?>
          <tr class="inscription-row" data-id="<?php echo $insc->getId(); ?>">
            <td>
              <?php
                $eventLabel = trim((string) $insc->getTitreEvenement());
                if ($eventLabel === '') {
                    $eventLabel = 'ID #' . (string) $insc->getIdEvenement();
                }
              ?>
              <strong><?php echo htmlspecialchars($eventLabel); ?></strong>
            </td>
            <td><strong><?php echo htmlspecialchars($insc->getNom()); ?></strong></td>
            <td><?php echo htmlspecialchars($insc->getPrenom()); ?></td>
            <td><?php echo htmlspecialchars($insc->getEmail()); ?></td>
            <td><?php echo date('d/m/Y', strtotime($insc->getDateInscription())); ?></td>
            <td>
              <span class="badge badge-green">
                <i class="fa fa-check"></i> <?php echo htmlspecialchars($insc->getStatut()); ?>
              </span>
            </td>
            <td>
              <div class="action-btns">
                <button class="action-btn edit" title="Modifier" onclick="openEditModal(<?php echo $insc->getId(); ?>)">
                  <i class="fa fa-edit"></i>
                </button>
                <button class="action-btn del" title="Supprimer" onclick="deleteInscription(<?php echo $insc->getId(); ?>)">
                  <i class="fa fa-trash"></i>
                </button>
              </div>
            </td>
          </tr>
              <?php endforeach; ?>
            <?php else: ?>
          <tr>
            <td colspan="7">
              <div class="empty-state">
                <i class="fa fa-inbox"></i>
                <p>Aucune inscription trouvÃ©e</p>
              </div>
            </td>
          </tr>
            <?php endif; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- MODALS -->

<!-- Edit Inscription Modal -->
<div class="modal-overlay" id="inscription-modal">
  <div class="modal">
    <div class="modal-header">
      <div class="modal-title">Modifier l'inscription</div>
      <button class="modal-close" onclick="closeModal()">
        <i class="fa fa-times"></i>
      </button>
    </div>
    <form id="inscription-form" method="POST" action="">
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label">Nom *</label>
          <input type="text" name="nom" class="form-input" required>
          <div class="form-error" data-field="nom"></div>
        </div>

        <div class="form-group">
          <label class="form-label">PrÃ©nom *</label>
          <input type="text" name="prenom" class="form-input" required>
          <div class="form-error" data-field="prenom"></div>
        </div>

        <div class="form-group">
          <label class="form-label">Email *</label>
          <input type="email" name="email" class="form-input" required>
          <div class="form-error" data-field="email"></div>
        </div>

        <div class="form-group">
          <label class="form-label">Statut *</label>
          <select name="statut" class="form-input" required>
            <option value="confirmÃ©e">ConfirmÃ©e</option>
            <option value="en attente">En attente</option>
            <option value="annulÃ©e">AnnulÃ©e</option>
          </select>
          <div class="form-error" data-field="statut"></div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn-cancel" onclick="closeModal()">Annuler</button>
        <button type="submit" class="btn-primary">
          <i class="fa fa-check"></i> Modifier
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal-overlay" id="delete-modal">
  <div class="modal" style="width: 420px;">
    <div class="modal-header">
      <div class="modal-title">Confirmer la suppression</div>
      <button class="modal-close" onclick="closeDeleteModal()">
        <i class="fa fa-times"></i>
      </button>
    </div>
    <div class="modal-body">
      <p>ÃŠtes-vous sÃ»r de vouloir supprimer cette inscription ? Cette action est irrÃ©versible.</p>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn-cancel" onclick="closeDeleteModal()">Annuler</button>
      <button type="button" class="btn-danger" onclick="confirmDelete()">
        <i class="fa fa-trash"></i> Supprimer
      </button>
    </div>
  </div>
</div>

<!-- Toast Notification -->
<div class="toast-container" id="toast-container"></div>

<script>
let inscriptionToDelete = null;
let editingInscriptionId = null;

// Get inscriptions data from PHP
const inscriptionsData = <?php 
  $inscArray = [];
  if (isset($inscriptions) && is_array($inscriptions)) {
    foreach ($inscriptions as $insc) {
      $inscArray[] = [
        'id' => $insc->getId(),
        'idEvenement' => $insc->getIdEvenement(),
        'titreEvenement' => $insc->getTitreEvenement(),
        'nom' => $insc->getNom(),
        'prenom' => $insc->getPrenom(),
        'email' => $insc->getEmail(),
        'statut' => $insc->getStatut(),
        'dateInscription' => $insc->getDateInscription(),
      ];
    }
  }
  echo json_encode($inscArray);
?>;

function openEditModal(id) {
  editingInscriptionId = id;
  const insc = inscriptionsData.find(i => String(i.id) === String(id));
  
  if (!insc) {
    showToast('Inscription non trouvÃ©e', 'error');
    return;
  }

  const form = document.getElementById('inscription-form');
  form.querySelector('input[name="nom"]').value = insc.nom;
  form.querySelector('input[name="prenom"]').value = insc.prenom;
  form.querySelector('input[name="email"]').value = insc.email;
  form.querySelector('select[name="statut"]').value = insc.statut;
  form.action = getAdminIndexPath() + '/admin/updateInscription/' + id;

  document.getElementById('inscription-modal').classList.add('open');
}

function closeModal() {
  document.getElementById('inscription-modal').classList.remove('open');
  editingInscriptionId = null;
}

function deleteInscription(id) {
  inscriptionToDelete = id;
  document.getElementById('delete-modal').classList.add('open');
}

function closeDeleteModal() {
  document.getElementById('delete-modal').classList.remove('open');
  inscriptionToDelete = null;
}

function confirmDelete() {
  if (!inscriptionToDelete) return;
  window.location.href = getAdminIndexPath() + '/admin/deleteInscription/' + inscriptionToDelete;
}

function getAdminIndexPath() {
  const path = window.location.pathname;
  const parts = path.split('/');
  
  // Find the index.php position and rebuild the path up to the project root
  let adminPath = '';
  for (let i = 0; i < parts.length; i++) {
    if (parts[i] === 'index.php') {
      adminPath = '/' + parts.slice(1, i).join('/');
      if (adminPath && !adminPath.endsWith('/')) {
        adminPath += '/';
      }
      if (adminPath === '/') adminPath = '';
      break;
    }
  }
  
  return (adminPath ? adminPath : '') + '/index.php';
}

function filterInscriptionTable() {
  const input = document.getElementById('search-input');
  const filter = input.value.toLowerCase();
  const table = document.getElementById('inscriptions-table');
  const tr = table.getElementsByTagName('tr');
  let count = 0;

  for (let i = 1; i < tr.length; i++) {
    const row = tr[i];
    if (row.classList.contains('empty-state')) continue;
    
    const text = row.textContent || row.innerText;
    if (text.toLowerCase().indexOf(filter) > -1) {
      row.style.display = '';
      count++;
    } else {
      row.style.display = 'none';
    }
  }

  document.getElementById('table-count').textContent = count + ' inscription' + (count !== 1 ? 's' : '') + ' trouvÃ©e' + (count !== 1 ? 's' : '');
}

function showToast(message, type = 'success') {
  const container = document.getElementById('toast-container');
  const toast = document.createElement('div');
  toast.className = 'toast ' + type;
  toast.innerHTML = '<i class="fa ' + (type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle') + '"></i> ' + message;
  container.appendChild(toast);

  setTimeout(() => {
    toast.style.opacity = '0';
    setTimeout(() => toast.remove(), 250);
  }, 3000);
}

// Initialize table count
document.addEventListener('DOMContentLoaded', () => {
  const tbody = document.getElementById('inscriptions-tbody');
  const rows = tbody.querySelectorAll('tr.inscription-row');
  document.getElementById('table-count').textContent = rows.length + ' inscription' + (rows.length !== 1 ? 's' : '') + ' trouvÃ©e' + (rows.length !== 1 ? 's' : '');

  const params = new URLSearchParams(window.location.search);
  if (params.get('success')) {
    const action = params.get('action') || 'update';
    const messages = {
      update: 'Inscription modifiée avec succès',
      delete: 'Inscription supprimée avec succès'
    };
    showToast(messages[action] || 'Opération réussie', 'success');
  }

  if (params.get('error')) {
    showToast(params.get('msg') || 'Une erreur est survenue', 'error');
  }
});
</script>
</body>
</html>

