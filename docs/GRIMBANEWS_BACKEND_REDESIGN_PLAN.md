# GrimbaNews — Admin Panel Visual Redesign Plan

**Date:** 2026-04-23
**Author:** Mythos (planning) + Steve Jobs (design lead)
**Scope:** Make the Botble admin look and feel like it was built by the same team that shipped the GroundNews-style public reader.
**Status:** Ready for Vader review → S50 start.

---

> **Steve (opening review):**
> *"We built a newsroom that feels like you're reading Le Monde on an OLED in 2030. Then the editor clicks 'Admin' and falls into 2012 Bootstrap. That's not a bug. That's an identity crisis. Editors are the first users — they live in this UI eight hours a day. The cream paper, the Fraunces display, the bias palette — none of that is decoration. It's the product's voice. Wherever the product speaks, it must speak the same way. So we don't 'theme' the admin. We make the admin feel like the newsroom it actually runs. And we do it without forking Botble's master template, because we have to survive Botble upgrades. One injected stylesheet, a handful of targeted views, a GrimbaNews-specific landing page that tells the editor something useful before they click. That's the bar."*

---

## 1. Executive Summary

GrimbaNews' admin is stock Botble today — Bootstrap 5, Tabler icons, navy sidebar, white cards. The public reader is Steve's cinematic francophone newsprint (Fraunces + Public Sans + JetBrains Mono, cream paper tokens, glass panels, L/C/R palette, dark-mode parity). This plan closes that gap in ~11 shippable sprints without forking `BaseHelper::getAdminMasterLayoutTemplate()`. We inject a `grimba-admin.css` + web-font load through Botble's theme-boot hook, override surface-level chrome (sidebar, navbar, cards, tables, form controls) by targeting the admin body classes, and replace the generic `/admin` dashboard with a GrimbaNews editorial cockpit. Our two existing custom pages (news-sources, story-clusters) plus the injected post-form fields get deliberate styling rules so bias chips, credibility bars, and coverage spreads feel native to the reader. Sprint 1 is a visible-win token-injection sprint — Vader sees Fraunces on the dashboard inside an hour.

---

## 2. Design Principles

1. **No fork, no ship.** Every visual change is an additive override on Botble's master layout. We never copy the master template into our theme. If Botble pushes an update, we re-test the overrides — we don't re-merge a fork.
2. **Tokens first, components second.** The `:root` palette from `grimba-home.css` is the single source of truth. Admin CSS references `--gn-paper`, `--gn-ink`, `--gn-left/center/right` — it never defines a competing palette.
3. **Legibility over glass.** The reader is allowed glass + blur because it's a consumption surface. The admin is a work surface. Glass is used sparingly — sidebar and topbar only — everything inside a card is flat, high-contrast, and fast.
4. **Every admin chart matches a public chart.** If `/comparatif/{id}` shows an L/C/R coverage bar one way, the admin dashboard shows it the same way. One renderer, two surfaces.
5. **Editors read French.** All admin copy stays French. New labels ship as `lang/fr.json` entries, not inline strings.
6. **Dark mode has parity.** Same `data-bs-theme="dark"` cookie the reader uses drives the admin. One toggle, both surfaces.

> **Steve:** *"If a reader and an editor sit next to each other and the reader's iPhone screen and the editor's MacBook look like different companies, we've failed. One company. One voice. Everywhere."*

---

## 3. Pillar-by-Pillar Technical Approach

### Pillar 1 — Tokens, Fonts, Chrome Injection

**Goal:** make every admin page inherit the GrimbaNews palette + typography without editing Botble core.

