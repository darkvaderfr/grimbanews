# GrimbaNews Pre-Production 1000-Sprint Master Plan

**Status:** draft for execution  
**Created:** 2026-04-29  
**Scope:** full pre-production overhaul, enhancement, and release hardening before any production move  
**Local repo:** `/Users/vb/GrimbaNews`  
**Local server target:** `http://127.0.0.1:8002`  

This plan supersedes the short next-sprint queue for pre-production planning only. It does not erase the shipped sprint ledger in `docs/GRIMBANEWS_SPRINT_PLAN.md` or the older Mythos fleet in `docs/MYTHOS_SPRINT_FLEET.md`.

The plan starts by reviewing what is already shipped, then turns that review into an iteration and enhancement map for the full product: ingest, NobuAI, translation, GroundNews-style analysis, public UX, admin UX, monetization, reliability, tests, security, observability, deployment, and post-launch growth.

All contributors should also follow `memory.md` and `docs/GRIMBANEWS_TANDEM_WORK_PROTOCOL.md`: keep the team moving in tandem, pick the next unblocked atomic sprint outcome, and leave evidence for every completed work block.

## Inputs Reviewed

- Current shipped ledger through the latest GrimbaNews automation, translation, admin, and public UX sprints.
- Mythos sprint fleet artifact already in the repo.
- Backend/data review lane: ingestion, scheduler, NobuAI, translation, data integrity, provider vault, and production operations.
- Frontend/UX review lane: homepage hierarchy, editions, dark/light mode, overlays, source logos, story readability, ads, accessibility, and visual regression.
- QA/release review lane: executable release gate, CI, browser E2E, visual regression, scheduler contract, live smoke, static analysis, security, rollback, and evidence reporting.
- Canonical Iboga roster directive: use only named contributors from the roster; do not fabricate team members.

## Operating Rules

- No production deployment until release gates in this document are green.
- Every sprint must leave evidence: commit SHA, tests or smoke result, changed files, risks, and next dependency.
- Reader-facing AI copy says `NobuAI` only. Provider names stay behind admin authentication.
- Public pages must pass light mode, dark mode, mobile, desktop, and incognito checks.
- Scheduler and ingest must be autonomous. Admin manual actions are controls, not daily operating requirements.
- Ads must be labeled, consent-aware, layout-stable, and suppressible for subscriber tiers.
- Source, bias, credibility, factuality, and ownership analysis must display confidence and unknown-state handling.
- The plan is intentionally broader than code tasks: editorial, ops, QA, growth, revenue, and compliance all block production readiness.

## Team Lanes

| Lane | Canonical contributors |
|---|---|
| Product and design | Steve Jobs, Liam Smith, Alex Morgan, Marissa Mayer |
| Frontend and reader UX | Nina Patel, Lisa Nguyen, Alice Chen |
| Backend and platform | Rajesh Kumar, Larry Ellison, Hannah Kim, Jacob Lee |
| Data and intelligence | Benjamin Lee, David Chen, Larry Ellison |
| QA and release | Sara Kim, Zenkai, Echo, Mnemo |
| Security and privacy | Sara Chen, Maya Patel, Sam Harris |
| Editorial and growth | Lucy Leai, Gary Vaynerchuk, Maria Lopez, Olivia Davis, Henry Walker, Robert Iger |
| Revenue and operations | Ray Dalio, Sheryl Sandberg, Warren Buffett, Victor Garcia |
| Documentation and support | Michael O'Connor, Emma Brown, Ethan Wilson |

## Release Gates

| Gate | Required outcome |
|---|---|
| G1 Current-state review | Every shipped GrimbaNews route, command, scheduler job, and admin surface is inventoried. |
| G2 Autonomous publishing | RSS and NewsAPI run without daily dashboard work and publish through guardrails. |
| G3 NobuAI readiness | Insight, translation, provider health, failure redaction, and cost controls pass tests. |
| G4 Public UX readiness | Home, editions, sources, story, comparison, search, local, auth, subscriber, and ads pass visual QA. |
| G5 Admin readiness | Cockpit, provider keys, RSS, NewsAPI, sources, triage, clusters, translations, cookies, and ads pass browser QA. |
| G6 Data readiness | Migrations, indexes, backups, restore, dedupe, clustering, source metadata, and article extraction are proven. |
| G7 Security readiness | Auth, CSRF, admin-only data, API key redaction, cookies, image proxy, and legal surfaces are verified. |
| G8 Performance readiness | TTFB, queries, asset weight, images, CLS, queue latency, and cache behavior meet budgets. |
| G9 Release readiness | CI, E2E, visual diff, scheduler smoke, rollback drill, and release evidence report are green. |
| G10 Business readiness | Monetization, subscriber value, editorial workflow, analytics, support docs, and launch monitoring are complete. |

