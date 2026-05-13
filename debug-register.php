<?php
// TEMP DEBUG FILE - DELETE AFTER USE
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once(__DIR__ . '/config.php');
require_once(__DIR__ . '/Controller/UtilisateurController.php');
require_once(__DIR__ . '/Model/Utilisateur.php');

echo "<h2>Debug: Test Registration</h2>";

// 1. Test DB connection
try {
    $db = config::getConnexion();
    echo "<p style='color:green'>✓ Database connected to: <strong>gestion_evenements</strong></p>";
} catch (Exception $e) {
    echo "<p style='color:red'>✗ DB Connection Error: " . $e->getMessage() . "</p>";
    die();
}

// 2. Check utilisateurs table exists
try {
    $stmt = $db->query("DESCRIBE utilisateurs");
    $cols = $stmt->fetchAll();
    echo "<p style='color:green'>✓ Table 'utilisateurs' exists with " . count($cols) . " columns</p>";
    echo "<ul>";
    foreach ($cols as $col) { echo "<li>" . $col['Field'] . " (" . $col['Type'] . ")</li>"; }
    echo "</ul>";
} catch (Exception $e) {
    echo "<p style='color:red'>✗ Table Error: " . $e->getMessage() . "</p>";
}

// 3. Test an insert
echo "<h3>Testing User Insert...</h3>";
try {
    $userController = new UtilisateurController();
    $user = new Utilisateur([
        'first_name' => 'Debug',
        'last_name' => 'Test',
        'username' => 'debugtest' . time(),
        'date_of_birth' => null,
        'phone' => null,
        'city' => null,
        'email' => 'debug_' . time() . '@test.com',
        'password' => 'TestPass123!',
        'role' => 'Entrepreneur',
        'status' => 'Actif'
    ]);
    $userId = $userController->addUser($user);
    if ($userId) {
        echo "<p style='color:green'>✓ User created! ID = $userId</p>";
        // Clean up
        $db->prepare("DELETE FROM utilisateurs WHERE id = :id")->execute([':id' => $userId]);
        echo "<p style='color:blue'>↩ Test user deleted (cleanup)</p>";
    } else {
        echo "<p style='color:red'>✗ addUser() returned false</p>";
    }
} catch (Exception $e) {
    echo "<p style='color:red'>✗ Insert Error: " . $e->getMessage() . "</p>";
}
?>
