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
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Session guard — only allow authenticated requests
session_start();
if (empty($_SESSION['logged_in'])) {
    http_response_code(401);
    echo json_encode(['error' => ['type' => 'unauthorized', 'message' => 'Nicht authentifiziert.']]);
    exit;
}

// Load settings
$settings = [];
$sf = __DIR__ . '/settings.json';
if (file_exists($sf)) {
    $settings = json_decode(file_get_contents($sf), true) ?? [];
}

$apiKey = getenv('ANTHROPIC_API_KEY') ?: ($settings['anthropic_api_key'] ?? '');
if (empty($apiKey)) {
    http_response_code(503);
    echo json_encode(['error' => ['type' => 'no_key', 'message' => 'Kein Anthropic API-Key hinterlegt. Bitte unter Einstellungen ergänzen.']]);
    exit;
}

$raw = file_get_contents('php://input');
if (empty($raw)) {
    http_response_code(400);
    echo json_encode(['error' => 'Leerer Request-Body']);
    exit;
}

$body = json_decode($raw, true);
if (!$body || empty($body['messages'])) {
    http_response_code(400);
    echo json_encode(['error' => 'messages fehlt oder ungültiges JSON']);
    exit;
}

// Use model from settings or default
$model = $settings['ai_model'] ?? 'claude-sonnet-4-5';

// Build payload — allow caller to override model, but enforce max_tokens cap
$payload = [
    'model'      => $body['model'] ?? $model,
    'max_tokens' => min((int)($body['max_tokens'] ?? 2000), 4096),
    'messages'   => $body['messages'],
];
if (!empty($body['system'])) {
    $payload['system'] = $body['system'];
}

$ch = curl_init('https://api.anthropic.com/v1/messages');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => json_encode($payload),
    CURLOPT_TIMEOUT        => 180,
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'x-api-key: ' . $apiKey,
        'anthropic-version: 2023-06-01',
    ],
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$err      = curl_error($ch);
curl_close($ch);

if ($err) {
    http_response_code(502);
    echo json_encode(['error' => ['type' => 'curl_error', 'message' => $err]]);
    exit;
}

http_response_code($httpCode);
echo $response;
