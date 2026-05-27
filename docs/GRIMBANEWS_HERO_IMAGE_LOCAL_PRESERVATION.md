# GrimbaNews — Hero-Image Local Preservation

**Status:** plan v0
**Owner:** Jacob Lee (DevOps) + Larry Ellison (DBA)
**Walks:** Mythos S2229 (Archive cadence — image-asset preservation) deferred → partial
**Gating dependency:** Vader migration approval for `posts.hero_image_local_copy_path` column + S3 / Backblaze storage.

## Why this exists

Per-article hero images today are URLs to publisher CDNs. When publisher CDN expires URLs (common after 1-2 years), historical articles lose hero image. Preservation requires local copy.

## v1 design

- Daily `grimba:preserve-hero-images` cron.
- Per-post: if `posts.hero_image_url` not local, fetch + store in `storage/app/public/article-heroes/<post_id>.jpg`.
- Per-post: update `posts.hero_image_local_copy_path`.
- Per-image: convert to WebP for compression.

## Storage budget

- ~30K articles × 200KB avg image = ~6GB total.
- S3 / Backblaze cost: ~$0.40/mo.
- Negligible.

## Copyright handling

- Hero images licensed under publisher's republication terms (already covered by ingest license).
- Per-publisher policy review: some require revocation on takedown.
- Per-article delete-image-on-publisher-takedown workflow.

## Cross-references

Master plan: S2229. Sister: `docs/GRIMBANEWS_MULTI_DECADE_PRESERVATION_SCOPE.md`, `app/Http/Controllers/GrimbaImageProxyController.php` (existing).
