# GrimbaNews — RTL (Right-to-Left) Chrome Support Plan

**Status:** plan v0 (manifest hard-codes `dir=ltr`; layouts need token audit)
**Owner:** Nina Patel (Lead Frontend) on token + layout pass + Alex Morgan (UI/UX) on visual review
**Walks:** Mythos S1142 (per-locale RTL support for AR/HE) deferred → partial
**Gating dependency:** AR or HE catalog (S1132 / S1137) ships first; RTL chrome must ship before any RTL locale lands.

## Why this exists

S1142 honest-deferred as "manifest hard-codes dir=ltr; layouts need token audit." That's correct. RTL chrome must ship in advance of any RTL locale (AR S1132, HE S1137). This doc captures the audit + token plan.

## RTL principles

1. **Logical properties first**: replace `margin-left` / `margin-right` with `margin-inline-start` / `margin-inline-end`; replace `padding-left` etc. with `padding-inline-*`; replace `text-align: left` with `text-align: start`.
2. **Automatic flip via `dir="rtl"`**: setting `dir` on `<html>` should propagate without per-component overrides.
3. **Mirror, don't mistranslate**: arrows, navigation chevrons, sliders flip; icons that have intrinsic LTR meaning (clock, magnifying glass, brand logo) do not flip.
4. **Numbers stay LTR**: numerals + percent signs + currency symbols render LTR even inside RTL contexts via Unicode bidi.
5. **Mixed-direction text**: brand "GrimbaNews" in Arabic body text relies on Unicode bidi algorithm + occasional `&lrm;` / `&rlm;` overrides if needed.

## Audit surfaces

### resources/css/grimba-chrome.css

- Replace ~25 occurrences of `left:`/`right:` with `inset-inline-start:` / `inset-inline-end:`
- Replace ~40 occurrences of `margin-left:`/`margin-right:` with `margin-inline-*`
- Replace ~30 occurrences of `padding-left:`/`padding-right:` with `padding-inline-*`
- Replace ~15 occurrences of `text-align: left|right` with `text-align: start|end`
- Replace ~10 occurrences of `float: left|right` with `float: inline-start|inline-end`

### resources/css/grimba-tokens.css

- Add RTL-aware tokens: `--grimba-arrow-forward`, `--grimba-arrow-back` (logical, flip based on dir)

### resources/views/layouts/grimba-chrome.blade.php

- `<html lang="{{ app()->getLocale() }}" dir="{{ in_array(app()->getLocale(), ['ar','he']) ? 'rtl' : 'ltr' }}">`
- Currently hardcoded `dir="ltr"`; needs middleware-driven detection

### public/grimba-manifest.json

- `dir` field currently hardcoded; needs per-locale manifest (or omit and let browser resolve from lang)

## Component-level audit

| Component | RTL action | Notes |
|---|---|---|
| Header nav | Mirror nav order | Logo stays left in LTR sites, becomes right anchor in RTL |
| Bias bar | No flip (graph stays semantic) | Bias L/center/R retains visual L/center/R per bias-bar definition |
| Article card | Full mirror | Title + image + meta all flip |
| Freshness pill | Pill text mirrors | Numeric date stays LTR |
| Source rail | Full mirror | Source logos do not flip (intrinsic LTR) |
| Search box | Magnifying glass on inline-end | Currently inline-start |
| Cookie consent banner | Full mirror | Buttons swap |
| Footer | Full mirror | Per-column order flips |
| Dossier voices | Per-source rail mirrors | Voice ordering stays L/center/R |

## Acceptance gates

1. Toggle DevTools → emulate `dir="rtl"` on `<html>` → chrome remains usable.
2. Playwright RTL smoke (when AR or HE catalog ships): navigate `/ar`, verify card layout mirrored.
3. axe-core RTL scan = zero violations.
4. Manual visual review by Alex Morgan on staging before any RTL locale launches.
5. `prefers-color-scheme: dark` + `dir="rtl"` combination renders correctly (cross-product matrix).

## Things deliberately NOT in this plan

- **Per-component opt-out of mirroring** — single global flip; if a component needs intrinsic LTR (like a numeric chart), it gets its own `dir="ltr"` wrapper.
- **Animation flip** — slide-in animations get inline-start/end equivalents; no per-keyframe RTL variants.
- **Print stylesheet RTL** — not in scope; print rare on news sites.

## Cross-references

- Master plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1142 row)
- Sister docs: `docs/GRIMBANEWS_AR_LOCALE_CATALOG_PLAN.md`, `docs/GRIMBANEWS_HE_LOCALE_CATALOG_PLAN.md`, `docs/GRIMBANEWS_FONT_SCALING_A11Y_MATRIX.md`, `docs/GRIMBANEWS_HIGH_CONTRAST_THEME_DESIGN.md`
- Existing infrastructure: `resources/css/grimba-chrome.css`, `resources/css/grimba-tokens.css`, `resources/views/layouts/grimba-chrome.blade.php`, `public/grimba-manifest.json`
