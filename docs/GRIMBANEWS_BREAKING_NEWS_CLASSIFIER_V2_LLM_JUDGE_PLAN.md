# GrimbaNews — Breaking News Classifier v2 (LLM Judge) Plan

**Status:** plan v0 (current `GrimbaBreakingClassifier` is keyword-based)
**Owner:** Rajesh Kumar (backend) + Liam Smith (PM) + Lisa Nguyen (data)
**Walks:** Mythos S1041 (breaking-news classifier v2 — LLM judge) deferred → partial
**Gating dependency:** NobuAI fallthrough budget; v1 classifier ships today and handles 95% of cases — the LLM-judge upgrade closes the 5% edge cases (multilingual idioms, ambiguous urgency cues).

## v1 (current) — keyword-based

`app/Support/GrimbaBreakingClassifier.php` matches a curated keyword list per locale (FR: "alerte", "urgent", "vient de"; EN: "breaking", "just in", "live update"). Score 0-100; threshold 70 triggers the `breaking` bucket.

## v2 — NobuAI judge

For posts where v1 score lands in [50, 80] (gray zone), call NobuAI with a strict prompt:

```
Given this article headline + first paragraph, classify on a scale of 0-100
whether this is a BREAKING NEWS event (unfolding right now, < 6 hours old,
high urgency). Return ONLY the integer.

Headline: <title>
First paragraph: <description>
```

Response cached for 24h per article-id. Cost: ~1 call per 5% of posts × ~200 posts/day = ~10 calls/day. Negligible against the per-day NobuAI budget.

## Wiring

1. `GrimbaBreakingClassifier::scoreV2($post)` — runs v1 first; calls NobuAI only if v1 score in gray zone.
2. Result stored in `posts.breaking_score_v2` (new nullable INT column — gates on Vader DB migration approval).
3. Original `is_breaking` boolean derived from `max(v1_score, v2_score) >= 70`.

## Fallback policy

If NobuAI is down: fall back to v1-only score. Never block ingest.

## Observability

- `grimba_automation_runs` row per nightly v2 run.
- Cockpit board surfaces "v2-disagreed-with-v1" count: when v2 disagreed with v1 by >20 points.
- Editor reviews disagreements weekly.

## Cross-references

Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1041).
Sister: `docs/GRIMBANEWS_BREAKING_NEWS_AB_TESTS.md` (S1050; lands with personalization v2).
Code: `app/Support/GrimbaBreakingClassifier.php`, `app/Services/GrimbaNobuAi.php`.
