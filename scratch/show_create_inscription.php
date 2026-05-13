<?php
include 'config.php';
try {
    $db = config::getConnexion();
    $stmt = $db->query("SHOW CREATE TABLE inscription");
    $row = $stmt->fetch(PDO::FETCH_NUM);
    echo $row[1];
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
