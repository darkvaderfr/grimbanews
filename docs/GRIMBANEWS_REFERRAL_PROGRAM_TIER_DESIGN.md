# GrimbaNews — Referral Program Design (Referrer / Referee Reward)

**Sprint ID:** S1951
**Status:** deferred → partial via surrogate documentation
**Master plan row:** `docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1951-s1960 — Referral program design (referrer reward / referee reward)`
**Walk wave:** BBBB

## Gating dependency

A two-sided referral program needs:

- `members.referral_code` column (S1952, deferred)
- Referral attribution tracking (S1953, deferred)
- Reward issuance — subscription discount or free tier (S1954, gated on S1211 monetization)
- Fraud detection layer (S1955, deferred)
- Per-member referral dashboard (S1957, deferred)
- Leaderboard (S1958, deferred — gamification surface)

All six gating sprints deferred. Foundation gate: monetization S1211 not live.

## Surrogate-now infra

- **Word-of-mouth via newsletter** — readers can fwd newsletters today; no attribution but real distribution
- **Per-article share buttons** — already shipped via `<x-grimba-share-buttons>` partial (Twitter/X / FB / LinkedIn / email / copy-link)
- **`/methodologie` social-proof section** — anchors the "tell a friend" call without code-level tracking

## Honest framing

Referral programs without billing infrastructure are content-marketing programs. The 6 sub-sprints all chain to S1211 monetization activation. Designing the program now (Lucy-CEO + Ray-CFO motion) is the actionable next step; coding it gates on activation.

## Owners

- **CEO:** Lucy Leai — program scope + brand fit
- **CFO:** Ray Dalio + Warren Buffett — reward economics
- **CMO:** Gary Vaynerchuk — incentive psychology + launch comms
- **Growth:** Maria Lopez — viral-coefficient targets
- **Backend:** Rajesh Kumar — referral_code + attribution schema
- **Frontend:** Nina Patel — per-member dashboard
- **CISO:** Sara Chen — fraud / abuse detection
- **Audit panel:** Zen / Echo / Mnemo at PR time

## Cross-references

- Sprint plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1951 row)
- Referral-code generation: `docs/GRIMBANEWS_REFERRAL_CODE_GENERATION_DESIGN.md`
- Referral attribution: `docs/GRIMBANEWS_REFERRAL_ATTRIBUTION_TRACKING_DESIGN.md`
- Existing affiliate plan: `docs/GRIMBANEWS_AFFILIATE_REFERRAL_PROGRAM_PLAN.md` (overlap; sibling scope)
- Roster: `/Users/vb/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
