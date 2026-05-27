# GrimbaNews — Per-Region Partner Brand Guidelines

**Status:** plan v0
**Owner:** Alex Morgan (UI/UX) + Lucy Leai (Strategy) + per-partner
**Walks:** Mythos S1691 (per-region partner brand guidelines) deferred → partial
**Gating dependency:** First-partner attribution surface live.

## Why this exists

Partners surface in GrimbaNews via attribution badges. These must:
1. Respect partner brand identity (logo, colors).
2. Not visually clash with GrimbaNews theme.
3. Be consistent across surfaces.

## Per-partner brand-asset schema

```
partner_brand:
  partner_id PK | logo_url (svg preferred) | primary_color (hex)
   | secondary_color (hex) | brand_voice_note | last_updated
```

## Asset technical specs

- Logo: SVG required (vector); fallback PNG 500x500
- Background-on-light + background-on-dark variants
- Per-partner color usable for attribution-badge text color
- Partner-provided brand-voice note for editorial alignment

## Cross-references

Master plan: S1691. Sister: `docs/GRIMBANEWS_NEWSROOM_PARTNERSHIP_TEMPLATE.md`, `docs/GRIMBANEWS_PER_REGION_PARTNER_ATTRIBUTION_METRICS.md`.
