# GrimbaNews — Advertiser Platform, Reader-Culture Surface, and Freshness Pipeline Sprint Plan

> **Owner:** Steve Jobs (CPO/design), Rajesh Kumar (backend), Larry Ellison (schema),
> Nina Patel (frontend lead), Alex Morgan (UI/UX), Liam Smith (PM),
> Sara Kim (QA), Sara Chen (CISO — first authed surface), Maya (compliance),
> Zen/Echo/Mnemo (audit panel).
> **Mandate (Vader 2026-05-17):** Three strategic features delivered as one
> coherent sprint stream — (1) rebuild `/advertise` from legacy Echo chrome
> into a real advertiser platform; (2) extend reader personalisation beyond
> the 4-region scope to a cookie-only culture/background surface;
> (3) split the urgency rail into a two-stream Latest + Breaking pipeline
> with consistent marquee physics and per-edition freshness SLOs.
>
> **Scope:** Spans the Echo theme fork at `platform/themes/echo/`, the
> `App\Support\Grimba*` allocator/feed/ad layer, the `posts` schema
> (read-only — migrations only when Vader explicitly approves), and a new
> advertiser subsystem (auth + dashboard + telemetry + billing).
>
> **Companion plans:**
> - `docs/GRIMBANEWS_LANGUAGE_SPRINT_PLAN.md` — bilingual pipeline (governs Phase C string surfaces).
> - `docs/GRIMBANEWS_AFRICA_INTERNATIONAL_EDITORIAL_PIVOT.md` — region scope.
> - `docs/GRIMBANEWS_INGEST_TO_PUBLIC_FRESHNESS_2026_05_11.md` — daily-publish floor.

---

## Architectural grounding (what we're building on)

- **Editorial region scope.** `App\Scopes\GrimbaRegionScope` (global on `Post`) filters every reader query by the `grimba_region` cookie → `posts.editorial_region` column (one of `africa`, `europe`, `americas`, `international`). `App\Ground\Regions::migrate()` is the canonical region normaliser.
- **Home allocator.** `App\Support\GrimbaHomeFeed::build()` is the single dedupe allocator across hero / briefing / all-sides / most-read / top-news / section blocks / latest. Cached 180s with a Cache::lock stampede guard.
- **Dossier reinvention.** `App\Support\GrimbaDossierVoices` shapes story-cluster pages.
- **Ad system.** `App\Support\GrimbaAds::resolve(location, configuredHtml, label)` returns one of `hidden | configured (botble) | network (adsense) | direct (house)`. 12 placements registered. House `direct` mode links to `/advertise?slot={placement}`.
- **Advertise page.** `platform/themes/echo/views/advertise.blade.php` extends `grimba-chrome` layout — currently a styled placeholder with a `mailto:` CTA into `grimba_ads.sales_email`. No routes, no controller, no auth, no dashboard.
- **Cookie-only reader state.** `grimba_region`, `grimba_lang`, vault id, saved searches all live in cookies. No reader accounts (members module is staff-only).
- **Breaking ticker.** `platform/themes/echo/partials/home/urgency-banner.blade.php` already enforces the 18h window + multi-word breaking phrase set + 45s region+lang cache key, with a `__breakingMode = 'real' | 'fallback'` flag. The marquee CSS animation is fixed-duration regardless of track length — this is the root of the "Africa ticker feels faster" complaint.
- **NobuTranslate driver.** `App\Services\GrimbaTranslator` is mid-rollout (see language plan Phase B).

This plan extends those primitives. It does NOT re-architect them.

---

## Phase A — Advertiser platform foundation (Sprints 1–30)

Rebuild the public `/advertise` surface from Echo placeholder to a proper
advertiser product, plus the auth + dashboard skeleton. This is the FIRST
authed surface in GrimbaNews — readers stay cookie-only forever.

