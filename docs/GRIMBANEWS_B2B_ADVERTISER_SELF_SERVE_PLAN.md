# GrimbaNews — B2B Advertiser Self-Serve Plan

**Status:** scaffolded (migration drafted, NOT applied) · pre-Stripe-engagement
**Walks:** Mythos S1231-S1260 (B2B API + advertiser tier) deferred → partial
**Owner:** Ray Dalio (CFO) cost-model · Lucy Leai (Strategy) market fit · Sara Chen (CISO) DPA · Steve Jobs (CPO) UX

## Why this exists

The `/advertise` page is currently **informational only** — a lead-capture form lands rows in `grimba_advertiser_leads`. There is no self-serve campaign creation, no Stripe billing, no editorial-review queue, no live serving. Vader's gut-check on "ready for primetime" flagged this as one of the explicit "NOT ready" items.

This plan scaffolds the B2B self-serve loop AS FAR AS POSSIBLE without:
- A live Stripe API key (charging real cards)
- A DB migration run (Vader's "no migrations without ask" rule)
- A first paying advertiser (no editorial review queue to test yet)

What lands in this commit:
1. **Schema draft** at `database/migrations/2026_05_23_140000_create_grimba_advertiser_campaigns_table.php.draft` — full advertiser_campaigns schema with Stripe-ready placeholders, ready to rename + run when Vader approves.
2. **This workflow runbook** — covers the lead → review → live → end lifecycle.
3. **Honest list of remaining work** — what blocks the live billing loop.

## Schema decision (for review)

One row per CAMPAIGN, not per lead. Justification:
- A single advertiser may run multiple campaigns over time
- A lead can never convert (still needs the row)
- A campaign can exist without a prior lead (admin-entered, e.g. for partnership deals)

Key columns:
- `advertiser_lead_id` (FK, nullable) — trace lineage
- `creative_url`, `click_url`, `creative_alt_text` — the served asset
- `slot_targeting`, `edition_targeting`, `topic_targeting` (JSON) — where it appears
- `exclusion_keywords` (JSON) — operator's exclusion list per S1808 brand-safety contract
- `starts_at` / `ends_at` / `daily_impression_cap` / `lifetime_impression_cap` — scheduling
- `billing_tier` / `stripe_customer_id` / `stripe_subscription_id` / `stripe_payment_intent_id` / `amount_cents` / `currency` — Stripe-ready placeholders
- `status`: `pending_review | approved | live | paused | ended | rejected`
- `reviewed_at`, `reviewed_by`, `review_notes`, `rejection_reason` — editorial review log
- `impressions_count`, `clicks_count`, `completions_count` — cached telemetry rollups

## Lifecycle (operator runbook)

```
[ /advertise form submit ]                          [ admin direct entry ]
              │                                                │
              ▼                                                ▼
   grimba_advertiser_leads row                  grimba_advertiser_campaigns row
              │                                                │
              ├── (admin review)                              ─┤
              │                                                │
              ▼                                                ▼
   campaign created from lead                            status=pending_review
              │                                                │
              ▼                                                ▼
        status=pending_review ──── reviewed_by=admin ──→ status=approved | rejected
              │
              ▼                                  Stripe checkout (separate sprint)
        status=approved
              │
              ▼
        Operator publishes (sets status=live, starts_at, ends_at)
              │
              ▼
        status=live  ──── impression / click / completion telemetry ───
              │                                                │
              │                                  ┌─────────────┘
              ▼                                  ▼
        Time elapses           OR     impression_cap hit
              │                                  │
              ▼                                  ▼
        status=ended                       status=ended
```

## What ships today vs deferred

| Capability | Today | Stripe-engagement | Operator-only |
|---|---|---|---|
| `/advertise` lead capture form | ✅ shipped | — | — |
| `grimba_advertiser_leads` table + admin queue | ✅ shipped | — | — |
| Editorial review on leads (mark contacted / converted) | ✅ shipped | — | — |
| `grimba_advertiser_campaigns` table | 🟡 schema drafted, not applied | — | — |
| Self-serve campaign creation form (member-side) | ⬜ deferred (S1247) | needs S1241 OAuth | — |
| Editorial review queue UI for campaigns | ⬜ deferred (S1244) | — | needs schema applied |
| Live serving (campaign → ad slot rendering) | ⬜ deferred (S1271 native + S1272 banner) | — | needs schema + admin UI |
| Stripe customer creation | ⬜ deferred (S1212 paid tier) | **YES — gated on Stripe API key** | — |
| Stripe checkout integration | ⬜ deferred (S1212 paid tier) | **YES — gated on Stripe API key** | — |
| Per-campaign telemetry dashboard | ⬜ deferred (S1261) | — | needs schema |
| CSV export of telemetry | ⬜ deferred (S1262) | — | needs schema + telemetry |
| Weekly billing-cycle reconciliation | ⬜ deferred (S1213) | **YES — gated on Stripe** | — |

## Honest "primetime" answer for B2B

**NOT primetime-ready for paid advertisers.** What we have today:
- Lead capture (advertisers can express interest)
- Manual editorial flow (admin reads leads + contacts via email + processes contracts off-site)

What's missing for paid:
- Stripe wiring (~6h of work + API key from Vader's Stripe account)
- Admin campaign-review queue (~4h)
- Live campaign serving in ad slots (~6h)
- Telemetry pipeline (~8h)

**Estimate to flip B2B to primetime:** 1-2 dev weeks plus a working Stripe key. The schema + workflow + brand-safety contracts are all drafted; the engineering is mostly wiring.

## Migration apply checklist (when Vader approves)

```bash
cd /Users/vb/GrimbaNews
mv database/migrations/2026_05_23_140000_create_grimba_advertiser_campaigns_table.php.draft \
   database/migrations/2026_05_23_140000_create_grimba_advertiser_campaigns_table.php
php artisan migrate --pretend  # review SQL first
php artisan migrate
sqlite3 database/grimbanews.sqlite ".schema grimba_advertiser_campaigns"
```

The `down()` method drops the campaigns table AND the stripe_customer_id column added to leads. Rollback is clean.

## Smaller pre-Stripe wins that DON'T need the schema

These can ship before the migration apply:

1. **Admin "convert lead to campaign" link** — adds a button on the existing lead-detail admin view that drops the operator into a "campaign creation" form. Without the table, the form posts nowhere — but the UX is wireable now and would prove out before the schema is applied. **Recommend: defer until schema applied (avoid orphan button).**

2. **Editorial review checklist doc** — a `docs/GRIMBANEWS_AD_REVIEW_CHECKLIST.md` enumerating what an admin should check on a campaign before approving (brand-safety, exclusion keywords applied, creative aspect ratio, click_url HTTPS, GDPR cookie consent compatible). **Can ship now.**

3. **`grimba:campaigns:telemetry-rollup` command** — pulls per-impression / per-click events from a future event log and aggregates into the campaign row. Stubbed today so the operator runbook has a clear "run this nightly" command. **Defer until schema applied.**

## Editorial review checklist (anticipates S1244 + brand-safety contract)

Before flipping any campaign from `pending_review` → `approved`:

- [ ] Creative ≤ 1MB, ≤ 1200×675 (banner) or ≤ 800×600 (native)
- [ ] `click_url` is HTTPS and resolves to a non-error landing page
- [ ] `creative_alt_text` is set (a11y)
- [ ] `exclusion_keywords` set per brand-safety guidance (default: violence, hate speech, adult content)
- [ ] `slot_targeting` doesn't include `comparatif_sidebar` for any campaign that could be confused with editorial framing (S1808 contract)
- [ ] `starts_at` ≥ now() + 24h (gives editorial time to swap if last-minute concerns)
- [ ] `billing_tier` matches the Stripe invoice / subscription line
- [ ] `advertiser_email` matches the lead-source-of-record (audit trail)
- [ ] No political-party / government / press-group conflict (per `docs/GRIMBANEWS_VENDOR_REGISTER.md` editorial-independence clause)

Reviewer fills `reviewed_at` + `reviewed_by` + `review_notes` on approve OR `rejection_reason` on reject.
