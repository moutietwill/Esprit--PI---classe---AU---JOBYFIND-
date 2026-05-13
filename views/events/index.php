<?php
require_once __DIR__ . '/../../config/QRCode.php';

$events = $events ?? [];
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
$publicBase = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
$publicBase = ($publicBase && $publicBase !== '.' && $publicBase !== '/') ? $publicBase : '';
$indexBase = $publicBase . '/index.php';

$url = $url ?? static function (string $path = '') use ($indexBase): string {
    $path = '/' . ltrim($path, '/');
    return $path === '/' ? $indexBase : $indexBase . $path;
};
$asset = $asset ?? static function (string $path = '') use ($publicBase): string {
    if (preg_match('#^(https?:)?//#i', $path) || strpos($path, 'data:') === 0) {
        return $path;
    }
    $path = preg_replace('#^/?public/#', '', $path);
    return $publicBase . '/' . ltrim($path, '/');
};
?>
<?php
    require_once(__DIR__ . '/../../config/session.php');
    startAppSession();
    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . $url('/login') . '?msg=' . urlencode('Veuillez vous connecter pour accéder aux événements.'));
        exit;
    }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Jobyfind - Événements</title>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Serif+Display&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
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
    .btn-qr {
      background: #f0f2f8;
      color: var(--blue);
      border: 1px solid var(--border);
      margin-left: 6px;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .btn-qr:hover { background: #e2e8f0; border-color: var(--blue); }

    .event-qr-badge {
      position: absolute;
      top: 12px;
      right: 12px;
      width: 58px;
      height: 58px;
      background: rgba(255,255,255,0.96);
      border-radius: 10px;
      padding: 5px;
      box-shadow: 0 3px 12px rgba(0,0,0,0.28);
      cursor: pointer;
      transition: transform .2s, box-shadow .2s;
      z-index: 5;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      gap: 2px;
    }
    .event-qr-badge:hover {
      transform: scale(1.13);
      box-shadow: 0 6px 22px rgba(0,0,0,0.38);
    }
    .event-qr-badge img {
      width: 100%;
      height: 100%;
      display: block;
      border-radius: 5px;
      object-fit: contain;
    }
    .qr-tooltip {
      position: absolute;
      bottom: calc(100% + 7px);
      right: 0;
      background: rgba(11,31,75,0.92);
      color: #fff;
      font-size: 10px;
      font-weight: 600;
      padding: 4px 8px;
      border-radius: 6px;
      white-space: nowrap;
      opacity: 0;
      pointer-events: none;
      transition: opacity .2s;
    }
    .event-qr-badge:hover .qr-tooltip { opacity: 1; }

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
    .search-clear {
      width: 30px;
      height: 30px;
      border: none;
      border-radius: 8px;
      background: #eef2f7;
      color: var(--muted);
      cursor: pointer;
      display: none;
      align-items: center;
      justify-content: center;
      transition: color .15s, background .15s;
    }
    .search-clear.show { display: inline-flex; }
    .search-clear:hover {
      background: #e2e8f0;
      color: var(--navy);
    }
    .search-feedback {
      max-width: 700px;
      margin: 8px auto 0;
      padding: 0 20px;
      color: var(--muted);
      font-size: 12.5px;
      text-align: center;
      min-height: 18px;
    }

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
      max-width: 1200px;
      margin: 28px auto 60px;
      padding: 0 20px;
      display: grid;
      grid-template-columns: 1fr 300px;
      gap: 28px;
      align-items: start;
    }

    .map-section {
      max-width: 1200px;
      margin: 30px auto 0;
      padding: 0 20px;
      display: grid;
      grid-template-columns: minmax(0, 1fr) 320px;
      gap: 22px;
      align-items: stretch;
    }
    .map-card, .ai-map-card {
      background: #fff;
      border: 1px solid var(--border);
      border-radius: 14px;
      overflow: hidden;
      box-shadow: 0 6px 22px rgba(11,31,75,.06);
    }
    .map-header {
      padding: 16px 18px;
      border-bottom: 1px solid var(--border);
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
    }
    .map-title {
      font-size: 15px;
      font-weight: 700;
      color: var(--navy);
      display: flex;
      align-items: center;
      gap: 8px;
    }
    .map-title i { color: var(--blue); }
    .map-hint { font-size: 12px; color: var(--muted); }
    #events-map {
      width: 100%;
      height: 360px;
      z-index: 1;
    }
    .ai-map-card {
      padding: 18px;
      display: flex;
      flex-direction: column;
      gap: 14px;
    }
    .ai-badge {
      width: max-content;
      border-radius: 99px;
      padding: 5px 12px;
      background: #eff6ff;
      color: var(--blue);
      font-size: 11px;
      font-weight: 800;
      letter-spacing: .06em;
      text-transform: uppercase;
    }
    .ai-map-card h3 {
      color: var(--navy);
      font-size: 17px;
      line-height: 1.25;
    }
    .ai-map-card p {
      color: #64748b;
      font-size: 13.5px;
      line-height: 1.55;
    }
    .ai-location-list {
      display: flex;
      flex-direction: column;
      gap: 8px;
    }
    .ai-location-item {
      border: 1px solid #eef2f7;
      border-radius: 10px;
      padding: 9px 10px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 8px;
      font-size: 13px;
      cursor: pointer;
      transition: border-color .15s, background .15s;
    }
    .ai-location-item:hover { border-color: var(--blue); background: #f8fbff; }
    .ai-location-item strong { color: var(--navy); }
    .ai-location-item span { color: var(--muted); font-size: 12px; }
    .map-popup-title { font-weight: 700; color: var(--navy); margin-bottom: 4px; }
    .map-popup-meta { color: #64748b; font-size: 12px; margin-bottom: 8px; }
    .map-popup-btn {
      border: 0;
      background: var(--blue);
      color: #fff;
      border-radius: 7px;
      padding: 6px 10px;
      font-size: 12px;
      cursor: pointer;
    }

    .events-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 18px;
      padding-bottom: 10px;
    }

    .event-card {
      background: #fff;
      border-radius: 14px;
      border: 1px solid var(--border);
      display: flex;
      flex-direction: column;
      width: 100%;
      gap: 0;
      overflow: hidden;
      transition: box-shadow .2s, transform .2s;
      cursor: pointer;
    }
    .event-card:hover {
      box-shadow: 0 6px 28px rgba(11,31,75,.1);
      transform: translateY(-2px);
    }

    .event-img-wrap {
      width: 100%;
      height: 180px;
      position: relative;
    }
    .event-img-wrap img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }
    .event-card-date {
      position: absolute;
      top: 12px;
      left: 12px;
      min-width: 56px;
      background: rgba(11, 31, 75, 0.85);
      backdrop-filter: blur(4px);
      border-radius: 10px;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 10px 8px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    .event-card-date .day {
      font-family: "DM Serif Display", serif;
      font-size: 24px;
      color: #fff;
      line-height: 1;
    }
    .event-card-date .month {
      font-size: 10px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: .08em;
      color: #7aabff;
      margin-top: 4px;
    }
    .event-card-date .year {
      display: none;
    }

    .event-card-body {
      flex: 1;
      padding: 18px 20px;
    }
    .event-card-top {
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      gap: 10px;
      margin-bottom: 8px;
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
      gap: 18px;
      font-size: 12.5px;
      color: var(--muted);
      margin-bottom: 10px;
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
      line-height: 1.55;
      margin-bottom: 14px;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }
    .event-card-footer {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
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
      background: url("<?php echo htmlspecialchars($asset('/assets/images/event/e1.png'), ENT_QUOTES, 'UTF-8'); ?>") center/cover no-repeat;
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
    @media (max-width: 980px) {
      .map-section, .content {
        grid-template-columns: 1fr;
      }
    }
    @media (max-width: 640px) {
      nav {
        height: auto;
        padding: 16px 20px;
        align-items: flex-start;
        gap: 14px;
        flex-direction: column;
      }
      .nav-links {
        gap: 16px;
        flex-wrap: wrap;
      }
      #events-map {
        height: 300px;
      }
    }
  </style>
</head>
<body>

<!-- NAV -->
<nav>
  <a class="nav-logo" href="<?php echo htmlspecialchars($url('/'), ENT_QUOTES, 'UTF-8'); ?>">Joby<span>find</span></a>
  <ul class="nav-links">
    <li><a href="<?php echo htmlspecialchars($url('/'), ENT_QUOTES, 'UTF-8'); ?>">Accueil</a></li>
    <li><a href="<?php echo $legacyBlogUrl; ?>">Blog</a></li>
    <li><a class="active" href="<?php echo htmlspecialchars($url('/events'), ENT_QUOTES, 'UTF-8'); ?>">Événements</a></li>
    <li><a href="#">À propos</a></li>
    <li><a href="#">Contact</a></li>
  </ul>
</nav>

<!-- HERO -->
<section class="hero">
  <div class="hero-badge"><i class="fa-solid fa-calendar-star"></i> Agenda des événements</div>
  <h1>Découvrez les événements<br>qui font avancer votre carrière</h1>
  <p>Conférences, ateliers, hackathons, salons… Restez connecté à l'écosystème professionnel tunisien.</p>
  <div class="hero-stats">
    <div class="hero-stat"><div class="num"><?php echo count($events); ?></div><div class="lbl">Événements</div></div>
    <div class="hero-stat"><div class="num">12</div><div class="lbl">Villes</div></div>
    <div class="hero-stat"><div class="num">0+</div><div class="lbl">Participants</div></div>
  </div>
</section>

<!-- SEARCH -->
<div class="search-bar-wrap">
  <div class="search-bar">
    <i class="fa fa-magnifying-glass"></i>
    <input type="text" id="search-input" placeholder="Rechercher un événement, un lieu, un organisateur…">
    <button class="search-clear" id="search-clear" type="button" onclick="clearSearch()" title="Effacer la recherche">
      <i class="fa fa-xmark"></i>
    </button>
    <button class="search-btn" onclick="filterEvents()">Rechercher</button>
  </div>
  <div class="search-feedback" id="search-feedback"></div>
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

<!-- MAP IA -->
<section class="map-section">
  <div class="map-card">
    <div class="map-header">
      <div class="map-title"><i class="fa-solid fa-map-location-dot"></i> Carte des événements</div>
      <div class="map-hint">Cliquez sur un marqueur pour voir l'événement</div>
    </div>
    <div id="events-map"></div>
  </div>
  <aside class="ai-map-card">
    <div class="ai-badge">Assistant IA</div>
    <h3>Analyse automatique des lieux</h3>
    <p id="ai-map-summary">Chargement de l'analyse des événements...</p>
    <div class="ai-location-list" id="ai-location-list"></div>
  </aside>
</section>

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
        $titreSlug = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        $organisateurLabel = $organisateurId ? 'Organisateur #' . $organisateurId : 'Organisateur inconnu';
        $eventImagePath = $event->getImage() ?: 'public/assets/images/event/default.jpg';
        $eventImage = htmlspecialchars($asset($eventImagePath), ENT_QUOTES, 'UTF-8');
        $qrEventUrl = QRCode::generateForEvent($event);
        $qrRegUrl   = QRCode::generateForRegistration($event);
      ?>
      <div class="event-card" onclick="viewEventDetails(<?php echo $event->getId(); ?>)">
        <div class="event-img-wrap">
          <img src="<?php echo $eventImage; ?>" alt="Image événement">
          <div class="event-card-date">
            <div class="day"><?php echo date('d', strtotime($date)); ?></div>
            <div class="month"><?php echo date('M', strtotime($date)); ?></div>
            <div class="year"><?php echo date('Y', strtotime($date)); ?></div>
          </div>
          <div class="event-qr-badge" onclick="event.stopPropagation(); showPublicQRModal('<?php echo $titreSlug; ?>', '<?php echo htmlspecialchars($qrEventUrl, ENT_QUOTES, 'UTF-8'); ?>', '<?php echo htmlspecialchars($qrRegUrl, ENT_QUOTES, 'UTF-8'); ?>')" title="Voir le QR Code">
            <img src="<?php echo htmlspecialchars($qrEventUrl, ENT_QUOTES, 'UTF-8'); ?>" alt="QR Code" loading="lazy">
            <span class="qr-tooltip">📱 QR Code</span>
          </div>
        </div>
        <div class="event-card-body">
          <div class="event-card-top">
            <div class="event-card-title"><?php echo $titreSlug; ?></div>
            <span class="event-badge badge-general">Général</span>
          </div>
          <div class="event-card-meta">
            <span><i class="fa fa-clock"></i> --:--</span>
            <span><i class="fa fa-location-dot"></i> <?php echo htmlspecialchars($lieu, ENT_QUOTES, 'UTF-8'); ?></span>
          </div>
          <p class="event-card-desc"><?php echo htmlspecialchars(substr($description, 0, 100), ENT_QUOTES, 'UTF-8'); ?>...</p>
          <div class="event-card-footer">
            <div class="event-organizer">
              <div class="org-avatar"><?php echo strtoupper(substr($organisateurLabel, 0, 2)); ?></div>
              <div><?php echo htmlspecialchars($organisateurLabel, ENT_QUOTES, 'UTF-8'); ?></div>
            </div>
            <div class="event-actions">
              <button class="btn btn-primary btn-sm" onclick="event.stopPropagation(); openInscriptionModal(<?php echo $event->getId(); ?>, <?php echo htmlspecialchars(json_encode($titreSlug), ENT_QUOTES, 'UTF-8'); ?>, '<?php echo htmlspecialchars($date, ENT_QUOTES, 'UTF-8'); ?>', '<?php echo htmlspecialchars($lieu, ENT_QUOTES, 'UTF-8'); ?>')">
                <i class="fa fa-plus"></i> S'inscrire
              </button>
            </div>
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
          <span><i class="fa fa-clock"></i> <span id="event-date-time"></span></span>
          <span><i class="fa fa-location-dot"></i> <span id="event-location"></span></span>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Prénom</label>
        <input type="text" class="form-control" id="firstname" placeholder="Jean">
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Nom</label>
          <input type="text" class="form-control" id="lastname" placeholder="Dupont">
        </div>
        <div class="form-group">
          <label class="form-label">Email</label>
          <input type="text" class="form-control" id="email" placeholder="jean@example.com">
        </div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-outline" onclick="closeModal('inscription-modal')">Annuler</button>
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

<!-- QR Code Modal -->
<div class="modal-overlay" id="public-qr-modal">
  <div class="modal" style="width: 600px;">
    <div class="modal-header" style="padding: 20px 24px 16px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between;">
      <div class="modal-title" id="public-qr-modal-title" style="font-size: 16px; font-weight: 600; color: var(--navy);">Codes QR</div>
      <button class="modal-close" onclick="closeModal('public-qr-modal')" style="background: none; border: none; color: var(--muted); cursor: pointer; font-size: 14px; padding: 4px;">
        <i class="fa fa-xmark"></i>
      </button>
    </div>
    <div class="modal-body" style="padding: 24px; display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
      <div style="text-align: center;">
        <h3 style="margin: 0 0 12px 0; font-size: 14px; color: var(--navy);">Détails de l'Événement</h3>
        <img id="public-qr-event-img" src="" alt="QR Code Événement" style="max-width: 220px; width: 100%; border: 2px solid var(--border); border-radius: 8px; padding: 8px; background: white;">
        <p style="margin: 12px 0 0 0; font-size: 12px; color: var(--muted);">Scannez pour voir les détails</p>
      </div>
      <div style="text-align: center;">
        <h3 style="margin: 0 0 12px 0; font-size: 14px; color: var(--navy);">Inscription à l'Événement</h3>
        <img id="public-qr-registration-img" src="" alt="QR Code Inscription" style="max-width: 220px; width: 100%; border: 2px solid var(--border); border-radius: 8px; padding: 8px; background: white;">
        <p style="margin: 12px 0 0 0; font-size: 12px; color: var(--muted);">Scannez pour s'inscrire</p>
      </div>
    </div>
    <div style="padding: 0 24px 20px;">
      <button class="btn btn-outline" style="width:100%;" onclick="closeModal('public-qr-modal')">Fermer</button>
    </div>
  </div>
</div>

<!-- TOAST -->
<div id="toast-container"></div>

<!-- FOOTER -->
<footer>
  <p>&copy; 2025 Jobyfind — Tous droits reservés | <a href="#">Politique de confidentialité</a></p>
</footer>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
  let allEvens = <?php echo json_encode(array_map(function($e) use ($asset) {
    $imagePath = $asset($e->getImage() ?: 'public/assets/images/event/default.jpg');
    return [
      'id'           => $e->getId(),
      'titre'        => $e->getTitre(),
      'date'         => $e->getDate(),
      'heure'        => '',
      'lieu'         => $e->getLieu(),
      'categorie'    => 'general',
      'organisateur' => $e->getIdOrganisateur() ? 'Organisateur #' . $e->getIdOrganisateur() : 'Organisateur inconnu',
      'intervenants' => '',
      'programme'    => $e->getDescription() ?: $e->getTitre(),
      'max'          => 0,
      'inscrits'     => 0,
      'image'        => $imagePath,
      'qrEventUrl'   => QRCode::generateForEvent($e),
      'qrRegUrl'     => QRCode::generateForRegistration($e),
    ];
  }, $events)); ?>;

  let currentFilter = 'all';
  let currentEventForModal = null;
  let eventsMap = null;
  let eventMarkers = [];
  let routeLines = [];
  let activeRouteLine = null;
  const userLocation = { lat: 36.8992, lng: 10.1892, label: 'Technopole El Ghazela' };

  const knownPlaces = [
    { keys: ['tunis', 'tunisie', 'lac', 'marsa', 'carthage', 'belvedere'], lat: 36.8065, lng: 10.1815, label: 'Tunis' },
    { keys: ['ariana', 'ghazela', 'raoued'], lat: 36.8625, lng: 10.1956, label: 'Ariana' },
    { keys: ['aouina', 'laouina', 'l aouina'], lat: 36.8485, lng: 10.2674, label: 'Aouina' },
    { keys: ['soukra', 'la soukra'], lat: 36.8797, lng: 10.2468, label: 'La Soukra' },
    { keys: ['gammarth'], lat: 36.9190, lng: 10.2866, label: 'Gammarth' },
    { keys: ['gabes', 'gabès', 'mareth'], lat: 33.8815, lng: 10.0982, label: 'Gabes' },
    { keys: ['sousse', 'kantaoui', 'sahloul'], lat: 35.8256, lng: 10.6369, label: 'Sousse' },
    { keys: ['sfax', 'sakiet'], lat: 34.7406, lng: 10.7603, label: 'Sfax' },
    { keys: ['nabeul', 'hammamet'], lat: 36.4561, lng: 10.7335, label: 'Nabeul' },
    { keys: ['bizerte', 'menzel'], lat: 37.2744, lng: 9.8739, label: 'Bizerte' },
    { keys: ['monastir', 'sahline'], lat: 35.7643, lng: 10.8113, label: 'Monastir' },
    { keys: ['kairouan'], lat: 35.6781, lng: 10.0963, label: 'Kairouan' },
    { keys: ['gafsa'], lat: 34.4250, lng: 8.7842, label: 'Gafsa' },
    { keys: ['beja', 'béja'], lat: 36.7256, lng: 9.1817, label: 'Beja' },
    { keys: ['kasserine'], lat: 35.1676, lng: 8.8317, label: 'Kasserine' },
    { keys: ['jendouba'], lat: 36.5011, lng: 8.7794, label: 'Jendouba' }
  ];

  function normalizeText(value) {
    return String(value || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
  }

  function inferEventLocation(eventData, index) {
    const place = normalizeText(eventData.lieu);
    const found = knownPlaces.find(p => p.keys.some(key => place.includes(normalizeText(key))));
    if (found) {
      // Small random jitter to separate markers in same city
      const jitterLat = (Math.random() - 0.5) * 0.01;
      const jitterLng = (Math.random() - 0.5) * 0.01;
      return { lat: found.lat + jitterLat, lng: found.lng + jitterLng, label: found.label };
    }

    const offset = (index % 10) * 0.03;
    return { lat: 36.8065 + offset, lng: 10.1815 + offset, label: eventData.lieu || 'Tunisie' };
  }

  function initEventsMap() {
    const mapElement = document.getElementById('events-map');
    if (!mapElement || typeof L === 'undefined') return;

    eventsMap = L.map('events-map', { scrollWheelZoom: false }).setView([36.8065, 10.1815], 9);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
      attribution: '&copy; OpenStreetMap'
    }).addTo(eventsMap);

    L.circleMarker([userLocation.lat, userLocation.lng], {
      radius: 9,
      color: '#0b1f4b',
      weight: 3,
      fillColor: '#2d79ff',
      fillOpacity: 1
    }).addTo(eventsMap).bindPopup(`<div class="map-popup-title">Votre position</div><div class="map-popup-meta">${userLocation.label}</div>`);

    renderMapMarkers(allEvens);
    renderAiMapInsights(allEvens);
  }

  function renderMapMarkers(eventsToShow) {
    if (!eventsMap) return;
    eventMarkers.forEach(marker => marker.remove());
    routeLines.forEach(line => line.remove());
    eventMarkers = [];
    routeLines = [];

    const bounds = [[userLocation.lat, userLocation.lng]];
    eventsToShow.forEach((e, index) => {
      const loc = inferEventLocation(e, index);
      const route = L.polyline([[userLocation.lat, userLocation.lng], [loc.lat, loc.lng]], {
        color: '#2d79ff',
        weight: 3,
        opacity: 0.45,
        dashArray: '8 8'
      }).addTo(eventsMap);
      const marker = L.marker([loc.lat, loc.lng]).addTo(eventsMap);
      marker.bindPopup(`
        <div class="map-popup-title">${escapeHtml(e.titre)}</div>
        <div class="map-popup-meta">${escapeHtml(loc.label)} · ${escapeHtml(e.date)}</div>
        <div class="map-popup-meta">Départ: ${escapeHtml(userLocation.label)}</div>
        <button class="map-popup-btn" onclick="openInscriptionModal(${e.id}, '${escapeJs(e.titre)}', '${escapeJs(e.date)}', '${escapeJs(e.lieu)}')">S'inscrire</button>
        <button class="map-popup-btn" style="margin-left:6px;background:#0b1f4b" onclick="showRoute(${loc.lat}, ${loc.lng}, '${escapeJs(e.titre)}')">Trajet</button>
      `);
      eventMarkers.push(marker);
      routeLines.push(route);
      bounds.push([loc.lat, loc.lng]);
    });

    if (bounds.length > 1) eventsMap.fitBounds(bounds, { padding: [35, 35] });
    else if (bounds.length === 1) eventsMap.setView(bounds[0], 12);
  }

  function showRoute(lat, lng, title) {
    if (!eventsMap) return;

    const url = `https://router.project-osrm.org/route/v1/driving/${userLocation.lng},${userLocation.lat};${lng},${lat}?overview=full&geometries=geojson`;

    fetch(url)
      .then(response => response.json())
      .then(data => {
        const route = data.routes && data.routes[0];
        if (!route) {
          throw new Error('Route introuvable');
        }

        if (activeRouteLine) {
          activeRouteLine.remove();
        }

        const coordinates = route.geometry.coordinates.map(point => [point[1], point[0]]);
        activeRouteLine = L.polyline(coordinates, {
          color: '#0b1f4b',
          weight: 6,
          opacity: 0.9
        }).addTo(eventsMap);

        eventsMap.fitBounds(activeRouteLine.getBounds(), { padding: [40, 40] });

        const distanceKm = (route.distance / 1000).toFixed(1);
        const durationMin = Math.round(route.duration / 60);
        showToast(`Trajet vers ${title}: ${distanceKm} km, environ ${durationMin} min.`, 'success');
      })
      .catch(() => {
        if (activeRouteLine) {
          activeRouteLine.remove();
        }
        activeRouteLine = L.polyline([[userLocation.lat, userLocation.lng], [lat, lng]], {
          color: '#0b1f4b',
          weight: 6,
          opacity: 0.9
        }).addTo(eventsMap);
        eventsMap.fitBounds(activeRouteLine.getBounds(), { padding: [40, 40] });
        showToast('Route exacte indisponible, trajet direct affiché sur la carte.', 'error');
      });
  }

  function renderAiMapInsights(eventsToAnalyze) {
    const summary = document.getElementById('ai-map-summary');
    const list = document.getElementById('ai-location-list');
    if (!summary || !list) return;

    const counts = {};
    eventsToAnalyze.forEach((e, index) => {
      const loc = inferEventLocation(e, index).label;
      counts[loc] = (counts[loc] || 0) + 1;
    });

    const sortedLocations = Object.entries(counts).sort((a, b) => b[1] - a[1]);
    const topLocation = sortedLocations[0]?.[0] || 'Tunisie';
    summary.textContent = `IA locale: départ depuis ${userLocation.label}. ${eventsToAnalyze.length} événement(s) détecté(s). La zone la plus active est ${topLocation}. Les pointillés montrent les trajets depuis votre localisation.`;

    list.innerHTML = sortedLocations.slice(0, 5).map(([location, count]) => `
      <div class="ai-location-item" onclick="focusLocation('${escapeJs(location)}')">
        <strong>${escapeHtml(location)}</strong>
        <span>${count} événement${count > 1 ? 's' : ''}</span>
      </div>
    `).join('');
  }

  function focusLocation(locationName) {
    const input = document.getElementById('search-input');
    if (input) input.value = locationName;
    filterEvents();
  }

  function escapeHtml(value) {
    return String(value || '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[c]));
  }

  function escapeJs(value) {
    return String(value || '').replace(/\\/g, '\\\\').replace(/'/g, "\\'").replace(/\n/g, ' ');
  }

  function setFilter(elem, filter) {
    document.querySelectorAll('.filter-chip, .cat-item').forEach(e => e.classList.remove('active'));
    if (elem) elem.classList.add('active');
    currentFilter = filter;
    filterEvents();
  }

  function filterEvents() {
    const searchInput = document.getElementById('search-input');
    const clearBtn = document.getElementById('search-clear');
    const feedback = document.getElementById('search-feedback');
    const search = normalizeText(searchInput.value).trim();
    const sort = document.getElementById('sort-select').value;
    
    let filtered = allEvens.filter(e => {
      const matchFilter = currentFilter === 'all' || e.categorie === currentFilter;
      
      // Expanded haystack with multiple date formats for dynamic searching
      const dateObj = new Date(e.date);
      const day = dateObj.getDate();
      const month = dateObj.toLocaleDateString('fr-FR', { month: 'long' });
      const year = dateObj.getFullYear();
      const dateFull = `${day} ${month} ${year}`;
      
      const haystack = normalizeText(`${e.titre} ${e.lieu} ${e.organisateur} ${e.programme} ${e.date} ${dateFull}`);
      const matchSearch = !search || haystack.includes(search);
      return matchFilter && matchSearch;
    });

    if (sort === 'date-desc') filtered.sort((a, b) => new Date(b.date) - new Date(a.date));
    else if (sort === 'popularity') filtered.sort((a, b) => b.inscrits - a.inscrits);
    else filtered.sort((a, b) => new Date(a.date) - new Date(b.date));

    const grid = document.getElementById('events-grid');
    grid.innerHTML = filtered.length ? filtered.map(e => `
      <div class="event-card" onclick="viewEventDetails(${e.id})">
        <div class="event-img-wrap">
          <img src="${e.image}" alt="Image événement">
          <div class="event-card-date">
            <div class="day">${new Date(e.date).getDate().toString().padStart(2, '0')}</div>
            <div class="month">${new Date(e.date).toLocaleDateString('fr-FR', {month: 'short'}).toUpperCase()}</div>
            <div class="year">${new Date(e.date).getFullYear()}</div>
          </div>
          <div class="event-qr-badge" onclick="event.stopPropagation(); showPublicQRModal('${e.titre.replace(/'/g,"\\'")}', '${e.qrEventUrl}', '${e.qrRegUrl}')" title="Voir le QR Code">
            <img src="${e.qrEventUrl}" alt="QR Code" loading="lazy">
            <span class="qr-tooltip">📱 QR Code</span>
          </div>
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
              <button class="btn btn-primary btn-sm" onclick="event.stopPropagation(); openInscriptionModal(${e.id}, '${e.titre.replace(/'/g,"\\'")}', '${e.date}', '${e.lieu.replace(/'/g,"\\'")}')">
                <i class="fa fa-plus"></i> S'inscrire
              </button>
            </div>
          </div>
        </div>
      </div>
    `).join('') : `
      <div style="grid-column: 1 / -1; background:#fff; border:1px solid var(--border); border-radius:14px; padding:36px; text-align:center; color:var(--muted);">
        <i class="fa fa-magnifying-glass" style="font-size:28px; margin-bottom:10px; display:block;"></i>
        Aucun événement trouvé pour cette recherche.
      </div>
    `;

    if (clearBtn) clearBtn.classList.toggle('show', search.length > 0);
    if (feedback) {
      feedback.textContent = search
        ? `${filtered.length} événement${filtered.length > 1 ? 's' : ''} trouvé${filtered.length > 1 ? 's' : ''}`
        : '';
    }

    renderMapMarkers(filtered);
    renderAiMapInsights(filtered);
  }

  function clearSearch() {
    const input = document.getElementById('search-input');
    if (input) {
      input.value = '';
      input.focus();
    }
    filterEvents();
  }

  function setupDynamicSearch() {
    const input = document.getElementById('search-input');
    if (!input) return;

    input.addEventListener('input', filterEvents);
    input.addEventListener('keydown', event => {
      if (event.key === 'Enter') {
        event.preventDefault();
        filterEvents();
      }
      if (event.key === 'Escape') {
        clearSearch();
      }
    });
  }

  function openInscriptionModal(id, titre, date, lieu) {
    const e = allEvens.find(ev => ev.id === id);
    if (e) currentEventForModal = e;
    else currentEventForModal = { id, titre, date, heure: '', lieu };

    document.getElementById('event-title').textContent    = titre;
    document.getElementById('event-date-time').textContent = date;
    document.getElementById('event-location').textContent = lieu;
    openModal('inscription-modal');
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

  function inscribeToEvent() {
    if (!currentEventForModal) return;
    openInscriptionModal(
      currentEventForModal.id,
      currentEventForModal.titre,
      currentEventForModal.date,
      currentEventForModal.lieu
    );
    closeModal('detail-modal');
  }

  function submitInscription() {
    const firstname = document.getElementById('firstname').value.trim();
    const lastname  = document.getElementById('lastname').value.trim();
    const email     = document.getElementById('email').value.trim();

    if (!firstname || !lastname || !email) {
      showToast('Veuillez remplir tous les champs.', 'error');
      return;
    }

    const nameRegex = /^[a-zA-ZÀ-ÿ\s\-]+$/;
    if (!nameRegex.test(firstname)) {
      showToast('Le prénom ne doit contenir que des lettres.', 'error');
      return;
    }
    if (!nameRegex.test(lastname)) {
      showToast('Le nom ne doit contenir que des lettres.', 'error');
      return;
    }

    if (!currentEventForModal) {
      showToast('Aucun événement sélectionné.', 'error');
      return;
    }

    const submitBtn = document.querySelector('#inscription-modal .btn-primary');
    submitBtn.disabled = true;
    submitBtn.textContent = 'Inscription en cours…';

    const formData = new FormData();
    formData.append('prenom', firstname);
    formData.append('nom',    lastname);
    formData.append('email',  email);

    const inscriptionBaseUrl = <?php echo json_encode($url('/events/inscrire')); ?>;
    fetch(inscriptionBaseUrl + '/' + currentEventForModal.id, {
      method: 'POST',
      body: formData,
    })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        showToast(data.message, 'success');
        closeModal('inscription-modal');
        document.getElementById('firstname').value = '';
        document.getElementById('lastname').value  = '';
        document.getElementById('email').value     = '';
        // Update inscrits count in local data
        if (typeof data.inscrits !== 'undefined') {
          const ev = allEvens.find(e => e.id === currentEventForModal.id);
          if (ev) ev.inscrits = data.inscrits;
        }
      } else {
        showToast(data.message || 'Erreur lors de l\'inscription.', 'error');
      }
    })
    .catch(() => showToast('Erreur de connexion au serveur.', 'error'))
    .finally(() => {
      submitBtn.disabled = false;
      submitBtn.textContent = 'S\'inscrire';
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

  function showPublicQRModal(eventTitle, qrEventUrl, qrRegistrationUrl) {
    const modal = document.getElementById('public-qr-modal');
    if (!modal) {
      console.error('QR Modal not found');
      return;
    }
    document.getElementById('public-qr-modal-title').textContent = 'Codes QR - ' + eventTitle;
    document.getElementById('public-qr-event-img').src = qrEventUrl;
    document.getElementById('public-qr-registration-img').src = qrRegistrationUrl;
    openModal('public-qr-modal');
  }

  document.querySelectorAll('.modal-overlay').forEach(modal => {
    modal.addEventListener('click', e => {
      if (e.target === modal) closeModal(modal.id);
    });
  });

  setupDynamicSearch();
  initEventsMap();
  filterEvents();
</script>

</body>
</html>
