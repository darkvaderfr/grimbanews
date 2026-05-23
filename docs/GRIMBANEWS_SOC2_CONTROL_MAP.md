# GrimbaNews — SOC 2 Control Map (Trust Services Criteria → Shipped Surrogates)

**Status:** control inventory v1 (mapped to shipped code; auditor engagement deferred)
**Owner:** Sara Chen (CISO)
**Walks:** Mythos S1801 (SOC 2 control inventory) — already partial; this doc consolidates into one map. Also reinforces S1802-S1808 partials.
**Gating dependency:** No SOC 2 firm engaged (S1811-S1820). This document is the **TSC → implementation evidence map** that the firm will iterate from when engaged.

## Why this exists

S1801 was already marked `partial` in the master ledger because individual security headers / backup verifier / scheduler retention shipped, but no consolidated control map existed. This doc builds the map — each AICPA Trust Services Criterion (TSC) row points at GrimbaNews shipped code / tests / runbooks. This is exactly what a SOC 2 Type I auditor (Drata / Vanta / Tugboat Logic / Strike Graph) asks for in their initial inventory request.

## Coverage scope

This map covers the **Security** (Common Criteria CC1-CC9) and **Availability** (A1) TSC categories. Confidentiality, Processing Integrity, and Privacy are TSC categories we will scope-in if a customer-contract trigger requires them.

## Control map

### CC1 — Control Environment

| CC1 control | Implementation surrogate | Evidence pointer |
|---|---|---|
| CC1.1 Org commits to integrity + ethics | Iboga Ventures exec charter; CLAUDE.md global policies | `~/.claude/projects/-Users-vb-kaizen/memory/feedback_*.md` |
| CC1.2 Board oversight | Exec roster + 5-0 vote governance cadence | `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md` |
| CC1.3 Mgmt establishes structure | Discipline-owner assignments per Mythos band | Various `docs/GRIMBANEWS_MYTHOS_*.md` packs |
| CC1.4 Commits to competence | Zen/Echo/Mnemo audit panel per non-trivial change | `~/.claude/projects/-Users-vb-kaizen/memory/feedback_dream_team_audit.md` |
| CC1.5 Enforces accountability | Co-Authored-By trailer required + git-push-before-deploy policy | `~/.claude/projects/-Users-vb-kaizen/memory/feedback_darkvaderfr_git_mandatory.md` |

### CC2 — Communication and Information

| CC2 control | Implementation surrogate | Evidence pointer |
|---|---|---|
| CC2.1 Internal info quality | `grimba_automation_runs` ledger + cockpit board | `app/Support/GrimbaAutomationMonitor.php`, `/admin/grimba/cockpit` |
| CC2.2 Internal comms | Iboga ops channel + Slack equivalents | (private — operator side) |
| CC2.3 External comms | `/contact` page + status page plan | `App\Http\Controllers\GrimbaContactController`, `docs/GRIMBANEWS_STATUS_PAGE_PLAN.md` |

### CC3 — Risk Assessment

| CC3 control | Implementation surrogate | Evidence pointer |
|---|---|---|
| CC3.1 Risk identification | Pre-launch risk register | `docs/GRIMBANEWS_S010_UNRESOLVED_RISK_REGISTER.md` |
| CC3.2 Fraud risk | N/A — no payment processing per S1841 | `docs/GRIMBANEWS_PCI_DSS_SCOPE_STATEMENT.md` |
| CC3.3 Change risk | Release-smoke + release-evidence retention | `app/Console/Commands/GrimbaReleaseSmoke.php`, `app/Console/Commands/GrimbaPruneReleaseEvidence.php` |
| CC3.4 Risk-assessment methodology | Documented per S1831 | `docs/GRIMBANEWS_RISK_ASSESSMENT_METHODOLOGY.md` |

### CC4 — Monitoring Activities

| CC4 control | Implementation surrogate | Evidence pointer |
|---|---|---|
| CC4.1 Ongoing monitoring | `grimba:health --fail-on-risk` hourly | `app/Console/Commands/GrimbaHealth.php`, `routes/console.php:173-176` |
| CC4.2 Deficiency communication | Cockpit board surfaces failed/stale jobs | `resources/views/grimba-admin/cockpit.blade.php:190-231` |

### CC5 — Control Activities

| CC5 control | Implementation surrogate | Evidence pointer |
|---|---|---|
| CC5.1 Activity selection | Per-job `grimba_schedule_command()` wrapping | `routes/console.php` (22+ scheduled jobs) |
| CC5.2 Technology controls | `GrimbaSecurityHeaders` middleware + automated tests | `app/Http/Middleware/GrimbaSecurityHeaders.php`, `tests/Feature/SecurityHeadersTest.php` |
| CC5.3 Policies + procedures | Policy library | `docs/GRIMBANEWS_POLICY_LIBRARY_INDEX.md` |

### CC6 — Logical and Physical Access Controls

