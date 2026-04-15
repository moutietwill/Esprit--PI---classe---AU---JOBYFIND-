<?php
include '../controller/formationC.php';

$formationC = new formationC();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    $formationC->deleteFormation($id);
}

header('Location: backoffice.php');
exit();
?>
