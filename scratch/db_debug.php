<?php
include 'config.php';
try {
    $db = config::getConnexion();
    echo "TABLES:\n";
    $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    foreach($tables as $t) echo "- $t\n";
    
    if(in_array('formation', $tables)) {
        $count = $db->query("SELECT COUNT(*) FROM formation")->fetchColumn();
        echo "\nCOUNT formation: " . $count . "\n";
        if ($count == 0) {
            echo "Inserting test formation...\n";
            $db->exec("INSERT INTO formation (titre, prix, date, duree, description, categorie) VALUES ('Formation Test AI', 150.0, '2026-06-01', '2 mois', 'Une formation générée par IA pour tester.', 'Développement Web')");
            echo "New count: " . $db->query("SELECT COUNT(*) FROM formation")->fetchColumn() . "\n";
        }
    }
    if(in_array('formations', $tables)) {
        echo "\nCOUNT formations: " . $db->query("SELECT COUNT(*) FROM formations")->fetchColumn() . "\n";
    }
} catch(Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
?>
