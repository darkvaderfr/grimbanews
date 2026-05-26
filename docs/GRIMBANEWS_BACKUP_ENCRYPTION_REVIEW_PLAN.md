# GrimbaNews — Backup Encryption Review Plan

**Status:** plan v0 (today: SQLite gzipped on VPS disk; offsite encrypted backup deferred to S1561 arc)
**Owner:** Sara Chen (CISO) + Jacob Lee (DevOps) + Larry Ellison (DBA)
**Walks:** Mythos S945 (backup encryption review) deferred → partial
**Gating dependency:** Offsite backup provider selected (S3, B2, Wasabi) + KMS for encryption key.

## Today's state

- `grimba:create-backup` Artisan command (Wave YYYYYYYYYY) makes `storage/app/backups/grimbanews.<timestamp>.sqlite` via `VACUUM INTO`.
- 14-day retention via `--keep=14`.
- `grimba:verify-backups` confirms PRAGMA quick_check on each.
- Backups live on the same VPS disk — single-machine failure = total data loss.

## Threats this plan addresses

| Threat | Mitigation |
|---|---|
| VPS disk failure | Offsite backup |
| VPS provider account compromise | Offsite + encryption-at-rest |
| Sysadmin curiosity | Encryption-at-rest with KMS-managed key |
| Backup tampering | Per-backup SHA256 stored separately |
| Backup loss | Multi-region offsite + retention policy |

## Target architecture

1. `grimba:create-backup` produces local snapshot.
2. New step: `grimba:push-backup-offsite` uploads to offsite (e.g. Backblaze B2) using rclone or AWS SDK.
3. Encryption: AES-256-GCM with key from KMS (AWS KMS, GCP KMS, or Hashicorp Vault).
4. Per-backup SHA256 stored both with the backup AND in a separate audit log.
5. Multi-region: at least 2 geographically-distinct regions.

## Retention policy

- Last 14 days: daily snapshot, hot tier.
- Weeks 3-12: weekly snapshot, warm tier.
- Months 4-12: monthly snapshot, cold tier.
- Year+ : quarterly snapshot, archive tier.

## DR drill

- Quarterly: restore from offsite into fresh VPS; verify `php artisan grimba:smoke` passes.
- Annually: full prod swap (operator chooses non-traffic window).

## Cost estimate

- B2 storage ~$5/TB/month; current DB ~20MB compressed → < $1/month for years of backups.
- KMS keys ~$1/month per key.
- Upload egress: B2 free for first 3× download volume.

## Cross-references

Master plan: S945. Sister: `docs/GRIMBANEWS_GO_LIVE_RUNBOOK.md`, `docs/GRIMBANEWS_DR_DRILL_2026_05_23.md`.
Code: `app/Console/Commands/GrimbaCreateBackup.php`, `app/Console/Commands/GrimbaVerifyBackups.php`.
