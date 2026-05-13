<?php
/** @var Event $event */
$basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
$basePath = ($basePath && $basePath !== '.') ? $basePath : '';

$imagePath = (string) $event->getImage();
$imagePath = str_replace('\\', '/', trim($imagePath));
if ($imagePath !== '' && strpos($imagePath, 'public/') === 0) {
    $imagePath = substr($imagePath, 7);
}
$imageUrl = $imagePath !== '' ? $basePath . '/' . ltrim($imagePath, '/') : '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier un evenement</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f8fafc; color: #1f2937; margin: 0; padding: 32px; }
        .card { max-width: 720px; margin: 0 auto; background: #fff; border-radius: 16px; padding: 32px; box-shadow: 0 14px 30px rgba(15, 23, 42, 0.08); }
        label { display: block; font-weight: 600; margin: 14px 0 6px; }
        input, textarea { width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; }
        button { margin-top: 18px; border: 0; background: #2563eb; color: #fff; padding: 12px 18px; border-radius: 8px; cursor: pointer; }
        a { color: #2563eb; text-decoration: none; }
    </style>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
</head>
<body>
    <div class="card">
        <p><a href="<?php echo htmlspecialchars($basePath . '/index.php/events', ENT_QUOTES, 'UTF-8'); ?>">Retour</a></p>
        <h1>Modifier un evenement</h1>
        <form method="POST" action="<?php echo htmlspecialchars($basePath . '/index.php/events/update/' . urlencode((string) $event->getId()), ENT_QUOTES, 'UTF-8'); ?>" enctype="multipart/form-data">
            <label for="titre">Titre</label>
            <input id="titre" name="titre" type="text" value="<?php echo htmlspecialchars($event->getTitre(), ENT_QUOTES, 'UTF-8'); ?>">

            <label for="description">Description</label>
            <textarea id="description" name="description" rows="5"><?php echo htmlspecialchars($event->getDescription(), ENT_QUOTES, 'UTF-8'); ?></textarea>

            <label for="date">Date</label>
            <input id="date" type="text" name="date" value="<?php echo htmlspecialchars($event->getDate(), ENT_QUOTES, 'UTF-8'); ?>">

            <label for="lieu">Lieu</label>
            <input id="lieu" name="lieu" type="text" value="<?php echo htmlspecialchars($event->getLieu(), ENT_QUOTES, 'UTF-8'); ?>">

            <label for="idOrganisateur">ID organisateur</label>
            <input id="idOrganisateur" type="text" name="idOrganisateur" value="<?php echo htmlspecialchars((string) $event->getIdOrganisateur(), ENT_QUOTES, 'UTF-8'); ?>">

            <label for="image">Image evenement</label>
            <?php if ($imageUrl !== ''): ?>
                <p style="margin: 6px 0 8px;">
                    <img src="<?php echo htmlspecialchars($imageUrl, ENT_QUOTES, 'UTF-8'); ?>" alt="Image actuelle" style="max-width: 180px; border-radius: 8px; border: 1px solid #cbd5e1;">
                </p>
            <?php endif; ?>
            <input id="image" type="file" name="image" accept=".jpg,.jpeg,.png,.gif,.webp">

            <button type="submit">Mettre a jour</button>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Activer Flatpickr sur le champ date pour avoir un calendrier (sans utiliser HTML5 type="date")
            flatpickr("#date", {
                dateFormat: "Y-m-d",
                allowInput: true
            });

            // Validation JS personnalisée sans attributs HTML5
            document.querySelector('form').addEventListener('submit', function(e) {
                var desc = document.getElementById('description').value.trim();
                // Controler si description est remplie et a au moins 10 caracteres
                if (desc.length < 10) {
                    e.preventDefault();
                    alert("Attention : La description est obligatoire et doit comporter au moins 10 caractères.");
                }
            });
        });
    </script>
</body>
</html>
