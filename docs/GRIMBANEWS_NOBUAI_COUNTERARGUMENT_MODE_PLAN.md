# GrimbaNews — NobuAI Counterargument Mode Plan

**Status:** plan v0 (post-launch product feature)
**Owner:** Steve Jobs (CPO) + Lucy Leai (Strategy) + Lisa Nguyen (data)
**Walks:** Mythos S1093 (NobuAI counterargument mode) deferred → partial
**Gating dependency:** Reader subscription tier (S1211) + per-cluster Q&A surface (S1145, partial).

## Why this exists

When reading a polemical story, readers benefit from "the strongest counterargument to what I just read" — for intellectual humility, not for ideological balance. Distinct from showing the other side's coverage (which is what dossiers do); this surfaces the steelman of the opposing position.

## v1 design

On any article page, a "Voir le contre-argument le plus fort" button surfaces. On click:

1. NobuAI reads the article + cluster context.
2. Generates the strongest factual counterargument that another camp would make.
3. Cites specific sources from the same cluster (if available) or notes "no counterargument found in current coverage".
4. Returns 3-paragraph response.

## Prompt design

```
You are presenting the steelman of the opposing position to this article.

Article: <full text>
Cluster context (other coverage of same story): <topic summary>

Generate the strongest counterargument that another political camp would make.
Cite specific facts from the cluster context where possible.
Do NOT introduce facts not present in any source.
Return 3 paragraphs:
  - The opposing position's main claim
  - The strongest evidence for that claim
  - Where the disagreement is rooted (interpretation? values? facts?)
```

## UX impact

- Surfaced as a Pro-tier feature (free tier: 1 view/article/month).
- Per-counterargument source-list shows which cluster sources support it.
- Per-counterargument disclaimer: "Généré par NobuAI à partir de la couverture existante. Ne reflète pas une opinion éditoriale."

## Risk

- NobuAI fabricating counterarguments not in source coverage → mitigated by hallucination check (S1079, partial Wave ZZZZ).
- Counterargument straw-manning the opposing position → mitigated by per-week editor sampling.

## Cross-references

Master plan: S1093. Sister: `docs/GRIMBANEWS_NOBUAI_MULTI_STEP_RESEARCH_MODE_PLAN.md`, `docs/GRIMBANEWS_HALLUCINATION_DETECTOR_PLAN.md`.
