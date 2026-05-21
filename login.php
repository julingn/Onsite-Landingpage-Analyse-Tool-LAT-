<?php
session_start();

$settingsFile = __DIR__ . '/app/settings.json';
$settings = [];
if (file_exists($settingsFile)) {
    $raw = file_get_contents($settingsFile);
    if ($raw !== false) {
        $settings = json_decode($raw, true) ?? [];
    }
}

// Already logged in → redirect
if (!empty($_SESSION['logged_in'])) {
    header('Location: app/index.php');
    exit;
}

// Logout
if (isset($_GET['logout'])) {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $p['path'], $p['domain'], $p['secure'], true);
    }
    session_destroy();
    header('Location: login.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfPost    = $_POST['csrf_token']    ?? '';
    $csrfSession = $_SESSION['csrf_token'] ?? '';

    if (empty($csrfPost) || empty($csrfSession) || !hash_equals($csrfSession, $csrfPost)) {
        $error = 'Sicherheitstoken abgelaufen. Bitte Seite neu laden.';
    } else {
        $password   = $_POST['password']               ?? '';
        $storedHash = $settings['login_password_hash'] ?? '';

        $valid = false;
        if (!empty($storedHash)) {
            $valid = password_verify($password, $storedHash);
        } else {
            $valid = ($password === 'evalupro2025');
        }

        if ($valid) {
            session_regenerate_id(true);
            $_SESSION['logged_in']  = true;
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            header('Location: app/index.php');
            exit;
        } else {
            $error = 'Falsches Passwort. Bitte erneut versuchen.';
            sleep(1); // throttle brute-force
        }
    }
}

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf     = htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8');
$errorHtml = $error ? htmlspecialchars($error, ENT_QUOTES, 'UTF-8') : '';
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>evalu-pro · Login</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..60,400;12..60,600;12..60,700&family=DM+Sans:ital,wght@0,400;0,500;0,600;1,400&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
  <style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
    :root{
      --bg:#f8f7f5;--bg2:#ffffff;--bg3:#f2f1ef;
      --border:#e3e2df;--border2:#d0ceca;
      --text:#1a1917;--text2:#4a4845;--text3:#908d8a;
      --accent:#4338ca;--accent2:#3730a3;
      --accent-bg:rgba(67,56,202,.07);--accent-border:rgba(67,56,202,.18);
      --green:#15803d;--green-bg:#f0fdf4;--green-border:#bbf7d0;
      --amber:#b45309;--amber-bg:#fffbeb;--amber-border:#fde68a;
      --red:#dc2626;--red-bg:#fef2f2;--red-border:#fecaca;
      --radius:10px;--radius-lg:14px;
    }
    body{
      font-family:'DM Sans',sans-serif;background:var(--bg);color:var(--text);
      min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;
    }
    .login-wrap{width:100%;max-width:400px;}
    .login-card{
      background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius-lg);
      padding:40px 36px;box-shadow:0 4px 24px rgba(26,25,23,.09);
    }
    .brand{display:flex;align-items:center;gap:12px;margin-bottom:6px;}
    .brand-icon{
      width:44px;height:44px;border-radius:10px;flex-shrink:0;
      background:linear-gradient(135deg,var(--accent),var(--accent2));
      display:flex;align-items:center;justify-content:center;
    }
    .brand-name{
      font-family:'Bricolage Grotesque',sans-serif;font-size:22px;font-weight:700;
      color:var(--text);letter-spacing:-.4px;
    }
    .brand-sub{
      font-size:13px;color:var(--text3);margin-bottom:32px;padding-left:56px;
    }
    label{
      display:block;font-size:13px;font-weight:600;color:var(--text2);margin-bottom:6px;
    }
    .input-wrap{position:relative;margin-bottom:20px;}
    .pw-input{
      width:100%;height:46px;padding:0 46px 0 14px;
      border:1.5px solid var(--border2);border-radius:var(--radius);
      font-family:'DM Mono',monospace;font-size:14px;color:var(--text);
      background:var(--bg2);outline:none;transition:border-color .15s;
    }
    .pw-input:focus{border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-bg);}
    .toggle-pw{
      position:absolute;right:0;top:0;height:46px;width:46px;
      border:none;background:none;cursor:pointer;color:var(--text3);
      display:flex;align-items:center;justify-content:center;
    }
    .toggle-pw:hover{color:var(--text2);}
    .btn-login{
      width:100%;height:46px;background:var(--accent);color:#fff;border:none;
      border-radius:var(--radius);font-family:'DM Sans',sans-serif;font-size:15px;font-weight:600;
      cursor:pointer;transition:background .15s,transform .1s;
      box-shadow:0 2px 12px rgba(67,56,202,.3);
      display:flex;align-items:center;justify-content:center;gap:8px;
    }
    .btn-login:hover{background:var(--accent2);transform:translateY(-1px);}
    .btn-login:active{transform:translateY(0);}
    .err-msg{
      margin-top:16px;padding:10px 14px;background:var(--red-bg);
      border:1px solid var(--red-border);border-radius:var(--radius);
      color:var(--red);font-size:13px;display:flex;align-items:center;gap:8px;
    }
  </style>
</head>
<body>
  <div class="login-wrap">
    <div class="login-card">
      <div class="brand">
        <div class="brand-icon">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
            <path d="M12 2L21.6 7.5V16.5L12 22L2.4 16.5V7.5L12 2Z" stroke="#fff" stroke-width="1.8" stroke-linejoin="round"/>
            <path d="M12 2V22M2.4 7.5L21.6 16.5M21.6 7.5L2.4 16.5" stroke="#fff" stroke-width="1.2" stroke-opacity=".4"/>
          </svg>
        </div>
        <span class="brand-name">evalu-pro</span>
      </div>
      <div class="brand-sub">Internes SEO-Tool</div>

      <form method="POST" action="" autocomplete="on">
        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
        <label for="password">Passwort</label>
        <div class="input-wrap">
          <input
            class="pw-input" type="password" id="password" name="password"
            autocomplete="current-password" autofocus required
            placeholder="Passwort eingeben"
          >
          <button type="button" class="toggle-pw" onclick="togglePw()" aria-label="Passwort anzeigen/verbergen">
            <svg id="eye-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
              <circle cx="12" cy="12" r="3"/>
            </svg>
          </button>
        </div>
        <button type="submit" class="btn-login">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <path d="M5 12h14M12 5l7 7-7 7"/>
          </svg>
          Anmelden
        </button>
        <?php if ($errorHtml): ?>
        <div class="err-msg">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"/>
            <line x1="12" y1="8" x2="12" y2="12"/>
            <line x1="12" y1="16" x2="12.01" y2="16"/>
          </svg>
          <?= $errorHtml ?>
        </div>
        <?php endif; ?>
      </form>
    </div>
  </div>
  <script>
    function togglePw() {
      var inp = document.getElementById('password');
      var icon = document.getElementById('eye-icon');
      if (inp.type === 'password') {
        inp.type = 'text';
        icon.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>';
      } else {
        inp.type = 'password';
        icon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
      }
    }
  </script>
</body>
</html>