## 1000-Sprint Registry

Each row below contains 10 atomic sprint IDs. The row is not a single epic; the comma-delimited items are the individual sprints to execute and close with evidence.

## Sprint Evidence Ledger

| Sprint | Evidence | Status |
|---|---|---|
| S001 | `docs/GRIMBANEWS_S001_ROUTE_INVENTORY.md` | complete |
| S002 | `docs/GRIMBANEWS_S002_ADMIN_ROUTE_INVENTORY.md` | complete |
| S003 | `docs/GRIMBANEWS_S003_COMMAND_INVENTORY.md` | complete |
| S004 | `docs/GRIMBANEWS_S004_SCHEDULER_INVENTORY.md` | complete |

| Sprint IDs | Program | Atomic sprint outcomes |
|---|---|---|
| S001-S010 | Current-state review | route inventory, admin route inventory, command inventory, scheduler inventory, model inventory, migration inventory, public surface inventory, admin surface inventory, shipped commit map, unresolved risk register |
| S011-S020 | Current-state review | content volume audit, source volume audit, cluster quality audit, translation storage audit, NobuAI storage audit, provider setting audit, ad setting audit, cookie audit, cache audit, test coverage audit |
| S021-S030 | Current-state review | homepage review, story page review, comparison review, source page review, search review, local page review, auth review, subscriber review, newsletter review, PWA review |
| S031-S040 | Current-state review | cockpit review, provider vault review, RSS admin review, NewsAPI admin review, source triage review, cluster admin review, coverage map review, translation admin review, cookie admin review, ads admin review |
| S041-S050 | Current-state review | light theme audit, dark theme audit, mobile audit, desktop audit, incognito audit, Safari audit, Chrome audit, Firefox audit, screen reader audit, keyboard audit |
| S051-S060 | Governance | definition of ready, definition of done, production freeze policy, release branch policy, sprint evidence format, risk severity rubric, rollback owner map, data owner map, QA signoff map, launch signoff map |
| S061-S070 | Governance | daily review cadence, defect triage cadence, source approval cadence, provider cost review, editorial review cadence, security review cadence, performance review cadence, accessibility review cadence, growth review cadence, launch readiness board |
| S071-S080 | Governance | backlog label taxonomy, sprint dependency graph, no-prod-deploy guard, emergency fix policy, secret handling policy, NobuAI copy policy, provider naming policy, ad consent policy, subscriber entitlement policy, data retention policy |
| S081-S090 | Governance | environment matrix, local parity checklist, staging parity checklist, production variable checklist, cron responsibility matrix, queue responsibility matrix, alert ownership matrix, incident role map, support escalation map, launch comms map |
| S091-S100 | Governance | release evidence template, smoke evidence template, visual evidence template, performance evidence template, security evidence template, data evidence template, editorial evidence template, revenue evidence template, support evidence template, final pre-prod checkpoint |
| S101-S110 | Ingestion | RSS source tiering, RSS feed health score, RSS timeout policy, RSS retry policy, RSS duplicate guard, RSS canonical URL normalization, RSS image extraction, RSS source fallback, RSS sick-feed quarantine, RSS recovery dashboard |
| S111-S120 | Ingestion | NewsAPI country sweep audit, NewsAPI category sweep audit, NewsAPI quota budget, NewsAPI request reservation, NewsAPI duplicate guard, NewsAPI source mapping, NewsAPI category mapping, NewsAPI image fallback, NewsAPI dry-run mode, NewsAPI live smoke |
| S121-S130 | Ingestion | Canada feed expansion, France feed expansion, UK feed expansion, US feed expansion, Africa feed expansion, International feed expansion, business feed expansion, technology feed expansion, health feed expansion, climate feed expansion |
| S131-S140 | Ingestion | politics feed expansion, science feed expansion, culture feed expansion, sports feed expansion, local feed expansion, wire service feed expansion, public broadcaster feeds, independent outlet feeds, high-trust feeds, source license notes |
| S141-S150 | Ingestion | ingestion job queue split, backpressure limits, per-source limits, per-country limits, per-category limits, auto-publish guard integration, draft pressure alerts, stuck ingest alerts, ingestion metrics export, ingestion runbook |
| S151-S160 | Publishing automation | trusted source category creation, unclassified source category creation, auto-publish rule review, draft guardrail tests, trusted-source publish smoke, unclassified-source publish smoke, failed-publish diagnostics, publish replay command, publish rollback command, publish audit log |
| S161-S170 | Publishing automation | five-times-daily cadence test, schedule contract test, cron install check, schedule monitor table, last-run dashboard, missed-run alert, overlap lock verification, background job verification, local scheduler docs, production scheduler docs |
| S171-S180 | Publishing automation | article freshness SLA, source freshness SLA, cluster freshness SLA, translation freshness SLA, NobuAI freshness SLA, full-content freshness SLA, stale article handling, stale cluster refresh, stale source alert, daily automation report |
| S181-S190 | Publishing automation | RSS-to-published smoke, NewsAPI-to-published smoke, full-content-to-subscriber smoke, NobuAI-to-story smoke, translation-to-home smoke, category-to-home smoke, edition-to-home smoke, source-to-profile smoke, search-index smoke, sitemap update smoke |
| S191-S200 | Publishing automation | autonomous day simulation, quota exhaustion simulation, provider failure simulation, bad feed simulation, duplicate storm simulation, empty edition simulation, admin manual override, safe reprocess command, safe purge command, automation signoff |
| S201-S210 | Dedup and clustering | canonical URL index, title similarity threshold, source-aware duplicate policy, cluster window policy, cross-language cluster policy, image duplicate policy, syndicated duplicate policy, update-vs-new policy, dedupe audit page, dedupe regression tests |
| S211-S220 | Dedup and clustering | cluster merge workflow, cluster split workflow, orphan cluster handling, low-source cluster handling, high-source cluster handling, source diversity target, bias diversity target, country diversity target, cluster confidence score, cluster confidence display |
| S221-S230 | Dedup and clustering | timeline normalization, first-seen timestamp, latest-seen timestamp, representative article selection, hero article selection, cluster title selection, cluster excerpt selection, cluster image selection, cluster canonical URL, cluster permalink stability |
| S231-S240 | Dedup and clustering | cluster search indexing, cluster sitemap entries, cluster RSS output, cluster metadata backfill, cluster stale refresh, cluster delete safety, cluster restore safety, cluster metrics export, cluster admin filters, cluster QA fixtures |
| S241-S250 | Dedup and clustering | synthetic duplicate fixtures, cross-language fixtures, syndicated content fixtures, wrong-source fixtures, missing-image fixtures, conflicting-date fixtures, low-confidence fixtures, high-volume fixtures, regression pack, clustering signoff |
| S251-S260 | NobuAI core | provider registry audit, provider credential vault, provider redaction tests, provider priority order, provider fallback order, provider timeout policy, provider retry policy, provider rate limit policy, provider cost guard, provider live smoke |
| S261-S270 | NobuAI core | insight prompt review, insight schema contract, insight JSON parser, malformed response handling, extractive fallback, source citation policy, bias language policy, factuality language policy, ownership language policy, provider leak prevention |
| S271-S280 | NobuAI core | cluster insight generation, article insight generation, source insight generation, bias confidence generation, factuality confidence generation, ownership summary generation, blindspot explanation generation, newsletter insight generation, search insight generation, local insight generation |
| S281-S290 | NobuAI core | stale insight refresh, manual regenerate action, batch regenerate action, partial failure display, admin failure diagnostics, reader freshness badge, confidence badge, cost dashboard, token usage dashboard, NobuAI runbook |
| S291-S300 | NobuAI core | mock success test, mock timeout test, mock rate-limit test, mock malformed test, mock fallback test, live bounded test, prompt snapshot test, provider redaction test, budget limit test, NobuAI signoff |
| S301-S310 | Translation | NobuTranslation module audit, EN-to-FR article path, FR-to-EN article path, static UI catalog audit, admin catalog audit, public catalog audit, mixed-language detection, source language detection, article language detection, locale fallback policy |
| S311-S320 | Translation | translation queue schema, translation retry policy, translation force-refresh policy, stale translation policy, missing translation badge, reader translated note, admin translated note, translation source attribution, provider leak prevention, translation cost guard |
| S321-S330 | Translation | homepage native-first sort, edition native-first sort, story native-first sort, search native-first sort, source native-first sort, blindspot native-first sort, local native-first sort, newsletter native-first sort, related stories native-first sort, fallback-last sorting |
| S331-S340 | Translation | static page FR snapshot, static page EN snapshot, homepage FR snapshot, homepage EN snapshot, story FR snapshot, story EN snapshot, search FR snapshot, search EN snapshot, auth FR snapshot, auth EN snapshot |
| S341-S350 | Translation | translation admin UX, translation backlog dashboard, translation error dashboard, translation replay command, translation purge command, translation cache policy, translation SEO hreflang, translation sitemap policy, translation metrics export, translation signoff |
| S351-S360 | Source intelligence | source logo collection, source logo proxy cache, source logo fallback, source country normalization, source language normalization, source homepage URL audit, source RSS URL audit, source ownership field audit, source credibility field audit, source bias field audit |
| S361-S370 | Source intelligence | trusted source criteria, unclassified source criteria, credibility score rubric, bias score rubric, factuality score rubric, ownership type rubric, confidence score rubric, unknown metadata policy, correction workflow, source approval workflow |
| S371-S380 | Source intelligence | source profile redesign, source cards logo display, source cards metadata display, source search facets, source country facets, source language facets, source bias facets, source credibility facets, source ownership facets, source comparison links |
| S381-S390 | Source intelligence | source triage queue, missing-logo queue, missing-bias queue, missing-owner queue, missing-credibility queue, low-confidence queue, inactive-source queue, sick-feed queue, duplicate-source queue, source admin bulk actions |
| S391-S400 | Source intelligence | source data fixtures, source logo tests, source profile tests, source triage tests, source metadata tests, source unknown-state tests, source privacy review, source legal review, source docs, source signoff |
| S401-S410 | GroundNews-style analysis | bias breakdown desktop, bias breakdown mobile, factuality breakdown desktop, factuality breakdown mobile, ownership breakdown desktop, ownership breakdown mobile, tab animation, distribution animation, legend clarity, unknown bucket display |
| S411-S420 | GroundNews-style analysis | source logo stacks, source count drilldown, left source drilldown, center source drilldown, right source drilldown, low factuality drilldown, high factuality drilldown, ownership drilldown, source excerpt anchors, source methodology link |
| S421-S430 | GroundNews-style analysis | homepage compact breakdown, story full breakdown, comparison breakdown, source page breakdown, blindspot breakdown, local breakdown, newsletter breakdown, mobile bottom-sheet breakdown, desktop side-panel breakdown, print-safe breakdown |
| S431-S440 | GroundNews-style analysis | bias percent consistency, factuality percent consistency, ownership percent consistency, small-sample warning, low-confidence warning, single-source warning, source imbalance warning, methodology copy, explainer modal, reader trust QA |
| S441-S450 | GroundNews-style analysis | chart accessibility, chart keyboard tabs, chart screen reader text, chart contrast light, chart contrast dark, chart animation reduced-motion, chart performance budget, chart visual baselines, chart tests, breakdown signoff |
| S451-S460 | Homepage UX | hero selection policy, hero readability overlay, hero fallback image, hero native-language priority, hero translated-note display, hero source metadata, hero bias metadata, hero save action, hero share action, hero performance budget |
| S461-S470 | Homepage UX | all-sides rail links, all-sides empty state, all-sides card images, all-sides source counts, all-sides bias pills, all-sides dark mode, all-sides mobile scroll, all-sides click target, all-sides tracking, all-sides tests |
| S471-S480 | Homepage UX | briefing list readability, briefing image fallback, briefing source metadata, briefing native sort, briefing time display, briefing empty state, briefing mobile layout, briefing dark mode, briefing performance, briefing tests |
| S481-S490 | Homepage UX | topic chip clarity, edition chip clarity, edition dropdown opacity, edition dropdown z-index, edition count accuracy, edition zero state, edition persistence, edition incognito behavior, edition dark mode, edition tests |
| S491-S500 | Homepage UX | search bar desktop, search bar mobile, subscribe CTA, login CTA, top pulse bar, admin bar compatibility, newsletter overlay compatibility, cookie banner compatibility, homepage visual baselines, homepage signoff |
| S501-S510 | Story UX | story hero readability, story title scale, story excerpt contrast, story source metadata, story NobuAI summary, story translated note, story timeline, story related stories, story share kit, story save action |
| S511-S520 | Story UX | article list grouping, article list sorting, article list logos, article list excerpts, article list upstream links, article list subscriber gate, article list full content, article list dark mode, article list mobile, article list tests |
| S521-S530 | Story UX | source drilldown clarity, source drilldown anchors, source drilldown excerpt safety, source drilldown unknown states, source drilldown mobile, source drilldown dark mode, source drilldown analytics, source drilldown tests, source drilldown docs, source drilldown signoff |
| S531-S540 | Story UX | full article extraction display, full article sanitization, full article word count, full article upstream attribution, full article subscriber CTA, full article logged-in path, full article extraction failure state, full article dark mode, full article mobile, full article tests |
| S541-S550 | Story UX | story SEO schema, story Open Graph, story canonical URL, story hreflang, story sitemap, story cache, story query budget, story visual baselines, story E2E path, story signoff |
| S551-S560 | Search and discovery | search input states, search results layout, source facet, bias facet, owner facet, date facet, language facet, country facet, category facet, saved search CTA |
| S561-S570 | Search and discovery | search native-language priority, search translation fallback, search empty state, search typo tolerance, search source logos, search result snippets, search dark mode, search mobile, search analytics, search tests |
| S571-S580 | Search and discovery | command palette shell, command palette index, command palette keyboard, command palette mobile fallback, command palette source search, command palette story search, command palette category search, command palette recent stories, command palette analytics, command palette tests |
| S581-S590 | Search and discovery | For You relevance score, read-history privacy, avoided topics, saved stories relevance, source diversity, bias diversity, language preference, edition preference, personalization reset, personalization tests |
| S591-S600 | Search and discovery | local page geolocation, local manual location, local Canada coverage, local France coverage, local UK coverage, local US coverage, local Africa coverage, local fallback, local privacy copy, discovery signoff |
| S601-S610 | Admin UX | admin shell audit, sidebar readability, topbar readability, dropdown opacity, dropdown z-index, menu hover light, menu hover dark, active state light, active state dark, admin layout tests |
| S611-S620 | Admin UX | cockpit metrics clarity, cockpit automation board, cockpit NobuAI board, cockpit ingest board, cockpit translation board, cockpit source board, cockpit quick actions, cockpit empty states, cockpit dark mode, cockpit tests |
| S621-S630 | Admin UX | provider vault readability, provider groups, provider health buttons, provider redaction display, provider save errors, provider live smoke copy, provider dark mode, provider mobile layout, provider tests, provider docs |
| S631-S640 | Admin UX | RSS feed list UX, RSS draft queue UX, RSS run action UX, RSS sick-feed UX, RSS guardrail badges, RSS dark mode, RSS responsive table, RSS tests, RSS docs, RSS signoff |
| S641-S650 | Admin UX | NewsAPI settings UX, NewsAPI category UX, NewsAPI quota UX, NewsAPI draft UX, NewsAPI guardrail UX, NewsAPI dark mode, NewsAPI responsive table, NewsAPI tests, NewsAPI docs, NewsAPI signoff |
| S651-S660 | Admin UX | source registry UX, source triage UX, source edit form UX, source logo UX, source bulk action UX, source dark mode, source responsive table, source tests, source docs, source signoff |
| S661-S670 | Admin UX | cluster list UX, cluster edit UX, cluster merge UX, cluster split UX, cluster NobuAI action UX, cluster dark mode, cluster responsive table, cluster tests, cluster docs, cluster signoff |
| S671-S680 | Admin UX | translation settings UX, translation queue UX, translation retry UX, translation stale UX, translation metrics UX, translation dark mode, translation responsive table, translation tests, translation docs, translation signoff |
| S681-S690 | Admin UX | ads admin UX, cookie admin UX, newsletter admin UX, subscriber admin UX, media admin compatibility, admin alert system, admin empty states, admin form system, admin visual baselines, admin signoff |
| S691-S700 | Admin UX | admin browser E2E desktop, admin browser E2E mobile, admin dark mode E2E, admin light mode E2E, admin keyboard E2E, admin dropdown E2E, admin provider E2E, admin ingest E2E, admin translation E2E, admin release gate |
| S701-S710 | Design system | token inventory, color token cleanup, typography token cleanup, spacing token cleanup, shadow token cleanup, border token cleanup, z-index token cleanup, opacity token cleanup, animation token cleanup, reduced-motion token |
| S711-S720 | Design system | public card classes, public pill classes, public dropdown classes, public modal classes, public chart classes, public article classes, public ad classes, public auth classes, public form classes, public table classes |
| S721-S730 | Design system | admin card classes, admin pill classes, admin dropdown classes, admin modal classes, admin metric classes, admin action classes, admin alert classes, admin form classes, admin table classes, admin responsive classes |
| S731-S740 | Design system | light theme matrix, dark theme matrix, auto theme matrix, contrast matrix, hover matrix, focus matrix, active matrix, disabled matrix, loading matrix, error matrix |
| S741-S750 | Design system | visual baseline home, visual baseline story, visual baseline sources, visual baseline search, visual baseline admin, visual baseline auth, visual baseline mobile, visual baseline dark, visual baseline ads, design signoff |
| S751-S760 | Accessibility | skip links, landmark structure, heading order, nav labels, icon labels, form labels, error descriptions, chart descriptions, source logo alt text, ad labels |
| S761-S770 | Accessibility | keyboard nav home, keyboard nav story, keyboard nav search, keyboard nav sources, keyboard nav admin, keyboard nav auth, keyboard nav overlays, keyboard nav charts, keyboard nav ads, keyboard nav mobile |
| S771-S780 | Accessibility | focus restoration, modal trap, dropdown escape, reduced motion, color-only bias replacement, screen reader translated note, screen reader NobuAI note, screen reader source details, high-contrast mode, accessibility docs |
| S781-S790 | Accessibility | WCAG light contrast, WCAG dark contrast, WCAG mobile contrast, touch target audit, zoom 200 audit, text spacing audit, aria tab audit, aria menu audit, aria live audit, accessibility tests |
| S791-S800 | Accessibility | axe home, axe story, axe search, axe sources, axe auth, axe admin, manual keyboard pass, screen reader pass, accessibility evidence report, accessibility signoff |
| S801-S810 | Performance | homepage query budget, story query budget, source query budget, search query budget, admin query budget, cache hit audit, N+1 audit, eager-load audit, index audit, slow query report |
| S811-S820 | Performance | asset build audit, CSS size budget, JS size budget, font preload audit, image dimension audit, lazy-load audit, hero eager-load policy, logo cache budget, ad CLS budget, performance docs |
| S821-S830 | Performance | homepage TTFB budget, story TTFB budget, search TTFB budget, source TTFB budget, admin TTFB budget, queue latency budget, scheduler latency budget, provider latency budget, extraction latency budget, translation latency budget |
| S831-S840 | Performance | public cache headers, cookie-aware vary, sitemap cache, source logo cache, chart render budget, command palette index cache, search result cache, edition count cache, pulse bar cache, cache invalidation tests |
| S841-S850 | Performance | Lighthouse home, Lighthouse story, Lighthouse sources, Lighthouse search, Lighthouse auth, Lighthouse mobile, Lighthouse dark, k6 smoke, performance evidence report, performance signoff |
| S851-S860 | Ads and revenue | ad provider inventory, Echo ads capability audit, AdSense evaluation, Ad Manager evaluation, header bidding evaluation, privacy impact review, highest-yield shortlist, fallback ad policy, house ad policy, no-provider empty state |
| S861-S870 | Ads and revenue | home top ad slot, home mid ad slot, story inline ad slot, story sidebar ad slot, search ad slot, source ad slot, newsletter ad slot, mobile sticky policy, subscriber suppression, ad label styling |
| S871-S880 | Ads and revenue | consent gating, regional consent rules, frequency capping, lazy ad loading, CLS reserved space, dark mode ad frames, blocked-ad fallback, ad error logging, ad revenue dashboard, ad QA fixtures |
| S881-S890 | Ads and revenue | subscription value proposition, subscriber ad-free flag, subscriber full-content gate, subscriber account page, subscriber billing placeholder, subscriber entitlement tests, newsletter monetization, sponsorship slots, campaign tagging, revenue docs |
| S891-S900 | Ads and revenue | revenue analytics, CPM dashboard, fill-rate dashboard, consent-rate dashboard, subscriber conversion dashboard, ad performance budget, ad security review, ad accessibility review, ad visual baselines, revenue signoff |
| S901-S910 | Security and privacy | admin auth audit, member auth audit, CSRF audit, route authorization audit, provider key encryption audit, API key redaction audit, log redaction audit, debugbar environment audit, cookie encryption audit, session config audit |
| S911-S920 | Security and privacy | image proxy allowlist, SSRF prevention, CSV export auth, RSS URL validation, external link safety, HTML sanitization, full-content sanitization, translation input sanitization, NobuAI prompt safety, admin action confirmation |
| S921-S930 | Security and privacy | consent banner audit, tracking opt-out, privacy policy links, data retention policy, analytics minimization, IP hash policy, saved-search privacy, vault privacy, local geolocation privacy, newsletter privacy |
| S931-S940 | Security and privacy | security tests public, security tests admin, security tests provider vault, security tests exports, security tests proxy, security tests cookies, security tests auth, security tests content, vulnerability scan, security docs |
| S941-S950 | Security and privacy | threat model, secret rotation runbook, incident response runbook, access review, backup encryption review, deploy key review, dependency audit, license audit, legal checklist, security signoff |
| S951-S960 | Data and storage | SQLite production decision, production DB plan, migration dry-run, migration rollback, indexes audit, foreign key audit, nullable audit, data type audit, seed data audit, table growth forecast |
| S961-S970 | Data and storage | backup command, restore command, backup schedule, backup verification, restore drill, media backup, settings backup, source metadata backup, translation backup, NobuAI insight backup |
| S971-S980 | Data and storage | article retention policy, draft retention policy, log retention policy, event retention policy, translation retention policy, provider diagnostic retention, analytics retention, privacy purge command, stale media cleanup, data docs |
| S981-S990 | Data and storage | data integrity tests, migration tests, restore tests, dedupe data tests, cluster data tests, translation data tests, insight data tests, source metadata tests, backup evidence report, data signoff |
| S991-S1000 | Production launch | CI green, E2E green, visual diff green, performance green, security green, scheduler smoke green, provider smoke green, rollback drill green, release evidence complete, production launch signoff |

## First Execution Wave

Start with these before opening feature work:

1. S001-S050: current-state review and visual parity inventory.
2. S101-S200: autonomous ingestion and publishing reliability.
3. S251-S350: NobuAI and translation hardening.
4. S401-S450: GroundNews-style breakdown quality.
5. S601-S700: admin UX closeout.
6. S751-S850: accessibility and performance gates.
7. S901-S1000: security, data, and launch readiness.

## Pre-Production Non-Negotiables

- The site cannot require manual dashboard work to publish normal daily news.
- Every public article path must resolve to a real story/article page, not a generic news/blog homepage.
- Editions must be readable in light and dark modes across France, UK, US, Canada, Africa, and International.
- GroundNews-style analysis must expose bias, factuality, ownership, source count, confidence, unknown states, and methodology.
- Subscribers/logged-in users must have a clear full-article reading path with safe extraction and upstream attribution.
- Admin provider key pages must be readable, solid, redacted, and testable in both themes.
- The production move is blocked until the executable release gate and rollback drill both pass.
