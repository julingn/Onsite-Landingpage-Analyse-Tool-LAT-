<?php
/**
 * config.php — Zentrale Credential-Verwaltung für evalu-pro
 *
 * Priorität: Umgebungsvariable (Railway/Server) > settings.json > Default
 *
 * Nutzung in anderen PHP-Dateien:
 *   require_once __DIR__ . '/config.php';   // aus app/
 *   require_once __DIR__ . '/app/config.php'; // aus Root
 *
 *   $key = cfg('ANTHROPIC_API_KEY');
 *   $login = cfg('DATAFORSEO_LOGIN', 'dataforseo_login');
 */

declare(strict_types=1);

/**
 * Lädt eine .env-Datei und setzt Variablen als Prozess-Umgebungsvariablen.
 * Bereits gesetzte Variablen (z.B. Railway) werden NICHT überschrieben.
 *
 * Format: KEY=VALUE, KEY="wert mit leerzeichen", # Kommentare
 * Speicherort: Projekt-Root (.env) — per router.php vor Browser-Zugriff geschützt.
 */
function _cfg_load_dotenv(): void {
    static $loaded = false;
    if ($loaded) return;
    $loaded = true;

    // Projekt-Root ist eine Ebene über app/
    $file = dirname(__DIR__) . '/.env';
    if (!file_exists($file) || !is_readable($file)) return;

    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        // Kommentare und leere Zeilen überspringen
        if ($line === '' || str_starts_with($line, '#')) continue;
        // KEY=VALUE aufteilen (nur am ersten = trennen)
        $eqPos = strpos($line, '=');
        if ($eqPos === false) continue;

        $key   = trim(substr($line, 0, $eqPos));
        $value = trim(substr($line, $eqPos + 1));

        // Anführungszeichen entfernen (einfach und doppelt)
        if (strlen($value) >= 2) {
            $first = $value[0];
            $last  = $value[-1];
            if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                $value = substr($value, 1, -1);
            }
        }

        // Nur setzen wenn noch nicht vorhanden (Railway-Vars haben Vorrang)
        if (getenv($key) === false) {
            putenv($key . '=' . $value);
        }
    }
}

// .env laden (vor allen cfg()-Aufrufen)
_cfg_load_dotenv();

/**
 * Lädt settings.json einmalig (Singleton).
 */
function _cfg_settings(): array {
    static $settings = null;
    if ($settings === null) {
        $file = __DIR__ . '/settings.json';
        $settings = [];
        if (file_exists($file)) {
            $decoded = json_decode(file_get_contents($file), true);
            $settings = is_array($decoded) ? $decoded : [];
        }
    }
    return $settings;
}

/**
 * Liest einen Konfigurationswert.
 *
 * @param string $envKey      Name der Umgebungsvariable (Priorität 1)
 * @param string $settingsKey Key in settings.json (Priorität 2), leer = $envKey in Lowercase
 * @param mixed  $default     Fallback wenn beides fehlt
 */
function cfg(string $envKey, string $settingsKey = '', mixed $default = ''): mixed {
    // 1. Umgebungsvariable (Railway Dashboard → Variables)
    $env = getenv($envKey);
    if ($env !== false && $env !== '') {
        return $env;
    }

    // 2. settings.json
    $key = $settingsKey ?: strtolower($envKey);
    $s = _cfg_settings();
    if (isset($s[$key]) && $s[$key] !== '') {
        return $s[$key];
    }

    return $default;
}

/**
 * Gibt true zurück wenn ein Credential gesetzt ist (Env ODER settings.json).
 */
function cfg_has(string $envKey, string $settingsKey = ''): bool {
    return cfg($envKey, $settingsKey) !== '';
}

// ─── Alle Credentials als benannte Konstanten ─────────────────────────────

// KI-Provider
define('CFG_AI_PROVIDER',         cfg('AI_PROVIDER',         'ai_provider',         'anthropic'));
define('CFG_AI_MODEL',            cfg('AI_MODEL',            'ai_model',            'claude-sonnet-4-5'));
define('CFG_ANTHROPIC_KEY',       cfg('ANTHROPIC_API_KEY',   'anthropic_api_key'));
define('CFG_OPENAI_KEY',          cfg('OPENAI_API_KEY',       'openai_api_key'));
define('CFG_OPENAI_MODEL',        cfg('OPENAI_MODEL',         'openai_model',        'gpt-4.1'));

// Datenquellen
define('CFG_DATAFORSEO_LOGIN',    cfg('DATAFORSEO_LOGIN',    'dataforseo_login'));
define('CFG_DATAFORSEO_PASSWORD', cfg('DATAFORSEO_PASSWORD', 'dataforseo_password'));
define('CFG_PAGESPEED_KEY',       cfg('PAGESPEED_API_KEY',   'pagespeed_api_key'));

// Google Search Console
define('CFG_GSC_SA_JSON',         cfg('GSC_SERVICE_ACCOUNT_JSON', 'gsc_service_account_json'));
define('CFG_GSC_SITE_URL',        cfg('GSC_SITE_URL',        'gsc_site_url'));

// App-Auth (Login-Passwort-Hash)
// Priorität: APP_PASSWORD_HASH (Env) > login_password_hash (settings.json)
define('CFG_PASSWORD_HASH',       cfg('APP_PASSWORD_HASH',   'login_password_hash'));
define('CFG_DEFAULT_PASSWORD',    'evalupro2025');
