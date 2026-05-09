<?php
// On inclut les fichiers de PHPMailer manuellement (sans Composer)
require_once __DIR__ . '/PHPMailer-6.9.1/src/Exception.php';
require_once __DIR__ . '/PHPMailer-6.9.1/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer-6.9.1/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class mailer {
    public static function sendConfirmationEmail($destinataire, $nom, $prenom, $titreFormation) {
        $mail = new PHPMailer(true);
        // Charger les variables d'environnement
        require_once __DIR__ . '/../config_env.php';

        try {
            // Configuration du serveur SMTP de Gmail
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            
            // ==========================================
            // IDENTIFIANTS GMAIL CHARGÉS DEPUIS LE .ENV :
            // ==========================================
            $mail->Username   = $_ENV['GMAIL_USERNAME'] ?? '';
            $mail->Password   = $_ENV['GMAIL_APP_PASSWORD'] ?? '';
            // ==========================================
            
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            
            // Pour éviter les soucis de certificat SSL en localhost
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );

            // Destinataires
            $mail->setFrom($mail->Username, 'JobyFind');
            $mail->addAddress($destinataire, $prenom . ' ' . $nom);

            // Contenu de l'e-mail
            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = "Confirmation d'inscription : " . $titreFormation;
            
            $message = '
            <html>
            <head>
              <style>
                body { font-family: Arial, sans-serif; background-color: #F8FAFC; margin: 0; padding: 20px; }
                .container { background-color: #ffffff; border-radius: 8px; padding: 30px; max-width: 600px; margin: 0 auto; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
                h2 { color: #1E3A8A; }
                p { color: #475569; line-height: 1.6; }
                .footer { margin-top: 30px; font-size: 0.85em; color: #94A3B8; text-align: center; }
                .btn { display: inline-block; padding: 10px 20px; background-color: #2563EB; color: #ffffff; text-decoration: none; border-radius: 5px; margin-top: 20px; font-weight: bold; }
              </style>
            </head>
            <body>
              <div class="container">
                <h2>Bonjour ' . htmlspecialchars($prenom) . ' ' . htmlspecialchars($nom) . ',</h2>
                <p>Nous vous confirmons que votre inscription à la formation <strong>' . htmlspecialchars($titreFormation) . '</strong> a bien été validée avec succès.</p>
                <p>Nous sommes ravis de vous compter parmi nos apprenants et nous avons hâte de vous accompagner dans le développement de vos compétences.</p>
                <p>Si vous avez des questions, n\'hésitez pas à nous contacter.</p>
                <a href="http://localhost/amen/view/frontoffice.php" class="btn">Retour à JobyFind</a>
                <div class="footer">
                  Ceci est un e-mail automatique généré par JobyFind.
                </div>
              </div>
            </body>
            </html>
            ';

            $mail->Body = $message;

            $mail->send();
            return true;
        } catch (Exception $e) {
            // En cas d'erreur (mot de passe invalide, etc), on retourne false
            // error_log("Le message n'a pas pu être envoyé. Erreur Mailer: {$mail->ErrorInfo}");
            return false;
        }
    }
}
?>
