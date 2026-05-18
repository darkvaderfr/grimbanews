# GrimbaNews — Vader Master Fleet (2026-05-18)

Mythos master sprint plan covering the four tracks Vader called out in the 2026-05-18 directive,
sequenced after the in-flight `S-LSAT` fleet so the reader pipeline is consistent before we layer
category-anchoring, info-pill polish, dark/light parity, and the sponsor homepage onto it.

---

## Framing (the four tracks and their dependencies)

The four tracks are deliberately stacked so each band lands on a foundation the previous band
already proved out. **S-LSAT-05 → S-LSAT-21** (the in-flight Language-Surfacing fleet) must close
first — it lands `translation_priority`, the rule engine, the `/admin/grimba/translation-rules`
form, scheduler entry, and the 8-URL × 2-locale release smoke. Two things downstream of this
fleet matter for Vader: (a) `S-LSAT-15` (admin save handler) is the **load-bearing prerequisite**
for the "500-view auto-translate" rule Vader asked about — without that form there's no operator
surface to drive auto-translation thresholds; (b) the reader pipeline becomes locale-strict, which
means rail composition is now deterministic enough to anchor by category. **S-PILL** runs second
because Vader hits popover pills on every page — until they reliably open/close/clamp on every
surface, anything we add visually rides on a broken interaction. **S-MODE** (light/dark parity)
runs third and can partly overlap with **S-ADS** (sponsor homepage + admin lead intake); they
touch different layers — S-MODE is a CSS-token + Blade sweep across reader chrome, S-ADS adds the
`/advertise` controller, leads table, slot admin, and conversion telemetry. **S-CAT** lands last,
on a now-stable rail pipeline, and enforces category-presence end-to-end (home hero, topNews,
sections, latest, /breaking, /latest, dossiers, mostRead, briefing) — wired to `GrimbaHomeFeed`
allocator and labeled at every rail boundary.

---

## Band 0 — `S-LSAT` continuation (already planned, runs FIRST)

Reference: `docs/GRIMBANEWS_LANGUAGE_SURFACING_AND_AUTO_TRANSLATE_PLAN.md`.
Sprints `S-LSAT-05` → `S-LSAT-21` are already specified; this band re-states them for sequencing
clarity only. **Total band budget: ~17h.**

| Sprint | Category | Description | Est | Deps |
|---|---|---|---|---|
| S-LSAT-05 | polish | Tail-expander partial — "Articles disponibles en anglais (N)" disclosure | 60m | 04 |
| S-LSAT-06 | big | Breaking-news locale filter (`GrimbaHomeFeed::breaking($locale, …)`) | 90m | 04 |
| S-LSAT-07 | polish | Urgency banner partial + `/breaking` route locale pass-through | 45m | 06 |
| S-LSAT-08 | big | `posts.translation_priority` migration + index | 30m | — |
| S-LSAT-09 | big | `GrimbaTranslationRules` pure-function rule engine | 90m | 03, 08 |
| S-LSAT-10 | big | `grimba:translate-by-rule` artisan command | 75m | 09 |
| S-LSAT-11 | big | Scheduler entry — `*/15 * * * *` with daily-cap guard | 45m | 10 |
| S-LSAT-12 | big | `GrimbaTranslatePending` `--order-by-priority` + `--respect-rule-cap` flags | 60m | 10 |
| S-LSAT-13 | polish | Admin route shell `/admin/grimba/translation-rules` + Botble menu | 60m | 03 |
| S-LSAT-14 | big | Admin form view — 13 fields, NobuAI brand purity | 90m | 13 |
| **S-LSAT-15** | **big** | **Admin save handler — POST endpoint, cache flush, audit log (load-bearing for "500-view auto-translate")** | **60m** | **14** |
| S-LSAT-16 | polish | Live "projected enqueue count" preview on the admin form | 60m | 14 |
| S-LSAT-17 | polish | Per-post override on post-edit screen (priority + force-translate radio) | 60m | 08 |
| S-LSAT-18 | big | Filter + rule unit/feature tests (4 surfaces × 2 locales + 6 rule predicates) | 90m | 04, 06, 09 |
| S-LSAT-19 | big | `translate-by-rule` command tests (idempotency, cap, ordering, dry-run) | 75m | 10, 11 |
| S-LSAT-20 | polish | Docs + operator handoff (`GRIMBANEWS_LANG_SURFACING_OPERATOR_HANDOFF.md`) | 45m | 18, 19 |
| S-LSAT-21 | polish | Release smoke — 8-URL × 2-locale + admin form roundtrip | 60m | 20 |

