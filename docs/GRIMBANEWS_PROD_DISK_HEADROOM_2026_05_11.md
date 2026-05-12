# GrimbaNews Production Disk Headroom - 2026-05-11

**Scope:** production disk pressure investigation and mitigation after the master sprint reconciliation.

## Starting State

Production root filesystem before cleanup:

- `/dev/sda2`: 40 GB total, 36 GB used, 1.3 GB available, 97% used.
- Inodes: 32% used.
- `grimba:health --fail-on-risk` still passed under the old 1024 MB floor, but the remaining headroom was too small for deploys, backups, and SQLite growth.

## Findings

Application footprint:

- `/var/www/grimbanews/current`: 871 MB.
- `database/backups`: 207 MB.
- `storage`: 9.2 MB.
- `vendor`: 436 MB.

Host-level pressure:

- `/var/lib/docker`: 12 GB.
- Docker JSON logs over 50 MB totaled about 1.8 GB.
- Largest log: `kaizen-db`, about 1.09 GB.
- Docker daemon already had JSON log rotation configured at `/etc/docker/daemon.json`:
  - `max-size`: `10m`
  - `max-file`: `3`

The oversized logs appear to be historical/running-container logs that had not been compacted yet. The GrimbaNews app is not the source of these logs, but the root disk is shared, so this directly affects GrimbaNews deploy and freshness reliability.

## Action Taken

Truncated Docker JSON logs larger than 50 MB:

```sh
find /var/lib/docker/containers -name "*-json.log" -size +50M -print -exec truncate -s 0 {} \;
```

Result:

- Root filesystem improved to 3.0 GB available, 92% used.
- Inodes remained healthy at 32% used.
- GrimbaNews production home and feed smokes passed after deploy.
- `grimba:health --fail-on-risk` passed after the scheduler observation patch.

## Code Guard

`grimba:health --fail-on-risk` now defaults to a 2048 MB free-space floor instead of 1024 MB. This makes the hourly ops health job fail earlier when disk headroom drops below 2 GB.

## Follow-Up - 2026-05-12

Deploy-time SQLite backups now use `sqlite3 .backup` when available and are compressed as `*.sqlite.gz`. The deploy script also compresses any older raw `database/backups/grimbanews.*.sqlite` snapshots and keeps the five newest backup artifacts across compressed and raw files.

This preserves restore evidence while reducing the backup directory footprint on the shared root disk.

## Follow-Up - 2026-05-12 Backup Integrity Guard

`grimba:health --fail-on-risk` now inspects `database/backups` when the directory exists. It reports the valid/invalid backup count, total backup footprint, and newest artifact age alongside disk free space. Tiny artifacts below 1 MB and files that do not read as SQLite backups are release-blocking risks.

The deploy script also prunes sub-1 MB backup artifacts using a `-1024k` predicate, which catches byte-sized failed backup shells correctly.

## Residual Risk

- Root disk is still tight at 92% used.
- Docker images and volumes still dominate host storage. Those containers belong to multiple services, so broad `docker system prune` is not a Grimba-only operation.
- Existing long-running containers may need recreation/restart to fully inherit daemon log rotation behavior.
- Production duplicate posts were safely applied for URL-backed groups; only ambiguous BBC title-only groups remain in review.

## Next

- Keep `grimba:health --fail-on-risk` green under the 2048 MB floor.
- Keep deploy backups compressed and let `grimba:health --fail-on-risk` fail on tiny or unreadable `*.sqlite.gz` artifacts.
- Verify restore documentation against `*.sqlite.gz` artifacts.
- Plan broader host maintenance separately for Docker image/volume cleanup.
