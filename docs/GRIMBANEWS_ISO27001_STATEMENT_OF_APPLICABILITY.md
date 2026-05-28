# GrimbaNews — ISO 27001 Statement of Applicability (Annex A Controls)

**Status:** plan v0
**Owner:** Sara Chen (CISO) + counsel + audit firm (when engaged)
**Walks:** Mythos S1822 (ISO 27001 SoA) deferred → partial
**Gating dependency:** ISO 27001 program kicked off + audit firm engaged.

## What this is

ISO 27001 requires a Statement of Applicability (SoA) listing all Annex A controls (93 in ISO 27001:2022) and per-control:
- Applicable yes / no.
- If yes: implementation status + reference.
- If no: rationale.

## Per-control template

```
| Control | Title | Applicable | Status | Reference | Rationale-if-N/A |
|---|---|---|---|---|---|
| A.5.1 | Information security policies | Yes | Implemented | docs/GRIMBANEWS_POLICY_LIBRARY_INDEX.md | n/a |
| A.5.2 | Information security roles + responsibilities | Yes | Implemented | docs/GRIMBANEWS_ISO27001_ISMS_RACI.md | n/a |
| ... | ... | ... | ... | ... | ... |
```

## Per-control coverage from prior Mythos work

Many Annex A controls already covered:
- A.5.x policy controls → Wave LLL `docs/GRIMBANEWS_POLICY_LIBRARY_INDEX.md`.
- A.6.x people controls → operator-side HR processes.
- A.7.x physical controls → cloud hosting; VPS-provider attestation.
- A.8.x technology controls → many Wave SUB-35 to SUB-38 SOC 2 mappings reusable.

## Per-non-applicable exclusion rationale

Per-N/A control: documented rationale (e.g., "We don't operate physical facilities; A.7 physical-security controls inherited from cloud provider").

## Per-quarter SoA review

- Per-quarter: Sara reviews per-control status updates.
- Per-incident: per-control status re-evaluation.

## Cross-references

Master plan: S1822. Sister: `docs/GRIMBANEWS_POLICY_LIBRARY_INDEX.md` (Wave LLL), `docs/GRIMBANEWS_ISMS_SCOPE.md` (Wave LLL).
