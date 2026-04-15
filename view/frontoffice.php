<?php
include '../controller/formationC.php';
$formationC = new formationC();
$listeFormations = $formationC->listeFormation();
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
    <div class="formations-grid">
      <?php 
      $colors = ['cat-color-1', 'cat-color-2', 'cat-color-3', 'cat-color-4', 'cat-color-5'];
      $i = 0;
      foreach ($listeFormations as $f): 
          $catColor = $colors[$i % count($colors)];
          $i++;
      ?>
      <div class="formation-card">
        <div class="card-img <?= $catColor ?>">
          <span class="card-img-emoji">🎓</span>
          <span class="card-img-badge"> <?= htmlspecialchars($f['categorie']) ?></span>
          <span class="card-img-type online">En ligne / Présentiel</span>
        </div>
        <div class="card-body">
          <div class="card-title"><?= htmlspecialchars($f['titre']) ?></div>
        </div>
        <div class="card-footer">
          <span class="card-price"><?= htmlspecialchars($f['prix']) ?> €</span>
          <span class="card-sessions">📅 <?= htmlspecialchars($f['duree']) ?></span>
        </div>
        <!-- Hover overlay -->
        <div class="card-hover-overlay">
          <div class="overlay-info"><span class="overlay-info-icon">⏱️</span> Durée : <?= htmlspecialchars($f['duree']) ?></div>
          <div class="overlay-info"><span class="overlay-info-icon">📅</span> Date : <?= htmlspecialchars($f['date']) ?></div>
          <button class="overlay-enroll-btn">S'inscrire →</button>
        </div>
      </div>
      <?php endforeach; ?>
      <?php if(empty($listeFormations)): ?>
        <p style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #64748B;">Aucune formation disponible pour le moment.</p>
      <?php endif; ?>
    </div>
  </div>
</div>

</body>
</html>
