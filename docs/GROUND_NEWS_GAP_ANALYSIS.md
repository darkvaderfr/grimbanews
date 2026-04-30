# GrimbaNews ↔ Ground.news — Gap Analysis

**Lead:** Steve Jobs (CPO)  
**Contributors:** Alex Morgan (UI/UX), Nina Patel (Lead FE), Liam Smith (PM)  
**Reads:** `GROUND_NEWS_RESEARCH.md` (must read first)  
**Date:** 2026-05-01  
**Scope:** front-end only. Backend ports (real per-source factuality scores from AllSides/Ad Fontes/MBFC, AI bias-comparison summary generation, server-side image proxy) are deferred to a separate fleet.

Verdict columns:
- ✅ at-parity — we ship something equivalent or better
- 🟡 partial — we ship a compressed / lower-fidelity version
- ❌ missing — we don't ship this at all
- ✂️ deliberately out of scope — front-end-only, this is a backend port

---

## A. Bias visualization

| Element | Ground | GrimbaNews today | Verdict | Priority |
|---------|--------|------------------|---------|----------|
| 3-tier coverage bar (story-level) | yes | yes (`partials/coverage-bar.blade.php`) | ✅ | — |
| Edition-aware color flip (blue=left vs red=left) | yes | no — we render FR convention on every edition | 🟡 | P1 |
| 7-tier source bias chip | yes (Far Left … Far Right) | 3-tier only (`bias_rating` enum: left/center/right + blindspot) | ❌ | P0 |
| Bias chip on every coverage-row | yes | partial — chip on story page coverage list, but only 3-tier | 🟡 | P0 |
| 1-source story handling (single segment) | yes | renders empty bar today | 🟡 | P1 |
| Bar click → filter coverage list to that side | yes | not wired | ❌ | P1 |
| Bias-bar explainer page | yes (`/bias-bar`) | none | ❌ | P1 |

## B. Factuality

| Element | Ground | GrimbaNews today | Verdict | Priority |
|---------|--------|------------------|---------|----------|
| 5-tier factuality (Very Low → Very High) | yes | integer `credibility_score` 0–100 (no tier rendering) | 🟡 | P0 |
| Factuality chip on coverage-row | yes | no | ❌ | P0 |
| Factuality chip on source ranking page | yes | no | ❌ | P1 |
| Sourced from 2 indep agencies (Ad Fontes + MBFC) | yes | seeded by editorial team | ✂️ | backend |

## C. Ownership

| Element | Ground | GrimbaNews today | Verdict | Priority |
|---------|--------|------------------|---------|----------|
| 8-category ownership classification | yes | `owner_name` text field + nothing else | 🟡 | P0 |
| Ownership chip on coverage-row | yes | no | ❌ | P1 |
| "Owned by:" line on story page | yes | no | ❌ | P1 |
| Source detail page lists ownership category | yes | no source detail page exists | ❌ | P0 |

## D. Story / coverage page

| Element | Ground | GrimbaNews today | Verdict | Priority |
|---------|--------|------------------|---------|----------|
| Headline + lede | yes | yes | ✅ | — |
| Bias bar in header | yes | yes (S171) | ✅ | — |
| Bias Comparison Summary (3-column AI framing) | yes | extractive single synthesis (S175 + S183 dots) | 🟡 | P0 |
| Ground Summary (extractive across sources) | yes | yes (S175) | ✅ | — |
| Full-coverage list with logo/name/bias/factuality/ownership/paywall/country | partial in current code (logo + name + 3-tier bias only) | 🟡 | P0 |
| Coverage timeline | yes | yes (S180, cluster ≥3) | ✅ | — |
| Compare 2–3 sources side-by-side modal | yes | no | ❌ | P1 |
| NobuAI editorial perspective summary | (no equivalent — Ground stays neutral) | yes (NobuAI EIC) | **differentiator we keep** | — |
| Save / share / follow actions | yes | save+follow yes, share partial | 🟡 | P2 |

## E. Source detail page

| Element | Ground | GrimbaNews today | Verdict | Priority |
|---------|--------|------------------|---------|----------|
| Source detail page exists at all | yes (`/source/{slug}`) | no — `/sources/{slug}` 404s | ❌ | P0 |
| Header with logo + name + country flag | yes | n/a | ❌ | P0 |
| 3 big chips (bias / factuality / ownership) | yes | n/a | ❌ | P0 |
| Recent stories grid | yes | n/a | ❌ | P0 |
| Sources-with-similar-bias rail | yes | n/a | ❌ | P1 |

## F. Source ranking page

| Element | Ground | GrimbaNews today | Verdict | Priority |
|---------|--------|------------------|---------|----------|
| `/sources` route | yes | yes | ✅ | — |
| Filter by bias | yes | yes (S101+) | ✅ | — |
| Filter by factuality | yes | partial (no tier filter) | 🟡 | P1 |
| Filter by ownership | yes | no | ❌ | P1 |
| Filter by country | yes | partial (region scope) | 🟡 | P2 |
| Per-source 7-tier bias chip | yes | 3-tier | 🟡 | P0 |
| Per-source factuality tier chip | yes | numeric score | 🟡 | P0 |
| Per-source ownership chip | yes | none | ❌ | P1 |
| Click row → source detail | yes | row click does nothing | ❌ | P0 (depends on E) |

## G. Topic / Interest pages

