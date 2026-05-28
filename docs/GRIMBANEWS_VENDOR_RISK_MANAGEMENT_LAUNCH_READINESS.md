# GrimbaNews — Vendor Risk Management Launch Readiness

**Status:** plan v0
**Owner:** Sara Chen (CISO) + Lucy Leai (CEO sponsor)
**Walks:** Mythos S1880 (vendor risk-management launch readiness) deferred → partial
**Gating dependency:** Wave LLL (vendor register), SUB-50 (tier + questionnaire), SUB-51 (attestations + clauses), this wave SUB-52 (quarterly cadence).

## Why this exists

We have shipped the building blocks of a vendor risk-management program across waves LLL → SUB-52. This doc is the **launch checklist**: the moment the program is "running" rather than "planned." It also serves as the SOC 2 Trust Services Criterion CC9.2 (vendor management) evidence pointer.

## Per-component readiness check

| Component | Wave | Status | Owner |
|---|---|---|---|
| Vendor register schema + intake | LLL | partial (doc + plan) | Rajesh Kumar |
| Risk-tier classification policy | SUB-50 | partial | Sara Chen |
| Per-tier security questionnaire | SUB-50 | partial | Sara Chen |
| SOC 2 / ISO attestation collection | SUB-51 | partial | Sara Chen |
| Incident-notification clauses | SUB-51 | partial | Sara Chen + Michael O'Connor |
| Termination + data-return clauses | SUB-51 | partial | Sara Chen + Michael O'Connor |
| Quarterly review cadence | SUB-52 | partial | Sara Chen |

## Per-launch gating items (Vader sign-off required)

- [ ] Schema migration approved + applied for `vendors`, `vendor_attestations`, `vendor_review_records` tables.
- [ ] `/admin/grimba/vendors` UI surface built (intake form, tier picker, attestation upload).
- [ ] First pass: all currently-in-use vendors backfilled into register with tier assignment.
- [ ] First pass: critical-tier vendor attestations collected + filed.
- [ ] First pass: addenda inserted into critical-tier MSAs (incident + termination clauses).
- [ ] Q# review meeting scheduled on Lucy + Sara calendars.
- [ ] Backup CISO designated (succession plan per separate Wave).
- [ ] SOC 2 control narrative CC9.2 references all of the above.

## Per-launch announcement

Once gating items clear:
- Internal: post in #grimba-ops + email exec team.
- External: optional trust-center update on /docs/trust noting "Vendor management program in operation as of YYYY-MM-DD."

## Per-launch metrics (steady-state)

Tracked on `/admin/grimba/vendor-metrics`:
- Total vendors per tier.
- % of critical/high vendors with current attestation.
- % of critical/high MSAs with required clauses.
- Quarterly review completion rate (target: 100%).
- Mean time from vendor incident notification to GrimbaNews-side acknowledgement.

## Cross-references

Master plan: S1880. Closes the S1871-S1880 vendor band. Sister: `docs/GRIMBANEWS_VENDOR_QUARTERLY_REVIEW_CADENCE.md` (SUB-52), all SUB-51 + SUB-50 + LLL vendor docs.
