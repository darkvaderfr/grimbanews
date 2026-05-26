# GrimbaNews — Ad Revenue Dashboard Scope

**Status:** scope v0 (no unified ad-revenue dashboard; AdSense console is the surrogate)
**Owner:** Ray Dalio (CFO) on metrics + Jacob Lee (DevOps) on pipeline
**Walks:** Mythos S1280 (ad revenue dashboard) deferred → partial
**Gating dependency:** Single-network state (AdSense console suffices). Becomes load-bearing once header bidding (S1271) + multiple SSPs ship, where per-source CPM splits matter.

## Why this exists

S1280 was honest-deferred with "surrogate is AdSense console" — that surrogate is accurate today because we ship one ad network. Once header bidding lands per `docs/GRIMBANEWS_HEADER_BIDDING_PLAN.md`, the AdSense console only shows AdSense's share and operators have no unified view across SSPs. This doc defines the **dashboard scope** so the moment we have multi-source data the dashboard ships as a straight engineering task, not a fresh design pass.

## Today's surrogate

- **Source of truth:** Google AdSense console (https://www.google.com/adsense).
- **Cadence:** Daily auto-refresh in the console; no internal pull.
- **Operators with access:** Vader + Ray Dalio (CFO).
- **No internal metric.** No `grimba_ad_revenue` table; no scheduled pull from AdSense API.

## Target dashboard

**Surface:** `/admin/grimba/ad-revenue` (new admin route).

**Time window controls:** Last 24h / 7d / 30d / 90d / YTD.

**Top-line metrics:**

| Metric | Source |
|---|---|
| Total revenue (period) | sum across SSPs + AdSense |
| Total impressions | sum across SSPs + AdSense |
| Average CPM | total revenue / total impressions × 1000 |
| Average viewability | per-SSP viewability rate (vendor SDK) |
| Fill rate | (impressions / requests) × 100 |

**Per-SSP breakdown:**

| Column | Notes |
|---|---|
| SSP name | AdSense / Magnite / Index Exchange / OpenX / PubMatic / Amazon TAM |
| Revenue | per period |
| Impressions | per period |
| Win rate | (wins / bid attempts) × 100 |
| Average CPM | |
| Viewability | |

**Per-slot breakdown:**

Same columns as per-SSP but rows are GrimbaNews ad slots from `app/Support/GrimbaAds.php::SLOTS`:
- home-leaderboard
- home-native
- sidebar
- in-article-1
- in-article-2
- cluster-mid
- dossier-foot

**Per-edition breakdown (when France / Africa / International editions diverge):**

Rows: `editorial_region` ∈ {africa, international, dom-tom}.

## Data pipeline

When header bidding ships per `docs/GRIMBANEWS_HEADER_BIDDING_PLAN.md`:

1. **Per-SSP daily API pull.** Each SSP exposes a reporting API. Schedule a `grimba:fetch-ad-revenue` command at 04:30 daily (post-newsletter-window).
2. **Persist to `grimba_ad_revenue_daily` table.** Columns: `date, ssp, slot, edition, revenue, impressions, requests, viewability, currency`.
3. **AdSense pull via AdSense Management API.**
4. **Per-slot attribution** from prebid wrapper's `bidWon` events — log to same table via `/api/v1/internal/ad-events` (admin-only endpoint).
5. **Dashboard query** = aggregate from `grimba_ad_revenue_daily` at view time (sub-second on monthly window).

## Alerting

- **Revenue drop >40% day-over-day** → page Jacob + Ray (per `docs/GRIMBANEWS_ONCALL_ROSTER_TEMPLATE.md` P1 tier).
- **Fill rate drop >30%** → SSP outage suspicion; investigate via per-SSP win rate column.
- **Viewability drop >20%** → CLS regression suspicion; trigger lighthouse audit.

Alerts piggyback on `App\Support\GrimbaAutomationMonitor` ledger so the cockpit board already surfaces them.

## Authority + access

- **Read:** Vader, Ray Dalio, Steve Jobs.
- **Edit (configure slot floors / SSP toggles):** Vader + Jacob Lee only.
- **Export:** CSV export per period (gates on `docs/GRIMBANEWS_ANALYTICS_WAREHOUSE_PLAN.md` warehouse-CSV-pipeline pattern).

## Engineering effort estimate

- Schema + migration: 1 sprint.
- Per-SSP API adapter (one per SSP): 1 sprint per SSP.
- Dashboard view: 2 sprints.
- Alerting wiring: 0.5 sprint.
- Full ship: **~8 sprints once header bidding live**.

## Cross-references

- Master plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1280; S1271-S1279 upstream)
- Ad slot definitions: `app/Support/GrimbaAds.php::SLOTS`
- Sister docs: `docs/GRIMBANEWS_HEADER_BIDDING_PLAN.md`, `docs/GRIMBANEWS_ANALYTICS_WAREHOUSE_PLAN.md`, `docs/GRIMBANEWS_ONCALL_ROSTER_TEMPLATE.md`
- Cockpit board: `resources/views/grimba-admin/cockpit.blade.php`
- Automation ledger: `app/Support/GrimbaAutomationMonitor.php`