**Acceptance:** Reader sees only articles in their locale on home/breaking/latest/dossiers.
`grimba:translate-by-rule` enqueues per rule, respects daily caps, idempotent. Operator can
edit/save all 13 settings keys from `/admin/grimba/translation-rules` with audit trail and live
preview count. **S-LSAT-15 closes the "500-view auto-translate" operator surface.**

---

## Band 1 — `S-PILL` — Info-pill audit + polish

The popover info-pill partial (`platform/themes/echo/partials/info-pill.blade.php`) is included
across 20+ surfaces (home rails, post page, sources, owners, breaking, latest, comparison, story
sub-partials) but UX is inconsistent. This band audits every call-site, repairs anchoring,
polishes the animation, and locks behavior with tests. **Total band budget: ~7h 30m.**

| Sprint | Category | Description | Est | Deps |
|---|---|---|---|---|
| S-PILL-01 | audit | Inventory every `@include('partials.info-pill', …)` call-site + tabulate which pages render correctly vs broken (popover offset, clipping, missing trigger, no-close-on-outside-click) | 60m | — |
| S-PILL-02 | audit | Reproduce each failure on local — script Playwright walk over 22 routes capturing pill open/close per pill ID | 45m | 01 |
| S-PILL-03 | big | Refactor `info-pill.blade.php` to a single canonical structure: `data-pill-id`, `data-pill-anchor`, ARIA role=tooltip, focus-trap when interactive | 90m | 02 |
| S-PILL-04 | big | Rewrite the JS controller as one module (`assets/js/info-pill.js`) — single delegated listener, viewport-clamp recalc on resize/scroll, ESC + outside-click close | 90m | 03 |
| S-PILL-05 | polish | Open/close animation — 180ms cubic-bezier(.2,.8,.2,1) scale+fade, prefers-reduced-motion respected | 45m | 04 |
| S-PILL-06 | polish | Token-driven theming — pill bg/border/text resolved from CSS vars, no hard-coded colors (sets up Band 2) | 45m | 04 |
| S-PILL-07 | big | Migrate all 22 call-sites to canonical pattern; delete bespoke inline pill markup | 75m | 03, 04 |
| S-PILL-08 | polish | Mobile pass — long-press to open on touch, dismiss on tap-outside, max-width 92vw | 45m | 04 |
| S-PILL-09 | big | Tests — feature test asserts every pill ID exists in DOM with `data-pill-id`, JS unit tests for clamp math | 60m | 07 |
| S-PILL-10 | polish | Release smoke — manual walkthrough of 22 surfaces, two screen sizes, both color modes | 30m | 09 |

**Acceptance:** Every info-pill on every reader surface opens within 200ms, animates smoothly,
clamps inside the viewport, closes on ESC + outside-click, passes keyboard + screen-reader
navigation, and inherits color from CSS tokens (so Band 2 just works).

---

## Band 2 — `S-MODE` — Light/dark mode parity sweep

Dark mode partly works but Vader flagged bleed-through (light surfaces leaking into dark, dark
text on dark bg, etc). This band is a token-first audit + repair across the reader site. Can run
**partly in parallel** with Band 3 (S-ADS) — different files, different ownership. **Total band
budget: ~10h.**

| Sprint | Category | Description | Est | Deps |
|---|---|---|---|---|
| S-MODE-01 | audit | Token-inventory — list every CSS var in `css-variable-declare.blade.php` + every hard-coded hex/rgb in theme CSS/Blade; classify dark-safe vs not | 60m | — |
| S-MODE-02 | audit | Visual sweep — Playwright screenshot 28 reader routes in both modes at 3 widths (375/768/1280); diff dark vs light | 60m | 01 |
| S-MODE-03 | big | Reader chrome — header, footer, nav, command palette, language switcher all token-driven | 75m | 01 |
| S-MODE-04 | big | Home rails — hero-grid, all-sides-rail, daily-briefing, most-read-by-bias, regional-mix, topic-chips token-driven | 90m | 01, 03 |
| S-MODE-05 | big | Article page — post-hero-img, body chrome, bias-distribution, coverage-details, dossier-voices | 75m | 01 |
| S-MODE-06 | big | Comparison + blindspot + dossiers + sources + owners + coffre + for-you | 90m | 01 |
| S-MODE-07 | polish | Form surfaces — auth, account, advertise (handed to S-ADS for cross-check), cookie consent | 45m | 03 |
| S-MODE-08 | polish | Image + media — placeholder, ad container, video poster, info-pill (already tokenized in S-PILL-06) | 45m | S-PILL-06 |
| S-MODE-09 | polish | Mode-toggle UX — persist preference, prefers-color-scheme fallback, no flash-of-wrong-mode (FOUC guard inline `<head>` script) | 60m | 03 |
| S-MODE-10 | big | Tests — Playwright visual-regression suite asserting no light-bleed pixels on 28 routes × 2 modes | 75m | 02, 09 |
| S-MODE-11 | polish | Release smoke — manual + screenshot diff; Vader-facing 28-URL walkthrough doc | 45m | 10 |

