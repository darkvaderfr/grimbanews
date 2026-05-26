# GrimbaNews — Translation-Level Correction Surface

**Status:** plan v0 (no per-translation correction notice; only article-level correction badge planned)
**Owner:** Liam Smith (PM) + Nina Patel (Lead Frontend) + Rajesh Kumar (Backend) on schema
**Walks:** Mythos S1436 (Translation-level correction) deferred → partial
**Gating dependency:** Article-level correction primitive (S2006 — corrections table) + translation status surface (existing `posts.translation_status`).

## Why this exists

S1436 acknowledges that a translation can be wrong even when the original is right (mistranslation, dropped clause, false-friend). Today a correction badge applies to the post as a whole; readers reading the FR translation of an EN article have no way to know "the FR rendering was corrected — original was always fine."

## Today's surrogate

- **`posts.translation_status`** + `GrimbaTranslationPresenter` carry translation metadata.
- **Article-level correction badge** (S2006 deferred) would apply globally — wrong for translation-only fixes.

## Schema additions (target)

```sql
ALTER TABLE corrections ADD COLUMN scope ENUM('article','translation') DEFAULT 'article';
ALTER TABLE corrections ADD COLUMN translation_locale VARCHAR(8) NULL;
ALTER TABLE corrections ADD COLUMN original_text TEXT NULL;
ALTER TABLE corrections ADD COLUMN corrected_text TEXT NULL;
```

## UI surface

- Badge variant: "Correction (FR translation)" vs "Correction (article)" — pill color distinct (amber vs red).
- Per-locale rendering: badge only shows on the affected locale's rendering of the article.
- Tooltip: shows original vs corrected snippet (when `original_text` + `corrected_text` populated).

## Acceptance gates

- A correction created with `scope=translation` + `translation_locale=fr` appears on `/fr/{slug}` only.
- The `/en/{slug}` rendering does NOT show the badge.
- Hreflang headers unaffected.

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1436)
- Sister docs: `docs/GRIMBANEWS_CORRECTION_NOTICE_BADGE_DESIGN.md`, `docs/GRIMBANEWS_CORRECTION_FLOW_LAUNCH_RETRO_PLAN.md`
- Existing infra: `app/Support/GrimbaTranslationPresenter.php`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
