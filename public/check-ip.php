<?php
require_once __DIR__ . '/../config/QRCode.php';

$detectedIP = QRCode::getServerIP();
$serverAddr = $_SERVER['SERVER_ADDR'] ?? 'N/A';
$httpHost   = $_SERVER['HTTP_HOST']   ?? 'N/A';
$hostname   = gethostname();
$gethostIP  = gethostbyname($hostname);

// Test ipconfig
$ipconfigOutput = '';
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' && function_exists('shell_exec')) {
    $ipconfigOutput = shell_exec('ipconfig');
}

$testUrl    = QRCode::getEventUrl(1);
$testQR     = QRCode::generate($testUrl, 200);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Diagnostic IP - QR Code</title>
  <style>
    body { font-family: Arial, sans-serif; max-width: 800px; margin: 30px auto; padding: 20px; background: #f5f7fb; }
    .box { background: #fff; border-radius: 10px; padding: 20px; margin-bottom: 20px; border: 1px solid #e2e8f0; }
    .ok  { color: #16a34a; font-weight: bold; }
    .bad { color: #ef4444; font-weight: bold; }
    h1   { color: #0b1f4b; }
    h2   { color: #2d79ff; font-size: 16px; margin-top: 0; }
    pre  { background: #f0f2f8; padding: 10px; border-radius: 6px; font-size: 12px; overflow-x: auto; }
    .ip-big { font-size: 28px; font-weight: bold; color: #2d79ff; }
    .url-box { word-break: break-all; font-family: monospace; font-size: 13px; background: #f0f2f8; padding: 10px; border-radius: 6px; }
    .step { background: #eff6ff; border-left: 4px solid #2d79ff; padding: 12px 16px; border-radius: 0 8px 8px 0; margin: 8px 0; }
  </style>
</head>
<body>
  <h1>🔍 Diagnostic IP pour les QR Codes</h1>

  <div class="box">
    <h2>📡 IP détectée automatiquement</h2>
    <div class="ip-big"><?php echo htmlspecialchars($detectedIP); ?></div>
    <?php if (strpos($detectedIP, '192.168') === 0 || strpos($detectedIP, '10.') === 0): ?>
      <p class="ok">✅ Bonne IP locale ! Votre téléphone peut accéder à cette adresse.</p>
    <?php else: ?>
      <p class="bad">❌ IP incorrecte (localhost/127.0.0.1). Configurez manuellement ci-dessous.</p>
    <?php endif; ?>
  </div>

  <div class="box">
    <h2>🔗 URL générée pour le QR Code (événement #1)</h2>
    <div class="url-box"><?php echo htmlspecialchars($testUrl); ?></div>
    <br>
    <img src="<?php echo htmlspecialchars($testQR); ?>" alt="QR Test" style="border:2px solid #e2e8f0; border-radius:8px; padding:8px; background:#fff;">
    <p><strong>Scannez ce QR code avec votre téléphone pour tester.</strong></p>
  </div>

  <div class="box">
    <h2>⚙️ Informations serveur</h2>
    <table style="width:100%; border-collapse:collapse;">
      <tr><td style="padding:6px 0; color:#666;">SERVER_ADDR</td><td><code><?php echo htmlspecialchars($serverAddr); ?></code></td></tr>
      <tr><td style="padding:6px 0; color:#666;">HTTP_HOST</td><td><code><?php echo htmlspecialchars($httpHost); ?></code></td></tr>
      <tr><td style="padding:6px 0; color:#666;">gethostname()</td><td><code><?php echo htmlspecialchars($hostname); ?></code></td></tr>
      <tr><td style="padding:6px 0; color:#666;">gethostbyname()</td><td><code><?php echo htmlspecialchars($gethostIP); ?></code></td></tr>
      <tr><td style="padding:6px 0; color:#666;">PHP_OS</td><td><code><?php echo PHP_OS; ?></code></td></tr>
      <tr><td style="padding:6px 0; color:#666;">shell_exec activé</td><td><code><?php echo function_exists('shell_exec') ? 'Oui' : 'Non'; ?></code></td></tr>
    </table>
  </div>

  <?php if ($ipconfigOutput): ?>
  <div class="box">
    <h2>🖥️ Résultat ipconfig (cherchez votre IP WiFi)</h2>
    <pre><?php echo htmlspecialchars($ipconfigOutput); ?></pre>
  </div>
  <?php endif; ?>

  <div class="box">
    <h2>🛠️ Si l'IP est incorrecte — Configuration manuelle</h2>
    <div class="step">1. Ouvrez <strong>c:\xampp\htdocs\projet\projetweb_avec_evenements_fix\.env</strong></div>
    <div class="step">2. Ajoutez cette ligne avec votre vraie IP :<br><br>
      <code>SERVER_IP=192.168.X.X</code><br><br>
      (remplacez 192.168.X.X par votre IP WiFi visible dans ipconfig ci-dessus)
    </div>
    <div class="step">3. Rafraîchissez cette page pour vérifier</div>
  </div>
</body>
</html>
