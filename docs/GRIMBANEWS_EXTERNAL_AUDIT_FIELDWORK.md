# GrimbaNews — External Audit Fieldwork Support

**Status:** plan v0
**Owner:** Sara Chen (CISO) — fieldwork SPOC; backed by control owners across the org
**Walks:** Mythos S1894 (external-audit fieldwork) deferred → partial
**Gating dependency:** Kickoff complete (Wave SUB-56 sister, S1893); PBC list delivered.

## Why this exists

Fieldwork is where the audit actually happens — interviews, sampling, evidence inspection, exception investigation. Our preparedness directly determines: (a) how many days fieldwork takes, (b) how many findings are raised, (c) how many of those findings get reclassified or dismissed during write-up.

## Per-week cadence during fieldwork

| Day | Activity |
|---|---|
| Monday | Status meeting (30 min): outstanding evidence requests, this week's interview schedule, blockers. |
| Tues-Thu | Interviews + walkthroughs (calendar-driven; auditor leads). |
| Friday | Evidence-request backlog clearance + auditor preliminary observations review. |

## Per-evidence-request workflow

1. Auditor posts request in portal.
2. CISO triages within 2 business days.
3. Control owner pulls evidence (with CISO support if process or tool unclear).
4. Evidence reviewed by CISO before upload (avoid PII leakage, over-disclosure).
5. Evidence uploaded with metadata: control mapped, time period, sampling justification.
6. Auditor acknowledges receipt; clarifying questions answered within 2 days.

## Per-interview prep

For each interview:
- Control owner briefed on scope by CISO ahead of time.
- Owner walks through their process; auditor probes.
- Owner does not improvise — refers to runbook / policy / register where it exists.
- "I don't know — let me check and follow up" is encouraged over guessing.
- CISO sits in on high-stakes interviews (CEO, CISO own, Finance, Legal).

## Per-evidence types we expect

- Per-control policy text (versioned).
- Per-control procedure documentation (runbooks).
- Per-control register snapshots (access list, vendor list, risk register).
- Per-control log samples + queries used to extract them.
- Per-control screenshots (timestamped, scoped).
- Per-control system configuration exports.
- Per-control training records.
- Per-control evidence of management review / approval.

## Per-exception handling

When the auditor identifies a potential exception:
- Per-exception we acknowledge or push back within 2 business days.
- Per-exception we provide additional evidence if it changes the picture.
- Per-exception we agree on classification (in-flight vs design vs operating).
- Per-exception we draft management response in parallel with auditor write-up.

## Per-fieldwork end signal

Fieldwork closes when:
- All PBC items have been delivered + acknowledged.
- All interviews completed.
- All sampling completed.
- Auditor has no outstanding clarifying questions.
- Auditor signals draft-report writing begins.

## Cross-references

Master plan: S1894. Sister: `docs/GRIMBANEWS_EXTERNAL_AUDIT_KICKOFF.md` (S1893), `docs/GRIMBANEWS_EXTERNAL_AUDIT_FINDINGS_RESPONSE.md` (S1895 next).
