<?php
function checkDb($name) {
    try {
        $db = new PDO("mysql:host=localhost;dbname=$name", 'root', '');
        $stmt = $db->query("SHOW TABLES");
        echo "Tables in database '$name':\n";
        print_r($stmt->fetchAll(PDO::FETCH_COLUMN));
    } catch (Exception $e) {
        echo "Error connecting to '$name': " . $e->getMessage() . "\n";
    }
}

checkDb('gestion_evenements');
echo "\n---\n";
checkDb('jobyfind');
