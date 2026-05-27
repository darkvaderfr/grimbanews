# GrimbaNews — Referral Attribution Tracking Design

**Sprint ID:** S1953
**Status:** deferred → partial via surrogate documentation
**Master plan row:** `docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1951-s1960 — Referral attribution tracking`
**Walk wave:** BBBB

## Gating dependency

Referral attribution needs:

- Middleware that reads `?ref={code}` and sets a `grimba_ref` cookie (configurable TTL, 30-90d typical)
- A `referral_attributions` table (`referrer_member_id`, `referee_member_id|cookie_hash`, `first_seen_at`, `converted_at`, `tier`)
- Conversion trigger (member registration + subscription event)
- DPIA update (S1855, deferred) — referral tracking is per-reader behavioral data
- Per-category consent (S1862, deferred) — attribution cookie likely requires "functional" or "analytics" tier consent
- Per-member dashboard (S1957, deferred)

None of this ships today.

## Surrogate-now infra

- **`grimba_ref` cookie via URL param** — could be wired in middleware in ~1 day; not currently set
- **UTM tagging** — readers can manually share `?utm_source=member` links; no aggregation today
- **Newsletter forwarding** — completely untracked

## Honest framing

Attribution tracking is privacy-sensitive — needs DPIA update before shipping. Cookie-based attribution is the standard pattern; per-member-fingerprint attribution is a non-starter.

## Owners

- **Backend:** Rajesh Kumar — middleware + ledger
- **Privacy:** Sara Chen (CISO) + Maya Patel — DPIA + consent
- **Data Eng:** Benjamin Lee — conversion aggregation
- **Product:** Liam Smith — attribution-window decision
- **Audit panel:** Zen / Echo / Mnemo at PR time

## Cross-references

- Sprint plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1953 row)
- Program design: `docs/GRIMBANEWS_REFERRAL_PROGRAM_TIER_DESIGN.md`
- Code generation: `docs/GRIMBANEWS_REFERRAL_CODE_GENERATION_DESIGN.md`
- Fraud detection: `docs/GRIMBANEWS_REFERRAL_FRAUD_DETECTION_PLAN.md`
- Roster: `/Users/vb/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
