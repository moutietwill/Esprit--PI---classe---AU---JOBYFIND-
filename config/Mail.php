<?php
/**
 * Email Configuration
 * Utilisez XAMPP avec Mailhog pour les tests locaux
 * Ou configurez vos paramètres SMTP réels
 */

require_once __DIR__ . '/EnvLoader.php';

return [
    // Utiliser 'sendmail' pour l'utilisation locale avec Mailhog
    // Ou 'smtp' pour un serveur SMTP réel
    'driver' => EnvLoader::get('MAIL_DRIVER', 'sendmail'),
    
    // Configuration SMTP (si driver = 'smtp')
    'host'       => EnvLoader::get('MAIL_HOST', 'localhost'),
    'port'       => (int) EnvLoader::get('MAIL_PORT', 1025),
    'username'   => EnvLoader::get('MAIL_USERNAME', ''),
    'password'   => EnvLoader::get('MAIL_PASSWORD', ''),
    'encryption' => EnvLoader::get('MAIL_ENCRYPTION', ''), // null, 'tls', ou 'ssl'
    
    // Email par défaut (qui envoie les emails)
    'from' => [
        'name'  => EnvLoader::get('MAIL_FROM_NAME', 'Gestion Événements'),
        'email' => EnvLoader::get('MAIL_FROM_EMAIL', 'noreply@evenements.local'),
    ],
];
?>

