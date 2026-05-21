<?php
header('Content-Type: application/json; charset=utf-8');

$url = $_GET['url'] ?? '';
if (!filter_var($url, FILTER_VALIDATE_URL)) {
    echo json_encode(['error' => 'Ungültige URL.']);
    exit;
}

// Only allow http/https
$scheme = parse_url($url, PHP_URL_SCHEME);
if (!in_array($scheme, ['http', 'https'], true)) {
    echo json_encode(['error' => 'Nur HTTP/HTTPS-URLs erlaubt.']);
    exit;
}

$ctx = stream_context_create([
    'http' => [
        'timeout'      => 20,
        'user_agent'   => 'Mozilla/5.0 (compatible; evalupro-bot/1.0)',
        'follow_location' => 1,
        'max_redirects'   => 5,
    ],
    'ssl' => [
        'verify_peer'      => true,
        'verify_peer_name' => true,
    ],
]);

$html = @file_get_contents($url, false, $ctx);
if ($html === false) {
    echo json_encode(['error' => 'Seite konnte nicht abgerufen werden. Bitte prüfen Sie die URL oder nutzen Sie den HTML-Modus.']);
    exit;
}

echo json_encode([
    'html'   => $html,
    'length' => strlen($html),
    'url'    => $url,
]);
