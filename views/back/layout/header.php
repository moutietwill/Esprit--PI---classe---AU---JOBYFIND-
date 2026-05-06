<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Jobyfind — Admin (MVC)</title>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Serif+Display&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<?php
// Détection de l'action courante pour surligner le lien actif
$currentAction = isset($_GET['action']) ? $_GET['action'] : 'front_offres';
function sidebarActive($actions) {
    global $currentAction;
    $list = is_array($actions) ? $actions : [$actions];
    return in_array($currentAction, $list) ? ' active' : '';
}
?>
  <style>
    /* Add base CSS directly from admin.html for quick layout */
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    :root { --blue: #2d79ff; --navy: #0b1f4b; --sidebar-w: 240px; --header-h: 60px; --bg: #f0f2f8; --surface: #ffffff; --border: #e2e8f0; --text: #374151; --muted: #9ca3af; --danger: #ef4444; --success: #22c55e; --warning: #f59e0b; --radius: 10px; }
    body { font-family: 'DM Sans', sans-serif; background: var(--bg); color: var(--text); display: flex; min-height: 100vh; font-size: 14px; }
    .sidebar { width: var(--sidebar-w); background: var(--navy); min-height: 100vh; position: fixed; top: 0; left: 0; display: flex; flex-direction: column; z-index: 100; }
    .sidebar-logo { display: flex; align-items: center; gap: 10px; padding: 20px 20px 16px; border-bottom: 1px solid rgba(255,255,255,.08); }
    .sidebar-logo .logo-icon { width: 34px; height: 34px; background: var(--blue); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 14px; font-weight: 600; }
    .sidebar-logo .logo-text { font-family: 'DM Serif Display', serif; color: #fff; font-size: 17px; } .sidebar-logo .logo-text span { color: #7aabff; }
    .sidebar-section { padding: 18px 12px 6px; }
    .sidebar-section-label { font-size: 10px; font-weight: 600; letter-spacing: .1em; text-transform: uppercase; color: rgba(255,255,255,.3); padding: 0 8px; margin-bottom: 6px; }
    .sidebar-link { display: flex; align-items: center; gap: 10px; padding: 9px 10px; border-radius: 8px; text-decoration: none; color: rgba(255,255,255,.55); font-size: 13.5px; font-weight: 500; transition: all .15s; }
    .sidebar-link i { width: 20px; text-align: center; font-size: 14px; }
    .sidebar-link:hover { background: rgba(255,255,255,.07); color: rgba(255,255,255,.85); }
    .sidebar-link.active { background: rgba(45,121,255,.25); color: #fff; } .sidebar-link.active i { color: var(--blue); }
    .main { margin-left: var(--sidebar-w); flex: 1; display: flex; flex-direction: column; }
    .header { height: var(--header-h); background: var(--surface); border-bottom: 1px solid var(--border); display: flex; align-items: center; padding: 0 28px; gap: 16px; position: sticky; top: 0; z-index: 50; }
    .header-breadcrumb { display: flex; align-items: center; gap: 6px; font-size: 13px; color: var(--muted); }
    .header-breadcrumb .current { color: var(--navy); font-weight: 600; }
    .content { padding: 28px; }
    
    /* Tables & Buttons */
    .table-card { background: var(--surface); border-radius: var(--radius); border: 1px solid var(--border); overflow: hidden; }
    .table-header { display: flex; align-items: center; justify-content: space-between; padding: 18px 22px; border-bottom: 1px solid var(--border); }
    .table-title { font-size: 15px; font-weight: 600; color: var(--navy); }
    .btn-primary { padding: 7px 14px; background: var(--blue); color: #fff; border: none; border-radius: 7px; font-family: 'DM Sans', sans-serif; font-size: 12px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px; text-decoration: none;}
    .btn-primary:hover { opacity: .88; }
    .btn-danger { padding: 7px 14px; background: var(--danger); color: #fff; border: none; border-radius: 7px; font-size: 12px; text-decoration: none; cursor:pointer;}
    .btn-danger:hover { opacity: .88; }
    .btn-outline { padding: 7px 14px; background: #fff; color: var(--text); border: 1px solid var(--border); border-radius: 7px; text-decoration: none; font-size:12px; }
    table { width: 100%; border-collapse: collapse; }
    thead th { padding: 11px 22px; text-align: left; font-size: 11px; font-weight: 600; text-transform: uppercase; color: var(--muted); border-bottom: 1px solid var(--border); background: #fafbfd; }
    tbody tr { border-bottom: 1px solid var(--border); } tbody tr:hover { background: #f8faff; }
    tbody td { padding: 13px 22px; vertical-align: middle; }
    .action-btn { display: inline-flex; width: 28px; height: 28px; border-radius: 6px; border: 1.5px solid var(--border); background: var(--surface); align-items: center; justify-content: center; color: var(--muted); text-decoration: none; font-size: 11px; transition: all .12s; margin-right:4px;}
    .action-btn.edit:hover { border-color: var(--blue); color: var(--blue); background: #dbeafe; }
    .action-btn.del:hover { border-color: var(--danger); color: var(--danger); background: #fee2e2; }

    /* Forms */
    .form-container { background: var(--surface); padding: 25px; border-radius: var(--radius); border: 1px solid var(--border); max-width: 600px; }
    .form-group { margin-bottom: 16px; }
    .form-label { display: block; font-size: 12px; font-weight: 600; color: var(--navy); margin-bottom: 6px; }
    .form-input { width: 100%; padding: 9px 12px; border: 1.5px solid var(--border); border-radius: 7px; font-family: 'DM Sans', sans-serif; font-size: 13px; color: var(--text); outline: none; }
    .form-input:focus { border-color: var(--blue); }
    .form-error { color: var(--danger); font-size: 11px; margin-top:4px; display: none; }
  </style>
</head>
<body>
  <!-- SIDEBAR -->
  <aside class="sidebar">
    <div class="sidebar-logo">
      <div class="logo-icon">J</div>
      <div class="logo-text">Joby<span>find</span></div>
    </div>
    <div class="sidebar-section">
      <p class="sidebar-section-label">Gestion</p>
      <a href="index.php?action=list_offres" class="sidebar-link<?= sidebarActive(['list_offres','add_offre','edit_offre']) ?>">
        <i class="fa-solid fa-briefcase"></i>
        <span>Offres</span>
      </a>
      <a href="index.php?action=list_candidatures" class="sidebar-link<?= sidebarActive(['list_candidatures','list_candidatures_offre','add_candidature','edit_candidature']) ?>">
        <i class="fa-solid fa-file-pen"></i>
        <span>Candidatures</span>
      </a>
    </div>
    <div class="sidebar-section">
      <p class="sidebar-section-label">Analytique</p>
      <a href="index.php?action=stats_offres" class="sidebar-link<?= sidebarActive('stats_offres') ?>">
        <i class="fa-solid fa-chart-pie"></i>
        <span>Stats Offres</span>
      </a>
      <a href="index.php?action=stats_candidatures" class="sidebar-link<?= sidebarActive('stats_candidatures') ?>">
        <i class="fa-solid fa-chart-column"></i>
        <span>Stats Candidatures</span>
      </a>
      <a href="index.php?action=pdf_offres" class="sidebar-link<?= sidebarActive('pdf_offres') ?>" target="_blank">
        <i class="fa-solid fa-file-pdf"></i>
        <span>Exporter PDF</span>
      </a>
    </div>
    <div class="sidebar-section">
      <p class="sidebar-section-label">Navigation</p>
      <a href="index.php?action=front_offres" class="sidebar-link" target="_blank">
        <i class="fa-solid fa-globe"></i>
        <span>Voir le Front-office</span>
      </a>
    </div>
  </aside>

<?php
$breadcrumbLabels = [
    'list_offres'              => 'Offres',
    'add_offre'                => 'Nouvelle offre',
    'edit_offre'               => 'Modifier l\'offre',
    'delete_offre'             => 'Offres',
    'stats_offres'             => 'Statistiques — Offres',
    'pdf_offres'               => 'Export PDF',
    'list_candidatures'        => 'Candidatures',
    'list_candidatures_offre'  => 'Candidatures par offre',
    'add_candidature'          => 'Nouvelle candidature',
    'edit_candidature'         => 'Modifier la candidature',
    'delete_candidature'       => 'Candidatures',
    'stats_candidatures'       => 'Statistiques — Candidatures',
];
$breadcrumb = $breadcrumbLabels[$currentAction] ?? 'Dashboard';
?>
  <!-- MAIN -->
  <div class="main">
    <header class="header">
      <div class="header-breadcrumb">
        <span>Admin</span> <i class="fa fa-chevron-right" style="font-size:9px"></i> <span class="current"><?= htmlspecialchars($breadcrumb) ?></span>
      </div>
    </header>
    <div class="content">
