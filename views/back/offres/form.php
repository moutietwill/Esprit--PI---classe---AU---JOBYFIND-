<?php include __DIR__ . '/../layout/header.php'; ?>
<?php 
    $isEdit = false;
    $actionUrl = "index.php?action=add_offre";
    if (isset($offreData) && !empty($offreData)) {
        $isEdit = true;
        $actionUrl = "index.php?action=edit_offre&id=" . $offreData['id_offre'];
    }
?>

<div class="form-container" style="max-width: 800px;">
    <p class="table-title" style="margin-bottom: 20px;">
        <?= $isEdit ? 'Modifier l\'offre' : 'Nouvelle offre' ?>
    </p>

    <!-- id="form-offre" is targeted by pure JS validation. NO HTML5 attributes. -->
    <form id="form-offre" method="POST" action="<?= $actionUrl ?>" novalidate>
        
        <div class="form-group">
            <label class="form-label">Titre de l'offre</label>
            <input type="text" id="titre" name="titre" class="form-input" value="<?= $isEdit ? htmlspecialchars($offreData['titre']) : '' ?>">
            <span class="form-error" id="error-titre">Le titre est requis.</span>
        </div>

        <div class="form-group">
            <label class="form-label">Description compléte</label>
            <textarea id="description_offre" name="description" class="form-input" rows="5"><?= $isEdit ? htmlspecialchars($offreData['description']) : '' ?></textarea>
            <span class="form-error" id="error-description_offre">La description est requise (min 15 caractères).</span>
        </div>

        <div style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px;">
            <div class="form-group">
                <label class="form-label">Date de publication</label>
                <input type="text" id="datePublication" name="datePublication" class="form-input" placeholder="YYYY-MM-DD" value="<?= $isEdit ? htmlspecialchars($offreData['datePublication']) : date('Y-m-d') ?>">
                <span class="form-error" id="error-datePublication">Date invalide (Format: YYYY-MM-DD).</span>
            </div>

            <div class="form-group">
                <label class="form-label">Statut</label>
                <select id="statut" name="statut" class="form-input">
                    <option value="Actif" <?= ($isEdit && $offreData['statut'] == 'Actif') ? 'selected' : '' ?>>Actif</option>
                    <option value="Inactif" <?= ($isEdit && $offreData['statut'] == 'Inactif') ? 'selected' : '' ?>>Inactif</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Type (Contrat)</label>
                <select id="type" name="type" class="form-input">
                    <option value="">-- Choisir --</option>
                    <option value="CDI" <?= ($isEdit && $offreData['type'] == 'CDI') ? 'selected' : '' ?>>CDI</option>
                    <option value="CDD" <?= ($isEdit && $offreData['type'] == 'CDD') ? 'selected' : '' ?>>CDD</option>
                    <option value="Stage" <?= ($isEdit && $offreData['type'] == 'Stage') ? 'selected' : '' ?>>Stage</option>
                    <option value="Freelance" <?= ($isEdit && $offreData['type'] == 'Freelance') ? 'selected' : '' ?>>Freelance</option>
                </select>
                <span class="form-error" id="error-type">Veuillez choisir un type.</span>
            </div>
        </div>

        <div style="margin-top: 20px; display:flex; gap:10px;">
            <button type="submit" class="btn-primary"><i class="fa fa-save"></i> Enregistrer</button>
            <a href="index.php?action=list_offres" class="btn-outline">Annuler</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
