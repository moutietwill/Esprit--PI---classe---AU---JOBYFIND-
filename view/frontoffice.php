<?php
include '../controller/formationC.php';
include '../controller/avisC.php';
$formationC = new formationC();
$avisC = new avisC();
$listeFormations = $formationC->listeFormation();

// Attach ratings and sort by highest rating
foreach ($listeFormations as &$f) {
    $stats = $avisC->getAverageRating($f['id']);
    $f['avgRating'] = $stats['moyenne'];
    $f['ratingCount'] = $stats['count'];
}
unset($f);

usort($listeFormations, function($a, $b) {
    return $b['avgRating'] <=> $a['avgRating'];
});
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Jobyfind – Formations (Frontoffice)</title>
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800;900&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="jobyfind-style.css">
</head>
<body>

<div id="view-front" style="display:block">
  <!-- Navbar -->
  <nav class="navbar">
    <div class="navbar-brand"><span>Joby</span><span>find</span></div>
    <div class="navbar-links">
      <a href="frontoffice.php" class="active">Formations</a>
      <a href="#">À propos</a>
      <a href="#">Contact</a>
    </div>
    <div class="nav-actions">
      <button class="btn-outline">Connexion</button>
      <button class="btn-primary">S'inscrire</button>
      <a href="backoffice.php" class="btn-admin" style="text-decoration:none">⚙ Admin</a>
    </div>
  </nav>

  <!-- Hero -->
  <section class="hero">
    <div class="hero-inner">
      <h1>Développez vos <em>compétences</em><br>avec les meilleures formations</h1>
      <p>Découvrez nos formations animées par des mentors experts pour booster votre carrière ou votre projet entrepreneurial.</p>
      <div class="hero-stats">
        <div class="hero-stat"><strong><?= count($listeFormations) ?></strong><span>Formations disponibles</span></div>
      </div>
    </div>
  </section>

  <!-- Main -->
  <div class="main-content">
    <p class="section-label">Catalogue</p>
    <h2 class="section-title">Découvrez nos formations</h2>



    <!-- Formation Cards -->
    <div class="formations-grid" id="formationsGrid">
      <!-- No Results Message -->
      <div id="noResults" style="display: none; grid-column: 1 / -1; text-align: center; padding: 60px 20px; background: #fff; border-radius: var(--radius); border: 1px dashed var(--gray-300);">
        <div style="font-size: 2rem; margin-bottom: 12px;">🧐</div>
        <h3 style="color: var(--navy); font-size: 1.1rem; margin-bottom: 6px;">Aucune formation trouvée</h3>
        <p style="color: var(--gray-500); font-size: 0.9rem;">Essayez d'ajuster vos filtres ou votre recherche.</p>
        <button onclick="resetFilters()" style="margin-top: 16px; padding: 8px 20px; background: var(--blue-light); color: var(--blue); border-radius: 50px; font-weight: 600; font-size: 0.85rem; border: none; cursor: pointer;">Réinitialiser les filtres</button>
      </div>

      <?php 
      $colors = ['cat-color-1', 'cat-color-2', 'cat-color-3', 'cat-color-4', 'cat-color-5'];
      $i = 0;
      foreach ($listeFormations as $f): 
          $catColor = $colors[$i % count($colors)];
          $i++;
          // Convert date "YYYY-MM-DD" to timestamp for sorting
          $timestamp = strtotime($f['date']) ?: 0;
          $titreLower = strtolower($f['titre']);
          
          $avgRating = $f['avgRating'];
          $ratingCount = $f['ratingCount'];
      ?>
      <div class="formation-card" data-titre="<?= htmlspecialchars($titreLower) ?>" data-categorie="<?= htmlspecialchars($f['categorie']) ?>" data-prix="<?= htmlspecialchars($f['prix']) ?>" data-date="<?= $timestamp ?>" data-rating="<?= $avgRating ?>">
        <div class="card-img <?= $catColor ?>">
          <span class="card-img-emoji">🎓</span>
          <span class="card-img-badge"> <?= htmlspecialchars($f['categorie']) ?></span>
          <span class="card-img-type online">En ligne / Présentiel</span>
        </div>
        <div class="card-body">
          <div class="card-title"><?= htmlspecialchars($f['titre']) ?></div>
          <div style="margin-top: 8px; display: flex; align-items: center; gap: 6px; font-size: 0.85rem; color: var(--gray-500);">
            <div style="color: #F59E0B; font-size: 1rem; letter-spacing: -2px;">
              <?php
                $fullStars = floor($avgRating);
                $emptyStars = 5 - $fullStars;
                echo str_repeat('★', $fullStars) . str_repeat('☆', $emptyStars);
              ?>
            </div>
            <span><?= $avgRating ?> (<?= $ratingCount ?>)</span>
          </div>
        </div>
        <div class="card-footer">
          <span class="card-price"><?= htmlspecialchars($f['prix']) ?> €</span>
          <span class="card-sessions">📅 <?= htmlspecialchars($f['duree']) ?></span>
        </div>
        <!-- Hover overlay -->
        <div class="card-hover-overlay">
          <div class="overlay-info"><span class="overlay-info-icon">⏱️</span> Durée : <?= htmlspecialchars($f['duree']) ?></div>
          <div class="overlay-info"><span class="overlay-info-icon">📅</span> Date : <?= htmlspecialchars($f['date']) ?></div>
          <a href="detailFormation.php?id=<?= $f['id'] ?>" class="overlay-enroll-btn" style="text-decoration:none; text-align:center; display:block; background:var(--white); color:var(--navy);">Voir les détails →</a>
          <a href="ajouterInscription.php?id_formation=<?= $f['id'] ?>" class="overlay-enroll-btn" style="text-decoration:none; text-align:center; display:block;">S'inscrire →</a>
        </div>
      </div>
      <?php endforeach; ?>
      <?php if(empty($listeFormations)): ?>
        <p style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #64748B;">Aucune formation disponible pour le moment.</p>
      <?php endif; ?>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  // 1. Inject CSS dynamically via JS
  const style = document.createElement('style');
  style.innerHTML = `
    .filter-bar { display: flex; gap: 16px; margin-bottom: 30px; justify-content: space-between; flex-wrap: wrap; align-items: center; background: var(--white); padding: 16px 24px; border-radius: var(--radius); box-shadow: var(--shadow-sm); border: 1px solid var(--gray-200); }
    .search-box { position: relative; flex: 1; min-width: 250px; }
    .search-icon { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--gray-400); font-size: 1.1rem; }
    .search-box input { width: 100%; padding: 12px 14px 12px 42px; border: 1.5px solid var(--gray-200); border-radius: 50px; font-size: 0.95rem; color: var(--navy); transition: .2s; background: var(--gray-50); }
    .search-box input:focus { border-color: var(--blue); background: var(--white); outline: none; box-shadow: 0 0 0 3px rgba(37,99,235,.1); }
    .filter-controls { display: flex; gap: 12px; flex-wrap: wrap; }
    .filter-controls select { padding: 12px 36px 12px 16px; border: 1.5px solid var(--gray-200); border-radius: 50px; font-size: 0.9rem; font-weight: 600; color: var(--gray-700); background: var(--white) url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2364748B' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E") no-repeat right 14px center; appearance: none; cursor: pointer; transition: .2s; }
    .filter-controls select:focus, .filter-controls select:hover { border-color: var(--blue); outline: none; }
  `;
  document.head.appendChild(style);

  // 2. Inject Toolbar dynamically via JS
  const filterBar = document.createElement('div');
  filterBar.className = 'filter-bar';
  filterBar.innerHTML = `
      <div class="search-box">
        <span class="search-icon">🔍</span>
        <input type="text" id="searchInput" placeholder="Rechercher une formation...">
      </div>
      <div class="filter-controls">
        <select id="categoryFilter">
          <option value="all">Toutes les catégories</option>
          <option value="Marketing Digital">Marketing Digital</option>
          <option value="Développement Web">Développement Web</option>
          <option value="Finance & Gestion">Finance & Gestion</option>
          <option value="Communication">Communication</option>
          <option value="Design & UX">Design & UX</option>
          <option value="Autre">Autre</option>
        </select>
        <select id="sortControl">
          <option value="rating-desc">Trier par : Mieux noté</option>
          <option value="default">Plus récent</option>
          <option value="price-asc">Prix : Croissant (Moins cher)</option>
          <option value="price-desc">Prix : Décroissant (Plus cher)</option>
        </select>
      </div>
  `;

  const grid = document.getElementById('formationsGrid');
  grid.parentNode.insertBefore(filterBar, grid);

  // 3. Filtering and sorting logic
  const searchInput = document.getElementById('searchInput');
  const categoryFilter = document.getElementById('categoryFilter');
  const sortControl = document.getElementById('sortControl');
  const noResultsMsg = document.getElementById('noResults');
  
  const cards = Array.from(grid.querySelectorAll('.formation-card'));

  function filterAndSort() {
    const searchTerm = searchInput.value.toLowerCase().trim();
    const category = categoryFilter.value;
    const sortBy = sortControl.value;
    
    let visibleCount = 0;
    let filteredCards = [];

    cards.forEach(card => {
      const title = card.getAttribute('data-titre');
      const cat = card.getAttribute('data-categorie');
      
      const matchesSearch = title.includes(searchTerm);
      const matchesCategory = (category === 'all' || cat === category);
      
      if (matchesSearch && matchesCategory) {
        card.style.display = 'block';
        visibleCount++;
        filteredCards.push(card);
      } else {
        card.style.display = 'none';
      }
    });

    if (visibleCount === 0) {
      noResultsMsg.style.display = 'block';
    } else {
      noResultsMsg.style.display = 'none';
      
      filteredCards.sort((a, b) => {
        if (sortBy === 'price-asc') {
          return parseFloat(a.getAttribute('data-prix')) - parseFloat(b.getAttribute('data-prix'));
        } else if (sortBy === 'price-desc') {
          return parseFloat(b.getAttribute('data-prix')) - parseFloat(a.getAttribute('data-prix'));
        } else if (sortBy === 'rating-desc') {
          return parseFloat(b.getAttribute('data-rating')) - parseFloat(a.getAttribute('data-rating'));
        } else {
          return parseInt(b.getAttribute('data-date')) - parseInt(a.getAttribute('data-date'));
        }
      });

      filteredCards.forEach(card => grid.appendChild(card));
    }
  }

  // Bind Events
  searchInput.addEventListener('input', filterAndSort);
  categoryFilter.addEventListener('change', filterAndSort);
  sortControl.addEventListener('change', filterAndSort);

  // Expose global window function for the inline button
  window.resetFilters = function() {
    searchInput.value = '';
    categoryFilter.value = 'all';
    sortControl.value = 'rating-desc';
    filterAndSort();
  };
});
</script>

</body>
</html>
