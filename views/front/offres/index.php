<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Jobyfind — Offres d'emploi</title>
  <meta name="description" content="Découvrez toutes les offres d'emploi, stages et opportunités sur Jobyfind.">
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=DM+Serif+Display&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <link rel="stylesheet" href="assets/css/chatbot.css">
  <link rel="stylesheet" href="assets/css/panier.css">
  <style>
    /* ── RESET & VARIABLES ── */
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; font-family: 'DM Sans', sans-serif; }
    :root {
      --blue-primary: #153c8f;
      --blue-light: #2d79ff;
      --text-main: #1f2937;
      --text-muted: #6b7280;
      --bg: #f8fafc;
      --surface: #ffffff;
      --border: #e2e8f0;
      --radius: 12px;
    }
    body { background-color: var(--bg); color: var(--text-main); }

    /* ── NAVBAR ── */
    .navbar {
      display: flex; justify-content: space-between; align-items: center;
      padding: 15px 40px; background: var(--surface);
      border-bottom: 1px solid var(--border);
      position: sticky; top: 0; z-index: 100;
      box-shadow: 0 1px 8px rgba(0,0,0,.05);
    }
    .logo { font-family: 'DM Serif Display', serif; font-size: 24px; color: #111827; }
    .logo span { color: var(--blue-light); }
    .nav-links { display: flex; gap: 30px; align-items: center; }
    .nav-link { text-decoration: none; color: var(--text-muted); font-weight: 500; transition: color .15s; }
    .nav-link.active { color: var(--blue-light); border-bottom: 2px solid var(--blue-light); padding-bottom: 4px; }
    .nav-link:hover { color: var(--blue-light); }

    /* ── HERO ── */
    .hero {
      background: var(--blue-primary);
      position: relative; overflow: hidden;
      padding: 80px 20px 120px;
      text-align: center; color: white;
    }
    .hero-badge {
      display: inline-block; background: rgba(255,255,255,0.12); border-radius: 20px;
      padding: 6px 14px; font-size: 11px; font-weight: 700; letter-spacing: 1px;
      text-transform: uppercase; margin-bottom: 20px;
    }
    .hero-title {
      font-family: 'DM Serif Display', serif; font-size: 46px; line-height: 1.2;
      max-width: 600px; margin: 0 auto 20px;
    }
    .hero-subtitle { font-size: 16px; color: rgba(255,255,255,0.7); max-width: 500px; margin: 0 auto 40px; }
    .stats { display: flex; justify-content: center; gap: 50px; flex-wrap: wrap; }
    .stat-item h3 { font-size: 32px; font-family: 'DM Serif Display', serif; }
    .stat-item p { font-size: 13px; color: rgba(255,255,255,0.6); text-transform: uppercase; letter-spacing: 1px; }
    .hero::before, .hero::after {
      content: ''; position: absolute; border-radius: 50%;
      background: linear-gradient(135deg, rgba(255,255,255,0.05), rgba(255,255,255,0));
    }
    .hero::before { width: 500px; height: 500px; top: -100px; left: -200px; }
    .hero::after  { width: 400px; height: 400px; bottom: -50px; right: -150px; }

    /* ── SEARCH BAR ── */
    .search-container {
      display: flex; justify-content: center; align-items: center;
      margin-top: -35px; position: relative; z-index: 10;
    }
    .search-box {
      background: var(--surface); padding: 8px 8px 8px 24px; border-radius: 30px;
      display: flex; align-items: center;
      box-shadow: 0 10px 30px rgba(0,0,0,0.12);
      width: 560px; max-width: 92%;
    }
    .search-box input {
      border: none; outline: none; flex: 1; font-size: 15px; background: transparent;
      color: var(--text-main);
    }
    .search-box input::placeholder { color: var(--text-muted); }
    .search-clear {
      background: none; border: none; cursor: pointer; color: var(--text-muted);
      font-size: 14px; padding: 4px 8px; display: none; transition: color .15s;
    }
    .search-clear:hover { color: var(--text-main); }
    .search-btn {
      background: var(--blue-light); color: white; border: none; border-radius: 22px;
      padding: 12px 24px; font-weight: 600; cursor: pointer; transition: background .2s; font-size: 14px;
    }
    .search-btn:hover { background: #1a5ccc; }

    /* ── TOOLBAR (filtres + tri) ── */
    .toolbar {
      max-width: 1040px; margin: 30px auto 0; padding: 0 20px;
      display: flex; align-items: center; gap: 12px; flex-wrap: wrap;
    }
    .filter-label { font-size: 13px; font-weight: 600; color: var(--text-muted); }
    .filter-badge {
      padding: 7px 16px; border-radius: 20px; background: var(--surface);
      border: 1.5px solid var(--border); color: var(--text-muted);
      font-size: 12px; font-weight: 600; cursor: pointer;
      text-decoration: none; transition: all .15s; user-select: none;
    }
    .filter-badge:hover { border-color: var(--blue-light); color: var(--blue-light); }
    .filter-badge.active { background: var(--blue-light); color: white; border-color: var(--blue-light); }

    /* Séparateur */
    .toolbar-sep { flex: 1; }

    .sort-wrap { display: flex; align-items: center; gap: 8px; }
    .sort-label { font-size: 13px; font-weight: 600; color: var(--text-muted); }
    .sort-select {
      padding: 7px 32px 7px 12px; border: 1.5px solid var(--border);
      border-radius: 8px; font-family: 'DM Sans', sans-serif; font-size: 12px;
      color: var(--text-main); background: var(--surface);
      cursor: pointer; outline: none; appearance: none;
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%236b7280' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
      background-repeat: no-repeat; background-position: right 10px center;
      transition: border-color .15s;
    }
    .sort-select:focus { border-color: var(--blue-light); }

    /* Compteur résultats */
    .results-count {
      max-width: 1040px; margin: 14px auto 0; padding: 0 20px;
      font-size: 13px; color: var(--text-muted);
    }
    .results-count span { font-weight: 700; color: var(--text-main); }

    /* ── OFFERS GRID ── */
    .container {
      max-width: 1040px; margin: 16px auto 60px; padding: 0 20px;
      display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;
    }
    .card {
      background: white; border-radius: var(--radius); padding: 25px;
      transition: transform .2s, box-shadow .2s; border: 1px solid var(--border);
    }
    .card:hover { transform: translateY(-4px); box-shadow: 0 12px 28px rgba(0,0,0,.07); }
    .card-meta { display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; }
    .card-cat {
      padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: 700;
      text-transform: uppercase; letter-spacing: .5px;
    }
    .card-cat.type-cdi   { background:#dbeafe; color:#1d4ed8; }
    .card-cat.type-cdd   { background:#fef3c7; color:#92400e; }
    .card-cat.type-stage { background:#dcfce7; color:#166534; }
    .card-cat.type-other { background:#f1f5f9; color:#475569; }
    .card-date { font-size: 12px; color: var(--text-muted); }
    .card-title { font-size: 18px; font-weight: 700; margin-bottom: 10px; color: var(--text-main); line-height: 1.3; }
    .card-desc {
      font-size: 14px; color: var(--text-muted); line-height: 1.6; margin-bottom: 18px;
      display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;
    }
    .card-footer { display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #f1f5f9; padding-top: 14px; }
    .card-type { font-size: 13px; font-weight: 600; display: flex; align-items: center; gap: 6px; color: var(--text-main); }
    .card-btn {
      color: var(--blue-light); font-size: 13px; font-weight: 700; text-decoration: none;
      display: flex; align-items: center; gap: 5px; transition: gap .15s;
    }
    .card-btn:hover { gap: 8px; }

    /* Aucun résultat */
    .no-results {
      grid-column: 1 / -1; text-align: center; padding: 60px 20px;
      color: var(--text-muted); display: none;
    }
    .no-results i { font-size: 36px; margin-bottom: 14px; display: block; opacity: .4; }
    .no-results p { font-size: 15px; }
  </style>
</head>
<body>

  <nav class="navbar">
    <div class="logo">Joby<span>find</span></div>
    <div class="nav-links">
      <a href="#" class="nav-link">Accueil</a>
      <a href="index.php?action=front_offres" class="nav-link active">Offres</a>
      <a href="index.php?action=list_offres" class="nav-link">Back-office</a>
    </div>
  </nav>

  <section class="hero">
    <span class="hero-badge">Agenda des offres</span>
    <h1 class="hero-title">Découvrez les offres qui font avancer votre carrière</h1>
    <p class="hero-subtitle">Emplois, stages, opportunités… Restez connecté à l'écosystème professionnel.</p>
    <div class="stats">
      <div class="stat-item">
        <h3 id="heroCount"><?= count($offres) ?></h3>
        <p>Offres disponibles</p>
      </div>
      <div class="stat-item">
        <?php
          $types = array_unique(array_column($offres, 'type'));
          $types = array_filter($types);
        ?>
        <h3><?= count($types) ?>+</h3>
        <p>Types de contrats</p>
      </div>
    </div>
  </section>

  <!-- Barre de recherche -->
  <div class="search-container">
    <div class="search-box">
      <i class="fa fa-search" style="color:var(--text-muted);margin-right:10px;"></i>
      <input type="text" id="searchInput" placeholder="Rechercher un titre, un type…" autocomplete="off">
      <button class="search-clear" id="searchClear" title="Effacer"><i class="fa fa-times"></i></button>
      <button class="search-btn" id="searchBtn">Rechercher</button>
    </div>
  </div>

  <!-- Filtres type + Tri -->
  <div class="toolbar">
    <span class="filter-label">Filtrer :</span>
    <span class="filter-badge active" data-type="">Toutes</span>
    <?php foreach($types as $t): ?>
    <span class="filter-badge" data-type="<?= htmlspecialchars($t) ?>"><?= htmlspecialchars($t) ?></span>
    <?php endforeach; ?>

    <div class="toolbar-sep"></div>

    <div class="sort-wrap">
      <span class="sort-label"><i class="fa fa-arrow-up-wide-short"></i> Trier :</span>
      <select id="sortSelect" class="sort-select">
        <option value="date-desc">Date (récent → ancien)</option>
        <option value="date-asc">Date (ancien → récent)</option>
        <option value="titre-asc">Titre A → Z</option>
        <option value="titre-desc">Titre Z → A</option>
      </select>
    </div>
  </div>

  <!-- Compteur résultats -->
  <div class="results-count">
    <span id="resultCount"><?= count($offres) ?></span> offre(s) trouvée(s)
  </div>

  <!-- Grille des offres -->
  <div class="container" id="offresGrid">
    <?php foreach($offres as $o): ?>
    <?php
      $type = strtolower($o['type'] ?? '');
      $catClass = 'type-other';
      if (str_contains($type, 'cdi'))   $catClass = 'type-cdi';
      elseif (str_contains($type, 'cdd'))   $catClass = 'type-cdd';
      elseif (str_contains($type, 'stage')) $catClass = 'type-stage';
    ?>
    <div class="card offre-card"
         data-titre="<?= htmlspecialchars(strtolower($o['titre'])) ?>"
         data-desc="<?= htmlspecialchars(strtolower($o['description'])) ?>"
         data-type="<?= htmlspecialchars($o['type']) ?>"
         data-date="<?= htmlspecialchars($o['datePublication']) ?>">
      <div class="card-meta">
        <span class="card-cat <?= $catClass ?>"><?= htmlspecialchars($o['type']) ?></span>
        <span class="card-date"><i class="fa-regular fa-calendar" style="margin-right:4px;"></i><?= htmlspecialchars($o['datePublication']) ?></span>
      </div>
      <h3 class="card-title"><?= htmlspecialchars($o['titre']) ?></h3>
      <p class="card-desc"><?= htmlspecialchars($o['description']) ?></p>
      <div class="card-footer">
        <span class="card-type"><i class="fa-solid fa-briefcase"></i> <?= htmlspecialchars($o['type']) ?></span>
        <a href="index.php?action=add_candidature&front=1&id_offre=<?= $o['id_offre'] ?>" class="card-btn">Postuler <i class="fa fa-arrow-right"></i></a>
      </div>
    </div>
    <?php endforeach; ?>

    <div class="no-results" id="noResults">
      <i class="fa fa-magnifying-glass"></i>
      <p>Aucune offre ne correspond à votre recherche.</p>
    </div>
  </div>

<script>
(function () {
  const searchInput  = document.getElementById('searchInput');
  const searchClear  = document.getElementById('searchClear');
  const searchBtn    = document.getElementById('searchBtn');
  const sortSelect   = document.getElementById('sortSelect');
  const resultCount  = document.getElementById('resultCount');
  const heroCount    = document.getElementById('heroCount');
  const noResults    = document.getElementById('noResults');
  const grid         = document.getElementById('offresGrid');
  const filterBadges = document.querySelectorAll('.filter-badge[data-type]');

  let activeType = '';

  // ── Récupérer toutes les cartes dans un tableau mutable ──
  function getCards() {
    return Array.from(document.querySelectorAll('.offre-card'));
  }

  // ── Appliquer recherche + filtre type ──
  function applyFilters() {
    const q     = searchInput.value.trim().toLowerCase();
    const cards = getCards();
    let visible = 0;

    cards.forEach(function (card) {
      const titre = card.dataset.titre || '';
      const desc  = card.dataset.desc  || '';
      const type  = card.dataset.type  || '';

      const matchSearch = !q || titre.includes(q) || desc.includes(q) || type.toLowerCase().includes(q);
      const matchType   = !activeType || type === activeType;

      const show = matchSearch && matchType;
      card.style.display = show ? '' : 'none';
      if (show) visible++;
    });

    resultCount.textContent = visible;
    heroCount.textContent   = visible;
    noResults.style.display = (visible === 0) ? 'block' : 'none';

    // Afficher/masquer bouton effacer
    searchClear.style.display = q ? 'inline-block' : 'none';
  }

  // ── Tri ──
  function applySort() {
    const val   = sortSelect.value;
    const cards = getCards();

    cards.sort(function (a, b) {
      if (val === 'date-desc') return (b.dataset.date || '').localeCompare(a.dataset.date || '');
      if (val === 'date-asc')  return (a.dataset.date || '').localeCompare(b.dataset.date || '');
      if (val === 'titre-asc') return (a.dataset.titre || '').localeCompare(b.dataset.titre || '', 'fr');
      if (val === 'titre-desc')return (b.dataset.titre || '').localeCompare(a.dataset.titre || '', 'fr');
      return 0;
    });

    // Réinsérer dans le DOM dans le bon ordre
    cards.forEach(c => grid.insertBefore(c, noResults));
  }

  // ── Événements ──
  searchInput.addEventListener('input', function () {
    applyFilters();
  });

  searchBtn.addEventListener('click', function () {
    applyFilters();
    searchInput.focus();
  });

  searchClear.addEventListener('click', function () {
    searchInput.value = '';
    this.style.display = 'none';
    applyFilters();
    searchInput.focus();
  });

  sortSelect.addEventListener('change', function () {
    applySort();
    applyFilters();
  });

  filterBadges.forEach(function (badge) {
    badge.addEventListener('click', function () {
      filterBadges.forEach(b => b.classList.remove('active'));
      this.classList.add('active');
      activeType = this.dataset.type;
      applyFilters();
    });
  });

  // Init
  searchInput.focus();
})();
</script>

<!-- Chatbot Widget -->
<script src="assets/js/chatbot.js"></script>

<!-- Panier Widget -->
<script src="assets/js/panier.js"></script>

</body>
</html>
