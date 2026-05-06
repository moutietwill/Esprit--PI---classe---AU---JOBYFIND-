<?php include __DIR__ . '/../layout/header.php'; ?>
<?php 
    $isEdit = false;
    $isFront = (isset($_GET['front']) && $_GET['front'] == '1') || (isset($_POST['is_front']) && $_POST['is_front'] == '1');
    $actionUrl = "index.php?action=add_candidature&id_offre=" . $id_offre;
    if ($isFront) $actionUrl .= '&front=1';
    if (isset($candidatureData) && !empty($candidatureData) && isset($candidatureData['id_candidature'])) {
        $isEdit = true;
        $actionUrl = "index.php?action=edit_candidature&id=" . $candidatureData['id_candidature'];
        $id_offre = $candidatureData['id_offre'];
    }
?>

<div class="form-container" style="max-width: 800px;">
    <p class="table-title" style="margin-bottom: 20px;">
        <?= $isEdit ? 'Modifier la candidature' : 'Nouvelle candidature' . ($offre ? ' à: ' . htmlspecialchars($offre['titre']) : '') ?>
    </p>

    <!-- Affichage des erreurs globales -->
    <?php if(!empty($errors) && isset($errors['general'])): ?>
        <div style="background: #fee2e2; color: #991b1b; padding: 12px; border-radius: 7px; margin-bottom: 20px; border-left: 4px solid #ef4444;">
            <i class="fa fa-exclamation-circle"></i> <?= htmlspecialchars($errors['general']) ?>
        </div>
    <?php endif; ?>

    <form id="form-candidature" method="POST" action="<?= $actionUrl ?>" enctype="multipart/form-data" novalidate>
        <?php if ($isFront): ?>
            <input type="hidden" name="is_front" value="1">
        <?php endif; ?>
        
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 15px;">
            <div class="form-group">
                <label class="form-label">Nom du candidat</label>
                <input type="text" id="nom_candidat" name="nom_candidat" class="form-input" value="<?= isset($candidatureData) ? htmlspecialchars($candidatureData['nom_candidat'] ?? '') : '' ?>" maxlength="255">
                <?php if(isset($errors['nom_candidat'])): ?>
                    <span class="form-error" style="display: block;"><?= htmlspecialchars($errors['nom_candidat']) ?></span>
                <?php else: ?>
                    <span class="form-error" id="error-nom_candidat"></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label class="form-label">Prénom du candidat</label>
                <input type="text" id="prenom_candidat" name="prenom_candidat" class="form-input" value="<?= isset($candidatureData) ? htmlspecialchars($candidatureData['prenom_candidat'] ?? '') : '' ?>" maxlength="255">
                <?php if(isset($errors['prenom_candidat'])): ?>
                    <span class="form-error" style="display: block;"><?= htmlspecialchars($errors['prenom_candidat']) ?></span>
                <?php else: ?>
                    <span class="form-error" id="error-prenom_candidat"></span>
                <?php endif; ?>
            </div>
        </div>

        <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 15px;">
            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="text" id="email_candidat" name="email_candidat" class="form-input" value="<?= isset($candidatureData) ? htmlspecialchars($candidatureData['email_candidat'] ?? '') : '' ?>" maxlength="255">
                <?php if(isset($errors['email_candidat'])): ?>
                    <span class="form-error" style="display: block;"><?= htmlspecialchars($errors['email_candidat']) ?></span>
                <?php else: ?>
                    <span class="form-error" id="error-email_candidat"></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label class="form-label">Téléphone</label>
                <input type="text" id="telephone" name="telephone" class="form-input" value="<?= isset($candidatureData) ? htmlspecialchars($candidatureData['telephone'] ?? '') : '' ?>" maxlength="20">
                <?php if(isset($errors['telephone'])): ?>
                    <span class="form-error" style="display: block;"><?= htmlspecialchars($errors['telephone']) ?></span>
                <?php else: ?>
                    <span class="form-error" id="error-telephone"></span>
                <?php endif; ?>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Offre ciblée</label>
            <select name="id_offre" id="id_offre" class="form-input" <?= $isEdit ? 'disabled' : '' ?>>
                <option value="">-- Choisir une offre --</option>
                <?php foreach($offres as $o): ?>
                    <option value="<?= $o['id_offre'] ?>" <?= (isset($id_offre) && $o['id_offre'] == $id_offre) ? 'selected' : '' ?>><?= htmlspecialchars($o['titre']) ?></option>
                <?php endforeach; ?>
            </select>
            <span class="form-error" id="error-id_offre">Veuillez sélectionner une offre.</span>
            <?php if(empty($offres)): ?>
                <span class="form-error" style="display:block;">Aucune offre n'est disponible. <a href="index.php?action=add_offre">Créez-en une d'abord</a>.</span>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label class="form-label">Lettre de motivation</label>
            <textarea id="lettre_motivation" name="lettre_motivation" class="form-input" rows="5" maxlength="5000"><?= isset($candidatureData) ? htmlspecialchars($candidatureData['lettre_motivation'] ?? '') : '' ?></textarea>
            <?php if(isset($errors['lettre_motivation'])): ?>
                <span class="form-error" style="display: block;"><?= htmlspecialchars($errors['lettre_motivation']) ?></span>
            <?php else: ?>
                <span class="form-error" id="error-lettre_motivation"></span>
            <?php endif; ?>
            <small style="color: var(--muted);">Minimum 20 caractères, maximum 5000.</small>
        </div>

        <div class="form-group">
            <label class="form-label">Fichier CV (PDF, DOC, DOCX - Max 5 MB)</label>
            <input type="file" id="cv_fichier" name="cv_fichier" class="form-input">
            <?php if($isEdit && isset($candidatureData['cv_fichier']) && $candidatureData['cv_fichier']): ?>
                <small style="color: var(--muted);">Fichier actuel: <strong><?= htmlspecialchars($candidatureData['cv_fichier']) ?></strong></small>
                <br>
                <small style="color: var(--text-muted);">Laissez vide si vous ne souhaitez pas changer le fichier.</small>
            <?php endif; ?>
            <?php if(isset($errors['cv_fichier'])): ?>
                <span class="form-error" style="display: block;"><?= htmlspecialchars($errors['cv_fichier']) ?></span>
            <?php else: ?>
                <span class="form-error" id="error-cv_fichier"></span>
            <?php endif; ?>
        </div>

        <?php if($isEdit): ?>
        <div class="form-group">
            <label class="form-label">Statut</label>
            <select id="statut" name="statut" class="form-input">
                <option value="">-- Sélectionner un statut --</option>
                <option value="En attente" <?= (isset($candidatureData['statut']) && $candidatureData['statut'] == 'En attente') ? 'selected' : '' ?>>En attente</option>
                <option value="Acceptée" <?= (isset($candidatureData['statut']) && $candidatureData['statut'] == 'Acceptée') ? 'selected' : '' ?>>Acceptée</option>
                <option value="Rejetée" <?= (isset($candidatureData['statut']) && $candidatureData['statut'] == 'Rejetée') ? 'selected' : '' ?>>Rejetée</option>
            </select>
            <?php if(isset($errors['statut'])): ?>
                <span class="form-error" style="display: block;"><?= htmlspecialchars($errors['statut']) ?></span>
            <?php else: ?>
                <span class="form-error" id="error-statut"></span>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <div style="margin-top: 20px; display:flex; gap:10px;">
            <button type="submit" class="btn-primary"><i class="fa fa-save"></i> Enregistrer</button>
            <a href="index.php?action=list_candidatures_offre&id_offre=<?= $id_offre ?>" class="btn-outline">Annuler</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
