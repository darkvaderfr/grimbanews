# GrimbaNews — Internal Audit Launch Readiness

**Status:** plan v0
**Owner:** Sara Chen (CISO) + Lucy Leai (CEO sponsor)
**Walks:** Mythos S1889 (internal-audit launch readiness) deferred → partial
**Gating dependency:** S1881-S1888 — team composition (S1882), annual plan (S1883), working-paper template (S1884), findings register (S1885), corrective-action tracking (S1886), management-review cadence (S1887).

## Why this exists

This wave closes the S1881-S1890 internal-audit band with the launch checklist that puts the program from "designed" into "operating." Without this gate, individual components could ship in isolation while the program never actually runs.

## Per-component readiness check

| Component | Wave | Status | Verifier | Notes |
|---|---|---|---|---|
| Team composition + independence | SUB-52 (S1882) | partial | Audit Committee Chair | Sara Chen confirmed as Lead Internal Auditor; rotation matrix to be initialized. |
| Annual audit plan | SUB-53 (S1883) | partial | Audit Committee | First-year plan to be drafted + approved at first management review meeting. |
| Working-paper template | SUB-53 (S1884) | partial | Lead Internal Auditor | Template stored at `/admin/grimba/audit-papers/template.md`. |
| Findings register | SUB-53 (S1885) | partial | Audit Committee Chair | Schema + admin surface pending Vader migration approval. |
| Corrective-action tracking | SUB-54 (S1886) | partial | Audit Committee | Lifecycle defined; depends on findings register UI. |
| Management-review cadence | SUB-54 (S1887) | partial | CEO | First quarterly meeting to be scheduled. |
| Internal Audit Charter (formal) | (separate wave, planned) | not started | CEO sign-off | Required document; cited in S1882. |

## Per-launch gating items (Vader sign-off required)

- [ ] Audit Committee constituted by board resolution / CEO appointment letter.
- [ ] Internal Audit Charter signed by CEO + Audit Committee Chair.
- [ ] First annual audit plan approved at first management review.
- [ ] `/admin/grimba/audit-papers/` storage location provisioned with access controls.
- [ ] `/admin/grimba/audit-findings` admin surface built.
- [ ] First management review meeting scheduled on Lucy's calendar.
- [ ] First quarterly audit (suggest: Access Management) scoped + scheduled.
- [ ] Backup auditor designated (Sara's vacation / departure resilience).
- [ ] SOC 2 / ISO 27001 control narratives updated to reference S1882-S1887 docs.

## Per-launch announcement

Once gating items clear:
- Internal: post in #grimba-ops; brief at next all-hands.
- Trust center: add "Internal Audit Program in operation as of YYYY-MM-DD" to /docs/trust.
- Audit Committee Charter publishable (redacted) on trust center for transparency.

## Per-launch steady-state metrics

Tracked on `/admin/grimba/audit-metrics`:
- Audits completed vs planned (annual %).
- Findings closure rate by quarter.
- SLA-breached findings count.
- PIR pass rate.
- Management review attendance + on-time minute approval rate.

## Cross-references

Master plan: S1889. Closes the S1881-S1890 internal-audit band. Sister: all SUB-52/53/54 internal-audit docs.
