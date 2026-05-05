<?php
session_start();
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'Admin') {
        header('Location: /this/view/backoffice/admine.php');
    } else {
        header('Location: /this/view/frontoffice/profile.php');
    }
    exit();
}
header('Location: view/frontoffice/signin.php');
?>