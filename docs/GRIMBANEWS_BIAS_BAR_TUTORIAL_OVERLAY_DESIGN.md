# GrimbaNews — Bias-Bar Tutorial Overlay Design

**Status:** design v0 (no overlay partial; standalone /explainer-bias-bar page is the surrogate)
**Owner:** Steve Jobs (CPO) on UX + Nina Patel (Lead FE) on implementation + Lucy Leai (Strategy) on copy
**Walks:** Mythos S1772 (bias-bar tutorial — first-visit overlay) + S1773 (step-through animation) deferred → partial
**Gating dependency:** Reading mode shell available (per `docs/GRIMBANEWS_READING_MODE_DESIGN.md`) for overlay-host pattern + per-overlay a11y review. Design itself is operator-side.

## Why this exists

S1772 was honest-deferred: no `bias-bar-tutorial-overlay.blade.php` partial. The surrogate (S1771 complete) is the **standalone explainer page** at `/explainer-bias-bar` + AboutPage JSON-LD per Wave OOOOO. The standalone page is fine for first-time readers who follow a link, but new readers who land on `/dossier/{cluster}` see the bias bar without context. The first-visit overlay is the missing onboarding affordance. This document defines the overlay so the moment Steve approves the copy + visual design, implementation is straight.

## Today's surrogate

- `/explainer-bias-bar` route + `views/explainer-bias-bar.blade.php` + AboutPage JSON-LD per Wave OOOOO. **Complete** per S1771.
- Per-cluster bias chart at `platform/themes/echo/partials/story/bias-distribution.blade.php`.
- Chart caption includes link to explainer page.
- **No first-visit overlay.**

## Overlay shape

- **Trigger:** first-time visit to a dossier page (gates on cookie `grimba_bias_tutorial_dismissed` absent — per S1774 partial).
- **Surface:** modal overlay anchored above the bias chart on `/dossier/{cluster}`.
- **Dismissable:** "Got it" button + close X + Esc key.
- **Step-through (S1773 ship target):** 3-step walkthrough.
  - Step 1: "This chart shows how this story is covered across the political spectrum." (highlights the chart)
  - Step 2: "Each segment = sources from that lean. Click a segment to see those articles." (highlights segments)
  - Step 3: "Want to learn more? Read our full methodology." (links to `/methodologie` + `/explainer-bias-bar`)
- **Persistent dismiss:** writes `grimba_bias_tutorial_dismissed=1` cookie (1-year TTL).
- **Re-trigger affordance:** "Show tutorial" link in dossier page footer for readers who dismissed too soon.

## Triggers + suppression

- **Trigger:** any dossier page visit where `grimba_bias_tutorial_dismissed` cookie absent.
- **Suppress:** reading mode (per `docs/GRIMBANEWS_READING_MODE_DESIGN.md` — reading mode is intentional focus surface; no overlays).
- **Suppress:** if reader arrived via `/explainer-bias-bar` referrer (they just read the explainer).
- **Suppress:** if reader has saved articles already (per `members.vault_digest_post_ids` — implies returning reader).

## Cookie footprint (S1774 ship)

| Cookie | Purpose | TTL | Privacy class |
|---|---|---|---|
| `grimba_bias_tutorial_dismissed` | Suppress repeat overlay | 1 year | Functional |
| `grimba_bias_tutorial_step` | Resume mid-tutorial after navigation | session | Functional |

Both purpose-bound, no profiling. Documented in `docs/GRIMBANEWS_CONSENT_LOG_DESIGN.md` next refresh.

## Per-locale copy (S1777 ship)

All strings wrapped in `__()` per Wave LLLLLLLLL + WWWWWWWWW pattern.

**Initial set:**

