# GrimbaNews Title-Only Dedupe Review - 2026-05-11

**Scope:** make the remaining title-only duplicate groups reviewable without encouraging unsafe deletion.

## Problem

Production health still reports title-only duplicate groups after the source+canonical-URL duplicate cleanup. Those groups are not automatically actionable because live blogs, recurring briefs, and evergreen utility articles can reuse the same headline while pointing to different canonical URLs.

The existing dedupe command skipped those groups by default, which was correct, but operators had no compact review artifact for editorial triage.

## Change

- Added `grimba:dedupe-posts --review-title-groups`.
- The mode is always non-destructive and returns before any delete path.
- The report prints source, title, count, post IDs, sample URLs, and explicit `review_post_id` / `review_url` lines for copyable review evidence.
- Health and cockpit copy now point operators to review mode first instead of implying `--apply`.
- BBC Sounds live-stream/title collisions are now classified as known recurring media, not unresolved article duplicates. They remain non-destructive and are excluded from health/cockpit duplicate debt.

## Verification

- `php artisan test tests/Feature/DedupePostsCommandTest.php`
  - 2 tests, 25 assertions.

Regression coverage proves:

- URL-backed duplicates still delete under `--apply`.
- Title-only groups remain skipped by default.
- Review mode lists the duplicate title, post IDs, sample URLs, and does not delete posts.

## Production Follow-Up

Run this on production for an editorial artifact:

```sh
php artisan grimba:dedupe-posts --review-title-groups --limit=100
```

Do not use `--include-title-groups` until each group has been reviewed against its sample URLs.

## Production Review - 2026-05-12

The remaining production groups were both BBC Sounds episode/live-stream URL collisions under source `BBC`:

- `LIVE at Maida Vale studios… How popular is Donald Trum...`
- `Trump takes his revenge on disloyal Republicans`

Sample URLs paired `/sounds/play/w3ct...` episode links with `/sounds/play/live:bbc_world_service...` stream links. These are recurring media feed artifacts, not safe article duplicates. The command and health guard now ignore this narrow shape so the duplicate warning remains reserved for real article-review debt.
