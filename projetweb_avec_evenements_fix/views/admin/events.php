<?php

if (realpath(__FILE__) === realpath($_SERVER['SCRIPT_FILENAME'])) {
    $requestUri = $_SERVER['REQUEST_URI'];
    $redirectUrl = preg_replace('@/views/admin(?:/.*)?$@', '/projetweb_avec_evenements/public/index.php/admin/events', $requestUri);
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
  <title>Jobyfind - Gestion Événements</title>
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
    .sidebar-badge {
      font-size: 10px;
      font-weight: 600;
      background: rgba(45,121,255,.3);
      color: #7aabff;
      padding: 2px 7px;
      border-radius: 99px;
      letter-spacing: .05em;
      text-transform: uppercase;
      margin-left: auto;
    }

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
    .sidebar-link .badge {
      margin-left: auto;
      background: rgba(255,255,255,.15);
      color: #fff;
      padding: 2px 7px;
      border-radius: 4px;
      font-size: 10px;
      font-weight: 600;
    }
    .sidebar-link.active .badge { background: rgba(255,255,255,.25); }

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
    .logout-btn {
      background: none;
      border: none;
      color: rgba(255,255,255,.5);
      cursor: pointer;
      font-size: 12px;
      padding: 4px;
      transition: color .15s;
      flex-shrink: 0;
    }
    .logout-btn:hover { color: var(--danger); }

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

    .smart-search-panel {
      padding: 18px 22px;
      border-bottom: 1px solid var(--border);
      background: linear-gradient(180deg, #fafcff 0%, #ffffff 100%);
    }
    .smart-search-bar {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 10px 12px;
      border: 1.5px solid var(--border);
      border-radius: 10px;
      background: var(--surface);
      box-shadow: 0 8px 24px rgba(11, 31, 75, 0.04);
    }
    .smart-search-bar i {
      color: var(--muted);
      font-size: 13px;
    }
    .smart-search-input {
      flex: 1;
      border: none;
      outline: none;
      background: transparent;
      font-family: "DM Sans", sans-serif;
      font-size: 13px;
      color: var(--text);
    }
    .smart-search-meta {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      margin-top: 12px;
      flex-wrap: wrap;
    }
    .smart-search-label {
      font-size: 11px;
      font-weight: 700;
      color: var(--navy);
      text-transform: uppercase;
      letter-spacing: .08em;
    }
    .smart-chip-row {
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
    }
    .smart-chip {
      border: 1px solid var(--border);
      background: #fff;
      color: var(--text);
      border-radius: 999px;
      padding: 6px 12px;
      font-size: 12px;
      font-weight: 600;
      cursor: pointer;
      transition: all .15s ease;
    }
    .smart-chip:hover {
      border-color: var(--blue);
      color: var(--blue);
      background: #eff6ff;
    }
    .smart-chip.active {
      background: var(--blue);
      border-color: var(--blue);
      color: #fff;
      box-shadow: 0 6px 18px rgba(45, 121, 255, 0.2);
    }
    .smart-chip-empty {
      font-size: 12px;
      color: var(--muted);
    }

    .header-actions { display: flex; gap: 8px; align-items: center; }
    .icon-btn {
      width: 34px; height: 34px;
      border-radius: 8px;
      border: 1.5px solid var(--border);
      background: var(--surface);
      display: flex; align-items: center; justify-content: center;
      color: var(--muted); cursor: pointer;
      font-size: 13px;
      position: relative;
      transition: border-color .2s;
    }
    .icon-btn:hover { border-color: var(--blue); color: var(--blue); }
    .icon-btn .dot {
      position: absolute;
      top: 5px; right: 5px;
      width: 7px; height: 7px;
      border-radius: 50%;
      background: var(--danger);
      border: 2px solid var(--surface);
    }

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

    .filter-select {
      padding: 6px 10px;
      border: 1.5px solid var(--border);
      border-radius: 7px;
      font-family: "DM Sans", sans-serif;
      font-size: 12px;
      color: var(--text);
      background: var(--surface);
      outline: none;
      cursor: pointer;
    }
    .filter-select:focus { border-color: var(--blue); }

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

    .btn-outline-sm {
      padding: 6px 12px;
      border: 1.5px solid var(--border);
      border-radius: 7px;
      background: transparent;
      font-family: "DM Sans", sans-serif;
      font-size: 12px;
      font-weight: 500;
      color: var(--text);
      cursor: pointer;
      transition: border-color .15s;
    }
    .btn-outline-sm:hover { border-color: var(--blue); color: var(--blue); }

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
    .badge-blue { background: #dbeafe; color: #1d4ed8; }
    .badge-green { background: #dcfce7; color: #15803d; }
    .badge-amber { background: #fef3c7; color: #92400e; }
    .badge-gray { background: #f1f5f9; color: #64748b; }
    .badge-red { background: #fee2e2; color: #b91c1c; }

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
    .action-btn:hover.del { border-color: var(--danger); color: var(--danger); background: #fee2e2; }
    .action-btn:hover.view { border-color: var(--success); color: var(--success); background: #dcfce7; }

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
    .modal-close:hover { color: var(--danger); }
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
    textarea.form-input { resize: vertical; min-height: 80px; }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }

    .form-input.success {
      border-color: var(--success) !important;
      background-color: rgba(34, 197, 94, 0.05);
    }
    .form-group.has-success .form-input {
      border-color: var(--success);
    }
    .form-group.has-error .form-input {
      border-color: var(--danger);
      background-color: rgba(239, 68, 68, 0.05);
    }
    .form-error {
      color: var(--danger);
      font-size: 12px;
      margin-top: 4px;
      display: none;
      transition: opacity 0.2s ease;
    }
    .form-error.show {
      display: block;
      opacity: 1;
    }
    .form-success {
      color: var(--success);
      font-size: 12px;
      margin-top: 4px;
      display: none;
      transition: opacity 0.2s ease;
    }
    .form-success.show {
      display: block;
      opacity: 1;
    }

    .btn-danger {
      padding: 8px 16px;
      background: var(--danger);
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
    .toast.error i { color: var(--danger); }
    @keyframes slideIn {
      from { transform: translateX(40px); opacity: 0; }
      to { transform: translateX(0); opacity: 1; }
    }
  </style>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>
</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar">
  <div class="sidebar-logo">
    <div class="logo-icon">JF</div>
    <div class="logo-text">Joby<span>find</span></div>
    <span class="sidebar-badge">Admin</span>
  </div>
  <div class="sidebar-section">
    <p class="sidebar-section-label">Tableau de bord</p>
    <a class="sidebar-link" href="<?php echo htmlspecialchars($indexBase . '/admin/events'); ?>">
      <i class="fa-solid fa-calendar-days"></i>
      <span>Événements</span>
    </a>
    <a class="sidebar-link" href="<?php echo htmlspecialchars($indexBase . '/admin/inscriptions'); ?>">
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
      <span>Admin</span>
      <span>›</span>
      <span class="current">Événements</span>
    </div>
    <div class="header-search">
      <i class="fa fa-magnifying-glass"></i>
      <input type="text" id="search-input" placeholder="Rechercher un événement..." onkeyup="filterEventTable()">
    </div>
    <div class="header-actions">
      <button class="icon-btn" title="Notifications">
        <i class="fa fa-bell"></i>
        <span class="dot"></span>
      </button>
    </div>
  </header>

  <!-- CONTENT -->
  <div class="content">

    <!-- STATS CHART -->
    <div class="table-card" style="padding: 24px; margin-bottom: 24px;">
      <h3 style="font-size: 16px; font-weight: 600; color: var(--navy); margin-bottom: 20px;">Statistiques des Événements selon le Lieu</h3>
      <div style="height: 320px; width: 100%;">
        <canvas id="locationChart"></canvas>
      </div>
    </div>

    <!-- TABLE -->
    <div class="table-card">
      <div class="table-header">
        <div>
          <div class="table-title">Gestion des Événements</div>
          <div class="table-subtitle" id="table-count">0 événements trouvés</div>
        </div>
        <div class="table-controls">
          <button type="button" class="btn-outline-sm" onclick="exportToPDF()" style="border-color: var(--danger); color: var(--danger);">
            <i class="fa fa-file-pdf"></i> Exporter PDF
          </button>
          <button type="button" class="btn-primary" onclick="openCreateModal()">
            <i class="fa fa-plus"></i> Ajouter
          </button>
        </div>
      </div>

      <!-- Smart search panel removed per user request -->

      <table id="events-table">
        <thead>
          <tr>
            <th>Titre</th>
            <th>Date</th>
            <th>Lieu</th>
            <th>Organisateur ID</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="events-tbody">
          <?php if (isset($events) && is_array($events)): ?>
            <?php foreach ($events as $event): ?>
          <tr
            class="event-row"
            data-id="<?php echo $event->getId(); ?>"
            data-title="<?php echo htmlspecialchars(mb_strtolower((string) $event->getTitre(), 'UTF-8')); ?>"
            data-description="<?php echo htmlspecialchars(mb_strtolower((string) $event->getDescription(), 'UTF-8')); ?>"
            data-lieu="<?php echo htmlspecialchars(mb_strtolower((string) $event->getLieu(), 'UTF-8')); ?>"
            data-organizer="<?php echo htmlspecialchars(mb_strtolower((string) $event->getIdOrganisateur(), 'UTF-8')); ?>"
          >
            <td><strong><?php echo htmlspecialchars($event->getTitre()); ?></strong></td>
            <td><?php echo date('d/m/Y', strtotime($event->getDate())); ?></td>
            <td><?php echo htmlspecialchars($event->getLieu()); ?></td>
            <td><?php echo htmlspecialchars($event->getIdOrganisateur()); ?></td>
            <td>
              <div class="action-btns">
                <button class="action-btn edit" title="Modifier" onclick="openEditModal(<?php echo $event->getId(); ?>)">
                  <i class="fa fa-edit"></i>
                </button>
                <button class="action-btn del" title="Supprimer" onclick="deleteEvent(<?php echo $event->getId(); ?>)">
                  <i class="fa fa-trash"></i>
                </button>
              </div>
            </td>
          </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- MODALS -->

<!-- Create/Edit Event Modal -->
<div class="modal-overlay" id="event-modal">
  <div class="modal">
    <div class="modal-header">
      <div class="modal-title" id="modal-title">Ajouter un événement</div>
      <button class="modal-close" onclick="closeEventModal()">
        <i class="fa fa-times"></i>
      </button>
    </div>
    <form id="event-form" method="POST" action="" enctype="multipart/form-data">
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label">Titre *</label>
          <input type="text" name="titre" class="form-input">
          <div class="form-error" data-field="titre"></div>
          <div class="form-success" data-field="titre"></div>
        </div>

        <div class="form-group">
          <label class="form-label">Description *</label>
          <textarea name="description" class="form-input" rows="4" placeholder="Description de l'événement"></textarea>
          <div class="form-error" data-field="description"></div>
          <div class="form-success" data-field="description"></div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Date *</label>
            <input type="text" name="date" class="form-input">
            <div class="form-error" data-field="date"></div>
            <div class="form-success" data-field="date"></div>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Lieu *</label>
          <input type="text" name="lieu" class="form-input">
          <div class="form-error" data-field="lieu"></div>
          <div class="form-success" data-field="lieu"></div>
        </div>

        <div class="form-group">
          <label class="form-label">Organisateur ID *</label>
          <input type="text" name="idOrganisateur" class="form-input" placeholder="Chiffre ou texte (ex: 1, USER-001)">
          <div class="form-error" data-field="idOrganisateur"></div>
          <div class="form-success" data-field="idOrganisateur"></div>
        </div>

        <div class="form-group">
          <label class="form-label">Image de l'événement</label>
          <input type="file" name="image" class="form-input" accept="image/jpeg,image/png,image/webp,image/gif">
          <p style="font-size: 11px; color: var(--muted); margin-top: 4px;">Formats acceptés : JPG, PNG, WEBP, GIF. Laissez vide pour conserver l'image actuelle.</p>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn-cancel" onclick="closeEventModal()">Annuler</button>
        <button type="submit" class="btn-primary">
          <i class="fa fa-check"></i> <span id="submit-btn-text">Ajouter</span>
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
      <p>Êtes-vous sûr de vouloir supprimer cet événement ? Cette action est irréversible.</p>
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
let eventToDelete = null;
let editingEventId = null;
let activeTopicFilter = null;

// Get events data from PHP
const eventsData = <?php 
  $eventsArray = [];
  if (isset($events) && is_array($events)) {
    foreach ($events as $e) {
      $eventsArray[] = [
        'id' => $e->getId(),
        'titre' => $e->getTitre(),
        'description' => $e->getDescription(),
        'date' => $e->getDate(),
        'lieu' => $e->getLieu(),
        'idOrganisateur' => $e->getIdOrganisateur(),
      ];
    }
  }
  echo json_encode($eventsArray);
?>;

const topicLibrary = [
  { label: 'Python', aliases: ['python', 'django', 'flask', 'pandas'] },
  { label: 'Web', aliases: ['web', 'site', 'html', 'css', 'javascript', 'php'] },
  { label: 'Design', aliases: ['design', 'ui', 'ux', 'graphique', 'figma'] },
  { label: 'Data', aliases: ['data', 'donnee', 'donnée', 'analyse', 'analytics', 'sql'] },
  { label: 'IA', aliases: ['ia', 'intelligence artificielle', 'machine learning', 'ai'] },
  { label: 'Mobile', aliases: ['mobile', 'android', 'ios', 'flutter', 'react native'] },
  { label: 'Cloud', aliases: ['cloud', 'aws', 'azure', 'devops', 'docker'] },
  { label: 'Startup', aliases: ['startup', 'entrepreneuriat', 'business', 'innovation'] },
  { label: 'Marketing', aliases: ['marketing', 'seo', 'communication', 'branding'] },
  { label: 'Formation', aliases: ['formation', 'atelier', 'workshop', 'cours', 'bootcamp'] }
];

function normalizeSearchText(value) {
  return (value || '')
    .toString()
    .toLowerCase()
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '');
}

function eventToSearchBlob(event) {
  return normalizeSearchText([
    event.titre,
    event.description,
    event.lieu,
    event.idOrganisateur
  ].join(' '));
}

function getDetectedTopics() {
  return topicLibrary.filter(topic => {
    return eventsData.some(event => {
      const text = eventToSearchBlob(event);
      return topic.aliases.some(alias => text.includes(normalizeSearchText(alias)));
    });
  });
}

function renderTopicChips() {
  const container = document.getElementById('smart-topic-chips');
  if (!container) return;

  const topics = getDetectedTopics();
  if (!topics.length) {
    container.innerHTML = '<span class="smart-chip-empty">Aucun sujet détecté pour le moment</span>';
    return;
  }

  container.innerHTML = topics.map(topic => `
    <button
      type="button"
      class="smart-chip ${activeTopicFilter === topic.label ? 'active' : ''}"
      onclick="toggleTopicFilter('${topic.label}')"
    >
      ${topic.label}
    </button>
  `).join('');
}

function toggleTopicFilter(topicLabel) {
  activeTopicFilter = activeTopicFilter === topicLabel ? null : topicLabel;

  const smartInput = document.getElementById('smart-search-input');
  if (smartInput) {
    smartInput.value = activeTopicFilter ? topicLabel : '';
  }

  filterEventTable();
}

// Validation function
function validateEventForm() {
  const errors = {};
  const form = document.getElementById('event-form');
  
  // Get form values
  const titre = form.querySelector('input[name="titre"]').value.trim();
  const description = form.querySelector('textarea[name="description"]').value.trim();
  const date = form.querySelector('input[name="date"]').value;
  const lieu = form.querySelector('input[name="lieu"]').value.trim();
  const idOrganisateur = form.querySelector('input[name="idOrganisateur"]').value.trim();

  // Clear previous errors
  clearFormErrors();

  // Validate titre (required string, not only numbers)
  if (!titre) {
    errors.titre = 'Le titre est requis';
  } else if (titre.length < 2) {
    errors.titre = 'Le titre doit contenir au moins 2 caractères';
  } else if (/^\d+$/.test(titre)) {
    errors.titre = 'Le titre doit être une chaîne de caractères (pas seulement des chiffres)';
  } else if (!/[a-zA-ZÀ-ÿ]/.test(titre)) {
    errors.titre = 'Le titre doit contenir au moins une lettre';
  }

  // Validate description (required string, min 10 chars, at least one letter)
  if (!description) {
    errors.description = 'La description est requise';
  } else if (description.length < 10) {
    errors.description = 'La description doit contenir au moins 10 caractères';
  }

  // Validate date (required, valid format)
  if (!date) {
    errors.date = 'La date est requise';
  } else {
    const dateObj = new Date(date);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    if (isNaN(dateObj.getTime())) {
      errors.date = 'La date n\'est pas valide';
    }
  }

  // Validate lieu (required string, not only numbers)
  if (!lieu) {
    errors.lieu = 'Le lieu est requis';
  } else if (lieu.length < 2) {
    errors.lieu = 'Le lieu doit contenir au moins 2 caractères';
  } else if (/^\d+$/.test(lieu)) {
    errors.lieu = 'Le lieu doit être une chaîne de caractères (pas seulement des chiffres)';
  } else if (!/[a-zA-ZÀ-ÿ]/.test(lieu)) {
    errors.lieu = 'Le lieu doit contenir au moins une lettre';
  }

  // Validate idOrganisateur (required: positive integer OR non-empty string)
  if (!idOrganisateur) {
    errors.idOrganisateur = 'L\'ID organisateur est requis';
  } else {
    const idOrgNum = Number(idOrganisateur);
    // If it's a number, it must be a positive integer > 0
    if (!isNaN(idOrgNum) && Number.isInteger(idOrgNum)) {
      if (idOrgNum <= 0) {
        errors.idOrganisateur = 'Si numérique, l\'ID organisateur doit être un entier positif (> 0)';
      }
    }
    // If it's a string, it must be non-empty
    else if (typeof idOrganisateur === 'string' && idOrganisateur.trim().length === 0) {
      errors.idOrganisateur = 'L\'ID organisateur ne peut pas être vide';
    }
    // If it's neither a positive integer nor a valid string, reject it
    else if (isNaN(idOrgNum) && typeof idOrganisateur === 'string' && idOrganisateur.trim().length < 2) {
      errors.idOrganisateur = 'L\'ID organisateur doit être soit un chiffre positif, soit au moins 2 caractères';
    }
  }

  // Display errors
  displayFormErrors(errors);

  return Object.keys(errors).length === 0;
}

// Clear form errors
function clearFormErrors() {
  const form = document.getElementById('event-form');
  if (!form) return;
  
  const groups = form.querySelectorAll('.form-group');
  groups.forEach(group => {
    group.classList.remove('has-error', 'has-success');
    
    const input = group.querySelector('.form-input');
    if (input) input.classList.remove('error', 'success');
    
    const errorDiv = group.querySelector('.form-error');
    if (errorDiv) {
      errorDiv.textContent = '';
      errorDiv.classList.remove('show');
    }
    
    const successDiv = group.querySelector('.form-success');
    if (successDiv) {
      successDiv.textContent = '';
      successDiv.classList.remove('show');
    }
  });
}

// Display form errors
function displayFormErrors(errors) {
  const form = document.getElementById('event-form');
  
  Object.keys(errors).forEach(fieldName => {
    const field = form.querySelector(`input[name="${fieldName}"], textarea[name="${fieldName}"]`);
    if (field) {
      const group = field.closest('.form-group');
      const errorDiv = group.querySelector('.form-error');
      
      group.classList.add('has-error');
      field.classList.add('error');
      errorDiv.textContent = errors[fieldName];
      errorDiv.classList.add('show');
    }
  });
}

// Submit event form
function submitEventForm(e) {
  console.log('submitEventForm called');
  e.preventDefault();
  
  console.log('Form validation starting...');
  if (!validateEventForm()) {
    console.log('Form validation failed');
    return false;
  }

  console.log('Form validation passed, submitting...');
  const form = document.getElementById('event-form');
  const action = form.action;
  console.log('Form action:', action);
  console.log('Form method:', form.method);
  
  // Manually submit the form via fetch
  const formData = new FormData(form);
  
  // Log formData contents for debugging
  for (let [key, value] of formData.entries()) {
    if (key === 'image' && value instanceof File) {
      console.log(key, ':', value.name, '(File)');
    } else {
      console.log(key, ':', value);
    }
  }
  
  fetch(action, {
    method: form.method,
    body: formData
  })
  .then(response => {
    console.log('Response received:', response.status);
    if (response.redirected) {
      console.log('Response redirected to:', response.url);
      window.location.href = response.url;
    } else if (response.ok) {
      console.log('Response OK, reloading page');
      window.location.reload();
    } else {
      console.error('Response not ok:', response.status, response.statusText);
      return response.text().then(text => {
        console.error('Response body:', text);
        throw new Error('HTTP ' + response.status + ': ' + response.statusText);
      });
    }
  })
  .catch(error => {
    console.error('Form submission error:', error);
    showToast('Erreur lors de l\'ajout: ' + error.message, 'error');
  });
  
  return false;
}

function getAdminIndexPath() {
  const path = window.location.pathname;
  const indexPos = path.indexOf('/index.php/admin');
  if (indexPos !== -1) {
    return path.slice(0, indexPos + '/index.php'.length);
  }
  const adminPos = path.indexOf('/admin');
  if (adminPos !== -1) {
    return path.slice(0, adminPos) + '/index.php';
  }
  return '/index.php';
}

function openCreateModal() {
  console.log('openCreateModal called');
  editingEventId = null;
  document.getElementById('event-form').reset();
  clearFormErrors();
  document.getElementById('modal-title').textContent = 'Ajouter un événement';
  document.getElementById('submit-btn-text').textContent = 'Ajouter';
  
  const indexPath = getAdminIndexPath();
  const action = indexPath + '/admin/storeEvent';
  document.getElementById('event-form').action = action;
  console.log('Modal action set to:', action);
  
  const modal = document.getElementById('event-modal');
  modal.classList.add('open');
  console.log('Modal opened:', modal.classList);
  
  // Trigger validation for empty form
  setTimeout(() => {
    validateAllFields();
  }, 100);
}

function openEditModal(eventId) {
  const event = eventsData.find(e => e.id == eventId);
  if (!event) return;

  editingEventId = eventId;
  document.getElementById('event-form').action = getAdminIndexPath() + '/admin/updateEvent/' + eventId;
  
  // Fill form
  document.querySelector('input[name="titre"]').value = event.titre;
  document.querySelector('textarea[name="description"]').value = event.description;
  document.querySelector('input[name="date"]').value = event.date;
  document.querySelector('input[name="lieu"]').value = event.lieu;
  document.querySelector('input[name="idOrganisateur"]').value = event.idOrganisateur;

  clearFormErrors();
  document.getElementById('modal-title').textContent = 'Modifier l\'événement';
  document.getElementById('submit-btn-text').textContent = 'Modifier';
  document.getElementById('event-modal').classList.add('open');
  
  // Trigger validation for filled form
  setTimeout(() => {
    validateAllFields();
  }, 100);
}

// Validate all fields
function validateAllFields() {
  const form = document.getElementById('event-form');
  
  const fields = ['titre', 'description', 'date', 'lieu', 'idOrganisateur'];
  fields.forEach(fieldName => {
    const field = form.querySelector(`input[name="${fieldName}"], textarea[name="${fieldName}"]`);
    if (field) {
      validateField(fieldName, field.value.trim());
    }
  });
}

function closeEventModal() {
  document.getElementById('event-modal').classList.remove('open');
  clearFormErrors();
}

function deleteEvent(eventId) {
  eventToDelete = eventId;
  document.getElementById('delete-modal').classList.add('open');
}

function closeDeleteModal() {
  document.getElementById('delete-modal').classList.remove('open');
  eventToDelete = null;
}


function confirmDelete() {
  if (!eventToDelete) return;
  window.location.href = getAdminIndexPath() + '/admin/deleteEvent/' + eventToDelete;
}

function filterEventTable() {
  const searchTerm = document.getElementById('search-input').value.toLowerCase();
  const rows = document.querySelectorAll('.event-row');
  let visibleCount = 0;

  rows.forEach(row => {
    const title = row.cells[0].textContent.toLowerCase();
    const matches = title.includes(searchTerm);
    row.style.display = matches ? '' : 'none';
    if (matches) visibleCount++;
  });

  document.getElementById('table-count').textContent = visibleCount + ' événement' + (visibleCount > 1 ? 's' : '') + ' trouvé' + (visibleCount > 1 ? 's' : '');
}

function filterEventTable() {
  const headerSearch = normalizeSearchText(document.getElementById('search-input').value);
  const smartSearchInput = document.getElementById('smart-search-input');
  const smartSearch = normalizeSearchText(smartSearchInput ? smartSearchInput.value : '');
  const searchTerms = [headerSearch, smartSearch].filter(Boolean);
  const rows = document.querySelectorAll('.event-row');
  let visibleCount = 0;
  const activeTopic = topicLibrary.find(topic => topic.label === activeTopicFilter);

  rows.forEach(row => {
    const searchBlob = normalizeSearchText([
      row.dataset.title,
      row.dataset.description,
      row.dataset.lieu,
      row.dataset.organizer
    ].join(' '));
    const matchesText = searchTerms.length === 0 || searchTerms.every(term => searchBlob.includes(term));
    const matchesTopic = !activeTopic || activeTopic.aliases.some(alias => searchBlob.includes(normalizeSearchText(alias)));
    const matches = matchesText && matchesTopic;
    row.style.display = matches ? '' : 'none';
    if (matches) visibleCount++;
  });

  document.getElementById('table-count').textContent = visibleCount + ' événement' + (visibleCount > 1 ? 's' : '') + ' trouvé' + (visibleCount > 1 ? 's' : '');
  renderTopicChips();
}

function showToast(message, type = 'success') {
  const container = document.getElementById('toast-container');
  const toast = document.createElement('div');
  toast.className = 'toast ' + type;
  toast.innerHTML = `
    <i class="fa fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
    <span>${message}</span>
  `;
  container.appendChild(toast);
  setTimeout(() => toast.remove(), 4000);
}

// Attach form submit handler
document.addEventListener('DOMContentLoaded', function() {
  console.log('DOMContentLoaded - initializing form');
  const form = document.getElementById('event-form');
  if (form) {
    console.log('Form found, attaching submit handler');
    form.addEventListener('submit', submitEventForm);
    console.log('Submit handler attached');
    
    // Add real-time validation
    addRealTimeValidation();
    console.log('Real-time validation added');
  } else {
    console.error('Event form not found!');
  }
});

// Real-time validation functions
function addRealTimeValidation() {
  const form = document.getElementById('event-form');
  
  // Validate titre on input and blur
  const titreField = form.querySelector('input[name="titre"]');
  titreField.addEventListener('input', function() {
    validateField('titre', this.value.trim());
  });
  titreField.addEventListener('blur', function() {
    validateField('titre', this.value.trim());
  });
  
  // Validate description on input and blur
  const descField = form.querySelector('textarea[name="description"]');
  descField.addEventListener('input', function() {
    validateField('description', this.value.trim());
  });
  descField.addEventListener('blur', function() {
    validateField('description', this.value.trim());
  });
  
  // Validate date on change
  form.querySelector('input[name="date"]').addEventListener('change', function() {
    validateField('date', this.value);
  });
  
  // Validate lieu on input and blur
  const lieuField = form.querySelector('input[name="lieu"]');
  lieuField.addEventListener('input', function() {
    validateField('lieu', this.value.trim());
  });
  lieuField.addEventListener('blur', function() {
    validateField('lieu', this.value.trim());
  });
  
  // Validate idOrganisateur on input and blur
  const idField = form.querySelector('input[name="idOrganisateur"]');
  idField.addEventListener('input', function() {
    validateField('idOrganisateur', this.value.trim());
  });
  idField.addEventListener('blur', function() {
    validateField('idOrganisateur', this.value.trim());
  });
}

// Validate individual field
function validateField(fieldName, value) {
  const form = document.getElementById('event-form');
  const field = form.querySelector(`input[name="${fieldName}"], textarea[name="${fieldName}"]`);
  const group = field.closest('.form-group');
  const errorDiv = group.querySelector('.form-error');
  const successDiv = group.querySelector('.form-success');
  
  let error = null;
  let success = null;
  
  switch(fieldName) {
    case 'titre':
      if (!value) {
        error = 'Le titre est requis';
      } else if (value.length < 2) {
        error = 'Le titre doit contenir au moins 2 caractères';
      } else if (/^\d+$/.test(value)) {
        error = 'Le titre doit être une chaîne de caractères (pas seulement des chiffres)';
      } else if (!/[a-zA-ZÀ-ÿ]/.test(value)) {
        error = 'Le titre doit contenir au moins une lettre';
      } else {
        success = '✓ Titre valide';
      }
      break;
      
    case 'description':
      if (!value) {
        error = 'La description est requise';
      } else if (value.length < 10) {
        error = 'La description doit contenir au moins 10 caractères';
      } else {
        success = '✓ Description valide';
      }
      break;
      
    case 'date':
      if (!value) {
        error = 'La date est requise';
      } else {
        const dateObj = new Date(value);
        if (isNaN(dateObj.getTime())) {
          error = 'La date n\'est pas valide';
        } else {
          success = '✓ Date valide';
        }
      }
      break;
      
    case 'lieu':
      if (!value) {
        error = 'Le lieu est requis';
      } else if (value.length < 2) {
        error = 'Le lieu doit contenir au moins 2 caractères';
      } else if (/^\d+$/.test(value)) {
        error = 'Le lieu doit être une chaîne de caractères (pas seulement des chiffres)';
      } else if (!/[a-zA-ZÀ-ÿ]/.test(value)) {
        error = 'Le lieu doit contenir au moins une lettre';
      } else {
        success = '✓ Lieu valide';
      }
      break;
      
    case 'idOrganisateur':
      if (!value) {
        error = 'L\'ID organisateur est requis';
      } else {
        const idOrgNum = Number(value);
        if (!isNaN(idOrgNum) && Number.isInteger(idOrgNum)) {
          if (idOrgNum <= 0) {
            error = 'Si numérique, l\'ID organisateur doit être un entier positif (> 0)';
          } else {
            success = '✓ ID numérique valide';
          }
        } else if (typeof value === 'string' && value.trim().length < 2) {
          error = 'L\'ID organisateur doit être soit un chiffre positif, soit au moins 2 caractères';
        } else {
          success = '✓ ID texte valide';
        }
      }
      break;
  }
  
  // Update field appearance
  if (error) {
    group.classList.remove('has-success');
    group.classList.add('has-error');
    field.classList.remove('success');
    field.classList.add('error');
    errorDiv.textContent = error;
    errorDiv.classList.add('show');
    successDiv.textContent = '';
    successDiv.classList.remove('show');
  } else if (success) {
    group.classList.remove('has-error');
    group.classList.add('has-success');
    field.classList.remove('error');
    field.classList.add('success');
    errorDiv.textContent = '';
    errorDiv.classList.remove('show');
    successDiv.textContent = success;
    successDiv.classList.add('show');
  } else {
    group.classList.remove('has-error', 'has-success');
    field.classList.remove('error', 'success');
    errorDiv.textContent = '';
    errorDiv.classList.remove('show');
    successDiv.textContent = '';
    successDiv.classList.remove('show');
  }
}

// Check for success messages
const params = new URLSearchParams(window.location.search);
if (params.get('success')) {
  const action = params.get('action') || 'create';
  const messages = {
    'create': 'Événement créé avec succès',
    'update': 'Événement modifié avec succès',
    'delete': 'Événement supprimé avec succès'
  };
  showToast(messages[action] || 'Opération réussie', 'success');
}

// Check for error messages
if (params.get('error')) {
  const msg = params.get('msg') || 'Une erreur s\'est produite';
  showToast(msg, 'error');
}

// Export to PDF
function exportToPDF() {
  const { jsPDF } = window.jspdf;
  const doc = new jsPDF();
  
  // Titre
  doc.setFontSize(18);
  doc.setTextColor(11, 31, 75);
  doc.text("Liste des Événements - Jobyfind", 14, 22);
  
  // Date de génération
  doc.setFontSize(10);
  doc.setTextColor(100);
  doc.text("Généré le : " + new Date().toLocaleDateString('fr-FR') + " à " + new Date().toLocaleTimeString('fr-FR'), 14, 30);

  // Préparation des données depuis eventsData
  const tableData = [];
  eventsData.forEach(e => {
    tableData.push([
      e.titre,
      new Date(e.date).toLocaleDateString('fr-FR'),
      e.lieu,
      e.idOrganisateur
    ]);
  });

  // Création du tableau PDF
  doc.autoTable({
    startY: 38,
    head: [['Titre', 'Date', 'Lieu', 'Organisateur ID']],
    body: tableData,
    theme: 'grid',
    headStyles: { fillColor: [45, 121, 255] },
    styles: { font: 'helvetica', fontSize: 10 }
  });

  // Sauvegarde
  doc.save('evenements_jobyfind.pdf');
  showToast('Export PDF réussi !', 'success');
}

// Render location chart
function renderLocationChart() {
  const locationCounts = {};
  eventsData.forEach(e => {
    const loc = e.lieu ? e.lieu.trim() : 'Inconnu';
    if (loc === '') return;
    locationCounts[loc] = (locationCounts[loc] || 0) + 1;
  });

  const labels = Object.keys(locationCounts);
  const data = Object.values(locationCounts);

  const ctx = document.getElementById('locationChart').getContext('2d');
  
  if (window.locationChartInstance) {
    window.locationChartInstance.destroy();
  }

  window.locationChartInstance = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: labels,
      datasets: [{
        label: 'Nombre d\'événements',
        data: data,
        backgroundColor: [
          'rgba(45, 121, 255, 0.8)',
          'rgba(34, 197, 94, 0.8)',
          'rgba(245, 158, 11, 0.8)',
          'rgba(239, 68, 68, 0.8)',
          'rgba(139, 92, 246, 0.8)',
          'rgba(14, 165, 233, 0.8)',
          'rgba(236, 72, 153, 0.8)'
        ],
        borderColor: 'transparent',
        borderWidth: 0,
        borderRadius: 6
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        y: {
          beginAtZero: true,
          ticks: { stepSize: 1 }
        }
      },
      plugins: {
        legend: { display: false },
        tooltip: {
          padding: 12,
          backgroundColor: 'rgba(11, 31, 75, 0.9)',
          titleFont: { size: 14, family: "'DM Sans', sans-serif" },
          bodyFont: { size: 13, family: "'DM Sans', sans-serif" },
          cornerRadius: 8,
          displayColors: false
        }
      }
    }
  });
}

// Initialize
renderLocationChart();
filterEventTable();

// Initialize Flatpickr for date inputs
flatpickr("input[name='date']", {
  dateFormat: "Y-m-d",
  allowInput: true
});
</script>

</body>
</html>
  doc.autoTable({
    startY: 38,
    head: [['Titre', 'Date', 'Lieu', 'Organisateur ID']],
    body: tableData,
    theme: 'grid',
    headStyles: { fillColor: [45, 121, 255] },
    styles: { font: 'helvetica', fontSize: 10 }
  });

  // Sauvegarde
  doc.save('evenements_jobyfind.pdf');
  showToast('Export PDF réussi !', 'success');
}

// Render location chart
function renderLocationChart() {
  const locationCounts = {};
  eventsData.forEach(e => {
    const loc = e.lieu ? e.lieu.trim() : 'Inconnu';
    if (loc === '') return;
    locationCounts[loc] = (locationCounts[loc] || 0) + 1;
  });

  const labels = Object.keys(locationCounts);
  const data = Object.values(locationCounts);

  const ctx = document.getElementById('locationChart').getContext('2d');
  
  if (window.locationChartInstance) {
    window.locationChartInstance.destroy();
  }

  window.locationChartInstance = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: labels,
      datasets: [{
        label: 'Nombre d\'événements',
        data: data,
        backgroundColor: [
          'rgba(45, 121, 255, 0.8)',
          'rgba(34, 197, 94, 0.8)',
          'rgba(245, 158, 11, 0.8)',
          'rgba(239, 68, 68, 0.8)',
          'rgba(139, 92, 246, 0.8)',
          'rgba(14, 165, 233, 0.8)',
          'rgba(236, 72, 153, 0.8)'
        ],
        borderColor: 'transparent',
        borderWidth: 0,
        borderRadius: 6
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        y: {
          beginAtZero: true,
          ticks: { stepSize: 1 }
        }
      },
      plugins: {
        legend: { display: false },
        tooltip: {
          padding: 12,
          backgroundColor: 'rgba(11, 31, 75, 0.9)',
          titleFont: { size: 14, family: "'DM Sans', sans-serif" },
          bodyFont: { size: 13, family: "'DM Sans', sans-serif" },
          cornerRadius: 8,
          displayColors: false
        }
      }
    }
  });
}

// Initialize
renderLocationChart();
filterEventTable();

// Initialize Flatpickr for date inputs
flatpickr("input[name='date']", {
  dateFormat: "Y-m-d",
  allowInput: true
});
</script>

</body>
</html>
