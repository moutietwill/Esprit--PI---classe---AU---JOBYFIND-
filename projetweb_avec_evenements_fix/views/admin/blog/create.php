<?php
/**
 * admin/blog/create.php - Creer un nouveau post
 */
?>

<div class="container mt-5">
    <div class="row mb-4">
        <div class="col">
            <h1><i class="fas fa-plus"></i> Creer un Post</h1>
        </div>
        <div class="col-auto">
            <a href="<?php echo $this->baseUrl('/admin/blog'); ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
    </div>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($_GET['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="<?php echo $this->baseUrl('/admin/blog/store'); ?>" enctype="multipart/form-data">
                <?php if (!empty($currentUser)): ?>
                    <?php
                    $authorLabel = trim(($currentUser['first_name'] ?? '') . ' ' . ($currentUser['last_name'] ?? ''));
                    if ($authorLabel === '') {
                        $authorLabel = $currentUser['username'] ?: $currentUser['email'];
                    }
                    ?>
                    <div class="alert alert-info">
                        <strong>Auteur connecte :</strong>
                        <?php echo htmlspecialchars($authorLabel); ?>
                        <?php if (!empty($currentUser['email'])): ?>
                            (<?php echo htmlspecialchars($currentUser['email']); ?>)
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <div class="mb-3">
                    <label class="form-label">Titre</label>
                    <input type="text" class="form-control" name="titre" required placeholder="Entrez le titre du post">
                </div>

                <div class="mb-3">
                    <label class="form-label">Resume</label>
                    <textarea class="form-control" name="resume" rows="3" placeholder="Resume court du post"></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Contenu</label>
                    <textarea class="form-control" id="contenu" name="contenu" rows="10" required placeholder="Contenu du post..."></textarea>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Categorie</label>
                            <select class="form-select" name="categorie">
                                <option value="">Choisir une categorie</option>
                                <?php foreach (($categories ?? []) as $cat): ?>
                                    <option value="<?php echo htmlspecialchars($cat['nom']); ?>">
                                        <?php echo htmlspecialchars($cat['nom']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Statut</label>
                            <select class="form-select" name="statut">
                                <option value="brouillon">Brouillon</option>
                                <option value="publie">Publie</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Evenement lie</label>
                            <select class="form-select" name="event_id">
                                <option value="">Aucun evenement</option>
                                <?php foreach (($events ?? []) as $event): ?>
                                    <option value="<?php echo (int) $event['idEvenement']; ?>">
                                        <?php echo htmlspecialchars($event['titre'] . ' - ' . date('d/m/Y', strtotime($event['date']))); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6 d-flex align-items-end">
                        <div class="mb-3 w-100">
                            <div class="form-text">
                                Le post sera automatiquement rattache a l'utilisateur connecte, comme l'organisateur d'un evenement.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Image de couverture</label>
                    <input type="file" class="form-control" name="image" accept="image/*">
                    <small class="text-muted">JPG, PNG, GIF ou WebP - Max 5MB</small>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <a href="<?php echo $this->baseUrl('/admin/blog'); ?>" class="btn btn-secondary">Annuler</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Creer le post
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
