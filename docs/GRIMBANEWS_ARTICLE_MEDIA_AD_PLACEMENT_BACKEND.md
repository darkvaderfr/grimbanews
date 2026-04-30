# GrimbaNews Article Media and Ad Placement Backend

**Status:** first backend pass shipped  
**Date:** 2026-04-30

## Article Image Provenance

The renderable hero image remains `posts.image`. The new migration `2026_04_30_120000_add_article_image_provenance_to_posts.php` adds audit fields:

- `image_source_url`: feed URL or article URL used during extraction.
- `image_extraction_method`: `feed`, `og`, `twitter`, or `img`.
- `image_extracted_at`: last extraction attempt timestamp.
- `image_extract_error`: compact failure reason, usually `no usable image found`.

`grimba:enrich-drafts` now writes these fields when they exist, while remaining backward-compatible before the migration is applied.

## Story Ad Locations

Story pages now have three dedicated locations:

- `grimba_story_after_hero`
- `grimba_story_mid`
- `grimba_story_sidebar`

They are registered in the existing ads location filter, so the current ads backend can assign creative to those placements without a new admin surface.

## Deferred Backend Work

- Persist normalized article content blocks for paragraph-aware inline ad insertion.
- Store image dimensions, captions, license/source credit, confidence, and local media cache path.
- Add consent/subscriber suppression metadata if the ads plugin cannot express it cleanly.
