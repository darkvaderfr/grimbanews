# GrimbaNews — Middle Ground Feature Changelog

Reverse-chronological log of every surface change in the Middle Ground editorial-signal feature. Helps a future operator (or auditor) understand how the feature was built — and why each pulse landed.

## 2026-05-26 — Loop 12 marathon

### Wave SSSS (`cdb8e9cb`)
Schema.org Dataset block on /methodologie. /api/middle-ground.json now discoverable via Google Dataset Search + Kaggle/Zenodo academic crawlers. CC-BY-4.0 license declared.

### Wave RRRR (`523894ce`)
`/api/middle-ground.atom` — Atom 1.0 sibling of the JSON endpoint. RSS readers + IFTTT-style automators get parity. Same data, different wrapper. 8 assertions.

### Wave QQQQ (`73b704db`)
`docs/GRIMBANEWS_MIDDLE_GROUND_API_REFERENCE.md` — v1 API docs. Enhances S1181 (Public API v2 design) Mythos row.

### Wave PPPP (`70e8deb6`)
10 EN translations for Wave LLLL/OOOO/EEEE FR copy.

### Wave OOOO (`57fbbf9e`)
Methodology §6 bis "Public API for the Middle Ground signal" with `#api` anchor. Links researchers/devs from the methodology doc to the JSON endpoint.

### Wave NNNN (`560249ed`)
**`/api/middle-ground.json`** — public read-only API. CORS open, 15-min cache, limit clamped [1, 200]. 29 assertions. Turns the editorial signal into a data product.

### Wave MMMM (`66a717e2`)
Smoke test extended to walk a real mg_-tagged cluster's /comparatif/{id} page.

### Wave LLLL (`4514f18c`)
Per-cluster "Juste milieu depuis N jours" header badge on /comparatif/{id}. Renders only when cluster carries `mg_` tag in `story_clusters.review_action`. Purple pill, aria-labeled, deep-links to /juste-milieu.

### Wave JJJJ (`d6d80eb3`)
Bugfix: Wave HHHH's `@foreach @php { $biasColor = match(...) }` shadowed the page-level `$biasColor` array → AdminRouteSmokeTest broke. Renamed to `$srcBiasDotColor`. Mnemo "go slow" rule logged.

### Wave IIII (`c6c36e87`)
`grimba:reclassify-clusters --json` mode for ops pipes. Suppresses all decorative text, emits a single JSON line. Schema: walked_limit, persist, totals (left/center/right/middle_ground/blindspot/balanced/unclassified), clusters_touched, generated_at.

### Wave HHHH (`2ae86553`)
Cockpit admin "Sources qui ancrent le juste milieu" — top-5 sources by MG contribution. Live data shows Le Figaro (right, 9 articles) leads, then Le Monde (left, 7), Libération (left, 7).

### Wave GGGG (`b8ecc3e5`)
9-surface smoke test in a single pass. Catches the exact class of bug Echo PARTIAL escalation found this loop.

### Wave FFFF (`680bee73`)
Lock test for `/dossiers?diversity=middle_ground` + `?blindspot` + 4 other tabs.

### Wave EEEE (`19018fdf`)
/angles-morts → /juste-milieu symmetric cross-link in blindspot page header. 4-card OG regeneration test (home/local/coffre/juste-milieu).

### Wave DDDD (`c7022722`)
Mythos walker pack 3 — 60 deferred → partial via 60 surrogate docs (mobile shell + push + native release pipeline + API v2 + author/byline + corrections + partnership analytics + case study).

### Wave CCCC (`edcbe0f0`)
/juste-milieu empty-state cinematic polish per Steve's design language standard. Hero typography (Fraunces 28px), purple gradient sweep, 48px ⊕ glyph, max-w-520px lede, cadence monitor footer.

### Wave BBBB (`edcbe0f0`)
GrimbaClusterBias::resolve() defensive-input tests (5 new cases: missing keys, null values, negative counts). Total 37 assertions on the resolver.

### Wave AAAA (`12b11372`)
sitemap-grimba.xml image:image extension (9 cards) + /juste-milieu Schema.org enhancement (real itemListElement[] + additionalType + inLanguage + image + about.description).

### Wave ZZZ (`e9cafc3f`)
4 aria-label EN translations — closes Echo PARTIAL gap on screen-reader-on-EN users hearing French.

