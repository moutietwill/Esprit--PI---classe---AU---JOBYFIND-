<?php
$path = $_SERVER['REQUEST_URI'] ?? '';
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$baseDir = dirname($scriptName);
$route = str_replace($baseDir, '', $path);
$route = ltrim($route, '/');

header('Location: public/index.php/' . $route);
exit;
