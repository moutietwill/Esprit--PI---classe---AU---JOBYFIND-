<?php
require_once __DIR__ . '/config/Database.php';
$db = Database::getInstance()->getConnection();
$s = $db->query('SELECT idEvenement, titre, idOrganisateur FROM evenement');
print_r($s->fetchAll(PDO::FETCH_ASSOC));
