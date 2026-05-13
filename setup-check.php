<?php
header('Content-Type: text/html; charset=utf-8');
echo "<h1>Diagnostic JobyFind</h1>";

$checks = [
    'PHP Version' => PHP_VERSION,
    'mod_rewrite' => in_array('mod_rewrite', apache_get_modules() ?? []) ? '✅ Activé' : '❌ Désactivé (ou impossible à vérifier)',
    'DocumentRoot' => $_SERVER['DOCUMENT_ROOT'],
    'Current Dir' => __DIR__,
    'Database Connection' => 'Vérification...',
];

echo "<ul>";
foreach ($checks as $k => $v) {
    if ($k === 'Database Connection') {
        try {
            require_once 'config/Database.php';
            $db = Database::getInstance()->getConnection();
            $v = '✅ Connecté';
        } catch (Exception $e) {
            $v = '❌ Erreur: ' . $e->getMessage();
        }
    }
    echo "<li><strong>$k :</strong> $v</li>";
}
echo "</ul>";

echo "<h2>Structure des dossiers</h2>";
$paths = ['public', 'core', 'controllers', 'models', 'View', 'projetweb'];
echo "<ul>";
foreach ($paths as $p) {
    echo "<li>$p : " . (is_dir($p) ? '✅ Existe' : '❌ MANQUANT') . "</li>";
}
echo "</ul>";

echo "<h2>URL suggérée</h2>";
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$uri = $_SERVER['REQUEST_URI'];
$baseDir = dirname($_SERVER['SCRIPT_NAME']);
$projectUrl = $protocol . '://' . $host . $baseDir;

echo "<p>Votre projet semble être ici : <a href='$projectUrl/public/index.php'>$projectUrl/public/index.php</a></p>";
?>
