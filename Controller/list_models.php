<?php
require_once __DIR__ . '/../config_env.php';
$apiKey = $_ENV['GEMINI_API_KEY'] ?? '';

header('Content-Type: text/plain');
echo "Checking available models for API Key: " . substr($apiKey, 0, 8) . "...\n\n";

$url = "https://generativelanguage.googleapis.com/v1beta/models?key=$apiKey";

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => false,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
if ($httpCode !== 200) {
    echo "Error: $response\n";
} else {
    $data = json_decode($response, true);
    foreach ($data['models'] as $model) {
        echo "- " . $model['name'] . " (" . implode(', ', $model['supportedGenerationMethods']) . ")\n";
    }
}
?>
