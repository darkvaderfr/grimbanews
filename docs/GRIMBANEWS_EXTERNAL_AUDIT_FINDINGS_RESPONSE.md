# GrimbaNews — External Audit Findings Response

**Status:** plan v0
**Owner:** Sara Chen (CISO) + Lucy Leai (CEO) + Michael O'Connor (Legal)
**Walks:** Mythos S1895 (external-audit findings response) deferred → partial
**Gating dependency:** Fieldwork closes + draft report received (Wave SUB-56 sister, S1894).

## Why this exists

When the external auditor's draft report identifies exceptions, our written management response sits in the final report alongside the auditor's observations forever. A poor response damages customer trust; a precise response demonstrates maturity. This doc walks how we craft those responses.

## Per-finding response template

```markdown
**Finding ref:** <auditor's reference>
**Control affected:** <SOC 2 CC / ISO clause reference>
**Auditor observation:** <quote or paraphrase>

**Management response:**
We acknowledge the observation. <Brief context for why the gap existed.>
Remediation actions:
1. <Specific action> — Owner: <name> — Target date: YYYY-MM-DD.
2. <Specific action> — Owner: <name> — Target date: YYYY-MM-DD.
Compensating control during remediation: <what mitigates risk in the interim>.
Verification: <how we will confirm remediation completed>.
```

## Per-response governance

Each draft response reviewed by:
- CISO (technical accuracy).
- CEO (tone, brand impact).
- Legal (any contractual or regulatory implications).
- Audit Committee Chair (alignment with internal audit program).

Final response signed by CEO + CISO before submission to auditor.

## Per-response tone guidelines

- Acknowledge without over-explaining (no "well actually" energy).
- Be specific about remediation (no "we will look into this").
- Be realistic on timelines (slipped target dates undermine credibility).
- Avoid blaming individuals.
- Avoid blaming the auditor's methodology.

## Per-finding triage

Before crafting a response, decide internally:
- **Agree fully** — response acknowledges + commits to remediation.
- **Agree with reframing** — response acknowledges core observation but corrects auditor's framing.
- **Disagree** — escalate to engagement partner; provide additional evidence; if still disagreed, request the finding be reclassified or removed.

Disagreement should be rare. If it happens, escalate early in draft-report phase, not after final.

## Per-finding follow-through

Each acknowledged remediation becomes:
- A finding-register entry (per SUB-53, S1885).
- A corrective-action entry (per SUB-54, S1886).
- A tracked item in subsequent quarterly internal-audit reports until verified closed.
- Evidence for next year's bridge letter (if applicable).

## Per-clean-report scenario

If no findings:
- A short congratulatory note in the next management review minutes.
- Public communication on trust center carefully calibrated ("SOC 2 Type II issued with no exceptions" is acceptable but should not overclaim).

## Cross-references

Master plan: S1895. Sister: `docs/GRIMBANEWS_EXTERNAL_AUDIT_FIELDWORK.md` (S1894), `docs/GRIMBANEWS_EXTERNAL_AUDIT_REPORT_PUBLICATION.md` (S1896 planned next), internal audit findings register (SUB-53, S1885).
