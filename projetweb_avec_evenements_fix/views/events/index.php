<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Jobyfind - Événements</title>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Serif+Display&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet-routing-machine/3.2.12/leaflet-routing-machine.css" />
  <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet-routing-machine/3.2.12/leaflet-routing-machine.min.js"></script>
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
    
    .search-clear-btn {
      background: none;
      border: none;
      color: var(--muted);
      cursor: pointer;
      font-size: 14px;
      padding: 4px 8px;
      transition: color .2s;
      display: none;
      align-items: center;
      justify-content: center;
    }
    .search-clear-btn:hover { color: var(--text); }
    .search-clear-btn.show { display: flex; }

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
    .search-bar .search-btn.hidden { display: none; }

    .search-container {
      position: relative;
      width: 100%;
    }

    .smart-filters-zone {
      max-width: 700px;
      margin: 0 auto;
      padding: 0 20px;
      margin-top: 20px;
      position: relative;
      z-index: 10;
    }

    .filters-title {
      font-size: 12px;
      font-weight: 600;
      color: var(--navy);
      text-transform: uppercase;
      letter-spacing: 0.05em;
      margin-bottom: 10px;
      display: flex;
      align-items: center;
      gap: 6px;
    }

    .smart-filter-chips {
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
      padding: 12px 14px;
      background: #f9fafb;
      border-radius: 12px;
      border: 1px solid var(--border);
      min-height: 44px;
      align-items: center;
    }

    .smart-chip {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      padding: 6px 12px;
      background: #fff;
      border: 1.5px solid var(--border);
      border-radius: 99px;
      font-size: 12.5px;
      font-weight: 500;
      color: var(--text);
      cursor: pointer;
      transition: all .2s;
      white-space: nowrap;
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
    }

    .smart-chip i {
      font-size: 11px;
      opacity: 0.7;
    }

    .smart-chip .chip-remove {
      margin-left: 3px;
      opacity: 0.5;
      transition: opacity .2s;
    }

    .smart-chip:hover .chip-remove {
      opacity: 1;
    }

    .smart-chip.active .chip-remove {
      opacity: 0.8;
    }

    .autocomplete-dropdown {
      position: absolute;
      top: 100%;
      left: 0;
      right: 0;
      background: #fff;
      border: 1px solid var(--border);
      border-top: none;
      border-radius: 0 0 14px 14px;
      max-height: 400px;
      overflow-y: auto;
      display: none;
      z-index: 1000;
      box-shadow: 0 4px 24px rgba(11,31,75,.12);
    }

    .autocomplete-dropdown.show { display: block; }

    .autocomplete-item {
      padding: 12px 18px;
      border-bottom: 1px solid #f3f4f6;
      cursor: pointer;
      transition: background .15s;
      display: flex;
      flex-direction: column;
      gap: 6px;
    }

    .autocomplete-item:last-child { border-bottom: none; }

    .autocomplete-item:hover {
      background: #f9fafb;
    }

    .autocomplete-item.highlighted {
      background: #eff6ff;
      border-left: 3px solid var(--blue);
      padding-left: 15px;
    }

    .autocomplete-title {
      font-weight: 600;
      color: var(--navy);
      font-size: 13px;
    }

    .autocomplete-excerpt {
      font-size: 12px;
      color: var(--muted);
      line-height: 1.4;
      max-width: 400px;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }

    .autocomplete-match {
      background: #fef3c7;
      padding: 1px 3px;
      border-radius: 2px;
      font-weight: 600;
    }

    .autocomplete-relevance {
      font-size: 11px;
      color: #9ca3af;
      text-transform: uppercase;
      letter-spacing: 0.05em;
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
      width: 100%;
      max-width: 1200px;
      margin: 28px auto 60px;
      padding: 0 20px;
      display: grid;
      grid-template-columns: minmax(0, 1fr) 300px;
      gap: 28px;
      align-items: start;
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
      background: var(--event-bg, url("assets/images/event/e1.png")) center/cover no-repeat;
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
  </style>
</head>
<body>

<?php
require_once __DIR__ . '/../../config/QRCode.php';
$events = $events ?? [];
$counts = $counts ?? [];
$basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
$basePath = ($basePath && $basePath !== '.') ? $basePath : '';

function buildEventImageUrl(string $imageDbPath, string $basePath): string
{
    $imageDbPath = trim(str_replace('\\', '/', $imageDbPath));

    if ($imageDbPath === '') {
        return $basePath . '/assets/images/event/e1.png';
    }

    if (preg_match('#^https?://#i', $imageDbPath) === 1 || stripos($imageDbPath, 'data:image/') === 0) {
        return $imageDbPath;
    }

    if (strpos($imageDbPath, 'public/') === 0) {
        $imageDbPath = substr($imageDbPath, 7);
    }

    return $basePath . '/' . ltrim($imageDbPath, '/');
}
?>

<!-- NAV -->
<nav>
  <a class="nav-logo" href="/">Joby<span>find</span></a>
  <ul class="nav-links">
    <li><a href="<?php echo htmlspecialchars($basePath . '/index.php/admin/events', ENT_QUOTES, 'UTF-8'); ?>" class="active">Événements</a></li>
  </ul>
  <div class="nav-actions">
      <?php if(isset($_SESSION['user_id'])): ?>
        <a class="btn btn-outline" style="background:#10b981; border:none; color:#fff;" href="<?php echo htmlspecialchars($basePath . '/../views/frontoffice/profile.php', ENT_QUOTES, 'UTF-8'); ?>"><i class="fa fa-user"></i> Mon Profil</a>
        <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'Admin'): ?>
            <a class="btn btn-outline" href="<?php echo htmlspecialchars($basePath . '/../views/backoffice/admine.php', ENT_QUOTES, 'UTF-8'); ?>">Dashboard Admin</a>
        <?php endif; ?>
        <a class="btn btn-outline" href="<?php echo htmlspecialchars($basePath . '/../views/frontoffice/logout.php', ENT_QUOTES, 'UTF-8'); ?>">Déconnexion</a>
      <?php else: ?>
        <a class="btn btn-outline" href="<?php echo htmlspecialchars($basePath . '/../views/frontoffice/signin.php', ENT_QUOTES, 'UTF-8'); ?>">Connexion</a>
        <a class="btn btn-primary" href="<?php echo htmlspecialchars($basePath . '/../views/frontoffice/register.php', ENT_QUOTES, 'UTF-8'); ?>">S'inscrire</a>
      <?php endif; ?>
  </div>
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
  <div class="search-container">
    <div class="search-bar">
      <i class="fa fa-magnifying-glass"></i>
      <input type="text" id="search-input" placeholder="Cherchez par description (ex: Python, Web, Formation)…" autocomplete="off">
      <button type="button" class="search-clear-btn" id="search-clear-btn" onclick="clearSearchInput()" title="Effacer la recherche">
        <i class="fa fa-times-circle"></i>
      </button>
      <button class="search-btn" onclick="filterEvents()">Rechercher</button>
    </div>
    <div class="autocomplete-dropdown" id="autocomplete-dropdown" aria-hidden="true"></div>
  </div>
