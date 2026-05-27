# GrimbaNews — API Quota Tiers Surrogate Plan

**Sprint ID:** S1256
**Status:** deferred → partial via surrogate documentation
**Master plan row:** `docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1251-s1260 — API quota tiers`
**Walk wave:** CCCC

## Gating dependency

Quota tiers (Free / Pro / Enterprise / Academic) need:

- Plans + entitlements table (gates on S1192-S1195 multi-tenant)
- Rate limiter that reads per-key quota from the key issuance layer (S1231)
- Overage policy decision (S1257)
- Pricing approval (Ray + Warren sign-off)

## Surrogate-now infra

- **Tiering doc precedent**: `docs/GRIMBANEWS_API_ACADEMIC_TIER_PLAN.md` already scopes the academic tier
- **`AdvertiserLeadController`** — per-IP `RateLimiter` pattern that the per-key limiter would mirror
- **`config/grimba_quotas.php`** — proposed shape: `['free' => ['rpm' => 60, 'rpd' => 5000], 'pro' => ['rpm' => 600, 'rpd' => 100000], ...]`

## Honest framing

Cheap to ship the *config*; expensive to ship the *enforcement* (needs key issuance + middleware). Surrogate today: the academic-tier doc already pins one of four tiers; the same author can ship the full matrix in a 1-week deferred sprint.

## Owners

- **Strategy:** Ray Dalio — unit-economics per tier
- **Finance:** Warren Buffett — pricing approval
- **Product:** Liam Smith — feature-gate matrix per tier
- **Backend:** Rajesh Kumar — middleware
- **Audit panel:** Zen / Echo / Mnemo at PR time

## Cross-references

- Sprint plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1256 row)
- Academic tier: `docs/GRIMBANEWS_API_ACADEMIC_TIER_PLAN.md`
- API overage: S1257 (deferred)
- Roster: `/Users/vb/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
