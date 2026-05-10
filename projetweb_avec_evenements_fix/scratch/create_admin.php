<?php
require 'config/Database.php';
$db = Database::getInstance()->getConnection();
$pass = password_hash('0000', PASSWORD_DEFAULT);

try {
    $stmt = $db->prepare("INSERT INTO utilisateurs (first_name, last_name, email, password, role, status) VALUES ('Admin', 'Super', 'admin123@gmail.com', :pass, 'Admin', 'Actif')");
    $stmt->execute([':pass' => $pass]);
    echo 'Admin user created successfully!';
} catch (PDOException $e) {
    if ($e->getCode() == 23000) {
        echo 'Admin user already exists. Updating password to 0000 and role to Admin.';
        $stmt = $db->prepare("UPDATE utilisateurs SET password = :pass, role = 'Admin', status = 'Actif' WHERE email = 'admin123@gmail.com'");
        $stmt->execute([':pass' => $pass]);
    } else {
        echo 'Error: ' . $e->getMessage();
    }
}
