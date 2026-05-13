<?php
/**
 * Classe Mailer pour envoyer des emails via SMTP
 * Fonctionne avec Gmail et autres serveurs SMTP
 */

if (!class_exists('Mailer')) {
class Mailer {
    private $config;
    
    public function __construct() {
        $this->config = require __DIR__ . '/Mail.php';
    }
    
    /**
     * Envoyer un email
     */
    public function send($to, $subject, $body, $fromName = null) {
        try {
            $fromEmail = $this->config['from']['email'];
            $fromName = $fromName ?? $this->config['from']['name'];
            
            if ($this->config['driver'] === 'smtp') {
                return $this->sendViaSMTP($to, $subject, $body, $fromEmail, $fromName);
            } else {
                // Fallback: sendmail
                return $this->sendViaMailFunction($to, $subject, $body, $fromEmail, $fromName);
            }
        } catch (Exception $e) {
            error_log('Mail Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Envoyer via SMTP (Gmail-compatible)
     */
    private function sendViaSMTP($to, $subject, $body, $fromEmail, $fromName) {
        try {
            $host = $this->config['host'];
            $port = (int) $this->config['port'];
            $username = $this->config['username'];
            $password = $this->config['password'];
            $encryption = $this->config['encryption'];
            
            // Créer un socket
            $context = stream_context_create();
            
            // Configurer SSL/TLS si nécessaire
            if ($encryption === 'tls' || $encryption === 'ssl') {
                stream_context_set_option($context, 'ssl', 'allow_self_signed', true);
                stream_context_set_option($context, 'ssl', 'verify_peer', false);
                stream_context_set_option($context, 'ssl', 'verify_peer_name', false);
            }
            
            $connect = fsockopen($host, $port, $errno, $errstr, 30);
            
            if (!$connect) {
                throw new Exception("Connexion SMTP échouée: $errstr ($errno)");
            }
            
            // Lire la réponse serveur
            $this->readResponse($connect);
            
            // Envoyer EHLO
            fputs($connect, "EHLO localhost\r\n");
            $this->readResponse($connect);
            
            // STARTTLS si nécessaire
            if ($encryption === 'tls') {
                fputs($connect, "STARTTLS\r\n");
                $this->readResponse($connect);
                stream_socket_enable_crypto($connect, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            }
            
            // Authentification
            if ($username && $password) {
                fputs($connect, "AUTH LOGIN\r\n");
                $this->readResponse($connect);
                
                fputs($connect, base64_encode($username) . "\r\n");
                $this->readResponse($connect);
                
                fputs($connect, base64_encode($password) . "\r\n");
                $response = $this->readResponse($connect);
                
                if (strpos($response, '235') === false) {
                    throw new Exception("Authentification SMTP échouée");
                }
            }
            
            // Expéditeur
            fputs($connect, "MAIL FROM: <{$fromEmail}>\r\n");
            $this->readResponse($connect);
            
            // Destinataire
            fputs($connect, "RCPT TO: <{$to}>\r\n");
            $this->readResponse($connect);
            
            // Données
            fputs($connect, "DATA\r\n");
            $this->readResponse($connect);
            
            // Construire le message
            $message = "From: {$fromName} <{$fromEmail}>\r\n";
            $message .= "To: {$to}\r\n";
            $message .= "Subject: " . $this->encodeSubject($subject) . "\r\n";
            $message .= "MIME-Version: 1.0\r\n";
            $message .= "Content-Type: text/html; charset=UTF-8\r\n";
            $message .= "Content-Transfer-Encoding: 8bit\r\n";
            $message .= "\r\n";
            $message .= $body . "\r\n";
            
            // Envoyer le message
            fputs($connect, $message);
            fputs($connect, "\r\n.\r\n");
            
            $response = $this->readResponse($connect);
            
            if (strpos($response, '250') === false) {
                throw new Exception("Erreur lors de l'envoi du message: " . $response);
            }
            
            // Fermer la connexion
            fputs($connect, "QUIT\r\n");
            fclose($connect);
            
            return true;
            
        } catch (Exception $e) {
            error_log('SMTP Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Lire la réponse du serveur SMTP
     */
    private function readResponse($connect) {
        $response = '';
        while ($line = fgets($connect, 515)) {
            $response .= $line;
            if (substr($line, 3, 1) === ' ') {
                break;
            }
        }
        return $response;
    }
    
    /**
     * Encoder le sujet en UTF-8 si nécessaire
     */
    private function encodeSubject($subject) {
        if (preg_match('/[\x80-\xFF]/', $subject)) {
            return '=?UTF-8?B?' . base64_encode($subject) . '?=';
        }
        return $subject;
    }
    
    /**
     * Fallback: Envoyer via mail() function
     */
    private function sendViaMailFunction($to, $subject, $body, $fromEmail, $fromName) {
        $headers = [
            'From' => "{$fromName} <{$fromEmail}>",
            'Reply-To' => $fromEmail,
            'Content-Type' => 'text/html; charset=UTF-8',
            'X-Mailer' => 'PHP/' . phpversion(),
        ];
        
        $headerString = '';
        foreach ($headers as $key => $value) {
            $headerString .= "{$key}: {$value}\r\n";
        }
        
        return mail($to, $subject, $body, $headerString);
    }
    
    /**
     * Envoyer un email de confirmation d'inscription
     */
    public function sendInscriptionConfirmation($prenom, $nom, $email, $eventTitle, $eventDate, $eventLieu) {
        $subject = "Confirmation d'inscription - {$eventTitle}";
        
        $body = $this->getInscriptionConfirmationTemplate(
            $prenom,
            $nom,
            $eventTitle,
            $eventDate,
            $eventLieu
        );
        
        return $this->send($email, $subject, $body);
    }
    
    /**
     * Template HTML pour l'email de confirmation
     */
    private function getInscriptionConfirmationTemplate($prenom, $nom, $eventTitle, $eventDate, $eventLieu) {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        .header {
            background-color: #007bff;
            color: white;
            padding: 20px;
            border-radius: 5px 5px 0 0;
            text-align: center;
        }
        .content {
            padding: 20px;
            background-color: white;
        }
        .event-details {
            background-color: #f0f0f0;
            padding: 15px;
            border-left: 4px solid #007bff;
            margin: 15px 0;
            border-radius: 3px;
        }
        .event-details p {
            margin: 8px 0;
        }
        .event-details strong {
            color: #007bff;
        }
        .footer {
            text-align: center;
            font-size: 12px;
            color: #666;
            padding: 15px;
            border-top: 1px solid #ddd;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>✓ Inscription Confirmée</h1>
        </div>
        
        <div class="content">
            <p>Bonjour <strong>{$prenom} {$nom}</strong>,</p>
            
            <p>Merci de votre inscription ! Votre place est réservée pour l'événement suivant :</p>
            
            <div class="event-details">
                <p><strong>Événement :</strong> {$eventTitle}</p>
                <p><strong>Date :</strong> {$eventDate}</p>
                <p><strong>Lieu :</strong> {$eventLieu}</p>
            </div>
            
            <p>Votre inscription est désormais confirmée. Vous recevrez les détails supplémentaires par email avant l'événement.</p>
            
            <p>Si vous avez des questions, n'hésitez pas à nous contacter.</p>
            
            <p>À bientôt !<br>
            <strong>L'équipe des événements</strong></p>
        </div>
        
        <div class="footer">
            <p>Cet email a été envoyé automatiquement. Veuillez ne pas répondre directement à ce message.</p>
            <p>&copy; 2026 Gestion Événements. Tous droits réservés.</p>
        </div>
    </div>
</body>
</html>
HTML;
    }
}
}
?>