| Element | Ground | GrimbaNews today | Verdict | Priority |
|---------|--------|------------------|---------|----------|
| Topic hub layout | yes | category page exists, generic Bootstrap | 🟡 | P1 |
| Topic-aggregate bias breakdown bar | yes | no | ❌ | P1 |
| Top sources for this topic | yes | no | ❌ | P2 |
| Topic-specific blindspots filter | yes | no | ❌ | P2 |
| Related interests rail | yes | no | ❌ | P2 |

## H. Blindspot feed (`/angles-morts`)

| Element | Ground | GrimbaNews today | Verdict | Priority |
|---------|--------|------------------|---------|----------|
| Header explanation | yes | yes | ✅ | — |
| Tabs: All / For Left / For Right | yes | bias filter on /coffre (S184), not on /angles-morts | 🟡 | P0 |
| Per-card blindspot badge | yes | yes | ✅ | — |
| Per-card coverage % gap | yes | partial | 🟡 | P1 |
| Per-card source count + 2–3-sentence summary | yes | yes | ✅ | — |
| "View International Blindspots" toggle | yes | edition toggle covers this implicitly | ✅ | — |

## I. Homepage

| Element | Ground | GrimbaNews today | Verdict | Priority |
|---------|--------|------------------|---------|----------|
| Daily Briefing hero | yes | partial (top-news + hero card) | 🟡 | P1 |
| All-sides rail | yes | yes (S154) | ✅ | — |
| Trending topic chip strip | yes | yes (S100 chips) | ✅ | — |
| Coverage bar on every card | yes | partial — many cards omit it | 🟡 | P0 |
| Edition switcher | yes (US Edition badge) | yes (Afrique/International toggle) | ✅ | — |
| Most-read by bias | yes | yes (S165 most-read-by-bias) | ✅ | — |
| Latest stories feed | yes | yes | ✅ | — |
| Sidebar reserved for ads | yes | yes (`grimba_home_*`) | ✅ | — |

## J. Newsletters

| Element | Ground | GrimbaNews today | Verdict | Priority |
|---------|--------|------------------|---------|----------|
| Daily newsletter ("Daily Ground" / GrimbaNews equivalent) | yes | partial (newsletter signup exists, no content yet) | 🟡 | backend |
| Blindspot Report newsletter | yes | none | ❌ | backend |
| "Burst Your Bubble" newsletter | yes | none | ❌ | backend |

(All newsletter content generation is server-side; the **rendering** of newsletter HTML is front-end and we'll spec it but defer execution to a templating sprint.)

## K. Methodology / explainer pages

| Element | Ground | GrimbaNews today | Verdict | Priority |
|---------|--------|------------------|---------|----------|
| Bias Bar explainer page | yes | none | ❌ | P0 |
| Rating System page | yes | none | ❌ | P0 |
| Media Bias position page | yes | none | ❌ | P1 |
| About page | yes | none | ❌ | P1 |
| FAQ | yes | none | ❌ | P1 |

## L. Brand differentiators we keep (not gap items, our edge)

1. **Steve's cinematic glass over newsprint** — paper bg, Fraunces serif, glass panels. Ground uses neutral sans-serif throughout; we look like a magazine, they look like a dashboard.
2. **Cookie-only persistence** — no auth required for vault, follow, theme, region. Ground requires signup for personalization.
3. **NobuAI editor-in-chief perspective** — a francophone-grounded "Perspective africaine" lens that Ground doesn't attempt.
4. **Single-language editorial control** with on-the-fly NobuAI translation — Ground is English-only (with translation labels).
5. **Multi-edition with deliberate Africa focus** — Ground has a US edition + international flip; we have Africa as a first-class edition with its own news rail.
6. **No paywall on blindspots** — Ground paywalls blindspots after a few reads; we don't (paid tier is full-article reading).

---

## Priority cuts for this fleet

### P0 — must ship for Ground-fidelity parity
1. 7-tier bias chip rendering on every source row (story coverage list, source ranking page)
2. 5-tier factuality chip rendering on every source row
3. 8-category ownership chip + "Owned by:" line
4. Bias bar on every feed card (currently inconsistent)
5. Source detail page at `/sources/{slug}`
6. Bias Bar explainer page (`/comprendre-le-barometre`)
7. Rating System page (`/methodologie`)
8. Bias filter tabs on `/angles-morts` (All / Pour la gauche / Pour la droite)
9. Compressed Bias Comparison Summary (3-column framing) — uses our existing extractive synthesis, presents it as 3 columns instead of 1 list
10. Compare-sources modal (pick 2–3 from a coverage list, side-by-side)

### P1 — strong differentiation
11. Edition-aware bias color flip with explainer footnote
12. Topic-aggregate bias bar on category pages
13. Sources-with-similar-bias rail on source detail
14. Coverage bar segment click → filter list
15. About page
16. FAQ page
17. Media bias position page

### P2 — defer to next fleet
18. Filter by ownership on source ranking
19. Filter by country on source ranking
20. Topic-specific blindspots filter
21. Related interests rail
22. Read time + paywall icon defense across all card variants
23. Reading-history bias breakdown

### Backend / out of scope
- Real per-source factuality from AllSides/Ad Fontes/MBFC
- AI bias-comparison summary generation
- Server-side image proxy / cache
- Newsletter content generation pipeline

---

## What we ship in this fleet

Everything in **P0 + P1**, in that order, until the visual fidelity matches Ground or until obvious diminishing returns. P2 stays in backlog.
