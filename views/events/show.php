<?php 
/** @var Event $event */ 
require_once __DIR__ . '/../../config/QRCode.php';
$qrUrl = QRCode::generateForEvent($event);
$registrationQrUrl = QRCode::generateForRegistration($event);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($event->getTitre(), ENT_QUOTES, 'UTF-8'); ?></title>
    <style>
        body { font-family: Arial, sans-serif; background: #f8fafc; color: #1f2937; margin: 0; padding: 32px; }
        .card { max-width: 900px; margin: 0 auto; background: #fff; border-radius: 16px; padding: 32px; box-shadow: 0 14px 30px rgba(15, 23, 42, 0.08); }
        .meta { color: #64748b; margin-bottom: 18px; }
        .actions { margin-top: 24px; display: flex; gap: 12px; flex-wrap: wrap; }
        a { text-decoration: none; color: #2563eb; font-weight: 600; }
        .btn { background: #2563eb; color: #fff; padding: 10px 16px; border-radius: 8px; display: inline-block; }
        .btn:hover { background: #1d4ed8; }
        .qr-section { margin-top: 32px; padding-top: 32px; border-top: 2px solid #e2e8f0; }
        .qr-container { display: grid; grid-template-columns: 1fr 1fr; gap: 32px; margin-top: 16px; }
        .qr-box { text-align: center; padding: 20px; background: #f8fafc; border-radius: 12px; border: 1px solid #e2e8f0; }
        .qr-box h3 { margin: 0 0 12px 0; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; }
        .qr-box img { max-width: 250px; width: 100%; height: auto; border: 2px solid #ddd; border-radius: 8px; padding: 8px; background: white; }
        .qr-download { margin-top: 12px; }
        .qr-download a { font-size: 12px; padding: 6px 12px; background: #10b981; color: white; }
        .qr-download a:hover { background: #059669; }
        @media (max-width: 768px) {
            .qr-container { grid-template-columns: 1fr; }
            .qr-box img { max-width: 200px; }
        }
    </style>
</head>
<body>
    <div class="card">
        <p><a href="<?php echo htmlspecialchars($url('/events'), ENT_QUOTES, 'UTF-8'); ?>">← Retour</a></p>
        <h1><?php echo htmlspecialchars($event->getTitre(), ENT_QUOTES, 'UTF-8'); ?></h1>
        <p class="meta">Date: <?php echo htmlspecialchars($event->getDate(), ENT_QUOTES, 'UTF-8'); ?> | Lieu: <?php echo htmlspecialchars($event->getLieu(), ENT_QUOTES, 'UTF-8'); ?></p>
        <p><?php echo nl2br(htmlspecialchars($event->getDescription(), ENT_QUOTES, 'UTF-8')); ?></p>
        
        <div class="actions">
            <a class="btn" href="<?php echo htmlspecialchars($url('/events/register/' . urlencode((string) $event->getId())), ENT_QUOTES, 'UTF-8'); ?>">S'inscrire</a>
        </div>

        <!-- Section Codes QR -->
        <div class="qr-section">
            <h2 style="margin-top: 0; color: #1f2937;">📱 Codes QR de l'Événement</h2>
            <p style="color: #64748b; font-size: 14px;">Partagez ces codes QR pour promouvoir l'événement et faciliter l'accès :</p>
            
            <div class="qr-container">
                <!-- QR pour voir l'événement -->
                <div class="qr-box">
                    <h3>Détails de l'Événement</h3>
                    <img src="<?php echo htmlspecialchars($qrUrl, ENT_QUOTES, 'UTF-8'); ?>" alt="QR Code - Détails événement" />
                    <p style="font-size: 12px; color: #64748b; margin: 12px 0 0 0;">Scannez pour voir les détails complets</p>
                </div>

                <!-- QR pour l'inscription -->
                <div class="qr-box">
                    <h3>Inscription à l'Événement</h3>
                    <img src="<?php echo htmlspecialchars($registrationQrUrl, ENT_QUOTES, 'UTF-8'); ?>" alt="QR Code - Inscription" />
                    <p style="font-size: 12px; color: #64748b; margin: 12px 0 0 0;">Scannez pour s'inscrire directement</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
