# GrimbaNews — Sponsorship Slot Inventory

**Status:** inventory v0 (no per-cluster sponsorship slot ships; /advertise lead pipeline is the sales-side)
**Owner:** Ray Dalio (CFO) on price + Lucy Leai (Strategy) on brand-safety + Steve Jobs (CPO) on UX
**Walks:** Mythos S1274 (sponsored-content slots) deferred → partial (advancing existing partial to firmer position)
**Gating dependency:** Brand-safety review per advertiser + paid newsletter (per `docs/GRIMBANEWS_NEWSLETTER_MONETIZATION_SCOPE.md`) for newsletter-sponsorship slot. Inventory + price card itself is operator-side.

## Why this exists

S1274 is partial because the **sales-side** lead pipeline ships at `/advertise` + `/admin/grimba/advertiser-leads`, but no **per-slot inventory + price card** exists. Ray needs the slot inventory + recommended pricing locked before sales conversations can quote consistently. This document is the inventory.

## Slot inventory

### Display (site-side)

| Slot | Location | Format | Per-day cap | Rec. price (CPM) |
|---|---|---|---|---|
| home-leaderboard | `/` top of fold | 728×90 banner | — (always-on) | €0.50 floor |
| home-native | `/` between rails | native card | — | €1.00 |
| sidebar | `/dossier/*` sidebar | 300×600 / 300×250 | — | €0.40 |
| in-article-1 | article body, after 2nd ¶ | 300×250 native | — | €0.50 |
| in-article-2 | article body, after 5th ¶ | 300×250 native | — | €0.50 |
| cluster-mid | `/dossier/{id}` mid-page | 728×90 or native | — | €0.60 |
| dossier-foot | `/dossier/{id}` below fold | 300×250 | — | €0.30 |

Code reference: `app/Support/GrimbaAds.php::SLOTS`.

### Newsletter (gates on per-region digest per `docs/GRIMBANEWS_PER_REGION_DAILY_DIGEST_CADENCE.md`)

| Slot | Position | Format | Cap | Rec. price |
|---|---|---|---|---|
| digest-top-sponsor | Daily digest top | "Presented by" line + 600×100 banner | 1 per issue per region | €500 / issue |
| digest-mid-native | Daily digest mid | Native ad unit (text + 200×200 image) | 1 per issue | €300 / issue |
| digest-footer-grid | Daily digest footer | "Brought to you by" + sponsor logo grid (max 4) | 1 per issue | €100 / logo / issue |

Newsletter ad revenue capped at 20% of total newsletter revenue per `docs/GRIMBANEWS_NEWSLETTER_MONETIZATION_SCOPE.md`.

### Sponsored content (per-cluster)

| Slot | Where | Format | Rules |
|---|---|---|---|
| sponsored-cluster-tile | `/` rails | Distinguished card with sponsor logo + "Sponsored by" header | Editorial review by Lucy before run; "Sponsored" label always visible; clearly distinguishable from editorial |
| sponsored-cluster-page | `/dossier/{id}` | Full dossier card | Same rules; sponsor-logo at top |

Hard rules:
- "Sponsored" label always visible.
- No mid-paragraph native blend that mimics editorial.
- Cluster classification, bias bar, source breakdown shown unchanged.
- Sponsor cannot influence editorial selection of cluster.

### Partner placements (per `docs/GRIMBANEWS_NEWSROOM_PARTNERSHIP_TEMPLATE.md`)

| Slot | Where | Format |
|---|---|---|
| partner-badge | every partner-tagged article | "Via [Partner]" + logo |
| partner-stream | `/partenaire/{slug}` | Listing page |

No revenue charge to partner. Reciprocal cross-promo only.

## Brand-safety policy

Per `docs/GRIMBANEWS_EDITORIAL_STYLE_GUIDE.md` anti-pattern list applies to ad creatives. Specific:

**Always allowed:**
- B2B SaaS, professional services, educational courses, books, films, cultural events.
- Climate-positive initiatives.
- Public-service campaigns.

**Never allowed:**
- Firearms, predatory financial products, payday loans, tobacco, alcohol over X% (operator threshold).
- Political-campaign messaging (preserves editorial neutrality).
- Adult content.
- Anti-vax / pseudo-medical claims.
- Anti-immigration / anti-minority dog-whistles.

**Case-by-case (Lucy review):**
- Cryptocurrency / Web3.
- Alcohol under threshold.
- B2B AI / ML services (verify no NobuAI competitor messaging in ad creative — keep underlying provider invisible per `feedback_nobuai_model_branding.md`).

## Per-region price modifier

Africa-region pricing 50% of metro-EU pricing reflecting market reality. Operator pricing card:

| Region | Modifier |
|---|---|
| France métropolitaine | 1.0× |
| Europe non-FR | 1.0× |
| North America EN | 1.2× |
| Africa francophone | 0.5× |
| Africa anglophone | 0.5× |
| DOM-TOM | 0.6× |
| Caribbean | 0.7× |

## Operator workflow

1. Lead arrives via `/advertise` form → `App\Http\Controllers\AdvertiserLeadController` writes to `grimba_advertiser_leads` table.
2. Sales mailbox per-region routes (existing per `App\Mail\GrimbaAdvertiserLeadNotification`).
3. Ray + Lucy review lead within 48 hours.
4. Quote sent (per inventory + region-modifier).
5. Brand-safety review of creative.
6. Contract signed.
7. Slot configured + cap set in `App\Support\GrimbaAds::SLOTS`.
8. Per-campaign tracking via UTM tags.

## Reporting + invoicing

- **Per-campaign reporting:** monthly PDF (operator-generated until automated per `docs/GRIMBANEWS_AD_REVENUE_DASHBOARD_SCOPE.md`).
- **Invoicing:** operator-side billing (Ray) until Stripe wires per `docs/GRIMBANEWS_PCI_DSS_SCOPE_STATEMENT.md` ships.

## Engineering effort estimate

- Per-slot cap enforcement in `GrimbaAds::shouldRender()`: 1 sprint.
- Sponsored-cluster-tile rendering: 2 sprints.
- Partner-badge UI (per `docs/GRIMBANEWS_NEWSROOM_PARTNERSHIP_TEMPLATE.md` S1324): 1 sprint.
- Per-campaign UTM tagging: 0.5 sprint.
- Per-campaign report generator: 2 sprints.
- **Full ship: ~6 sprints, gates on first signed campaign.**

## Cross-references

- Master plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1274; sister S1271-S1280)
- Sister docs: `docs/GRIMBANEWS_AD_REVENUE_DASHBOARD_SCOPE.md`, `docs/GRIMBANEWS_HEADER_BIDDING_PLAN.md`, `docs/GRIMBANEWS_NEWSLETTER_MONETIZATION_SCOPE.md`, `docs/GRIMBANEWS_NEWSROOM_PARTNERSHIP_TEMPLATE.md`, `docs/GRIMBANEWS_EDITORIAL_STYLE_GUIDE.md`, `docs/GRIMBANEWS_PER_REGION_DAILY_DIGEST_CADENCE.md`, `docs/GRIMBANEWS_B2B_ADVERTISER_SELF_SERVE_PLAN.md`
- Ad slots code: `app/Support/GrimbaAds.php::SLOTS`
- Lead pipeline: `app/Http/Controllers/AdvertiserLeadController.php`, `app/Mail/GrimbaAdvertiserLeadNotification.php`
- Brand-purity lock: `~/.claude/projects/-Users-vb-kaizen/memory/feedback_nobuai_model_branding.md`
- Iboga roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
