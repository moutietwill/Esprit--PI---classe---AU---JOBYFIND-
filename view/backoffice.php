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
<title>Jobyfind – Backoffice</title>
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800;900&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="jobyfind-style.css">
</head>
<body>

<div id="view-back" style="display:flex">
  <div class="back-layout" style="width:100%;">
    <!-- Sidebar -->
    <aside class="back-sidebar">
      <div class="back-logo">
        <div class="navbar-brand" style="font-size:1.2rem"><span style="color:#fff">Joby</span><span style="color:#93C5FD">find</span></div>
        <span class="back-logo-badge">ADMIN</span>
      </div>
      <nav class="back-nav">
        <div class="back-nav-section">Gestion</div>
        <a href="backoffice.php" class="back-nav-item active" style="text-decoration:none">
          <span class="nav-icon">🎓</span> Formations
        </a>
      </nav>
      <div class="back-footer">
        <div class="back-footer-avatar">A</div>
        <div class="back-footer-info">
          <div class="back-footer-name">Admin</div>
        </div>
        <a href="frontoffice.php" class="back-logout" title="Retour au site" style="text-decoration:none; padding-top:6px;">↩</a>
      </div>
    </aside>

    <!-- Main area -->
    <div class="back-main">
      <div class="back-topbar">
        <div class="back-breadcrumb">Admin › <strong>Formations</strong></div>
        <div class="back-topbar-right">
          <a href="frontoffice.php" class="topbar-icon-btn" title="Voir le site" style="text-decoration:none; padding-top:4px;">👁</a>
        </div>
      </div>

      <div class="back-content">
        <!-- FORMATIONS SECTION -->
        <div>
          <h2 class="back-page-title">Gestion des formations</h2>
          <p class="back-page-sub">Gérez les formations de Jobyfind via la base de données</p>
          <div class="back-panel">
            <div class="back-panel-header">
              <div>
                <div class="back-panel-title">Toutes les Formations</div>
                <div class="back-panel-sub"><?= count($listeFormations) ?> formation(s) enregistrée(s)</div>
              </div>
              <div class="back-panel-actions">
                <a href="ajouterFormation.php" class="btn-add">＋ Nouvelle formation</a>
              </div>
            </div>
            <table class="back-table">
              <thead>
                <tr>
                  <th>Titre & Description</th>
                  <th>Catégorie</th>
                  <th>Date</th>
                  <th>Durée</th>
                  <th>Prix</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach($listeFormations as $f): ?>
                <tr>
                  <td>
                    <div class="enroll-course"><?= htmlspecialchars($f['titre']) ?></div>
                    <div class="enroll-date" style="max-width:250px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="<?= htmlspecialchars($f['description']) ?>"><?= htmlspecialchars($f['description']) ?></div>
                  </td>
                  <td><span class="badge badge-blue"><?= htmlspecialchars($f['categorie']) ?></span></td>
                  <td><?= htmlspecialchars($f['date']) ?></td>
                  <td><?= htmlspecialchars($f['duree']) ?></td>
                  <td><strong style="color:var(--blue)"><?= htmlspecialchars($f['prix']) ?> €</strong></td>
                  <td>
                    <div class="action-btns">
                      <a href="modifierFormation.php?id=<?= $f['id'] ?>" class="action-btn" title="Modifier">✏️</a>
                      <a href="supprimerFormation.php?id=<?= $f['id'] ?>" class="action-btn delete" onclick="return confirm('Confirmer la suppression de la formation <?= addslashes($f['titre']) ?> ?')" title="Supprimer">🗑️</a>
                    </div>
                  </td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($listeFormations)): ?>
                  <tr><td colspan="6" style="text-align:center;color:var(--gray-400);padding:30px">Aucune formation trouvée. Créez-en une l'aide du bouton + Nouvelle formation.</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

</body>
</html>
