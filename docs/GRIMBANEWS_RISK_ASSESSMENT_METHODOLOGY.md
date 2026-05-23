# GrimbaNews — Risk Assessment Methodology (v0)

**Status:** methodology v0 (ready to run a first iteration)
**Owner:** Sara Chen (CISO)
**Walks:** Mythos S1831 (risk-assessment methodology definition) deferred → partial
**Gating dependency:** None to define the methodology; a first iteration of the register (S1832-S1837) follows on adoption. The existing pre-launch register at `docs/GRIMBANEWS_S010_UNRESOLVED_RISK_REGISTER.md` will be re-scored under this methodology to seed the ISO-conformant register.

## Why this exists

S1831 was honest-deferred as "no methodology doc; ISO 27005 / NIST 800-30 candidates." Picking a methodology and writing it down is a paper exercise that does not need an external auditor. This doc picks **ISO 27005-lite** (a simplified single-team variant of ISO/IEC 27005:2022) and defines the scoring rubric. Once adopted, the existing pre-launch register can be re-mapped into this scoring frame and the first iteration of S1832-S1837 becomes tractable in a single sitting.

## Methodology choice

**ISO 27005-lite** — qualitative, single-team workshop-driven.

Why this over alternatives:

- **NIST 800-30** — more government-flavored; heavier than we need for a single product.
- **FAIR** — quantitative; requires loss-event-frequency data we don't have pre-launch.
- **OCTAVE** — heavyweight workshop process; same as NIST issue.

**ISO 27005-lite** is the right fit for a 1-product / 1-team / pre-launch state. Once we have ≥1 year of operational data + Sentry metrics (S1013), we can upgrade individual high-severity risks to FAIR-style quantitative analysis.

## Scoring rubric

### Impact (1-5 scale)

| Score | Label | Definition |
|---|---|---|
| 1 | Negligible | Cosmetic; reader unaware; recoverable in <1h |
| 2 | Minor | Single feature degraded; recoverable in <1 day; ≤10 readers visibly affected |
| 3 | Moderate | One critical surface down for <4h; brand impact bounded; recoverable from last backup |
| 4 | Major | Outage >4h or partial data loss; visible to all readers; press-noticeable |
| 5 | Severe | Full breach or multi-day outage or full data loss; regulatory reportable per RGPD Article 33 |

### Likelihood (1-5 scale)

| Score | Label | Definition |
|---|---|---|
| 1 | Rare | <1 per 5 years |
| 2 | Unlikely | <1 per year |
| 3 | Possible | Once or twice per year |
| 4 | Likely | Quarterly |
| 5 | Almost certain | Monthly or more |

### Risk score = Impact × Likelihood

| Range | Tier | Required action |
|---|---|---|
| 1-4 | Low | Accept; document; monitor |
| 5-9 | Medium | Mitigate within current quarter; risk-register row |
| 10-14 | High | Mitigate within current sprint; risk-register row + Sara Chen + Vader sign-off |
| 15-25 | Critical | Stop-the-line; mitigate within 48h; immediate Tier 3/Tier 4 escalation per `docs/GRIMBANEWS_ESCALATION_TIERS.md` |

## Treatment decisions

For each risk:

1. **Avoid** — remove the asset / process that creates the risk (e.g. don't integrate Stripe → avoid PCI DSS scope per S1841).
2. **Mitigate** — add a control to reduce likelihood or impact.
3. **Transfer** — insurance, contractual liability shift to vendor.
4. **Accept** — risk owner + Sara Chen sign off in writing.

Choice is documented in the register row with the rationale.

## Process

### Iteration cadence

- **Initial iteration:** within 30 days of methodology adoption. Sara Chen leads workshop; Jacob Lee, Hannah Kim, Larry Ellison, Lucy Leai attend.
- **Quarterly refresh:** Q1, Q2, Q3, Q4. Each open risk re-scored; new risks added; closed risks archived.
- **Trigger refresh:** any incident (per `docs/GRIMBANEWS_INCIDENT_RESPONSE_RUNBOOK.md` Phase 5) adds risks discovered during root-cause analysis.

### Workshop format

1. Asset enumeration (5 min) — pull from ISMS scope (S1821 / `docs/GRIMBANEWS_ISMS_SCOPE.md`).
2. Threat brainstorming per asset (30 min) — STRIDE prompts (Spoofing / Tampering / Repudiation / Information disclosure / Denial of service / Elevation of privilege).
3. Vulnerability mapping (15 min) — what existing controls address each threat; what gaps exist.
4. Impact + Likelihood scoring (30 min) — group consensus, dissent recorded.
5. Treatment decision (15 min) — per risk.
6. Register update (15 min) — append/update rows in `docs/GRIMBANEWS_S010_UNRESOLVED_RISK_REGISTER.md`.

Total: ~2 hours.

### Output

- Register update.
- Workshop notes archived as `docs/risk-workshops/YYYY-MM-DD.md` (directory does not yet exist; create on first workshop).
- Action items (new mitigations) added to the sprint queue.

## Seed: re-scoring the existing pre-launch register

The current pre-launch register at `docs/GRIMBANEWS_S010_UNRESOLVED_RISK_REGISTER.md` was assembled ad-hoc per S010 and predates this methodology. First workshop should:

1. Walk every existing row.
2. Apply the I×L scoring rubric above.
3. Apply a treatment decision.
4. Promote each row from "unresolved risk" prose to a structured register row.

## Cross-references

- Master plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1831 row; gates for S1832-S1840)
- ISMS scope (parent): `docs/GRIMBANEWS_ISMS_SCOPE.md`
- Existing risk register (input to seed re-scoring): `docs/GRIMBANEWS_S010_UNRESOLVED_RISK_REGISTER.md`
- Escalation tiers (consumed for critical-tier action): `docs/GRIMBANEWS_ESCALATION_TIERS.md`
- IR runbook (Phase 5 feeds back into register): `docs/GRIMBANEWS_INCIDENT_RESPONSE_RUNBOOK.md`
- Standard references: ISO/IEC 27005:2022 (Information security risk management), NIST SP 800-30 Rev. 1 (Risk Assessment Guide), STRIDE threat model
