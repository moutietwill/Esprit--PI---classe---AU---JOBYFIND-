<?php
    $pageTitle = htmlspecialchars($formation->getTitre());
    ob_start();
?>
<div class="container my-5">
    <nav aria-label="breadcrumb" class="breadcrumb-custom">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= $url('/') ?>">Accueil</a></li>
            <li class="breadcrumb-item"><a href="<?= $url('/formation') ?>">Formations</a></li>
            <li class="breadcrumb-item active"><?= htmlspecialchars($formation->getTitre()) ?></li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-lg-8">
            <div class="card-modern p-4 mb-4">
                <h1 class="mb-3 text-navy fw-bold"><?= htmlspecialchars($formation->getTitre()) ?></h1>
                <div class="d-flex gap-3 mb-4 flex-wrap">
                    <span class="badge bg-primary px-3 py-2 fs-6"><?= htmlspecialchars($formation->getCategorie()) ?></span>
                    <span class="text-muted"><i class="fas fa-calendar-alt text-primary me-2"></i>Début : <?= date('d/m/Y', strtotime($formation->getDate())) ?></span>
                    <span class="text-muted"><i class="fas fa-clock text-primary me-2"></i>Durée : <?= htmlspecialchars($formation->getDuree()) ?></span>
                </div>
                
                <h4 class="fw-bold mb-3">Description du cours</h4>
                <div class="text-muted" style="line-height: 1.8; font-size: 1.1rem;">
                    <?= nl2br(htmlspecialchars($formation->getDescription())) ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card-modern p-4 sticky-top" style="top: 20px;">
                <h3 class="fw-bold mb-4">Détails de l'inscription</h3>
                <div class="d-flex justify-content-between mb-3 fs-5">
                    <span>Prix total</span>
                    <span class="fw-bold text-primary"><?= number_format($formation->getPrix(), 2) ?> DT</span>
                </div>
                <hr>
                <div class="mb-4">
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-check-circle text-success me-2"></i>
                        <span>Accès à vie</span>
                    </div>
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-check-circle text-success me-2"></i>
                        <span>Certificat de réussite</span>
                    </div>
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-check-circle text-success me-2"></i>
                        <span>Support 24/7</span>
                    </div>
                </div>
                <button class="btn btn-primary w-100 py-3 fw-bold shadow-sm" onclick="alert('Module d\'inscription bientôt disponible !')">
                    S'inscrire maintenant
                </button>
                <p class="text-center text-muted small mt-3">Garantie satisfait ou remboursé sous 30 jours</p>
            </div>
        </div>
    </div>
</div>

<style>
    .text-navy { color: #0b1f4b; }
</style>

<?php
    $content = ob_get_clean();
    require __DIR__ . '/../layout.php';
?>
