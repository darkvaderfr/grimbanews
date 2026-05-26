# GrimbaNews — Per-Topic Editorial Brief Template

**Status:** template v0
**Owner:** Steve Jobs (CPO) + Lucy Leai (Strategy) + per-topic editor (TBD per topic)
**Walks:** Mythos S1032 (per-topic editorial brief) deferred → partial
**Gating dependency:** v2 taxonomy (S1031) shipped + per-topic editor hired.

## Use

One brief per v2 topic bucket (40 total per `docs/GRIMBANEWS_TOPIC_TAXONOMY_V2_DESIGN.md`). Lives in `docs/editorial-briefs/<topic-slug>.md`. Editor signs off on the brief; it's the source of truth for "what does this topic mean at GrimbaNews."

## Template

```
# GrimbaNews — Editorial Brief: <Topic Name>

**Effective:** YYYY-MM-DD
**Editor:** <name>
**Review cadence:** quarterly

## Scope (what's in)
- ...

## Out-of-scope (what's NOT in this bucket)
- ...

## Tone + style
- Voice:
- Length cap:
- Quote-attribution preference:

## Source weighting
- Primary: <named sources>
- Secondary: <named sources>
- Avoid: <sources flagged for accuracy issues>

## Bias-balance target
- L/C/R coverage ratio: <e.g. 30/40/30>
- Acceptable drift: <±5%>

## Cluster bias-tagging notes
- Specific terms to watch (e.g. "élite", "système", "réforme") that may swing classifier
- Override notes for the source classifier

## Cross-topic interaction
- Stories that span this bucket + another: which gets primary tag?

## Reader signals
- KPIs: average read time, return rate, share rate
- Red flags: high bounce, low scroll depth

## Editorial-style guide cross-refs
- ...
```

## Onboarding cadence

1. Editor drafts brief.
2. Lucy + Steve review.
3. Counsel reviews if topic touches legal-risk area (Justice, Cybersécurité, Géopolitique).
4. Brief becomes the per-topic source of truth.
5. Quarterly editor review; revise as needed.

## Cross-references

Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1032).
Sister: `docs/GRIMBANEWS_TOPIC_TAXONOMY_V2_DESIGN.md`, `docs/GRIMBANEWS_EDITORIAL_STYLE_GUIDE.md`.
