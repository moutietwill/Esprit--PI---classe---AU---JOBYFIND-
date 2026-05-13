<?php
require_once __DIR__ . '/../projetweb/connexion.php';
$db = Config::GetConnexion();
echo "--- STRUCTURE DE LA TABLE COMMENTS ---\n";
try {
    $cols = $db->query("SHOW COLUMNS FROM comments")->fetchAll(PDO::FETCH_ASSOC);
    print_r($cols);
} catch (Exception $e) {
    echo "Erreur: " . $e->getMessage();
}
?>
