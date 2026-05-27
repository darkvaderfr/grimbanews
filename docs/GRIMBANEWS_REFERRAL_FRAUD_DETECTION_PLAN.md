# GrimbaNews — Referral Fraud Detection Plan

**Sprint ID:** S1955
**Status:** deferred → partial via surrogate documentation
**Master plan row:** `docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1951-s1960 — Referral fraud detection`
**Walk wave:** BBBB

## Gating dependency

Fraud detection on referrals needs:

- Live referral data (S1951-S1954, all deferred)
- Heuristics: same IP cluster, same device fingerprint, sub-min-cohort time between signup + conversion, disposable-email-domain check
- A `referral_flags` table (`attribution_id`, `flag_reason`, `auto_or_manual`, `decision_at`)
- A review queue UI for ops
- Per-flag remediation (revoke reward, dock referrer, ban abuser)
- Disclosure to affected reader if reward revoked

## Surrogate-now infra

- **Botble's existing IP rate limit on `/register`** — partial signup-abuse guard
- **`grimba_security_headers` middleware** — basic abuse hardening
- **Manual ops review** — until fraud detection exists, ops would review attributions by hand

## Honest framing

Fraud detection is mandatory for any referral with monetary reward. Deferred because reward issuance (S1954) is deferred — no money in motion means nothing to defraud yet.

## Owners

- **CISO:** Sara Chen — fraud heuristic policy
- **Backend:** Rajesh Kumar — flag schema + heuristic engine
- **Compliance:** Maya Patel — disclosure rules
- **Ops:** Ethan Wilson — review-queue workflow
- **Audit panel:** Zen / Echo / Mnemo at PR time

## Cross-references

- Sprint plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1955 row)
- Attribution: `docs/GRIMBANEWS_REFERRAL_ATTRIBUTION_TRACKING_DESIGN.md`
- Leaderboard (gaming-vector source): `docs/GRIMBANEWS_REFERRAL_LEADERBOARD_DESIGN.md`
- Roster: `/Users/vb/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
