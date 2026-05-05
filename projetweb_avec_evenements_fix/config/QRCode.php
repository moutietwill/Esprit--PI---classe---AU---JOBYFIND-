<?php

class QRCode {
    private $size = 300;
    private $encoding = 'UTF-8';
    private $errorCorrection = 'L';
    private $margin = 2;

    /**
     * Generer un code QR avec l'API qrserver.
     */
    public static function generate($text, $size = 300) {
        $encodedText = urlencode($text);
        return "https://api.qrserver.com/v1/create-qr-code/?size={$size}x{$size}&data={$encodedText}";
    }

    /**
     * Check whether an IPv4 belongs to private LAN ranges.
     */
    private static function isPrivateIPv4($ip) {
        return is_string($ip)
            && preg_match('/^(10\\.|192\\.168\\.|172\\.(1[6-9]|2\\d|3[01])\\.)/', $ip) === 1;
    }

    /**
     * Check whether an IPv4 can be used for LAN QR links.
     */
    private static function isUsableLanIPv4($ip) {
        if (!is_string($ip) || !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return false;
        }

        if ($ip === '127.0.0.1' || $ip === '0.0.0.0') {
            return false;
        }

        return self::isPrivateIPv4($ip);
    }

    /**
     * Push IP once in target array.
     */
    private static function pushUniqueIp(&$ips, $ip) {
        if (!in_array($ip, $ips, true)) {
            $ips[] = $ip;
        }
    }

    /**
     * Read SERVER_IP from .env if present.
     */
    private static function getEnvServerIP() {
        $envFile = __DIR__ . '/../.env';
        if (!file_exists($envFile)) {
            return null;
        }

        $lines = @file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return null;
        }

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '' || strpos($trimmed, '#') === 0) {
                continue;
            }

            if (strpos($trimmed, 'SERVER_IP=') === 0) {
                $ip = trim(substr($trimmed, strlen('SERVER_IP=')));
                $ip = trim($ip, "\"' \t\r\n");
                return $ip !== '' ? $ip : null;
            }
        }

        return null;
    }

    /**
     * Detect local LAN IPv4 candidates.
     * On Windows, interfaces with default gateway are preferred.
     */
    private static function getLanIPv4Candidates() {
        $withGateway = [];
        $withoutGateway = [];

        if (function_exists('shell_exec') && strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $output = @shell_exec('ipconfig');
            if ($output) {
                $blocks = preg_split('/\R{2,}/', trim($output));
                foreach ($blocks as $block) {
                    if (!preg_match('/IPv4[^:]*:\s*([0-9]+\.[0-9]+\.[0-9]+\.[0-9]+)/i', $block, $ipMatch)) {
                        continue;
                    }

                    $ip = trim($ipMatch[1]);
                    if (!self::isUsableLanIPv4($ip)) {
                        continue;
                    }

                    $hasGateway = preg_match('/(?:Gateway|Passerelle)[^:]*:\s*([0-9]+\.[0-9]+\.[0-9]+\.[0-9]+)/i', $block) === 1;
                    if ($hasGateway) {
                        self::pushUniqueIp($withGateway, $ip);
                    } else {
                        self::pushUniqueIp($withoutGateway, $ip);
                    }
                }

                // Locale/encoding fallback parser.
                if (empty($withGateway) && empty($withoutGateway)) {
                    if (preg_match_all('/\b((?:192\.168|10\.|172\.(?:1[6-9]|2\d|3[01]))\.\d+\.\d+)\b/', $output, $matches)) {
                        foreach ($matches[1] as $ip) {
                            if (self::isUsableLanIPv4($ip)) {
                                self::pushUniqueIp($withoutGateway, $ip);
                            }
                        }
                    }
                }
            }
        }

        if (function_exists('shell_exec') && strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
            $output = @shell_exec('hostname -I 2>/dev/null');
            if ($output) {
                $ips = preg_split('/\s+/', trim($output));
                foreach ($ips as $ip) {
                    $ip = trim($ip);
                    if (self::isUsableLanIPv4($ip)) {
                        self::pushUniqueIp($withoutGateway, $ip);
                    }
                }
            }
        }

        return array_merge($withGateway, $withoutGateway);
    }

    /**
     * Recuperer l'adresse IP LAN reelle du serveur.
     */
    public static function getServerIP() {
        $envIp = self::getEnvServerIP();
        $candidates = self::getLanIPv4Candidates();

        // Keep env override only if it still exists locally,
        // or when auto-detection is unavailable.
        if (self::isUsableLanIPv4($envIp)) {
            if (in_array($envIp, $candidates, true)) {
                return $envIp;
            }

            if (empty($candidates)) {
                return $envIp;
            }
        }

        if (!empty($candidates)) {
            return $candidates[0];
        }

        $hostname = gethostname();
        if ($hostname) {
            $ip = gethostbyname($hostname);
            if (self::isUsableLanIPv4($ip)) {
                return $ip;
            }
        }

        if (!empty($_SERVER['SERVER_ADDR']) && self::isUsableLanIPv4($_SERVER['SERVER_ADDR'])) {
            return $_SERVER['SERVER_ADDR'];
        }

        // Last chance: keep manual value if it is a valid IPv4.
        if (is_string($envIp) && filter_var($envIp, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) && $envIp !== '127.0.0.1') {
            return $envIp;
        }

        return 'localhost';
    }

    /**
     * Resolve host used in LAN QR links.
     */
    private static function resolveHostForQr() {
        $host = $_SERVER['HTTP_HOST'] ?? '';
        $port = $_SERVER['SERVER_PORT'] ?? '80';

        if ($host === '' || strpos($host, 'localhost') !== false || strpos($host, '127.0.0.1') !== false) {
            $host = self::getServerIP();
            if ($port !== '80' && $port !== '443') {
                $host .= ':' . $port;
            }
        }

        return $host;
    }

    /**
     * Obtenir l'URL d'un evenement pour le QR code.
     */
    public static function getEventUrl($eventId) {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = self::resolveHostForQr();
        $basePath = $_SERVER['SCRIPT_NAME'] ?? '/projet/projetweb_avec_evenements_fix/public/index.php';

        return $protocol . '://' . $host . $basePath . '/events/show/' . $eventId;
    }

    /**
     * Generer un QR code pour un evenement.
     */
    public static function generateForEvent($event) {
        if (!is_object($event) || !method_exists($event, 'getId')) {
            return '';
        }
        $eventUrl = self::getEventUrl($event->getId());
        return self::generate($eventUrl, 300);
    }

    /**
     * Generer un QR code pour l'inscription a un evenement.
     */
    public static function generateForRegistration($event) {
        if (!is_object($event) || !method_exists($event, 'getId')) {
            return '';
        }
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = self::resolveHostForQr();
        $basePath = $_SERVER['SCRIPT_NAME'] ?? '/projet/projetweb_avec_evenements_fix/public/index.php';

        $registrationUrl = $protocol . '://' . $host . $basePath . '/events/register/' . $event->getId();
        return self::generate($registrationUrl, 300);
    }

    /**
     * Debug helper.
     */
    public static function getDebugUrl($eventId) {
        return self::getEventUrl($eventId);
    }
}