### Wave YYY (`e9cafc3f`)
**bias-legend site-wide 404 fix** — `/methodology` (404) → `/methodologie` (200). bias-legend ships on every category + feed page, so this was a live site-wide regression. Plus bias-legend chips now clickable deep-links to /juste-milieu + /angles-morts with aria-labels.

### Wave XXX (`7ef19e08`)
6 EN translations + 3 aria-labels for the Wave UUU/RRR/SSS copy.

### Wave WWW (`5c7ec695`)
Mythos walker pack 2 — 30 deferred → partial via 30 surrogate docs (per-locale catalogs ES/PT-BR/DE/IT/AR/HE/JA/ZH/KO/RU/HI/SW + RTL + high-contrast theme + per-locale ad consent/pricing/comms).

### Wave VVV (`762c5813`)
`/health` adds `middle_ground_clusters_24h` velocity key. External monitors can detect MG signal drying up.

### Wave UUU (`f2132cd4`)
/juste-milieu CTAs added to /search, /404, /account dashboard. Methodology cross-link from /juste-milieu header. Empty-state copy fixed (was leaking admin Artisan command to readers).

### Wave TTT (`9b0f52b4`)
Pattern-sweep for tie-breaking reducer bugs — found `category.blade.php:59` using same broken `collect(...)->sortDesc()->keys()->first()` pattern. **Live UX fix** (Botble BlogService.php:154 wires the view via /blog/{slug}, verified on /blog/immigration). Plus 9-case resolver lock test.

## 2026-05-26 — Loop 11

### Wave SSS (`2dbbb772`)
`grimba:rebuild-og` command — operator's OG cache invalidation surface. Closes Zen YELLOW from earlier audit.

### Wave LLL (`9e18783e`)
Mythos walker pack 1 — 32 deferred → partial via 32 surrogate docs (monetization, retention, editorial workflow v2, partnership program, search v2, personalization v2, moderation, reader product v2, local/tools/data, API+observability+tutorial).

### Wave RRR (`a08a6722`)
**bias-distribution.blade.php fix** — the EXACT bug Vader screenshotted 2026-05-23. Line 71 `collect($pct)->sortDesc()->keys()->first()` picked 'left' on tied clusters. Verified live on `/article/tchernobyl-40-ans-apres-...` — now reads "Camp majoritaire: Juste milieu". coverage-details.blade.php same class of fix (defense-in-depth — S341 dropped from live page). Regression test prevents recurrence.

### Wave PPP (`92d63bef`)
Cockpit admin "Signal éditorial" tile — MG + Blindspot cluster counts + ratio + 3 deep-links.

### Wave OOO (`92d63bef`)
Methodology §3 bis "Qu'est-ce que le juste milieu" explainer with `#juste-milieu` anchor + EN translations.

### Wave NNN (`9ba2349d`)
`/og/juste-milieu.png` dedicated GD share card (purple #a855f7, 1200×630). /juste-milieu page now advertises its own OG instead of falling back to home.

### Wave MMM (`9ba2349d`)
`grimba:health --min-middle-ground-clusters=N` floor flag + ops report row.

## 2026-05-23 — Loop 10 (initial Middle Ground feature)

Earlier waves CCC through KKK established the Middle Ground feature across 11 initial surfaces: GrimbaClusterBias::resolve helper, bias-legend chip, /juste-milieu listing route, /feed.juste-milieu.xml RSS, sitemap entry, source-diversity-meter tag, GrimbaSourceBreakdown wiring, GrimbaHomeFeed hero rail, GrimbaReclassifyClusters command (daily 03:35 UTC), /dossiers diversity filter, /health JSON middle_ground_clusters key. Initial corpus reclassify: 21 MG clusters identified from 722 total.

## Cross-references

- Resume prompt: `~/.claude/projects/-Users-vb-kaizen/memory/project_grimbanews_next_prompt.md`
- API reference: `docs/GRIMBANEWS_MIDDLE_GROUND_API_REFERENCE.md`
- Master plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md`
- Resolver: `app/Support/GrimbaClusterBias.php`
- Classifier: `app/Console/Commands/GrimbaReclassifyClusters.php`
- Lock tests: `tests/Feature/GrimbaLaunchReadinessTest.php` (search for `middle_ground` or `juste_milieu`)
