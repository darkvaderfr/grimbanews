# GrimbaNews — Subscriber Tier (Annual) Design

**Sprint ID:** S1263
**Status:** deferred → partial via surrogate documentation
**Master plan row:** `docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1261-s1270 — Subscriber tier (annual)`
**Walk wave:** BBBB

## Gating dependency

Annual tier is a price-list + Stripe-product-config delta on top of monthly (S1262). It needs:

- S1262 monthly tier shipped first
- A second Stripe Price ID (annual interval)
- A toggle in the upgrade flow (S1266, deferred)
- A discount ratio (typical 16-25% off monthly × 12) — Ray-CFO call
- A prorated upgrade from monthly → annual

## Surrogate-now infra

- **Same surrogate as monthly tier** — all content free; no annual cadence to disclose
- **Newsletter subscription frequency** — readers already pick weekly / daily cadence on the newsletter; annual is a billing analog of that commitment

## Honest framing

Annual tier follows monthly trivially. Almost always shipped in the same sprint pair. Tracked as a separate deferred row because the discount-ratio decision is a real exec decision, not a code decision.

## Owners

- **CFO:** Ray Dalio + Warren Buffett — discount-ratio decision
- **Product:** Liam Smith — toggle UX
- **Backend:** Rajesh Kumar — Stripe Price config + prorate logic
- **Audit panel:** Zen / Echo / Mnemo at PR time

## Cross-references

- Sprint plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1263 row)
- Monthly sister tier: `docs/GRIMBANEWS_SUBSCRIBER_TIER_MONTHLY_DESIGN.md`
- Roster: `/Users/vb/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
