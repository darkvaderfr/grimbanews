# GrimbaNews Production Dedupe Apply - 2026-05-11

**Scope:** controlled production cleanup for RSS duplicate posts backed by the same source and canonical URL hash.

## Preconditions

- Current deploy completed successfully at commit `3ff5785`.
- Production SQLite backup evidence existed before apply:
  - `database/backups/grimbanews.20260511170914.sqlite`
  - Current DB size: 44 MB.
  - Backup directory retained 5 snapshots, 212 MB total.
- `grimba:dedupe-posts` defaults to dry-run and skips title-only groups unless `--include-title-groups` is explicitly supplied.

## Dry Run

Command:

```sh
php artisan grimba:dedupe-posts --limit=500
```

Result:

- 342 actionable duplicate groups by source plus canonical URL hash.
- 286 title-only review groups skipped.
- Would delete 424 duplicate posts.

## Apply

Command:

```sh
php artisan grimba:dedupe-posts --apply --limit=500
```

Result:

- Deleted 424 duplicate posts across 342 source+URL groups.
- Title-only groups remained skipped.
- No `--include-title-groups` cleanup was run.

## Verification

Production smoke:

- Home route: passed.
- Feed route: passed.
- `grimba:health --fail-on-risk`: passed.

Post-apply dedupe dry-run:

- 0 actionable duplicate URL groups remain.
- 20 title-only review groups remain.
- Would delete 0 posts without `--include-title-groups`.

Production health after apply:

- Published posts: 8000.
- Draft posts: 21.
- Published in last 24h: 615.
- RSS items in last 24h: 608.
- RSS feed health: 10 healthy active feeds, 0 stale, 0 sick.
- Disk free: 3053 MB / 39.2 GB, above the 2048 MB floor.

## Residual Risk

- 20 title-only groups need editorial review before any deletion.
- NewsAPI has 0 items in the last 24h; RSS is currently carrying freshness.
- Root disk remains tight at 92% used even after Docker log cleanup.
