<?php
session_start();
if (empty($_SESSION['logged_in'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Nicht authentifiziert']);
    exit;
}

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// CSRF check
$csrfToken = $_POST['csrf_token'] ?? '';
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrfToken)) {
    http_response_code(403);
    echo json_encode(['error' => 'CSRF-Validierung fehlgeschlagen']);
    exit;
}

$sf = __DIR__ . '/settings.json';
$settings = [];
if (file_exists($sf)) {
    $settings = json_decode(file_get_contents($sf), true) ?? [];
}

$action = $_POST['action'] ?? '';
$errors = [];

if ($action === 'save_api_key') {
    $key = trim($_POST['anthropic_api_key'] ?? '');
    if (!empty($key) && !str_starts_with($key, 'sk-ant-')) {
        $errors[] = 'API-Key muss mit "sk-ant-" beginnen.';
    } else {
        $settings['anthropic_api_key'] = $key;
    }
}

if ($action === 'save_password') {
    $pw  = $_POST['new_password'] ?? '';
    $pw2 = $_POST['confirm_password'] ?? '';

    if (strlen($pw) < 8) {
        $errors[] = 'Passwort muss mindestens 8 Zeichen lang sein.';
    } elseif ($pw !== $pw2) {
        $errors[] = 'Passwörter stimmen nicht überein.';
    } else {
        $settings['login_password_hash'] = password_hash($pw, PASSWORD_DEFAULT);
    }
}

if ($action === 'save_model') {
    $allowed = ['claude-sonnet-4-5', 'claude-opus-4-5', 'claude-haiku-4-5', 'claude-opus-4-0', 'claude-sonnet-4-0'];
    $model = $_POST['ai_model'] ?? 'claude-sonnet-4-5';
    if (in_array($model, $allowed, true)) {
        $settings['ai_model'] = $model;
    }
}

if (!empty($errors)) {
    http_response_code(422);
    echo json_encode(['error' => implode(' ', $errors)]);
    exit;
}

// Write settings
$written = file_put_contents($sf, json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
if ($written === false) {
    http_response_code(500);
    echo json_encode(['error' => 'Einstellungen konnten nicht gespeichert werden. Schreibrechte prüfen.']);
    exit;
}

echo json_encode(['success' => true]);