</div>

<!-- SMART FILTERS BY KEYWORDS -->
<div class="smart-filters-zone">
  <div class="filters-title">
    <i class="fa fa-tag"></i> Thèmes détectés
  </div>
  <div class="smart-filter-chips" id="smart-filter-chips">
    <!-- Chips seront générées dynamiquement ici -->
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
  <div class="filter-sep" style="margin-left: auto;"></div>
  <div style="display: flex; gap: 8px;">
    <button class="btn btn-primary" id="btn-view-grid" onclick="toggleView('grid')"><i class="fa fa-border-all"></i> Grille</button>
    <button class="btn btn-outline" id="btn-view-map" onclick="toggleView('map')"><i class="fa fa-map-location-dot"></i> Carte IA</button>
  </div>
</div>

<!-- CONTENT -->
<div class="content">
  <!-- MAIN COLUMN -->
  <div class="main-column" style="display: flex; flex-direction: column; gap: 20px; width: 100%; min-width: 0;">
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
        $eventImage = htmlspecialchars(buildEventImageUrl((string) $event->getImage(), $basePath), ENT_QUOTES, 'UTF-8');
        $qrEventUrl = QRCode::generateForEvent($event);
        $qrRegUrl   = QRCode::generateForRegistration($event);
        $inscrits = $counts[$event->getId()] ?? 0;
        $isComplet = $inscrits >= 5;
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
              <?php if ($isComplet): ?>
                <button class="btn btn-outline btn-sm" disabled style="opacity: 0.6; cursor: not-allowed; border-color: #ef4444; color: #ef4444;">
                  <i class="fa fa-lock"></i> Complet
                </button>
              <?php else: ?>
                <button class="btn btn-primary btn-sm" onclick="event.stopPropagation(); openInscriptionModal(<?php echo $event->getId(); ?>, <?php echo htmlspecialchars(json_encode($titreSlug), ENT_QUOTES, 'UTF-8'); ?>, '<?php echo htmlspecialchars($date, ENT_QUOTES, 'UTF-8'); ?>', '<?php echo htmlspecialchars($lieu, ENT_QUOTES, 'UTF-8'); ?>')">
                  <i class="fa fa-plus"></i> S'inscrire
                </button>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
    </div>

    <!-- MAP VIEW -->
    <div id="map-container" style="display: none; width: 100%; height: 600px; min-height: 600px; background-color: #e5e7eb; border-radius: 14px; overflow: hidden; position: relative; border: 2px solid var(--blue); box-shadow: 0 4px 24px rgba(11,31,75,.15);">
      <div id="map" style="position: absolute; inset: 0; z-index: 1;"></div>
      <div id="map-loading" style="display: none; position: absolute; inset: 0; background: rgba(255,255,255,0.9); z-index: 10; flex-direction: column; align-items: center; justify-content: center;">
        <i class="fa fa-circle-notch fa-spin fa-3x" style="color: var(--blue); margin-bottom: 15px;"></i>
        <p style="font-weight: 600; color: var(--navy); font-size: 16px;">Chargement de la carte et de l'IA...</p>
      </div>
      <div style="position: absolute; bottom: 20px; left: 50%; transform: translateX(-50%); z-index: 1000; display: flex; gap: 10px;">
        <button class="btn btn-primary" id="btn-calc-route" onclick="generateSmartRoute()" style="box-shadow: 0 4px 12px rgba(0,0,0,0.3); padding: 10px 20px; font-size: 14px;">
          <i class="fa fa-magic"></i> Tracer le meilleur trajet
        </button>
        <button class="btn btn-outline" id="btn-clear-route" onclick="clearRoute()" style="display: none; background: #fff; box-shadow: 0 4px 12px rgba(0,0,0,0.1); padding: 10px 20px; font-size: 14px;">
          <i class="fa fa-eraser"></i> Effacer
        </button>
      </div>
    </div>
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
      <button class="btn btn-primary" id="detail-btn-inscrire" style="width:100%;" onclick="inscribeToEvent()">S'inscrire maintenant</button>
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

