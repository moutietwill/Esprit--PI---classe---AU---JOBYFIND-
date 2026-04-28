<?php
include '../model/formation.php';
include '../controller/formationC.php';

$error = "";
$formationC = new formationC();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (
        !empty($_POST["titre"]) &&
        !empty($_POST["prix"]) &&
        !empty($_POST["date"]) &&
        !empty($_POST["duree"]) &&
        !empty($_POST["description"]) &&
        !empty($_POST["categorie"])
    ) {
        $formation = new formation(
            $_POST['titre'],
            (float)$_POST['prix'],
            $_POST['date'],
            $_POST['duree'],
            $_POST['description'],
            $_POST['categorie']
        );
        $formationC->addFormation($formation);
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
<title>Jobyfind – Nouvelle formation</title>
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
  .field-group label { font-size: .82rem; font-weight: 600; color: var(--gray-700); text-transform: uppercase; letter-spacing: .04em; }
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

  /* ═══ AI GENERATE SECTION ═══ */
  .desc-wrapper { position: relative; }
  .desc-wrapper textarea { padding-bottom: 50px; }

  .desc-ai-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: 6px;
  }

  .char-count {
    font-size: .76rem;
    color: var(--gray-400);
    font-weight: 500;
  }

  .btn-ai {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 8px 18px;
    border-radius: var(--radius-sm);
    font-size: .84rem;
    font-weight: 700;
    font-family: 'DM Sans', sans-serif;
    cursor: pointer;
    border: 1.5px solid var(--gray-200);
    background: var(--white);
    color: var(--gray-700);
    transition: all .25s ease;
    position: relative;
    overflow: hidden;
  }

  .btn-ai::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, rgba(37,99,235,.06), rgba(124,58,237,.06));
    opacity: 0;
    transition: opacity .25s;
  }
  .btn-ai:hover::before { opacity: 1; }

  .btn-ai:hover {
    border-color: var(--blue);
    color: var(--blue);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(37,99,235,.15);
  }

  .btn-ai .ai-sparkle {
    font-size: 1.05rem;
    background: linear-gradient(135deg, #2563EB, #7C3AED);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
  }

  .btn-ai:disabled {
    opacity: .6;
    cursor: not-allowed;
    transform: none !important;
    box-shadow: none !important;
  }

  /* Loading spinner */
  .btn-ai .spinner {
    display: none;
    width: 16px;
    height: 16px;
    border: 2px solid var(--gray-200);
    border-top-color: var(--blue);
    border-radius: 50%;
    animation: spin .6s linear infinite;
  }
  .btn-ai.loading .spinner { display: inline-block; }
  .btn-ai.loading .ai-sparkle { display: none; }
  .btn-ai.loading .ai-label { color: var(--gray-400); }

  @keyframes spin { to { transform: rotate(360deg); } }

  /* Success / error feedback */
  .ai-feedback {
    font-size: .78rem;
    font-weight: 600;
    margin-top: 6px;
    display: flex;
    align-items: center;
    gap: 5px;
    opacity: 0;
    transition: opacity .3s;
  }
  .ai-feedback.visible { opacity: 1; }
  .ai-feedback.success { color: var(--green); }
  .ai-feedback.error   { color: var(--red); }

  /* Textarea AI glow when generated */
  .desc-generated {
    animation: aiGlow .5s ease;
  }
  @keyframes aiGlow {
    0%   { box-shadow: 0 0 0 0 rgba(37,99,235,.3); }
    50%  { box-shadow: 0 0 0 6px rgba(37,99,235,.12); }
    100% { box-shadow: 0 0 0 3px rgba(37,99,235,.09); }
  }

  /* Scroll arrow */
  .scroll-arrow {
    display: flex;
    justify-content: center;
    padding: 6px 0 2px;
    color: var(--gray-300);
    font-size: 1.3rem;
    animation: bounceDown 1.5s infinite;
  }
  @keyframes bounceDown {
    0%, 100% { transform: translateY(0); }
    50%      { transform: translateY(4px); }
  }

  /* Modal header gradient bar */
  .modal-header-bar {
    height: 4px;
    background: linear-gradient(90deg, var(--navy), var(--blue), #7C3AED, var(--orange));
    border-radius: var(--radius) var(--radius) 0 0;
  }
</style>
</head>
<body style="background:rgba(0,0,0,0.5); display:flex; align-items:center; justify-content:center; min-height:100vh; margin:0; padding:20px;">

<div class="back-modal" style="width:100%; max-width:580px; position:relative; animation:slideUp .25s ease; box-shadow:0 10px 40px rgba(0,0,0,0.2); border-radius:var(--radius); background:var(--white); overflow:hidden;">
    <!-- Gradient top bar -->
    <div class="modal-header-bar"></div>

    <form action="" method="POST" id="formAdd" novalidate onsubmit="return validateAjouter(event)">
    <div style="padding:24px 28px 18px; border-bottom:1px solid var(--gray-100); display:flex; align-items:center; justify-content:space-between; position:sticky; top:0; background:#fff; z-index:1;">
      <div>
        <p style="font-size:.75rem; font-weight:700; letter-spacing:.1em; text-transform:uppercase; color:var(--blue); margin-bottom:4px;">Nouvelle</p>
        <h3 style="font-size:1.15rem; color:var(--navy); margin:0; font-family:'Nunito',sans-serif; font-weight:800;">Nouvelle formation</h3>
      </div>
      <a href="backoffice.php" class="modal-close" style="text-decoration:none; background:var(--gray-100); width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center; color:var(--gray-500); font-size:1.1rem;">✕</a>
    </div>

    <div style="padding:24px 28px; max-height:60vh; overflow-y:auto;">
      <?php if ($error != ""): ?>
        <div style="background:#FEE2E2; border:1px solid #EF4444; color:#B91C1C; padding:12px 16px; border-radius:8px; font-size:.85rem; font-weight:600; margin-bottom:18px;">✕ <?= $error ?></div>
      <?php endif; ?>

      <!-- Titre -->
      <div class="row-1">
        <div class="field-group">
          <label>Titre de la formation *</label>
          <input type="text" id="titre" name="titre" placeholder="Ex: Maîtriser le Marketing Digital">
          <span class="error-msg" id="err-titre">Le titre est obligatoire.</span>
        </div>
      </div>

      <!-- Catégorie + Coût -->
      <div class="row-2">
        <div class="field-group">
          <label>Catégorie *</label>
          <select id="categorie" name="categorie">
            <option value="">-- Choisir --</option>
            <option value="Marketing Digital">📉 Marketing Digital</option>
            <option value="Développement Web">💻 Développement Web</option>
            <option value="Finance & Gestion">💰 Finance & Gestion</option>
            <option value="Communication">🎤 Communication</option>
            <option value="Design & UX">🎨 Design & UX</option>
            <option value="Autre">📂 Autre</option>
          </select>
        </div>
        <div class="field-group">
          <label>Coût (€) *</label>
          <input type="number" step="0.5" id="prix" name="prix" placeholder="Ex: 350" min="0">
          <span class="error-msg" id="err-prix">Le prix est obligatoire (nombre positif).</span>
        </div>
      </div>

      <!-- Durée + Date -->
      <div class="row-2">
        <div class="field-group">
          <label>Durée *</label>
          <input type="text" id="duree" name="duree" placeholder="Ex: 3 mois / 20h">
          <span class="error-msg" id="err-duree">La durée est obligatoire.</span>
        </div>
        <div class="field-group">
          <label>Date de début *</label>
          <input type="date" id="date" name="date">
          <span class="error-msg" id="err-date">La date de début est obligatoire.</span>
        </div>
      </div>

      <!-- Description + AI Button -->
      <div class="row-1">
        <div class="field-group">
          <label>Description</label>
          <div class="desc-wrapper">
            <textarea id="description" name="description" placeholder="Description détaillée de la formation..."></textarea>
          </div>
          <div class="desc-ai-bar">
            <span class="char-count" id="charCount">0 car.</span>
            <button type="button" class="btn-ai" id="btnAI" onclick="generateDescription()">
              <span class="spinner"></span>
              <span class="ai-sparkle">✦</span>
              <span class="ai-label">Générer avec l'IA</span>
            </button>
          </div>
          <div class="ai-feedback" id="aiFeedback"></div>
          <span class="error-msg" id="err-description">La description est obligatoire.</span>
        </div>
      </div>
    </div>

    <!-- Scroll indicator -->
    <div class="scroll-arrow">↓</div>

    <div style="padding:16px 28px 24px; display:flex; gap:10px; justify-content:flex-end; border-top:1px solid var(--gray-100);">
      <a href="backoffice.php" class="btn-cancel" style="text-decoration:none; padding:10px 22px; border:1.5px solid var(--gray-200); border-radius:var(--radius-sm); font-size:.88rem; font-weight:600; color:var(--gray-600); background:var(--white); display:flex; align-items:center;">Annuler</a>
      <button type="submit" class="btn-save" style="padding:10px 26px; background:var(--blue); color:#fff; border-radius:var(--radius-sm); font-size:.88rem; font-weight:700; border:none; cursor:pointer;">💾 Enregistrer</button>
    </div>
  </form>
</div>

<script>
// ── Character counter ────────────────────────────────────────
const descEl    = document.getElementById('description');
const countEl   = document.getElementById('charCount');
const btnAI     = document.getElementById('btnAI');
const feedback  = document.getElementById('aiFeedback');
let hasGenerated = false;

descEl.addEventListener('input', () => {
  countEl.textContent = descEl.value.length + ' car.';
});

// ── AI Description Generation ────────────────────────────────
async function generateDescription() {
  const titre     = document.getElementById('titre').value.trim();
  const categorie = document.getElementById('categorie').value;
  const duree     = document.getElementById('duree').value.trim();
  const cout      = document.getElementById('prix').value;

  // Validate required fields
  if (!titre || !categorie) {
    showFeedback('error', '✕ Remplissez le titre et la catégorie d\'abord.');
    // highlight missing fields
    if (!titre) {
      document.getElementById('titre').classList.add('field-error');
      document.getElementById('err-titre').style.display = 'block';
    }
    if (!categorie) {
      document.getElementById('categorie').classList.add('field-error');
    }
    return;
  }

  // Set loading state
  btnAI.classList.add('loading');
  btnAI.disabled = true;
  hideFeedback();

  try {
    const resp = await fetch('../controller/api_gemini.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ titre, categorie, duree, cout })
    });

    const data = await resp.json();

    if (!resp.ok || data.error) {
      throw new Error(data.error || 'Erreur inconnue');
    }

    // Success — fill the textarea
    descEl.value = data.description;
    countEl.textContent = data.description.length + ' car.';
    descEl.classList.add('desc-generated');
    setTimeout(() => descEl.classList.remove('desc-generated'), 600);

    // Clear any error state on description
    descEl.classList.remove('field-error');
    const errDesc = document.getElementById('err-description');
    if (errDesc) errDesc.style.display = 'none';

    showFeedback('success', '✓ Description générée avec succès !');

    // Switch button text to "Régénérer"
    hasGenerated = true;
    btnAI.querySelector('.ai-label').textContent = 'Régénérer';

  } catch (err) {
    showFeedback('error', '✕ Erreur lors de la génération. Réessayez.');
    console.error('Gemini API error:', err);
  } finally {
    btnAI.classList.remove('loading');
    btnAI.disabled = false;
  }
}

function showFeedback(type, msg) {
  feedback.className = 'ai-feedback visible ' + type;
  feedback.textContent = msg;
}
function hideFeedback() {
  feedback.className = 'ai-feedback';
  feedback.textContent = '';
}

// ── Form Validation ──────────────────────────────────────────
function validateAjouter(e) {
  let valid = true;

  const fields = [
    { id: 'titre',       errId: 'err-titre',       check: v => v.trim() !== '' },
    { id: 'categorie',   errId: null,              check: v => v !== '' },
    { id: 'prix',        errId: 'err-prix',        check: v => v !== '' && parseFloat(v) >= 0 },
    { id: 'duree',       errId: 'err-duree',       check: v => v.trim() !== '' },
    { id: 'date',        errId: 'err-date',        check: v => v !== '' },
    { id: 'description', errId: 'err-description', check: v => v.trim() !== '' },
  ];

  fields.forEach(f => {
    if (!f.check) return;
    const el = document.getElementById(f.id);
    const errEl = f.errId ? document.getElementById(f.errId) : null;
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

  if (!valid) {
    e.preventDefault();
  }
  return valid;
}

// Clear error on input
['titre','prix','duree','date','description'].forEach(id => {
  const el = document.getElementById(id);
  if (el) el.addEventListener('input', function() {
    this.classList.remove('field-error');
    const errEl = document.getElementById('err-' + id);
    if (errEl) errEl.style.display = 'none';
  });
});

// Also clear categorie error on change
document.getElementById('categorie').addEventListener('change', function() {
  this.classList.remove('field-error');
});
</script>

</body>
</html>
