<?php
require_once __DIR__ . '/../config_env.php';
$apiKey = $_ENV['GEMINI_API_KEY'] ?? $_SERVER['GEMINI_API_KEY'] ?? '';

if (empty($apiKey) || $apiKey === 'YOUR_GEMINI_API_KEY_HERE') {
    if (file_exists(__DIR__ . '/../.env')) {
        $lines = file(__DIR__ . '/../.env');
        foreach ($lines as $line) {
            if (strpos($line, 'GEMINI_API_KEY=') === 0) {
                $apiKey = trim(str_replace('GEMINI_API_KEY=', '', $line));
                break;
            }
        }
    }
}
$apiKey = trim($apiKey);

header('Content-Type: text/html; charset=utf-8');
echo "<h2>🔍 Diagnostic Gemini API</h2>";
echo "Clé API : " . substr($apiKey, 0, 8) . "..." . substr($apiKey, -4) . "<br><br>";

$models = [
    'v1/gemini-1.5-flash',
    'v1beta/gemini-1.5-flash',
    'v1beta/gemini-2.0-flash-exp',
    'v1beta/gemini-pro',
    'v1/gemini-pro'
];

foreach ($models as $m) {
    list($ver, $model) = explode('/', $m);
    echo "Test de <strong>$model</strong> ($ver)... ";
    
    $url = "https://generativelanguage.googleapis.com/$ver/models/$model:generateContent?key=$apiKey";
    $payload = json_encode(['contents' => [['parts' => [['text' => 'Hi']]]]]);
    
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_SSL_VERIFYPEER => false,
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        echo "<span style='color:green; font-weight:bold;'>✅ SUCCÈS !</span><br>";
    } else {
        $data = json_decode($response, true);
        $err = $data['error']['message'] ?? "Erreur $httpCode";
        echo "<span style='color:red;'>❌ ÉCHEC ($err)</span><br>";
    }
}

echo "<br><strong>S'ils échouent tous avec 'API Key not valid', vérifiez que l'API 'Generative Language API' est bien activée sur votre console Google Cloud.</strong>";
?>
