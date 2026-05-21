# LAT Design System
**evalu-pro · SQEG Analyzer**
**Stand:** Mai 2026 · Version 2.0
**Dieses Dokument wird bei jeder Design-Änderung aktualisiert.**

---

## 1 · Philosophie

Professionelles B2B-SaaS-Design: Cool, crisp, datenorientiert. Keine Ablenkung vom Analyseergebnis. Visuelles Gewicht folgt der Informationshierarchie (Score → Prioritäten → Detail).

---

## 2 · Farbpalette

### 2.1 Hintergrund & Oberfläche

| Token | Hex | Verwendung |
|-------|-----|------------|
| `--bg` | `#F8FAFC` | Seitenhintergrund (Slate-50) |
| `--bg2` | `#FFFFFF` | Card-Hintergrund |
| `--bg3` | `#F1F5F9` | Sekundäre Flächen, Table-Header |
| `--bg4` | `#E2E8F0` | Hover-Zustände |

### 2.2 Borders

| Token | Hex | Verwendung |
|-------|-----|------------|
| `--border` | `#E2E8F0` | Standard-Trennlinie (Slate-200) |
| `--border2` | `#CBD5E1` | Inputs, betonte Trennlinie (Slate-300) |

### 2.3 Text

| Token | Hex | Verwendung |
|-------|-----|------------|
| `--text` | `#0F172A` | Primärtext (Slate-900) |
| `--text2` | `#475569` | Sekundärtext (Slate-600) |
| `--text3` | `#94A3B8` | Placeholder, Labels, Meta (Slate-400) |

### 2.4 Accent (Brand)

| Token | Hex | Verwendung |
|-------|-----|------------|
| `--accent` | `#4F46E5` | CTAs, Active States (Indigo-600) |
| `--accent2` | `#4338CA` | Hover (Indigo-700) |
| `--accent-bg` | `#EEF2FF` | Accent-Flächen (Indigo-50) |
| `--accent-border` | `#C7D2FE` | Accent-Border (Indigo-200) |

### 2.5 System-Farben (WCAG 2.1 AA)

| Status | Hex | bg | border |
|--------|-----|-----|--------|
| Green | `#16A34A` | `#F0FDF4` | `#BBF7D0` |
| Amber | `#D97706` | `#FFFBEB` | `#FDE68A` |
| Red | `#DC2626` | `#FEF2F2` | `#FECACA` |
| Blue | `#2563EB` | `#EFF6FF` | `#BFDBFE` |

---

## 3 · Typografie

### 3.1 Schriftarten

| Rolle | Familie | Quelle |
|-------|---------|--------|
| UI / Body | **Inter** | Google Fonts |
| Monospace | **Geist Mono** | Vercel (CDN: `r2.vercel-storage.com`) |
| *(Entfernt)* | ~~Bricolage Grotesque~~ | Ersetzt durch Inter 700 |
| *(Entfernt)* | ~~DM Sans~~ | Ersetzt durch Inter |
| *(Entfernt)* | ~~DM Mono~~ | Ersetzt durch Geist Mono |

### 3.2 Type Scale

| Token | Size | Weight | Line-Height | Verwendung |
|-------|------|--------|-------------|------------|
| h1 | 24px | 700 | 1.25 | Score-Headline, Page Title |
| h2 | 18px | 700 | 1.3 | Card Title |
| h3 | 14px | 600 | 1.4 | Section Label, Table Header |
| body | 14px | 400 | 1.6 | Fließtext, Befund |
| sm | 13px | 400 | 1.5 | Meta, Labels |
| xs | 11px | 600 | 1.4 | Tags, Badges (Uppercase) |
| mono | 12px | 400 | 1.6 | URLs, IDs, Code |

---

## 4 · Spacing

Basis: **8px-Grid** (4px für Micro-Spacing)

| Token | Wert |
|-------|------|
| `--s-1` | 4px |
| `--s-2` | 8px |
| `--s-3` | 12px |
| `--s-4` | 16px |
| `--s-5` | 20px |
| `--s-6` | 24px |
| `--s-8` | 32px |
| `--s-10` | 40px |

Container: `max-width: 960px`, Sidebar: `220px`, Content-Padding: `32px`

---

## 5 · Border-Radius

| Token | Wert | Verwendung |
|-------|------|------------|
| `--radius-sm` | `6px` | Badges, Tags, kleine Chips |
| `--radius` | `8px` | Buttons, Inputs, Standard-Cards |
| `--radius-lg` | `12px` | Haupt-Cards, Panels |
| `--radius-xl` | `16px` | Score-Block, Hero-Cards |

---

## 6 · Shadows (Layering-System)

| Token | Wert | Verwendung |
|-------|------|------------|
| `--shadow-sm` | `0 1px 2px rgba(15,23,42,.05)` | Subtle Cards |
| `--shadow` | `0 1px 4px rgba(15,23,42,.08), 0 0 0 1px rgba(15,23,42,.04)` | Standard Cards |
| `--shadow-md` | `0 4px 12px rgba(15,23,42,.10), 0 0 0 1px rgba(15,23,42,.04)` | Hover, Elevated |
| `--shadow-lg` | `0 8px 24px rgba(15,23,42,.12)` | Modals, Popovers |

---

## 7 · Komponenten

### 7.1 Buttons

| State | Beschreibung |
|-------|-------------|
| Default | `bg=--accent`, `shadow-sm`, `radius=8px` |
| Hover | `bg=--accent2`, `translateY(-1px)`, `shadow-md` |
| Active | `bg=#3730A3`, `translateY(0)`, `shadow-sm` |
| Focus | `outline: 3px solid --accent-border`, `outline-offset: 2px` |
| Disabled | `bg=--bg4`, `color=--text3`, `cursor: not-allowed` |

### 7.2 Score-Block (Hero)

Prominente Karte oben in den Ergebnissen:
- Score-Zahl: **64px, Inter 700**, coloriert nach Niveau
- SQEG-Level: **Pill-Badge** rechts vom Score
- Sekundäre Infos: YMYL-Status · Kriterien-Anzahl · Analysezeit
- Progress-Bar unter der Zahl für visuelle Stärke

### 7.3 Kriterien-Tabelle

- Zeilen **standardmäßig kompakt** (nur Status + Kriterienname + Kurzfazit)
- **Click-to-Expand**: Vollständiger Befund (Beleg, Regel, Bewertung, Verbesserungsvorschlag) klappt auf
- Expanded-State: hellblauer Hintergrund, Befund als strukturiertes Layout

### 7.4 Skeleton Screens

Während Analyse läuft: Platzhalter-Blöcke für Stat-Grid und Score-Badge:
```css
.skeleton {
  background: linear-gradient(90deg, #F1F5F9 25%, #E2E8F0 50%, #F1F5F9 75%);
  background-size: 200% 100%;
  animation: skeleton-wave 1.4s ease-in-out infinite;
}
@keyframes skeleton-wave {
  0% { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}
```

### 7.5 Prioritäts-Matrix

- Gewichtungs-Punkte (●) als visuelle Stärke neben jedem Item
- Farbcodierte Spalten-Header
- Effort-Badge rechtsbündig pro Item

---

## 8 · Icons

**Bibliothek:** Lucide Icons (Inline SVG, `stroke-width: 1.75`, konsistente `16×16px` Größe)
CDN: `https://unpkg.com/lucide@latest`

---

## 9 · Changelog

| Version | Datum | Änderungen |
|---------|-------|------------|
| 2.0 | Mai 2026 | Initialer Design-System-Entwurf: Slate-Palette, Inter, Score-Hero, Expand-Rows, Skeleton |
