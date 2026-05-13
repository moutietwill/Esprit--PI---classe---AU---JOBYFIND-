<?php
include '../controller/formationC.php';
$formationC = new formationC();
$listeFormations = $formationC->listeFormation();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Jobyfind — Gestion des Formations</title>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Serif+Display&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <link rel="stylesheet" href="backoffice/assets/css/styleadmin.css">
</head>
<body>

  <aside class="sidebar">
    <div class="sidebar-logo">
      <div class="logo-icon" style="background-color: #7EB2FF;"><img src="backoffice/assets/images/jlog.png" style="width: 30px; height: 30px;" onerror="this.style.display='none'"></div>
      <div class="logo-text">Joby<span>find</span></div>
      <span class="sidebar-badge">Admin</span>
    </div>
    
    <div class="sidebar-section">
      <p class="sidebar-section-label">Tableau de bord</p>
      <a class="sidebar-link" href="backoffice/admine.php">
        <i class="fa-solid fa-users"></i>
        <span>Utilisateurs</span>
      </a>
      <a class="sidebar-link" href="backoffice/admine.php?sort=stats">
        <i class="fa-solid fa-chart-line"></i>
        <span>Statistiques</span>
      </a>
    </div>

    <div class="sidebar-section">
      <p class="sidebar-section-label">Gestion</p>
      <a class="sidebar-link" href="/yassine/web/view/backoffice/quiz-list-admin.php">
        <i class="fa-solid fa-lightbulb"></i>
        <span>Gestion des Quiz</span>
      </a>
      <a class="sidebar-link" href="../projetweb/view/backoffice.php">
        <i class="fa-solid fa-blog"></i>
        <span>Blogs</span>
      </a>
      <a class="sidebar-link" href="../public/index.php/admin/events">
        <i class="fa-solid fa-calendar-star"></i>
        <span>Événements</span>
      </a>
      <a class="sidebar-link active" href="backoffice.php">
        <i class="fa-solid fa-graduation-cap"></i>
        <span>Formations</span>
      </a>
      <a class="sidebar-link" href="backofficeInscription.php">
        <i class="fa-solid fa-clipboard-list"></i>
        <span>Inscriptions</span>
      </a>
      <a class="sidebar-link" href="#">
        <i class="fa-solid fa-briefcase"></i>
        <span>Offres</span>
      </a>
      <a class="sidebar-link" href="#">
        <i class="fa-solid fa-gear"></i>
        <span>Paramètres</span>
      </a>
    </div>

    <div class="sidebar-footer">
      <div class="admin-profile">
        <div class="admin-avatar">A</div>
        <div class="admin-info">
          <p>Admin</p>
        </div>
        <button class="logout-btn" title="Déconnexion" onclick="window.location.href='backoffice/logout.php'">
          <i class="fa fa-right-from-bracket"></i>
        </button>
      </div>
    </div>
  </aside>

  <div class="main">
    <header class="header">
      <div class="header-breadcrumb">
        <span>Admin</span> <i class="fa fa-chevron-right" style="font-size:9px"></i> <span class="current">Formations</span>
      </div>
    </header>

    <div class="content">
      <div class="table-card">
        <div class="table-header">
          <div>
            <p class="table-title">Liste des Formations</p>
            <p class="table-subtitle"><?= count($listeFormations) ?> formation(s) enregistrée(s)</p>
          </div>
          <div class="table-controls">
            <a href="ajouterFormation.php" class="btn-primary" style="text-decoration:none">
              <i class="fa fa-plus"></i> Ajouter
            </a>
          </div>
        </div>

        <table>
          <thead>
            <tr>
              <th>Titre</th>
              <th>Catégorie</th>
              <th>Prix</th>
              <th>Date</th>
              <th>Durée</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($listeFormations as $f): ?>
            <tr>
              <td>
                <div class="user-name"><?= htmlspecialchars($f['titre']) ?></div>
                <div class="user-email" style="max-width:300px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="<?= htmlspecialchars($f['description']) ?>">
                  <?= htmlspecialchars($f['description']) ?>
                </div>
              </td>
              <td><span class="badge badge-blue"><?= htmlspecialchars($f['categorie']) ?></span></td>
              <td><strong style="color:var(--blue)"><?= htmlspecialchars($f['prix']) ?> €</strong></td>
              <td><?= htmlspecialchars($f['date']) ?></td>
              <td><span class="badge badge-gray"><?= htmlspecialchars($f['duree']) ?></span></td>
              <td>
                <div class="action-btns">
                  <a href="modifierFormation.php?id=<?= $f['id'] ?>" class="action-btn edit" title="Modifier"><i class="fa fa-pen"></i></a>
                  <a href="supprimerFormation.php?id=<?= $f['id'] ?>" class="action-btn del" onclick="return confirm('Supprimer la formation <?= addslashes($f['titre']) ?> ?')" title="Supprimer"><i class="fa fa-trash"></i></a>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
            <?php if(empty($listeFormations)): ?>
              <tr><td colspan="6" style="text-align:center;color:var(--muted);padding:40px">Aucune formation trouvée.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

</body>
</html>
