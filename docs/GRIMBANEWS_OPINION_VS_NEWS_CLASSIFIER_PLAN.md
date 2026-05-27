# GrimbaNews — Opinion vs News Classifier Plan

**Status:** plan v0
**Owner:** Lisa Nguyen (data) + Lucy Leai (Strategy)
**Walks:** Mythos S1069 (opinion-vs-news classifier) deferred → partial
**Gating dependency:** Editorial heuristic baseline + NobuAI judge budget.

## Why this exists

Articles in the corpus include news reports, op-eds, analyses, editorials, and reader-submitted columns. Treating all as "news" pollutes bias measurements (op-eds intentionally take a position; news shouldn't). Per the editorial-style guide (Wave LLL), news vs opinion is a key separation.

## v1 — heuristic-only

Per-post boolean `is_opinion` derived from:

1. **URL pattern** — `/opinion/`, `/op-ed/`, `/analysis/`, `/editorial/`, `/tribune/` in the canonical URL.
2. **Source-side category** — when ingest preserves category tag (e.g. WordPress category), match against an opinion-keyword list.
3. **First-byline pattern** — "Par <Name>, éditorialiste" or "Opinion by ..." in first paragraph.

Coverage: ~70% of clearly-opinion articles flagged.

## v2 — NobuAI judge

For posts where v1 doesn't fire AND meta-signals are ambiguous (e.g. first-person voice detected), call NobuAI:

```
Is this article opinion (op-ed, editorial, columnist) or news (report,
investigation, dispatch)?
Answer ONLY "opinion" or "news".

Headline: ...
First paragraph: ...
```

## UX impact

- Bias-distribution panel excludes `is_opinion=true` posts from cluster bias counts (or surfaces them in a separate "opinion mix" sub-panel).
- Article cards carry an "Opinion" badge.
- /search adds a `?type=opinion|news` facet.
- Per-source profile shows opinion-vs-news ratio.

## Schema (new column, gates on Vader DB migration approval)

```
posts.is_opinion BOOLEAN DEFAULT FALSE
posts.opinion_confidence FLOAT NULL  -- 0.0 - 1.0
```

## Cross-references

Master plan: S1069. Sister: `docs/GRIMBANEWS_EDITORIAL_STYLE_GUIDE.md` (Wave LLL).
Code: `app/Support/GrimbaBreakingClassifier.php` (similar heuristic pattern).
