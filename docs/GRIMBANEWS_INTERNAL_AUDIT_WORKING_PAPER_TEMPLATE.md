# GrimbaNews — Internal Audit Working-Paper Template

**Status:** plan v0
**Owner:** Sara Chen (CISO)
**Walks:** Mythos S1884 (internal-audit working-paper template) deferred → partial
**Gating dependency:** Internal audit plan (Wave SUB-53 sister, S1883).

## Why this exists

Working papers are the auditor's evidence file: what was tested, how, by whom, what was found. They're the artifact that lets an external SOC 2 auditor or ISO certification body see that internal audit happened in a defensible way. A consistent template makes them comparable across audits and reduces re-work.

## Template structure (markdown file per audit, stored in `/admin/grimba/audit-papers/YYYY/`)

```markdown
# Audit Working Paper — <audit topic> — YYYY-Qn

## Scope
- Topic:
- In-scope controls (cite from SOC 2 / ISO 27001 register):
- Out-of-scope:
- Audit period: YYYY-MM-DD to YYYY-MM-DD
- Authorized by: <Audit Committee meeting minute reference>

## Team
- Lead auditor:
- Technical auditor:
- Process auditor:
- Observed by:

## Methodology
- Population definition:
- Sample size + selection method (random / judgmental / 100%):
- Test procedures (per control):

## Evidence collected
For each test:
- Test ID:
- Control tested:
- Procedure performed:
- Evidence reference (screenshot / log query / interview note / config dump path):
- Result: PASS | FAIL | EXCEPTION-with-explanation
- Tester + date:

## Interview notes
- Interviewee + role:
- Date + duration:
- Key statements (verbatim where material):
- Cross-references to evidence:

## Findings summary
| ID | Severity | Control | Finding | Root cause | Recommendation |
|---|---|---|---|---|---|

## Management response
For each finding:
- Acknowledgement:
- Remediation owner:
- Target date:
- Compensating control during remediation:

## Closing
- Audit closed: YYYY-MM-DD
- Closing meeting attendees:
- Findings transferred to register (per S1885):
- Working paper archived at:
```

## Per-template integrity rules

- Per-paper read-only once audit closes (no edits, only addenda with timestamps).
- Per-paper retained 7 years minimum (SOC 2 audit window + buffer).
- Per-paper PII redaction reviewed before storage.
- Per-paper access restricted to Audit Committee + CISO.

## Per-quality-review process

After each audit, working paper reviewed by:
- Audit Committee Chair (Lucy Leai) for completeness.
- Lead Internal Auditor (Sara Chen) for technical accuracy.
- Sign-off recorded as a final section in the paper.

## Cross-references

Master plan: S1884. Sister: `docs/GRIMBANEWS_INTERNAL_AUDIT_PLAN_ANNUAL.md` (S1883), `docs/GRIMBANEWS_INTERNAL_AUDIT_FINDINGS_REGISTER.md` (S1885 next), `docs/GRIMBANEWS_INTERNAL_AUDIT_TEAM_COMPOSITION.md` (S1882).
