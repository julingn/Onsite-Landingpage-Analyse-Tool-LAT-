# LAT — Must Read

> **Pflege-Regel:** Nach jedem Deploy, der Design, Struktur, Roadmap oder kritischen Code betrifft,
> müssen dieses Dokument UND `/memories/repo/must-read.md` aktualisiert werden.

---

## Projekt

| | |
|---|---|
| **Repo** | https://github.com/julingn/LAT-Landingpage-Analyse-Tool |
| **Branch** | `main` → auto-deploy Railway |
| **Stack** | PHP 8.3 CLI Alpine, kein Framework |
| **Kern** | `app/index.php` (~1750 Zeilen — PHP + HTML + CSS + JS) |
| **Letzter Deploy** | `e6890ff` — Sistrix Settings-UI entfernt |

---

## API-Key-Verwaltung — KRITISCH

### Regel: Wer bekommt ein Settings-UI?

| Typ | Settings-UI | Railway ENV |
|---|---|---|
| Anthropic API-Key | ✅ | ✅ |
| OpenAI API-Key / Modell | ✅ | ✅ |
| Login-Passwort | ✅ | ✅ |
| **DataForSEO** | ❌ | ✅ `DATAFORSEO_LOGIN` / `DATAFORSEO_PASSWORD` |
| **PageSpeed** | ❌ | ✅ `PAGESPEED_API_KEY` |
| **Google Search Console** | ❌ | ✅ `GSC_SERVICE_ACCOUNT_JSON` / `GSC_SITE_URL` |
| **Sistrix** | ❌ | ✅ `SISTRIX_API_KEY` |

**→ Alle externen Datenquellen laufen ausschließlich über Railway-Umgebungsvariablen.**

### Config-Muster (`app/config.php`)

```php
define('CFG_XYZ', cfg('ENV_KEY', 'settings_json_key'));
```

`cfg()` priorisiert: **ENV → settings.json → default**

---

## PHP-Proxy-Muster

Alle API-Calls laufen serverseitig — der Browser sieht nie einen API-Key.

**Struktur jedes Proxys:**
1. `session_start()` + Login-Check → 401
2. CSRF-Token-Validierung → 403
3. `require_once config.php` → Credentials aus `CFG_*`
4. cURL-Request → JSON-Response

**Bestehende Proxys:**

| Datei | Zweck |
|---|---|
| `app/dataforseo.php` | SERP + Backlinks |
| `app/gsc.php` | Google Search Console |
| `app/pagespeed.php` | PageSpeed Insights |
| `app/sistrix.php` | URL-Sichtbarkeit + Keywords (DE) |

**Referenz-Template:** `app/dataforseo.php`

---

## Daten-Flow in `app/index.php`

```js
// 1. Globale Variablen (Zeile ~894)
let gscData=null, serpData=null, backlinkData=null, psiData=null, sistrixData=null;

// 2. Reset bei jedem Start (startAnalysis + startDemo)
gscData=null; serpData=null; backlinkData=null; psiData=null; sistrixData=null;

// 3. Parallel fetchen
const [gscRes, serpRes, blRes, psiRes, sistrixRes] = await Promise.allSettled([
  fetchGscData(url),
  fetchSerpData(keyword),
  fetchBacklinkData(url),
  fetchPageSpeedData(url),
  fetchSistrixData(url),
]);

// 4. Rendern
renderResults() → rendert alle Panels
```

---

## Settings-Panel Struktur (`app/index.php`)

1. Anthropic API-Key
2. KI-Modell (Anthropic / OpenAI)
3. Login-Passwort ändern
4. Darstellung (Dark Mode)
5. Entwickler-Optionen (Demo-Button)

**→ Keine neuen Sections ohne explizite Anfrage hinzufügen.**

---

## CSS-Design-System

- **Nur `var(--*)` verwenden** — keine hardcodierten Farben
- **Light-Mode** (`:root`): `--bg`, `--bg2`, `--bg3`, `--bg4`, `--border`, `--border2`, `--text`, `--text2`, `--text3`, `--accent`, `--accent2`, `--accent-bg`, `--accent-border`
- **Dark-Mode** (`[data-theme="dark"]`): Navy-Palette — `--bg:#0D1525`, `--bg2:#172035`, `--bg3:#09111D` etc. + `--green`, `--amber`, `--red`, `--blue`
- **Fonts:** Inter (UI), Geist Mono (Mono/Code)
- **FOUC-Prevention:** Inline-`<script>` im `<head>` liest `lat_theme` aus localStorage vor erstem Paint

---

## Ergebnisstruktur (Results-Section)

| Reihenfolge | Element | ID/Klasse |
|---|---|---|
| 1 | Gesamtscore | `.score-hero` |
| 2 | KI-Executive Summary | `.exec-summary-card` |
| 3 | Stat-Grid (Grün/Amber/Rot) | `.stat-grid` — 3 Spalten, kein PQ-Erweitert |
| 4 | Cluster-Übersicht | `#cluster-overview` |
| 5 | SQEG-Scale | `.sqeg-scale` |
| 6 | Needs-Met-Block | `.needs-met-block` |
| 7 | GSC-Keywords | `#gsc-panel` |
| 8 | Sistrix URL-Sichtbarkeit | `#sistrix-panel` |
| 9 | Priority-Matrix | `#priority-matrix` |
| 10 | Kriterien-Tabelle | `#criteria-table` |

---

## Roadmap / Offene Punkte

- [x] Visuelle Hierarchie Runde 2 — `aa34eba`
- [x] Dark Mode (Navy-Tokens) — `d08c4bb`
- [x] Input Hero (sticky, kondensiert) — `cb41b2b`
- [x] PQ-Erweitert entfernt (Duplikat) — `b2414b3`
- [x] Sistrix Integration — `e6890ff` → KEY `SISTRIX_API_KEY` in Railway setzen
- [ ] ...

---

## Bekannte Fallstricke

| Problem | Lösung |
|---|---|
| Dark Mode FOUC | Inline-`<script>` im `<head>` — **vor** CSS-Load |
| Settings-UI für Datenquellen | **Nicht machen** — nur Railway ENV |
| Hardcoded Farben | **Nicht machen** — immer `var(--)` |
| PQ-Erweitert war Duplikat | Entfernt — war nur Re-Render von Cluster 5 |