| Sprint | Title | Acceptance | Owner |
|--------|-------|------------|-------|
| A-01 | Advertiser product brief locked | Steve writes the one-pager: pitch page → signup → dashboard → campaign create → telemetry → invoice. Pinned in `docs/`. No code. | Steve |
| A-02 | `/advertise` rebrand audit | Inventory every visible string + style on current `advertise.blade.php`. Identify all legacy Echo chrome. Capture screenshots FR + EN. | Alex |
| A-03 | New pitch-page layout (Steve cinematic) | Replace `advertise.blade.php` body with Steve-language hero + value props + inventory grid + FAQ + CTA — `grimba-chrome` layout retained, content reinvented. Bilingual. | Nina, Steve |
| A-04 | Inventory grid driven by `GrimbaAds::SLOTS` | The 12 placements render dynamically from the registry, not hand-rolled. CTA on each tile = "Sponsor this slot". | Rajesh, Nina |
| A-05 | Pitch-page CTA wired to /advertise/start | New route `GET /advertise/start?slot=…` lands the visitor on signup with slot pre-selected. | Rajesh |
| A-06 | Advertiser auth — DB design (no migration) | Larry drafts the schema for `advertisers`, `advertiser_users`, `advertiser_sessions`, `campaigns`, `creatives`, `ad_events`, `ad_invoices`. Schema written to `docs/`. **Migration awaits Vader approval.** | Larry |
| A-07 | Migration approval gate | Vader reviews A-06 schema and either approves or amends. NO migration runs until approval. | Vader |
| A-08 | Migration: `advertisers` + `advertiser_users` | (Only after A-07.) Tenancy table + auth users. Email + bcrypt password + email_verified_at + role enum (owner / staff). | Larry, Rajesh |
| A-09 | Migration: `campaigns` + `creatives` | `campaign_id`, `advertiser_id`, `slot`, `status enum`, `starts_at`, `ends_at`, `creative_id FK`, `geo_targeting JSON`, `edition_targeting JSON`, `daily_budget_cents`, `bid_cpm_cents`. | Larry |
| A-10 | Migration: `grimba_ad_events` | `event_id`, `slot`, `campaign_id`, `creative_id`, `event_type enum(impression\|click)`, `viewer_region`, `viewer_lang`, `user_agent_hash`, `ip_hash` (Maya/Sara Chen: NEVER plaintext IP), `created_at`. Indexed on `(campaign_id, created_at)`. | Larry, Sara Chen |
| A-11 | Advertiser registration flow | `/advertiser/register` — company name, contact email, billing country. Email-verification gated. No login until verified. | Rajesh |
| A-12 | Advertiser login flow | `/advertiser/login` — bcrypt + rate-limited (5 tries / 15min / IP-hash). Session cookie `grimba_adv_session` SameSite=Lax, HttpOnly, Secure. Distinct from reader cookies. | Rajesh, Sara Chen |
| A-13 | Advertiser password reset | Token-based, single-use, 60min TTL. Email via existing Botble mail pipeline. | Rajesh |
| A-14 | Advertiser auth — 2FA optional | TOTP enrollment in account settings. Sara Chen marks "required" gate for accounts spending > $X/mo (deferred to Phase B). | Sara Chen, Rajesh |
| A-15 | Dashboard shell route + nav | `/advertiser/dashboard` behind auth middleware. Left nav: Overview / Campaigns / Creatives / Billing / Account. Empty states only. | Nina |
| A-16 | Dashboard branding pass | NobuAI/GrimbaNews wordmark; bias-tier colors (`#3b82f6` / `#a8a8a8` / `#e84c3d`) reserved for editorial only — dashboard uses the chrome ink palette. | Steve, Alex |
| A-17 | Slot picker UI | Form: pick placement from `GrimbaAds::SLOTS`, show preview thumbnail + average traffic per slot (placeholder until B-06). | Alex |
| A-18 | Creative upload pipeline | Image + HTML5 banner upload. Validated dimensions per slot. Stored in `storage/app/advertiser/creatives/{advertiser_id}/{creative_id}`. Antivirus scan stub (clamav-shell exec, gated by env). | Rajesh, Sara Chen |
| A-19 | Creative MIME + dimension validator | Allowlist PNG/JPG/WebP + safe-HTML banner. Reject SVG (XSS vector per Sara Chen). | Sara Chen, Rajesh |
| A-20 | Campaign draft create | POST `/advertiser/campaigns` writes `status=draft`. Validation: slot exists, dates sane, at least one creative attached. | Rajesh |
| A-21 | Campaign edit (draft only) | Drafts editable. Once submitted, locked except by admin. | Rajesh |
| A-22 | Campaign status state machine | `draft → submitted → under_review → approved → live → paused → ended → archived`. State transitions logged. | Rajesh, Larry |
| A-23 | Admin review queue (Botble admin) | New admin route `/admin/advertiser/review` listing submitted campaigns. Editor can approve / reject / request changes with note. | Liam, Rajesh |
| A-24 | Approval triggers eligibility | Approved campaign becomes eligible for `GrimbaAds::resolve()` direct-card mode for its slot. | Rajesh |
| A-25 | `GrimbaAds::resolve()` direct-card integration | When mode = `direct` and an approved live campaign exists for the slot+region+lang, render that creative instead of the house "advertise here" card. Falls through to house when no campaign live. | Rajesh |
| A-26 | Frequency/rotation rule v1 | If multiple campaigns are live for the same slot, weighted round-robin by bid_cpm_cents. Capped at 1 impression per viewer per slot per page-load. | Rajesh |
| A-27 | NobuAI brand sanitiser pass on advertiser UI | Grep dashboard for "OpenAI" / "Claude" / "Anthropic" / any provider name. CI guard added. | Sara Kim, Maya |
| A-28 | Reader-side: zero leakage of advertiser surfaces | Confirm no reader-facing nav, footer, or sitemap exposes `/advertiser/*` to anonymous crawlers (robots.txt block + nofollow). | Sara Chen |
| A-29 | Phase A audit panel | Zen reviews advertiser auth + creative upload code paths. Echo verifies "is this actually a usable advertiser product?" Mnemo cross-checks against Phase B plan. | Zen, Echo, Mnemo |
| A-30 | Phase A close + go-live gate | Steve signs off pitch page; Sara Chen signs off auth; Larry signs off schema. Push `darkvaderfr/grimbanews:main` BEFORE any deploy. | Steve, Sara Chen, Larry, Vader |

