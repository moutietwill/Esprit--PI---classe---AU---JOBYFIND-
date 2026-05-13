<?php

require_once(__DIR__ . '/../../config/session.php');
startAppSession();
session_unset();
session_destroy();

header('Location: ../frontoffice/signin.php');
exit();
?>
