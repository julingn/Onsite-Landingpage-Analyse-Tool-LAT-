<?php
session_start();
if (empty($_SESSION['logged_in'])) { header('Location: ../login.php'); exit; }
if (empty($_SESSION['csrf_token'])) { $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); }
$csrfToken = $_SESSION['csrf_token'];
?><!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>evalu-pro · SQEG Analyzer</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,400;12..96,600;12..96,700&family=DM+Sans:wght@400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
:root {
  --bg:#f8f7f5; --bg2:#ffffff; --bg3:#f2f1ef;
  --border:#e3e2df; --border2:#d0ceca;
  --text:#1a1917; --text2:#4a4845; --text3:#908d8a;
  --accent:#4338ca; --accent2:#3730a3;
  --accent-bg:rgba(67,56,202,.07); --accent-border:rgba(67,56,202,.18);
  --green:#15803d; --green-bg:#f0fdf4; --green-border:#bbf7d0;
  --amber:#b45309; --amber-bg:#fffbeb; --amber-border:#fde68a;
  --red:#dc2626; --red-bg:#fef2f2; --red-border:#fecaca;
  --blue:#1d4ed8; --blue-bg:#eff6ff; --blue-border:#bfdbfe;
  --radius:10px; --radius-lg:14px;
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:smooth}
body{font-family:'DM Sans',sans-serif;background:var(--bg);color:var(--text);line-height:1.5}
a{color:inherit;text-decoration:none}
button{font-family:inherit}
.app-shell{display:flex;min-height:100vh}
.sidebar{
  width:216px;flex-shrink:0;position:fixed;top:0;left:0;bottom:0;z-index:100;
  background:#fff;border-right:1px solid var(--border);
  box-shadow:2px 0 8px rgba(26,25,23,.04);
  display:flex;flex-direction:column;overflow-y:auto;
}
.sidebar-logo{
  padding:0 16px;display:flex;align-items:center;gap:10px;
  border-bottom:1px solid var(--border);height:72px;flex-shrink:0;
}
.sidebar-brand{font-family:'Bricolage Grotesque',sans-serif;font-size:17px;font-weight:700;color:var(--text);letter-spacing:-0.2px}
.sidebar-brand span{color:var(--accent)}
.brand-icon-sm{
  width:34px;height:34px;background:linear-gradient(135deg,var(--accent),var(--accent2));
  border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;
}
.brand-icon-sm svg{color:#fff}
.sidebar-nav{flex:1;padding:12px 8px}
.nav-section-label{
  font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;
  color:var(--text3);padding:8px 10px 4px;
}
.nav-item{
  display:flex;align-items:center;gap:10px;width:100%;
  padding:9px 10px;border:none;border-radius:8px;background:none;
  cursor:pointer;text-align:left;color:var(--text2);margin-bottom:1px;
  transition:background .15s,color .15s;font-family:inherit;font-size:13px;font-weight:600;
}
.nav-item svg{flex-shrink:0;opacity:.7}
.nav-item:hover{background:var(--bg3);color:var(--text)}
.nav-item:hover svg{opacity:1}
.nav-item.active{background:var(--accent-bg);color:var(--accent);font-weight:700}
.nav-item.active svg{opacity:1}
.sidebar-footer{
  padding:12px 16px;border-top:1px solid var(--border);font-size:11px;
  color:var(--text3);display:flex;align-items:center;justify-content:space-between;
}
.sidebar-footer a{color:var(--text3);font-size:11px;transition:color .15s}
.sidebar-footer a:hover{color:var(--red)}
.main-content{margin-left:216px;flex:1;min-width:0}
.container{max-width:900px;margin:0 auto;padding:92px 28px 32px}
.tool-panel{display:none}
.tool-panel.active{display:block}
.section-divider{display:flex;align-items:center;gap:12px;margin:24px 0 16px}
.section-divider-line{flex:1;height:1px;background:var(--border)}
.section-divider-label{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--text3);white-space:nowrap}
.top-bar{
  position:fixed;top:0;left:216px;right:0;z-index:99;
  background:var(--bg2);border-bottom:1px solid var(--border);
  height:72px;padding:0 28px;display:flex;align-items:center;gap:12px;
}
.url-input{
  flex:1;height:44px;padding:0 14px;border:1px solid var(--border2);border-radius:var(--radius);
  background:var(--bg);font-family:'DM Mono',monospace;font-size:13px;
  color:var(--text);outline:none;transition:border-color .15s,box-shadow .15s;min-width:0;
}
.url-input:focus{border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-border);background:#fff}
.mode-toggle{display:flex;border:1px solid var(--border2);border-radius:var(--radius);overflow:hidden;flex-shrink:0;height:44px}
.mode-btn{height:100%;padding:0 14px;border:none;background:var(--bg3);cursor:pointer;font-size:12px;font-weight:600;color:var(--text2);transition:background .15s,color .15s;white-space:nowrap}
.mode-btn.active{background:var(--accent);color:#fff}
.btn-start{
  height:44px;padding:0 22px;background:var(--accent);color:#fff;
  border:none;border-radius:var(--radius);font-size:14px;font-weight:600;
  cursor:pointer;transition:all .2s;font-family:'DM Sans',sans-serif;
  box-shadow:0 2px 8px rgba(67,56,202,.25);flex-shrink:0;
  display:flex;align-items:center;gap:7px;white-space:nowrap;
}
.btn-start:hover{background:var(--accent2);transform:translateY(-1px);box-shadow:0 4px 12px rgba(67,56,202,.35)}
.btn-start:disabled{background:var(--text3);box-shadow:none;transform:none;cursor:not-allowed}
.html-area-wrap{
  display:none;position:fixed;left:216px;right:0;z-index:98;
  top:72px;background:var(--bg2);border-bottom:1px solid var(--border);padding:12px 28px;
}
.html-area-wrap.visible{display:block}
.html-textarea{
  width:100%;height:120px;padding:10px 14px;border:1px solid var(--border2);border-radius:var(--radius);
  background:var(--bg);font-family:'DM Mono',monospace;font-size:12px;
  color:var(--text);resize:vertical;outline:none;transition:border-color .15s,box-shadow .15s;
}
.html-textarea:focus{border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-border)}
.context-toggle{
  display:inline-flex;align-items:center;gap:6px;font-size:12px;font-weight:600;
  color:var(--text3);border:none;background:none;cursor:pointer;padding:4px 0;transition:color .15s;
}
.context-toggle:hover{color:var(--accent)}
.context-fields{display:none;gap:12px;margin-top:12px}
.context-fields.visible{display:flex;flex-wrap:wrap}
.ctx-field{display:flex;flex-direction:column;gap:4px;flex:1;min-width:180px}
.ctx-label{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--text3)}
.ctx-input{
  height:36px;padding:0 10px;border:1px solid var(--border2);border-radius:8px;
  background:var(--bg);font-family:'DM Sans',sans-serif;font-size:13px;color:var(--text);outline:none;transition:border-color .15s;
}
.ctx-input:focus{border-color:var(--accent)}
.input-card{
  background:var(--bg2);border:1px solid var(--border);
  border-radius:var(--radius-lg);padding:24px;margin-bottom:20px;
  box-shadow:0 1px 3px rgba(26,25,23,.06);
}
.card-header{display:flex;align-items:center;gap:12px;margin-bottom:16px}
.card-icon{
  width:40px;height:40px;background:var(--accent-bg);border:1px solid var(--accent-border);
  border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;
}
.card-icon svg{color:var(--accent)}
.card-title{font-family:'Bricolage Grotesque',sans-serif;font-size:17px;font-weight:700;color:var(--text)}
.card-sub{font-size:12px;color:var(--text3);margin-top:1px;font-family:'DM Mono',monospace}
.card-actions{margin-left:auto;display:flex;gap:8px;align-items:center}
.url-display{
  font-family:'DM Mono',monospace;font-size:12px;color:var(--accent);
  background:var(--accent-bg);border:1px solid var(--accent-border);
  border-radius:6px;padding:4px 10px;display:none;word-break:break-all;margin-bottom:12px;
}
.btn-secondary{
  height:38px;padding:0 16px;background:var(--bg2);color:var(--text2);
  border:1px solid var(--border2);border-radius:var(--radius);font-size:13px;
  font-weight:600;cursor:pointer;transition:all .15s;font-family:'DM Sans',sans-serif;
  display:flex;align-items:center;gap:6px;
}
.btn-secondary:hover{border-color:var(--accent);color:var(--accent);background:var(--accent-bg)}
.err-box{
  padding:12px 16px;background:var(--red-bg);border:1px solid var(--red-border);
  border-radius:var(--radius);color:var(--red);font-size:13px;
  display:flex;align-items:flex-start;gap:10px;margin-bottom:16px;
}
.progress-section{margin-bottom:20px}
.progress-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:8px}
.progress-label{font-size:13px;font-weight:600;color:var(--text2)}
.progress-pct{font-size:13px;font-weight:700;color:var(--accent);font-family:'DM Mono',monospace}
.progress-bar-bg{height:8px;background:var(--bg3);border-radius:999px;overflow:hidden;margin-bottom:16px}
.progress-bar{
  height:100%;border-radius:999px;width:0%;
  background:linear-gradient(90deg,var(--accent),#818cf8);
  transition:width .3s ease;position:relative;
}
.progress-bar::after{
  content:'';position:absolute;top:0;left:0;right:0;bottom:0;
  background:linear-gradient(90deg,transparent,rgba(255,255,255,.3),transparent);
  animation:shimmer 1.5s infinite;
}
@keyframes shimmer{0%{transform:translateX(-100%)}100%{transform:translateX(100%)}}
.loader-dots{display:flex;gap:5px;align-items:center;margin-bottom:12px}
.loader-dot{width:7px;height:7px;border-radius:50%;background:var(--accent);opacity:.3;animation:dotpulse 1.4s ease-in-out infinite}
.loader-dot:nth-child(2){animation-delay:.2s}
.loader-dot:nth-child(3){animation-delay:.4s}
@keyframes dotpulse{0%,80%,100%{opacity:.3;transform:scale(1)}40%{opacity:1;transform:scale(1.3)}}
.status-msg{font-size:13px;color:var(--text2);margin-bottom:10px}
.log-box{
  background:var(--bg);border:1px solid var(--border);border-radius:var(--radius);
  padding:12px 14px;font-family:'DM Mono',monospace;font-size:11px;color:var(--text3);
  height:160px;overflow-y:auto;line-height:1.6;
}
.log-box .log-ok{color:var(--green)}
.log-box .log-err{color:var(--red)}
.log-box .log-info{color:var(--accent)}
.settings-section{margin-bottom:32px}
.settings-section-title{font-family:'Bricolage Grotesque',sans-serif;font-size:16px;font-weight:700;color:var(--text);margin-bottom:4px}
.settings-section-desc{font-size:13px;color:var(--text3);margin-bottom:16px}
.settings-field{margin-bottom:14px}
.settings-label{display:block;font-size:13px;font-weight:600;color:var(--text2);margin-bottom:6px}
.settings-input{
  width:100%;height:42px;padding:0 14px;border:1px solid var(--border2);
  border-radius:var(--radius);background:var(--bg);font-family:'DM Mono',monospace;
  font-size:13px;color:var(--text);outline:none;transition:border-color .15s,box-shadow .15s;
}
.settings-input:focus{border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-border)}
.settings-input-wrap{position:relative}
.settings-toggle-btn{
  position:absolute;right:10px;top:50%;transform:translateY(-50%);
  background:none;border:none;cursor:pointer;color:var(--text3);
  padding:4px;font-size:11px;font-weight:600;transition:color .15s;
}
.settings-toggle-btn:hover{color:var(--accent)}
.btn-save{
  height:40px;padding:0 20px;background:var(--accent);color:#fff;
  border:none;border-radius:var(--radius);font-size:13px;font-weight:600;
  cursor:pointer;transition:all .2s;font-family:'DM Sans',sans-serif;
  box-shadow:0 2px 6px rgba(67,56,202,.2);
}
.btn-save:hover{background:var(--accent2)}
.success-msg{padding:8px 14px;background:var(--green-bg);border:1px solid var(--green-border);border-radius:var(--radius);color:var(--green);font-size:13px;margin-top:10px;display:none}
.key-masked{font-family:'DM Mono',monospace;font-size:12px;color:var(--text3);margin-bottom:8px}
.results-header{display:flex;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:20px}
.score-badge{display:inline-flex;align-items:center;gap:8px;padding:8px 16px;border-radius:999px;font-weight:700;font-size:15px;font-family:'Bricolage Grotesque',sans-serif}
.score-badge.green{background:var(--green-bg);color:var(--green);border:1px solid var(--green-border)}
.score-badge.amber{background:var(--amber-bg);color:var(--amber);border:1px solid var(--amber-border)}
.score-badge.red{background:var(--red-bg);color:var(--red);border:1px solid var(--red-border)}
.ymyl-badge{padding:5px 12px;border-radius:999px;font-size:12px;font-weight:700}
.ymyl-badge.red{background:var(--red-bg);color:var(--red);border:1px solid var(--red-border)}
.ymyl-badge.amber{background:var(--amber-bg);color:var(--amber);border:1px solid var(--amber-border)}
.ymyl-badge.green{background:var(--green-bg);color:var(--green);border:1px solid var(--green-border)}
.stat-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:20px}
.stat-box{padding:16px;border-radius:var(--radius-lg);border:1px solid;text-align:center}
.stat-box.green{background:var(--green-bg);border-color:var(--green-border)}
.stat-box.amber{background:var(--amber-bg);border-color:var(--amber-border)}
.stat-box.red{background:var(--red-bg);border-color:var(--red-border)}
.stat-box.blue{background:var(--blue-bg);border-color:var(--blue-border)}
.stat-num{font-family:'Bricolage Grotesque',sans-serif;font-size:28px;font-weight:700;line-height:1;margin-bottom:4px}
.stat-box.green .stat-num{color:var(--green)}
.stat-box.amber .stat-num{color:var(--amber)}
.stat-box.red .stat-num{color:var(--red)}
.stat-box.blue .stat-num{color:var(--blue)}
.stat-lbl{font-size:11px;font-weight:600;color:var(--text3)}
.sqeg-scale{display:flex;align-items:center;margin-bottom:20px;border:1px solid var(--border);border-radius:var(--radius);overflow:hidden}
.sqeg-level{flex:1;padding:8px 4px;text-align:center;font-size:11px;font-weight:700;color:var(--text3);cursor:default;border-right:1px solid var(--border)}
.sqeg-level:last-child{border-right:none}
.sqeg-level.active{background:var(--accent);color:#fff}
.needs-met-block{background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius-lg);padding:18px 20px;margin-bottom:20px;display:none}
.needs-met-label{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--text3);margin-bottom:8px}
.needs-met-scale{display:flex;gap:6px;flex-wrap:wrap}
.nm-btn{padding:5px 12px;border-radius:999px;font-size:12px;font-weight:700;border:1px solid var(--border2);background:var(--bg3);color:var(--text3)}
.nm-btn.active{background:var(--accent);color:#fff;border-color:var(--accent)}
.priority-matrix{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:24px}
.priority-col{border:1px solid var(--border);border-radius:var(--radius-lg);overflow:hidden}
.priority-col-header{padding:10px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px}
.priority-col-header.red{background:var(--red-bg);color:var(--red);border-bottom:1px solid var(--red-border)}
.priority-col-header.amber{background:var(--amber-bg);color:var(--amber);border-bottom:1px solid var(--amber-border)}
.priority-col-header.blue{background:var(--blue-bg);color:var(--blue);border-bottom:1px solid var(--blue-border)}
.priority-item{padding:8px 14px;font-size:12px;color:var(--text2);border-bottom:1px solid var(--border);display:flex;align-items:flex-start;gap:7px}
.priority-item:last-child{border-bottom:none}
.pri-dot{width:7px;height:7px;border-radius:50%;flex-shrink:0;margin-top:4px}
.pri-dot.red{background:var(--red)}
.pri-dot.amber{background:var(--amber)}
.pri-dot.blue{background:var(--blue)}
.effort-badge{font-size:10px;padding:1px 6px;border-radius:4px;background:var(--bg3);color:var(--text3);white-space:nowrap;margin-left:auto;flex-shrink:0}
.filter-bar{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:16px}
.filter-btn{padding:6px 14px;border-radius:999px;font-size:12px;font-weight:600;border:1px solid var(--border2);background:var(--bg2);color:var(--text2);cursor:pointer;transition:all .15s}
.filter-btn:hover{border-color:var(--accent);color:var(--accent)}
.filter-btn.active{background:var(--accent);color:#fff;border-color:var(--accent)}
.criteria-table{width:100%;border-collapse:collapse;margin-bottom:24px}
.criteria-table th{text-align:left;padding:10px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--text3);background:var(--bg3);border-bottom:2px solid var(--border)}
.criteria-table td{padding:14px;border-bottom:1px solid var(--border);vertical-align:top}
.criteria-table tr:hover td{background:var(--bg3)}
.status-dot{width:30px;height:30px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;flex-shrink:0}
.status-dot.green{background:var(--green-bg);color:var(--green);border:1px solid var(--green-border)}
.status-dot.amber{background:var(--amber-bg);color:var(--amber);border:1px solid var(--amber-border)}
.status-dot.red{background:var(--red-bg);color:var(--red);border:1px solid var(--red-border)}
.crit-id{font-family:'DM Mono',monospace;font-size:11px;color:var(--text3)}
.crit-name{font-size:13px;font-weight:700;color:var(--text)}
.crit-cat{font-size:11px;color:var(--text3)}
.crit-ref{font-size:11px;color:var(--accent);font-family:'DM Mono',monospace}
.finding-beleg{display:inline-block;background:var(--bg3);border-radius:4px;padding:2px 7px;font-size:11px;color:var(--text3);margin-bottom:4px}
.finding-rule{font-size:12px;font-style:italic;color:var(--text2);margin-bottom:4px}
.finding-verdict{font-size:12px;font-weight:700;color:var(--text)}
.suggest{margin-top:8px;padding:8px 12px;background:rgba(67,56,202,.08);border-left:2px solid var(--accent);border-radius:0 6px 6px 0;font-size:12px;color:var(--text2);line-height:1.5}
.pq-cards{display:grid;grid-template-columns:repeat(2,1fr);gap:12px;margin-bottom:24px}
.pq-card{background:var(--bg2);border:1px solid var(--border);border-radius:var(--radius-lg);padding:16px 18px;box-shadow:0 1px 3px rgba(26,25,23,.04)}
.pq-card-header{display:flex;align-items:center;gap:10px;margin-bottom:8px}
.pq-card-id{font-family:'DM Mono',monospace;font-size:11px;color:var(--text3)}
.pq-card-name{font-size:13px;font-weight:700;color:var(--text)}
.pq-card-body{font-size:12px;color:var(--text2);line-height:1.6}
.export-bar{display:flex;gap:10px;margin-bottom:24px;flex-wrap:wrap}
@media(max-width:768px){
  .sidebar{width:100%;height:auto;position:static;flex-direction:row;overflow-x:auto;border-right:none;border-bottom:1px solid var(--border)}
  .main-content{margin-left:0}
  .top-bar{left:0}
  .html-area-wrap{left:0}
  .stat-grid{grid-template-columns:repeat(2,1fr)}
  .priority-matrix{grid-template-columns:1fr}
  .pq-cards{grid-template-columns:1fr}
}
</style>
</head>
<body>
<div class="app-shell">
<aside class="sidebar">
  <div class="sidebar-logo">
    <div class="brand-icon-sm">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
    </div>
    <span class="sidebar-brand">evalu<span>-pro</span></span>
  </div>
  <nav class="sidebar-nav">
    <button class="nav-item active" data-tool="sqeg">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
      SQEG Analyzer
    </button>
    <div class="nav-section-label">System</div>
    <button class="nav-item" data-tool="settings">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93l-1.41 1.41M21 12h-2M19.07 19.07l-1.41-1.41M12 21v-2M4.93 19.07l1.41-1.41M3 12h2M4.93 4.93l1.41 1.41"/></svg>
      Einstellungen
    </button>
  </nav>
  <div class="sidebar-footer">
    <span>evalu-pro v1.0</span>
    <a href="../login.php?logout=1">Abmelden</a>
  </div>
</aside>
<div class="main-content">
<div class="top-bar">
  <input type="text" id="url-input" class="url-input" placeholder="https://www.beispiel.de/seite" autocomplete="off" spellcheck="false">
  <div class="mode-toggle">
    <button class="mode-btn active" id="mode-url" onclick="setMode('url')">URL</button>
    <button class="mode-btn" id="mode-html" onclick="setMode('html')">HTML einfügen</button>
  </div>
  <button class="btn-start" id="btn-start" onclick="startAnalysis()">
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="5 3 19 12 5 21 5 3"/></svg>
    Analyse starten
  </button>
</div>
<div class="html-area-wrap" id="html-area-wrap">
  <textarea id="html-textarea" class="html-textarea" placeholder="HTML-Quellcode hier einfügen…"></textarea>
</div>
<div class="container">
<div class="tool-panel active" id="panel-sqeg">
  <div class="input-card">
    <div class="card-header">
      <div class="card-icon">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
      </div>
      <div>
        <div class="card-title">SQEG Analyzer</div>
        <div class="card-sub">Google Search Quality Evaluator Guidelines · Nov 2025</div>
      </div>
    </div>
    <div id="url-display" class="url-display"></div>
    <button class="context-toggle" onclick="toggleContext()">
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
      Optionale Kontext-Felder (Keyword, Ziel, Zielgruppe)
    </button>
    <div class="context-fields" id="context-fields">
      <div class="ctx-field">
        <span class="ctx-label">Keyword / Suchanfrage</span>
        <input type="text" id="ctx-keyword" class="ctx-input" placeholder="z.B. beste Zahnversicherung">
      </div>
      <div class="ctx-field">
        <span class="ctx-label">Conversion-Ziel</span>
        <input type="text" id="ctx-goal" class="ctx-input" placeholder="z.B. Newsletter-Anmeldung">
      </div>
      <div class="ctx-field">
        <span class="ctx-label">Zielgruppe</span>
        <input type="text" id="ctx-audience" class="ctx-input" placeholder="z.B. Frauen 35–50">
      </div>
    </div>
  </div>

  <div id="progress-section" style="display:none">
    <div class="input-card">
      <div class="progress-header">
        <span class="progress-label" id="progress-label">Analyse läuft…</span>
        <span class="progress-pct" id="progress-pct">0%</span>
      </div>
      <div id="progress-bar-wrap"><div class="progress-bar-bg"><div class="progress-bar" id="progress-bar"></div></div></div>
      <div id="loader-wrap"><div class="loader-dots">
        <div class="loader-dot"></div><div class="loader-dot"></div><div class="loader-dot"></div>
      </div></div>
      <div class="status-msg" id="status-msg">Initialisierung…</div>
      <div class="log-box" id="log-box"></div>
    </div>
  </div>

  <div id="results-section" style="display:none">
    <div class="section-divider"><div class="section-divider-line"></div><span class="section-divider-label">Schnellüberblick</span><div class="section-divider-line"></div></div>
    <div class="results-header">
      <div id="score-badge" class="score-badge green">–</div>
      <div id="ymyl-badge" class="ymyl-badge green"></div>
      <div style="margin-left:auto;display:flex;gap:8px;flex-wrap:wrap">
        <button class="btn-secondary" onclick="startAnalysis()"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-3.51"/></svg>Re-Analyse</button>
        <button class="btn-secondary" onclick="exportHtml()">↓ HTML-Bericht</button>
        <button class="btn-secondary" onclick="window.print()">⎙ PDF</button>
      </div>
    </div>
    <div class="stat-grid">
      <div class="stat-box green"><div class="stat-num" id="cnt-g">0</div><div class="stat-lbl">✓ Bestanden</div></div>
      <div class="stat-box amber"><div class="stat-num" id="cnt-a">0</div><div class="stat-lbl">◑ Verbesserungswürdig</div></div>
      <div class="stat-box red"><div class="stat-num" id="cnt-r">0</div><div class="stat-lbl">✗ Fehlerhaft</div></div>
      <div class="stat-box blue"><div class="stat-num" id="cnt-pq">7</div><div class="stat-lbl">☐ PQ-Erweitert</div></div>
    </div>
    <div class="sqeg-scale" id="sqeg-scale">
      <div class="sqeg-level" data-level="Lowest">Lowest</div>
      <div class="sqeg-level" data-level="Low">Low</div>
      <div class="sqeg-level" data-level="Medium">Medium</div>
      <div class="sqeg-level" data-level="Medium+">Medium+</div>
      <div class="sqeg-level" data-level="High">High</div>
      <div class="sqeg-level" data-level="Highest">Highest</div>
    </div>
    <div class="needs-met-block" id="needs-met-block">
      <div class="needs-met-label">e8 · Needs Met</div>
      <div class="needs-met-scale" id="needs-met-scale">
        <span class="nm-btn" data-nm="FullyM">Fully Meets</span>
        <span class="nm-btn" data-nm="HighlyM">Highly Meets</span>
        <span class="nm-btn" data-nm="ModeratelyM">Moderately Meets</span>
        <span class="nm-btn" data-nm="SlightlyM">Slightly Meets</span>
        <span class="nm-btn" data-nm="FailsM">Fails to Meet</span>
      </div>
    </div>
    <div class="section-divider"><div class="section-divider-line"></div><span class="section-divider-label">Prioritäten-Matrix</span><div class="section-divider-line"></div></div>
    <div class="priority-matrix">
      <div class="priority-col"><div class="priority-col-header red">🔴 Sofort angehen</div><div id="pri-sofort"></div></div>
      <div class="priority-col"><div class="priority-col-header amber">🟡 Quick Wins</div><div id="pri-quick"></div></div>
      <div class="priority-col"><div class="priority-col-header blue">🔵 Mittelfristig</div><div id="pri-mid"></div></div>
    </div>
    <div class="section-divider"><div class="section-divider-line"></div><span class="section-divider-label">Detailanalyse</span><div class="section-divider-line"></div></div>
    <div class="export-bar">
      <button class="btn-secondary" onclick="exportHtml()">↓ HTML-Bericht</button>
      <button class="btn-secondary" onclick="window.print()">⎙ PDF</button>
    </div>
    <div class="filter-bar">
      <button class="filter-btn active" data-filter="all" onclick="setFilter('all',this)">Alle</button>
      <button class="filter-btn" data-filter="green" onclick="setFilter('green',this)">✓ Bestanden</button>
      <button class="filter-btn" data-filter="amber" onclick="setFilter('amber',this)">◑ Verbesserbar</button>
      <button class="filter-btn" data-filter="red" onclick="setFilter('red',this)">✗ Fehlerhaft</button>
      <button class="filter-btn" data-filter="pq" onclick="setFilter('pq',this)">☐ PQ-Erweitert</button>
    </div>
    <table class="criteria-table" id="criteria-table">
      <thead><tr><th style="width:44px">Status</th><th>Kriterium</th><th>Befund &amp; Bewertung</th></tr></thead>
      <tbody id="criteria-tbody"></tbody>
    </table>
    <div class="section-divider"><div class="section-divider-line"></div><span class="section-divider-label">PQ-Erweitert (e1–e7)</span><div class="section-divider-line"></div></div>
    <div class="pq-cards" id="pq-cards"></div>
  </div>
</div><!-- /panel-sqeg -->
<div class="tool-panel" id="panel-settings">
  <div class="input-card">
    <div class="card-header">
      <div class="card-icon">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93l-1.41 1.41M21 12h-2M19.07 19.07l-1.41-1.41M12 21v-2M4.93 19.07l1.41-1.41M3 12h2M4.93 4.93l1.41 1.41"/></svg>
      </div>
      <div>
        <div class="card-title">Einstellungen</div>
        <div class="card-sub">API-Keys · Modell · Passwort</div>
      </div>
    </div>
    <div class="settings-section">
      <div class="settings-section-title">Anthropic API-Key</div>
      <div class="settings-section-desc">Erforderlich für den SQEG Analyzer. Erhältlich unter console.anthropic.com.</div>
      <div id="key-masked-display" class="key-masked"></div>
      <form id="form-apikey" onsubmit="saveApiKey(event)">
        <div class="settings-field">
          <label class="settings-label" for="s-apikey">API-Key</label>
          <div class="settings-input-wrap">
            <input type="password" id="s-apikey" class="settings-input" placeholder="sk-ant-…" autocomplete="off">
            <button type="button" class="settings-toggle-btn" onclick="toggleSettingsPw('s-apikey',this)">Anzeigen</button>
          </div>
        </div>
        <button type="submit" class="btn-save">Speichern</button>
        <div class="success-msg" id="msg-apikey">✓ API-Key gespeichert.</div>
        <div class="err-box" id="err-apikey" style="display:none;margin-top:10px;"></div>
      </form>
    </div>
    <div style="height:1px;background:var(--border);margin:24px 0"></div>
    <div class="settings-section">
      <div class="settings-section-title">KI-Modell</div>
      <div class="settings-section-desc">Claude-Modell für die SQEG-Analyse auswählen.</div>
      <form id="form-model" onsubmit="saveModel(event)">
        <div class="settings-field">
          <label class="settings-label" for="s-model">Modell</label>
          <select id="s-model" class="settings-input" style="font-family:'DM Sans',sans-serif;cursor:pointer">
            <option value="claude-sonnet-4-5">claude-sonnet-4-5 (Standard)</option>
            <option value="claude-opus-4-5">claude-opus-4-5 (leistungsstärker)</option>
            <option value="claude-haiku-4-5">claude-haiku-4-5 (schneller / günstiger)</option>
          </select>
        </div>
        <button type="submit" class="btn-save">Speichern</button>
        <div class="success-msg" id="msg-model">✓ Modell gespeichert.</div>
      </form>
    </div>
    <div style="height:1px;background:var(--border);margin:24px 0"></div>
    <div class="settings-section">
      <div class="settings-section-title">Login-Passwort ändern</div>
      <div class="settings-section-desc">Mindestens 8 Zeichen. Gespeichert als sicherer Hash.</div>
      <form id="form-password" onsubmit="savePassword(event)">
        <div class="settings-field">
          <label class="settings-label" for="s-pw">Neues Passwort</label>
          <input type="password" id="s-pw" class="settings-input" placeholder="Neues Passwort" autocomplete="new-password" minlength="8">
        </div>
        <div class="settings-field">
          <label class="settings-label" for="s-pw2">Passwort bestätigen</label>
          <input type="password" id="s-pw2" class="settings-input" placeholder="Passwort wiederholen" autocomplete="new-password" minlength="8">
        </div>
        <button type="submit" class="btn-save">Passwort ändern</button>
        <div class="success-msg" id="msg-password">✓ Passwort geändert.</div>
        <div class="err-box" id="err-password" style="display:none;margin-top:10px;"></div>
      </form>
    </div>
  </div>
</div>
</div>
</div>
</div>
<script>
const CSRF_TOKEN = '<?= htmlspecialchars($csrfToken, ENT_QUOTES) ?>';
// === ROUTING ===
function showTool(name){
  document.querySelectorAll('.tool-panel').forEach(p=>p.classList.remove('active'));
  document.querySelectorAll('[data-tool]').forEach(b=>b.classList.remove('active'));
  const p=document.getElementById('panel-'+name);
  if(p)p.classList.add('active');
  const b=document.querySelector('[data-tool="'+name+'"]');
  if(b)b.classList.add('active');
}
document.querySelectorAll('[data-tool]').forEach(btn=>btn.addEventListener('click',()=>showTool(btn.dataset.tool)));

// === MODE TOGGLE ===
let currentMode='url';
function setMode(mode){
  currentMode=mode;
  document.getElementById('mode-url').classList.toggle('active',mode==='url');
  document.getElementById('mode-html').classList.toggle('active',mode==='html');
  document.getElementById('html-area-wrap').classList.toggle('visible',mode==='html');
}
function toggleContext(){document.getElementById('context-fields').classList.toggle('visible')}

// === CRITERIA ===
const CRITERIA=[
  {id:'c1', cat:'A: Seitenzweck',          name:'Klar erkennbarer Seitenzweck (Beneficial Purpose)',          ref:'Sek. 2.2'},
  {id:'c2', cat:'A: Seitenzweck',          name:'MC klar identifizierbar und vom Rest abgegrenzt',            ref:'Sek. 2.4.1'},
  {id:'c3', cat:'A: Seitenzweck',          name:'YMYL-Einordnung & erhöhte Qualitätsstandards',               ref:'Sek. 2.3'},
  {id:'c4', cat:'A: Seitenzweck',          name:'Seitentyp-angemessene Qualitätserwartung erfüllt',           ref:'Sek. 3.1'},
  {id:'c5', cat:'B: E-E-A-T',              name:'Experience – Ersthand-Erfahrung des Content-Creators',       ref:'Sek. 3.4'},
  {id:'c6', cat:'B: E-E-A-T',              name:'Expertise – Fachkompetenz (formal & informal)',              ref:'Sek. 3.4'},
  {id:'c7', cat:'B: E-E-A-T',              name:'Authoritativeness – Autorität der Website im Themenfeld',    ref:'Sek. 3.4'},
  {id:'c8', cat:'B: E-E-A-T',              name:'Trust – Gesamtvertrauenswürdigkeit (wichtigstes Element)',   ref:'Sek. 3.4'},
  {id:'c9', cat:'B: E-E-A-T',              name:'YMYL: Experience vs. Expertise korrekt eingesetzt',          ref:'Sek. 3.4.1'},
  {id:'c10',cat:'C: MC-Qualität',          name:'Effort – Menschlicher Aufwand bei Content-Erstellung',      ref:'Sek. 3.2'},
  {id:'c11',cat:'C: MC-Qualität',          name:'Originality – Einzigartiger, nicht-kopierbarer Content',    ref:'Sek. 3.2'},
  {id:'c12',cat:'C: MC-Qualität',          name:'Talent & Skill – Handwerkliche Qualität der Ausführung',    ref:'Sek. 3.2'},
  {id:'c13',cat:'C: MC-Qualität',          name:'Accuracy – Faktische Korrektheit & Expertenkonsens',        ref:'Sek. 3.2'},
  {id:'c14',cat:'C: MC-Qualität',          name:'Kein Filler-Content – MC steht prominent vorne',            ref:'Sek. 5.2.2'},
  {id:'c15',cat:'C: MC-Qualität',          name:'Kein Scaled/AI Content Abuse',                              ref:'Sek. 4.6.5'},
  {id:'c16',cat:'D: Reputation & Transparenz',name:'Reputation der Website',                                 ref:'Sek. 3.3.1'},
  {id:'c17',cat:'D: Reputation & Transparenz',name:'Reputation des Content-Creators erkennbar',              ref:'Sek. 3.3.4'},
  {id:'c18',cat:'D: Reputation & Transparenz',name:'Verantwortlichkeit – Wer steckt hinter der Seite?',      ref:'Sek. 2.5.2'},
  {id:'c19',cat:'D: Reputation & Transparenz',name:'About-Seite / Impressum / Rechtliche Angaben',           ref:'Sek. 2.5.3'},
  {id:'c20',cat:'D: Reputation & Transparenz',name:'Kontakt & Kundenservice',                                ref:'Sek. 2.5.3'},
  {id:'c21',cat:'D: Reputation & Transparenz',name:'Kein offensichtlicher Interessenkonflikt ohne Offenlegung',ref:'Sek. 3.4'},
  {id:'c22',cat:'E: Lowest Quality Signals',  name:'Kein täuschendes Design / täuschender Seitenzweck',     ref:'Sek. 4.5.3'},
  {id:'c23',cat:'E: Lowest Quality Signals',  name:'MC nicht durch Ads/SC verdeckt oder obstruiert',        ref:'Sek. 4.5.4'},
  {id:'c24',cat:'E: Lowest Quality Signals',  name:'Kein Verdacht auf Scam oder schädliches Verhalten',     ref:'Sek. 4.5.5'},
  {id:'c25',cat:'F: UX & SC',              name:'Supplementary Content unterstützt Seitenzweck sinnvoll',    ref:'Sek. 2.4.2'},
  {id:'c26',cat:'F: UX & SC',              name:'Seitentitel beschreibend und nicht irreführend',            ref:'Sek. 3.1'},
  {id:'c27',cat:'F: UX & SC',              name:'Mobile-Nutzbarkeit & Page Experience',                      ref:'Sek. 7.0'},
  {id:'c28',cat:'G: Freshness',            name:'Aktualität: Freshness für zeitkritische Themen',            ref:'Sek. 18.0'},
  {id:'c29',cat:'G: Freshness',            name:'Content-Vollständigkeit & Tiefe',                           ref:'Sek. 4.1'},
];
const PQ_CRITERIA=[
  {id:'e1',name:'Externe Reputation der Website',         ref:'Sek. 3.3.1–3.3.4'},
  {id:'e2',name:'Deceptive Design & Creator-Verifikation',ref:'Sek. 4.5.3'},
  {id:'e3',name:'Harmful to Self or Others',              ref:'Sek. 4.2'},
  {id:'e4',name:'Harmful to Specified Groups',            ref:'Sek. 4.3'},
  {id:'e5',name:'Harmfully Misleading Information',       ref:'Sek. 4.4'},
  {id:'e6',name:'Interessenkonflikt & Transparenz',       ref:'Sek. 3.4'},
  {id:'e7',name:'Seitentyp-Sonderregeln',                 ref:'Sek. 9.0–9.3'},
];
const WEIGHTS={c8:4,c24:4,c22:4,c3:3,c5:3,c6:3,c7:3,c9:3,c13:3,c18:3,c19:3,c20:3,c21:3,c23:3,e3:3,e4:3,e5:3,e6:3,c10:1.5,c11:1.5,c12:1.5,c14:1.5,c15:1.5,c28:1.5,c29:1.5};
function getWeight(id){return WEIGHTS[id]??2}
function statusScore(s){return s==='green'?100:s==='amber'?50:0}
const MINI_CALLS=[['c1','c2'],['c3','c4'],['c5','c6'],['c7','c8'],['c9','c10'],['c11','c12'],['c13','c14'],['c15','c16'],['c17','c18'],['c19','c20'],['c21','c22'],['c23','c24'],['c25','c26'],['c27','c28'],['c29']];

// === STATE ===
let analysisResults=[],pqResults=[],e8Result=null,ymylResult=null,currentUrl='',currentHtml='';
let gscData=null,serpData=null,backlinkData=null,psiData=null;

// === LOG / PROGRESS ===
function escHtml(s){return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;')}
function log(msg,type='info'){
  const box=document.getElementById('log-box');
  const cls=type==='ok'?'log-ok':type==='err'?'log-err':'log-info';
  box.innerHTML+=`<div class="${cls}">[${new Date().toLocaleTimeString()}] ${escHtml(msg)}</div>`;
  box.scrollTop=box.scrollHeight;
}
function setProgress(pct,label='',status=''){
  document.getElementById('progress-bar').style.width=pct+'%';
  document.getElementById('progress-pct').textContent=Math.round(pct)+'%';
  if(label)document.getElementById('progress-label').textContent=label;
  if(status)document.getElementById('status-msg').textContent=status;
}

// === START ANALYSIS ===
async function startAnalysis(){
  const urlVal=document.getElementById('url-input').value.trim();
  const htmlVal=document.getElementById('html-textarea').value.trim();
  const keyword=document.getElementById('ctx-keyword').value.trim();
  if(currentMode==='url'&&!urlVal){alert('Bitte eine URL eingeben.');return}
  if(currentMode==='html'&&!htmlVal){alert('Bitte HTML einfügen.');return}

  document.getElementById('btn-start').disabled=true;
  document.getElementById('progress-section').style.display='block';
  document.getElementById('progress-bar-wrap').style.display='block';
  document.getElementById('loader-wrap').style.display='block';
  document.getElementById('status-msg').style.display='block';
  document.getElementById('progress-pct').style.display='';
  document.getElementById('results-section').style.display='none';
  document.getElementById('log-box').innerHTML='';
  analysisResults=[];pqResults=[];e8Result=null;ymylResult=null;
  gscData=null;serpData=null;backlinkData=null;psiData=null;
  setProgress(0,'Analyse startet…','Vorbereitung…');
  showTool('sqeg');

  try{
    if(currentMode==='url'){
      currentUrl=urlVal;
      const ud=document.getElementById('url-display');
      ud.textContent=currentUrl;ud.style.display='block';
      log('Rufe URL ab: '+currentUrl);
      setProgress(3,'HTML abrufen…','Seite wird geladen…');
      const res=await fetch('fetch.php?url='+encodeURIComponent(currentUrl));
      if(!res.ok)throw new Error('fetch.php HTTP '+res.status);
      const data=await res.json();
      if(data.error)throw new Error(data.error);
      currentHtml=data.html;
      log(`HTML abgerufen (${(data.length/1024).toFixed(1)} KB)`,'ok');
    }else{
      currentUrl=urlVal||'(HTML-Modus)';
      currentHtml=htmlVal;
      log('HTML manuell eingefügt ('+( currentHtml.length/1024).toFixed(1)+' KB)','ok');
    }
    setProgress(8);
    const htmlSnippet=currentHtml.substring(0,12000);
    const effectiveKeyword=keyword||'';

    // Externe Daten parallel abrufen (Fehler blockieren nicht)
    setProgress(8,'Daten abrufen…','GSC · SERP · Backlinks · PageSpeed…');
    const [gscRes,serpRes,blRes,psiRes]=await Promise.allSettled([
      currentMode==='url'&&currentUrl?fetchGscData(currentUrl):Promise.resolve(null),
      effectiveKeyword?fetchSerpData(effectiveKeyword):Promise.resolve(null),
      currentMode==='url'&&currentUrl?fetchBacklinkData(currentUrl):Promise.resolve(null),
      currentMode==='url'&&currentUrl?fetchPageSpeedData(currentUrl):Promise.resolve(null),
    ]);
    gscData     = gscRes.status==='fulfilled'?gscRes.value:null;
    serpData    = serpRes.status==='fulfilled'?serpRes.value:null;
    backlinkData= blRes.status==='fulfilled'?blRes.value:null;
    psiData     = psiRes.status==='fulfilled'?psiRes.value:null;

    if(gscData?.keywords?.length)log(`GSC: ${gscData.keywords.length} Keywords geladen`,'ok');
    else log('GSC: keine Daten (nicht konfiguriert oder keine Treffer)');
    if(serpData?.tasks?.[0]?.result?.[0]?.items)log(`SERP: Top-10 für "${effectiveKeyword}" geladen`,'ok');
    else if(effectiveKeyword)log(`SERP: keine Daten für "${effectiveKeyword}"`);
    if(backlinkData?.tasks?.[0]?.result?.[0])log('Backlinks: Profil geladen','ok');
    else log('Backlinks: keine Daten');
    if(psiData?.success)log(`PageSpeed: Score ${psiData.perf_score}/100 (Mobile)`,'ok');
    else if(currentMode==='url')log('PageSpeed: keine Daten');
    setProgress(14);

    // Kontext-Blöcke bauen
    const ctx={
      ctxBlock:    buildCtxBlock(effectiveKeyword,gscData),
      serpBlock:   buildSerpBlock(serpData,effectiveKeyword),
      backlinkBlock: buildBacklinkBlock(backlinkData),
      psiBlock:    buildPsiBlock(psiData),
    };

    log('Klassifiziere YMYL…');
    setProgress(15,'YMYL klassifizieren…','YMYL-Analyse…');
    ymylResult=await classifyYmyl(htmlSnippet,currentUrl);
    log('YMYL: '+ymylResult,'ok');
    setProgress(18);

    log('Starte 15 parallele SQEG-Mini-Calls…');
    setProgress(18,'SQEG-Kriterien analysieren…','15 parallele KI-Anfragen…');
    const miniPromises=MINI_CALLS.map((ids,idx)=>runMiniCall(ids,htmlSnippet,currentUrl,ymylResult,effectiveKeyword,idx,ctx));
    const miniResults=await Promise.allSettled(miniPromises);
    miniResults.forEach((r,i)=>{
      if(r.status==='fulfilled'){analysisResults.push(...r.value);log(`Call ${i+1} (${MINI_CALLS[i].join(',')}) ✓`,'ok')}
      else{log(`Call ${i+1} fehlgeschlagen: `+r.reason,'err')}
      setProgress(18+((i+1)/15)*52);
    });
    setProgress(72);

    log('Analysiere PQ-Erweitert (e1–e7)…');
    setProgress(74,'PQ-Erweitert…','e1–e7 Analyse…');
    try{pqResults=await runPqExtended(htmlSnippet,currentUrl,ymylResult,ctx);log('PQ-Erweitert abgeschlossen','ok')}
    catch(e){log('PQ-Erweitert fehlgeschlagen: '+e.message,'err')}
    setProgress(86);

    // Needs Met: GSC Top-Keyword als Fallback
    const nmKeyword=effectiveKeyword||(gscData?.keywords?.[0]?.query||'');
    if(nmKeyword){
      log('Needs Met (e8) für: '+nmKeyword);
      setProgress(88,'Needs Met…','e8 Analyse…');
      try{e8Result=await runNeedsMet(htmlSnippet,currentUrl,nmKeyword,gscData,serpData);log('Needs Met: '+(e8Result?.rating||'–'),'ok')}
      catch(e){log('Needs Met fehlgeschlagen: '+e.message,'err')}
    }
    setProgress(95,'Ergebnisse rendern…','Fast fertig…');
    renderResults(keyword);
    setProgress(100,'Fertig!','Analyse abgeschlossen.');
    setTimeout(()=>{
      // Fortschrittsbalken + Loader ausblenden, Log-Box bleibt sichtbar
      document.getElementById('progress-bar-wrap').style.display='none';
      document.getElementById('loader-wrap').style.display='none';
      document.getElementById('status-msg').style.display='none';
      document.getElementById('progress-label').textContent='Analyse-Log';
      document.getElementById('progress-pct').style.display='none';
      document.getElementById('results-section').style.display='block';
    },600);
  }catch(err){
    log('Kritischer Fehler: '+err.message,'err');
    setProgress(0,'Fehler',err.message);
  }
  document.getElementById('btn-start').disabled=false;
}
// === API HELPER ===
async function callApi(messages,systemPrompt,maxTokens=2000){
  const res=await fetch('api.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({messages,system:systemPrompt,max_tokens:maxTokens})});
  const data=await res.json();
  if(data.error)throw new Error(typeof data.error==='object'?data.error.message:data.error);
  return data.content?.[0]?.text??'';
}

// === DATEN-FETCH ===
async function fetchGscData(url){
  try{const res=await fetch('gsc.php?action=data',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({url})});const d=await res.json();return d.success?d:null;}catch(e){return null;}
}
async function fetchSerpData(keyword){
  try{const res=await fetch('dataforseo.php?action=serp',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({keyword,limit:10})});return await res.json();}catch(e){return null;}
}
async function fetchBacklinkData(url){
  try{const res=await fetch('dataforseo.php?action=backlinks',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({target:url})});return await res.json();}catch(e){return null;}
}
async function fetchPageSpeedData(url){
  try{const res=await fetch('pagespeed.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({url,strategy:'mobile'})});return await res.json();}catch(e){return null;}
}

// === KONTEXT-BLÖCKE ===
function buildCtxBlock(keyword,gsc){
  let b='';
  if(keyword)b+=`\nZiel-Keyword: ${keyword}`;
  if(gsc?.keywords?.length){const top=gsc.keywords.slice(0,10);b+='\n\nGSC-Keyword-Performance (90 Tage):\n'+top.map(k=>`• ${k.query}: ${k.clicks} Klicks, ${k.impressions} Imp., CTR ${k.ctr}%, Pos. ${k.position}`).join('\n');}
  return b;
}
function buildSerpBlock(serp,keyword){
  if(!serp?.tasks?.[0]?.result?.[0]?.items)return'';
  const items=serp.tasks[0].result[0].items.filter(i=>i.type==='organic').slice(0,10);
  if(!items.length)return'';
  return`\n\nSERP-Benchmark für "${keyword}" (Top ${items.length}):\n`+items.map((i,n)=>`${n+1}. ${i.url||i.relative_url||''} – ${i.title||''}${i.description?'\n   '+i.description.substring(0,100):''}`).join('\n');
}
function buildBacklinkBlock(bl){
  const r=bl?.tasks?.[0]?.result?.[0];
  if(!r)return'';
  return`\n\nBacklink-Profil:\n• Domain Rank: ${r.rank||'–'}\n• Referring Domains: ${r.referring_domains||'–'}\n• Backlinks: ${r.backlinks||'–'}\n• Spam Score: ${r.spam_score||0}%`;
}
function buildPsiBlock(psi){
  if(!psi?.success)return'';
  return`\n\nPageSpeed Mobile:\n• Score: ${psi.perf_score||'–'}/100\n• LCP: ${psi.lcp||'–'} · CLS: ${psi.cls||'–'} · TBT: ${psi.tbt||'–'} · FCP: ${psi.fcp||'–'}`;
}

// === YMYL ===
async function classifyYmyl(htmlSnippet,url){
  const sys=`Du bist ein Google Search Quality Evaluator. Klassifiziere den YMYL-Status der Seite.\nAntworte NUR mit einem dieser drei Werte (kein weiterer Text): clear_ymyl | mixed_ymyl | none\nYMYL-Kategorien: Finanzen, Medizin/Gesundheit, Recht, Sicherheit, große Kaufentscheidungen, Neuigkeiten/gesellschaftliche Themen, Kinderschutz.`;
  const r=await callApi([{role:'user',content:`URL: ${url}\nHTML (3000 Zeichen):\n${htmlSnippet.substring(0,3000)}`}],sys,50);
  const c=r.trim().toLowerCase();
  if(c.includes('clear_ymyl'))return 'clear_ymyl';
  if(c.includes('mixed_ymyl'))return 'mixed_ymyl';
  return 'none';
}

// === MINI CALLS ===
async function runMiniCall(ids,htmlSnippet,url,ymyl,keyword,idx,ctx={}){
  const criteriaList=ids.map(id=>{const c=CRITERIA.find(x=>x.id===id);return`${c.id} · ${c.name} · ${c.ref}`}).join('\n');
  const ymylHint=ymyl==='clear_ymyl'?'YMYL: Klar YMYL – erhöhte Qualitätsanforderungen.':ymyl==='mixed_ymyl'?'YMYL: Teilweise YMYL – erhöhte Sorgfalt.':'';
  const sys=`Du bist ein Google Search Quality Evaluator (SQEG November 2025).\nAntworte AUSSCHLIESSLICH als JSON-Array. Kein Text davor oder danach.\nFormat je Objekt: {"id":"c1","category":"A: Seitenzweck","criterion":"Name","sqeg_ref":"Sek. X.X","status":"green|amber|red","finding":"Beleg: [Signal aus HTML] | Regel: [WENN-Bedingung] | Bewertung: [Urteil]","improvement":"[konkreter Vorschlag, leer wenn green]","confidence":80}`;
  const contextParts=(ctx.ctxBlock||'')+(ctx.serpBlock||'')+(ctx.backlinkBlock||'')+(ctx.psiBlock||'');
  const msg=`URL: ${url}\nHTML-Ausschnitt (12.000 Zeichen):\n${htmlSnippet}${keyword?'\nKeyword: '+keyword:''}\n${ymylHint}${contextParts}\n\nZu bewertende Kriterien:\n${criteriaList}`;
  const text=await callApi([{role:'user',content:msg}],sys,2000);
  const m=text.match(/\[[\s\S]*\]/);
  if(!m)throw new Error('Kein JSON-Array in Call '+(idx+1));
  return JSON.parse(m[0]);
}

// === PQ EXTENDED ===
async function runPqExtended(htmlSnippet,url,ymyl,ctx={}){
  const criteriaList=PQ_CRITERIA.map(c=>`${c.id} · ${c.name} · ${c.ref}`).join('\n');
  const sys=`Du bist ein Google Search Quality Evaluator (SQEG November 2025).\nAntworte AUSSCHLIESSLICH als JSON-Array.\nFormat: {"id":"e1","name":"Name","status":"green|amber|red","finding":"Befund","improvement":"Vorschlag"}`;
  const contextParts=(ctx.ctxBlock||'')+(ctx.backlinkBlock||'')+(ctx.psiBlock||'');
  const text=await callApi([{role:'user',content:`URL: ${url}\nHTML (8000 Zeichen):\n${htmlSnippet.substring(0,8000)}${contextParts}\n\nKriterien:\n${criteriaList}`}],sys,2000);
  const m=text.match(/\[[\s\S]*\]/);
  if(!m)throw new Error('Kein JSON in PQ-Erweitert');
  return JSON.parse(m[0]);
}

// === NEEDS MET ===
async function runNeedsMet(htmlSnippet,url,keyword,gsc=null,serp=null){
  const sys=`Du bist ein Google Search Quality Evaluator. Bewerte Needs Met (e8).\nAntworte NUR als JSON: {"rating":"FullyM|HighlyM|ModeratelyM|SlightlyM|FailsM","score":100,"finding":"Begründung"}\nSkala: FullyM=100, HighlyM=80, ModeratelyM=55, SlightlyM=30, FailsM=10`;
  let ctx='';
  if(gsc?.keywords?.length){const top=gsc.keywords.slice(0,5);ctx+='\n\nGSC Top-Keywords (90 Tage):\n'+top.map(k=>`• ${k.query}: ${k.clicks} Klicks, Ø-Pos ${k.position}`).join('\n');}
  if(serp?.tasks?.[0]?.result?.[0]?.items){const items=serp.tasks[0].result[0].items.filter(i=>i.type==='organic').slice(0,5);if(items.length)ctx+='\n\nSERP Top 5 für "'+keyword+'":\n'+items.map((i,n)=>`${n+1}. ${i.url||''} – ${i.title||''}`).join('\n');}
  const text=await callApi([{role:'user',content:`URL: ${url}\nKeyword: ${keyword}\nHTML (6000 Zeichen):\n${htmlSnippet.substring(0,6000)}${ctx}`}],sys,500);
  const m=text.match(/\{[\s\S]*\}/);
  if(!m)throw new Error('Kein JSON in Needs Met');
  return JSON.parse(m[0]);
}

// === RENDERING ===
function calcScore(){
  let tw=0,ts=0;
  analysisResults.forEach(r=>{const w=getWeight(r.id);tw+=w;ts+=statusScore(r.status)*w});
  return tw>0?ts/tw:0;
}
function scoreToLevel(s){
  if(s>=87)return'Highest';if(s>=73)return'High';if(s>=60)return'Medium+';if(s>=47)return'Medium';if(s>=30)return'Low';return'Lowest';
}

function renderResults(keyword){
  const score=calcScore(),level=scoreToLevel(score);
  const g=analysisResults.filter(r=>r.status==='green').length;
  const a=analysisResults.filter(r=>r.status==='amber').length;
  const r=analysisResults.filter(r=>r.status==='red').length;

  const badge=document.getElementById('score-badge');
  const cls=score>=75?'green':score>=50?'amber':'red';
  badge.className='score-badge '+cls;
  badge.innerHTML=`<span>${Math.round(score)}%</span><span>${level}</span>`;

  const ymylEl=document.getElementById('ymyl-badge');
  if(ymylResult==='clear_ymyl'){ymylEl.className='ymyl-badge red';ymylEl.textContent='YMYL: Erhöhter Maßstab'}
  else if(ymylResult==='mixed_ymyl'){ymylEl.className='ymyl-badge amber';ymylEl.textContent='YMYL: Teilweise'}
  else{ymylEl.className='ymyl-badge green';ymylEl.textContent='Kein YMYL'}

  document.getElementById('cnt-g').textContent=g;
  document.getElementById('cnt-a').textContent=a;
  document.getElementById('cnt-r').textContent=r;
  document.getElementById('cnt-pq').textContent=pqResults.length||7;

  document.querySelectorAll('.sqeg-level').forEach(el=>el.classList.toggle('active',el.dataset.level===level));

  if(e8Result&&keyword){
    document.getElementById('needs-met-block').style.display='block';
    document.querySelectorAll('.nm-btn').forEach(btn=>btn.classList.toggle('active',btn.dataset.nm===e8Result.rating));
  }
  renderPriorityMatrix();
  renderCriteriaTable(analysisResults,'all');
  renderPqCards();
}

function renderPriorityMatrix(){
  const s=document.getElementById('pri-sofort'),q=document.getElementById('pri-quick'),m=document.getElementById('pri-mid');
  s.innerHTML=q.innerHTML=m.innerHTML='';
  analysisResults.forEach(r=>{
    if(r.status==='green')return;
    const w=getWeight(r.id);
    const crit=CRITERIA.find(c=>c.id===r.id)||{name:r.criterion||r.id};
    const name=crit.name.length>50?crit.name.substring(0,50)+'…':crit.name;
    const effort=w>=4?'Hoch':w>=3?'Mittel':'Niedrig';
    let col,dot;
    if(r.status==='red'&&w>=3){col=s;dot='red'}
    else if(r.status==='amber'&&w>=2||r.status==='red'&&w<3){col=q;dot='amber'}
    else{col=m;dot='blue'}
    col.innerHTML+=`<div class="priority-item"><div class="pri-dot ${dot}"></div><span>${escHtml(r.id+' · '+name)}</span><span class="effort-badge">${effort}</span></div>`;
  });
  if(!s.innerHTML)s.innerHTML='<div class="priority-item" style="color:var(--green)">✓ Keine kritischen Fehler</div>';
  if(!q.innerHTML)q.innerHTML='<div class="priority-item" style="color:var(--green)">✓ Keine Quick Wins nötig</div>';
  if(!m.innerHTML)m.innerHTML='<div class="priority-item" style="color:var(--text3)">–</div>';
}

let currentFilter='all';
function setFilter(filter,btn){
  currentFilter=filter;
  document.querySelectorAll('.filter-btn').forEach(b=>b.classList.remove('active'));
  btn.classList.add('active');
  if(filter==='pq'){renderPqCards();document.getElementById('criteria-tbody').innerHTML='';return}
  renderCriteriaTable(analysisResults,filter);
}

function renderCriteriaTable(results,filter){
  const tbody=document.getElementById('criteria-tbody');
  let filtered=filter==='all'?results:results.filter(r=>r.status===filter);
  if(!filtered.length){tbody.innerHTML='<tr><td colspan="3" style="text-align:center;color:var(--text3);padding:24px">Keine Einträge für diesen Filter.</td></tr>';return}
  tbody.innerHTML=filtered.map(r=>{
    const crit=CRITERIA.find(c=>c.id===r.id)||{cat:'',name:r.criterion||r.id,ref:r.sqeg_ref||''};
    const sym=r.status==='green'?'✓':r.status==='amber'?'◑':'✗';
    const parts=(r.finding||'').split('|');
    const beleg=(parts[0]||'').replace(/^Beleg:\s*/,'').trim();
    const rule=(parts[1]||'').replace(/^Regel:\s*/,'').trim();
    const verdict=(parts[2]||'').replace(/^Bewertung:\s*/,'').trim();
    const imp=r.improvement?`<div class="suggest">💡 ${escHtml(r.improvement)}</div>`:'';
    return`<tr><td><div class="status-dot ${r.status}">${sym}</div></td><td><div class="crit-id">${escHtml(r.id)}</div><div class="crit-name">${escHtml(crit.name)}</div><div class="crit-cat">${escHtml(crit.cat)}</div><div class="crit-ref">${escHtml(crit.ref||r.sqeg_ref||'')}</div></td><td>${beleg?`<div class="finding-beleg">Beleg: ${escHtml(beleg)}</div>`:''} ${rule?`<div class="finding-rule">Regel: ${escHtml(rule)}</div>`:''} ${verdict?`<div class="finding-verdict">Bewertung: ${escHtml(verdict)}</div>`:''} ${imp}</td></tr>`;
  }).join('');
}

function renderPqCards(){
  const c=document.getElementById('pq-cards');
  if(!pqResults.length){c.innerHTML='<div style="color:var(--text3);font-size:13px;grid-column:1/-1">PQ-Erweitert wird nach der Analyse angezeigt.</div>';return}
  c.innerHTML=pqResults.map(r=>{
    const pq=PQ_CRITERIA.find(x=>x.id===r.id)||{name:r.name||r.id,ref:''};
    const sym=r.status==='green'?'✓':r.status==='amber'?'◑':'✗';
    return`<div class="pq-card"><div class="pq-card-header"><div class="status-dot ${r.status}">${sym}</div><div><div class="pq-card-id">${escHtml(r.id)} · ${escHtml(pq.ref)}</div><div class="pq-card-name">${escHtml(pq.name)}</div></div></div><div class="pq-card-body">${escHtml(r.finding||'')}${r.improvement?`<div class="suggest" style="margin-top:8px">💡 ${escHtml(r.improvement)}</div>`:''}</div></div>`;
  }).join('');
}

// === EXPORT ===
function exportHtml(){
  const score=calcScore(),level=scoreToLevel(score);
  const html=`<!DOCTYPE html><html lang="de"><head><meta charset="UTF-8"><title>SQEG Analyse – ${escHtml(currentUrl)}</title><style>body{font-family:sans-serif;max-width:900px;margin:40px auto;padding:0 20px;color:#1a1917}h1{font-size:22px}h2{font-size:16px;margin:24px 0 8px;border-bottom:1px solid #e3e2df;padding-bottom:6px}table{width:100%;border-collapse:collapse;margin-bottom:16px}th,td{text-align:left;padding:10px 12px;border:1px solid #e3e2df;font-size:13px}th{background:#f8f7f5;font-weight:700}.green{color:#15803d}.amber{color:#b45309}.red{color:#dc2626}.suggest{background:#f0f0ff;padding:6px 10px;border-left:3px solid #4338ca;margin-top:4px;font-size:12px}@media print{body{margin:0}}</style></head><body><h1>SQEG Analyse: ${escHtml(currentUrl)}</h1><p>Score: ${Math.round(score)}% · Stufe: ${escHtml(level)} · YMYL: ${escHtml(ymylResult||'none')} · ${new Date().toLocaleDateString('de-DE')}</p><h2>Kriterien c1–c29</h2><table><thead><tr><th>ID</th><th>Kriterium</th><th>Status</th><th>Befund</th><th>Verbesserung</th></tr></thead><tbody>${analysisResults.map(r=>`<tr><td>${escHtml(r.id)}</td><td>${escHtml(r.criterion||r.id)}</td><td class="${r.status}">${r.status}</td><td>${escHtml(r.finding||'')}</td><td>${r.improvement?`<div class="suggest">${escHtml(r.improvement)}</div>`:''}</td></tr>`).join('')}</tbody></table><h2>PQ-Erweitert e1–e7</h2><table><thead><tr><th>ID</th><th>Name</th><th>Status</th><th>Befund</th></tr></thead><tbody>${pqResults.map(r=>`<tr><td>${escHtml(r.id)}</td><td>${escHtml(r.name||r.id)}</td><td class="${r.status}">${r.status}</td><td>${escHtml(r.finding||'')}</td></tr>`).join('')}</tbody></table>${e8Result?`<h2>Needs Met (e8)</h2><p><strong>${escHtml(e8Result.rating)}</strong>: ${escHtml(e8Result.finding||'')}</p>`:''}</body></html>`;
  const w=window.open('','_blank');w.document.write(html);w.document.close();
}

// === SETTINGS ===
function toggleSettingsPw(id,btn){
  const inp=document.getElementById(id);
  if(inp.type==='password'){inp.type='text';btn.textContent='Verbergen'}else{inp.type='password';btn.textContent='Anzeigen'}
}
async function saveApiKey(e){
  e.preventDefault();
  const key=document.getElementById('s-apikey').value.trim();
  const errEl=document.getElementById('err-apikey'),msgEl=document.getElementById('msg-apikey');
  errEl.style.display='none';msgEl.style.display='none';
  const fd=new FormData();fd.append('action','save_api_key');fd.append('anthropic_api_key',key);fd.append('csrf_token',CSRF_TOKEN);
  try{
    const r=await fetch('settings_save.php',{method:'POST',body:fd});
    const d=await r.json();
    if(d.error){errEl.textContent=d.error;errEl.style.display='flex'}
    else{msgEl.style.display='block';document.getElementById('key-masked-display').textContent='API-Key ist hinterlegt.';setTimeout(()=>msgEl.style.display='none',3000)}
  }catch(err){errEl.textContent=err.message;errEl.style.display='flex'}
}
async function saveModel(e){
  e.preventDefault();
  const fd=new FormData();fd.append('action','save_model');fd.append('ai_model',document.getElementById('s-model').value);fd.append('csrf_token',CSRF_TOKEN);
  const r=await fetch('settings_save.php',{method:'POST',body:fd});
  const d=await r.json();
  const msg=document.getElementById('msg-model');
  if(d.success){msg.style.display='block';setTimeout(()=>msg.style.display='none',3000)}
}
async function savePassword(e){
  e.preventDefault();
  const pw=document.getElementById('s-pw').value,pw2=document.getElementById('s-pw2').value;
  const errEl=document.getElementById('err-password'),msgEl=document.getElementById('msg-password');
  errEl.style.display='none';msgEl.style.display='none';
  if(pw.length<8){errEl.textContent='Passwort muss mindestens 8 Zeichen lang sein.';errEl.style.display='flex';return}
  if(pw!==pw2){errEl.textContent='Passwörter stimmen nicht überein.';errEl.style.display='flex';return}
  const fd=new FormData();fd.append('action','save_password');fd.append('new_password',pw);fd.append('confirm_password',pw2);fd.append('csrf_token',CSRF_TOKEN);
  try{
    const r=await fetch('settings_save.php',{method:'POST',body:fd});
    const d=await r.json();
    if(d.error){errEl.textContent=d.error;errEl.style.display='flex'}
    else{msgEl.style.display='block';document.getElementById('s-pw').value='';document.getElementById('s-pw2').value='';setTimeout(()=>msgEl.style.display='none',3000)}
  }catch(err){errEl.textContent=err.message;errEl.style.display='flex'}
}
</script>
</body>
</html>
