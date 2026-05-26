# GrimbaNews — Header Bidding Plan

**Status:** plan v0 (no prebid wrapper shipped; AdSense single-network today)
**Owner:** Ray Dalio (CFO) on unit economics + Jacob Lee (DevOps) on integration + Steve Jobs (CPO) on UX-impact
**Walks:** Mythos S1271 (header bidding plan) deferred → partial
**Gating dependency:** SSP relationships (Magnite / Index Exchange / OpenX / PubMatic) + GAM 360 publisher account or equivalent ad server + minimum monthly impression floor to justify SSP onboarding. This document is the **integration plan** that those vendor conversations will iterate from.

## Why this exists

S1271 was honest-deferred because GrimbaNews ships **AdSense single-network** today via `app/Support/GrimbaAds.php::SLOTS` (one global render path; AdSense decides the price). Header bidding (prebid.js or Amazon TAM) is a meaningful revenue-lift play that needs **SSP onboarding work upstream of any code change**. This plan documents the integration shape so the moment SSPs are onboarded the engineering scope is a straight task list, not a discovery project.

## Today's ads surface

- `app/Support/GrimbaAds.php::SLOTS` defines slot inventory (home-leaderboard, home-native, sidebar, in-article-1, in-article-2, cluster-mid, dossier-foot).
- `app/Support/GrimbaAds.php::shouldRender()` is the single gate (respects member opt-out, cookie consent posture per `docs/GRIMBANEWS_CONSENT_LOG_DESIGN.md`, admin-side disable).
- `resources/views/partials/ads/google-adsense.blade.php` is the single AdSense render template.
- No prebid wrapper, no Amazon TAM tag, no GAM ad-server. Single-network = AdSense decides the price.

## Why header bidding lifts revenue

- Single-network = AdSense bids alone. No competition = no price discovery.
- Header bidding runs N SSPs in parallel **before** the AdSense call; AdSense competes against the highest SSP bid (or Amazon TAM bid).
- Industry reports lift of **10-30%** on display revenue depending on inventory quality.
- Lift compounds once viewability tracking (S1279) and floor pricing (S1276) ship.

## Integration shape (when SSPs onboard)

1. **Choose wrapper** — recommend **prebid.js** (open-source, vendor-neutral, ~100k publishers). Amazon TAM as parallel demand source (single-tag, lower friction).
2. **Provision ad server** — Google Ad Manager (GAM) 360 (or GAM Small Business + manual line-items if floor too low for 360).
3. **Onboard SSPs** — start with **3-4 demand partners**. Recommended initial set (subject to vendor sales conversations):
   - Magnite (formerly Rubicon)
   - Index Exchange
   - OpenX
   - PubMatic
4. **Configure prebid wrapper** — wrap each slot in `app/Support/GrimbaAds.php::SLOTS` as a prebid `adUnit` with bidder configs.
5. **Wire wrapper** — load prebid.js bundle in `partials/ads/google-adsense.blade.php`; replace direct AdSense call with prebid → GAM call.
6. **Set timeout budget** — prebid timeout ≤ 800ms (above which fall through to AdSense direct to avoid blocking render).
7. **Brand-safety chain** — keep `App\Support\GrimbaIngestGuardrails` keyword filter for editorial-side brand-safety (per S1277 partial); add ad-side brand-safety via SSP-provided keyword lists.
8. **Cookie consent integration** — prebid wrapper must respect the `grimba_consent` cookie; non-consenting readers get contextual-only ads (no behavioral) per `docs/GRIMBANEWS_GDPR_ROPA.md`.

## Performance constraints

- Header bidding **adds latency** (parallel SSP calls before render).
- Mitigate via:
  - 800ms hard timeout.
  - Lazy-load below-fold slots (in-article-2, dossier-foot).
  - Async wrapper load (do not block DOMContentLoaded).
- Track CLS regression via per-build `docs/GRIMBANEWS_ADMIN_PROD_READINESS_SMOKE.md` lighthouse audits.

## Privacy + consent

- Header bidding fires identity signals (User ID 2.0 / shared IDs / vendor SDKs).
- Non-consent path = contextual-only fallback (AdSense direct, no SSPs).
- Consent path = full prebid wrapper + SSPs.
- Per `docs/GRIMBANEWS_CONSENT_LOG_DESIGN.md` consent posture is captured server-side; prebid wrapper reads same cookie.

## Floor pricing (S1276 dependency)

Once SSPs are bidding, set **per-slot floor prices** to prevent race-to-bottom CPM. Recommended initial floors:
- Home leaderboard: $0.50 CPM floor.
- Sidebar: $0.30 CPM.
- In-article: $0.40 CPM.
- Cluster-mid: $0.50 CPM (higher engagement context).
- Dossier-foot: $0.20 CPM.

Floors iterate quarterly per Ray's unit-economics review.

## Reporting + dashboards (S1280 dependency)

- Per-SSP win-rate report (which SSP wins how often).
- Per-slot CPM dashboard.
- Daily revenue dashboard (today: AdSense console; future: unified per-source via warehouse per `docs/GRIMBANEWS_ANALYTICS_WAREHOUSE_PLAN.md`).

## Engineering effort estimate (when SSPs onboarded)

- Prebid wrapper integration: ~2 sprints.
- GAM line-item provisioning: ~1 sprint (operator side).
- Per-SSP onboarding + creative review: ~1 sprint per SSP (vendor pace).
- A/B test on revenue lift (gates on S1346 A/B harness): ~2 sprints.
- Full ship + measurement: **~10-12 sprints once SSPs are signed**.

## Cross-references

- Master plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1271-S1280)
- Ads surface: `app/Support/GrimbaAds.php`, `resources/views/partials/ads/google-adsense.blade.php`
- Sister docs: `docs/GRIMBANEWS_CONSENT_LOG_DESIGN.md`, `docs/GRIMBANEWS_GDPR_ROPA.md`, `docs/GRIMBANEWS_B2B_ADVERTISER_SELF_SERVE_PLAN.md`, `docs/GRIMBANEWS_ADVERTISER_CULTURE_FRESHNESS_PLAN.md`, `docs/GRIMBANEWS_ANALYTICS_WAREHOUSE_PLAN.md`
- Brand-safety upstream filter: `app/Support/GrimbaIngestGuardrails.php`
- Iboga roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
