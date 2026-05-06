<?php
/**
 * MailManager — Classe helper pour l'envoi d'emails via PHPMailer
 */

require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailManager {

    /**
     * Envoie un email de confirmation de candidature
     */
    public static function sendConfirmation($toEmail, $prenom, $nom, $titreOffre) {
        $config = require __DIR__ . '/../config/mail.php';
        
        $mail = new PHPMailer(true);

        try {
            // Configuration du serveur
            $mail->isSMTP();
            $mail->Host       = $config['host'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $config['username'];
            $mail->Password   = $config['password'];
            $mail->SMTPSecure = $config['encryption'];
            $mail->Port       = $config['port'];
            $mail->CharSet    = 'UTF-8';

            // Expéditeur et Destinataire
            $mail->setFrom($config['from_email'], $config['from_name']);
            $mail->addAddress($toEmail, "$prenom $nom");

            // Contenu
            $mail->isHTML(true);
            $mail->Subject = sprintf($config['subject_template'], $titreOffre);
            $mail->Body    = sprintf($config['body_template'], $prenom, $nom, $titreOffre);
            $mail->AltBody = "Bonjour $prenom $nom, votre candidature pour $titreOffre a bien été reçue.";

            $mail->send();
            return ['success' => true, 'error' => null];

        } catch (Exception $e) {
            self::log("Erreur Mail: " . $mail->ErrorInfo);
            return ['success' => false, 'error' => $mail->ErrorInfo];
        } catch (\Exception $e) {
            self::log("Erreur Générale Mail: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Log des erreurs
     */
    private static function log($message) {
        $file = __DIR__ . '/../mail_errors.log';
        $time = date('Y-m-d H:i:s');
        file_put_contents($file, "[$time] $message" . PHP_EOL, FILE_APPEND);
    }
}
?>
