# GrimbaNews — Master Sprint Ledger

**Product:** GrimbaNews
**Platform:** Echo News CMS v3.1.1 on Laravel 12
**Repo:** `darkvaderfr/grimbanews`
**Local server:** `http://127.0.0.1:8002`
**Last updated:** 2026-04-27

This is the active implementation ledger. The original Mythos 500-sprint output referenced by the early handoff was not present on disk, so this file now tracks the real shipped state from git history and defines the next sprint queue.

## Current Status

GrimbaNews is past the baseline phase. The product now has a GroundNews-style reader experience, RSS/NewsAPI ingest, source/bias intelligence, story clusters, NobuAI translation, NobuAI story insights, a custom admin cockpit, and test coverage for the critical public/admin flows.

Latest pushed commits:

- `bf3bc60` Finish futuristic admin shell audit
- `e9f4a2e` Add guardrail quick-fix links
- `efd8ea2` Add ingest guardrail metrics
- `ef2a4cd` Extract shared ingest guardrails
- `5ac0fbf` Add NewsAPI draft publish guardrails

Latest verification:

- `php artisan test` passed with `45` tests and `633` assertions.
- `php artisan grimba:nobuai-health` reports OpenAI configured, NobuTranslation/OpenAI/GoogleTx translation chain, and story insight readiness.

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

### S185-S237 — Maturity, Accessibility, Admin, And Tests

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

### S238 — Admin Empty-State Polish

Goal: Replace plain empty rows/messages with useful futuristic empty states that tell editors what to do next.

Acceptance:

- RSS drafts, NewsAPI drafts, subscribers, sources, and clusters use shared empty-state cards.
- Empty states include a next action or diagnostic link where practical.
- Tests cover shared empty-state CSS markers and representative admin render.

## Operating Rules

- Commit and push every completed sprint to `origin/main`.
- Do not deploy to production until explicitly requested.
- Keep reader-facing AI/provider copy branded as `NobuAI`.
- Provider names are allowed only behind the admin guard.
- Keep `php artisan test` green before pushing.
