<?php
/**
 * sistrix.php — Sistrix API Proxy
 *
 * Alle Anfragen laufen serverseitig durch diesen Proxy.
 * Der API-Key wird niemals an den Browser übertragen.
 *
 * Actions (via ?action=...):
 *   test         GET  → Verbindungstest (credits)
 *   url_data     POST → Visibility + Top-Keywords für eine URL via domain.overview + keyword.domain.seo { url, csrf_token }
 */

session_start();
if (empty($_SESSION['logged_in'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Nicht authentifiziert']);
    exit;
}

header('Content-Type: application/json; charset=utf-8');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

require_once __DIR__ . '/config.php';

$apiKey = CFG_SISTRIX_KEY;
if (empty($apiKey)) {
    http_response_code(503);
    echo json_encode(['success' => false, 'error' => 'Kein Sistrix API-Key konfiguriert. Bitte in den Einstellungen hinterlegen.']);
    exit;
}

$action = $_GET['action'] ?? '';

/**
 * Führt einen GET-Request gegen die Sistrix API aus.
 * Fügt automatisch api_key, format=json und country=de hinzu.
 */
function sistrixGet(string $endpoint, array $params, string $apiKey): array {
    $params['api_key'] = $apiKey;
    $params['format']  = 'json';
    $params['country'] = 'de';

    $url = 'https://api.sistrix.com/' . ltrim($endpoint, '/') . '?' . http_build_query($params);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_USERAGENT      => 'LAT/2.0 (+https://github.com/julingn/LAT-Landingpage-Analyse-Tool)',
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS      => 3,
    ]);

    $resp = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    curl_close($ch);

    if ($err) {
        return ['_error' => 'Netzwerkfehler: ' . $err];
    }

    $decoded = json_decode($resp, true);
    if (!is_array($decoded)) {
        return ['_error' => 'Ungültige Antwort von Sistrix (HTTP ' . $httpCode . ')'];
    }

    // Sistrix API-Fehler abfangen
    $firstAnswer = $decoded['answer'][0] ?? [];
    if (isset($firstAnswer['error'])) {
        $msg = $firstAnswer['error'][0]['@message'] ?? 'Unbekannter Sistrix API-Fehler';
        return ['_error' => $msg];
    }

    return $decoded;
}

// ── GET: Verbindungstest ─────────────────────────────────────────────────────

if ($action === 'test') {
    $result = sistrixGet('credits', [], $apiKey);
    if (isset($result['_error'])) {
        echo json_encode(['success' => false, 'error' => $result['_error']]);
        exit;
    }
    $credits = $result['answer'][0]['credits'][0] ?? [];
    echo json_encode([
        'success'   => true,
        'remaining' => $credits['@remaining'] ?? null,
        'used'      => $credits['@used']      ?? null,
    ]);
    exit;
}

// ── POST: URL-Daten (Overview + Keywords) ────────────────────────────────────

if ($action === 'url_data') {
    // CSRF prüfen
    $body  = json_decode(file_get_contents('php://input'), true) ?? [];
    $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($body['csrf_token'] ?? '');
    if ($token !== ($_SESSION['csrf_token'] ?? '')) {
        http_response_code(403);
        echo json_encode(['error' => 'CSRF-Token ungültig']);
        exit;
    }

    $url = trim($body['url'] ?? '');
    if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
        http_response_code(400);
        echo json_encode(['error' => 'Gültige URL erforderlich']);
        exit;
    }

    // Beide Endpunkte parallel über cURL Multi fetchen
    $handles = [];
    $multi   = curl_multi_init();
    $base    = ['api_key' => $apiKey, 'format' => 'json', 'country' => 'de', 'url' => $url];

    foreach (['domain.overview' => [], 'keyword.domain.seo' => ['limit' => 20]] as $ep => $extra) {
        $params = array_merge($base, $extra);
        $epUrl  = 'https://api.sistrix.com/' . $ep . '?' . http_build_query($params);
        $ch     = curl_init($epUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_USERAGENT      => 'LAT/2.0',
            CURLOPT_FOLLOWLOCATION => true,
        ]);
        curl_multi_add_handle($multi, $ch);
        $handles[$ep] = $ch;
    }

    // Ausführen
    $running = null;
    do { curl_multi_exec($multi, $running); } while ($running > 0);

    $raw = [];
    foreach ($handles as $ep => $ch) {
        $resp = curl_multi_getcontent($ch);
        curl_multi_remove_handle($multi, $ch);
        curl_close($ch);
        $raw[$ep] = json_decode($resp ?: '{}', true) ?? [];
    }
    curl_multi_close($multi);

    // ── domain.overview parsen ──
    $overview   = $raw['domain.overview']['answer'][0]['domain.overview'][0] ?? null;
    $visibility = null;
    $kwCount    = null;
    if ($overview) {
        $visTxt     = $overview['sichtbarkeitsindex'][0]['#text'] ?? null;
        $visibility = $visTxt !== null ? round((float)$visTxt, 4) : null;
        $kwTxt      = $overview['kwcount.seo'][0]['#text'] ?? null;
        $kwCount    = $kwTxt !== null ? (int)$kwTxt : null;
    }

    // ── keyword.domain.seo parsen ──
    $kwItems  = $raw['keyword.domain.seo']['answer'][0]['keyword.domain.seo'] ?? [];
    $keywords = [];
    if (is_array($kwItems)) {
        foreach (array_slice($kwItems, 0, 20) as $kw) {
            if (!is_array($kw)) continue;
            $keywords[] = [
                'keyword'  => (string)($kw['@kw']       ?? ''),
                'position' => (int)($kw['@position']    ?? 0),
                'volume'   => (int)($kw['@traffic']     ?? 0),
            ];
        }
    }

    echo json_encode([
        'success'    => true,
        'visibility' => $visibility,
        'kw_count'   => $kwCount,
        'keywords'   => $keywords,
        'no_data'    => ($visibility === null && empty($keywords)),
    ]);
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'Unbekannte Action: ' . htmlspecialchars($action, ENT_QUOTES)]);
