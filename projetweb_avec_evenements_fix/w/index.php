<?php
// Redirect alias for legacy /view/index.php routes to the public front controller.
$requestUri = $_SERVER['REQUEST_URI'];
$scriptName = $_SERVER['SCRIPT_NAME'];

// Preserve any path after /view/index.php
$path = '';
if (strpos($requestUri, $scriptName) === 0) {
    $path = substr($requestUri, strlen($scriptName));
} elseif (strpos($requestUri, '/view/index.php') === 0) {
    $path = substr($requestUri, strlen('/view/index.php'));
}

$path = ltrim($path, '/');

$redirect = '/projetweb_avec_evenements/public/index.php';
if ($path !== '') {
    $redirect .= '/' . $path;
}

header('Location: ' . $redirect);
exit;
