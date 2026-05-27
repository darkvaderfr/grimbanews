# GrimbaNews — Sponsored Content Detector Plan

**Status:** plan v0
**Owner:** Lisa Nguyen (data) + Lucy Leai (Strategy)
**Walks:** Mythos S1070 (sponsored-content detector) deferred → partial
**Gating dependency:** Editorial heuristic baseline + NobuAI judge budget.

## Why this exists

Major publishers run sponsored/native-advertising content alongside news. Treating sponsored content as news inflates a source's article count and skews bias measurements. Per the editorial-style guide (Wave LLL), sponsored content must be tagged separately.

## v1 — heuristic-only

Per-post boolean `is_sponsored` derived from:

1. **URL pattern** — `/sponsored/`, `/partner-content/`, `/advertorial/`, `/brand-studio/` in the canonical URL.
2. **Disclosure label** — "Contenu sponsorisé", "Sponsored", "Paid Partnership", "Promotion" in the first 200 chars.
3. **Source-side category** — when ingest preserves category tag, match against sponsored-keyword list.

Coverage: ~85% of clearly-sponsored articles flagged.

## v2 — NobuAI judge

For ambiguous cases, NobuAI prompt:

```
Is this article sponsored content (paid placement by a brand) or
editorial content (independent journalism)?
Answer ONLY "sponsored" or "editorial".

Headline: ...
First paragraph: ...
```

## UX impact

- Bias-distribution panel excludes `is_sponsored=true` posts from cluster counts.
- Article cards carry a "Sponsorisé" badge in amber.
- /search adds `?type=editorial|sponsored` facet.
- Per-source profile shows sponsored-vs-editorial ratio.

## Schema (gates on Vader migration approval)

```
posts.is_sponsored BOOLEAN DEFAULT FALSE
posts.sponsored_disclosure VARCHAR(255) NULL
```

## Cross-references

Master plan: S1070. Sister: `docs/GRIMBANEWS_OPINION_VS_NEWS_CLASSIFIER_PLAN.md` (similar heuristic pattern), `docs/GRIMBANEWS_EDITORIAL_STYLE_GUIDE.md`.
