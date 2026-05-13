<?php
require_once __DIR__ . '/connexion.php';

try {
    $db = Config::GetConnexion();
    
    echo "Démarrage de la migration...<br>";

    // 1. Ajouter instructor
    try {
        $db->exec("ALTER TABLE posts ADD COLUMN instructor VARCHAR(255) DEFAULT 'JobyFind AI' AFTER category");
        echo "✅ Colonne 'instructor' ajoutée.<br>";
    } catch (Exception $e) {
        echo "ℹ️ Colonne 'instructor' déjà présente ou erreur : " . $e->getMessage() . "<br>";
    }

    // 2. Ajouter price
    try {
        $db->exec("ALTER TABLE posts ADD COLUMN price DECIMAL(10,2) DEFAULT 0.00 AFTER instructor");
        echo "✅ Colonne 'price' ajoutée.<br>";
    } catch (Exception $e) {
        echo "ℹ️ Colonne 'price' déjà présente.<br>";
    }

    // 3. Ajouter duration_hours
    try {
        $db->exec("ALTER TABLE posts ADD COLUMN duration_hours INT DEFAULT 0 AFTER price");
        echo "✅ Colonne 'duration_hours' ajoutée.<br>";
    } catch (Exception $e) {
        echo "ℹ️ Colonne 'duration_hours' déjà présente.<br>";
    }

    // 4. Ajouter excerpt si manquant
    try {
        $db->exec("ALTER TABLE posts ADD COLUMN excerpt TEXT DEFAULT NULL AFTER title");
        echo "✅ Colonne 'excerpt' ajoutée.<br>";
    } catch (Exception $e) {
        echo "ℹ️ Colonne 'excerpt' déjà présente.<br>";
    }

    echo "Migration terminée avec succès !";

} catch (Exception $e) {
    echo "❌ Erreur critique : " . $e->getMessage();
}
?>
