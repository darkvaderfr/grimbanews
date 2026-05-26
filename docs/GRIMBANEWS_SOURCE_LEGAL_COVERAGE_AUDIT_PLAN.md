# GrimbaNews — Source Legal Coverage Audit Plan

**Status:** plan v0 (no per-source legal audit run)
**Owner:** Lucy Leai (Strategy) + retained counsel + Sara Chen (CISO) on data-flow review
**Walks:** Mythos S1030 (source legal coverage audit) deferred → partial
**Gating dependency:** Retained counsel review per source, jurisdiction-specific. Operator slot is `news_sources.license_notes`.

## What the audit covers per source

1. **Republication terms** — does the source's ToS or country copyright law allow our aggregation pattern (headline + first 250 chars + canonical link)?
2. **Author rights** — is byline attribution required? GrimbaNews defaults to source-attribution; per-author attribution is on a future-roadmap surface (`docs/GRIMBANEWS_AUTHOR_BYLINE_TABLE_SCHEMA.md` Wave DDDD).
3. **Image rights** — can we proxy publisher hero images via `app/Http/Controllers/GrimbaImageProxyController.php` under fair-use / news-reporting carve-outs in the source's jurisdiction?
4. **Quote-length limits** — most jurisdictions allow "short quotation for review" but precise length varies. Default cap = 250 chars in GrimbaArticleSummarizer.
5. **Mandatory takedown windows** — DMCA (US) requires 24h; EU directive 2019/790 varies per country; UK CDPA varies.

## Per-source audit output

Each audit produces a stored note in `news_sources.license_notes` (TEXT column). Format:

```
[Audited YYYY-MM-DD by <counsel>]
  Republication: <allowed | review-required | not-allowed>
  Quote cap: <chars>
  Image: <proxy-ok | no-proxy>
  Takedown SLA: <hours>
  Special: <any per-source carve-outs>
```

## Cadence

- New sources: counsel audit BEFORE seeder enable.
- Existing sources: 12-month rolling audit (operator-driven).
- ToS update detected (per `grimba:check-source-tos` cron — future, not built): flag for re-audit.

## Risk-register snapshot

| Risk | Mitigation |
|---|---|
| State-owned outlet pulls license | Per-source `license_notes` flags + 24h takedown via admin |
| Author estate disputes attribution | Per-author `author_attribution_required` flag (S1145 partial, Wave KKKK) |
| Image rights challenge | Image proxy cache invalidation via `grimba:rebuild-og --include-articles` |
| DMCA notice | Ombudsman + 24h takedown per `docs/GRIMBANEWS_OMBUDSMAN_CHARTER_DRAFT.md` |

## Cross-references

Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1030).
Sister: `docs/GRIMBANEWS_NEWSROOM_PARTNERSHIP_TEMPLATE.md`, `docs/GRIMBANEWS_DMCA_RIGHT_OF_REPLY_POLICY.md`, `docs/GRIMBANEWS_OMBUDSMAN_CHARTER_DRAFT.md`.
Schema slot: `news_sources.license_notes`.
