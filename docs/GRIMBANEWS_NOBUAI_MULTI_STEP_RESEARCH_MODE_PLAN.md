# GrimbaNews — NobuAI Multi-Step Research Mode Plan

**Status:** plan v0 (post-launch product feature)
**Owner:** Steve Jobs (CPO) + Lisa Nguyen (data) + Liam Smith (PM)
**Walks:** Mythos S1091 (NobuAI multi-step research mode) deferred → partial
**Gating dependency:** Reader subscription tier (S1211) + per-session research budget cap.

## Why this exists

For complex stories (e.g. unfolding crisis), a reader benefits from a guided multi-step research walkthrough: "Walk me through the timeline → who are the key actors → what does each camp claim → what's the consensus → what's still disputed." Today, reader pieces this together manually.

## v1 design

On a complex cluster (≥ 5 sources, ≥ 3 days active), a "Recherche guidée" button surfaces. On click:

1. NobuAI generates a 5-step research brief:
   - Timeline (date-anchored summary of events)
   - Key actors (named entities + their role)
   - Per-camp positions (with quotes)
   - Consensus (high-confidence shared facts)
   - Disputed (low-consensus claims, with each side's view)
2. Reader navigates step-by-step with "Suivant" / "Précédent".
3. Per-step deep-links to the source articles.

## Cost

- One brief = 5-7 NobuAI calls.
- Reader-tier-gated: free tier gets 3 briefs/month, pro tier gets unlimited.
- Cache 7 days per cluster (cluster doesn't change much after week 1).

## Schema (gates on Vader migration approval)

```
cluster_research_briefs:
  id | cluster_id | brief_json | generated_at | model | tokens_used | accessed_count
```

## Reader UX

- Inline on cluster detail page below the source comparison.
- Per-step swipeable on mobile.
- Per-step share-link (deep-link to specific step).
- Per-step audio narration option (gates on audio-narration plan, Wave KKKK).

## Cross-references

Master plan: S1091. Sister: `docs/GRIMBANEWS_NOBUAI_COUNTERARGUMENT_MODE_PLAN.md` (companion), `docs/GRIMBANEWS_CLUSTER_QUOTE_EXTRACTION_PLAN.md`.
