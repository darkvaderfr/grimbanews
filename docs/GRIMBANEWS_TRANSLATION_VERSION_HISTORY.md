# GrimbaNews — Translation Version History (Per-Article)

**Status:** plan v0
**Owner:** Lisa Nguyen (data) + Larry Ellison (DBA) + Liam Smith (PM)
**Walks:** Mythos S2230 (translation-archive — per-article translation history) deferred → partial
**Gating dependency:** Vader migration approval for `posts_translation_history` table.

## Why this exists

Today `posts.translated_<locale>` is overwritten on each re-translation. Lost version history limits:
- Editor review of translation quality over time.
- Per-version rollback.
- Reader-side "view earlier version" feature.

## Schema (gates on migration approval)

```
posts_translation_history:
  id | post_id | locale | translation_text | translated_at | translator (driver / human)
   | replaced_at | replaced_by_translation_id
```

## Per-translation lifecycle

1. New translation written → row in history.
2. Per-translation version becomes "current."
3. On re-translate, current marked replaced + new row.
4. Per-translation never deleted.

## UX

- Per-article reader-facing: "Voir les versions" link → opens version history modal.
- Per-version translator credit + date.
- Editor admin: per-translation review + rollback button.

## Storage budget

~30K articles × 5 locales × 2-3 versions avg × 200 char avg = ~30M rows over 5 years. SQLite handles. Negligible cost.

## Cross-references

Master plan: S2230. Sister: `docs/GRIMBANEWS_PER_CLUSTER_AUTO_TRANSLATION_PIPELINE.md`, `docs/GRIMBANEWS_PER_LOCALE_TONE_PROMPT_TEMPLATES.md`.
