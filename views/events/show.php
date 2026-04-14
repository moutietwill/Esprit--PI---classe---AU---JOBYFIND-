<?php /** @var Event $event */ ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($event->getTitre(), ENT_QUOTES, 'UTF-8'); ?></title>
    <style>
        body { font-family: Arial, sans-serif; background: #f8fafc; color: #1f2937; margin: 0; padding: 32px; }
        .card { max-width: 760px; margin: 0 auto; background: #fff; border-radius: 16px; padding: 32px; box-shadow: 0 14px 30px rgba(15, 23, 42, 0.08); }
        .meta { color: #64748b; margin-bottom: 18px; }
        .actions { margin-top: 24px; display: flex; gap: 12px; }
        a { text-decoration: none; color: #2563eb; font-weight: 600; }
        .btn { background: #2563eb; color: #fff; padding: 10px 16px; border-radius: 8px; display: inline-block; }
    </style>
</head>
<body>
    <div class="card">
        <p><a href="/projetweb_avec_evenements/public/index.php/events">Retour</a></p>
        <h1><?php echo htmlspecialchars($event->getTitre(), ENT_QUOTES, 'UTF-8'); ?></h1>
        <p class="meta">Date: <?php echo htmlspecialchars($event->getDate(), ENT_QUOTES, 'UTF-8'); ?> | Lieu: <?php echo htmlspecialchars($event->getLieu(), ENT_QUOTES, 'UTF-8'); ?></p>
        <p><?php echo nl2br(htmlspecialchars($event->getDescription(), ENT_QUOTES, 'UTF-8')); ?></p>
        <div class="actions">
            <a class="btn" href="/projetweb_avec_evenements/public/index.php/events/register/<?php echo urlencode((string) $event->getId()); ?>">S'inscrire</a>
        </div>
    </div>
</body>
</html>
