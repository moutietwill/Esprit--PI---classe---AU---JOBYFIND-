<?php
/**
 * API endpoint that proxies description generation requests to Google Gemini.
 * Receives: titre, categorie, duree, cout via POST (JSON body)
 * Returns:  JSON { "description": "..." } or { "error": "..." }
 * 
 * Uses fallback models if the primary one hits rate limits.
 */

header('Content-Type: application/json; charset=utf-8');

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Méthode non autorisée']);
    exit;
}

// Read JSON body
$input = json_decode(file_get_contents('php://input'), true);

$titre     = trim($input['titre']     ?? '');
$categorie = trim($input['categorie'] ?? '');
$duree     = trim($input['duree']     ?? '');
$cout      = trim($input['cout']      ?? '');

// Validate required fields
if ($titre === '' || $categorie === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Le titre et la catégorie sont obligatoires.']);
    exit;
}

// ── Build the prompt ────────────────────────────────────────────────
$prompt  = "Tu es un rédacteur professionnel spécialisé dans la formation. ";
$prompt .= "Rédige une description professionnelle et attrayante en français (3 à 4 phrases) pour une formation avec les informations suivantes :\n\n";
$prompt .= "- Titre : $titre\n";
$prompt .= "- Catégorie : $categorie\n";
if ($duree !== '') {
    $prompt .= "- Durée : $duree\n";
}
if ($cout !== '') {
    $prompt .= "- Coût : {$cout} €\n";
}
$prompt .= "\nLa description doit inclure : les objectifs de la formation, le public cible, et les bénéfices concrets pour les participants. ";
$prompt .= "Ne mets pas de titre ni de bullet points, juste un paragraphe fluide et professionnel. ";
$prompt .= "Réponds UNIQUEMENT avec la description, sans aucune introduction ni commentaire.";

// Charger les variables d'environnement
require_once __DIR__ . '/../config_env.php';

// ── API config ──────────────────────────────────────────────────────
$apiKey = $_ENV['GEMINI_API_KEY'] ?? '';

$models = [
    'gemini-2.5-flash',
    'gemini-2.0-flash',
];

$payload = json_encode([
    'contents' => [
        [
            'parts' => [
                ['text' => $prompt]
            ]
        ]
    ],
    'generationConfig' => [
        'temperature'     => 0.7,
        'maxOutputTokens' => 2048
    ]
]);

$lastError = '';

foreach ($models as $model) {
    $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_SSL_VERIFYPEER => false,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    if ($curlErr) {
        $lastError = "Erreur réseau : $curlErr";
        continue; // try next model
    }

    $data = json_decode($response, true);

    // Rate limited — try next model
    if ($httpCode === 429) {
        $lastError = "Quota dépassé pour $model, tentative suivante...";
        sleep(1); // brief pause before trying next
        continue;
    }

    // Other API error
    if ($httpCode !== 200) {
        $lastError = $data['error']['message'] ?? "Erreur API ($httpCode)";
        continue;
    }

    // Success — extract text
    if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
        $description = trim($data['candidates'][0]['content']['parts'][0]['text']);
        echo json_encode(['description' => $description]);
        exit;
    }

    $lastError = "Réponse inattendue de l'API.";
}

// All models failed
http_response_code(502);
echo json_encode(['error' => $lastError ?: 'Tous les modèles sont indisponibles. Réessayez dans quelques minutes.']);
