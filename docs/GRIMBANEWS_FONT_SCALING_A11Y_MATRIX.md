# GrimbaNews — Font Scaling + A11y Matrix

**Status:** matrix v0 (browser-default zoom + rem-based stack today; explicit controls deferred)
**Owner:** Steve Jobs (CPO) on UX + Nina Patel (Lead FE) on implementation + Sara Kim (QA) on a11y testing
**Walks:** Mythos S1573 (dyslexia-friendly font), S1574 (line-spacing controls) deferred → partial
**Gating dependency:** Reading mode shell (per `docs/GRIMBANEWS_READING_MODE_DESIGN.md`) to host the controls + per-font license review.

## Why this exists

S1573 + S1574 share a root: GrimbaNews ships **one font stack** (Public Sans body + Fraunces display, rem-based) and relies on browser-default zoom for scaling. Peer publishers offering reading-mode controls (Pocket, Instapaper, NYT iOS app) ship explicit font-family + line-spacing pickers. This document defines the matrix of font + scale + spacing combinations so engineering can ship the picker without re-discovering trade-offs per combination.

## Today's baseline (locked by tests)

- **Body:** Public Sans, system-stack fallback. 16px base. 1.6 line-height. Defined in `resources/sass/**/*.scss`.
- **Display:** Fraunces. Headers only.
- **rem-based scaling** respects browser-zoom + user-set base font size.
- **Dark / light parity** locked by `GrimbaDarkModeContractTest`.
- **A11y baseline** locked by `tests/e2e/grimbanews-keyboard-navigation.cjs` + `docs/GRIMBANEWS_ADMIN_VISUAL_REGRESSION_ROUTES.md`.

## Proposed font picker (S1573)

Three families. All open-licensed:

| Family | License | Lang coverage | Rationale |
|---|---|---|---|
| Public Sans (default) | OFL | Latin + Latin Ext + Cyrillic | Already the body stack |
| Atkinson Hyperlegible | OFL (Braille Institute) | Latin + Latin Ext + Cyrillic + Greek | Designed for low-vision; high letter-disambiguation |
| OpenDyslexic | Bitstream Vera | Latin + Latin Ext | Heavier baseline weight; designed to reduce dyslexic letter-flip |

All three ship as **self-hosted woff2** (per security headers — no external font CDN); served from `public/fonts/`. License files committed to `public/fonts/LICENSES/`.

## Proposed scale picker (S1572 + S1573 hand-off)

7-step scale, rem-based. Default = step 4.

| Step | Body | Display | Notes |
|---|---|---|---|
| 1 | 0.875rem | 1.5rem | Compact |
| 2 | 0.9375rem | 1.75rem | |
| 3 | 0.9375rem | 1.875rem | |
| 4 | 1rem | 2rem | DEFAULT |
| 5 | 1.125rem | 2.25rem | |
| 6 | 1.25rem | 2.5rem | |
| 7 | 1.5rem | 2.875rem | Maximum (preserves mobile readability) |

Reading-mode header offers A− / A / A+ shortcut (jumps ±1 step) + dropdown for direct step pick.

## Proposed line-spacing picker (S1574)

Two values:

| Setting | Line height | Paragraph spacing | Notes |
|---|---|---|---|
| Normal | 1.6 | 1em | DEFAULT (today's value) |
| Loose | 2.0 | 1.5em | Cognitive-load reduction |

(Not three values — Reddit-research-style pickers with 5 options become noise. Two genuine choices.)

## Compatibility matrix

Per (font × scale × spacing) — flag combinations that may fail a11y or break layout:

| Font | Scale | Spacing | Notes / Risks |
|---|---|---|---|
| Public Sans | 1-7 | Normal or Loose | ✅ Safe |
| Atkinson | 1-7 | Normal or Loose | ✅ Safe (slightly wider; verify long URL wraps) |
| OpenDyslexic | 1-4 | Normal or Loose | ✅ Safe |
| OpenDyslexic | 5-7 | Normal or Loose | ⚠️ Display headers may cap at 6 to prevent overflow on narrow mobile |

## Per-component verification matrix

Pages that need per-(font × scale × spacing) visual-regression coverage:

- `/` home (rails)
- `/{post}` article body
- `/dossier/{cluster}` cluster page
- `/search` (long input + facets)
- `/local` (cards)
- `/methodologie` (long-form text)
- `/transparence` (when shipped per `docs/GRIMBANEWS_TRANSPARENCY_REPORT_SCOPE.md`)
- `/pour-vous` (personalized rail)
- `/coffre` (vault)
- Reading mode itself

Per matrix: 10 routes × 3 fonts × 7 scales × 2 spacings = **420 combinations**. Sampled smoke pass = 10 routes × 3 fonts × {1, 4, 7} scales × {normal, loose} = **180 verifications**. Lighthouse + axe-core scan on 60 of these (sampled).

## Per-locale rendering check

- FR-FR + FR-CA (Quebec accent presence) — verify diacritics render on each font.
- Hreflang routing locked by `GrimbaLocaleEnforce`.
- Pt-BR + ES if those locales ship (not yet — per `docs/GRIMBANEWS_LEGAL_PAGES_LOCALIZATION_MATRIX.md`).

## A11y test matrix

| Check | Tool | Coverage |
|---|---|---|
| Color contrast (text vs background) | axe-core | All 3 themes × all 2 spacings |
| Focus ring visibility | manual + axe-core | All 3 themes |
| Keyboard navigation through preferences panel | tests/e2e/grimbanews-keyboard-navigation.cjs | Add coverage |
| Screen-reader announcement on preference change | manual VoiceOver + NVDA | Sampled |
| Reduced-motion preservation | axe-core | All combinations |
| Text reflow at 320px viewport with step 7 | manual | Per font |

## Performance budget

- Each woff2 face ~ 30-50 KB compressed.
- **Lazy-load** non-default fonts (Atkinson, OpenDyslexic) — only fetch when user selects.
- **font-display: swap** to avoid FOIT/FOUT regression.
- Cache headers: 1-year immutable (cache-busted via filename hash).

## License compliance

- Public Sans — OFL — embed in self-hosted, no royalty.
- Atkinson Hyperlegible — OFL — attribution in `public/fonts/LICENSES/`.
- OpenDyslexic — Bitstream Vera — attribution in same.
- `/credits` page (deferred) lists font credits per OFL requirement.

## Engineering effort estimate

- Font self-hosting (3 families × 4 weights × 2 styles each ≈ 24 woff2 files): 1 sprint.
- Preferences panel implementation: 2 sprints (gates on `docs/GRIMBANEWS_READING_MODE_DESIGN.md` shell).
- Per-(font × scale × spacing) visual regression sweep: 2 sprints.
- Per-locale rendering check: 0.5 sprint.
- A11y sweep + axe-core CI gate (S1579 dependency): 1.5 sprints.
- Credits page + attribution: 0.5 sprint.
- **Full ship: ~7-8 sprints, gates on Reading Mode shell.**

## Cross-references

- Master plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1572, S1573, S1574, S1575, S1579)
- Sister docs: `docs/GRIMBANEWS_READING_MODE_DESIGN.md` (host shell), `docs/GRIMBANEWS_ADMIN_VISUAL_REGRESSION_ROUTES.md`, `docs/GRIMBANEWS_CONSENT_LOG_DESIGN.md`
- Existing baseline: `resources/sass/**/*.scss`, `tests/e2e/grimbanews-keyboard-navigation.cjs`, `GrimbaDarkModeContractTest`
- Locale enforce: `app/Http/Middleware/GrimbaLocaleEnforce.php`
- Iboga roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
