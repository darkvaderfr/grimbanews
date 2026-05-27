# GrimbaNews — Methodology Video Hosting Plan

**Status:** plan v0
**Owner:** Jacob Lee (DevOps) + Lucy Leai (Strategy)
**Walks:** Mythos S1793 (methodology video hosting) deferred → partial
**Gating dependency:** Operator-side platform decision.

## Hosting options

### YouTube
- Pro: free, max reach, embeddable, search-discoverable.
- Con: ads on free tier may pre-roll competitor content; algorithm dependent.

### Vimeo Pro
- Pro: ad-free, clean embed, password-protect option.
- Con: ~$20/mo subscription, smaller reach.

### Self-hosted (Bunny.net CDN)
- Pro: full control, no ads, no platform risk.
- Con: ~$10/mo CDN cost, no built-in audience.

## Recommendation: hybrid

- **Primary:** YouTube (max reach + free).
- **Secondary embed:** Vimeo Pro for `/methodologie` page embed (ad-free reader UX).
- **Master archive:** self-hosted on Bunny.net (own-the-master safety net).

## Per-platform metadata

Per-platform per-language:
- Title + description tuned per platform SEO.
- Per-platform subtitle file (SRT for YouTube; VTT for HTML5).
- Per-platform thumbnail variant.

## Per-platform analytics

- YouTube Analytics (free).
- Vimeo Pro analytics.
- Per-platform monthly Lucy review.

## Cross-references

Master plan: S1793. Sister: `docs/GRIMBANEWS_METHODOLOGY_VIDEO_PRODUCTION_PLAN.md`.
