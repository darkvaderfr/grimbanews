# Ground-Fidelity Fleet (Front-End)

**Lead:** Steve Jobs (CPO)  
**Co-leads:** Alex Morgan (UI/UX), Nina Patel (Lead FE)  
**PM:** Liam Smith  
**Backend liaison (data shape only):** Lisa Nguyen  
**QA:** Sara Kim, Zenkai  
**Audit:** Zen, Echo, Mnemo  
**Reads first:** `GROUND_NEWS_RESEARCH.md`, `GROUND_NEWS_GAP_ANALYSIS.md`  
**Date:** 2026-05-01  
**Cadence:** every sprint = single coherent commit, push to `darkvaderfr/grimbanews:main` immediately, no batching.

---

## Wave 0 — Foundation (the chip system)

These four sprints build the reusable atoms every later wave depends on. Ship in order.

### S301 — 7-tier bias chip atom + helper
- New partial: `partials/bias-chip.blade.php` taking `$bias` (string slug) + `$size` (sm/md/lg)
- New helper: `App\Support\GrimbaBias::tier($source)` returning one of `far_left | left | lean_left | center | lean_right | right | far_right` from existing `bias_rating` + a derived heuristic for sub-tiers (we don't need a migration; we infer lean_* / far_* from `credibility_score` + `bias_rating` for now)
- Color tokens: `--gn-bias-far-left: #1d4ed8`, `--gn-bias-left: var(--gn-left)`, `--gn-bias-lean-left: #7aa6f9`, `--gn-bias-center: var(--gn-center)`, `--gn-bias-lean-right: #f49b94`, `--gn-bias-right: var(--gn-right)`, `--gn-bias-far-right: #b91c1c`
- Acceptance: chip renders for all 7 tiers, contrast-tested on light + dark, sized correctly for inline-with-text usage and standalone label usage

### S302 — 5-tier factuality chip atom + helper
- New partial: `partials/factuality-chip.blade.php` taking `$score` (int 0–100) + `$size`
- Helper: `App\Support\GrimbaFactuality::tier($score)` mapping score to `very_low | low | mixed | high | very_high`
- Color tokens: `--gn-fact-very-low: #b91c1c`, `--gn-fact-low: #e84c3d`, `--gn-fact-mixed: #f59e0b`, `--gn-fact-high: #16a34a`, `--gn-fact-very-high: #14532d`
- Acceptance: chip renders for all 5 tiers, includes a check/warn glyph

### S303 — 8-category ownership chip atom + helper
- New partial: `partials/ownership-chip.blade.php` taking `$category` (slug) + `$size`
- Helper: `App\Support\GrimbaOwnership::category($source)` deriving category from `owner_name` heuristics + a small static map of well-known parent companies (Bouygues, Bolloré, NYTCo, BBC, RFI, etc.)
- Categories: `conglomerate | private_equity | individual | government | telecom | corporation | independent | other`
- Acceptance: chip renders with appropriate icon for each category (use existing Tabler icon set already loaded by chrome)

### S304 — Coverage bar v2: edition-aware + 1-source edge case
- Update `partials/coverage-bar.blade.php` to handle 1-source story (single 100% segment)
- Add hover affordance (tiny tooltip with raw counts)
- Edition-aware color flip: store the convention as a config so we can add it later without touching the partial; for now keep FR convention everywhere
- Acceptance: bar renders correctly for edge cases, hover reveals counts, no visual regression on existing usage

---

## Wave 1 — Story page (the most valuable surface)

### S305 — Full coverage list redesign with the new chips
- Update `partials/story/full-coverage.blade.php` (create if missing) to render each source row as: logo / name / 7-tier bias chip / 5-tier factuality chip / ownership chip / paywall icon / country / source headline / time / external-link button
- Acceptance: list renders for a story with mixed sources, all chips reflect tier correctly, mobile stacks cleanly

### S306 — Bias Comparison Summary (3-column framing)
- New partial: `partials/story/bias-comparison-summary.blade.php`
- Compresses our existing extractive synthesis into 3 columns (Gauche / Centre / Droite)
- Each column: 1–2 representative source headlines + framing word/phrase highlights
- AI disclosure footnote: "Synthèse rédactionnelle assistée par NobuAI · Vérification humaine en bouclage"
- Acceptance: 3 columns side-by-side desktop, stacked tabs on mobile, source attribution under each headline

### S307 — Compare-sources modal
- New partial + JS: `partials/story/compare-modal.blade.php` + inline JS
- User checkboxes 2–3 source rows in the full-coverage list, hits "Comparer", modal opens with side-by-side headlines + (if available) lede sentences
- Acceptance: keyboard-navigable, escape closes, focus trap, NoJS fallback gracefully degrades to a "select on the page" anchor link

### S308 — Bias bar segment click filters coverage list
- Wire `partials/coverage-bar.blade.php` segments as buttons; clicking left filters coverage list to left sources, etc.
- Use a tiny URL hash (`#coverage=left`) so bookmarking works
- Acceptance: filter applies on click, hash updates, "Reset" link clears

---

## Wave 2 — Source detail page (a missing surface)

### S309 — Source detail page route + view
- New route: `Route::get('sources/{slug}', ...)` already exists in admin; expose public version
- New view: `views/source-detail.blade.php` extending `grimba-chrome`
- Sections: header (logo + name + flag) / 3 chips (bias + factuality + ownership) / "Owned by:" line / recent stories grid (12 most recent posts from this source) / "Sources avec un biais similaire" rail / methodology footer
- Acceptance: route resolves, all chips render, recent stories load, similar-bias rail shows 3 sources

### S310 — Source ranking page chip upgrade
- Update `views/sources.blade.php` (or wherever the index lives) to render the new bias + factuality + ownership chips on every row
- Add row click → source detail page
- Acceptance: every row carries 3 chips and is clickable

### S311 — Source ranking filter chips: factuality
- Add factuality tier filter pills to `/sources` page
- Pills: Toutes · Très haute · Haute · Mixte · Basse · Très basse
- URL state: `?factuality=high`
- Acceptance: pill filter works, count updates, URL state preserved on reload

---

## Wave 3 — Methodology / explainer pages

### S312 — Bias Bar explainer page
- New route: `Route::get('comprendre-le-barometre', ...)`
- New view: `views/explainer-bias-bar.blade.php`
- Sections: what the bar shows, edition convention (FR uses blue=L red=R always), edge cases (1 source, all-one-side, equal split), aggregation methodology, sources (AllSides + Ad Fontes + MBFC + GrimbaNews editorial)
- Visual examples: 4 inline bias bars showing the edge cases in action
- Acceptance: page renders, link from every bias bar's "?" tooltip points here

### S313 — Rating System page
- New route: `Route::get('methodologie', ...)`
- New view: `views/methodologie.blade.php`
- Sections: 7-tier bias (with chip + definition), 5-tier factuality, 8-category ownership, blindspot definition (the formula in plain language), AI usage policy
- Acceptance: every chip from S301-S303 appears in this page with its definition

### S314 — Coverage card refactor — bias bar always present
- Audit every card variant (`partials/article-card.blade.php`, `partials/blog/post-mixed.blade.php`, `partials/home/top-news-inline.blade.php`, hero card, all-sides-rail card)
- Ensure each one always renders a coverage bar (or single-source bias chip if N=1) — Ground does this on 100% of cards, we do it on ~70%
- Acceptance: visual sweep at 1440 desktop + 390 mobile shows a coverage indicator on every card

---

## Wave 4 — Blindspot feed and topic hubs

### S315 — Blindspot feed bias filter tabs
- Add tabs to `/angles-morts`: Tous · Pour la gauche · Pour la droite · International (the international tab maps to `is_international_blindspot=1`)
- Acceptance: tabs filter, URL state preserved, count updates

### S316 — Topic page upgrade
- Update category route view to add: topic-aggregate bias breakdown bar at top + "Top sources for this topic" rail
- Acceptance: category page now feels like a hub, not a search result

---

## Wave 5 — Brand pages

### S317 — About page
- New view: `views/about.blade.php`
- Sections: brand thesis (cinematic glass over newsprint, francophone-grounded), team section (real Iboga roster, NOT fabricated), methodology link, contact
- Acceptance: page renders, links to methodology page, footer link added

### S318 — FAQ page
- New view: `views/faq.blade.php`
- 8–12 Q&A grouped: méthodologie, biais, paywall, NobuAI, données utilisateur, RGPD
- Acceptance: page renders, accordion-collapsed Q&A, anchor-linked questions

---

## Wave 6 — Polish + close-out

### S319 — Final visual sweep + audit panel
- Headless Playwright on /, /article/{slug}, /sources, /sources/{slug}, /angles-morts, /coffre, /pour-vous, /comprendre-le-barometre, /methodologie, /a-propos, /faq
- Capture screenshots in light + dark, desktop + mobile (390x844)
- Run audit panel: Zen / Echo / Mnemo
- Acceptance: every screenshot looks Steve-quality, no broken cards, no missing chips, no overlay regressions

### S320 — Memory close + documentation freshness
- Update `project_grimbanews_next_prompt.md` with the closing state
- Mark every wave's commit SHA in the resume note
- Push final commit

---

## Out of scope (queued for next fleet)

- Server-side publisher-image proxy / cache (carry-over from Session 9 — needs backend sprint)
- Real bias / factuality data integration (AllSides + Ad Fontes + MBFC API or scrape)
- AI-generated bias comparison summary (NobuAI prompt eng)
- Full ownership database (hand-coded for 50+ francophone outlets)
- "My News Bias" reading-history breakdown
- Browser extension
- Mobile app

---

## Cadence rules

- Each sprint = one commit. No bundling.
- Push to `darkvaderfr/grimbanews:main` immediately after commit (no PRs unless Vader asks).
- After every commit: brief verify via headless Playwright OR (if blocked) via curl + grep + visual inspection of rendered HTML.
- After every 5 sprints: brief Zen audit pass.
- After Wave 6: full Zen / Echo / Mnemo audit panel before claiming done.
- Don't touch `CLAUDE.md` (unrelated local change).
- Don't run migrations.
- Don't `git add -A` — stage by name.
- Co-author trailer required.
- If a sprint's verify fails, don't push — debug, fix, re-verify, push.
