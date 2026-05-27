# GrimbaNews — Reader Feedback Widget (Per-Article)

**Status:** plan v0
**Owner:** Steve Jobs (CPO) + Nina Patel (Lead FE) + Lucy Leai (Strategy)
**Walks:** Mythos S1661 (reader feedback intake widget) deferred → partial
**Gating dependency:** /contact form exists; per-article context-attached widget is the upgrade.

## Why this exists

Reader feedback today goes to /contact (generic). Per-article widget captures article-specific signal: "this summary is wrong" / "missing source X" / "great cluster" — with auto-attached article + cluster ID.

## v1 design

Below every article, small "Une remarque sur ce dossier?" link → opens modal:

- Rating: 5-star (engagement) + emoji (vibe)
- Free-text comment (250 chars)
- Category dropdown: factual correction | missing source | great coverage | confusing | bias concern | other
- Reader email (optional, for follow-up)
- Auto-attached: article ID, cluster ID, current locale

## Mod queue

Per-article feedback goes to per-cluster-editor mod queue (per Wave AACC).

- Factual correction → routes to corrections workflow (Wave KKKK)
- Missing source → routes to source-roster expansion check
- Bias concern → routes to bias-shift detection (Wave AAFF) + editor review
- Other → routes to Lucy weekly review

## Schema

```
article_feedback:
  id | post_id | cluster_id | submitter_member_id (nullable) | rating | category
   | comment | locale | created_at | reviewed_at | review_outcome
```

## Cross-references

Master plan: S1661. Sister: `docs/GRIMBANEWS_CITIZEN_CONTRIBUTION_FACT_SUBMISSION_PLAN.md`, `docs/GRIMBANEWS_NOBUAI_HALLUCINATION_CORPUS_GROWTH_PLAN.md`.
