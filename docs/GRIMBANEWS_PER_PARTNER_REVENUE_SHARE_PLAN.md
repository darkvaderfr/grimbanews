# GrimbaNews — Per-Partner Revenue Share Surrogate Plan

**Sprint ID:** S1326
**Status:** deferred → partial via surrogate documentation
**Master plan row:** `docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1321-s1330 — Per-partner revenue share`
**Walk wave:** CCCC

## Gating dependency

Per-partner revenue share needs:

- Paid tier (S1261 Stripe install)
- Newsletter editor revenue share (S1288) shape extended to syndication/B2B partners
- Per-partner attribution ledger (which subscriber / API customer is "owned" by which partner)
- Stripe Connect or off-platform payout pipeline
- Contract template defining split, payment terms, dispute resolution

## Surrogate-now infra

- **`docs/GRIMBANEWS_REVENUE_SHARE_PARTNER_LEDGER.md`** — adjacent partner ledger scope doc
- **`docs/GRIMBANEWS_NEWSLETTER_EDITOR_REVENUE_SHARE_PLAN.md`** — sister doc for editor-side share
- **`docs/GRIMBANEWS_PER_PARTNER_ANALYTICS_SCOPE.md`** — adjacent per-partner analytics scope
- **`grimba_advertiser_leads_sales_mailbox`** — per-region routing pattern that proves partner attribution is solvable

## Honest framing

Downstream of both S1261 (paid tier) and an actual partner roster. Operator-side contract decisions dominate the work; the engineering is straightforward once those land.

## Owners

- **Strategy:** Ray Dalio — split economics
- **Finance:** Warren Buffett — payout treatment
- **Business Dev:** Victor Garcia — partner contract template
- **Backend:** Rajesh Kumar — attribution ledger
- **Audit panel:** Zen / Echo / Mnemo at PR time

## Cross-references

- Sprint plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1326 row)
- Newsletter editor share: `docs/GRIMBANEWS_NEWSLETTER_EDITOR_REVENUE_SHARE_PLAN.md`
- Per-partner SLA: S1327 (deferred)
- Roster: `/Users/vb/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