```php
// lang/fr/grimba.php
'bias_tutorial' => [
    'step1_title' => 'Comment lire la barre de biais',
    'step1_body'  => 'Cette barre montre comment cette histoire est couverte par les médias selon leur orientation politique.',
    'step2_title' => 'Sources par segment',
    'step2_body'  => 'Chaque segment représente les sources de cette orientation. Cliquez pour voir leurs articles.',
    'step3_title' => 'Notre méthodologie',
    'step3_body'  => 'Vous voulez en savoir plus ? Lisez notre méthodologie complète.',
    'methodology_link' => 'Lire la méthodologie',
    'explainer_link'   => 'En savoir plus',
    'dismiss_button'   => 'J\'ai compris',
    'show_again'       => 'Revoir le tutoriel',
],

// lang/en/grimba.php  (matching keys, English)
```

## A11y (S1775 + S1776 ship)

- **Keyboard navigation:** Tab cycles through (next-step, dismiss, methodology-link). Esc exits.
- **Focus trap:** YES — modal pattern requires it (per `partials/focus-manager.blade.php` extensible focus-trap helper).
- **First-focused element:** the "Got it" button (per typical modal-onboarding pattern).
- **Screen-reader announcement:** "Welcome. Bias-bar tutorial: 3 steps. Step 1 of 3."
- **Reduced-motion:** step transitions use opacity-only (no slide / scale) when `prefers-reduced-motion: reduce`.
- **`aria-modal="true"` + `role="dialog"` + `aria-labelledby` + `aria-describedby`.**

## Visual design hooks (Steve sign-off)

- **Backdrop:** rgba(0,0,0,0.5) dim. Bias chart highlighted (z-index lifted, surrounding content dimmed).
- **Modal placement:** above-chart on desktop, below on mobile (chart needs to remain visible).
- **Step indicator:** dots (1•••, •2••, ••3•).
- **Step transition:** 200ms ease-in-out crossfade (no slide).
- **Color:** matches dossier-page theme (dark / light parity locked per `GrimbaDarkModeContractTest`).

## Engineering effort estimate

- Overlay component (Blade partial + JS controller): 2 sprints.
- Cookie persistence + suppress rules: 0.5 sprint.
- Per-locale string keys + translations: 0.5 sprint.
- A11y pass + focus trap + ARIA + Esc handling: 1 sprint.
- Step-through transitions + reduced-motion fallback: 1 sprint.
- Tests (E2E for first-visit, dismiss, suppress in reading mode): 1 sprint.
- **Full ship: ~6 sprints.**

## Completion-rate analytics (S1779)

Gates on either:

- **A/B harness** per `docs/GRIMBANEWS_AB_HARNESS_DESIGN.md` (write outcome events: `tutorial_shown`, `step_completed`, `tutorial_dismissed`).
- **OR** simpler vault_events-style logger (read-event ledger) — gates on per-article read-event capture (S1733 partial → ship).

Once one of those ships, analytics columns:
- Show rate (% of dossier visits where overlay fires).
- Per-step completion rate.
- Dismiss-without-completion rate.
- Re-show via footer link count.

## Partner-school distribution (S1778)

Gates on `docs/GRIMBANEWS_CLASSROOM_VIEW_SCOPE.md` partner-school program. Once school-partner tier ships, classroom mode could:
- Force tutorial on first dossier visit per classroom (overrides cookie dismiss for educational context).
- Per-classroom completion stats for the teacher.

## Cross-references

- Master plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1771-S1780, related S1774)
- Sister docs: `docs/GRIMBANEWS_READING_MODE_DESIGN.md`, `docs/GRIMBANEWS_CONSENT_LOG_DESIGN.md`, `docs/GRIMBANEWS_CLASSROOM_VIEW_SCOPE.md`, `docs/GRIMBANEWS_AB_HARNESS_DESIGN.md`
- Existing surrogate page: `/explainer-bias-bar` route + `platform/themes/echo/views/explainer-bias-bar.blade.php`
- Existing chart partial: `platform/themes/echo/partials/story/bias-distribution.blade.php`
- Existing focus manager: `platform/themes/echo/partials/focus-manager.blade.php`
- Methodology page: `/methodologie` route
- Iboga roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
