<?php
include 'config.php';
$db = config::getConnexion();

// Create inscription table
$sqlInscription = "CREATE TABLE IF NOT EXISTS inscription (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100),
    prenom VARCHAR(100),
    email VARCHAR(100),
    telephone VARCHAR(20),
    methode_paiement VARCHAR(50),
    id_formation INT,
    FOREIGN KEY (id_formation) REFERENCES formation(id) ON DELETE CASCADE
)";
$db->exec($sqlInscription);

// Create avis table (for ratings)
$sqlAvis = "CREATE TABLE IF NOT EXISTS avis (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_formation INT,
    note INT,
    commentaire TEXT,
    date_avis TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_formation) REFERENCES formation(id) ON DELETE CASCADE
)";
$db->exec($sqlAvis);

echo "Tables inscription and avis ready.";
?>