**Acceptance:** No light bleed-through in dark mode, no dark text on dark bg, no FOUC on first
load, mode preference persists, all 28 reader routes pass visual-regression diff in both modes.

---

## Band 3 — `S-ADS` — Sponsor homepage + admin/backend

`/advertise` route exists (`platform/themes/echo/routes/web.php:333`) and renders
`views/advertise.blade.php`, but it's a static landing — no lead-capture, no admin, no slot
management beyond the 12-slot constants in `app/Support/GrimbaAds.php`. This band wires the
front-end CTA to a real lead intake, adds the admin scaffolding to manage slots/leads, and
polishes the page. Can run **partly in parallel** with Band 2 (S-MODE). **Total band budget: ~12h.**

| Sprint | Category | Description | Est | Deps |
|---|---|---|---|---|
| S-ADS-01 | audit | Walk current `/advertise` page + map to spec — what's missing (lead form, pricing tier, FAQ, slot preview, demo screenshots, dark-mode parity, NobuAI brand purity) | 45m | — |
| S-ADS-02 | big | DB — `grimba_advertiser_leads` migration (email, company, budget_band, goals, source_referrer, locale, created_at) | 30m | — |
| S-ADS-03 | big | Backend — `AdvertiserLeadController@store` POST `/advertise/leads` with validation, rate-limit, honeypot, locale capture | 60m | 02 |
| S-ADS-04 | big | Frontend — replace static "leave us your address" copy with a real form (email + company + goals + budget band), POSTs to S-ADS-03, success state, error state | 90m | 03 |
| S-ADS-05 | polish | Page polish — hero, value props, slot preview grid (renders all 12 `GrimbaAds::SLOTS` as visual placeholders), FAQ, pricing tiers, dark/light parity | 90m | 01, 04 |
| S-ADS-06 | big | Admin — `/admin/grimba/advertiser-leads` Botble list view with search/filter/export CSV | 75m | 02 |
| S-ADS-07 | big | Admin — `/admin/grimba/ad-slots` form to edit slot HTML/label per slot key (was hardcoded; promote to settings + cache) | 90m | — |
| S-ADS-08 | polish | Admin — slot preview pane showing live render of each slot on the active reader theme | 45m | 07 |
| S-ADS-09 | big | Telemetry — slot impression + click counters (`grimba_ad_events` table; throttled fire-and-forget JS beacon) + admin dashboard tile | 75m | 07 |
| S-ADS-10 | polish | Sales handoff email — submitted leads fire transactional email to `sales@grimbanews.com` via LeafRelay; templated FR + EN | 45m | 03 |
| S-ADS-11 | big | Tests — feature tests for lead POST validation, admin index, slot edit roundtrip, beacon counters | 75m | 03, 06, 07, 09 |
| S-ADS-12 | polish | Release smoke — submit lead from local + prod-like, verify admin row, verify email arrives at b.boula+gnsales@icloud.com sandbox | 45m | 10, 11 |

