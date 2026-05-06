<?php
/**
 * TwilioSMS — Helper d'envoi de SMS via l'API Twilio
 * 
 * Utilisation :
 *   require_once 'lib/TwilioSMS.php';
 *   $result = TwilioSMS::send('+21612345678', 'Votre message ici');
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Twilio\Rest\Client;

class TwilioSMS {

    /**
     * Envoie un SMS via Twilio
     *
     * @param string $to      Numéro destinataire (format international, ex: +216XXXXXXXX)
     * @param string $message Contenu du SMS
     * @return array           ['success' => bool, 'sid' => string|null, 'error' => string|null]
     */
    public static function send($to, $message) {
        try {
            // Charger la config
            $config = require __DIR__ . '/../config/twilio.php';

            $sid    = $config['account_sid'];
            $token  = $config['auth_token'];
            $from   = $config['from_number'];

            // Vérifier que les identifiants sont configurés
            if (strpos($sid, 'xxxxxxxxx') !== false || strpos($token, 'xxxxxxxxx') !== false) {
                self::log('SMS non envoyé: identifiants Twilio non configurés');
                return [
                    'success' => false,
                    'sid'     => null,
                    'error'   => 'Twilio non configuré.'
                ];
            }

            // Normaliser le numéro (ajouter + si absent)
            $to = self::normalizePhone($to);

            // Créer le client Twilio et envoyer
            $client = new Client($sid, $token);
            $msg = $client->messages->create(
                $to,
                [
                    'from' => $from,
                    'body' => $message
                ]
            );

            self::log("SMS envoyé avec succès à $to. SID: " . $msg->sid);

            return [
                'success' => true,
                'sid'     => $msg->sid,
                'error'   => null
            ];

        } catch (\Twilio\Exceptions\RestException $e) {
            self::log("Erreur Twilio Rest: " . $e->getMessage());
            return [
                'success' => false,
                'sid'     => null,
                'error'   => $e->getMessage()
            ];
        } catch (\Exception $e) {
            self::log("Erreur SMS Générale: " . $e->getMessage());
            return [
                'success' => false,
                'sid'     => null,
                'error'   => $e->getMessage()
            ];
        }
    }

    private static function log($message) {
        $file = __DIR__ . '/../sms_errors.log';
        $time = date('Y-m-d H:i:s');
        file_put_contents($file, "[$time] $message" . PHP_EOL, FILE_APPEND);
    }

    /**
     * Génère le message SMS de confirmation de candidature
     *
     * @param string $prenom Prénom du candidat
     * @param string $nom    Nom du candidat
     * @param string $titre  Titre de l'offre
     * @return string         Message formaté
     */
    public static function buildCandidatureMessage($prenom, $nom, $titre) {
        $config = require __DIR__ . '/../config/twilio.php';
        return sprintf($config['sms_template'], $prenom, $nom, $titre);
    }

    /**
     * Normalise un numéro de téléphone au format international
     */
    private static function normalizePhone($phone) {
        // Supprimer espaces, tirets, parenthèses
        $phone = preg_replace('/[\s\-\(\)]/', '', $phone);

        // Si commence par 00, remplacer par +
        if (substr($phone, 0, 2) === '00') {
            $phone = '+' . substr($phone, 2);
        }

        // Si pas de +, ajouter le préfixe Tunisie par défaut
        if (substr($phone, 0, 1) !== '+') {
            $phone = '+216' . $phone;
        }

        return $phone;
    }
}
?>
