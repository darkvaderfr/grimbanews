# GrimbaNews — Master Sprint Ledger

**Product:** GrimbaNews
**Platform:** Echo News CMS v3.1.1 on Laravel 12
**Repo:** `darkvaderfr/grimbanews`
**Local server:** `http://127.0.0.1:8002`
**Last updated:** 2026-04-28

This is the active implementation ledger. The original Mythos 500-sprint output referenced by the early handoff was not present on disk, so this file now tracks the real shipped state from git history and defines the next sprint queue.

## Current Status

GrimbaNews is past the baseline phase. The product now has a GroundNews-style reader experience, RSS/NewsAPI ingest, source/bias intelligence, story clusters, NobuAI translation, NobuAI story insights, a custom admin cockpit, and test coverage for the critical public/admin flows.

Latest pushed commits:

- `dd2b650` Publish guardrail drafts into review categories
- `d0c8e1f` Add Canada coverage and solid edition menu
- `58428ca` Improve hero image text contrast
- `55785e7` Add owner and date search facets

Latest verification:

- `php artisan test` passed with `66` tests and `1201` assertions after the D5 search-facet sprint.
- Focused D2 verification passed: `ForYouAvoidedTopicsTest`.
- Local scheduler was started with `CACHE_STORE=array LOG_CHANNEL=stderr php -d max_execution_time=0 artisan schedule:work`.
- Local Canada coverage now has 10 published CA-source articles from Global News, all with extracted full content.

## Completed Sprint Bands

### S1-S10 — Baseline And First GroundNews Layer

- Echo CMS extracted, dependencies installed, SQLite local dev verified.
- French locale configured.
- Admin credentials reset.
- Bias columns, first source/story cluster tables, comparison view, blindspot feed, source seed, and initial Grimba CSS shipped.

### S84-S108 — Ingest Hardening And NobuAI Seed

- Image backfill, RSS ingest hardening, dedupe, personalization, newsletter popup polish, and NobuAI branding rules.
- `CLAUDE.md` established the project rule: reader surfaces say only `NobuAI`; provider names remain admin-only.

### S111-S165 — Source Intelligence, Translation, And GroundNews Fidelity

- Source profile pages, ownership data, NewsAPI ingest, high-volume source classification, canonical URL dedupe, region picker/filtering, story pages, media-ownership map, translation fallback, cross-language clustering, source logos, full article fetch, and French news taxonomy.

### S166-S184 — Reader Account, Vault, Story Polish

- Member auth/dashboard restyle, local page, footer refresh, GroundNews-style hero, dark-mode coverage, save-for-later vault, CSV export, story timeline, one-sided coverage callouts, and bias-filtered vault.

### S185-S250 — Maturity, Accessibility, Admin, And Tests

- Story/vault maturity, orphan layout, reading progress, NobuAI health/confidence polish, public cache, SEO, accessibility skip links/focus states, contrast tokens, admin cockpit, admin settings/dark mode fixes, extractive synthesis tests, cluster page tests, admin UI kit, edit forms, source triage, coverage map, NobuAI insight generation, NobuTranslation integration, most-read-by-bias, fine-grained source bias scores, newsletter bias signal, bidirectional translation queues, static UI localization, and admin dropdown/theme chrome hardening.
- S219 added a clamped cockpit action for small-batch NobuAI insight generation.
- S220 hardened public NobuAI insight rendering so reader pages dedupe lines and scrub provider names from saved insight copy.
- S221 moved remaining core public chrome, metadata, blindspot, comparison, and error-page copy behind saved EN/FR catalogs, with regression tests for catalog coverage.
- S222 raised admin dropdown/header stacking above page actions, made dropdown panels effectively solid in both themes, fixed dark-mode switch sync against stale local storage, and reorganized the NobuAI provider vault into readable provider groups.
- S223 added a cockpit operations board for RSS/NewsAPI 24h ingest, sick feeds, draft pressure, duplicate groups, pending translations, and pending NobuAI insights with direct admin links.
- S224 added cockpit runbook actions for health checks, NobuAI health, one-feed RSS polling, NewsAPI fetch, and bounded FR/EN translation queue runs.
- S225 expanded public story insight QA with GroundNews-style labels, provider-scrubbed NobuAI copy, generation notes, and stable multi-post story fixtures.
- S226 added a story source drilldown that maps each bias/source row to its supporting excerpt and exact article anchor without exposing provider names.
- S227 added NobuAI insight freshness signals for stale reader summaries and cockpit stale/missing insight counts.
- S228 added admin source drilldown diagnostics on story cluster edit pages, including post edit links plus missing-source, unknown-bias, and low-credibility flags.
- S229 added stale-only NobuAI insight refreshes, a cockpit refresh action, and stale warnings on story cluster edit pages.
- S230 added sanitized NobuAI provider failure diagnostics in the cockpit and provider vault, with tests for admin-only visibility and secret redaction.
- S231 added RSS draft publish guardrails that flag missing source, unknown bias, missing translation, and short excerpts, while blocking weak drafts from bulk/single publish.
- S232 added NewsAPI draft readiness guardrails and a guarded publish action in the NewsAPI admin page.
- S233 extracted shared ingest guardrails so RSS and NewsAPI publish paths use one tested readiness policy.
- S234 added ingest guardrail metrics in cockpit plus per-queue RSS and NewsAPI blocker summaries.
- S235 linked guardrail badges and cockpit blocker counts to source triage, translation settings, or the relevant article editor for faster remediation.
- S236 finished the shared futuristic admin shell audit: RSS drafts, RSS feeds, and subscribers now use the Grimba hero shell, shared metric cards, dark/light tokens, and enforced Blade-shell tests.
- S237 added a shared admin action system for cockpit, RSS, NewsAPI, subscribers, source, and cluster pages with distinct primary, warning, and destructive button states.
- S238 replaced plain admin empty rows with shared Grimba empty-state cards across RSS drafts, NewsAPI drafts, subscribers, sources, and clusters, each with a direct next action.
- S239 added responsive admin table cards and full-width mobile action rows so RSS, NewsAPI, subscriber, source, and cluster queues remain readable on narrow screens.
- S240 added shared admin form sections/actions across source, RSS feed, story cluster, and NobuAI provider settings forms, with light/dark contrast locked by tests.
- S241 added solid, high-contrast Grimba alert and diagnostic styling across custom admin pages, including warning rows and dark-mode alert surfaces.
- S242 added shared wayfinder navigation to source, RSS feed, story cluster, and NobuAI provider settings pages, with light/dark hierarchy locked by tests.
- S243 tightened inline admin actions in dense tables and cards, including larger hit areas and explicit destructive labels instead of tiny symbol-only controls.
- S244 completed the backend cinematic SOK checklist, added wayfinding to remaining list/control pages, finished responsive coverage/triage tables, and brought cookie settings into the shared form system.
- S245 recorded a local production-readiness smoke without deploying: app health, NobuAI health, 52 admin routes, and the full test suite are green.
- S246 documented the later production deployment cache order, post-deploy smoke, and rollback path for the admin redesign without deploying production.
- S247 documented the required admin visual-regression screenshot routes and pass criteria so future changes preserve the redesigned backend contract.
- S248 extracted an isolated focused admin route smoke test covering cockpit, provider vault, RSS, NewsAPI, sources, triage, clusters, coverage map, subscribers, and cookies.
- S249 normalized remaining custom admin hero/page copy into French editorial language while preserving admin-only provider naming where appropriate.
- S250 added a backend closeout index linking the cinematic SOK, production-readiness smoke, deployment checklist, visual-regression routes, and master sprint ledger.
- S251 selected the next highest-impact post-redesign product sprint: Discovery D5 search facets.
- S252 added Canada coverage defaults and a Global News Canada RSS feed, seeded 10 local Canada articles, and made the public edition dropdown fully solid.
- S253 hardened the homepage featured-story image overlay so title, excerpt, and source metadata remain readable on busy photos.
- S254 added `/search` facets for source, bias, owner, and date range, with regression coverage for owner/date filtering.
- S255 added `/pour-vous` avoided-topic personalization for readers with more than 10 local read-history items, linking recent unread categories to `/blog?categorie=X`.

