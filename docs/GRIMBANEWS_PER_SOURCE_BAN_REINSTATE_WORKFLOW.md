# GrimbaNews — Per-Source Ban + Reinstate Workflow

**Status:** plan v0
**Owner:** Lucy Leai (Strategy) + Sara Chen (CISO)
**Walks:** Mythos S1662 (per-source ban-and-reinstate workflow) deferred → partial
**Gating dependency:** Operator decision authority.

## Why this exists

Sometimes a source crosses an editorial / legal line (sustained disinformation, hate-speech bursts, ToS violation). GrimbaNews needs a clean ban workflow that:
1. Stops ingest immediately
2. Hides existing articles from reader-facing surfaces (preserves URLs for back-link integrity)
3. Logs rationale for transparency report
4. Has a clear reinstate path if conditions change

## Workflow

1. Operator triggers ban via `/admin/grimba/news-sources/{id}/ban`.
2. Required fields: rationale (free-text), category (disinformation | hate | tos | other), effective_until (date or "permanent").
3. System:
   - Sets `news_sources.banned_at`, `banned_until`, `ban_rationale`.
   - Stops `grimba:poll-feeds` for that source.
   - Marks all existing posts from source as hidden from /breaking, /latest, /pour-vous (but URL-accessible with banner).
   - Logs to `source_ban_log` table.
4. Per-month transparency-report entry per `docs/GRIMBANEWS_TRANSPARENCY_REPORT_SCOPE.md`.

## Reinstate path

- After `banned_until` elapses OR operator review:
- Editor + Lucy + Sara Chen review.
- Sign-off required from at least 2.
- Reinstatement logged with rationale.

## Cross-references

Master plan: S1662. Sister: `docs/GRIMBANEWS_TRANSPARENCY_REPORT_SCOPE.md` (Wave LLL), `docs/GRIMBANEWS_OMBUDSMAN_CHARTER_DRAFT.md`.
