<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['logged_in'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Nicht autorisiert.']);
    exit;
}

$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!$data || empty($data['action'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Ungültige Anfrage.']);
    exit;
}

$settingsFile = __DIR__ . '/settings.json';
$settings = [];
if (file_exists($settingsFile)) {
    $raw2 = file_get_contents($settingsFile);
    if ($raw2 !== false) {
        $settings = json_decode($raw2, true) ?? [];
    }
}

function saveSettings(string $file, array $data): bool {
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    return file_put_contents($file, $json) !== false;
}

$action = $data['action'];

switch ($action) {
    // ── Save Anthropic API Key ──────────────────────────────────────────────
    case 'save_api_key':
        $key = trim($data['key'] ?? '');
        if (empty($key)) {
            echo json_encode(['error' => 'API-Key darf nicht leer sein.']);
            exit;
        }
        if (!str_starts_with($key, 'sk-ant-')) {
            echo json_encode(['error' => 'Ungültiger API-Key. Muss mit "sk-ant-" beginnen.']);
            exit;
        }
        if (strlen($key) < 20) {
            echo json_encode(['error' => 'API-Key scheint ungültig (zu kurz).']);
            exit;
        }
        $settings['anthropic_api_key'] = $key;
        if (!saveSettings($settingsFile, $settings)) {
            http_response_code(500);
            echo json_encode(['error' => 'Fehler beim Speichern. Prüfen Sie Datei-Schreibrechte.']);
            exit;
        }
        echo json_encode(['success' => true, 'message' => 'API-Key erfolgreich gespeichert.']);
        break;

    // ── Change Password ─────────────────────────────────────────────────────
    case 'change_password':
        $newPw   = $data['password'] ?? '';
        $confirm = $data['confirm']  ?? '';

        if (strlen($newPw) < 8) {
            echo json_encode(['error' => 'Passwort muss mindestens 8 Zeichen lang sein.']);
            exit;
        }
        if ($newPw !== $confirm) {
            echo json_encode(['error' => 'Passwörter stimmen nicht überein.']);
            exit;
        }
        $settings['login_password_hash'] = password_hash($newPw, PASSWORD_DEFAULT);
        if (!saveSettings($settingsFile, $settings)) {
            http_response_code(500);
            echo json_encode(['error' => 'Fehler beim Speichern. Prüfen Sie Datei-Schreibrechte.']);
            exit;
        }
        echo json_encode(['success' => true, 'message' => 'Passwort erfolgreich geändert.']);
        break;

    // ── Save AI Model ───────────────────────────────────────────────────────
    case 'save_model':
        $allowed = ['claude-sonnet-4-5', 'claude-opus-4-5', 'claude-haiku-4-5',
                    'claude-3-5-sonnet-20241022', 'claude-3-haiku-20240307'];
        $model = $data['model'] ?? '';
        if (!in_array($model, $allowed, true)) {
            echo json_encode(['error' => 'Unbekanntes Modell.']);
            exit;
        }
        $settings['ai_model'] = $model;
        if (!saveSettings($settingsFile, $settings)) {
            http_response_code(500);
            echo json_encode(['error' => 'Fehler beim Speichern.']);
            exit;
        }
        echo json_encode(['success' => true, 'message' => 'Modell gespeichert.']);
        break;

    default:
        http_response_code(400);
        echo json_encode(['error' => 'Unbekannte Aktion: ' . htmlspecialchars($action)]);
}
