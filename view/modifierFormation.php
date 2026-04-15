<?php
include '../model/formation.php';
include '../controller/formationC.php';

$error = "";
$formationC = new formationC();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$formation = $formationC->getFormationById($id);

if (!$formation) {
    header('Location: backoffice.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (
        !empty($_POST["titre"]) &&
        !empty($_POST["prix"]) &&
        !empty($_POST["date"]) &&
        !empty($_POST["duree"]) &&
        !empty($_POST["description"]) &&
        !empty($_POST["categorie"])
    ) {
        $formationC->updateFormation(
            $id,
            $_POST['titre'],
            (float)$_POST['prix'],
            $_POST['date'],
            $_POST['duree'],
            $_POST['description'],
            $_POST['categorie']
        );
        header('Location: backoffice.php');
        exit();
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
<title>Jobyfind – Modifier la formation</title>
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
  .field-group input, .field-group select, .field-group textarea {
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
  .field-group textarea { resize: vertical; min-height: 90px; }
  .field-error { border-color: #EF4444 !important; background: #FFF5F5 !important; }
  .error-msg { color: #EF4444; font-size: .76rem; margin-top: 4px; font-weight: 600; display: none; }
  .row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px; }
  .row-1 { display: grid; grid-template-columns: 1fr; gap: 16px; margin-bottom: 16px; }
</style>
</head>
<body style="background:rgba(0,0,0,0.5); display:flex; align-items:center; justify-content:center; min-height:100vh; margin:0; padding:20px;">

<div class="back-modal" style="width:100%; max-width:580px; position:relative; animation:slideUp .25s ease; box-shadow:0 10px 40px rgba(0,0,0,0.2);">
  <form action="" method="POST" id="formModifier" novalidate onsubmit="return validateModifier(event)">
    <div class="back-modal-header" style="padding:24px 28px 18px; border-bottom:1px solid var(--gray-100); display:flex; align-items:center; justify-content:space-between; position:sticky; top:0; background:#fff; z-index:1;">
      <div>
        <p style="font-size:.75rem; font-weight:700; letter-spacing:.1em; text-transform:uppercase; color:var(--blue); margin-bottom:4px;">Modifier</p>
        <h3 style="font-size:1.15rem; color:var(--navy); margin:0; font-family:'Nunito',sans-serif; font-weight:800;">Mettre à jour la formation</h3>
      </div>
      <a href="backoffice.php" class="modal-close" style="text-decoration:none; background:var(--gray-100); width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center; color:var(--gray-500); font-size:1.1rem;">✕</a>
    </div>

    <div class="back-modal-body" style="padding:24px 28px;">
      <?php if ($error != ""): ?>
        <div style="background:#FEE2E2; border:1px solid #EF4444; color:#B91C1C; padding:12px 16px; border-radius:8px; font-size:.85rem; font-weight:600; margin-bottom:18px;">✕ <?= $error ?></div>
      <?php endif; ?>

      <div class="row-1">
        <div class="field-group">
          <label>Titre de la formation *</label>
          <input type="text" id="titre" name="titre" placeholder="Ex: Maîtriser le Marketing Digital" value="<?= htmlspecialchars($formation['titre']) ?>">
          <span class="error-msg" id="err-titre">Le titre est obligatoire.</span>
        </div>
      </div>

      <div class="row-2">
        <div class="field-group">
          <label>Catégorie *</label>
          <select id="categorie" name="categorie">
            <?php
            $cats = ['Marketing Digital' => '📉', 'Développement Web' => '💻', 'Finance & Gestion' => '💰', 'Communication' => '🎤', 'Design & UX' => '🎨', 'Autre' => '📂'];
            foreach ($cats as $cat => $icon):
              $sel = ($formation['categorie'] === $cat) ? 'selected' : '';
            ?>
              <option value="<?= $cat ?>" <?= $sel ?>><?= $icon ?> <?= $cat ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="field-group">
          <label>Coût (€) *</label>
          <input type="number" step="0.5" id="prix" name="prix" placeholder="Ex: 350" value="<?= htmlspecialchars($formation['prix']) ?>">
          <span class="error-msg" id="err-prix">Le prix est obligatoire (nombre positif).</span>
        </div>
      </div>

      <div class="row-2">
        <div class="field-group">
          <label>Durée *</label>
          <input type="text" id="duree" name="duree" placeholder="Ex: 3 mois / 20h" value="<?= htmlspecialchars($formation['duree']) ?>">
          <span class="error-msg" id="err-duree">La durée est obligatoire.</span>
        </div>
        <div class="field-group">
          <label>Date de début *</label>
          <input type="date" id="date" name="date" value="<?= htmlspecialchars($formation['date']) ?>">
          <span class="error-msg" id="err-date">La date de début est obligatoire.</span>
        </div>
      </div>

      <div class="row-1">
        <div class="field-group">
          <label>Description</label>
          <textarea id="description" name="description" placeholder="Description détaillée de la formation..."><?= htmlspecialchars($formation['description']) ?></textarea>
          <span class="error-msg" id="err-description">La description est obligatoire.</span>
        </div>
      </div>
    </div>

    <div class="back-modal-footer" style="padding:16px 28px 24px; display:flex; gap:10px; justify-content:flex-end; border-top:1px solid var(--gray-100);">
      <a href="backoffice.php" class="btn-cancel" style="text-decoration:none; padding:10px 22px; border:1.5px solid var(--gray-200); border-radius:var(--radius-sm); font-size:.88rem; font-weight:600; color:var(--gray-600); background:var(--white); display:flex; align-items:center;">Annuler</a>
      <button type="submit" class="btn-save" style="padding:10px 26px; background:var(--blue); color:#fff; border-radius:var(--radius-sm); font-size:.88rem; font-weight:700; border:none; cursor:pointer;">💾 Enregistrer</button>
    </div>
  </form>
</div>

<script>
function validateModifier(e) {
  let valid = true;

  const fields = [
    { id: 'titre',       errId: 'err-titre',       check: v => v.trim() !== '' },
    { id: 'prix',        errId: 'err-prix',        check: v => v !== '' && parseFloat(v) >= 0 },
    { id: 'duree',       errId: 'err-duree',       check: v => v.trim() !== '' },
    { id: 'date',        errId: 'err-date',        check: v => v !== '' },
    { id: 'description', errId: 'err-description', check: v => v.trim() !== '' },
  ];

  fields.forEach(f => {
    const el = document.getElementById(f.id);
    const errEl = document.getElementById(f.errId);
    const ok = f.check(el.value);
    if (!ok) {
      el.classList.add('field-error');
      if (errEl) errEl.style.display = 'block';
      valid = false;
    } else {
      el.classList.remove('field-error');
      if (errEl) errEl.style.display = 'none';
    }
  });

  if (!valid) e.preventDefault();
  return valid;
}

// Clear error on user input
['titre','prix','duree','date','description'].forEach(id => {
  const el = document.getElementById(id);
  if (el) el.addEventListener('input', function() {
    this.classList.remove('field-error');
    const errEl = document.getElementById('err-' + id);
    if (errEl) errEl.style.display = 'none';
  });
});
</script>

</body>
</html>