**Mechanism:**
- New file: `public/themes/echo/css/grimba-admin.css` — mirrors the `:root` block from `grimba-home.css` but re-declared inside `body.body-admin, .app-sidebar, .navbar-vertical` selectors so it wins against Botble's admin SCSS specificity.
- New function: `platform/themes/echo/functions/grimba-admin-chrome.php` — hooks into `admin_header` / `theme_asset_head` (or Botble's `add_filter('admin_assets', …)`) to inject:
  - Google Fonts link for Fraunces + Public Sans + JetBrains Mono (same `<link rel="preload">` + `<link rel="stylesheet">` pair used in `grimba-header.blade.php`).
  - `<link rel="stylesheet" href="/themes/echo/css/grimba-admin.css">` with a cache-busting version string tied to `filemtime()`.
  - A tiny `<script>` that reads `grimba_theme` cookie and mirrors `data-bs-theme` on `<html>` so dark mode flips admin and reader together.
- Override targets (non-exhaustive):
  - `.app-sidebar` → paper-warm background, Fraunces for section headings, JetBrains Mono for the GrimbaNews wordmark at the top.
  - `.navbar-vertical .nav-link.active` → ink-on-paper pill instead of Bootstrap primary blue.
  - `.page-title` → Fraunces display, `-0.01em` tracking.
  - `.card` → 14px radius, 1px `--gn-rule` border, subtle `box-shadow: 0 4px 14px rgba(26,23,19,0.04)`.
  - `.table thead th` → uppercase small-caps, Public Sans, `color: var(--gn-ink-soft)`.
  - `.btn-primary` → `background: var(--gn-ink); color: var(--gn-paper)` (matches `.btn-grimba--solid`).
  - `.form-control:focus` → `border-color: var(--gn-ink)` (no Bootstrap blue glow).
  - `.badge.text-bg-success / warning / danger` → remapped to paper-friendly hues (moss, tan, right-red) so a "Partielle" badge looks like it belongs on cream, not Bootstrap factory floor.
- Dark-mode variant: every rule above gets a `[data-bs-theme="dark"]` sibling that pulls from the dark-mode tokens already declared in `grimba-home.css`.

**Non-goals for P1:** we do NOT touch Botble's icon set (Tabler stays), nor the login screen (separate sprint later), nor any CodeMirror/Filament/DataTables plugin chrome (P2 cleanup).

### Pillar 2 — GrimbaNews Editorial Cockpit (`/admin` landing)

**Goal:** replace Botble's generic "welcome" dashboard with a page that tells the editor, on arrival, what the newsroom looks like right now.

**Mechanism:**
- We don't delete Botble's dashboard widgets; we register a high-priority GrimbaNews widget block that renders *above* everything else. Botble's dashboard supports `registerDashboardWidget()` or a blade partial hooked into `dashboard.top`. If neither is clean, we register a route alias `/admin/grimba` and set it as the post-login redirect via `config/auth.php` or a tiny middleware.
- Page composition (4 rows, desktop 12-col grid):
  1. **Meta strip** — "Aujourd'hui • {date} • {N} articles publiés • {M} en brouillon". JetBrains Mono, small caps.
  2. **Row A (span 8 / 4):** "Équilibre de couverture — aujourd'hui" (large L/C/R horizontal stacked bar over all posts `published_at = today`, reusing the `.grimba-coverage__bar` renderer); sidebar mini-card "Angles morts à valider" (count + link to filter list).
  3. **Row B (span 6 / 6):** "Dossiers actifs" list (top 5 `story_clusters` by post count, each with inline L/C/R mini-bar + "Voir le comparatif" CTA); "Sources les plus citées (7j)" horizontal bar list with credibility-colored bars.
  4. **Row C (span 8 / 4):** Newsletter signups 7-day sparkline (SVG, no chart lib — 7 `<rect>` + a trend line); "Biais de la rédaction" donut (aggregate L/C/R across all published last 7d — is WE the biased ones?).
- All data comes from raw `DB::table()` queries in a single `GrimbaDashboardController` — no new Eloquent models, no new migrations. Cache the queries for 60s using Laravel's cache façade.
- Every tile on this page is click-through: coverage bar → filtered post list, dossier row → edit cluster, source row → edit source, newsletter spark → future `/admin/grimba/subscribers`.

> **Steve (on the cockpit):** *"When an editor logs in Monday morning, before they do anything else they should know: are we balanced this week? Are we missing the story the other side is covering? Are readers actually signing up? That's the dashboard. Not 'welcome back, admin.' Tell them something true."*

### Pillar 3 — Post Editor: GrimbaNews Right-Rail Card

**Goal:** the fields `grimba-post-form.php` injects today render inline at the bottom of the generic Post form. Promote them to a right-rail card that feels like a first-class editorial tool, not a plugin afterthought.

**Mechanism:**
- Botble's post form uses a left/right layout with a sidebar column (Categories, Tags, Featured Image, etc.). We register our fields into that sidebar column via the existing `form_builder` hook (currently bottom-of-form), switching the target to the sidebar slot if Botble exposes one; if not, we wrap our four fields in a full-width card and re-order DOM on admin boot with a 10-line script.
- Card composition:
  - **Header:** "GrimbaNews" wordmark (JetBrains Mono) + help-tip icon → opens a `<details>` explaining bias override semantics.
  - **Source** select: on change, fetch `/admin/grimba/news-sources/{id}.json` and render a live preview strip below — source name, bias chip (L/C/R/?), credibility bar 0-100 with the same palette the reader uses, ownership pill. Editor sees the source's identity before saving.
  - **Dossier (cluster)** select: on change, fetch the cluster's current L/C/R spread and render the same `.grimba-coverage__bar` the reader sees. Editor knows "this cluster currently has 3 left, 0 center, 1 right — I'm about to make it worse" before they save.
  - **Biais éditorial (override)** — segmented 4-way toggle (Auto / Gauche / Centre / Droite) with ink-on-paper active state. Default "Auto" = use source's bias.
  - **Angle mort** — toggle switch. When on, shows inline warning: "Cochez uniquement si cette histoire est couverte par un seul côté du spectre et vaut la peine d'être signalée."
- Two tiny JSON endpoints added to `grimba-admin-sources.php` and `grimba-admin-clusters.php` to feed the live previews. Both read-only, cached 60s.

### Pillar 4 — News Sources + Story Clusters List/Form Styling

**Goal:** keep the functional CRUD we already shipped (S35, S39); upgrade the visual treatment to match the reader.

**Rules:**
- Bias chip: replace the current hex-with-inline-style with a `.grimba-bias-chip--{left,center,right,unknown}` class set, matching the reader's `.grimba-coverage__chip--*` hue logic on light and dark backgrounds.
- Credibility score: replace the bare number with a 0-100 mini-bar. 0-40 → `--gn-right` (red), 41-70 → `--gn-center` (neutral tan), 71-100 → `--gn-left` (blue) — but label the semantic is "quality," not politics. Number sits right-aligned next to the bar.
- Ownership pill: state=slate, corporate=slate-500, independent=emerald, nonprofit=indigo. Flat pills, 0.75rem, small-caps.
- Table row hover: `background: rgba(26,23,19,0.03)` light / `rgba(255,255,255,0.04)` dark. Row becomes visually selectable without being noisy.
- Story-clusters spread column: ditch the `● N ● N ● N` text dots, render the real `.grimba-coverage__bar` component (same partial as the reader) so the editor sees the exact same visual they're curating for the public. "Statut barre" badge inherits from that same bar's activeSides count.
- Forms (not listed here but in scope): every `.form-label` gets Public Sans 600 weight + `--gn-ink-soft` color; every `.form-control` gets a 10px radius + 1px rule border.

### Pillar 5 — Admin Navigation (GrimbaNews section)

**Current:** `GrimbaNews > Sources`, `GrimbaNews > Dossiers`. Icon `ti-news`.

**Proposed structure (post-redesign):**

```
GrimbaNews
├── Tableau de bord      (ti-layout-dashboard) → /admin/grimba [new]
├── Sources              (ti-building-broadcast-tower) → existing
├── Dossiers             (ti-layout-collage) → existing
├── Newsletter           (ti-mail) → /admin/grimba/subscribers [new, S57]
├── Éditions régionales  (ti-map-2) → /admin/grimba/regions [planned]
├── Traductions          (ti-language) → /admin/grimba/translations [planned]
└── Analytics lecteurs   (ti-chart-histogram) → /admin/grimba/analytics [planned]
```

All registered via the existing `DashboardMenuItem::make()` pattern in the respective `grimba-admin-*.php` function files. Icons stay Tabler (consistent with Botble); only the surrounding chrome changes.

---

## 4. Sprint Breakdown

Each sprint is 1–2h, shippable independently, pushable to `darkvaderfr/grimbanews` main without breaking the live admin. Audit/iteration checkpoints after S52, S55, S58.

| Sprint | Title | Scope | Visible Outcome | Est. |
|---|---|---|---|---|
| **S50** | Admin tokens + fonts injected | Create `grimba-admin.css` with `:root` tokens scoped to `.body-admin`. Add `grimba-admin-chrome.php` to inject fonts + CSS + `data-bs-theme` cookie mirror. | Fraunces visible on `/admin` page titles. Paper-cream sidebar. Ink-black primary buttons. Dark-mode toggle mirrors reader. | 1.5h |
| **S51** | Sidebar + navbar chrome | Targeted overrides for `.app-sidebar`, `.navbar-vertical`, active-state pill, user menu, Tabler icon sizing. JetBrains Mono wordmark atop sidebar. | Sidebar feels like GrimbaNews header. Old Botble navy gone. | 1.5h |
| **S52** | Cards + tables + forms base | Override `.card`, `.table`, `.form-control`, `.btn-*`, `.badge.text-bg-*`. | News-sources + Story-clusters tables look native to the cream palette without any view edits. | 2h |
| *Checkpoint 1* | **Audit + iterate** | Walk every admin page (Posts list, Post edit, Pages, Media, Settings). Log visual regressions. Patch. | Regression ledger + 3-5 fixes. | 1h |
| **S53** | Bias chips + credibility bars | Replace inline hex styles in `news-sources/index.blade.php` with `.grimba-bias-chip` + `.grimba-credibility-bar` components. Same components usable everywhere. | Source table visually matches reader's source directory `/sources`. | 1.5h |
| **S54** | Cluster spread bar component | Replace text-dots in `story-clusters/index.blade.php` with the real `.grimba-coverage__bar` partial. | Editor sees literal reader bar in the dossier list. | 1h |
| **S55** | Editorial cockpit — structure | New `GrimbaDashboardController` + `/admin/grimba` route. Layout skeleton (meta strip + 3 rows, placeholder tiles). Post-login redirect middleware. | `/admin` now shows GrimbaNews cockpit shell. | 2h |
| *Checkpoint 2* | **Audit + iterate** | SOK review with Steve + Elon. Is the cockpit telling the editor the right things? Adjust tile priorities. | Tile spec v2. | 1h |
| **S56** | Cockpit — coverage balance + dossiers | Wire the live coverage-balance bar + active-dossiers list with real queries. Cache 60s. | Real L/C/R for today. Real top 5 dossiers clickable. | 2h |
| **S57** | Cockpit — sources + newsletter | Top-sources horizontal bar list. Newsletter 7-day sparkline SVG. Angles morts counter. | All cockpit tiles live. | 1.5h |
| **S58** | Post editor right-rail card | Promote `grimba-post-form.php` fields into a right-rail `.grimba-editor-card`. Segmented bias toggle, angle-mort switch with help text. | Post editor feels editorial, not bolted-on. | 2h |
| *Checkpoint 3* | **Audit + iterate** | Editor walkthrough. Time a real post creation end-to-end. | Timing baseline + fixes. | 1h |
| **S59** | Live preview strips | JSON endpoints + JS fetch for source preview (bias chip + credibility bar) and cluster preview (coverage bar) inside the right-rail card. | Editor sees source/cluster identity live before save. | 2h |
| **S60** | Newsletter subscribers admin | New `/admin/grimba/subscribers` page — list, search, export CSV, unsubscribe. Styled with the now-canonical table chrome. | New nav item live, editor can manage newsletter. | 2h |
| **S61** | Dark-mode parity sweep | Walk every new and existing admin surface in `data-bs-theme="dark"`. Fix contrast regressions, chart hue swaps, any forgotten glass. | Admin looks right at 2 a.m. too. | 1h |

**Total:** 11 build sprints + 3 checkpoints ≈ 19h. Split across 2 evening sessions + 1 weekend block.

> **Steve (on sprint sizing):** *"If a sprint can't be shown to Vader as a before/after screenshot, it's too big. S50 is the unlock — the moment he sees Fraunces on /admin, he knows we're doing this right. Every subsequent sprint builds on that moment. Don't let any sprint go longer than two hours. If it's going longer, it's two sprints."*

---

## 5. Known Unknowns + Research Tasks

1. **Botble admin asset hook name.** We've been using `functions/*.php` for theme boot; confirm the cleanest way to enqueue admin-only CSS. Research task at top of S50: grep `BaseHelper::` and `admin_header` in Botble core. Fallback: `View::composer(BaseHelper::getAdminMasterLayoutTemplate(), …)` pushing HEAD content.
2. **Dashboard widget vs. route override.** Does Botble expose a `dashboard.top` blade section, or a `registerDashboardWidget()`-style API? If yes, prefer additive widget. If no, we post-login-redirect to `/admin/grimba`. Research task at top of S55.
3. **Post form sidebar slot.** Confirm whether Botble's Post edit view has a named `sidebar` slot we can register into, or if we need the 10-line DOM-reorder script. Research task at top of S58.
4. **DataTables vs. native tables.** Some Botble list pages use DataTables (the Posts list does). Our CSS needs to also target `.dataTables_wrapper table` — check for specificity collisions in S52.
5. **Filament admin.** If Botble v7+ starts leaning on Filament-Vue components (e.g. the Media manager), our CSS may not reach them. Log any escaped-styling surface in the S52 checkpoint; address in S61 or a follow-up.
6. **License gate.** `project_grimbanews_next_prompt.md` notes admin license is bypassed in dev via settings table. On prod, confirm this redesign survives whatever license/front-door Botble enforces. No code change expected, but verify in a staging smoke.

---

## 6. Rollout Strategy

- **Branch:** all 11 sprints on a single feature branch `feat/admin-cinematic` off `main`. No feature flag — this is additive CSS + one new route. The admin is internal-only (editors + Vader), so no user-facing risk of a bad interim state.
- **Local-only until Checkpoint 1.** No push to prod until S50-S52 land and the regression audit clears. Vader reviews the checkpoint, we merge to `main`, push to `darkvaderfr`.
- **Production deploy cadence:** merge after each checkpoint (3 deploys total). Each merge is a named tag: `admin-v0.1` (chrome), `admin-v0.2` (cockpit + bias UI), `admin-v0.3` (editor card + subscribers).
- **Rollback plan:** every sprint adds either (a) one CSS file loaded via a single PHP hook, or (b) one blade file / one route. Rollback = revert the PHP hook; the admin falls back to stock Botble instantly. No destructive DB work, no Botble-core edits, no migrations.
- **Staging:** we do not currently have a GrimbaNews staging environment. S61 follow-up = spin one up on the VPS under `uat.grimbanews.com` before we accept bigger admin changes. For this fleet, local + prod is acceptable because each sprint is reversible in < 60 seconds.
- **SOK gate:** after S61, formal SOK review (Steve, Elon, Vader) — ship / iterate / kill per sprint. Write the outcome into a new `GRIMBANEWS_ADMIN_CINEMATIC_SOK.md` so the next session can resume cleanly.

> **Steve (closing review):**
> *"This is the right plan. Don't over-build the cockpit on day one — ship the tokens first, let Vader feel the difference, then put data in the tiles. And when it's done, no one should be able to tell where the reader ends and the admin begins. That's the test. That's always the test."*

---

*End of plan. ~2,400 words. Return path: `/Users/vb/kaizen/GRIMBANEWS_BACKEND_REDESIGN_PLAN.md`.*