| CC6 control | Implementation surrogate | Evidence pointer |
|---|---|---|
| CC6.1 Access auth | Botble admin auth + member auth | `Botble\ACL` + `Botble\Member` packages |
| CC6.2 New-user provisioning | Admin-only via Botble user-mgmt | `/admin/users` |
| CC6.3 Access removal | Same — Botble admin user-mgmt | same |
| CC6.4 Restricted physical access | VPS provider physical security (vendor #9 per `docs/GRIMBANEWS_VENDOR_REGISTER.md`) | Hetzner/equivalent provider attestation |
| CC6.5 Data-discard | Backup rotation + GDPR erasure workflow per S1859 | `app/Console/Commands/GrimbaArchiveVaultEvents.php` |
| CC6.6 External-threat protection | CSP + nosniff + frame-options + referrer + permissions-policy + HSTS | `app/Http/Middleware/GrimbaSecurityHeaders.php` |
| CC6.7 Transmission integrity | HSTS forced HTTPS | `GrimbaSecurityHeaders::handle()` (HSTS on HTTPS) |
| CC6.8 Malware-prevention | Server-side input sanitization + admin upload validation | `app/Http/Middleware/*`, Botble validators |

### CC7 — System Operations

| CC7 control | Implementation surrogate | Evidence pointer |
|---|---|---|
| CC7.1 Detection (vulnerabilities) | composer audit (deferred to live — S939) + dependabot on darkvaderfr | GitHub repo settings |
| CC7.2 Anomaly detection | `grimba:health --fail-on-risk` SLO breaches | `app/Console/Commands/GrimbaHealth.php` |
| CC7.3 Incident response | IR runbook | `docs/GRIMBANEWS_INCIDENT_RESPONSE_RUNBOOK.md` |
| CC7.4 Incident recovery | Backup-verify + restore-smoke | `app/Console/Commands/GrimbaVerifyBackups.php`, `tests/Feature/DatabaseBackupVerificationTest.php` |
| CC7.5 Recovery testing | Restore-drill cadence — deferred per S1945 | `docs/GRIMBANEWS_RTO_RPO_DEFINITION.md` |

### CC8 — Change Management

| CC8 control | Implementation surrogate | Evidence pointer |
|---|---|---|
| CC8.1 Change auth | Git push to darkvaderfr → deploy gate | per `feedback_darkvaderfr_git_mandatory.md` |
| CC8.1 Change testing | `grimba:release-smoke` post-deploy + automated test suite | `app/Console/Commands/GrimbaReleaseSmoke.php`, `tests/` |
| CC8.1 Change docs | Per-release evidence files | `storage/app/grimba-release-evidence/` |

### CC9 — Risk Mitigation

| CC9 control | Implementation surrogate | Evidence pointer |
|---|---|---|
| CC9.1 Risk-mitigation activities | Sprint cadence per Mythos plan | `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` |
| CC9.2 Vendor risk | Vendor register | `docs/GRIMBANEWS_VENDOR_REGISTER.md` |

### A1 — Availability

| A1 control | Implementation surrogate | Evidence pointer |
|---|---|---|
| A1.1 Capacity planning | Disk-headroom playbook + image-proxy cache GC | `docs/GRIMBANEWS_PROD_DISK_HEADROOM_2026_05_11.md`, `app/Console/Commands/GrimbaPruneImageProxyCache.php` |
| A1.2 Environmental protections | VPS provider attestation (vendor #9) | per `docs/GRIMBANEWS_VENDOR_REGISTER.md` |
| A1.3 Recovery infrastructure | Backup-verify daily + restore-smoke | `app/Console/Commands/GrimbaVerifyBackups.php` |

## Auditor-engagement checklist (day-1 when SOC 2 firm engaged per S1811)

1. Walk auditor through this map.
2. Demo: cockpit board → failed/stale jobs.
3. Demo: `grimba:health --fail-on-risk` exit codes.
4. Demo: `grimba:verify-backups` daily output + DatabaseBackupVerificationTest.
5. Walk through security headers test (`tests/Feature/SecurityHeadersTest.php`).
6. Provide policy library index (`docs/GRIMBANEWS_POLICY_LIBRARY_INDEX.md`).
7. Provide IR runbook (`docs/GRIMBANEWS_INCIDENT_RESPONSE_RUNBOOK.md`).
8. Provide vendor register (`docs/GRIMBANEWS_VENDOR_REGISTER.md`).
9. Provide risk methodology (`docs/GRIMBANEWS_RISK_ASSESSMENT_METHODOLOGY.md`).
10. Identify the 6-12 month evidence-collection window start date.

## Cross-references

- Master plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1801-S1810)
- Sister docs: `docs/GRIMBANEWS_VENDOR_REGISTER.md`, `docs/GRIMBANEWS_POLICY_LIBRARY_INDEX.md`, `docs/GRIMBANEWS_INCIDENT_RESPONSE_RUNBOOK.md`, `docs/GRIMBANEWS_RISK_ASSESSMENT_METHODOLOGY.md`, `docs/GRIMBANEWS_PCI_DSS_SCOPE_STATEMENT.md`, `docs/GRIMBANEWS_GDPR_ROPA.md`
- Core code surfaces: `app/Http/Middleware/GrimbaSecurityHeaders.php`, `app/Support/GrimbaAutomationMonitor.php`, `app/Support/GrimbaDatabaseBackups.php`, `routes/console.php`
- Core tests: `tests/Feature/SecurityHeadersTest.php`, `tests/Feature/DatabaseBackupVerificationTest.php`
