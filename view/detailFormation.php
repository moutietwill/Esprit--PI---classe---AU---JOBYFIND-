<?php
include '../controller/formationC.php';

$formationC = new formationC();

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: frontoffice.php');
    exit();
}

$f = $formationC->getFormationById($_GET['id']);

if (!$f) {
    header('Location: frontoffice.php');
    exit();
}

// Theme colors (always use blue as requested)
$colors = ['#2563EB', '#1E40AF']; // Primary blue and dark blue

// Category emoji
$catEmojis = [
    'Marketing Digital'   => '📉',
    'Développement Web'   => '💻',
    'Finance & Gestion'   => '💰',
    'Communication'       => '🎤',
    'Design & UX'         => '🎨',
    'Autre'               => '📂',
];
$emoji = $catEmojis[$f['categorie']] ?? '📂';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Jobyfind – <?= htmlspecialchars($f['titre']) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800;900&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="jobyfind-style.css">
<style>
  /* ═══ DETAIL PAGE ═══ */
  .detail-hero {
    background: linear-gradient(135deg, <?= $colors[0] ?>, <?= $colors[1] ?>);
    padding: 48px 40px 56px;
    position: relative;
    overflow: hidden;
  }
  .detail-hero::before {
    content: '';
    position: absolute;
    top: -80px;
    right: -60px;
    width: 300px;
    height: 300px;
    background: rgba(255,255,255,.06);
    border-radius: 50%;
  }
  .detail-hero::after {
    content: '';
    position: absolute;
    bottom: -50px;
    left: 15%;
    width: 200px;
    height: 200px;
    background: rgba(255,255,255,.04);
    border-radius: 50%;
  }
  .detail-hero-inner {
    max-width: 1100px;
    margin: 0 auto;
    position: relative;
    z-index: 1;
  }
  .detail-breadcrumb {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: .84rem;
    color: rgba(255,255,255,.6);
    margin-bottom: 18px;
  }
  .detail-breadcrumb a {
    color: rgba(255,255,255,.7);
    text-decoration: none;
    transition: .2s;
  }
  .detail-breadcrumb a:hover { color: #fff; }
  .detail-cat-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: rgba(255,255,255,.18);
    backdrop-filter: blur(8px);
    color: #fff;
    padding: 6px 16px;
    border-radius: 50px;
    font-size: .82rem;
    font-weight: 700;
    margin-bottom: 14px;
    border: 1px solid rgba(255,255,255,.2);
  }
  .detail-hero h1 {
    color: #fff;
    font-size: 2.1rem;
    line-height: 1.25;
    margin-bottom: 16px;
    max-width: 700px;
  }
  .detail-meta {
    display: flex;
    gap: 24px;
    flex-wrap: wrap;
  }
  .detail-meta-item {
    display: flex;
    align-items: center;
    gap: 7px;
    color: rgba(255,255,255,.8);
    font-size: .88rem;
    font-weight: 500;
  }
  .detail-meta-icon {
    font-size: 1rem;
  }

  /* ═══ CONTENT LAYOUT ═══ */
  .detail-content {
    max-width: 1100px;
    margin: -32px auto 0;
    padding: 0 24px 60px;
    display: grid;
    grid-template-columns: 1fr 340px;
    gap: 28px;
    position: relative;
    z-index: 2;
  }

  /* Left: description */
  .detail-main {
    background: var(--white);
    border-radius: var(--radius);
    border: 1px solid var(--gray-200);
    padding: 32px;
    box-shadow: var(--shadow-sm);
  }
  .detail-section-title {
    font-size: 1.1rem;
    color: var(--navy);
    font-family: 'Nunito', sans-serif;
    font-weight: 800;
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 8px;
  }
  .detail-section-title::before {
    content: '';
    width: 4px;
    height: 22px;
    background: linear-gradient(180deg, <?= $colors[0] ?>, <?= $colors[1] ?>);
    border-radius: 4px;
  }
  .detail-description {
    font-size: .92rem;
    color: var(--gray-600);
    line-height: 1.75;
    white-space: pre-line;
  }
  .detail-no-desc {
    color: var(--gray-400);
    font-style: italic;
    font-size: .88rem;
  }

  /* Right: sidebar */
  .detail-sidebar {
    display: flex;
    flex-direction: column;
    gap: 20px;
  }

  .detail-features {
    background: var(--white);
    border-radius: var(--radius);
    border: 1px solid var(--gray-200);
    overflow: hidden;
    box-shadow: var(--shadow-sm);
  }
  .detail-features-header {
    background: linear-gradient(135deg, <?= $colors[0] ?>, <?= $colors[1] ?>);
    color: #fff;
    font-family: 'Nunito', sans-serif;
    font-weight: 800;
    font-size: 1.05rem;
    text-align: center;
    padding: 16px;
    letter-spacing: .02em;
  }
  .detail-features-list {
    padding: 6px 0;
  }
  .detail-feature-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 22px;
    border-bottom: 1px solid var(--gray-100);
    font-size: .88rem;
    color: var(--gray-700);
  }
  .detail-feature-item:last-child {
    border-bottom: none;
  }
  .detail-feature-label {
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 500;
  }
  .detail-feature-icon {
    font-size: 1.05rem;
    width: 22px;
    text-align: center;
  }
  .detail-feature-value {
    background: var(--blue-light);
    color: var(--blue);
    font-weight: 700;
    font-size: .82rem;
    padding: 4px 12px;
    border-radius: 20px;
    font-family: 'Nunito', sans-serif;
  }

  /* Price */
  .detail-price-box {
    background: linear-gradient(135deg, <?= $colors[0] ?>, <?= $colors[1] ?>);
    color: #fff;
    text-align: center;
    padding: 18px;
    border-radius: var(--radius);
    font-family: 'Nunito', sans-serif;
    font-weight: 800;
    font-size: 1.4rem;
    letter-spacing: .02em;
    box-shadow: 0 4px 16px rgba(0,0,0,.15);
  }
  .detail-price-label {
    font-size: .78rem;
    font-weight: 600;
    opacity: .8;
    text-transform: uppercase;
    letter-spacing: .1em;
    margin-bottom: 2px;
  }

  /* CTA button */
  .detail-cta {
    display: block;
    text-align: center;
    padding: 16px;
    background: var(--navy);
    color: #fff;
    border-radius: var(--radius);
    font-family: 'Nunito', sans-serif;
    font-weight: 800;
    font-size: 1rem;
    text-decoration: none;
    transition: all .25s;
    box-shadow: 0 4px 16px rgba(30,45,90,.25);
    letter-spacing: .02em;
  }
  .detail-cta:hover {
    background: var(--navy-dark);
    transform: translateY(-2px);
    box-shadow: 0 6px 24px rgba(30,45,90,.35);
  }

  /* Back link */
  .detail-back {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    margin-top: 20px;
    font-size: .85rem;
    font-weight: 600;
    color: var(--gray-500);
    text-decoration: none;
    transition: .2s;
  }
  .detail-back:hover { color: var(--blue); }

  /* Responsive */
  @media (max-width: 768px) {
    .detail-content {
      grid-template-columns: 1fr;
    }
    .detail-hero h1 { font-size: 1.6rem; }
  }
