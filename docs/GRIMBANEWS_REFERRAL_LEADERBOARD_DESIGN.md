# GrimbaNews — Referral Leaderboard Design

**Sprint ID:** S1958
**Status:** deferred → partial via surrogate documentation
**Master plan row:** `docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1951-s1960 — Referral leaderboard`
**Walk wave:** BBBB

## Gating dependency

A public leaderboard adds gamification on top of attribution tracking (S1953). Needs:

- Aggregator: `SELECT referrer_member_id, COUNT(*) FROM referral_attributions WHERE converted_at IS NOT NULL GROUP BY 1 ORDER BY 2 DESC LIMIT 100`
- Per-member display-name opt-in (anonymous leaderboard otherwise)
- Anti-gaming guard (correlated with fraud detection S1955)
- Reset cadence (weekly / monthly / all-time)
- A `/parrainages/classement` route (FR) / `/referrals/leaderboard` (EN)
- i18n catalog

## Surrogate-now infra

- **None** — leaderboard requires per-member attribution data which does not exist
- **Editorial author byline** — closest analog is the (also-deferred) per-author leaderboard concept

## Honest framing

Gamification is downstream of attribution. Optional even after S1953 ships — some publishers deliberately skip leaderboards to avoid race-to-the-bottom share-spam behavior.

## Owners

- **Product:** Liam Smith — leaderboard policy (opt-in display, cadence)
- **CMO:** Gary Vaynerchuk — gamification psychology
- **Backend:** Rajesh Kumar — aggregator query + cache
- **Frontend:** Nina Patel — leaderboard partial
- **CISO:** Sara Chen — anti-spam guards
- **Audit panel:** Zen / Echo / Mnemo at PR time

## Cross-references

- Sprint plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1958 row)
- Program design: `docs/GRIMBANEWS_REFERRAL_PROGRAM_TIER_DESIGN.md`
- Dashboard sister: `docs/GRIMBANEWS_REFERRAL_PER_MEMBER_DASHBOARD_DESIGN.md`
- Fraud detection: `docs/GRIMBANEWS_REFERRAL_FRAUD_DETECTION_PLAN.md`
- Roster: `/Users/vb/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
