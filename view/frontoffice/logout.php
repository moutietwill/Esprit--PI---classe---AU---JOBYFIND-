<?php
session_start();
session_unset();
session_destroy();
$reason = isset($_GET['reason']) && $_GET['reason'] === 'violation' ? '?error=Securite' : '';
header('Location: signin.php' . $reason);
exit();
?>
