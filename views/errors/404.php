<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page introuvable</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f7fb; color: #1f2937; display: grid; place-items: center; min-height: 100vh; margin: 0; }
        .card { background: #fff; padding: 32px; border-radius: 14px; box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08); max-width: 520px; text-align: center; }
        h1 { margin: 0 0 12px; font-size: 28px; }
        p { margin: 0 0 18px; line-height: 1.5; }
        a { color: #2563eb; text-decoration: none; font-weight: 600; }
    </style>
</head>
<body>
    <div class="card">
        <h1>404</h1>
        <p>La page demandee est introuvable.</p>
        <a href="<?php echo htmlspecialchars($url('/events'), ENT_QUOTES, 'UTF-8'); ?>">Retour aux evenements</a>
    </div>
</body>
</html>
