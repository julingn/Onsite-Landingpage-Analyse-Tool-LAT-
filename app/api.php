<?php
set_time_limit(0);
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

$settings = [];
$sf = __DIR__ . '/settings.json';
if (file_exists($sf)) {
    $raw = file_get_contents($sf);
    if ($raw !== false) {
        $settings = json_decode($raw, true) ?? [];
    }
}

$apiKey = getenv('ANTHROPIC_API_KEY') ?: ($settings['anthropic_api_key'] ?? '');
if (empty($apiKey)) {
    http_response_code(503);
    echo json_encode([
        'error' => [
            'type'    => 'no_key',
            'message' => 'Kein API-Key hinterlegt. Bitte unter Einstellungen hinterlegen.'
        ]
    ]);
    exit;
}

$raw  = file_get_contents('php://input');
$body = json_decode($raw, true);

if (!$body || empty($body['messages'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Feld "messages" fehlt oder ungültiges JSON.']);
    exit;
}

// Force model from settings if not provided
if (empty($body['model'])) {
    $body['model'] = $settings['ai_model'] ?? 'claude-sonnet-4-5';
}
if (empty($body['max_tokens'])) {
    $body['max_tokens'] = 2000;
}

$payload = json_encode($body);

$ch = curl_init('https://api.anthropic.com/v1/messages');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $payload,
    CURLOPT_TIMEOUT        => 180,
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'x-api-key: ' . $apiKey,
        'anthropic-version: 2023-06-01',
    ],
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr  = curl_error($ch);
curl_close($ch);

if ($curlErr) {
    http_response_code(502);
    echo json_encode(['error' => ['type' => 'curl_error', 'message' => $curlErr]]);
    exit;
}

http_response_code($httpCode);
echo $response;
