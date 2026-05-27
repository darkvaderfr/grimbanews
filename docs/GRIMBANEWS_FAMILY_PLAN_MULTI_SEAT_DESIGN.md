# GrimbaNews — Family Plan (Per-Publisher-Tier Multi-Seat Bundling) Design

**Sprint ID:** S1264
**Status:** deferred → partial via surrogate documentation
**Master plan row:** `docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1261-s1270 — Family plan`
**Walk wave:** BBBB

## Gating dependency

A family / multi-seat plan needs:

- Stripe install (S1261, deferred)
- A `subscriptions` table with `seat_count` + `seat_owner_member_id`
- A `subscription_members` join table (per-seat → per-member binding)
- Invite-flow UX (owner sends seat invite via email link)
- Per-seat billing impact (Ray-CFO unit economics; usually flat-fee with seat cap)
- Upgrade / downgrade flow (S1266, deferred)

None ship today.

## Surrogate-now infra

- **Botble `members` table** — supports multiple members already; just no group / seat-ownership concept
- **Per-member newsletter subscription** — each household member subscribes individually today (free); family-plan adds paid-tier shared access
- **Multi-publisher bundling concept** — could extend to "Nobu Kaizen pass" cross-product (NobuTrust + GrimbaNews + Yabacademy); out of S1264 scope but on the wider Iboga product roadmap

## Honest framing

Family plan is a packaging decision more than a code decision once S1261-S1263 are live. The seat-invite flow + per-seat cap is ~3 days of work after the billing rail exists.

## Owners

- **Product:** Liam Smith — seat-invite UX + plan packaging
- **Backend:** Rajesh Kumar — `subscriptions` + `subscription_members` schema
- **Finance:** Ray Dalio + Warren Buffett — pricing + seat cap economics
- **Frontend:** Nina Patel — invite-flow UI
- **Audit panel:** Zen / Echo / Mnemo at PR time

## Cross-references

- Sprint plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1264 row)
- Subscriber tier monthly: `docs/GRIMBANEWS_SUBSCRIBER_TIER_MONTHLY_DESIGN.md`
- Gift sub sister: `docs/GRIMBANEWS_GIFT_SUBSCRIPTION_DESIGN_S1265.md`
- Roster: `/Users/vb/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
