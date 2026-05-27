# GrimbaNews — NobuAI Hallucination Detector Plan

**Status:** plan v0 (brand-leak detector ships per `GrimbaNobuAiBrandPurityTest`)
**Owner:** Sara Chen (CISO) + Lisa Nguyen (data) + Lucy Leai (Strategy)
**Walks:** Mythos S1079 (hallucination-detector pass) deferred → partial
**Gating dependency:** Reference-grounded LLM call + per-claim validation pipeline.

## Why this exists

NobuAI summaries occasionally fabricate facts (dates, names, quotes) that aren't in the source. For an editorial product, this is reputation-critical. We need a per-summary hallucination check that runs BEFORE the summary ships to readers.

## v1 — string-match check

For each NobuAI-generated summary, verify that named entities (dates, monetary amounts, proper names) actually appear in the source article. Mismatches flag for editor review.

```php
GrimbaHallucinationChecker::check($source_text, $summary_text)
  -> ['ok' | 'flagged', 'fabricated_entities' => [...]]
```

## v2 — LLM verifier (gates on Wave YYYY agent-verifier harness)

For more subtle hallucinations (paraphrasing changing meaning), call a second NobuAI driver:

```
Fact-check this summary against the source article. Return JSON:
{"hallucinated_claims": [...], "confidence": 0.0-1.0}

Source:
<original article text>

Summary:
<NobuAI output>
```

## Wiring

- Per-summary check runs synchronously before save.
- If flagged: store summary in `posts.summary_nobuai_pending` (new column); editor reviews + approves before promoting to `posts.summary_nobuai` (visible).
- 95% expected pass-through; 5% manual review.

## Editor surface

`/admin/grimba/hallucination-reviews` lists pending summaries with diff vs source + suggested correction.

## Cross-references

Master plan: S1079. Sister: `docs/GRIMBANEWS_AGENT_VERIFIER_HARNESS_PLAN.md`, `tests/Feature/GrimbaNobuAiBrandPurityTest.php`.
Code: `app/Services/GrimbaNobuAi.php`.
