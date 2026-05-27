# GrimbaNews — Trust Project Indicators Integration

**Status:** plan v0
**Owner:** Lucy Leai (Strategy) + Sara Chen (CISO)
**Walks:** Mythos S1713 (Trust Project indicators integration) deferred → partial
**Gating dependency:** Trust Project organizational onboarding + per-article markup.

## Why this exists

The Trust Project (thetrustproject.org) is a global standard for journalistic transparency markers. Adopting their 8-indicator framework signals editorial accountability + integrates GrimbaNews with broader trust ecosystem.

## The 8 Trust Indicators

1. **Best practices:** Publisher's editorial policies.
2. **Author expertise:** Per-byline qualifications.
3. **Type of work:** News / opinion / analysis / sponsored.
4. **Citations + references:** Source-link transparency.
5. **Methods:** How story was reported.
6. **Locally sourced:** Local-perspective indicator.
7. **Diverse voices:** Multiple-source inclusion.
8. **Actionable feedback:** Reader-feedback channel.

## GrimbaNews mapping

- 1: methodology page (already shipped)
- 2: per-author profile (Wave DDDD)
- 3: opinion-vs-news classifier (Wave ZZZZ partial)
- 4: per-article source-link preservation (already in ingest)
- 5: NobuAI summary methodology cross-link (already shipped)
- 6: per-cluster `editorial_region` (already shipped)
- 7: cluster aggregation IS this (already the product)
- 8: reader feedback widget (Wave AAMM)

## Per-article markup

Schema.org NewsArticle + Trust Project `tp:trustIndicators` array on every article page.

## Cross-references

Master plan: S1713. Sister: `docs/GRIMBANEWS_TRANSPARENCY_REPORT_SCOPE.md` (Wave LLL).
