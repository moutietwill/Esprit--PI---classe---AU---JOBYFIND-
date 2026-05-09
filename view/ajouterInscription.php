<?php
session_start();
include '../model/inscription.php';
include '../model/formation.php';
include '../controller/inscriptionC.php';
include '../controller/formationC.php';
include '../controller/api_stripe.php';

$error = "";
$success = "";

// Affichage message de Stripe "annulé" si retour
if (isset($_GET['status']) && $_GET['status'] === 'cancel') {
    $error = "Paiement annulé. Vous pouvez réessayer.";
}

$inscriptionC = new inscriptionC();
$formationC = new formationC();

// Get formation id from URL
$id_formation = isset($_GET['id_formation']) ? (int)$_GET['id_formation'] : 0;
$formation = null;

if ($id_formation > 0) {
    $formation = $formationC->getFormationById($id_formation);
}

// Get all formations for dropdown (in case no id passed)
$listeFormations = $formationC->listeFormation();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (
        !empty($_POST["nom"]) &&
        !empty($_POST["prenom"]) &&
        !empty($_POST["email"]) &&
        !empty($_POST["telephone"]) &&
        !empty($_POST["methode_paiement"]) &&
        !empty($_POST["id_formation"])
    ) {
        
        $post_formation_id = (int)$_POST['id_formation'];
        $selected_formation = $formationC->getFormationById($post_formation_id);
        
        if ($_POST["methode_paiement"] === "Visa Card") {
            // Sauvegarder les données du formulaire dans la session temporairement
            $_SESSION['pending_inscription'] = [
                'nom' => $_POST['nom'],
                'prenom' => $_POST['prenom'],
                'email' => $_POST['email'],
                'telephone' => $_POST['telephone'],
                'methode_paiement' => $_POST['methode_paiement'],
                'id_formation' => $post_formation_id
            ];

            // 1. Préparer les données pour Stripe Checkout
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
            $domainName = $_SERVER['HTTP_HOST'];
            $base_url = $protocol . $domainName . dirname($_SERVER['PHP_SELF']);
            
            $success_url = $base_url . "/success_stripe.php?session_id={CHECKOUT_SESSION_ID}";
            $cancel_url  = $base_url . "/ajouterInscription.php?id_formation=" . $post_formation_id . "&status=cancel";
            
            $prix_cents = intval(floatval($selected_formation['prix']) * 100);
            
            // Si le prix est 0, on pourrait bloquer Stripe, mais on suppose un prix valide.
            if ($prix_cents <= 0) $prix_cents = 1000; // minimum 10€ par ex. en fallback
            
            $data = [
                'payment_method_types' => ['card'],
                'line_items' => [
                    [
                        'price_data' => [
                            'currency' => 'eur',
                            'product_data' => [
                                'name' => 'Inscription: ' . $selected_formation['titre'],
                            ],
                            'unit_amount' => $prix_cents,
                        ],
                        'quantity' => 1,
                    ],
                ],
                'mode' => 'payment',
                'success_url' => $success_url,
                'cancel_url' => $cancel_url,
                'customer_email' => $_POST['email']
            ];

            // 2. Appel cURL à l'API REST Stripe
            $ch = curl_init('https://api.stripe.com/v1/checkout/sessions');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . api_stripe::getSecretKey()
            ]);

            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $result = json_decode($response, true);

            if ($http_code == 200 && isset($result['url'])) {
                // Redirection vers l'URL de paiement Stripe
                header("Location: " . $result['url']);
                exit;
            } else {
                $error = "Erreur de communication avec Stripe: " . (isset($result['error']['message']) ? $result['error']['message'] : 'Erreur inconnue');
            }
            
        } else {
            // Traitement normal (D17)
            $inscription = new inscription(
                $_POST['nom'],
                $_POST['prenom'],
                $_POST['email'],
                $_POST['telephone'],
                $_POST['methode_paiement'],
                $post_formation_id
            );
            $inscriptionC->addInscription($inscription);
            
            // Envoyer l'email de confirmation
            include_once '../controller/mailer.php';
            mailer::sendConfirmationEmail($_POST['email'], $_POST['nom'], $_POST['prenom'], $selected_formation['titre']);
            
            $success = "Inscription enregistrée avec succès ! Un e-mail de confirmation vous a été envoyé.";
        }
    } else {
        $error = "Tous les champs sont obligatoires !";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Jobyfind – Inscription à une formation</title>
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800;900&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="jobyfind-style.css">
<style>
  input:focus, select:focus, textarea:focus {
    border-color: var(--blue) !important;
    background: #fff !important;
    outline: none !important;
    box-shadow: 0 0 0 3px rgba(37,99,235,.09) !important;
  }
  .field-group { display: flex; flex-direction: column; gap: 6px; }
  .field-group label { font-size: .82rem; font-weight: 600; color: var(--gray-700); }
  .field-group input, .field-group select {
    padding: 10px 14px;
    border: 1.5px solid var(--gray-200);
    border-radius: var(--radius-sm);
    font-size: .88rem;
    color: var(--gray-800);
    background: var(--gray-50);
    font-family: 'DM Sans', sans-serif;
    transition: .2s;
    width: 100%;
    box-sizing: border-box;
  }
  .field-error { border-color: #EF4444 !important; background: #FFF5F5 !important; }
  .error-msg { color: #EF4444; font-size: .76rem; margin-top: 4px; font-weight: 600; display: none; }
  .row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px; }
  .row-1 { display: grid; grid-template-columns: 1fr; gap: 16px; margin-bottom: 16px; }

  /* Payment method radio buttons */
  .payment-options { display: flex; gap: 14px; margin-top: 4px; }
  .payment-option {
    flex: 1;
    position: relative;
  }
  .payment-option input[type="radio"] {
    position: absolute;
    opacity: 0;
    width: 0;
    height: 0;
  }
  .payment-option label {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    padding: 18px 14px;
    border: 2px solid var(--gray-200);
    border-radius: 12px;
    cursor: pointer;
    transition: all .25s ease;
    background: var(--gray-50);
    font-size: .82rem !important;
    font-weight: 600 !important;
    color: var(--gray-600) !important;
  }
  .payment-option label:hover {
    border-color: var(--blue);
    background: #EFF6FF;
  }
  .payment-option input:checked + label {
    border-color: var(--blue);
    background: #EFF6FF;
    color: var(--blue) !important;
    box-shadow: 0 0 0 3px rgba(37,99,235,.10);
  }
  .payment-icon {
    font-size: 2rem;
    line-height: 1;
  }
  .payment-check {
    display: none;
    position: absolute;
    top: 8px;
    right: 10px;
    color: var(--blue);
    font-size: .9rem;
    font-weight: 800;
  }
  .payment-option input:checked ~ .payment-check {
    display: block;
  }

  /* Formation info card */
  .formation-info-card {
    background: linear-gradient(135deg, #EFF6FF, #F0F9FF);
    border: 1.5px solid #BFDBFE;
    border-radius: 12px;
    padding: 16px 20px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 14px;
  }
  .formation-info-icon {
    background: var(--blue);
    color: #fff;
    width: 44px;
    height: 44px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
    flex-shrink: 0;
  }
  .formation-info-text {
    display: flex;
    flex-direction: column;
    gap: 2px;
  }
  .formation-info-label {
    font-size: .72rem;
    font-weight: 700;
    color: var(--blue);
    text-transform: uppercase;
    letter-spacing: .08em;
  }
  .formation-info-title {
    font-size: .95rem;
    font-weight: 700;
    color: var(--navy);
    font-family: 'Nunito', sans-serif;
  }
  .formation-info-price {
    font-size: .82rem;
    color: var(--gray-500);
    font-weight: 500;
  }
</style>
</head>
<body style="background:rgba(0,0,0,0.5); display:flex; align-items:center; justify-content:center; min-height:100vh; margin:0; padding:20px;">

<div class="back-modal" style="width:100%; max-width:580px; position:relative; animation:slideUp .25s ease; box-shadow:0 10px 40px rgba(0,0,0,0.2);">
    <form action="" method="POST" id="formInscription" novalidate onsubmit="return validateInscription(event)">
    <div class="back-modal-header" style="padding:24px 28px 18px; border-bottom:1px solid var(--gray-100); display:flex; align-items:center; justify-content:space-between; position:sticky; top:0; background:#fff; z-index:1;">
      <div>
        <p style="font-size:.75rem; font-weight:700; letter-spacing:.1em; text-transform:uppercase; color:var(--blue); margin-bottom:4px;">Inscription</p>
        <h3 style="font-size:1.15rem; color:var(--navy); margin:0; font-family:'Nunito',sans-serif; font-weight:800;">S'inscrire à une formation</h3>
      </div>
      <a href="frontoffice.php" class="modal-close" style="text-decoration:none; background:var(--gray-100); width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center; color:var(--gray-500); font-size:1.1rem;">✕</a>
    </div>

    <div class="back-modal-body" style="padding:24px 28px; max-height:65vh; overflow-y:auto;">
      <?php if ($error != ""): ?>
        <div style="background:#FEE2E2; border:1px solid #EF4444; color:#B91C1C; padding:12px 16px; border-radius:8px; font-size:.85rem; font-weight:600; margin-bottom:18px;">✕ <?= $error ?></div>
      <?php endif; ?>

      <?php if ($success != ""): ?>
        <div style="background:#D1FAE5; border:1px solid #10B981; color:#065F46; padding:12px 16px; border-radius:8px; font-size:.85rem; font-weight:600; margin-bottom:18px;">✓ <?= $success ?></div>
      <?php endif; ?>

      <!-- Formation info card -->
      <?php if ($formation): ?>
        <div class="formation-info-card">
          <div class="formation-info-icon">🎓</div>
          <div class="formation-info-text">
            <span class="formation-info-label">Formation sélectionnée</span>
            <span class="formation-info-title"><?= htmlspecialchars($formation['titre']) ?></span>
            <span class="formation-info-price"><?= htmlspecialchars($formation['prix']) ?> € · <?= htmlspecialchars($formation['duree']) ?></span>
          </div>
        </div>
        <input type="hidden" name="id_formation" value="<?= $id_formation ?>">
      <?php else: ?>
        <!-- Dropdown to choose formation -->
        <div class="row-1">
          <div class="field-group">
            <label>Formation *</label>
            <select id="id_formation" name="id_formation">
              <option value="">-- Choisir une formation --</option>
              <?php foreach ($listeFormations as $f): ?>
                <option value="<?= $f['id'] ?>"><?= htmlspecialchars($f['titre']) ?> (<?= $f['prix'] ?> €)</option>
              <?php endforeach; ?>
            </select>
            <span class="error-msg" id="err-id_formation">Veuillez choisir une formation.</span>
          </div>
        </div>
      <?php endif; ?>

      <div class="row-2">
        <div class="field-group">
          <label>Nom *</label>
          <input type="text" id="nom" name="nom" placeholder="Ex: Dupont">
          <span class="error-msg" id="err-nom">Le nom est obligatoire et doit contenir uniquement des lettres.</span>
        </div>
        <div class="field-group">
          <label>Prénom *</label>
          <input type="text" id="prenom" name="prenom" placeholder="Ex: Jean">
          <span class="error-msg" id="err-prenom">Le prénom est obligatoire et doit contenir uniquement des lettres.</span>
        </div>
      </div>

      <div class="row-2">
        <div class="field-group">
          <label>Email *</label>
          <input type="text" id="email" name="email" placeholder="Ex: jean@email.com">
          <span class="error-msg" id="err-email">L'email doit contenir "@" et "." (ex: nom@domaine.com).</span>
        </div>
        <div class="field-group">
          <label>Numéro de téléphone *</label>
          <input type="text" id="telephone" name="telephone" placeholder="Ex: 12345678">
          <span class="error-msg" id="err-telephone">Le numéro doit contenir exactement 8 chiffres.</span>
        </div>
      </div>

      <div class="row-1">
        <div class="field-group">
          <label>Méthode de paiement *</label>
          <div class="payment-options">
            <div class="payment-option">
              <input type="radio" name="methode_paiement" id="paiement_d17" value="D17">
              <label for="paiement_d17">
                <span class="payment-icon">🏦</span>
                D17
              </label>
              <span class="payment-check">✓</span>
            </div>
            <div class="payment-option">
              <input type="radio" name="methode_paiement" id="paiement_visa" value="Visa Card">
              <label for="paiement_visa">
                <span class="payment-icon">💳</span>
                Visa Card
              </label>
              <span class="payment-check">✓</span>
            </div>
          </div>
          <span class="error-msg" id="err-paiement">Veuillez choisir une méthode de paiement.</span>
        </div>
      </div>
    </div>

    <div class="back-modal-footer" style="padding:16px 28px 24px; display:flex; gap:10px; justify-content:flex-end; border-top:1px solid var(--gray-100);">
      <a href="frontoffice.php" class="btn-cancel" style="text-decoration:none; padding:10px 22px; border:1.5px solid var(--gray-200); border-radius:var(--radius-sm); font-size:.88rem; font-weight:600; color:var(--gray-600); background:var(--white); display:flex; align-items:center;">Annuler</a>
      <button type="submit" class="btn-save" style="padding:10px 26px; background:var(--blue); color:#fff; border-radius:var(--radius-sm); font-size:.88rem; font-weight:700; border:none; cursor:pointer;">📝 S'inscrire</button>
    </div>
  </form>
</div>

<script>
function validateInscription(e) {
  let valid = true;

  // Regex : lettres uniquement (avec accents et espaces)
  var lettresRegex = /^[a-zA-ZÀ-ÿ\s'-]+$/;
  // Regex : exactement 8 chiffres
  var telRegex = /^[0-9]{8}$/;

  // --- Nom ---
  var nom = document.getElementById('nom');
  var errNom = document.getElementById('err-nom');
  if (nom.value.trim() === '' || !lettresRegex.test(nom.value.trim())) {
    nom.classList.add('field-error');
    errNom.style.display = 'block';
    valid = false;
  } else {
    nom.classList.remove('field-error');
    errNom.style.display = 'none';
  }

  // --- Prénom ---
  var prenom = document.getElementById('prenom');
  var errPrenom = document.getElementById('err-prenom');
  if (prenom.value.trim() === '' || !lettresRegex.test(prenom.value.trim())) {
    prenom.classList.add('field-error');
    errPrenom.style.display = 'block';
    valid = false;
  } else {
    prenom.classList.remove('field-error');
    errPrenom.style.display = 'none';
  }

  // --- Email : doit contenir "@" et "." ---
  var email = document.getElementById('email');
  var errEmail = document.getElementById('err-email');
  var emailVal = email.value.trim();
  if (emailVal === '' || emailVal.indexOf('@') === -1 || emailVal.indexOf('.') === -1) {
    email.classList.add('field-error');
    errEmail.style.display = 'block';
    valid = false;
  } else {
    email.classList.remove('field-error');
    errEmail.style.display = 'none';
  }

  // --- Téléphone : exactement 8 chiffres ---
  var telephone = document.getElementById('telephone');
  var errTel = document.getElementById('err-telephone');
  if (!telRegex.test(telephone.value.trim())) {
    telephone.classList.add('field-error');
    errTel.style.display = 'block';
    valid = false;
  } else {
    telephone.classList.remove('field-error');
    errTel.style.display = 'none';
  }

  // --- Formation (si dropdown visible) ---
  var formationSelect = document.getElementById('id_formation');
  if (formationSelect) {
    var errFormation = document.getElementById('err-id_formation');
    if (formationSelect.value === '') {
      formationSelect.classList.add('field-error');
      if (errFormation) errFormation.style.display = 'block';
      valid = false;
    } else {
      formationSelect.classList.remove('field-error');
      if (errFormation) errFormation.style.display = 'none';
    }
  }

  // --- Méthode de paiement ---
  var paiementChecked = document.querySelector('input[name="methode_paiement"]:checked');
  var errPaiement = document.getElementById('err-paiement');
  if (!paiementChecked) {
    errPaiement.style.display = 'block';
    document.querySelectorAll('.payment-option label').forEach(function(l) { l.style.borderColor = '#EF4444'; });
    valid = false;
  } else {
    errPaiement.style.display = 'none';
    document.querySelectorAll('.payment-option label').forEach(function(l) { l.style.borderColor = ''; });
  }

  if (!valid) {
    e.preventDefault();
  }
  return valid;
}

// Effacer les erreurs à la saisie
['nom','prenom','email','telephone'].forEach(function(id) {
  var el = document.getElementById(id);
  if (el) el.addEventListener('input', function() {
    this.classList.remove('field-error');
    var errEl = document.getElementById('err-' + id);
    if (errEl) errEl.style.display = 'none';
  });
});

// Effacer erreur paiement
document.querySelectorAll('input[name="methode_paiement"]').forEach(function(radio) {
  radio.addEventListener('change', function() {
    document.getElementById('err-paiement').style.display = 'none';
    document.querySelectorAll('.payment-option label').forEach(function(l) { l.style.borderColor = ''; });
  });
});

// Effacer erreur formation
var formationSelect = document.getElementById('id_formation');
if (formationSelect) {
  formationSelect.addEventListener('change', function() {
    this.classList.remove('field-error');
    var errEl = document.getElementById('err-id_formation');
    if (errEl) errEl.style.display = 'none';
  });
}
</script>

</body>
</html>