**Acceptance:** `/advertise` page collects qualified leads (validated, rate-limited, audit-logged,
emailed to sales), admin can read/export leads + edit all 12 slot HTML+label + see live preview +
see impression/click counters, page passes dark/light parity, no provider names visible to
visitors (Vader's NobuAI brand directive).

---

## Band 4 — `S-CAT` — Category-anchoring across reader rails

The home allocator currently picks-by-bias and picks-by-recency but doesn't enforce
category-presence per rail. Vader wants a Sports article never to appear in a Politics rail and
every rail to be labeled + anchored by category. This band lands the rail-level category contract,
allocator changes, label additions, and an end-to-end test. Runs **last** because it builds on
the locale-strict pipeline (S-LSAT), the consistent pill UX (S-PILL), the token-driven theme
(S-MODE), and assumes the sponsor page (S-ADS) has settled. **Total band budget: ~9h.**

| Sprint | Category | Description | Est | Deps |
|---|---|---|---|---|
| S-CAT-01 | audit | Inventory every rail on every public reader page (home hero/topNews/sections/latest, /breaking, /latest, dossiers, mostRead, briefing) — tabulate which category each rail currently shows vs intends to show | 60m | S-LSAT-21 |
| S-CAT-02 | big | `GrimbaHomeFeed::byCategory($category, $locale, $limit)` query method — strict category filter, locale-aware, recency-weighted | 75m | 01 |
| S-CAT-03 | big | Allocator refactor — `GrimbaHomeFeed::compose()` accepts a `rails` config (`['hero' => ['mixed'], 'politics' => ['politics'], 'sports' => ['sports'], …]`) and routes each rail through `byCategory()` | 90m | 02 |
| S-CAT-04 | polish | Add `category-badge` partial to every rail header — labels "Politique", "Sport", "Économie", etc. localized | 45m | 03 |
| S-CAT-05 | big | `/breaking` + `/latest` route accept `?category=` and filter — currently they don't | 60m | 02 |
| S-CAT-06 | big | Dossier rails — `GrimbaStoryInsights` ensures dossier most-read / coverage rails respect parent dossier category | 60m | 02 |
| S-CAT-07 | polish | Most-read-by-bias + regional-mix + daily-briefing rails — annotate category, fall back to "Toutes catégories" only when rail is explicitly mixed | 60m | 03 |
| S-CAT-08 | big | Tests — feature test asserts no cross-category leakage on 12 reader surfaces, both locales | 75m | 03, 06 |
| S-CAT-09 | polish | Admin — `/admin/grimba/home-rails` form to edit which category each home rail anchors to (settings-backed, no code change to reshuffle) | 75m | 03 |
| S-CAT-10 | polish | Release smoke — 12-URL × 2-locale × 5-category-spot-check sweep, Vader-facing screenshot evidence doc | 45m | 08, 09 |

**Acceptance:** Every rail labels its category, no off-category articles surface, operator can
reshuffle rails from admin without code changes, tests prove no leakage on 12 reader surfaces ×
2 locales.

---

## Runtime + parallelism

| Band | Sprints | Sequential estimate | Notes |
|---|---|---|---|
| S-LSAT (05→21) | 17 | ~17h | Already planned; runs FIRST |
| S-PILL | 10 | ~7h 30m | After S-LSAT-21 closes |
| S-MODE | 11 | ~10h | Can overlap with S-ADS (different files) |
| S-ADS | 12 | ~12h | Can overlap with S-MODE |
| S-CAT | 10 | ~9h | Runs LAST, after the other 3 bands |
| **Total** | **60** | **~55h 30m sequential** | **~42h with S-MODE ‖ S-ADS overlap** |

**Parallelizable:** S-MODE and S-ADS share no files of consequence — S-MODE is CSS-tokens + Blade
chrome, S-ADS is controllers + migrations + admin views. Running them concurrently saves ~10h.
**Not parallelizable:** S-PILL must close before S-MODE-08 (pill is tokenized inside the mode
band); S-CAT depends on a stable S-LSAT pipeline; S-ADS-05 depends on S-MODE token work for
dark/light parity on the advertise page itself, so S-ADS-05 should land after S-MODE-07.

---

## Risk register — what Vader will notice on manual check

| ID | Risk | Surface where Vader will spot it | Mitigation |
|---|---|---|---|
| R1 | **Info-pill silently broken on a page** — pill exists in DOM but click does nothing | Any reader surface, especially story pages with bias-distribution | S-PILL-02 Playwright sweep enumerates every pill; S-PILL-09 tests assert pill DOM contract |
| R2 | **Pill clips off-viewport on mobile** — opens but is half-cut | iPhone-width view of /breaking + /post pages | S-PILL-04 clamp recalc on resize/scroll; S-PILL-08 mobile pass |
| R3 | **Dark-mode bleed-through** — white card on dark bg, or dark text on dark bg | Home daily-briefing, all-sides-rail, advertise page | S-MODE-04 + S-MODE-07 token-drive these rails; S-MODE-10 visual-regression catches |
| R4 | **FOUC on first load** — flash of light theme before dark settles | Any reader route on cold load | S-MODE-09 inline `<head>` guard script before CSS parse |
| R5 | **Sponsor page has no lead form** — only a static "leave us your address" CTA | `/advertise` | S-ADS-03 + S-ADS-04 land real form with validation + persistence |
| R6 | **Sponsor leads go nowhere** — form submits but no admin sees it | `/admin/grimba/advertiser-leads` | S-ADS-06 admin index + S-ADS-10 email handoff |
| R7 | **Category leakage in rails** — Sports article in Politics rail | Home page Politics rail, /breaking?category= | S-CAT-03 strict byCategory query; S-CAT-08 anti-leakage tests |
| R8 | **Rail not labeled by category** — visitor doesn't know what they're looking at | Home page rail headers | S-CAT-04 category-badge added to every rail header |
| R9 | **Auto-translate has no operator surface** — Vader asked for "translate when 500 views"; no UI to set it | `/admin/grimba/translation-rules` | S-LSAT-14 + **S-LSAT-15** (load-bearing) ship the form + save handler |
| R10 | **Provider name visible to end users** — "DeepL" / "Anthropic" / "Claude" leaks into Blade copy | Advertise page, translation tail-expander, admin-form copy that leaks into reader | S-LSAT-14 + S-ADS-05 acceptance criteria forbid provider names; Mnemo audit greps for them |
| R11 | **Slot HTML edits require code change** — operator can't move a slot or change copy | Admin missing slot editor | S-ADS-07 promotes hardcoded `GrimbaAds::SLOTS` to settings |
| R12 | **No telemetry on sponsor slots** — sales can't pitch "X impressions per week" | Admin dashboard | S-ADS-09 beacon counters + dashboard tile |
| R13 | **Light-mode regressions on lower-traffic pages** — coffre, owners, blindspot | Long-tail reader routes | S-MODE-06 covers them explicitly; S-MODE-02 screenshot sweep diffs both modes |
| R14 | **Category-anchoring breaks dossier coherence** — dossier suddenly hides its own articles | Story / dossier rails | S-CAT-06 carves dossier rails out of the strict global rule |

---

## Cross-cutting constraints

- **NobuAI brand purity:** No external provider names in any user-facing surface. Applies to
  S-LSAT-14 (admin form labels visible nowhere but still graded), S-ADS-04/05 (form + page copy),
  S-MODE-07 (mode-toggle tooltip), S-PILL pill copy. Mnemo greps the diff each sprint.
- **darkvaderfr push policy:** Every sprint pushes to `darkvaderfr/GrimbaNews` after Zen/Echo
  audit. No direct-on-VPS edits. Push BEFORE prod pull.
- **Audit panel (Zen / Echo / Mnemo) in parallel** before declaring any non-trivial sprint shipped.
- **Sprint cadence audit / big / polish** — don't stack 3 bigs in a row; recover with polish between
  them. Current ordering already alternates where possible.

---

## Wired into existing code — what gets touched per band

- **S-PILL:** `platform/themes/echo/partials/info-pill.blade.php`, `assets/js/info-pill.js`
  (new), 22 call-site Blade files, `tests/Feature/InfoPillTest.php` (new).
- **S-MODE:** `platform/themes/echo/partials/css-variable-declare.blade.php`, theme `assets/css`,
  ~28 Blade routes, `tests/Visual/DarkModeRegressionTest.php` (new).
- **S-ADS:** `routes/web.php` (advertise lead POST), `app/Http/Controllers/AdvertiserLeadController.php`
  (new), `database/migrations/*_create_grimba_advertiser_leads.php` (new),
  `database/migrations/*_create_grimba_ad_events.php` (new), `app/Support/GrimbaAds.php`
  (settings-backed slots), `views/advertise.blade.php`, Botble admin module
  `platform/plugins/grimba-ads/*` (new or extend existing).
- **S-CAT:** `app/Support/GrimbaHomeFeed.php` (`byCategory`, `compose` refactor), routes for
  `/breaking` + `/latest` (category query param), 12+ rail partials in
  `platform/themes/echo/partials/home/*`, `tests/Feature/CategoryAnchoringTest.php` (new),
  Botble admin module for rail-config.

---

## Master-plan integration

Append this fleet to `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` under a new section
"Vader 2026-05-18 Master Fleet (60 sprints)" once S-LSAT-20 lands. Cross-link
`GRIMBANEWS_LANGUAGE_SURFACING_AND_AUTO_TRANSLATE_PLAN.md` (Band 0),
`GRIMBANEWS_INFO_PILL_ROLLOUT_PLAN.md` (Band 1 precedent),
`GRIMBANEWS_UI_DARK_LIGHT_55_SPRINTS.md` (Band 2 precedent — supersedes for these specific
routes), and `GRIMBANEWS_ADVERTISER_CULTURE_FRESHNESS_PLAN.md` (Band 3 context).

— Mythos, 2026-05-18
