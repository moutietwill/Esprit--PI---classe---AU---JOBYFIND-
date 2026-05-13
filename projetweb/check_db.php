<?php
require 'connexion.php';
$db = Config::GetConnexion();
$stmt = $db->query("SELECT id, title, cover_image FROM posts WHERE title LIKE '%kosay%' OR title LIKE '%React.js%' OR title LIKE '%Pratiques en PHP%'");
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($results);
