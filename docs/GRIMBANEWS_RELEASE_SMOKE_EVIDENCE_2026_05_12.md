# GrimbaNews Release Smoke Evidence - 2026-05-12

**Scope:** make the post-deploy release smoke leave a durable evidence artifact for release readiness, rollback review, and production handoff.

## What Changed

- `grimba:release-smoke` now supports `--evidence`.
- Evidence defaults to `storage/app/grimba-release-evidence/`.
- `--evidence-path=` writes to an explicit Markdown file path for CI, local tests, or a release packet.
- The report records the commit, environment, base URL, Host header, disk floor, full-content floor, and every Artisan/HTTP smoke check result.
- The homepage smoke now fails when enforced CSP or companion security headers disappear.
- `--require-newsapi --newsapi-recent-hours=24` can turn NewsAPI readiness into a hard release-smoke gate once a production key exists.
- Failed smoke runs still write evidence before returning failure, so the release packet captures the reason for the block.
- `deploy.sh` writes the deployed short SHA to `REVISION` after extracting the tarball, so production evidence reports can identify the exact release even though `.git` is not deployed.
- `grimba:prune-release-evidence --days=30 --keep=30` now keeps the evidence trail bounded and is scheduled daily at `03:35`.

## Operational Use

```sh
php artisan grimba:release-smoke --base-url=https://grimbanews.com --evidence
```

For IP-based production verification:

```sh
php artisan grimba:release-smoke --base-url=http://209.74.88.135 --host-header=grimbanews.com --evidence
```

## Verification

- `php artisan test tests/Feature/ReleaseSmokeCommandTest.php`
- `php artisan test tests/Feature/SecurityHeadersTest.php tests/Feature/ReleaseSmokeCommandTest.php`
- `php artisan test tests/Feature/AutomationScheduleTest.php tests/Feature/DailyPublishFreshnessTest.php tests/Feature/ReleaseSmokeCommandTest.php`
- `php artisan test`
- `bash -n deploy.sh`
- `php artisan test tests/Feature/ReleaseEvidencePruneTest.php tests/Feature/AutomationScheduleTest.php`
- Production deploy and `grimba:release-smoke --base-url=http://209.74.88.135 --host-header=grimbanews.com --evidence`

## Remaining Risks

- This is release-smoke evidence, not final launch signoff.
- NewsAPI still needs a real production key before it can contribute to daily publishing.
- Root disk headroom remains tight enough that the 2048 MB health floor should stay release-blocking.
