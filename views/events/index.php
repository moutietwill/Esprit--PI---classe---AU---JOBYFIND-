<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Jobyfind - Événements</title>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Serif+Display&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --blue:    #2d79ff;
      --navy:    #0b1f4b;
      --light:   #f5f7fb;
      --border:  #e2e8f0;
      --text:    #374151;
      --muted:   #9ca3af;
      --radius:  10px;
      --success: #22c55e;
      --danger:  #ef4444;
      --warning: #f59e0b;
    }

    body {
      font-family: "DM Sans", sans-serif;
      background: var(--light);
      color: var(--text);
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }

    nav {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 40px;
      height: 64px;
      background: #fff;
      border-bottom: 1px solid var(--border);
      position: sticky;
      top: 0;
      z-index: 100;
    }
    .nav-logo {
      font-family: "DM Serif Display", serif;
      font-size: 22px;
      color: var(--navy);
      text-decoration: none;
    }
    .nav-logo span { color: var(--blue); }

    .nav-links {
      display: flex;
      align-items: center;
      gap: 28px;
      list-style: none;
    }
    .nav-links a {
      text-decoration: none;
      font-size: 14px;
      font-weight: 500;
      color: var(--muted);
      transition: color .2s;
    }
    .nav-links a:hover, .nav-links a.active { color: var(--navy); }
    .nav-links a.active {
      color: var(--blue);
      border-bottom: 2px solid var(--blue);
      padding-bottom: 2px;
    }

    .nav-actions {
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .btn {
      padding: 8px 18px;
      border-radius: var(--radius);
      font-size: 13.5px;
      font-weight: 500;
      cursor: pointer;
      border: none;
      transition: all .2s;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }
    .btn-outline {
      background: transparent;
      border: 1.5px solid var(--border);
      color: var(--text);
    }
    .btn-outline:hover { border-color: var(--blue); color: var(--blue); }
    .btn-primary {
      background: var(--blue);
      color: #fff;
    }
    .btn-primary:hover { background: #1a66f0; }
    .btn-secondary {
      background: var(--border);
      color: var(--text);
      border: none;
      transition: background .2s;
    }
    .btn-secondary:hover { background: #d1d5db; }
    .btn-danger {
      background: var(--danger);
      color: #fff;
      border: none;
      transition: background .2s;
    }
    .btn-danger:hover { background: #dc2626; }

    .hero {
      background: linear-gradient(135deg, var(--navy) 0%, #1a3a7a 100%);
      padding: 60px 40px;
      text-align: center;
      position: relative;
      overflow: hidden;
    }
    .hero::before {
      content: "";
      position: absolute;
      top: -60px; right: -60px;
      width: 340px; height: 340px;
      background: rgba(45,121,255,.15);
      border-radius: 50%;
    }
    .hero::after {
      content: "";
      position: absolute;
      bottom: -80px; left: -40px;
      width: 260px; height: 260px;
      background: rgba(45,121,255,.1);
      border-radius: 50%;
    }
    .hero-badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: rgba(45,121,255,.2);
      border: 1px solid rgba(45,121,255,.4);
      color: #7aabff;
      font-size: 12px;
      font-weight: 600;
      padding: 5px 14px;
      border-radius: 99px;
      margin-bottom: 18px;
      text-transform: uppercase;
      letter-spacing: .05em;
    }
    .hero h1 {
      font-family: "DM Serif Display", serif;
      font-size: 42px;
      color: #fff;
      line-height: 1.2;
      margin-bottom: 14px;
      position: relative;
    }
    .hero p {
      color: rgba(255,255,255,.65);
      font-size: 15px;
      max-width: 520px;
      margin: 0 auto 28px;
      position: relative;
    }
    .hero-stats {
      display: flex;
      justify-content: center;
      gap: 40px;
      position: relative;
    }
    .hero-stat {
      text-align: center;
    }
    .hero-stat .num {
      font-family: "DM Serif Display", serif;
      font-size: 28px;
      color: #fff;
      line-height: 1;
    }
    .hero-stat .lbl {
      font-size: 12px;
      color: rgba(255,255,255,.5);
      margin-top: 3px;
    }

    .search-bar-wrap {
      max-width: 700px;
      margin: 0 auto;
      padding: 0 20px;
      margin-top: -26px;
      position: relative;
      z-index: 10;
    }
    .search-bar {
      background: #fff;
      border-radius: 14px;
      box-shadow: 0 4px 24px rgba(11,31,75,.12);
      padding: 14px 18px;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .search-bar i { color: var(--muted); font-size: 15px; }
    .search-bar input {
      flex: 1;
      border: none;
      outline: none;
      font-family: "DM Sans", sans-serif;
      font-size: 14px;
      color: var(--text);
      background: transparent;
    }
    .search-bar input::placeholder { color: var(--muted); }
    .search-bar .search-btn {
      background: var(--blue);
      color: #fff;
      border: none;
      border-radius: 8px;
      padding: 8px 18px;
      font-size: 13px;
      font-weight: 500;
      cursor: pointer;
      transition: background .2s;
    }
    .search-bar .search-btn:hover { background: #1a66f0; }

    .filters-row {
      max-width: 1200px;
      margin: 36px auto 0;
      padding: 0 20px;
      display: flex;
      align-items: center;
      gap: 10px;
      flex-wrap: wrap;
    }
    .filter-label {
      font-size: 13px;
      font-weight: 600;
      color: var(--navy);
      margin-right: 6px;
    }
    .filter-chip {
      padding: 6px 16px;
      border-radius: 99px;
      border: 1.5px solid var(--border);
      background: #fff;
      font-size: 13px;
      font-weight: 500;
      color: var(--muted);
      cursor: pointer;
      transition: all .2s;
    }
    .filter-chip:hover { border-color: var(--blue); color: var(--blue); }
    .filter-chip.active {
      background: var(--blue);
      border-color: var(--blue);
      color: #fff;
    }
    .filter-sep {
      width: 1px;
      height: 20px;
      background: var(--border);
      margin: 0 6px;
    }
    .sort-select {
      margin-left: auto;
      padding: 7px 14px;
      border-radius: 8px;
      border: 1.5px solid var(--border);
      font-family: "DM Sans", sans-serif;
      font-size: 13px;
      color: var(--text);
      background: #fff;
      cursor: pointer;
      outline: none;
    }

    .content {
      max-width: 1400px;
      margin: 28px auto 60px;
      padding: 0 20px;
      display: grid;
      grid-template-columns: 1fr 280px;
      gap: 28px;
      align-items: start;
    }

    .events-grid { 
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 20px;
    }

    .event-card {
      background: #fff;
      border-radius: 14px;
      border: 1px solid var(--border);
      overflow: hidden;
      transition: box-shadow .3s, transform .3s;
      cursor: pointer;
      display: flex;
      flex-direction: column;
      height: 100%;
    }
    .event-card:hover {
      box-shadow: 0 12px 32px rgba(11,31,75,.15);
      transform: translateY(-4px);
    }

    .event-card-image {
      width: 100%;
      height: 180px;
      background: linear-gradient(135deg, var(--navy) 0%, #1a3a7a 100%);
      object-fit: cover;
      display: block;
    }

    .event-card-date-badge {
      position: absolute;
      top: 12px;
      right: 12px;
      background: var(--navy);
      color: #fff;
      padding: 8px 12px;
      border-radius: 8px;
      font-size: 13px;
      font-weight: 600;
      display: flex;
      flex-direction: column;
      align-items: center;
      line-height: 1.2;
    }
    .event-card-date-badge .day {
      font-size: 16px;
      font-weight: 700;
    }
    .event-card-date-badge .month {
      font-size: 10px;
      opacity: .8;
    }

    .event-card-body {
      flex: 1;
      padding: 16px;
      display: flex;
      flex-direction: column;
    }
    .event-card-top {
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      gap: 10px;
      margin-bottom: 10px;
    }
    .event-card-title {
      font-size: 16px;
      font-weight: 600;
      color: var(--navy);
      line-height: 1.3;
    }
    .event-badge {
      display: inline-flex;
      align-items: center;
      gap: 4px;
      font-size: 11px;
      font-weight: 600;
      padding: 3px 10px;
      border-radius: 99px;
      white-space: nowrap;
    }
    .badge-tech    { background: #eff6ff; color: #1d4ed8; }
    .badge-culture { background: #fdf4ff; color: #7c3aed; }
    .badge-sport   { background: #f0fdf4; color: #16a34a; }
    .badge-emploi  { background: #fff7ed; color: #c2410c; }
    .badge-art     { background: #fef2f2; color: #b91c1c; }

    .event-card-meta {
      display: flex;
      align-items: center;
      gap: 12px;
      font-size: 12.5px;
      color: var(--muted);
      margin-bottom: 12px;
      flex-wrap: wrap;
    }
    .event-card-meta span {
      display: flex;
      align-items: center;
      gap: 5px;
    }
    .event-card-desc {
      font-size: 13.5px;
      color: #6b7280;
      line-height: 1.5;
      margin-bottom: 14px;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
      flex-grow: 1;
    }
    .event-card-footer {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      padding-top: 12px;
      border-top: 1px solid var(--border);
    }
    .event-organizer {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 12.5px;
      color: var(--muted);
    }
    .org-avatar {
      width: 26px; height: 26px;
      border-radius: 50%;
      background: var(--blue);
      color: #fff;
      font-size: 11px;
      font-weight: 600;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .event-actions {
      display: flex;
      align-items: center;
      gap: 8px;
    }
    .btn-sm {
      padding: 6px 14px;
      font-size: 12.5px;
      border-radius: 8px;
    }
    .btn-ghost {
      background: transparent;
      border: 1.5px solid var(--border);
      color: var(--muted);
    }
    .btn-ghost:hover { border-color: var(--blue); color: var(--blue); }

    .capacity-wrap {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 12px;
      color: var(--muted);
    }
    .capacity-bar {
      flex: 1;
      height: 5px;
      background: #e5e7eb;
      border-radius: 99px;
      overflow: hidden;
    }
    .capacity-fill {
      height: 100%;
      border-radius: 99px;
      background: var(--blue);
      transition: width .4s;
    }
    .capacity-fill.full { background: var(--danger); }
    .capacity-fill.high { background: var(--warning); }

    .sidebar-panel { display: flex; flex-direction: column; gap: 18px; }

    .panel-card {
      background: #fff;
      border-radius: 14px;
      border: 1px solid var(--border);
      padding: 20px;
    }
    .panel-title {
      font-size: 14px;
      font-weight: 600;
      color: var(--navy);
      margin-bottom: 14px;
      display: flex;
      align-items: center;
      gap: 8px;
    }
    .panel-title i { color: var(--blue); }

    .mini-event {
      display: flex;
      gap: 12px;
      align-items: flex-start;
      padding: 10px 0;
      border-bottom: 1px solid #f3f4f6;
    }
    .mini-event:last-child { border-bottom: none; padding-bottom: 0; }
    .mini-date {
      min-width: 40px;
      text-align: center;
      background: var(--light);
      border-radius: 8px;
      padding: 6px 4px;
    }
    .mini-date .d { font-weight: 700; font-size: 16px; color: var(--navy); line-height: 1; }
    .mini-date .m { font-size: 10px; color: var(--blue); font-weight: 600; text-transform: uppercase; }
    .mini-info .mini-title {
      font-size: 13px;
      font-weight: 600;
      color: var(--navy);
      line-height: 1.3;
      margin-bottom: 3px;
    }
    .mini-info .mini-loc {
      font-size: 11.5px;
      color: var(--muted);
      display: flex;
      align-items: center;
      gap: 4px;
    }

    .cat-item {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 9px 0;
      border-bottom: 1px solid #f3f4f6;
      cursor: pointer;
      transition: color .15s;
    }
    .cat-item:last-child { border-bottom: none; }
    .cat-item:hover .cat-name { color: var(--blue); }
    .cat-name {
      font-size: 13.5px;
      color: var(--text);
      display: flex;
      align-items: center;
      gap: 8px;
      font-weight: 500;
    }
    .cat-count {
      font-size: 12px;
      background: var(--light);
      color: var(--muted);
      padding: 2px 9px;
      border-radius: 99px;
      font-weight: 600;
    }

    .modal-overlay {
      position: fixed;
      inset: 0;
      background: rgba(11,31,75,.35);
      backdrop-filter: blur(3px);
      z-index: 200;
      display: none;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }
    .modal-overlay.open { display: flex; }

    .modal {
      background: #fff;
      border-radius: 16px;
      width: 100%;
      max-width: 500px;
      box-shadow: 0 20px 60px rgba(11,31,75,.2);
      animation: slideUp .25s ease;
    }
    @keyframes slideUp {
      from { transform: translateY(24px); opacity: 0; }
      to   { transform: translateY(0);    opacity: 1; }
    }
    .modal-header {
      padding: 20px 24px 16px;
      border-bottom: 1px solid var(--border);
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    .modal-title {
      font-size: 16px;
      font-weight: 600;
      color: var(--navy);
    }
    .modal-close {
      background: none;
      border: none;
      color: var(--muted);
      cursor: pointer;
      font-size: 18px;
      padding: 4px;
      transition: color .15s;
    }
    .modal-close:hover { color: var(--danger); }
    .modal-body { padding: 22px 24px; }
    .event-summary {
      background: var(--light);
      border-radius: 10px;
      padding: 14px 16px;
      margin-bottom: 20px;
    }
    .event-summary .es-title {
      font-weight: 600;
      color: var(--navy);
      font-size: 14px;
      margin-bottom: 6px;
    }
    .event-summary .es-meta {
      font-size: 12.5px;
      color: var(--muted);
      display: flex;
      gap: 14px;
      flex-wrap: wrap;
    }
    .event-summary .es-meta span {
      display: flex;
      align-items: center;
      gap: 5px;
    }
    .form-group { margin-bottom: 16px; }
    .form-label {
      display: block;
      font-size: 12px;
      font-weight: 600;
      color: var(--navy);
      margin-bottom: 6px;
      text-transform: uppercase;
      letter-spacing: .04em;
    }
    .form-control {
      width: 100%;
      padding: 10px 14px;
      border: 1.5px solid var(--border);
      border-radius: var(--radius);
      font-family: "DM Sans", sans-serif;
      font-size: 14px;
      color: var(--text);
      outline: none;
      transition: border-color .2s;
    }
    .form-control:focus { border-color: var(--blue); }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .modal-footer {
      padding: 16px 24px 20px;
      display: flex;
      gap: 10px;
      justify-content: flex-end;
    }

    .detail-modal { max-width: 620px; }
    .detail-header-img {
      height: 200px;
      background: linear-gradient(135deg, var(--navy) 0%, #1a3a7a 100%);
      border-radius: 16px 16px 0 0;
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
      overflow: hidden;
    }
    .detail-header-img::before {
      content: "";
      position: absolute;
      inset: 0;
      background: url("assets/images/event/e1.png") center/cover no-repeat;
      opacity: .35;
    }
    .detail-header-img .detail-badge {
      position: relative;
      font-size: 13px;
      font-weight: 600;
      background: rgba(255,255,255,.15);
      border: 1px solid rgba(255,255,255,.3);
      color: #fff;
      padding: 6px 18px;
      border-radius: 99px;
    }
    .detail-body { padding: 24px; }
    .detail-title {
      font-family: "DM Serif Display", serif;
      font-size: 22px;
      color: var(--navy);
      margin-bottom: 10px;
      line-height: 1.3;
    }
    .detail-meta {
      display: flex;
      flex-wrap: wrap;
      gap: 16px;
      margin-bottom: 18px;
    }
    .detail-meta-item {
      display: flex;
      align-items: center;
      gap: 7px;
      font-size: 13px;
      color: var(--muted);
    }
    .detail-meta-item i { color: var(--blue); font-size: 13px; }
    .detail-desc {
      font-size: 14px;
      color: #4b5563;
      line-height: 1.65;
      margin-bottom: 20px;
    }
    .detail-organizer {
      display: flex;
      align-items: center;
      gap: 12px;
      background: var(--light);
      border-radius: 10px;
      padding: 12px 16px;
      margin-bottom: 18px;
    }
    .org-avatar-lg {
      width: 42px; height: 42px;
      border-radius: 50%;
      background: var(--blue);
      color: #fff;
      font-size: 15px;
      font-weight: 600;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .org-info .org-name { font-weight: 600; font-size: 14px; color: var(--navy); }
    .org-info .org-role { font-size: 12px; color: var(--muted); }

    #toast-container {
      position: fixed;
      bottom: 24px;
      right: 24px;
      z-index: 999;
      display: flex;
      flex-direction: column;
      gap: 8px;
    }
    .toast {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 12px 18px;
      border-radius: 10px;
      font-size: 13.5px;
      font-weight: 500;
      box-shadow: 0 4px 20px rgba(0,0,0,.12);
      animation: fadeIn .25s ease;
      color: #fff;
    }
    .toast.success { background: #16a34a; }
    .toast.error   { background: var(--danger); }
    @keyframes fadeIn {
      from { opacity: 0; transform: translateX(20px); }
      to   { opacity: 1; transform: translateX(0); }
    }

    .empty-state {
      text-align: center;
      padding: 60px 20px;
      display: none;
    }
    .empty-state i { font-size: 40px; color: var(--border); margin-bottom: 14px; }
    .empty-state p { color: var(--muted); font-size: 14px; }

    .pagination {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 6px;
      margin-top: 28px;
    }
    .page-btn {
      width: 36px; height: 36px;
      border-radius: 8px;
      border: 1.5px solid var(--border);
      background: #fff;
      font-size: 13px;
      font-weight: 500;
      color: var(--text);
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all .2s;
    }
    .page-btn:hover { border-color: var(--blue); color: var(--blue); }
    .page-btn.active { background: var(--blue); border-color: var(--blue); color: #fff; }

    footer {
      background: var(--navy);
      color: rgba(255,255,255,.5);
      text-align: center;
      padding: 22px 40px;
      font-size: 13px;
      margin-top: auto;
    }
    footer a { color: #7aabff; text-decoration: none; }

    @media (max-width: 1024px) {
      .content {
        grid-template-columns: 1fr;
      }
      .events-grid {
        grid-template-columns: repeat(2, 1fr);
      }
    }

    @media (max-width: 768px) {
      .events-grid {
        grid-template-columns: 1fr;
        gap: 16px;
      }
      .event-card-title {
        font-size: 15px;
      }
      .filters-row {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
      }
      .sort-select {
        width: 100%;
        margin-left: 0;
      }
      nav {
        padding: 0 20px;
      }
    }

    @media (max-width: 480px) {
      .events-grid {
        grid-template-columns: 1fr;
      }
      .search-bar {
        flex-wrap: wrap;
      }
      .search-bar input {
        width: 100%;
        order: 1;
      }
      .search-bar .search-btn {
        order: 3;
        width: 100%;
      }
      .event-card-image {
        height: 150px;
      }
    }
  </style>
</head>
<body>

<?php
$events = $events ?? [];
$inscriptionsCount = $inscriptionsCount ?? 0;
$basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
$basePath = ($basePath && $basePath !== '.') ? $basePath : '';
?>

<!-- NAV -->
<nav>
  <a class="nav-logo" href="/">Joby<span>find</span></a>
  <ul class="nav-links">
    <li><a href="<?php echo htmlspecialchars($basePath . '/admin/events', ENT_QUOTES, 'UTF-8'); ?>" class="active">Événements</a></li>
  </ul>
</nav>

<!-- HERO -->
<section class="hero">
  <div class="hero-badge"><i class="fa-solid fa-calendar-star"></i> Agenda des événements</div>
  <h1>Découvrez les événements<br>qui font avancer votre carrière</h1>
  <p>Conférences, ateliers, hackathons, salons… Restez connecté à l'écosystème professionnel tunisien.</p>
  <div class="hero-stats">
    <div class="hero-stat"><div class="num"><?php echo count($events); ?></div><div class="lbl">Événements</div></div>
        <div class="hero-stat"><div class="num"><?php echo (int) $inscriptionsCount; ?></div><div class="lbl">Participants</div></div>
  </div>
</section>

<!-- SEARCH -->
<div class="search-bar-wrap">
  <div class="search-bar">
    <i class="fa fa-magnifying-glass"></i>
    <input type="text" id="search-input" placeholder="Rechercher un événement, un lieu, un organisateur…">
    <button class="search-btn" onclick="filterEvents()">Rechercher</button>
  </div>
</div>

<!-- FILTERS -->
<div class="filters-row">
  <span class="filter-label">Filtrer :</span>
  <button class="filter-chip active" data-filter="all" onclick="setFilter(this,'all')">Tous</button>
  <button class="filter-chip" data-filter="tech" onclick="setFilter(this,'tech')">Technologie</button>
  <button class="filter-chip" data-filter="culture" onclick="setFilter(this,'culture')">Culture</button>
  <button class="filter-chip" data-filter="emploi" onclick="setFilter(this,'emploi')">Emploi</button>
  <div class="filter-sep"></div>
  <select class="sort-select" id="sort-select" onchange="filterEvents()">
    <option value="date-asc">Date croissante</option>
    <option value="date-desc">Date décroissante</option>
    <option value="popularity">Plus populaires</option>
  </select>
</div>

<!-- CONTENT -->
<div class="content">
  <!-- EVENTS GRID -->
  <div class="events-grid" id="events-grid">
    <?php foreach ($events as $event): ?>
      <?php
        $title = $event->getTitre();
        $date = $event->getDate();
        $lieu = $event->getLieu();
        $organisateurId = $event->getIdOrganisateur();
        $description = $event->getDescription();
        $image = $event->getImage() ?: 'assets/images/event/default-event.jpg';
        $titreSlug = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        $organisateurLabel = $organisateurId ? 'Organisateur #' . $organisateurId : 'Organisateur inconnu';
      ?>
      <div class="event-card" onclick="viewEventDetails(<?php echo $event->getId(); ?>)">
        <div style="position: relative;">
          <img src="/projetweb_avec_evenements/public/<?php echo htmlspecialchars($image, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo $titreSlug; ?>" class="event-card-image" onerror="this.src='/projetweb_avec_evenements/public/assets/images/event/default-event.jpg'">
          <div class="event-card-date-badge">
            <div class="day"><?php echo date('d', strtotime($date)); ?></div>
            <div class="month"><?php echo date('M', strtotime($date)); ?></div>
          </div>
        </div>
        <div class="event-card-body">
          <div class="event-card-top">
            <div>
              <div class="event-card-title"><?php echo $titreSlug; ?></div>
              <span class="event-badge badge-tech">Général</span>
            </div>
          </div>
          <div class="event-card-meta">
            <span><i class="fa fa-location-dot"></i> <?php echo htmlspecialchars($lieu, ENT_QUOTES, 'UTF-8'); ?></span>
          </div>
          <p class="event-card-desc"><?php echo htmlspecialchars(substr($description, 0, 85), ENT_QUOTES, 'UTF-8'); ?></p>
          <div class="event-card-footer">
            <div class="event-organizer">
              <div class="org-avatar"><?php echo strtoupper(substr($organisateurLabel, 0, 2)); ?></div>
              <span><?php echo htmlspecialchars($organisateurLabel, ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
            <button class="btn btn-sm btn-primary" onclick="event.stopPropagation(); openInscriptionModalWithEvent(<?php echo $event->getId(); ?>)" title="S'inscrire">
              <i class="fa fa-plus"></i> S'inscrire
            </button>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- SIDEBAR -->
  <div class="sidebar-panel">
    <!-- Upcoming Events -->
    <div class="panel-card">
      <div class="panel-title"><i class="fa fa-calendar"></i> Prochains Événements</div>
      <div id="upcoming-events">
        <?php 
          $sorted = $events;
          usort($sorted, function($a, $b) { return strtotime($a->getDate()) - strtotime($b->getDate()); });
          foreach (array_slice($sorted, 0, 3) as $event): 
            $eventDate = $event->getDate();
        ?>
          <div class="mini-event">
            <div class="mini-date">
              <div class="d"><?php echo date('d', strtotime($eventDate)); ?></div>
              <div class="m"><?php echo date('M', strtotime($eventDate)); ?></div>
            </div>
            <div class="mini-info">
              <div class="mini-title"><?php echo htmlspecialchars($event->getTitre(), ENT_QUOTES, 'UTF-8'); ?></div>
              <div class="mini-loc"><i class="fa fa-location-dot"></i> <?php echo htmlspecialchars($event->getLieu(), ENT_QUOTES, 'UTF-8'); ?></div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Categories -->
    <div class="panel-card">
      <div class="panel-title"><i class="fa fa-filter"></i> Catégories</div>
      <div>
        <div class="cat-item" onclick="setFilter(this, 'all')">
          <span class="cat-name"><i class="fa fa-check"></i> Toutes</span>
          <span class="cat-count"><?php echo count($events); ?></span>
        </div>
        <div class="cat-item" onclick="setFilter(this, 'tech')">
          <span class="cat-name">Technologie</span>
          <span class="cat-count">0</span>
        </div>
        <div class="cat-item" onclick="setFilter(this, 'culture')">
          <span class="cat-name">Culture</span>
          <span class="cat-count">0</span>
        </div>
        <div class="cat-item" onclick="setFilter(this, 'emploi')">
          <span class="cat-name">Emploi</span>
          <span class="cat-count">0</span>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- MODALS -->
<div class="modal-overlay" id="inscription-modal">
  <div class="modal">
    <div class="modal-header">
      <p class="modal-title">S'inscrire à l'événement</p>
      <button class="modal-close" onclick="closeModal('inscription-modal')"><i class="fa fa-xmark"></i></button>
    </div>
    <div class="modal-body">
      <div class="event-summary" id="event-summary">
        <div class="es-title" id="event-title"></div>
        <div class="es-meta">
          <span><i class="fa fa-calendar"></i> <span id="event-date-time"></span></span>
          <span><i class="fa fa-location-dot"></i> <span id="event-location"></span></span>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Prénom *</label>
        <input type="text" class="form-control" id="firstname" placeholder="Jean">
      </div>
      <div class="form-group">
        <label class="form-label">Nom *</label>
        <input type="text" class="form-control" id="lastname" placeholder="Dupont">
      </div>
      <div class="form-group">
        <label class="form-label">Email *</label>
        <input type="email" class="form-control" id="email" placeholder="jean@email.com">
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary" onclick="closeModal('inscription-modal')">Annuler</button>
      <button class="btn btn-primary" onclick="submitInscription()">S'inscrire</button>
    </div>
  </div>
</div>

<div class="modal-overlay" id="detail-modal">
  <div class="modal detail-modal">
    <div class="detail-header-img">
      <div class="detail-badge" id="detail-category"></div>
    </div>
    <div class="detail-body">
      <div class="detail-title" id="detail-title"></div>
      <div class="detail-meta" id="detail-meta"></div>
      <p class="detail-desc" id="detail-desc"></p>
      <div class="detail-organizer">
        <div class="org-avatar-lg" id="detail-org-avatar">JD</div>
        <div class="org-info">
          <div class="org-name" id="detail-org-name"></div>
          <div class="org-role">Organisateur</div>
        </div>
      </div>
      <button class="btn btn-primary" style="width:100%;" onclick="inscribeToEvent()">S'inscrire maintenant</button>
    </div>
    <div style="padding: 0 24px 20px;">
      <button class="btn btn-outline" style="width:100%;" onclick="closeModal('detail-modal')">Fermer</button>
    </div>
  </div>
</div>

<!-- TOAST -->
<div id="toast-container"></div>

<!-- FOOTER -->
<footer>
  <p>&copy; 2025 Jobyfind — Tous droits reservés | <a href="#">Politique de confidentialité</a></p>
</footer>

<script>
  let allEvens = <?php echo json_encode(array_map(function($e) {
    return [
      'id' => $e->getId(),
      'titre' => $e->getTitre(),
      'date' => $e->getDate(),
      'heure' => '',
      'lieu' => $e->getLieu(),
      'categorie' => 'general',
      'organisateur' => $e->getIdOrganisateur() ? 'Organisateur #' . $e->getIdOrganisateur() : 'Organisateur inconnu',
      'intervenants' => '',
      'programme' => $e->getDescription() ?: $e->getTitre(),
      'max' => 0,
      'inscrits' => 0
    ];
  }, $events)); ?>;

  let currentFilter = 'all';
  let currentEventForModal = null;
  let currentEventIdForInscription = null;

  function openInscriptionModalWithEvent(eventId) {
    const event = allEvens.find(e => e.id === eventId);
    if (!event) return;
    
    currentEventIdForInscription = eventId;
    document.getElementById('event-title').textContent = event.titre;
    document.getElementById('event-date-time').textContent = `${event.date} à ${event.heure || '--:--'}`;
    document.getElementById('event-location').textContent = event.lieu;
    
    // Reset form
    document.getElementById('firstname').value = '';
    document.getElementById('lastname').value = '';
    document.getElementById('email').value = '';
    
    openModal('inscription-modal');
  }

  function setFilter(elem, filter) {
    document.querySelectorAll('.filter-chip, .cat-item').forEach(e => e.classList.remove('active'));
    if (elem) elem.classList.add('active');
    currentFilter = filter;
    filterEvents();
  }

  function filterEvents() {
    const search = document.getElementById('search-input').value.toLowerCase();
    const sort = document.getElementById('sort-select').value;
    
    let filtered = allEvens.filter(e => {
      const matchFilter = currentFilter === 'all' || e.categorie === currentFilter;
      const matchSearch = !search || e.titre.toLowerCase().includes(search) || e.lieu.toLowerCase().includes(search) || e.organisateur.toLowerCase().includes(search);
      return matchFilter && matchSearch;
    });

    if (sort === 'date-desc') filtered.sort((a, b) => new Date(b.date) - new Date(a.date));
    else if (sort === 'popularity') filtered.sort((a, b) => b.inscrits - a.inscrits);
    else filtered.sort((a, b) => new Date(a.date) - new Date(b.date));

    const grid = document.getElementById('events-grid');
    grid.innerHTML = filtered.map(e => `
      <div class="event-card" onclick="viewEventDetails(${e.id})">
        <div class="event-card-date">
          <div class="day">${new Date(e.date).getDate().toString().padStart(2, '0')}</div>
          <div class="month">${new Date(e.date).toLocaleDateString('fr-FR', {month: 'short'}).toUpperCase()}</div>
          <div class="year">${new Date(e.date).getFullYear()}</div>
        </div>
        <div class="event-card-body">
          <div class="event-card-top">
            <div class="event-card-title">${e.titre}</div>
            <span class="event-badge badge-${e.categorie}">${e.categorie.charAt(0).toUpperCase() + e.categorie.slice(1)}</span>
          </div>
          <div class="event-card-meta">
            <span><i class="fa fa-clock"></i> ${e.heure}</span>
            <span><i class="fa fa-location-dot"></i> ${e.lieu}</span>
          </div>
          <p class="event-card-desc">${e.programme.substring(0, 100)}...</p>
          <div class="event-card-footer">
            <div class="event-organizer">
              <div class="org-avatar">${e.organisateur.substring(0, 2).toUpperCase()}</div>
              <div>${e.organisateur}</div>
            </div>
            <div class="event-actions">
              <button class="btn btn-sm btn-primary" onclick="event.stopPropagation(); openInscriptionModalWithEvent(${e.id})" title="S'inscrire">
                <i class="fa fa-plus"></i> S'inscrire
              </button>
            </div>
          </div>
        </div>
      </div>
    `).join('');
  }

  function viewEventDetails(id) {
    const e = allEvens.find(ev => ev.id === id);
    currentEventForModal = e;
    
    const meta = `
      <span><i class="fa fa-calendar"></i> ${new Date(e.date).toLocaleDateString('fr-FR')}</span>
      <span><i class="fa fa-clock"></i> ${e.heure}</span>
      <span><i class="fa fa-location-dot"></i> ${e.lieu}</span>
    `;
    
    document.getElementById('detail-title').textContent = e.titre;
    document.getElementById('detail-meta').innerHTML = meta;
    document.getElementById('detail-desc').textContent = e.programme;
    document.getElementById('detail-category').textContent = e.categorie;
    document.getElementById('detail-org-avatar').textContent = e.organisateur.substring(0, 2).toUpperCase();
    document.getElementById('detail-org-name').textContent = e.organisateur;
    
    openModal('detail-modal');
  }

  function submitInscription() {
    const userIdSelect = document.getElementById('user-select').value;
    let userId = userIdSelect ? parseInt(userIdSelect) : null;
    
    if (!userId) {
      showToast('Veuillez sélectionner un utilisateur', 'error');
      return;
    }

    if (!currentEventIdForInscription) {
      showToast('Erreur : événement non trouvé', 'error');
      return;
    }

    // Call API to register
    fetch('/projetweb_avec_evenements/public/index.php/inscriptions/create', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        idUtilisateur: userId,
        idEvenement: currentEventIdForInscription,
        statut: 'Confirmée'
      })
    })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        showToast('✓ Inscription confirmée!', 'success');
        closeModal('inscription-modal');
      } else {
        showToast('Erreur : ' + (data.error || 'Inscription échouée'), 'error');
      }
    })
    .catch(e => {
      console.error(e);
      showToast('Erreur réseau : ' + e.message, 'error');
    });
  }

  function openModal(id) {
    document.getElementById(id).classList.add('open');
  }

  function closeModal(id) {
    document.getElementById(id).classList.remove('open');
  }

  function showToast(msg, type) {
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerHTML = `<i class="fa fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i> ${msg}`;
    document.getElementById('toast-container').appendChild(toast);
    setTimeout(() => toast.remove(), 3500);
  }

  document.querySelectorAll('.modal-overlay').forEach(modal => {
    modal.addEventListener('click', e => {
      if (e.target === modal) closeModal(modal.id);
    });
  });

  // Initialize
  loadUsersDropdown();
  filterEvents();
</script>

</body>
</html>



