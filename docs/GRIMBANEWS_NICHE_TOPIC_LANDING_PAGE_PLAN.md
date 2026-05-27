# GrimbaNews — Niche-Topic Landing Page (Deeper than /categorie/{slug})

**Status:** plan v0
**Owner:** Steve Jobs (CPO) + Nina Patel (Lead FE)
**Walks:** Mythos S2198 (Niche-topic per-bucket landing) deferred → partial
**Gating dependency:** v2 taxonomy + sub-bucket cluster volume.

## Why this exists

`/categorie/{slug}` lists posts in a bucket but doesn't show:
- Per-bucket trend chart
- Per-bucket key clusters
- Per-bucket editor's-pick standout
- Per-bucket-related dossiers cross-link

A richer `/sujets/{slug}` lands.

## v1 design

`/sujets/{slug}` page (e.g. `/sujets/climat`):
- Hero: per-bucket headline cluster
- Below: 3 per-bucket sub-bucket rails
- Per-bucket bias-mix retro chart (90d)
- Per-bucket Middle Ground / Blindspot rates
- Per-bucket newsletter signup CTA
- Per-bucket methodology cross-link
- Per-bucket featured-editor quote

## Cadence

Per-bucket editor weekly curation of the page.

## Cross-references

Master plan: S2198. Sister: `docs/GRIMBANEWS_TOPIC_TAXONOMY_V2_DESIGN.md`, `docs/GRIMBANEWS_NICHE_TOPIC_NEWSLETTER_PLAN.md`.
