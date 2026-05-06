<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Postuler — Jobyfind</title>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'DM Sans', sans-serif; }
    body { background-color: #f8fafc; color: #1f2937; display:flex; justify-content:center; padding-top: 50px; }
    .card { background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); width: 500px; max-width: 90%; }
    .title { font-size: 22px; font-weight:700; margin-bottom: 20px; text-align:center; color:#153c8f; }
    .form-group { margin-bottom: 15px; }
    .form-label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; }
    .form-input { width: 100%; padding: 10px 12px; border: 1.5px solid #e2e8f0; border-radius: 7px; font-size: 14px; outline: none; }
    .form-error { color: #ef4444; font-size: 11px; margin-top: 4px; display: none; }
    .btn { width: 100%; padding: 12px; background: #2d79ff; color: white; border: none; border-radius: 7px; font-weight: 600; cursor: pointer; margin-top: 10px; font-size:15px; }
    .btn:hover { background: #1a5ccc; }
    .back { display:block; text-align:center; margin-top:15px; text-decoration:none; color: #6b7280; font-size:13px; }
  </style>
</head>
<body>

  <div class="card">
    <p class="title">Déposer une candidature</p>

    <!-- novalidate to bypass HTML5 validation according to rules -->
    <form id="form-candidature-front" method="POST" action="index.php?action=add_candidature" novalidate>
        <input type="hidden" name="is_front" value="1">
        
        <!-- id_offre dropdown restricted to the offered ID or let them choose -->
        <div class="form-group">
            <label class="form-label">Offre choisie</label>
            <select id="front_offre" name="id_offre" class="form-input">
                <option value="">-- Sélectionner l'offre --</option>
                <?php foreach($offres as $o): ?>
                    <option value="<?= $o['id_offre'] ?>" <?= (isset($id_offre) && $id_offre == $o['id_offre']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($o['titre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <span class="form-error" id="error-front_offre">Veuillez sélectionner l'offre.</span>
        </div>

        <div style="display:flex; gap:10px;">
            <div class="form-group" style="flex:1;">
                <label class="form-label">Prénom</label>
                <input type="text" id="front_prenom" name="prenom" class="form-input">
                <span class="form-error" id="error-front_prenom">Prénom requis.</span>
            </div>
            <div class="form-group" style="flex:1;">
                <label class="form-label">Nom</label>
                <input type="text" id="front_nom" name="nom" class="form-input">
                <span class="form-error" id="error-front_nom">Nom requis.</span>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Adresse e-mail</label>
            <input type="text" id="front_email" name="email" class="form-input">
            <span class="form-error" id="error-front_email">E-mail invalide.</span>
        </div>

        <div class="form-group">
            <label class="form-label">Lettre de motivation</label>
            <textarea id="front_lm" name="lettre_motivation" class="form-input" rows="4"></textarea>
            <span class="form-error" id="error-front_lm">Lettre requise (min 20 caractères).</span>
        </div>

        <button type="submit" class="btn">Envoyer ma candidature</button>
        <a href="index.php?action=front_offres" class="back">Retour aux offres</a>
    </form>
  </div>

  <script src="assets/js/validation_candidature_front.js"></script>
</body>
</html>
