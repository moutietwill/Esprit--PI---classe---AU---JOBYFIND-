<?php
require_once __DIR__ . '/config/Database.php';

$db = Database::getInstance()->getConnection();
$stmt = $db->query("SHOW COLUMNS FROM evenement LIKE 'image'");

if (!$stmt->fetch()) {
    $db->exec("ALTER TABLE evenement ADD COLUMN image VARCHAR(255) NULL DEFAULT 'public/assets/images/event/default.jpg'");
    echo "image column added";
} else {
    echo "image column exists";
}
