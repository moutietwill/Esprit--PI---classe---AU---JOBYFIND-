<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('BASE_PATH', dirname(dirname(__FILE__)));

require_once BASE_PATH . '/core/Autoloader.php';

Autoloader::register(BASE_PATH);

$router = new Router();
$router->dispatch($_SERVER['REQUEST_URI'] ?? '/', $_SERVER['SCRIPT_NAME'] ?? '/public/index.php');
