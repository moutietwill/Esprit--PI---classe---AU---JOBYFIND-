<?php
session_start();
include '../model/inscription.php';
include '../controller/inscriptionC.php';

$success = false;
$errorMsg = "";

// Vérifier si un session_id est passé et s'il y a une inscription en attente
if (isset($_GET['session_id']) && isset($_SESSION['pending_inscription'])) {
    
    $pending = $_SESSION['pending_inscription'];
    
    // Créer l'objet inscription et l'ajouter à la BD
    $inscription = new inscription(
        $pending['nom'],
        $pending['prenom'],
        $pending['email'],
        $pending['telephone'],
        $pending['methode_paiement'],
        $pending['id_formation']
    );
    
    $inscriptionC = new inscriptionC();
    $inscriptionC->addInscription($inscription);
    
    // Récupérer le titre de la formation pour l'email
    include_once '../controller/formationC.php';
    include_once '../controller/mailer.php';
    
    $formationC = new formationC();
    $formation = $formationC->getFormationById($pending['id_formation']);
    $titreFormation = $formation ? $formation['titre'] : 'Formation';
    
    // Envoyer l'email
    FormationMailer::sendConfirmationEmail($pending['email'], $pending['nom'], $pending['prenom'], $titreFormation);
    
    // Vider la session
    unset($_SESSION['pending_inscription']);
    
    $success = true;
    
} elseif (!isset($_GET['session_id'])) {
    $errorMsg = "Accès invalide. Aucun identifiant de session Stripe fourni.";
} else {
    // Session id present but no pending inscription in session
    // It means either it was already processed or session expired.
    $errorMsg = "Votre inscription a déjà été traitée ou la session a expiré.";
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Jobyfind – Paiement Réussi</title>
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800;900&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="jobyfind-style.css">
<style>
  body {
    background: #F8FAFC;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 100vh;
    margin: 0;
    padding: 20px;
    font-family: 'DM Sans', sans-serif;
  }
  .success-card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.05);
    padding: 40px;
    max-width: 480px;
    width: 100%;
    text-align: center;
    animation: slideUp .4s ease;
  }
  @keyframes slideUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
  }
  .icon-circle {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
    margin: 0 auto 20px;
  }
  .icon-success {
    background: #D1FAE5;
    color: #10B981;
  }
  .icon-error {
    background: #FEE2E2;
    color: #EF4444;
  }
  h1 {
    font-family: 'Nunito', sans-serif;
    font-weight: 800;
    color: var(--navy);
    font-size: 1.5rem;
    margin: 0 0 12px;
  }
  p {
    color: var(--gray-500);
    font-size: 0.95rem;
    line-height: 1.5;
    margin: 0 0 30px;
  }
  .btn-primary {
    display: inline-block;
    background: var(--blue);
    color: #fff;
    text-decoration: none;
    padding: 12px 28px;
    border-radius: 50px;
    font-weight: 600;
    transition: all .2s;
  }
  .btn-primary:hover {
    background: #1D4ED8;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(37,99,235,0.2);
  }
</style>
</head>
<body>

<div class="success-card">
  <?php if ($success): ?>
    <div class="icon-circle icon-success">✓</div>
    <h1>Paiement Réussi !</h1>
    <p>Merci pour votre confiance. Votre paiement par carte a bien été validé via Stripe et votre inscription à la formation est désormais confirmée.</p>
  <?php else: ?>
    <div class="icon-circle icon-error">✕</div>
    <h1>Oups, un problème est survenu</h1>
    <p><?= htmlspecialchars($errorMsg) ?></p>
  <?php endif; ?>
  
  <a href="frontoffice.php" class="btn-primary">Retour aux formations</a>
</div>

</body>
</html>