---

## Phase B — Advertiser features (Sprints 31–60)

Now the platform actually serves campaigns. Telemetry, scheduling, billing,
sales handoff, brand sanitiser hardening.

| Sprint | Title | Acceptance | Owner |
|--------|-------|------------|-------|
| B-01 | Campaign scheduling — start/end | Date+time UI in dashboard. Server enforces window in `GrimbaAds::resolve()`. | Alex, Rajesh |
| B-02 | Campaign scheduling — day-of-week | Optional weekday mask. Pacific-to-publisher TZ explicit. | Rajesh |
| B-03 | Campaign scheduling — hour-of-day | Optional time-of-day windows (e.g. workday-only). | Rajesh |
| B-04 | Geo targeting | Country-level allow/deny list. Inferred from CF-IPCountry / Botble IP-lookup. No raw IP stored. | Rajesh, Sara Chen |
| B-05 | Edition targeting | Pick from `africa`/`europe`/`americas`/`international`. Plumbed through `GrimbaAds::resolve()`. | Rajesh |
| B-06 | Impression event capture | When `GrimbaAds::resolve()` returns a direct-card with a live campaign, fire a beacon → POST `/ad-events/impression` → `grimba_ad_events` insert. Queue-deferred via Botble queue worker, not request-blocking. | Rajesh |
| B-07 | Click event capture | Wrapper redirect at `/r/{campaign_id}/{creative_id}?to={dest}` logs click then 302s. Dest URL signed to prevent open-redirect abuse. | Rajesh, Sara Chen |
| B-08 | Bot-traffic filter | UA-hash blocklist + headless detection + bot networks (per `BotDetector` or new `App\Support\GrimbaAdBots`). Bot events flagged but not counted in billing. | Sara Kim, Rajesh |
| B-09 | Daily aggregation cron | New scheduled command `grimba:ads-aggregate-daily` rolls `grimba_ad_events` into a `grimba_ad_daily_rollups` materialisation per campaign per day. | Rajesh, Larry |
| B-10 | Self-serve creative editor v1 | Inline editor for HTML5 banners — headline + subhead + CTA + image — generates a safe sandboxed creative without raw HTML. | Alex, Nina |
| B-11 | Creative preview | Live preview in the slot's actual visual context. Mobile + desktop. | Alex |
| B-12 | Real-time impression telemetry on dashboard | Dashboard widget polls `/advertiser/api/telemetry?campaign={id}` every 60s, charts impressions + clicks + CTR. | Nina |
| B-13 | Telemetry exposure boundary | Sara Chen audit: advertiser can ONLY see their own campaign events. RBAC test added. | Sara Chen, Sara Kim |
| B-14 | Stripe customer + payment-method onboarding | New advertiser → Stripe Customer object created on email-verification. Add card flow in Billing tab. | Rajesh |
| B-15 | Stripe Checkout for prepaid balance | "Add $500 credit" → Stripe Checkout → webhook → `advertiser_balance` ledger update. | Rajesh |
| B-16 | Stripe webhook hardening | Signature verification, idempotency key, retry-safe ledger writes. | Sara Chen, Rajesh |
| B-17 | Billing engine — CPM accrual | Daily cron debits balance by `impressions_billable * bid_cpm_cents / 1000`. Persisted in ledger. | Rajesh, Larry |
| B-18 | Daily cost cap | Campaign auto-pauses when `daily_budget_cents` met. State machine moves to `paused`. | Rajesh |
| B-19 | Balance floor | When balance drops below threshold, campaign pauses + email advertiser. | Rajesh |
| B-20 | Invoice generation | Monthly PDF invoice via existing Botble PDF stack; downloadable from Billing tab. | Liam, Rajesh |
| B-21 | Invoice email | Auto-send on 1st of month. Bilingual (FR/EN per advertiser preference). | Rajesh |
| B-22 | Ad approval queue UI polish | Admin reviewer side: bias / banned-claims checklist; one-click approve/reject. | Alex, Liam |
| B-23 | Banned-content policy doc | Maya drafts the editorial / advertiser policy: gambling, weapons, political, adult, deceptive — all banned or restricted. Linked from approval queue. | Maya |
| B-24 | Banned-content pre-flight scanner | On creative submit, NobuAI text classification flags policy-violating copy BEFORE human review. Branded "NobuAI compliance check" — never names the LLM provider. | Maya, Rajesh |
| B-25 | Sales-contact escalation flow | "Talk to sales" button in dashboard → form → Slack/email to sales@grimbanews. Logged in `advertiser_support_threads`. | Liam |
| B-26 | Sales agent reply UI in admin | Internal admin can reply; thread visible to advertiser in dashboard. | Liam, Alex |
| B-27 | NobuAI brand discipline sweep (Phase B sanitiser) | Final grep across advertiser surfaces, emails, invoices, dashboards for any leaked provider name. CI guard `tests/Feature/AdvertiserNobuBrandTest.php` blocks regressions. | Sara Kim, Maya |
| B-28 | Advertiser audit log | All campaign state transitions, creative uploads, payment events written to `advertiser_audit_log`. 90-day retention. | Sara Chen |
| B-29 | Phase B audit panel | Zen reviews billing / Stripe / event-capture code. Echo verifies advertiser can run a full campaign end-to-end on a clean test account. Mnemo cross-checks bias-color discipline (advertiser dashboard never uses #3b82f6 / #e84c3d as accents — those stay editorial-only). | Zen, Echo, Mnemo |
| B-30 | Phase B close + production launch gate | Stripe live keys swapped in (per ops checklist). darkvaderfr push first. Vader signoff. | Vader |

---

## Phase C — Reader background / region / culture surface (Sprints 61–90)

Extend personalisation beyond the 4 regions to cultural / diaspora / interest
axes. Cookie-only — no reader accounts ever.

| Sprint | Title | Acceptance | Owner |
|--------|-------|------------|-------|
| C-01 | Cultural taxonomy doc | Steve + Liam draft the cultural-tag set: e.g. `diaspora-maghreb-eu`, `african-american-history`, `latinx-us-politics`, `francophone-africa`, `anglophone-africa`, `caribbean-diaspora`, `mena-business`. Pinned in `docs/`. ~25 tags v1. | Steve, Liam |
| C-02 | Tag-to-source mapping | For each cultural tag, list the editorial categories + source domains + keyword signals that compose it. Lives in `config/grimba_culture_tags.php`. NO migration. | Liam, Rajesh |
| C-03 | `GrimbaCultureTags` support class | Static methods: `tags()`, `tagsForPost(Post)`, `matchScore(Post, tag)`. Pure PHP — derives from post content + source + region. | Rajesh |
| C-04 | Tag inference at ingest time | When a post is published, derive its tag set and store in `posts.culture_tags JSON`. **Requires migration — gated on Vader approval.** | Larry, Vader |
| C-05 | Migration: `posts.culture_tags JSON` | After Vader approval. Index on JSON column where supported; fallback to materialised lookup table otherwise. | Larry |
| C-06 | Backfill command | `php artisan grimba:backfill-culture-tags` derives tags for every existing post. Idempotent. | Rajesh |
| C-07 | Cookie schema for follow set | Cookie `grimba_culture` is a JSON-encoded list of tag IDs (or `null` = no follows). 365d TTL. Cookie-only — never written to a DB row keyed to a user. | Sara Chen, Nina |
| C-08 | "Follow this culture" UI v1 | Steve-styled picker modal: list of cultural tags, multi-select toggle, save. Triggerable from header + footer. | Steve, Alex |
| C-09 | Picker copy bilingual | All tag names + descriptions translated FR/EN — extends `lang/en.json` + `fr.json`. | Nina |
| C-10 | Follow-state header chip | When follows set, header shows "Your culture: 3 follows" pill. Click → reopens picker. | Alex |
| C-11 | `GrimbaHomeFeed::followsRail()` | New allocator slice: pulls up to 8 recent posts matching ANY of the follow set, deduped against other home sections. Soft per-source cap. Inserted between hero and section blocks. | Rajesh |
| C-12 | Home rail render partial | `partials/home/follows-rail.blade.php` — Steve-cinematic, eyebrow "From your cultures", card style consistent with All-Sides rail. Bilingual. | Nina |
| C-13 | Empty-state for no-follows readers | When `grimba_culture` cookie absent, the rail becomes "Discover this culture" — surfaces 3 trending tags this week with a "Follow" CTA. | Alex |
| C-14 | Tag landing page `/culture/{tag}` | Public deep-link page per tag. Bilingual. Posts sorted by `published_at` desc. Standard chrome. | Nina, Rajesh |
| C-15 | Tag SEO | OG title + description + canonical per tag landing. JSON-LD CollectionPage. | Liam, Nina |
| C-16 | Tag landing in sitemap | Per-locale entries. | Rajesh |
| C-17 | Reading-history cookie | `grimba_read` JSON array of last 50 post IDs + tags read + region read. Cookie-only. TTL 90d. | Nina, Sara Chen |
| C-18 | Reading-history-driven re-rank | Posts whose tags overlap with the reader's last-30 read posts get a soft boost in the follows-rail allocator (1.2x). | Rajesh |
| C-19 | Reading-history surfaced as "Continue reading" rail | When reader has > 5 reads, surface a "From your reading patterns" rail with the inferred tags they engage with. | Alex |
| C-20 | "Why am I seeing this?" disclosure chip | On any personalised card, a small i icon reveals: "We surfaced this because you follow `diaspora-maghreb-eu`." | Steve, Maya |
| C-21 | Privacy boundary doc | Maya + Sara Chen write `docs/GRIMBANEWS_READER_PRIVACY_POLICY.md`: cookie-only, no auth, no cross-session join, no third-party sharing, no fingerprinting beyond bot detection. | Maya, Sara Chen |
| C-22 | GDPR clear-my-data flow | Reader can wipe `grimba_culture` + `grimba_read` + vault + saved searches from a single "Reset personalisation" footer link. | Maya |
| C-23 | DNT (Do Not Track) honor | If `DNT: 1` header present, personalisation falls back to vanilla allocator. Logged silently. | Sara Chen |
| C-24 | Cookie consent banner integration | The 3-button consent banner includes a "Personalised content" toggle distinct from "Analytics". | Maya, Alex |
| C-25 | Region × culture interaction rule | A culture follow can pull posts from a region OTHER than the reader's current `grimba_region` (e.g. an Americas-region reader following `francophone-africa` sees those African posts in the follows-rail despite region scope). Document the bypass logic. | Rajesh, Liam |
| C-26 | Region × culture scope test | Sara Kim writes feature test: Americas-region cookie + francophone-africa follow → home renders an Americas hero AND African posts in the follows rail. | Sara Kim |
| C-27 | Performance budget | `followsRail()` adds < 80ms to home build. Eager-load source + tags. Indexed JSON queries verified on MySQL/MariaDB. | Rajesh, Larry |
| C-28 | NobuAI brand sweep | Tag picker, disclosure chip, reading-history rail copy — no provider names. | Sara Kim |
| C-29 | Phase C audit panel | Zen reviews allocator changes. Echo verifies a reader with 3 follows actually sees different content than a baseline reader. Mnemo cross-checks privacy doc against feedback_nobuai_model_branding + Iboga compliance memory. | Zen, Echo, Mnemo |
| C-30 | Phase C close | Steve signs off the reader experience; Maya signs off the privacy boundary. Push `darkvaderfr` BEFORE deploy. | Steve, Maya |

---

## Phase D — Latest + Breaking dual-stream (Sprints 91–115)

Split the urgency rail into two distinct streams. Latest is always-on
freshness. Breaking is editorial-keyword-driven. Marquee physics consistent
across every edition.

| Sprint | Title | Acceptance | Owner |
|--------|-------|------------|-------|
| D-01 | Current urgency-banner audit | Inventory the existing `urgency-banner.blade.php` logic: cache key, 18h window, keyword set, `__breakingMode` flag, CSS animation. Document inputs / outputs. | Rajesh |
| D-02 | `GrimbaHomeFeed::latest(region, lang, limit)` | Pure-fresh feed: status=published, region+lang scoped, sorted by `published_at` desc, deduped against hero + briefing + section blocks (re-uses existing allocator dedupe set). Returns up to 30 posts. Cached 60s per region+lang. | Rajesh |
| D-03 | `GrimbaHomeFeed::breaking(region, lang, hours)` | Strict-keyword-match feed: the existing 18h window + multi-word phrase set + title-only LIKE pre-filter + PHP regex finaliser. Cached 45s per region+lang. Returns 0..N posts (0 most of the time). | Rajesh |
| D-04 | Both methods covered by unit tests | Sara Kim writes phpunit tests with fixture posts in each region + lang, asserting `latest()` always returns ≥ N items (when corpus permits) and `breaking()` returns only keyword matches. | Sara Kim |
| D-05 | Latest never duplicates dossier hero | `latest()` excludes post IDs already taken by hero / briefing / section blocks. Vault and dossier-internal pages remain free to show the post — dedupe is a home-surface rule only. | Rajesh |
| D-06 | Per-region per-lang latest SLO | If `latest()` returns < 5 items for an edition+lang, log a freshness alert via `GrimbaAutomationMonitor`. Threshold tunable in settings. | Rajesh |
| D-07 | Refactor `urgency-banner.blade.php` to consume both | New consolidated partial calls `breaking()` first; if non-empty, render breaking mode (red eyebrow + "Breaking" + accent #e84c3d). Otherwise render latest mode (neutral eyebrow + "Latest" + ink color). | Nina |
| D-08 | Visual distinction between modes | Eyebrow differs: BREAKING (uppercase, red accent, pulse animation respecting `prefers-reduced-motion`) vs LATEST (uppercase, ink color, no pulse). Both share the same chrome. | Steve, Alex |
| D-09 | Marquee physics normalisation | JS measures the rendered track's pixel width on resize + content-change, sets `animation-duration` to `trackWidth / SPEED_PX_PER_SEC` (target 50 px/s). Track speed identical across every edition regardless of item count. | Nina |
| D-10 | Marquee duplicate-content guard | If the underlying feed has < 5 items, the marquee duplicates content inline (CSS `content` doubling) so the strip doesn't visibly restart mid-row. | Nina |
| D-11 | Marquee pause-on-hover + focus | Pauses on `:hover` and `:focus-within` for accessibility. Resumes on leave. | Sara Kim, Alex |
| D-12 | Reduced-motion full-stop | When `prefers-reduced-motion: reduce`, the strip becomes a static list (no scroll, no pulse). | Alex |
| D-13 | Mode-transition without layout shift | Switching breaking → latest (or vice-versa) reuses the same partial DOM; only data attributes flip. No height jump. | Nina |
| D-14 | Cache key includes mode | Avoid serving stale "breaking" markup once it should have downgraded. Cache key encodes the resolved mode for the window. | Rajesh |
| D-15 | Breaking → latest fall-through telemetry | When `breaking()` returns 0, log to `grimba_automation_runs` so we can see "we showed latest mode 96% of the time today". Surface in admin. | Rajesh |
| D-16 | Lang+region breaking keyword extension | Today's keyword set is FR + EN. Phase H of language plan introduces ES/PT/DE — leave hooks (per-locale keyword arrays) but no new keywords this phase. | Rajesh |
| D-17 | Dossier-page latest variant | The dossier page reuses `latest()` for its "more from this region" trailer — same dedupe rule applies relative to that dossier. | Rajesh |
| D-18 | Category-page latest variant | Each category page reuses `latest()` scoped to that category. Sorted by `published_at` desc. | Rajesh |
| D-19 | Search-results sort defaults to latest | When no other sort selected. | Liam, Nina |
| D-20 | "Last updated" stamp on latest rail | "Updated 3 min ago" (locale-aware). Honors translation chain. | Alex, Nina |
| D-21 | Stale-article badge | If a post inside `latest()` is older than X hours (configurable, default 6h) for a region that publishes more frequently, render a small "older" pill. Catches feed-mode regressions. | Alex |
| D-22 | Breaking-mode kill switch | Admin can force `breaking()` to return empty (e.g. to suppress a sensitive editorial alert). Stored in settings. | Maya, Rajesh |
| D-23 | Breaking-mode override switch | Admin can force a specific post into breaking for a short window. Useful for editorial calls. Audit-logged. | Maya, Rajesh |
| D-24 | Phase D audit panel | Zen reviews allocator + cache + state-machine code. Echo verifies marquee speed identical across all 4 editions side-by-side. Mnemo cross-checks against `GRIMBANEWS_INGEST_TO_PUBLIC_FRESHNESS_2026_05_11.md`. | Zen, Echo, Mnemo |
| D-25 | Phase D close | Steve signs off the visual distinction; Nina signs off marquee physics; push `darkvaderfr` BEFORE deploy. | Steve, Nina, Vader |

---

## Phase E — Freshness QA + observability (Sprints 116–135)

Make the freshness pipeline self-monitoring. Catch leaks before readers do.

| Sprint | Title | Acceptance | Owner |
|--------|-------|------------|-------|
| E-01 | Extend `grimba:ensure-daily-publish` to per-region per-lang | Today the command enforces a daily-publish floor globally. Extend to enforce per-region (`africa`, `europe`, `americas`, `international`) × per-lang (`fr`, `en`) cells. Reports each cell's count. | Rajesh |
| E-02 | Per-cell publish floor settings | Settings table entries: `grimba_publish_floor_africa_fr = 10`, etc. Defaults reasonable. | Larry, Liam |
| E-03 | Floor-violation alert | When any cell drops below floor for the day, surface in admin + log to Sentry-equivalent. Email Vader daily at 23:00 publisher TZ. | Rajesh, Sara Kim |
| E-04 | Reader-facing status pill | Footer-level "Africa edition: 17 articles in the last 24h" pill. Greys out under floor. Bilingual. | Alex, Nina |
| E-05 | Admin dashboard for ingest health | New admin route `/admin/ingest-health` — current cell counts, last-ingest-per-source, RSS feed health (re-uses `GrimbaRssFeedHealth`). | Liam, Alex |
| E-06 | Sort-order integration tests across all home + category surfaces | Sara Kim writes phpunit feature tests asserting: home `latest()` is sorted by `published_at` desc, every category page is sorted by `published_at` desc, no duplicate post ID across home sections, no post appears in `latest()` that already appears in hero/briefing/section-blocks. | Sara Kim |
| E-07 | Stale-article alert in `latest()` | If `latest()[0].published_at` is older than 4h for a region that nominally publishes ≥ 1/hour, raise an alert. | Rajesh |
| E-08 | Region-scope leak test | Feature test: with `grimba_region=africa` cookie, no post with `editorial_region=europe` may appear on any home surface (except via culture follow per Phase C-25). | Sara Kim |
| E-09 | Language-leak guard | Feature test: with `grimba_lang=en` cookie, every visible headline either has `translated_to=en` populated or is `original_language=en`. No FR headlines bleed through. | Sara Kim |
| E-10 | Breaking false-positive monitor | Daily scrape of yesterday's breaking-fired posts → manually-tagged "real" vs "noise" — if noise > 20%, tighten phrase set. | Liam, Maya |
| E-11 | Marquee-physics regression test | Playwright test: home page renders in each region, the marquee `animation-duration` computes within ± 5% of the 50px/s target across all 4 editions. | Sara Kim |
| E-12 | Freshness scorecard email | Daily 09:00 email to Vader: per-cell publish counts + breaking-fire counts + stale-alerts fired + follows-rail-hit-rate. | Liam, Rajesh |
| E-13 | Per-source freshness telemetry | Per-source "last published" timestamp. Surface in admin. | Rajesh |
| E-14 | Self-heal: auto-pause silent sources | If a source hasn't published in 14d AND its peers in the same region are healthy, auto-flag for editor review. | Rajesh |
| E-15 | Region-specific RSS backstops | Tied into existing `add_free_africa_rss_backstops` migration pattern — if Africa-FR cell drops below floor, auto-enable additional backstop feeds. | Rajesh |
| E-16 | Culture-tag freshness floor | Each followed tag has a published-in-7d count. If 0 for a follower's tag, surface "this culture has been quiet — explore [adjacent]". | Liam, Alex |
| E-17 | Latest-vs-Breaking time-spent telemetry | Beacon-track which mode the reader sees on each page-load + how long they hover on the strip. Privacy-respecting (cookie-only, anonymous). | Rajesh, Maya |
| E-18 | Visual regression snapshot for breaking + latest modes | Playwright pixel-diff both modes per region. | Sara Kim |
| E-19 | Phase E audit panel | Zen reviews observability code. Echo verifies "would I notice if Africa-EN dropped to zero today?" Mnemo cross-checks coverage. | Zen, Echo, Mnemo |
| E-20 | Phase E close + final signoff | Steve, Sara Chen, Maya, Liam, Vader all sign. Push `darkvaderfr` BEFORE deploy. | All, Vader |

---

## Cross-phase standing rules (baked into every commit)

1. **darkvaderfr git BEFORE prod.** Every coherent commit pushes to
   `darkvaderfr/grimbanews:main` before any VPS deploy. No exceptions.
   (`feedback_darkvaderfr_git_mandatory` / `feedback_github_always`.)
2. **NobuAI brand inviolable on every user-facing surface.** Advertiser
   dashboard, invoices, emails, telemetry, culture picker, reading-history
   rail, breaking alerts — none of them ever name Anthropic / OpenAI /
   Claude / GPT / Mistral / DeepL / Cohere / Llama / Groq. Phase B-27 and
   subsequent sanitiser sprints enforce. CI guard required.
3. **Bias colors fixed to editorial only.** `#3b82f6` (left) / `#a8a8a8`
   (centre) / `#e84c3d` (right) reserved for bias-meaning. Advertiser
   dashboard, culture picker, latest mode use ink palette only.
   Breaking mode (Phase D-08) is the ONE permitted reuse of `#e84c3d`
   — and only as an editorial-urgency accent, not a brand accent.
4. **Cookie-only persistence for readers; advertiser is the FIRST authed
   surface.** No reader gets an account. Advertiser sessions live behind
   `grimba_adv_session` (HttpOnly, Secure, SameSite=Lax) — distinct from
   `grimba_*` reader cookies. No cross-cookie join.
5. **No `git add -A`.** Each commit stages specific files per
   `feedback_git_add_specific`.
6. **Mythos audit panel (Zen / Echo / Mnemo) runs in parallel** on every
   non-trivial commit per `feedback_dream_team_audit`. Each phase explicitly
   schedules a panel sprint, but the rule is per-commit, not per-phase.
7. **Migrations only when Vader explicitly approves.** Phase A-07, Phase
   C-04 / C-05 are blocked behind explicit approval gates. No migration
   ships without Vader's per-migration green light.
8. **Mythos sprint cadence** — alternate audit / big / polish per
   `feedback_sprint_cadence_audit_big_polish`. Don't stack three big
   sprints back-to-back.
9. **Reduced motion respected** on every animated surface (marquee,
   breaking pulse, dashboard charts).
10. **Privacy boundary documented per phase.** Phase C-21 is the master
    doc; advertiser handling (Phase A-10, B-13, B-16, B-28) cross-references
    it.

---

## Roster (drawn from `project_iboga_full_roster.md`)

| Seat | Real name | Discipline this sprint stream |
|---|---|---|
| Product / Design | Steve Jobs | Pitch-page reinvention, brand discipline, cinematic UI on every new surface |
| PM | Liam Smith | Phase ordering, advertiser policy, sales handoff, editorial-policy linkage |
| Backend lead | Rajesh Kumar | Advertiser auth, ad event capture, allocator additions, freshness commands |
| Backend support | Lisa Nguyen | Cron + admin endpoints, ingest-health admin |
| Schema | Larry Ellison | advertisers / campaigns / creatives / ad_events tables + culture_tags JSON |
| Frontend lead | Nina Patel | Advertiser dashboard, follows-rail partial, marquee physics, mode-switch UI |
| UI/UX | Alex Morgan | Slot picker, culture picker, stale-pill, status-pill, disclosure chip |
| QA | Sara Kim | Region-leak tests, lang-leak tests, marquee-physics regression, advertiser RBAC, Playwright snapshots |
| CISO | Sara Chen | Advertiser auth review, ad-event PII handling, Stripe webhook hardening, DNT compliance |
| Compliance | Maya | Advertiser banned-content policy, NobuAI brand sweep, GDPR clear-my-data, breaking-mode kill switch |
| Infra | Jacob Lee | Stripe webhook reliability, queue worker tuning for ad-event ingest |
| Audit | Zen / Echo / Mnemo | Per-commit panel; explicit phase-close panels (A-29, B-29, C-29, D-24, E-19) |
| Oversight | Vader (signoff), Lucy Leai (strategy), Ray Dalio (advertiser unit economics) | Phase gates |

---

## Status

- **Plan locked:** 2026-05-17 (Vader directive)
- **Phase A kickoff:** awaiting first sprint claim
- **Phase B prerequisite:** Phase A close (A-30) + Stripe keys provisioned
- **Phase C prerequisite:** Vader approval on A-07-pattern schema gate for
  C-04 (culture_tags JSON column)
- **Phase D prerequisite:** none — can run in parallel with Phase A/B since
  it only touches the home allocator + existing urgency partial
- **Phase E prerequisite:** Phase D close (D-25) — observability targets
  Phase D's split streams
- **Recommended launch-week sequence:** Phase D first (highest reader-visible
  impact and unblocks Phase E), then Phase A pitch-page rebrand (A-01..A-05)
  for marketing-week external messaging, then Phase A advertiser auth + Phase
  B billing on parallel tracks, then Phase C as a culture-launch beat.

> **135 sprints across 5 phases. Owned by the Iboga Ventures roster.
> Advertiser is GrimbaNews' first authed surface; readers stay cookie-only
> forever; the freshness pipeline becomes self-monitoring.**
