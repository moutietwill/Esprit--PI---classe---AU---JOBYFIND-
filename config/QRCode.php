<?php

class QRCode {
    private $size = 300;
    private $encoding = 'UTF-8';
    private $errorCorrection = 'L';
    private $margin = 2;

    /**
     * Générer un code QR avec l'API qrserver
     * @param string $text Texte à encoder
     * @param int $size Taille de l'image (100-1000)
     * @return string URL de l'image QR
     */
    public static function generate($text, $size = 300) {
        // Encoder le texte pour l'URL
        $encodedText = urlencode($text);
        
        // Utiliser l'API qrserver.com (gratuit et sans clé API requise)
        $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=" . $size . "x" . $size . "&data=" . $encodedText;
        
        return $qrUrl;
    }

    /**
     * Récupérer l'adresse IP LAN réelle du serveur (accessible depuis le téléphone)
     */
    public static function getServerIP() {
        // Priorité 0 : Variable SERVER_IP dans le fichier .env (configuration manuelle)
        $envFile = __DIR__ . '/../.env';
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos(trim($line), 'SERVER_IP=') === 0) {
                    $ip = trim(substr($line, strlen('SERVER_IP=')));
                    if ($ip && filter_var($ip, FILTER_VALIDATE_IP)) {
                        return $ip;
                    }
                }
            }
        }

        // Méthode 1 : Windows - ipconfig
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' && function_exists('shell_exec')) {
            $output = @shell_exec('ipconfig');
            if ($output) {
                if (preg_match_all('/IPv4[^:]*:\s*((?:192\.168|10\.|172\.(?:1[6-9]|2\d|3[01]))\.\d+\.\d+)/i', $output, $matches)) {
                    if (!empty($matches[1][0])) {
                        return $matches[1][0];
                    }
                }
            }
        }

        // Méthode 2 : Linux/Mac - hostname -I
        if (function_exists('shell_exec')) {
            $output = @shell_exec("hostname -I 2>/dev/null");
            if ($output) {
                $ips = explode(' ', trim($output));
                foreach ($ips as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_LOOPBACK | FILTER_FLAG_IPV4)) {
                        return $ip;
                    }
                }
            }
        }

        // Méthode 3 : gethostbyname
        $hostname = gethostname();
        if ($hostname) {
            $ip = gethostbyname($hostname);
            if ($ip !== $hostname && $ip !== '127.0.0.1' && $ip !== '::1') {
                return $ip;
            }
        }

        // Méthode 4 : SERVER_ADDR si non-loopback
        if (!empty($_SERVER['SERVER_ADDR']) && !in_array($_SERVER['SERVER_ADDR'], ['127.0.0.1', '::1'])) {
            return $_SERVER['SERVER_ADDR'];
        }

        // Fallback
        return 'localhost';
    }

    /**
     * Obtenir l'URL d'un événement pour le QR code
     * @param int $eventId ID de l'événement
     * @return string URL complète de l'événement
     */
    public static function getEventUrl($eventId) {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? '';
        $port = $_SERVER['SERVER_PORT'] ?? '80';
        
        // Si c'est localhost, utiliser l'adresse IP locale
        if (strpos($host, 'localhost') !== false || strpos($host, '127.0.0.1') !== false) {
            $ip = self::getServerIP();
            $host = $ip;
            // Ajouter le port s'il n'est pas le port par défaut
            if ($port != '80' && $port != '443') {
                $host .= ':' . $port;
            }
        }
        
        $basePath = $_SERVER['SCRIPT_NAME'] ?? '/projet/projetweb_avec_evenements_fix/public/index.php';
        
        return $protocol . '://' . $host . $basePath . '/events/show/' . $eventId;
    }

    /**
     * Générer un QR code pour un événement
     * @param Event $event
     * @return string URL du QR code
     */
    public static function generateForEvent($event) {
        $eventUrl = self::getEventUrl($event->getId());
        return self::generate($eventUrl, 300);
    }

    /**
     * Générer un QR code pour l'inscription à un événement
     * @param Event $event
     * @return string URL du QR code
     */
    public static function generateForRegistration($event) {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? '';
        $port = $_SERVER['SERVER_PORT'] ?? '80';
        
        // Si c'est localhost, utiliser l'adresse IP locale
        if (strpos($host, 'localhost') !== false || strpos($host, '127.0.0.1') !== false) {
            $ip = self::getServerIP();
            $host = $ip;
            if ($port != '80' && $port != '443') {
                $host .= ':' . $port;
            }
        }
        
        $basePath = $_SERVER['SCRIPT_NAME'] ?? '/projet/projetweb_avec_evenements_fix/public/index.php';
        $registrationUrl = $protocol . '://' . $host . $basePath . '/events/register/' . $event->getId();
        
        return self::generate($registrationUrl, 300);
    }
    
    /**
     * Déboguer - afficher l'URL générée
     */
    public static function getDebugUrl($eventId) {
        return self::getEventUrl($eventId);
    }
}

