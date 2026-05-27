# GrimbaNews — Long-Form Layout Template (>3000 words)

**Status:** plan v0
**Owner:** Steve Jobs (CPO) + Nina Patel (Lead FE) + Alex Morgan (UI/UX)
**Walks:** Mythos S2209 (Long-form investigations layout template) deferred → partial
**Gating dependency:** Current article layout (`partials/post-hero-img.blade.php`) is standard reader; long-form needs distinct chrome.

## v1 design (Steve cinematic standard)

For articles where `posts.is_longform=true`:
- Hero: Full-width image + serif headline (Fraunces 56px)
- Byline: investigator + co-investigators + counsel-review badge
- Reading time estimate
- Per-section nav sidebar (collapsible on mobile)
- Pull-quotes (typography emphasized)
- Inline embeds: per-section data viz + image gallery
- Glossary panel
- Per-section share-link
- "Methodology" panel at bottom
- "Source-protection note" at bottom
- "Counsel-review log" at bottom

## Schema (gates on Vader migration approval)

```
posts.is_longform BOOLEAN DEFAULT FALSE
posts.longform_subtitle VARCHAR(255) NULL
posts.longform_glossary JSON NULL
posts.longform_methodology_note TEXT NULL
```

## Reader UX

- Per-section auto-scroll with progress indicator
- Per-section permalink anchors
- "Save for later" / "Print PDF" / "Share" sticky toolbar
- Reading-mode toggle (per Wave LLL reading-mode design)

## Cross-references

Master plan: S2209. Sister: `docs/GRIMBANEWS_LONG_FORM_INVESTIGATIONS_SCOPE.md`, `feedback_steve_design_language.md`.
