<?php
// Charger les variables d'environnement
require_once __DIR__ . '/../config_env.php';

// Configuration de l'API Stripe
class api_stripe {
    // Clé secrète de test Stripe (à ne jamais exposer côté client)
    public static function getSecretKey() {
        return $_ENV['STRIPE_SECRET_KEY'] ?? '';
    }
    
    // Clé publique de test (utile pour l'interface frontend si on utilisait Stripe Elements)
    public static function getPublicKey() {
        return $_ENV['STRIPE_PUBLIC_KEY'] ?? '';
    }
}
?>
