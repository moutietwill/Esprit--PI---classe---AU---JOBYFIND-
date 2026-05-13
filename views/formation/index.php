<?php
    require_once(__DIR__ . '/../../config/session.php');
    startAppSession();
    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . $url('/login') . '?msg=' . urlencode('Veuillez vous connecter pour accéder aux formations.'));
        exit;
    }
    $pageTitle = 'Nos Formations';
    ob_start();
?>
<div class="hero" style="padding: 3rem 0;">
    <div class="container">
        <h1><i class="fas fa-graduation-cap"></i> Nos Formations</h1>
        <p>Développez vos compétences avec nos experts</p>
    </div>
</div>

<div class="container my-5">
    <div class="row">
        <?php if (!empty($formations)): ?>
            <?php foreach ($formations as $f): ?>
                <div class="col-md-4 mb-4">
                    <div class="card-modern">
                        <div class="card-image" style="background: linear-gradient(135deg, #2d79ff 0%, #0b1f4b 100%); font-size: 2rem;">
                            <i class="fas fa-book"></i>
                        </div>
                        <div class="card-body">
                            <div style="font-size: 0.85rem; color: #2d79ff; font-weight: 700; margin-bottom: 0.5rem;">
                                <?= htmlspecialchars($f->getCategorie()) ?>
                            </div>
                            <h5 class="card-title"><?= htmlspecialchars($f->getTitre()) ?></h5>
                            <p class="card-text"><?= htmlspecialchars(substr($f->getDescription(), 0, 100)) ?>...</p>
                            <div class="d-flex justify-content-between align-items: center; mt-3;">
                                <span class="fw-bold text-primary"><?= number_format($f->getPrix(), 2) ?> DT</span>
                                <span class="text-muted small"><i class="fas fa-clock"></i> <?= htmlspecialchars($f->getDuree()) ?></span>
                            </div>
                            <hr>
                            <a href="<?= $url('/formation/show/' . $f->getId()) ?>" class="card-link">
                                Voir détails <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <i class="fas fa-search fa-3x text-muted mb-3"></i>
                <p class="text-muted">Aucune formation disponible pour le moment.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
    $content = ob_get_clean();
    require __DIR__ . '/../layout.php';
?>
