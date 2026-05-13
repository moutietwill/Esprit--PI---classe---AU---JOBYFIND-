<?php
require_once __DIR__ . '/connexion.php';
$db = Config::GetConnexion();
$tables = ['posts', 'comments', 'likes', 'post_ratings'];

header('Content-Type: text/plain');
echo "Checking tables in " . $db->query("SELECT database()")->fetchColumn() . ":\n";

foreach ($tables as $table) {
    try {
        $db->query("SELECT 1 FROM $table LIMIT 1");
        echo "Table [$table]: OK\n";
    } catch (Exception $e) {
        echo "Table [$table]: MISSING or ERROR (" . $e->getMessage() . ")\n";
    }
}
?>
