# GrimbaNews — Per-Region Partner Data Export Plan

**Status:** plan v0
**Owner:** Lucy Leai (Strategy) + Sara Chen (CISO)
**Walks:** Mythos S1692 (per-region partner data export) deferred → partial
**Gating dependency:** Per-partner SLA + GDPR-compliance review.

## Why this exists

Partners deserve their own data: which clusters their content fed, which articles got most engagement, reader-feedback breakdown. Export on-demand + scheduled.

## v1 design

`/partenaire/{slug}/export` (partner auth-gated):

- Pick date range (max 1 year window)
- Pick formats: JSON | CSV | PDF
- Pick categories: articles | clusters | reader-engagement | bias-mix | brand-safety
- One-click generate + download

## Schema (no new tables — uses existing partner_id joins)

Standard joins across posts + clusters + reader_events + brand_safety_logs filtered by partner_id.

## GDPR compliance

- Per-export includes IP-anonymized aggregates only
- Per-reader data: never included
- Partner DPA agreement covers their consumption

## Cross-references

Master plan: S1692. Sister: `docs/GRIMBANEWS_PER_REGION_PARTNER_ATTRIBUTION_METRICS.md`, `docs/GRIMBANEWS_PII_FIELD_INVENTORY.md`.
