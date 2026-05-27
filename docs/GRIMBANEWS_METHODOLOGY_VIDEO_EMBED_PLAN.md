# GrimbaNews — Methodology Video Embed on /methodologie

**Status:** plan v0
**Owner:** Jacob Lee (DevOps) + Nina Patel (Lead FE) + Sara Chen (CISO)
**Walks:** Mythos S1794 (methodology video embed) deferred → partial
**Gating dependency:** `GrimbaSecurityHeaders` CSP frame-src needs Vimeo/YouTube allow once host picked (Wave SUB-33 sister).

## v1 embed implementation

`/methodologie` page gets video embed at top:

- Vimeo iframe (primary per Wave SUB-33 ad-free reader UX).
- YouTube fallback if Vimeo down.
- Lazy-load via IntersectionObserver (don't load video until reader scrolls to it).

## CSP changes required

```php
// app/Http/Middleware/GrimbaSecurityHeaders.php
'frame-src' => "'self' https://player.vimeo.com https://www.youtube.com",
```

## Per-locale embed

Per-reader-locale, embed loads matching subtitle track + dubbing track.

## Per-embed analytics

- Per-embed impressions (page-view × video-loaded).
- Per-embed play-button click rate.
- Per-embed completion rate (via Vimeo/YouTube API).

## Cross-references

Master plan: S1794. Sister: `docs/GRIMBANEWS_METHODOLOGY_VIDEO_HOSTING_PLAN.md`, `app/Http/Middleware/GrimbaSecurityHeaders.php`.
