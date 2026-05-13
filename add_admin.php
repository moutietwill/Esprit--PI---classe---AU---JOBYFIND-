<?php
require_once(__DIR__ . '/config.php');
require_once(__DIR__ . '/Controller/UtilisateurController.php');
require_once(__DIR__ . '/Model/Utilisateur.php');

$db = config::getConnexion();

// Vérifier si l'utilisateur existe déjà
$stmt = $db->prepare("SELECT * FROM utilisateurs WHERE email = 'admin@gmail.com'");
$stmt->execute();
$user = $stmt->fetch();

if ($user) {
    echo "<h1>L'utilisateur admin@gmail.com existe déjà !</h1>";
    echo "<p>ID: " . $user['id'] . "</p>";
    echo "<p>Rôle actuel: " . $user['role'] . "</p>";
    
    // S'assurer qu'il a le rôle Mentor
    if ($user['role'] !== 'Mentor') {
        $update = $db->prepare("UPDATE utilisateurs SET role = 'Mentor' WHERE email = 'admin@gmail.com'");
        $update->execute();
        echo "<p style='color:orange;'>Rôle mis à jour vers 'Mentor'.</p>";
    }
} else {
    echo "<h1>Création de l'utilisateur admin@gmail.com...</h1>";
    
    $hashedPassword = password_hash('0000', PASSWORD_DEFAULT);
    
    $sql = "INSERT INTO utilisateurs (first_name, last_name, username, date_of_birth, phone, city, email, password, role, status) 
            VALUES ('Mentor', 'Système', 'mentor', '2000-01-01', '00000000', 'Tunis', 'admin@gmail.com', :password, 'Mentor', 'Actif')";
    
    $stmt = $db->prepare($sql);
    $stmt->execute(['password' => $hashedPassword]);
    
    echo "<p style='color:green;'>L'utilisateur admin@gmail.com a été créé avec succès avec le mot de passe 0000 !</p>";
}

echo "<br><a href='View/frontoffice/signin.php'>Aller à la page de connexion</a>";
?>
