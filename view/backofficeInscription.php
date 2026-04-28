<?php
include '../controller/inscriptionC.php';
include '../controller/formationC.php';

$inscriptionC = new inscriptionC();
$formationC = new formationC();
$listeInscriptions = $inscriptionC->listeInscription();
$listeFormations = $formationC->listeFormation();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Jobyfind – Gestion des inscriptions</title>
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800;900&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="jobyfind-style.css">
<style>
  .badge-d17 {
    background: #DBEAFE; color: #1D4ED8; padding: 4px 10px; border-radius: 20px;
    font-size: .75rem; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;
  }
  .badge-visa {
    background: #EDE9FE; color: #6D28D9; padding: 4px 10px; border-radius: 20px;
    font-size: .75rem; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;
  }
</style>
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
        <a href="backoffice.php" class="back-nav-item" style="text-decoration:none">
          <span class="nav-icon">🎓</span> Formations
        </a>
        <a href="backofficeInscription.php" class="back-nav-item active" style="text-decoration:none">
          <span class="nav-icon">📋</span> Inscriptions
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
        <div class="back-breadcrumb">Admin › <strong>Inscriptions</strong></div>
        <div class="back-topbar-right">
          <a href="frontoffice.php" class="topbar-icon-btn" title="Voir le site" style="text-decoration:none; padding-top:4px;">👁</a>
        </div>
      </div>

      <div class="back-content">
        <div>
          <h2 class="back-page-title">Gestion des inscriptions</h2>
          <p class="back-page-sub">Consultez et gérez les inscriptions aux formations</p>
          <div class="back-panel">
            <div class="back-panel-header">
              <div>
                <div class="back-panel-title">Toutes les Inscriptions</div>
                <div class="back-panel-sub"><?= count($listeInscriptions) ?> inscription(s) enregistrée(s)</div>
              </div>
              <div class="back-panel-actions">
                <a href="ajouterInscription.php" class="btn-add">＋ Nouvelle inscription</a>
              </div>
            </div>
            <table class="back-table">
              <thead>
                <tr>
                  <th>Participant</th>
                  <th>Email</th>
                  <th>Téléphone</th>
                  <th>Formation</th>
                  <th>Paiement</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach($listeInscriptions as $insc): ?>
                <tr>
                  <td>
                    <div class="enroll-course"><?= htmlspecialchars($insc['prenom'] . ' ' . $insc['nom']) ?></div>
                  </td>
                  <td><?= htmlspecialchars($insc['email']) ?></td>
                  <td><?= htmlspecialchars($insc['telephone']) ?></td>
                  <td><span class="badge badge-blue"><?= htmlspecialchars($insc['formation_titre']) ?></span></td>
                  <td>
                    <?php if ($insc['methode_paiement'] === 'D17'): ?>
                      <span class="badge-d17">🏦 D17</span>
                    <?php else: ?>
                      <span class="badge-visa">💳 Visa</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <div class="action-btns">
                      <a href="modifierInscription.php?id=<?= $insc['id'] ?>" class="action-btn" title="Modifier">✏️</a>
                      <a href="supprimerInscription.php?id=<?= $insc['id'] ?>" class="action-btn delete" onclick="return confirm('Confirmer la suppression de l\'inscription de <?= addslashes($insc['prenom'] . ' ' . $insc['nom']) ?> ?')" title="Supprimer">🗑️</a>
                    </div>
                  </td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($listeInscriptions)): ?>
                  <tr><td colspan="6" style="text-align:center;color:var(--gray-400);padding:30px">Aucune inscription trouvée.</td></tr>
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
