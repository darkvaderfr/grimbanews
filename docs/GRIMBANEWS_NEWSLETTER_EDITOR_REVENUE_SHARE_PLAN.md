# GrimbaNews — Newsletter Editor Revenue Share Surrogate Plan

**Sprint ID:** S1288
**Status:** deferred → partial via surrogate documentation
**Master plan row:** `docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1281-s1290 — Newsletter revenue share (with editors)`
**Walk wave:** CCCC

## Gating dependency

A per-editor newsletter revenue share needs:

- In-house editor program (S1311+ deferred)
- Per-newsletter attribution ledger (which editor owns which send)
- Stripe Connect for payouts (gates on S1456 contributor payout integration)
- Revenue allocation policy (gross vs net, per-send vs per-subscriber)
- Tax-form intake (W-9 / W-8BEN / FR equivalent)

## Surrogate-now infra

- **`docs/GRIMBANEWS_CONTRIBUTOR_PAYOUT_PLAN.md`** — adjacent contributor-payout doc establishes the Stripe Connect pattern
- **`grimba:saved-search-digests`** — per-recipient ledger that an editor-attribution column could extend
- **`docs/GRIMBANEWS_REVENUE_SHARE_PARTNER_LEDGER.md`** — analogous per-partner ledger scope

## Honest framing

Deeply downstream — requires an editor program that doesn't exist (single-tenant Botble auth today). Decision-heavy: revenue split formula (50/50, 70/30, performance-based) is a board call before any code.

## Owners

- **Strategy:** Ray Dalio — revenue split economics
- **Finance:** Warren Buffett — payout treatment + tax forms
- **Marketing:** Henry Walker — editor program scope
- **Product:** Liam Smith — attribution UX
- **Audit panel:** Zen / Echo / Mnemo at PR time

## Cross-references

- Sprint plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1288 row)
- Contributor payout: S1456 (deferred)
- Editor program: S1311+ (deferred)
- Roster: `/Users/vb/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