</style>
</head>
<body>

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

<!-- Hero Banner -->
<section class="detail-hero">
  <div class="detail-hero-inner">
    <div class="detail-breadcrumb">
      <a href="frontoffice.php">Formations</a>
      <span>›</span>
      <span><?= htmlspecialchars($f['categorie']) ?></span>
      <span>›</span>
      <span style="color:#fff;"><?= htmlspecialchars($f['titre']) ?></span>
    </div>
    <div class="detail-cat-badge"><?= $emoji ?> <?= htmlspecialchars($f['categorie']) ?></div>
    <h1><?= htmlspecialchars($f['titre']) ?></h1>
    <div class="detail-meta">
      <div class="detail-meta-item">
        <span class="detail-meta-icon">📅</span>
        <?= htmlspecialchars($f['date']) ?>
      </div>
      <div class="detail-meta-item">
        <span class="detail-meta-icon">⏱️</span>
        <?= htmlspecialchars($f['duree']) ?>
      </div>
      <div class="detail-meta-item">
        <span class="detail-meta-icon">💶</span>
        <?= htmlspecialchars($f['prix']) ?> €
      </div>
    </div>
  </div>
</section>

<!-- Content -->
<div class="detail-content">
  <!-- Left: description -->
  <div class="detail-main">
    <h2 class="detail-section-title">À propos de cette formation</h2>
    <?php if (!empty($f['description'])): ?>
      <p class="detail-description"><?= htmlspecialchars($f['description']) ?></p>
    <?php else: ?>
      <p class="detail-no-desc">Aucune description disponible pour cette formation.</p>
    <?php endif; ?>

    <a href="frontoffice.php" class="detail-back">← Retour aux formations</a>
  </div>

  <!-- Right: sidebar -->
  <div class="detail-sidebar">
    <!-- Course features -->
    <div class="detail-features">
      <div class="detail-features-header">Détails de la formation</div>
      <div class="detail-features-list">
        <div class="detail-feature-item">
          <div class="detail-feature-label">
            <span class="detail-feature-icon">📅</span> Date de début
          </div>
          <span class="detail-feature-value"><?= htmlspecialchars($f['date']) ?></span>
        </div>
        <div class="detail-feature-item">
          <div class="detail-feature-label">
            <span class="detail-feature-icon">⏱️</span> Durée
          </div>
          <span class="detail-feature-value"><?= htmlspecialchars($f['duree']) ?></span>
        </div>
        <div class="detail-feature-item">
          <div class="detail-feature-label">
            <span class="detail-feature-icon"><?= $emoji ?></span> Catégorie
          </div>
          <span class="detail-feature-value"><?= htmlspecialchars($f['categorie']) ?></span>
        </div>
        <div class="detail-feature-item">
          <div class="detail-feature-label">
            <span class="detail-feature-icon">🌐</span> Mode
          </div>
          <span class="detail-feature-value">En ligne / Présentiel</span>
        </div>
      </div>
    </div>

    <!-- Price box -->
    <div class="detail-price-box">
      <div class="detail-price-label">Prix de la formation</div>
      <?= htmlspecialchars($f['prix']) ?> €
    </div>

    <!-- CTA -->
    <a href="ajouterInscription.php?id_formation=<?= $f['id'] ?>" class="detail-cta">
      S'inscrire maintenant →
    </a>
  </div>
</div>

</body>
</html>
