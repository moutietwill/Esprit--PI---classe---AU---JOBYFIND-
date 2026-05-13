<?php
/**
 * Loader pour les variables d'environnement (.env)
 */

class EnvLoader {
    private static $vars = [];
    private static $loaded = false;
    
    /**
     * Charger le fichier .env
     */
    public static function load() {
        if (self::$loaded) {
            return;
        }
        
        $envFile = __DIR__ . '/../.env';
        
        if (!file_exists($envFile)) {
            return;
        }
        
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            // Ignorer les commentaires
            if (strpos($line, '#') === 0) {
                continue;
            }
            
            // Parser KEY=VALUE
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                
                // Enlever les guillemets si présents
                if (preg_match('/^"(.*)"$/', $value) || preg_match('/^\'(.*)\'$/', $value)) {
                    $value = substr($value, 1, -1);
                }
                
                self::$vars[$key] = $value;
                putenv("{$key}={$value}");
            }
        }
        
        self::$loaded = true;
    }
    
    /**
     * Obtenir une variable d'environnement
     */
    public static function get($key, $default = null) {
        self::load();
        
        if (isset(self::$vars[$key])) {
            return self::$vars[$key];
        }
        
        $value = getenv($key);
        return $value !== false ? $value : $default;
    }
}

// Charger automatiquement les variables
EnvLoader::load();
?>
