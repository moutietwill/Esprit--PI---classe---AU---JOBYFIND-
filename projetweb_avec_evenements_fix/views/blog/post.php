<?php
$comments = $comments ?? [];
$relatedPosts = $relatedPosts ?? [];
$currentUser = $currentUser ?? null;
$authorName = trim((string) ($post['auteur_nom'] ?: $post['auteur_username'] ?: $post['auteur_email'] ?: 'Auteur inconnu'));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($post['titre']); ?> - Blog</title>
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/css/all.min.css">
    <style>
        body { background: #f5f7fb; color: #20304a; }
        .post-shell {
            max-width: 980px;
            margin: 40px auto;
            background: #fff;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(11, 31, 75, 0.08);
        }
        .post-top {
            background: linear-gradient(135deg, #0b1f4b 0%, #2d79ff 100%);
            color: #fff;
            padding: 42px;
        }
        .cover { max-height: 420px; width: 100%; object-fit: cover; }
        .post-body { padding: 38px; }
        .meta-chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 7px 12px;
            border-radius: 999px;
            background: rgba(255,255,255,0.14);
            margin: 6px 10px 0 0;
        }
        .linked-event {
            border: 1px solid #e6edf6;
            background: #f8fbff;
            border-radius: 18px;
            padding: 18px 20px;
            margin: 28px 0;
        }
        .comment {
            background: #f8fafc;
            border-left: 4px solid #2d79ff;
            border-radius: 12px;
            padding: 16px 18px;
            margin-bottom: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <article class="post-shell">
            <header class="post-top">
                <a href="<?php echo $baseUrl; ?>/blog" class="btn btn-light btn-sm mb-4">
                    <i class="fas fa-arrow-left"></i> Retour au blog
                </a>
                <div class="mb-3">
                    <span class="meta-chip"><i class="fas fa-folder-open"></i> <?php echo htmlspecialchars($post['categorie'] ?: 'General'); ?></span>
                    <span class="meta-chip"><i class="fas fa-user"></i> <?php echo htmlspecialchars($authorName); ?></span>
                    <span class="meta-chip"><i class="fas fa-eye"></i> <?php echo (int) $post['vues']; ?> vues</span>
                </div>
                <h1 class="display-6 fw-bold mb-3"><?php echo htmlspecialchars($post['titre']); ?></h1>
                <div class="opacity-75">
                    <i class="fas fa-calendar-alt"></i>
                    <?php echo date('d/m/Y H:i', strtotime($post['date_publication'] ?: $post['date_creation'])); ?>
                </div>
            </header>

            <?php if (!empty($post['image_couverture'])): ?>
                <img class="cover" src="<?php echo $baseUrl . '/uploads/blog/' . htmlspecialchars($post['image_couverture']); ?>" alt="<?php echo htmlspecialchars($post['titre']); ?>">
            <?php endif; ?>

            <div class="post-body">
                <?php if (!empty($post['event_titre'])): ?>
                    <section class="linked-event">
                        <div class="small text-uppercase fw-semibold text-primary mb-2">Evenement associe</div>
                        <h2 class="h4 mb-2"><?php echo htmlspecialchars($post['event_titre']); ?></h2>
                        <p class="mb-3 text-muted">
                            <i class="fas fa-calendar-day"></i> <?php echo !empty($post['event_date']) ? date('d/m/Y', strtotime($post['event_date'])) : 'Date non definie'; ?>
                            <span class="mx-2">•</span>
                            <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($post['event_lieu'] ?: 'Lieu non defini'); ?>
                        </p>
                        <a class="btn btn-outline-primary btn-sm" href="<?php echo $baseUrl . '/events/show/' . (int) $post['event_id']; ?>">Voir l'evenement</a>
                    </section>
                <?php endif; ?>

                <div class="post-content">
                    <?php echo $post['contenu']; ?>
                </div>

                <section class="mt-5 pt-4 border-top">
                    <h2 class="h4 mb-3">Commentaires</h2>

                    <?php if (isset($_GET['error'])): ?>
                        <div class="alert alert-danger">Le commentaire n'a pas pu etre ajoute.</div>
                    <?php elseif (isset($_GET['success'])): ?>
                        <div class="alert alert-success">Commentaire ajoute avec succes.</div>
                    <?php endif; ?>

                    <?php if (!empty($comments)): ?>
                        <?php foreach ($comments as $comment): ?>
                            <div class="comment">
                                <div class="fw-semibold"><?php echo htmlspecialchars(trim((string) ($comment['auteur_nom'] ?: $comment['auteur_username'] ?: $comment['nom']))); ?></div>
                                <div class="text-muted small mb-2"><?php echo date('d/m/Y H:i', strtotime($comment['date_creation'])); ?></div>
                                <div><?php echo nl2br(htmlspecialchars($comment['contenu'])); ?></div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted">Aucun commentaire pour le moment.</p>
                    <?php endif; ?>

                    <form method="POST" action="<?php echo $baseUrl . '/blog/comment/' . (int) $post['id']; ?>" class="mt-4">
                        <div class="row g-3">
                            <?php if ($currentUser): ?>
                                <?php
                                $commentAuthor = trim(($currentUser['first_name'] ?? '') . ' ' . ($currentUser['last_name'] ?? ''));
                                if ($commentAuthor === '') {
                                    $commentAuthor = $currentUser['username'] ?: $currentUser['email'];
                                }
                                ?>
                                <div class="col-12">
                                    <div class="alert alert-light border">
                                        Commentaire publie en tant que <strong><?php echo htmlspecialchars($commentAuthor); ?></strong>
                                        <?php if (!empty($currentUser['email'])): ?>
                                            (<?php echo htmlspecialchars($currentUser['email']); ?>)
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="col-md-6">
                                    <label class="form-label">Nom</label>
                                    <input type="text" class="form-control" name="nom" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" name="email" required>
                                </div>
                            <?php endif; ?>
                            <div class="col-12">
                                <label class="form-label">Commentaire</label>
                                <textarea class="form-control" name="contenu" rows="4" required></textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">Publier le commentaire</button>
                            </div>
                        </div>
                    </form>
                </section>

                <?php if (!empty($relatedPosts)): ?>
                    <section class="mt-5 pt-4 border-top">
                        <h2 class="h4 mb-3">Articles lies</h2>
                        <div class="d-flex flex-column gap-2">
                            <?php foreach ($relatedPosts as $relatedPost): ?>
                                <a href="<?php echo $baseUrl . '/blog/post/' . (int) $relatedPost['id']; ?>" class="text-decoration-none">
                                    <i class="fas fa-angle-right"></i>
                                    <?php echo htmlspecialchars($relatedPost['titre']); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif; ?>
            </div>
        </article>
    </div>
</body>
</html>
