# GrimbaNews — High-Contrast Accessibility Theme Design

**Status:** design v0 (no separate high-contrast theme shipped; baseline contrast already AAA on both light + dark)
**Owner:** Nina Patel (Lead Frontend) on token + CSS architecture + Alex Morgan (UI/UX) on visual review + Maya Patel (Security & Compliance) on WCAG sign-off
**Walks:** Mythos S779 (high-contrast mode) deferred → partial
**Gating dependency:** Vader call on whether dedicated high-contrast theme is in scope before launch (baseline already passes AAA, so the marginal accessibility win is for low-vision readers who use a forced OS-level high-contrast mode). Theme itself is operator-side CSS work.

## Why this exists

S779 was honest-deferred as "contrast already AAA (13.7:1 light, 16.4:1 dark); separate high-contrast theme deferred." That deferral is correct on baseline-WCAG grounds — current `light` and `dark` modes both clear AAA on body text (7:1 minimum), and AA-Large on every UI surface. But high-contrast goes beyond AAA: it is a distinct accessibility surface intended for users who run OS-level forced-colors mode (Windows Forced Colors, macOS Increase Contrast) and need a theme that **respects `prefers-contrast: more`**, not just one that scores well on Lighthouse.

This document walks the row from deferred (no plan) to partial (design exists, token map drafted, ready for ship sprint when Vader picks an a11y window).

## Current baseline

Existing tokens (per `resources/css/grimba-tokens.css`):

- Light: `--grimba-fg: #0a0a0f` on `--grimba-bg: #ffffff` = 19.4:1 (AAA)
- Dark: `--grimba-fg: #f4f4f7` on `--grimba-bg: #0a0a0f` = 18.1:1 (AAA)
- Accent rails: `--grimba-accent` paired with surfaces clears 4.5:1 on body and 3:1 on large headings.

These are excellent baselines. High-contrast mode goes further: **strip every gradient, remove every shadow, force every border to pure foreground color, force every interactive surface to system-default `Highlight` / `HighlightText` tokens when `forced-colors: active`.**

## High-contrast theme principles

1. **Honor `prefers-contrast: more`** — auto-flip when the OS requests it; do not require manual toggle.
2. **Honor `forced-colors: active`** — Windows / Edge high-contrast mode wins; respect `CanvasText`, `Canvas`, `LinkText`, `ButtonText`, `Highlight`, `HighlightText` system tokens.
3. **No information conveyed by color alone** — bias bars get patterns + text labels, not just hue; freshness pills get text + icon, not just dot color.
4. **Borders become structural** — replace shadow-defined card boundaries with `border: 2px solid currentColor`.
5. **Underline every link** — current design uses hover-only underline on body links; high-contrast forces always-underline.
6. **Focus rings stay 3px AAA** — already AAA; no degradation.

## Token map (when shipped)

```css
@media (prefers-contrast: more), (forced-colors: active) {
  :root {
    --grimba-fg: CanvasText;
    --grimba-bg: Canvas;
    --grimba-accent: LinkText;
    --grimba-accent-fg: HighlightText;
    --grimba-surface: Canvas;
    --grimba-border: CanvasText;
    --grimba-shadow: none;
    --grimba-gradient: none;
    --grimba-pill-bg: Canvas;
    --grimba-pill-fg: CanvasText;
    --grimba-pill-border: CanvasText;
  }

  /* Strip shadows + gradients */
  .grimba-card,
  .grimba-rail,
  .grimba-modal { box-shadow: none !important; background-image: none !important; }

  /* Borders replace shadow-defined boundaries */
  .grimba-card { border: 2px solid var(--grimba-border); }

  /* Underline every link */
  a { text-decoration: underline; }

  /* Bias bars carry patterns, not just color */
  .grimba-bias-bar[data-bias='left']   { background-image: repeating-linear-gradient(45deg, transparent 0 2px, currentColor 2px 4px); }
  .grimba-bias-bar[data-bias='center'] { background-image: repeating-linear-gradient(0deg,  transparent 0 2px, currentColor 2px 4px); }
  .grimba-bias-bar[data-bias='right']  { background-image: repeating-linear-gradient(-45deg, transparent 0 2px, currentColor 2px 4px); }
}
```

## Surfaces requiring audit

- Bias distribution bar (article + dossier)
- Source factuality / credibility pills
- Freshness staleness pills (Frais / Récent / Daté)
- Translation indicators (badge sur card)
- Saved-search digest cards
- Vault entry cards
- Cookie consent banner (must never disappear under high-contrast)
- AdSense / direct-sponsor surfaces (Google's AdSense honors `forced-colors`; direct-sponsor surfaces need our own pass)

## Acceptance gates

1. Toggle Chrome → DevTools → Rendering → Emulate CSS media feature `prefers-contrast: more` → every Grimba surface remains readable.
2. Windows VM → enable High Contrast Black → every Grimba surface remains usable.
3. macOS → Settings → Accessibility → Display → Increase Contrast → every surface respects the boost.
4. axe-core run with `bestpractice` ruleset returns zero new issues.
5. Manual screen-reader pass (NVDA on Windows, VoiceOver on macOS) confirms semantic structure intact.

## Things deliberately NOT in this design

- **A standalone toggle in /account** — operating-system-driven detection covers the use case without adding settings UI noise. Power users can override via stylesheet extension; we don't ship a in-app theme picker.
- **A printable / e-ink theme variant** — separate concern; lands with the future "reader mode" S-band.
- **Dark-mode-only contrast boost** — `prefers-contrast: more` applies to both light + dark; no asymmetry.

## Cross-references

- Master plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S779 row)
- Sister docs: `docs/GRIMBANEWS_FONT_SCALING_A11Y_MATRIX.md` (font-scale matrix is the sister accessibility surface), `docs/GRIMBANEWS_UI_DARK_LIGHT_55_SPRINTS.md` (baseline dark/light theme work)
- Existing token surface: `resources/css/grimba-tokens.css`, `resources/css/grimba-chrome.css`
- Existing accessibility surface: `tests/e2e/grimbanews-keyboard-navigation.cjs`, `tests/Feature/GrimbaA11y*Test.php`
