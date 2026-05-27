# GrimbaNews — Per-Author Correction Tracking

**Status:** plan v0
**Owner:** Lucy Leai (Strategy) + Lisa Nguyen (data)
**Walks:** Mythos S1633 (per-author correction-tracking dashboard) deferred → partial
**Gating dependency:** Author byline table (Wave DDDD) + corrections schema (Wave KKKK).

## Why this exists

Per-source corrections rate is a known editorial metric. Per-author corrections rate is more granular and informs reader trust calibration at the byline level.

## v1 design

`/auteurs/{slug}` author profile (gates on author-byline pack):
- Total articles in corpus
- Total corrections (auto + reader-submitted)
- Correction rate per 100 articles
- Per-correction-type breakdown (date / name / quote / fact)
- Rolling 365-day trend chart

## Admin dashboard

`/admin/grimba/author-corrections`:
- Top-20 authors by correction count
- Authors with >5% correction rate flagged for editor review
- Per-author correction-resolution time

## Reader UX

On article byline: subtle "correction history" link → opens author profile.

## Cross-references

Master plan: S1633. Sister: `docs/GRIMBANEWS_PER_AUTHOR_TRUST_BADGE_PROGRESSION.md`, corrections-badge from Wave DDDD.
