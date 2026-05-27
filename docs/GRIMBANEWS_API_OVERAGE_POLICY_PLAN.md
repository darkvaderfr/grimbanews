# GrimbaNews — API Overage Policy Surrogate Plan

**Sprint ID:** S1257
**Status:** deferred → partial via surrogate documentation
**Master plan row:** `docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1251-s1260 — API overage policy`
**Walk wave:** CCCC

## Gating dependency

An overage policy needs:

- Quota tiers (S1256)
- Billing meter (S1254)
- Notification fan-out at 80% / 100% / overage (gates on customer-facing notification surface)
- Decision: hard cap vs metered overage vs auto-upgrade

## Surrogate-now infra

- **`GrimbaProviderCredits` budget guard** — internal pattern that emits warnings at 80%/100% of daily budget
- **`grimba_health` cron** — fail-on-risk daily — pattern for at-rest threshold checks
- **`AdvertiserLeadController`** — per-IP rate ladder is the architectural cousin of the per-key ladder

## Honest framing

Decision-heavy, low-build: once tiers + meter exist, the overage *policy* is a 1-page op-ed (operator chooses hard cap / metered / upgrade) and a 2-day code change. Today it sits behind both gates.

## Owners

- **Strategy:** Ray Dalio — overage economics (Stripe metered vs hard cap NPS impact)
- **Finance:** Warren Buffett — revenue treatment
- **Product:** Liam Smith — UX policy (toast vs email vs in-app)
- **Customer success:** Emma Brown — handle-and-escalation playbook
- **Audit panel:** Zen / Echo / Mnemo at PR time

## Cross-references

- Sprint plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1257 row)
- Billing meter: `docs/GRIMBANEWS_API_BILLING_METER_SCOPE.md`
- Quota tiers: `docs/GRIMBANEWS_API_QUOTA_TIERS_PLAN.md`
- Roster: `/Users/vb/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
