<?php
try {
    $db = new PDO('mysql:host=localhost;dbname=jobyfind', 'root', '');
    $res = $db->query('SELECT id_question, enonce FROM question ORDER BY id_question DESC LIMIT 10');
    $questions = $res->fetchAll(PDO::FETCH_ASSOC);
    foreach ($questions as $q) {
        echo "[ID: " . $q['id_question'] . "] " . $q['enonce'] . "\n";
    }
} catch (Exception $e) {
    echo $e->getMessage();
}
