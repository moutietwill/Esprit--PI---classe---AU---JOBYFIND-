<?php
require_once __DIR__ . '/../connexion.php';
$db = Config::GetConnexion();
$res = $db->query("SELECT id, title, cover_image FROM posts LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: text/plain');
foreach ($res as $row) {
    echo "ID: {$row['id']} | Title: {$row['title']} | Image: [{$row['cover_image']}]\n";
}
?>
