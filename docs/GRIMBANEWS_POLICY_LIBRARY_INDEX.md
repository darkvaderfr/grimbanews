# GrimbaNews — Policy Library Index (v0)

**Status:** policy library v0 (indexed; per-policy doc deferred to per-policy owners)
**Owner:** Sara Chen (CISO)
**Walks:** Mythos S1825 (ISO 27001 policy library) deferred → partial
**Gating dependency:** Per-policy doc drafting is the next step; many policies are partly captured in CLAUDE.md + Iboga memory + this repo's existing docs. This index consolidates the surface so auditors / new hires don't archaeology-hunt for each policy.

## Why this exists

S1825 was honest-deferred with the note "no policy library shipped; CLAUDE.md is the closest 'acceptable-use-by-Claude' surrogate." That's true but incomplete — the *index* of policies (where each one currently lives, what's missing) is operator-side work that doesn't depend on the audit. Shipping this index makes the gap visible and gives each future per-policy doc a parent.

## Index of policies

| Policy | Status | Current location / surrogate | Owner | Next step |
|---|---|---|---|---|
| Information Security Policy (master) | Stub | This document + `docs/GRIMBANEWS_SOC2_CONTROL_MAP.md` | Sara Chen | Draft standalone doc per ISO 27001 A.5.1.1 |
| Acceptable Use Policy (AUP) | Partial | `~/.claude/projects/-Users-vb-kaizen/memory/CLAUDE.md` Global section (Claude AUP) + Iboga staff handbook (TBD) | Sara Chen + Vader | Extract operator-facing AUP doc |
| Access Control Policy | Partial | Botble ACL package config + admin user-mgmt | Sara Chen | Document RBAC matrix |
| Cryptography Policy | Partial | HSTS-enforced HTTPS + bcrypt member passwords (Laravel default) + HMAC-SHA256 ip-hash | Sara Chen | Document key-management practice |
| Physical Security Policy | Delegated | VPS vendor's attested controls per `docs/GRIMBANEWS_VENDOR_REGISTER.md` vendor #9 | Sara Chen | Document delegation chain |
| Operations Security Policy | Partial | `docs/GRIMBANEWS_INCIDENT_RESPONSE_RUNBOOK.md`, `docs/GRIMBANEWS_PROD_DEDUPE_APPLY_2026_05_11.md`, `docs/GRIMBANEWS_PROD_DISK_HEADROOM_2026_05_11.md` | Jacob Lee | Consolidate into ops policy doc |
| Communications Security Policy | Partial | `GrimbaSecurityHeaders` middleware (CSP/HSTS/nosniff/frame/referrer/permissions) | Sara Chen | Document network-segmentation chain |
| System Acquisition / Dev / Maintenance Policy | Partial | CLAUDE.md git policy + dream-team audit cadence + release-smoke gate | Sara Chen + Jacob Lee | Document SDLC formally |
| Supplier Relationships Policy | Partial | `docs/GRIMBANEWS_VENDOR_REGISTER.md` | Sara Chen + Ray Dalio | DPA collection per S1873 |
| Information Security Incident Management Policy | Shipped | `docs/GRIMBANEWS_INCIDENT_RESPONSE_RUNBOOK.md` + `docs/GRIMBANEWS_ESCALATION_TIERS.md` + `docs/GRIMBANEWS_INCIDENT_COMMS_TEMPLATES.md` | Sara Chen + Jacob Lee | Add post-mortem template |
| Business Continuity / DR Policy | Partial | `docs/GRIMBANEWS_RTO_RPO_DEFINITION.md` (when written) + backup-verify cron | Jacob Lee + Larry Ellison | Drill cadence per S1946 |
| Compliance Policy | Partial | `docs/GRIMBANEWS_SOC2_CONTROL_MAP.md`, `docs/GRIMBANEWS_GDPR_ROPA.md`, `docs/GRIMBANEWS_PCI_DSS_SCOPE_STATEMENT.md` | Sara Chen | Add per-regulation policy excerpts |
| Privacy Policy (public-facing) | Shipped | `/politique-de-confidentialite` (FR), `/privacy-policy` (EN) | Lucy Leai + Sara Chen | Per-locale variants per S1147 |
| Cookie Policy | Partial | `platform/themes/echo/partials/cookie-consent.blade.php` + `/cookie-consent/{accept\|reject}` endpoints | Sara Chen + Lucy Leai | Per-category granularity per S1862 |
| Data Retention Policy | Partial | `GrimbaArchiveVaultEvents` (vault), `GrimbaPruneReleaseEvidence` (30d), `GrimbaPruneImageProxyCache`, `GrimbaDatabaseBackups` retention rotation | Sara Chen + Larry Ellison | Standalone retention doc |
| Editorial / Content Policy | Partial | `docs/GRIMBANEWS_AFRICA_INTERNATIONAL_EDITORIAL_PIVOT.md`, `docs/GRIMBANEWS_LANGUAGE_SPRINT_PLAN.md`, source classifier rules | Lucy Leai + Steve Jobs | Editorial workflow per S1291-S1300 |
| Code of Conduct (community) | Deferred | Contributor Covenant 2.1 — to ship with OSS methodology repo per S2051 | TBD (community manager) | After S2043 |
| Whistleblower / Ombudsman Policy | Deferred | `docs/GRIMBANEWS_OMBUDSMAN_CHARTER_DRAFT.md` | Vader + counsel | Ombudsman hire per S2022 |
| Vendor Risk Policy | Shipped | `docs/GRIMBANEWS_VENDOR_REGISTER.md` | Sara Chen | Quarterly review per S1878 |

## Drafting order

When Sara Chen has bandwidth to extract standalone per-policy docs, recommended order (highest auditor-impact first):

1. Master Information Security Policy (parent doc).
2. Acceptable Use Policy (operator-facing, today's gap).
3. Access Control Policy (RBAC matrix).
4. Operations Security Policy (consolidate the runbook trio).
5. Data Retention Policy (consolidates the 4 prune/archive crons + DB backup rotation).
6. System Acquisition / Dev / Maintenance Policy (consolidates SDLC + audit + release-smoke).

## Cross-references

- Master plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1825 row; dependency for S1822 Statement of Applicability)
- ISMS scope statement (parent): `docs/GRIMBANEWS_ISMS_SCOPE.md`
- Existing runbook trio: `docs/GRIMBANEWS_INCIDENT_RESPONSE_RUNBOOK.md`, `docs/GRIMBANEWS_PROD_DEDUPE_APPLY_2026_05_11.md`, `docs/GRIMBANEWS_PROD_DISK_HEADROOM_2026_05_11.md`
- CLAUDE.md global AUP (Claude-specific): `~/.claude/projects/-Users-vb-kaizen/memory/CLAUDE.md`
- Sister compliance docs: `docs/GRIMBANEWS_SOC2_CONTROL_MAP.md`, `docs/GRIMBANEWS_GDPR_ROPA.md`, `docs/GRIMBANEWS_PCI_DSS_SCOPE_STATEMENT.md`, `docs/GRIMBANEWS_VENDOR_REGISTER.md`