## Active Systems

### Public Reader

- Homepage with GroundNews-style story rails, most-read-by-bias, topic chips, region selector, translation note, vault controls, PWA shell, and localized static UI.
- Story pages with multi-source comparison, bias distribution, timeline, extractive synthesis, coverage-gap callout, source logos, and NobuAI chips.
- Source pages, search, ownership map, local page, vault, member auth/dashboard, and translated EN/FR UI.

### Ingest And Intelligence

- RSS polling: `grimba:poll-feeds`
- NewsAPI fetching: `grimba:fetch-newsapi`
- Trusted-source auto-publishing: `grimba:publish-trusted`
- Dedupe: `grimba:dedupe-posts`
- Full article extraction: `grimba:fetch-full-articles`
- Category backfill: `grimba:classify-categories`
- Translation queue: `grimba:translate-pending`
- NobuAI story insights: `grimba:nobuai-summaries`
- System health: `grimba:health`, `grimba:nobuai-health`

### Admin Backend

- GrimbaNews cockpit: `/admin/grimba/cockpit`
- Source registry + source triage
- RSS feed registry + RSS draft queue
- NewsAPI settings
- Story clusters + coverage map + per-cluster NobuAI insight action
- Translation/provider vault for OpenAI, OpenRouter, Anthropic, xAI, Google, Mistral, Perplexity, Groq, DeepL, Libre
- Newsletter/subscriber export
- Cookie banner settings

## Next Sprint Queue

### S251-S255 — Discovery And Coverage Continuation

Goal: Move from backend-redesign closeout into product-discovery work without breaking ingest or reader polish.

Acceptance:

- Canada has a real source/feed path and visible published articles.
- Edition dropdowns stay solid and readable.
- Featured-story hero details are readable over busy images.
- `/search?q=...` supports source, bias, owner, `from_date`, and `to_date` facets.
- `/pour-vous` surfaces personal blind-spot categories once local read history has enough signal.
- Keep backend closeout artifacts intact.
- Keep tests green and do not deploy production.

Status: S251-S255 shipped locally; S255 pending final full-suite verification and push in the current working session.

### S256 — Next Feature Sprint

Goal: Continue Discovery & Navigation after D5.

Recommended next options:

- D8 site-wide command palette, because it improves speed of navigation across stories, sources, and categories.
- D7 saved-search alerts, because search facets are now expressive enough to persist for members.
- C8 vault analytics, because it will show which saved stories actually matter to readers without per-user tracking.

## Operating Rules

- Commit and push every completed sprint to `origin/main`.
- Do not deploy to production until explicitly requested.
- Keep reader-facing AI/provider copy branded as `NobuAI`.
- Provider names are allowed only behind the admin guard.
- Keep `php artisan test` green before pushing.
