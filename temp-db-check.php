<?php
require "app/config/Database.php";
$db = Database::getInstance()->getConnection();
$stmt = $db->query("SELECT COUNT(*) AS c FROM evenement");
$row = $stmt->fetch(PDO::FETCH_ASSOC);
echo $row["c"];
?>
