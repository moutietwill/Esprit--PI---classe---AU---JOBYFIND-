<?php
// Chargement de la clé API depuis le fichier local s'il existe
if (file_exists(__DIR__ . '/config.local.php')) {
    require_once __DIR__ . '/config.local.php';
}

if (!defined('BREVO_API_KEY')) {
    define('BREVO_API_KEY', 'VOTRE_CLE_API_ICI'); // Valeur par défaut vide
}
?>