<script>
  let allEvens = <?php echo json_encode(array_map(function($e) use ($basePath, $counts) {
    $imagePath = buildEventImageUrl((string) $e->getImage(), $basePath);
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
      'max'          => 5,
      'inscrits'     => $counts[$e->getId()] ?? 0,
      'image'        => $imagePath,
      'qrEventUrl'   => QRCode::generateForEvent($e),
      'qrRegUrl'     => QRCode::generateForRegistration($e),
    ];
  }, $events)); ?>;

  let currentFilter = 'all';
  let currentEventForModal = null;
  let activeKeywordFilter = null;

  // ===== SMART KEYWORD DETECTION FOR FILTERING =====
  const keywordLibrary = {
    // Tech keywords
    'Python': { icon: '🐍', color: '#3776ab', category: 'tech' },
    'JavaScript': { icon: '✨', color: '#f7df1e', category: 'tech' },
    'Web': { icon: '🌐', color: '#0066cc', category: 'tech' },
    'API': { icon: '🔌', color: '#00a6ff', category: 'tech' },
    'Frontend': { icon: '🎨', color: '#ff6b6b', category: 'tech' },
    'Backend': { icon: '⚙️', color: '#333333', category: 'tech' },
    'React': { icon: '⚛️', color: '#61dafb', category: 'tech' },
    'Node.js': { icon: '💚', color: '#339933', category: 'tech' },
    'Database': { icon: '🗄️', color: '#336791', category: 'tech' },
    'Data': { icon: '📊', color: '#ff6b35', category: 'tech' },
    'IA': { icon: '🤖', color: '#ff6b9d', category: 'tech' },
    'Cloud': { icon: '☁️', color: '#4285f4', category: 'tech' },
    
    // Formation keywords
    'Formation': { icon: '📚', color: '#9b59b6', category: 'formation' },
    'Cours': { icon: '👨‍🏫', color: '#e74c3c', category: 'formation' },
    'Atelier': { icon: '🛠️', color: '#f39c12', category: 'formation' },
    'Workshop': { icon: '🧑‍🔧', color: '#e67e22', category: 'formation' },
    'Certification': { icon: '🎓', color: '#3498db', category: 'formation' },
    'Apprentissage': { icon: '📖', color: '#1abc9c', category: 'formation' },
    
    // Conférence keywords
    'Conférence': { icon: '🎤', color: '#e91e63', category: 'conference' },
    'Talk': { icon: '💬', color: '#9c27b0', category: 'conference' },
    'Keynote': { icon: '🗣️', color: '#673ab7', category: 'conference' },
    'Panel': { icon: '👥', color: '#3f51b5', category: 'conference' },
    'Débat': { icon: '⚔️', color: '#2196f3', category: 'conference' },
    'Présentation': { icon: '📊', color: '#00bcd4', category: 'conference' },
    
    // Emploi keywords
    'Emploi': { icon: '💼', color: '#263238', category: 'emploi' },
    'Recrutement': { icon: '🎯', color: '#37474f', category: 'emploi' },
    'Carrière': { icon: '📈', color: '#455a64', category: 'emploi' },
    'Stage': { icon: '👨‍💼', color: '#607d8b', category: 'emploi' },
    'Job': { icon: '💻', color: '#455a64', category: 'emploi' },
    
    // Réseau keywords
    'Networking': { icon: '🤝', color: '#ff9800', category: 'reseau' },
    'Communauté': { icon: '👨‍👩‍👧‍👦', color: '#ff7043', category: 'reseau' },
    'Startup': { icon: '🚀', color: '#ff5722', category: 'reseau' },
    'Business': { icon: '📱', color: '#ff6e40', category: 'reseau' },
    'Entrepreneur': { icon: '🏆', color: '#ff5252', category: 'reseau' },
    
    // Design & Art keywords
    'Design': { icon: '🎨', color: '#c2185b', category: 'design' },
    'UX': { icon: '✏️', color: '#ec407a', category: 'design' },
    'UI': { icon: '🖌️', color: '#f48fb1', category: 'design' },
    'Créatif': { icon: '💡', color: '#ffd54f', category: 'design' },
    'Graphique': { icon: '🖼️', color: '#9c27b0', category: 'design' },
    
    // Culture & Sport keywords
    'Culture': { icon: '🎭', color: '#8b4513', category: 'culture' },
    'Sport': { icon: '⚽', color: '#00695c', category: 'culture' },
    'Yoga': { icon: '🧘', color: '#00796b', category: 'culture' },
    'Fitness': { icon: '💪', color: '#004d40', category: 'culture' },
  };

  function extractKeywords(text) {
    if (!text) return [];
    
    const lower = text.toLowerCase();
    const foundKeywords = new Set();
    
    for (const keyword of Object.keys(keywordLibrary)) {
      const keywordLower = keyword.toLowerCase();
      if (lower.includes(keywordLower)) {
        foundKeywords.add(keyword);
      }
    }
    
    return Array.from(foundKeywords);
  }

  function getAllKeywordsFromEvents() {
    const allKeywords = new Set();
    
    allEvens.forEach(event => {
      const keywords = extractKeywords(event.programme + ' ' + event.titre);
      keywords.forEach(k => allKeywords.add(k));
    });
    
    return Array.from(allKeywords).sort();
  }

  // --- Semantic helpers: pertinence, extrait, highlight ---
  function calculateRelevance(event, searchTerm) {
    if (!searchTerm) return 0;
    const s = searchTerm.toLowerCase().trim();
    if (!s) return 0;

    let score = 0;

    const title = (event.titre || '').toLowerCase();
    const programme = (event.programme || '').toLowerCase();
    const lieu = (event.lieu || '').toLowerCase();

    // Exact title match -> fort
    if (title === s) score += 200;
    // Title contains
    if (title.includes(s)) score += 120;

    // Programme contains -> important
    if (programme.includes(s)) score += 100;

    // Lieu contains -> utile
    if (lieu.includes(s)) score += 30;

    // Split words and reward partial matches
    const parts = s.split(/\s+/).filter(Boolean);
    parts.forEach(p => {
      if (title.includes(p)) score += 30;
      if (programme.includes(p)) score += 20;
      if (lieu.includes(p)) score += 5;
    });

    // Keywords from library
    try {
      const kws = extractKeywords(event.programme + ' ' + event.titre);
      kws.forEach(k => {
        if (k.toLowerCase().includes(s) || s.includes(k.toLowerCase())) score += 40;
      });
    } catch (e) {
      // ignore
    }

    return score;
  }

  function extractRelevantExcerpt(text, searchTerm, length = 80) {
    if (!text) return '';
    const lower = text.toLowerCase();
    const s = (searchTerm || '').toLowerCase().trim();
    if (!s) return text.substring(0, length) + (text.length > length ? '...' : '');

    const idx = lower.indexOf(s);
    if (idx === -1) {
      // fallback: return start
      return text.substring(0, length) + (text.length > length ? '...' : '');
    }

    const start = Math.max(0, idx - Math.floor(length / 2));
    const excerpt = text.substring(start, start + length);
    return (start > 0 ? '...' : '') + excerpt + (start + length < text.length ? '...' : '');
  }

  function highlightMatch(text, searchTerm) {
    if (!text || !searchTerm) return text;
    const s = searchTerm.replace(/[-/\\^$*+?.()|[\]{}]/g, '\\$&');
    try {
      return text.replace(new RegExp(s, 'ig'), match => `<span class="search-highlight">${match}</span>`);
    } catch (e) {
      return text;
    }
  }

  function updateSmartFilters() {
    const chipsContainer = document.getElementById('smart-filter-chips');
    if (!chipsContainer) return;
    
    const keywords = getAllKeywordsFromEvents();
    
    if (keywords.length === 0) {
      chipsContainer.innerHTML = '<p style="color: var(--muted); font-size: 12px;">Aucun thème détecté</p>';
      return;
    }
    
    chipsContainer.innerHTML = keywords.map(keyword => {
      const data = keywordLibrary[keyword];
      const isActive = activeKeywordFilter === keyword;
      
      return `
        <button class="smart-chip ${isActive ? 'active' : ''}" onclick="filterByKeyword('${keyword}')">
          <span>${data.icon}</span>
          <span>${keyword}</span>
          ${isActive ? '<span class="chip-remove"><i class="fa fa-x"></i></span>' : ''}
        </button>
      `;
    }).join('');
  }

  function filterByKeyword(keyword) {
    if (activeKeywordFilter === keyword) {
      activeKeywordFilter = null;
      document.getElementById('search-input').value = '';
    } else {
      activeKeywordFilter = keyword;
      document.getElementById('search-input').value = keyword;
    }
    
    updateSmartFilters();
    filterEvents();
  }

  function filterEvents() {
    const search = document.getElementById('search-input').value.toLowerCase().trim();
    const sort = document.getElementById('sort-select').value;
    
    let filtered = allEvens.filter(e => {
      const matchFilter = currentFilter === 'all' || e.categorie === currentFilter;
      
      if (!search) return matchFilter;
      
      // Filtrage intelligent par keywords dans la description
      const eventText = (e.programme + ' ' + e.titre).toLowerCase();
      const searchLower = search.toLowerCase();
      
      // Match exact ou partiel
      const hasMatch = eventText.includes(searchLower);
      
      return matchFilter && hasMatch;
    });

    // Tri par date par défaut
    if (sort === 'date-desc') filtered.sort((a, b) => new Date(b.date) - new Date(a.date));
    else if (sort === 'popularity') filtered.sort((a, b) => b.inscrits - a.inscrits);
    else filtered.sort((a, b) => new Date(a.date) - new Date(b.date));

    const grid = document.getElementById('events-grid');
    
    if (filtered.length === 0) {
      grid.innerHTML = `
        <div class="empty-state" style="display: flex; grid-column: 1/-1; width: 100%; justify-content: center; align-items: center;">
          <div style="text-align: center;">
            <i class="fa fa-search" style="font-size: 50px; color: #d1d5db; margin-bottom: 16px; display: block;"></i>
            <p style="color: #9ca3af; font-size: 15px; margin-bottom: 8px;">Aucun événement ne correspond à votre recherche</p>
            <p style="color: #d1d5db; font-size: 13px;">Essayez avec: Python, Formation, Conférence, Design...</p>
          </div>
        </div>
      `;
    } else {
      grid.innerHTML = filtered.map(e => `
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
                ${e.inscrits >= 5 ? `
                  <button class="btn btn-outline btn-sm" disabled style="opacity: 0.6; cursor: not-allowed; border-color: #ef4444; color: #ef4444;">
                    <i class="fa fa-lock"></i> Complet
                  </button>
                ` : `
                  <button class="btn btn-primary btn-sm" onclick="event.stopPropagation(); openInscriptionModal(${e.id}, '${e.titre.replace(/'/g,"\\\\'")}', '${e.date}', '${e.lieu.replace(/'/g,"\\\\'")}')">
                    <i class="fa fa-plus"></i> S'inscrire
                  </button>
                `}
              </div>
            </div>
          </div>
        </div>
      `).join('');
    }

    if (typeof currentView !== 'undefined' && currentView === 'map') {
      if (typeof clearRoute === 'function') clearRoute();
      if (typeof updateMapMarkers === 'function') updateMapMarkers();
    }

    updateSmartFilters();
  }

  // --- Autocomplete and selection ---
  function updateAutocomplete() {
    const searchInput = document.getElementById('search-input');
    const dropdown = document.getElementById('autocomplete-dropdown');
    if (!dropdown || !searchInput) return;
    const search = searchInput.value.trim();

    if (!search || search.length < 2) {
      dropdown.classList.remove('show');
      return;
    }

    const scored = allEvens.map(e => ({ event: e, score: calculateRelevance(e, search) }))
      .filter(s => s.score > 0)
      .sort((a, b) => b.score - a.score)
      .slice(0, 8);

    if (scored.length === 0) {
      dropdown.classList.remove('show');
      return;
    }

    dropdown.innerHTML = scored.map((item, idx) => {
      const excerpt = extractRelevantExcerpt(item.event.programme, search);
      const highlightedExcerpt = highlightMatch(excerpt, search);
      const relevanceLabel = item.score > 100 ? '🔥 Très pertinent' : item.score > 50 ? '✓ Pertinent' : '◆ Suggéré';
      return `
        <div class="autocomplete-item ${idx === 0 ? 'highlighted' : ''}" onclick="selectAutocompleteItem('${item.event.titre.replace(/'/g, "\\\\'")}')">
          <div class="autocomplete-title">${item.event.titre}</div>
          <div class="autocomplete-excerpt">${highlightedExcerpt}</div>
          <div class="autocomplete-relevance">${relevanceLabel}</div>
        </div>
      `;
    }).join('');

    dropdown.classList.add('show');
  }

  function selectAutocompleteItem(titre) {
    const input = document.getElementById('search-input');
    if (input) input.value = titre;
    const dd = document.getElementById('autocomplete-dropdown');
    if (dd) dd.classList.remove('show');
    filterEvents();
  }

  function setFilter(elem, filter) {
    document.querySelectorAll('.filter-chip, .cat-item').forEach(e => e.classList.remove('active'));
    if (elem) elem.classList.add('active');
    currentFilter = filter;
    filterEvents();
  }

  function filterEvents() {
    const search = document.getElementById('search-input').value.toLowerCase().trim();
    const sort = document.getElementById('sort-select').value;
    
    let filtered = allEvens.filter(e => {
      const matchFilter = currentFilter === 'all' || e.categorie === currentFilter;
      
      if (!search) return matchFilter;
      
      // Recherche intelligente par description
      const relevance = calculateRelevance(e, search);
      e._relevance = relevance; // Store pour le tri
      
      return matchFilter && relevance > 0;
    });

    // Tri par pertinence si une recherche est active
    if (search) {
      filtered.sort((a, b) => (b._relevance || 0) - (a._relevance || 0));
    } else {
      if (sort === 'date-desc') filtered.sort((a, b) => new Date(b.date) - new Date(a.date));
      else if (sort === 'popularity') filtered.sort((a, b) => b.inscrits - a.inscrits);
      else filtered.sort((a, b) => new Date(a.date) - new Date(b.date));
    }

    const grid = document.getElementById('events-grid');
    
    if (filtered.length === 0) {
      grid.innerHTML = `
        <div class="empty-state" style="display: flex; grid-column: 1/-1; width: 100%; justify-content: center; align-items: center;">
          <div style="text-align: center;">
            <i class="fa fa-search" style="font-size: 50px; color: #d1d5db; margin-bottom: 16px; display: block;"></i>
            <p style="color: #9ca3af; font-size: 15px; margin-bottom: 8px;">Aucun événement ne correspond à votre recherche</p>
            <p style="color: #d1d5db; font-size: 13px;">Essayez avec: "Python", "Formation", "Web", "Conférence"...</p>
          </div>
        </div>
      `;
    } else {
      grid.innerHTML = filtered.map(e => `
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
                ${e.inscrits >= 5 ? `
                  <button class="btn btn-outline btn-sm" disabled style="opacity: 0.6; cursor: not-allowed; border-color: #ef4444; color: #ef4444;">
                    <i class="fa fa-lock"></i> Complet
                  </button>
                ` : `
                  <button class="btn btn-primary btn-sm" onclick="event.stopPropagation(); openInscriptionModal(${e.id}, '${e.titre.replace(/'/g,"\\\\'")}', '${e.date}', '${e.lieu.replace(/'/g,"\\\\'")}')">
                    <i class="fa fa-plus"></i> S'inscrire
                  </button>
                `}
              </div>
            </div>
          </div>
        </div>
      `).join('');
    }

    if (typeof currentView !== 'undefined' && currentView === 'map') {
      if (typeof clearRoute === 'function') clearRoute();
      if (typeof updateMapMarkers === 'function') updateMapMarkers();
    }
  }

  function openInscriptionModal(id, titre, date, lieu) {
    const e = allEvens.find(ev => ev.id == id);
    if (e) currentEventForModal = e;
    else currentEventForModal = { id, titre, date, heure: '', lieu };

    document.getElementById('event-title').textContent    = titre;
    document.getElementById('event-date-time').textContent = date;
    document.getElementById('event-location').textContent = lieu;
    openModal('inscription-modal');
  }

  function viewEventDetails(id) {
    const e = allEvens.find(ev => ev.id == id);
    if (!e) return;
    currentEventForModal = e;
    
    const headerImg = document.querySelector('.detail-header-img');
    if (headerImg) {
      headerImg.style.setProperty('--event-bg', `url("${e.image}")`);
    }
    
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
    
    const btnInscrire = document.getElementById('detail-btn-inscrire');
    if (e.inscrits >= 5) {
        btnInscrire.textContent = 'Complet';
        btnInscrire.disabled = true;
        btnInscrire.style.opacity = '0.6';
        btnInscrire.style.cursor = 'not-allowed';
        btnInscrire.style.background = '#ef4444';
        btnInscrire.style.borderColor = '#ef4444';
    } else {
        btnInscrire.innerHTML = "S'inscrire maintenant";
        btnInscrire.disabled = false;
        btnInscrire.style.opacity = '1';
        btnInscrire.style.cursor = 'pointer';
        btnInscrire.style.background = '';
        btnInscrire.style.borderColor = '';
    }
    
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

    const basePath = <?php echo json_encode($basePath); ?>;
    fetch(basePath + '/index.php/events/inscrire/' + currentEventForModal.id, {
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
          const ev = allEvens.find(e => e.id == currentEventForModal.id);
          if (ev) {
            ev.inscrits = data.inscrits;
            filterEvents(); // Re-render the grid to show 'Complet' if reached 5
          }
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


  // ----- MAP & AI ROUTING LOGIC -----
  let map = null;
  let mapMarkers = [];
  let routingControl = null;
  let currentView = 'grid'; // 'grid' or 'map'

  function toggleView(view) {
    if (view === currentView) return;
    currentView = view;
    
    const btnGrid = document.getElementById('btn-view-grid');
    const btnMap = document.getElementById('btn-view-map');
    const gridEl = document.getElementById('events-grid');
    const mapEl = document.getElementById('map-container');

    if (view === 'grid') {
      btnGrid.className = 'btn btn-primary';
      btnMap.className = 'btn btn-outline';
      gridEl.style.display = 'grid';
      mapEl.style.display = 'none';
    } else {
      btnMap.className = 'btn btn-primary';
      btnGrid.className = 'btn btn-outline';
      gridEl.style.display = 'none';
      mapEl.style.display = 'block';
      
      try {
        if (!map) {
          setTimeout(() => initMap(), 150);
        } else {
          setTimeout(() => {
            map.invalidateSize();
            updateMapMarkers();
          }, 150);
        }
      } catch (e) {
        showToast("Erreur basculement carte: " + e.message, 'error');
      }
    }
  }

  function initMap() {
    try {
      if (typeof L === 'undefined') {
        showToast("Erreur: Leaflet n'est pas chargé. Vérifiez votre connexion internet.", "error");
        return;
      }
      
      const mapDiv = document.getElementById('map');
      if (!mapDiv) {
        showToast("Erreur: div #map introuvable", "error");
        return;
      }
      
      map = L.map('map').setView([33.8869, 9.5375], 6);
      L.tileLayer('https://{s}.tile.openstreetmap.fr/osmfr/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap France | &copy; OpenStreetMap contributors'
      }).addTo(map);
      
      // Force leaflet to recalculate its size right away
      setTimeout(() => { map.invalidateSize(); }, 50);
      
      updateMapMarkers();
    } catch(e) {
      console.error(e);
      showToast("Erreur initMap: " + e.message, 'error');
    }
  }

  // Cache for geocoding to avoid rate limits
  const geocodeCache = {
    'Tunis': {lat: 36.8065, lon: 10.1815},
    'Sfax': {lat: 34.7406, lon: 10.7603},
    'Sousse': {lat: 35.8256, lon: 10.6369},
    'Bizerte': {lat: 37.2744, lon: 9.8739},
    'Gabès': {lat: 33.8815, lon: 10.0982},
    'Nabeul': {lat: 36.4561, lon: 10.7376},
    'Ariana': {lat: 36.8625, lon: 10.1956},
    'Kairouan': {lat: 35.6781, lon: 10.0963},
    'Gafsa': {lat: 34.4250, lon: 8.7842},
    'Monastir': {lat: 35.7780, lon: 10.8262},
    'Médenine': {lat: 33.3550, lon: 10.5055},
    'Ben Arous': {lat: 36.7531, lon: 10.2189},
    'Esprit': {lat: 36.8993, lon: 10.1897}, 
    'Paris': {lat: 48.8566, lon: 2.3522},
  };

  async function getCoordinates(lieu) {
    if (!lieu) lieu = "Tunis";
    let searchLieu = lieu.split(',')[0].trim();
    
    for (const key in geocodeCache) {
      if (searchLieu.toLowerCase().includes(key.toLowerCase())) {
        return geocodeCache[key];
      }
    }
    
    if (geocodeCache[searchLieu]) return geocodeCache[searchLieu];

    try {
      await new Promise(res => setTimeout(res, 300)); // Rate limit delay
      const res = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(searchLieu + ', Tunisia')}`);
      const data = await res.json();
      if (data && data.length > 0) {
        geocodeCache[searchLieu] = { lat: parseFloat(data[0].lat), lon: parseFloat(data[0].lon) };
        return geocodeCache[searchLieu];
      }
    } catch (e) {
      console.warn("Geocoding failed for", lieu);
    }
    
    // FALLBACK : Si le lieu est faux ou introuvable (ex: "dbdhd"), on place un point par défaut (Tunis) avec un léger décalage aléatoire
    const fallback = { 
       lat: 36.8065 + (Math.random() - 0.5) * 0.05, 
       lon: 10.1815 + (Math.random() - 0.5) * 0.05,
       isFallback: true
    };
    geocodeCache[searchLieu] = fallback;
    return fallback;
  }

  async function updateMapMarkers() {
    if (!map) return;
    
    mapMarkers.forEach(m => map.removeLayer(m));
    mapMarkers = [];
    
    const loading = document.getElementById('map-loading');
    loading.style.display = 'flex';
    
    const search = document.getElementById('search-input').value.toLowerCase().trim();
    let visibleEvents = allEvens.filter(e => {
      const matchFilter = currentFilter === 'all' || e.categorie === currentFilter;
      
      if (!search) return matchFilter;
      
      const relevance = calculateRelevance(e, search);
      return matchFilter && relevance > 0;
    });

    let bounds = L.latLngBounds();
    let hasValidPoints = false;

    for (let ev of visibleEvents) {
      const coords = await getCoordinates(ev.lieu);
      if (coords) {
        ev.coords = coords;
        hasValidPoints = true;
        
        const marker = L.marker([coords.lat, coords.lon]).addTo(map);
        const fallbackNote = coords.isFallback ? `<div style="background:#fee2e2; color:#dc2626; padding:4px; border-radius:4px; font-size:10px; margin-bottom:6px; font-weight:600;"><i class="fa fa-triangle-exclamation"></i> Lieu exact introuvable</div>` : '';
        marker.bindPopup(`
          <div style="font-family: 'DM Sans', sans-serif; width: 200px;">
            <img src="${ev.image}" style="width:100%; height:90px; object-fit:cover; border-radius:6px; margin-bottom:8px;">
            <h4 style="margin:0 0 4px; font-size:14px;">${ev.titre}</h4>
            ${fallbackNote}
            <p style="margin:0 0 8px; font-size:12px; color:#6b7280;"><i class="fa fa-location-dot"></i> ${ev.lieu}</p>
            <div style="display:flex; flex-direction:column; gap:6px;">
              <button class="btn btn-primary btn-sm" style="width:100%; font-size:12px; padding:6px 0;" onclick="viewEventDetails(${ev.id})">Voir détails</button>
              <button class="btn btn-outline btn-sm" style="width:100%; font-size:12px; padding:6px 0; border: 1.5px solid var(--blue); color: var(--blue);" onclick="addToRoute(${ev.id})"><i class="fa fa-route"></i> Ajouter au trajet</button>
            </div>
          </div>
        `);
        mapMarkers.push(marker);
        bounds.extend([coords.lat, coords.lon]);
      }
    }
    
    loading.style.display = 'none';
    
    if (hasValidPoints) {
      map.fitBounds(bounds, { padding: [50, 50], maxZoom: 14 });
    }
  }

  let selectedRouteEvents = [];

  function addToRoute(eventId) {
    const ev = allEvens.find(e => e.id == eventId);
    if (!ev) {
      showToast('Erreur: Événement introuvable.', 'error');
      return;
    }
    if (!ev.coords) {
      showToast('Erreur: Impossible de localiser cet événement.', 'error');
      return;
    }
    
    if (selectedRouteEvents.find(e => e.id == eventId)) {
      showToast('Cet événement est déjà dans votre trajet.', 'warning');
      return;
    }
    
    if (selectedRouteEvents.length >= 2) {
      showToast('Vous ne pouvez sélectionner que 2 événements maximum pour le trajet.', 'warning');
      return;
    }
    
    selectedRouteEvents.push(ev);
    document.getElementById('btn-calc-route').innerHTML = `<i class="fa fa-magic"></i> Tracer le trajet (${selectedRouteEvents.length}/2)`;
    showToast(`Ajouté au trajet : ${ev.titre} (${selectedRouteEvents.length}/2)`, 'success');
  }

  function generateSmartRoute() {
    if (!map) return;
    
    if (selectedRouteEvents.length === 0) {
      showToast('Veuillez sélectionner au moins 1 événement sur la carte en cliquant sur "Ajouter au trajet".', 'warning');
      return;
    }

    if (routingControl) {
      map.removeControl(routingControl);
    }
    
    showToast('Calcul du trajet depuis Technopole El Ghazela...', 'success');
    
    // Starting point: Technopole El Ghazela
    const startPoint = L.latLng(36.8927, 10.1868);
    const points = [startPoint];
    
    selectedRouteEvents.forEach(e => {
      points.push(L.latLng(e.coords.lat, e.coords.lon));
    });

    routingControl = L.Routing.control({
      waypoints: points,
      routeWhileDragging: false,
      addWaypoints: false,
      show: true,
      language: 'fr',
      lineOptions: {
        styles: [{color: '#2d79ff', opacity: 0.8, weight: 6}]
      },
      createMarker: function(i, wp, nWps) {
        if (i === 0) {
          // Custom marker for start point
          return L.marker(wp.latLng, {
            icon: L.divIcon({
              className: 'custom-start-marker',
              html: '<div style="background:#10b981; color:white; border-radius:50%; width:24px; height:24px; display:flex; align-items:center; justify-content:center; box-shadow:0 2px 4px rgba(0,0,0,0.3); border:2px solid white;"><i class="fa fa-home"></i></div>',
              iconSize: [24, 24],
              iconAnchor: [12, 12]
            })
          }).bindPopup('<div style="font-family:\'DM Sans\',sans-serif;"><b>Point de départ</b><br>Technopole El Ghazela</div>');
        }
        return null;
      },
      fitSelectedRoutes: true
    }).addTo(map);

    document.getElementById('btn-clear-route').style.display = 'block';
  }

  function clearRoute() {
    if (routingControl && map) {
      map.removeControl(routingControl);
      routingControl = null;
    }
    selectedRouteEvents = [];
    document.getElementById('btn-calc-route').innerHTML = `<i class="fa fa-magic"></i> Tracer le meilleur trajet`;
    document.getElementById('btn-clear-route').style.display = 'none';
    showToast('Trajet effacé.', 'success');
  }

  // ===== DYNAMIC SEARCH =====
  let searchTimeout;
  const searchInput = document.getElementById('search-input');
  const searchBtn = document.querySelector('.search-btn');
  const clearBtn = document.getElementById('search-clear-btn');

  function clearSearchInput() {
    searchInput.value = '';
    searchInput.focus();
    clearBtn.classList.remove('show');
    filterEvents();
  }

  function updateClearButton() {
    if (searchInput.value.trim()) {
      clearBtn.classList.add('show');
    } else {
      clearBtn.classList.remove('show');
    }
  }

  if (searchInput) {
    // Add real-time search on input
    searchInput.addEventListener('input', function() {
      updateClearButton();
      updateAutocomplete();
      clearTimeout(searchTimeout);
      searchTimeout = setTimeout(() => {
        filterEvents();
      }, 300); // Debounce 300ms for smooth performance
    });

    // Allow Enter key to trigger search immediately
    searchInput.addEventListener('keypress', function(e) {
      if (e.key === 'Enter') {
        clearTimeout(searchTimeout);
        const dd = document.getElementById('autocomplete-dropdown'); if (dd) dd.classList.remove('show');
        filterEvents();
      }
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
      if (!e.target.closest('.search-container')) {
        const dd = document.getElementById('autocomplete-dropdown'); if (dd) dd.classList.remove('show');
      }
    });

    // Initial state
    updateClearButton();
  }

  // Initialize view
  filterEvents();
</script>

</body>
</html>
