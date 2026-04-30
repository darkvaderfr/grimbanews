# GrimbaNews Article Media and Ad Placement Backend

**Status:** backend pass 2 shipped
**Date:** 2026-04-30

## Article Image Provenance

The renderable hero image remains `posts.image`. The new migration `2026_04_30_120000_add_article_image_provenance_to_posts.php` adds audit fields:

- `image_source_url`: feed URL or article URL used during extraction.
- `image_extraction_method`: extraction hint such as `enclosure`, `media_thumbnail`, `newsapi`, `og`, `twitter`, `schema`, `image_src`, `jsonld`, `srcset`, or `img`.
- `image_extracted_at`: last extraction attempt timestamp.
- `image_extract_error`: compact failure reason, usually `no usable image found`.

`grimba:enrich-drafts`, the RSS poller, and the NewsAPI fetcher now write these fields when they exist, while remaining backward-compatible before the migration is applied.

The RSS draft queue and NewsAPI draft readiness table expose the provenance method, source URL, image extraction failure, and full-article extraction failure so editors can separate publish guardrail issues from upstream media/extraction problems without opening each Botble post.

The article-page scraper now checks common publisher metadata in this order: Open Graph, Twitter card, schema.org `itemprop=image`, `image_src`, JSON-LD image fields, `srcset`, then a direct image `src`.

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
