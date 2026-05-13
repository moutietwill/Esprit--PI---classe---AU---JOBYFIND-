<?php
try {
    $db = new PDO('mysql:host=localhost;dbname=gestion_evenements', 'root', '');
    $stmt = $db->query('DESCRIBE evenement');
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC), JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
