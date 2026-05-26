# GrimbaNews — Complaint Triage Rubric Plan

**Status:** plan v0 (no rubric; operator triages ad hoc)
**Owner:** Lucy Leai (CEO) + Ombudsman (S2022 — not hired) + Sara Chen on severity scoring
**Walks:** Mythos S2026 (Complaint triage workflow — severity rubric) deferred → partial
**Gating dependency:** Ombudsman hire (S2022) + editorial-board sign-off.

## Why this exists

S2026 standardizes how complaints are scored so SLAs and escalation tiers are deterministic — not operator-mood-dependent.

## Today's surrogate

- Vader manually decides. Inconsistent.

## Severity rubric

| Tier | Definition | SLA | Response |
|---|---|---|---|
| P0 — Critical | Risk to life/safety, defamation suit threatened, ongoing harm | 24h ack, 7d decision | Ombudsman + Lucy + counsel immediately |
| P1 — High | Material factual error, source-rights challenge, journalist conduct | 48h ack, 14d decision | Ombudsman + editor-in-chief |
| P2 — Standard | Editorial disagreement, framing complaint, missing context | 7d ack, 30d decision | Assigned editor with ombudsman review |
| P3 — Low | Style nit, typo, broken link | 7d ack, when-time-permits | Tech writer / next-deploy |

## Routing matrix

- Defamation / legal threat → P0 → counsel-loop required
- Source license challenge → P1 → routed to `source_license_challenges` log (S2005)
- DMCA → not via this rubric — routed to `takedown_requests` (S2003)
- Generic editorial → triage owner's call

## Anti-triage-fatigue rules

- Same complainant >5 P3 in 30 days → auto-bundle into single P2.
- Anonymous + low-detail → P3 default unless content signals higher.

## Audit

- Every triage decision logged with reasoning to `ombudsman_investigations` (S2027).
- Quarterly audit by Maya Patel.

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S2026)
- Sister docs: `docs/GRIMBANEWS_OMBUDSMAN_INTAKE_PAGE_SCOPE.md`, `docs/GRIMBANEWS_INVESTIGATION_LOG_SCHEMA.md`, `docs/GRIMBANEWS_COMPLAINT_PUBLIC_FINDINGS_PUBLICATION_PLAN.md`, `docs/GRIMBANEWS_DISPUTE_ESCALATION_WORKFLOW.md`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
