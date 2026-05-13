<?php
require_once 'mailer.php';

// Test d'envoi d'email
$result = BlogMailer::notifyNewComment('Test User', 'Ceci est un test de commentaire', 'Test Post');
echo "Résultat notification commentaire: " . ($result ? 'Succès' : 'Échec') . "\n";

$result2 = BlogMailer::notifyNewPost('Test Publication', 'Contenu de test pour la publication', 'Test Catégorie', 'published');
echo "Résultat notification publication: " . ($result2 ? 'Succès' : 'Échec') . "\n";
?>