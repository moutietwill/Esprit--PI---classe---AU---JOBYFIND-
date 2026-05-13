<?php
require_once __DIR__ . '/../config/Mailer.php';

class FormationMailer {
    public static function sendConfirmationEmail($destinataire, $nom, $prenom, $titreFormation) {
        $subject = "Confirmation d'inscription : " . $titreFormation;
        
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $domain = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $url = $protocol . $domain . "/JobyFind/view/frontoffice.php";

        $body = '
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
            <a href="' . $url . '" class="btn">Retour à JobyFind</a>
            <div class="footer">
              Ceci est un e-mail automatique généré par JobyFind.
            </div>
          </div>
        </body>
        </html>
        ';

        $mailer = new Mailer();
        return $mailer->send($destinataire, $subject, $body);
    }
}
?>
