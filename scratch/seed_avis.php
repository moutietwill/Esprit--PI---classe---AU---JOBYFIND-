<?php
include 'config.php';
try {
    $db = config::getConnexion();
    
    // Get all formation IDs
    $formations = $db->query("SELECT id FROM formation")->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($formations as $fid) {
        // Add 2-5 random reviews if none exist
        $count = $db->query("SELECT COUNT(*) FROM avis WHERE id_formation = $fid")->fetchColumn();
        if ($count == 0) {
            $numReviews = rand(2, 5);
            for ($j = 0; $j < $numReviews; $j++) {
                $note = rand(3, 5);
                $db->exec("INSERT INTO avis (id_formation, note, commentaire) VALUES ($fid, $note, 'Super formation !')");
            }
        }
    }
    echo "Test reviews added successfully.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
