# GrimbaNews — Internal Audit Retrospective (Template + Cadence)

**Status:** plan v0
**Owner:** Sara Chen (CISO) + Audit Committee
**Walks:** Mythos S1890 (internal-audit retrospective) deferred → partial
**Gating dependency:** Internal audit launch (SUB-54, S1889) — retro happens after at least one audit cycle completes.

## Why this exists

The audit program itself needs to be audited. Without periodic retrospectives, the audit team can drift into rubber-stamping, miss systemic issues, or burn out from poorly-scoped audits. The retro is the program's own continuous-improvement loop, closing the S1881-S1890 band cleanly.

## Per-retro cadence

- **Per-audit retro** (~30 min): immediately after each audit closes. Lightweight: what went well, what didn't, one improvement to try next time.
- **Per-year retro** (~2 hours): annually in December, before the next year's plan is approved. Full review across all audits + findings + corrective actions.

## Per-retro template (per-audit version)

```markdown
# Audit Retrospective — <audit topic> — YYYY-Qn

## Outcomes
- Findings count by severity:
- CAs spawned:
- Person-days spent vs estimate:

## What went well
- (1-3 bullets)

## What didn't
- (1-3 bullets)

## Improvement to try next audit
- One concrete change to scope, method, sampling, or tooling.

## Carry-forward items
- (Anything to track outside this audit's findings register.)
```

## Per-retro template (annual version)

Adds:
- Year-over-year metric trends (audits completed, findings closure rate, SLA breaches).
- Audit team capacity assessment (burnout risk, skill gaps).
- Tool/process changes accumulated through the year.
- Stakeholder feedback (management, audit committee, audited teams).
- Recommendations for next year's plan + budget.

## Per-retro participants

- Per-audit: Lead Internal Auditor + Technical Auditor + (optionally) audited-team rep.
- Per-year: All internal audit team + CISO + Audit Committee Chair.

## Per-retro output

- Retro notes archived in `/admin/grimba/audit-retros/YYYY-Qn/`.
- Improvement items → tracked as small finding-register entries (low severity, internal-audit-program scope).
- Annual retro → input pack to first management review of new year (per SUB-54, S1887).

## Per-retro psychological safety

The retro is not a performance review. It is a process-improvement venue. Specific norms:
- "Failures" framed as system issues, not individual ones.
- Blameless language enforced ("what conditions led to this" not "why did Alice do this").
- Auditors encouraged to flag their own mistakes first — modelled by Sara Chen as Lead.

## Cross-references

Master plan: S1890. Closes S1881-S1890 internal-audit band's improvement loop. Sister: `docs/GRIMBANEWS_INTERNAL_AUDIT_LAUNCH_READINESS.md` (S1889), all SUB-52/53/54 internal-audit docs.
