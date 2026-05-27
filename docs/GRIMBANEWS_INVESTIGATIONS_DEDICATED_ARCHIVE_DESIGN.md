# GrimbaNews — Investigations Dedicated Archive Design

**Status:** plan v0
**Owner:** Steve Jobs (CPO) + Nina Patel (Lead FE)
**Walks:** Mythos S2216 (Long-form investigations dedicated archive) deferred → partial
**Gating dependency:** First investigation published.

## Why this exists

Investigations deserve a dedicated archive surface distinct from `/dossiers`. Curated, high-prestige, long-form-only.

## v1 design

`/investigations` landing:
- Hero: latest investigation
- Card grid: per-year (most recent first)
- Per-investigation card: title, lede, byline, publish date, awards-won badge
- Filter: by topic, by year, by award-winning
- Editor's-collection picks panel

## Per-investigation page

- Long-form layout (per Wave SUB-10)
- Impact panel (per Wave SUB-12 SUB-11 sister)
- Companion podcast/video (per Wave SUB-11)
- Data publication (per Wave SUB-10)
- Counsel-review log
- Awards-submitted-to record

## SEO + sitemap

- Per-investigation Schema.org NewsArticle + ReportageNewsArticle
- Per-investigation OG image dedicated card
- Investigations sitemap separate from main

## Cross-references

Master plan: S2216. Sister: `docs/GRIMBANEWS_LONG_FORM_INVESTIGATIONS_SCOPE.md`, `docs/GRIMBANEWS_LONG_FORM_LAYOUT_TEMPLATE.md`.
