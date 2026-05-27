# GrimbaNews — Annotation Export Design

**Status:** plan v0
**Owner:** Nina Patel (Lead FE) + Rajesh Kumar (Backend) + Michael O'Connor (Tech Writer)
**Walks:** Mythos S1547 (annotation export — Markdown / Roam) deferred → partial
**Gating dependency:** S1371 annotation schema + S1541 reader-side highlights.

## Why this exists

Reader annotations are reader data. The export option is the litmus test for whether Grimba respects portability or holds notes hostage like the worst SaaS platforms.

## v1 formats

| Format | Endpoint | Notes |
|---|---|---|
| Markdown (per-notebook) | `/coffre/carnets/{slug}/export.md` | Quote + note + source link per entry |
| Markdown (all annotations, flat) | `/coffre/annotations/export.md` | Chronological |
| Roam Research JSON | `/coffre/annotations/export.roam.json` | Pages = articles, blocks = quotes |
| Obsidian zip | `/coffre/annotations/export.obsidian.zip` | One MD file per article with frontmatter |
| Plain JSON | `/coffre/annotations/export.json` | Canonical schema, future-proof |

## Behavior

- Async job (large libraries can run > 10s).
- Email delivered when ready (link is signed, 24h expiry).
- Include attribution + source URL in every export to preserve provenance.

## Anti-patterns

- No export fees.
- No partial export with cap.
- No format lock-in (always offer plain JSON alongside).

## Cross-references

Master plan: S1547. Sister: S1371 (annotation schema), S1376 (notebook), S1548 (annotation analytics), S1549 (moderation).
