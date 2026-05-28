# GrimbaNews — SOC 2 Audit Field-Work Week 2 (Encryption / Logging / Backup)

**Status:** plan v0
**Owner:** Sara Chen (CISO) + Jacob Lee (DevOps) + Larry Ellison (DBA)
**Walks:** Mythos S1815 (SOC 2 audit field-work week 2) deferred → partial
**Gating dependency:** Week 1 audit complete (Wave SUB-36).

## Per-week 2 focus

### Day 1: Encryption walkthrough
- Per-disk encryption at rest evidence (VPS LUKS / FileVault).
- Per-DB encryption evidence (TDE or app-layer).
- Per-transit TLS evidence (certs, cipher suites, TLS-version pinning).
- Per-backup encryption evidence (Wave LLL backup-encryption plan).
- Per-secret-rotation evidence (Wave LLL secret-rotation runbook).

### Day 2: Logging + monitoring walkthrough
- Per-system log retention policy (1y standard).
- Per-system log integrity (no-delete + offsite mirror).
- Per-incident alert pipeline (Slack webhook + PagerDuty per Wave LLL paging matrix).
- Per-monitoring dashboard sample (Sentry, /admin/grimba/cockpit).
- Per-log-anomaly review cadence.

### Day 3: Backup walkthrough
- Per-backup cadence (daily SQLite backup per Wave OO `grimba:create-backup`).
- Per-backup verify (Wave OO `grimba:verify-backups`).
- Per-DR-drill log (`docs/DR_DRILL_2026_05_23.md`).
- Per-offsite-backup plan (Wave SUB-14 multi-decade preservation).

### Day 4-5: Sampling + per-control follow-up + per-week wrap

## Cross-references

Master plan: S1815. Sister: `docs/GRIMBANEWS_SOC2_AUDIT_WEEK1_ACCESS_CHANGE_MGMT.md`, `docs/GRIMBANEWS_BACKUP_ENCRYPTION_REVIEW_PLAN.md` (Wave LLL).
