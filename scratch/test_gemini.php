<?php
require_once __DIR__ . '/config_env.php';
$apiKey = $_ENV['GEMINI_API_KEY'] ?? '';

echo "Testing Gemini API Key: " . substr($apiKey, 0, 8) . "...\n";

$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=$apiKey";

$payload = json_encode([
    'contents' => [['parts' => [['text' => 'Hello, are you working?']]]]
]);

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
    CURLOPT_POSTFIELDS     => $payload,
    CURLOPT_SSL_VERIFYPEER => false,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
echo "Response: $response\n";
?>
