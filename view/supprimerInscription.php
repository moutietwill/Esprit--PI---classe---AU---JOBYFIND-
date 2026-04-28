<?php
include '../controller/inscriptionC.php';

$inscriptionC = new inscriptionC();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    $inscriptionC->deleteInscription($id);
}

header('Location: backofficeInscription.php');
exit();
?>
