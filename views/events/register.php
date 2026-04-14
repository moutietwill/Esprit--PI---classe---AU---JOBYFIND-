<?php /** @var Event $event */ ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription evenement</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f8fafc; color: #1f2937; margin: 0; padding: 32px; }
        .card { max-width: 720px; margin: 0 auto; background: #fff; border-radius: 16px; padding: 32px; box-shadow: 0 14px 30px rgba(15, 23, 42, 0.08); }
        .alert { background: #fee2e2; color: #991b1b; padding: 12px; border-radius: 8px; margin-bottom: 16px; }
        label { display: block; font-weight: 600; margin: 14px 0 6px; }
        input { width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; }
        button { margin-top: 18px; border: 0; background: #2563eb; color: #fff; padding: 12px 18px; border-radius: 8px; cursor: pointer; }
        a { color: #2563eb; text-decoration: none; }
    </style>
</head>
<body>
    <div class="card">
        <p><a href="/projetweb_avec_evenements/public/index.php/events/show/<?php echo urlencode((string) $event->getId()); ?>">Retour</a></p>
        <h1>Inscription a l'evenement</h1>
        <p><strong><?php echo htmlspecialchars($event->getTitre(), ENT_QUOTES, 'UTF-8'); ?></strong></p>

        <?php if (!empty($error)): ?>
            <div class="alert"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <form method="POST" action="/projetweb_avec_evenements/public/index.php/events/storeRegistration/<?php echo urlencode((string) $event->getId()); ?>">
            <label for="prenom">Prenom</label>
            <input id="prenom" name="prenom">

            <label for="nom">Nom</label>
            <input id="nom" name="nom">

            <label for="email">Email</label>
            <input id="email" name="email" type="text">

            <button type="submit">Valider l'inscription</button>
        </form>
    </div>
</body>
</html>
