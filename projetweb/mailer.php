<?php
if (file_exists(__DIR__ . '/config.php')) {
    require_once __DIR__ . '/config.php';
}
/**
 * ═══════════════════════════════════════════════════════════
 *  MAILER - Notification par email via BREVO (Sendinblue)
 * ═══════════════════════════════════════════════════════════
 */

class BlogMailer {

    // Récupération dynamique de la clé
    private static function getApiKey() {
        return defined('BREVO_API_KEY') ? BREVO_API_KEY : '';
    }

    private static $fromEmail = 'malekchhoumi1920@gmail.com'; 
    private static $fromName = 'JobyFind Notifications';
    private static $adminEmail = 'malekchhoumi1920@gmail.com';
    private static $adminName = 'Admin JobyFind';

    /**
     * Envoie un email via l'API Brevo v3
     */
    private static function sendEmail($subject, $htmlContent) {
        $apiKey = self::getApiKey();
        $url = 'https://api.brevo.com/v3/smtp/email';

        $data = [
            'sender' => [
                'name'  => self::$fromName,
                'email' => self::$fromEmail
            ],
            'to' => [
                [
                    'email' => self::$adminEmail,
                    'name'  => self::$adminName
                ]
            ],
            'subject' => $subject,
            'htmlContent' => $htmlContent
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'api-key: ' . $apiKey,
            'Content-Type: application/json',
            'Accept: application/json'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Log pour debug
        $logFile = __DIR__ . '/mail_log.txt';
        $logEntry = date('Y-m-d H:i:s') . " | BREVO HTTP $httpCode | Subject: $subject | Resp: $response\n";
        file_put_contents($logFile, $logEntry, FILE_APPEND);

        return $httpCode >= 200 && $httpCode < 300;
    }

    public static function notifyNewComment($userName, $commentContent, $postTitle = '') {
        $date = date('d/m/Y à H:i');
        $html = "<h2>💬 Nouveau Commentaire</h2>
                 <p><strong>Utilisateur:</strong> $userName</p>
                 <p><strong>Blog:</strong> $postTitle</p>
                 <p><strong>Contenu:</strong><br>$commentContent</p>
                 <p><em>Date: $date</em></p>";
        return self::sendEmail("💬 Nouveau commentaire de $userName", $html);
    }

    public static function notifyNewPost($title, $content, $category = '', $status = 'published') {
        $date = date('d/m/Y à H:i');
        $html = "<h2>📢 Nouvelle Publication</h2>
                 <p><strong>Titre:</strong> $title</p>
                 <p><strong>Catégorie:</strong> $category</p>
                 <p><strong>Statut:</strong> $status</p>
                 <p><em>Date: $date</em></p>";
        return self::sendEmail("📢 Nouvelle publication : $title", $html);
    }
}
?>
