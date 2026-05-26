# GrimbaNews — Reading Mode Design

**Status:** design v0 (no ?reading=1 variant; default article view is already content-first)
**Owner:** Steve Jobs (CPO) on UX + Nina Patel (Lead FE) on implementation
**Walks:** Mythos S1571 (reading mode — design) deferred → partial
**Gating dependency:** Design sign-off (operator-side). Implementation gates on `partials/post-hero-img.blade.php` extension + per-component a11y review per `docs/GRIMBANEWS_ADMIN_VISUAL_REGRESSION_ROUTES.md`.

## Why this exists

S1571 was honest-deferred with the note "current article view is already content-first." That's true — `partials/post-hero-img.blade.php` + the reading-time chip already trim chrome. But "reading mode" as peers ship it (Safari Reader, Mozilla Readability, Pocket Article View) does more: removes sidebar, scales typography per reader pick, neutralizes ambient color, hides reactions / sharing during read. This document defines the GrimbaNews variant so the moment Steve signs off it's a straight implementation task, not a fresh design pass.

## Today's article view

- `views/post.blade.php` renders article with: header chrome (nav + locale switcher), bias-bar chip, reading-time chip, hero image, body, related dossiers, share kit, comments anchor (when comments ship per `docs/GRIMBANEWS_COMMENT_V2_DESIGN.md`), footer chrome.
- **Reading-time chip** (per `App\Support\GrimbaReadingTime` calc) is the only "reading-mode-adjacent" affordance.
- **No font scaling controls** (per S1572 partial).
- **No reading mode toggle.**
- **No reader-set color scheme override.** Theme = dark / light from system + manual toggle.

## Reading-mode goal

A "Reading mode" toggle that, when active:

1. Hides global navigation, sidebar, share kit, comments anchor.
2. Constrains content to single-column max-width (~ 680px / 38em).
3. Lifts body typography to reader-preferred scale (S1572 dependency).
4. Suppresses ambient distractions (decorative images, animated chips, bias-bar mini-chart).
5. Persists toggle state in cookie (`grimba_reading_mode=on`) for return visits.
6. Provides a single exit affordance (top-right close X, plus Esc key).
7. Remains accessible (keyboard-navigable, screen-reader friendly, no focus traps).

## Trigger affordances

- **Article header button** — "Reading mode" icon (book / focus glyph). Tooltip "Lecture concentrée" (FR) / "Focus reading" (EN).
- **Keyboard shortcut** — `r` (when no input focused). Optional shortcut overlay per S1577 deferred.
- **Per-locale string keys** added to `lang/{fr,en}/grimba.php`.

## Reading-mode layout

```
+----------------------------------------+
|                                   [X]  |  <- close affordance only
|                                        |
|         Article headline               |
|         · Author · Date · 8 min read   |
|                                        |
|         (hero image — optional toggle) |
|                                        |
|         Article body                   |
|         single column 680px max        |
|         reader-preferred font scale    |
|         reader-preferred theme         |
|                                        |
|         Source citations               |
|                                        |
|         [exit reading mode]            |
+----------------------------------------+
```

## Hidden in reading mode

- Global nav + sub-nav.
- Sidebar (already hidden on mobile).
- Bias-bar chip (link out to `/explainer-bias-bar` instead, per `docs/GRIMBANEWS_BIAS_BAR_TUTORIAL_OVERLAY_DESIGN.md`).
- Related dossiers rail.
- Share kit (collapsed to single button).
- Reactions (when comments ship).
- Ad slots (per `App\Support\GrimbaAds::shouldRender()` extension — reading mode disables).
- Newsletter signup widget.

## Preserved in reading mode

- Article body + media.
- Author + date + reading time.
- Source citations (`posts.source_citations`).
- "Powered by NobuAI" footer if summary or translation is NobuAI-touched.
- Original-source link.

## Persistence

- **Cookie:** `grimba_reading_mode=on` (1-year TTL), session-scoped fallback if cookies declined.
- **Server-side respect:** middleware reads cookie, sets `$layout = 'reading'` on article routes.
- **Per-locale persistence:** cookie applies across both FR and EN routes.

## Accessibility

- **Focus trap NOT used** (reading mode is page-level, not modal).
- **Skip-link** to article body preserved (per `partials/focus-manager.blade.php`).
- **High-contrast theme** honored (per S1575 partial).
- **Reduced-motion** honored (per `prefers-reduced-motion` media query).
- **Screen-reader announcement** on toggle: "Reading mode enabled. Press Escape to exit."
- **Keyboard:** Tab order preserves article semantic flow.
- **Esc key** exits reading mode.

## Interaction with sister specs

- **Font scaling controls (S1572)** — A−/A+ buttons live in reading mode header; outside reading mode keep browser-default zoom.
- **Dyslexia-friendly font (S1573)** — toggle inside reading mode preferences; sticky cookie.
- **Line-spacing controls (S1574)** — same.
- **High-contrast (S1575)** — same.
- **Dark / light theme** — existing toggle preserved.
- **Keyboard shortcuts (S1577)** — `r` for reading mode added to shortcut list.

## Reader preferences panel

Reading mode introduces a small **reader-preferences panel** (gear icon in reading-mode header):

```
+--------------------------------+
|  Reading Preferences           |
|  --------                      |
|  Font scale    [-] [A] [+]    |
|  Font family   ( ) Default     |
|                ( ) Atkinson    |  (S1573)
|                ( ) OpenDyslexic|
|  Line spacing  ( ) Normal      |  (S1574)
|                ( ) Loose       |
|  Theme         ( ) System      |
|                ( ) Light       |
|                ( ) Dark        |
|                ( ) High contrast|  (S1575)
+--------------------------------+
```

All preferences persist via cookie. Each preference is honest about cookie footprint:
- `grimba_reading_font_scale` (0-9 enum)
- `grimba_reading_font_family` (default | atkinson | opendyslexic)
- `grimba_reading_line_spacing` (normal | loose)
- `grimba_reading_theme` (system | light | dark | high-contrast)

Total footprint: 5 cookies, all small (< 30 bytes each), purpose-bound. Documented in `docs/GRIMBANEWS_CONSENT_LOG_DESIGN.md` next refresh.

## Engineering effort estimate

- Layout variant + toggle: 2 sprints.
- Reader-preferences panel (S1572 + S1573 + S1574 + S1575 wired through here): 3 sprints.
- Cookie persistence + middleware: 0.5 sprint.
- Keyboard shortcut + a11y pass: 1 sprint.
- Per-locale string keys: 0.5 sprint.
- Tests + visual regression: 1 sprint.
- **Full ship: ~8 sprints.**

## Cross-references

- Master plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1571; sister rows S1572-S1577)
- Sister docs: `docs/GRIMBANEWS_FONT_SCALING_A11Y_MATRIX.md`, `docs/GRIMBANEWS_BIAS_BAR_TUTORIAL_OVERLAY_DESIGN.md`, `docs/GRIMBANEWS_ADMIN_VISUAL_REGRESSION_ROUTES.md`, `docs/GRIMBANEWS_CONSENT_LOG_DESIGN.md`
- Reading-time helper: `app/Support/GrimbaReadingTime.php` (if shipped) + reading-time chip
- Existing post view: `platform/themes/echo/views/post.blade.php`, `partials/post-hero-img.blade.php`
- Focus manager: `partials/focus-manager.blade.php`
- Iboga roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
