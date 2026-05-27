# GrimbaNews — Election-Period Editorial Guardrails

**Status:** plan v0
**Owner:** Lucy Leai (Strategy) + Sara Chen (CISO) + per-region editor
**Walks:** Mythos S1481 (election-period editorial guardrails) deferred → partial
**Gating dependency:** Per-region election calendar (operator-curated).

## Why this exists

During elections, editorial-balance scrutiny is intense. France: 75 days before round-1 of presidential election, broadcaster speaking-time-per-candidate is legally regulated. Brazil: TRE rules. Various countries: similar.

GrimbaNews aggregation pattern needs heightened guardrails during these windows to:
- Avoid amplifying disinformation
- Ensure source-roster bias balance per electoral camp
- Block sponsored content / advocacy ads from election surfaces

## v1 guardrails

Operator marks an election period in `election_periods` table:

```
country | election_type | period_start | period_end | candidates JSON | guardrail_level (advisory|enforced)
```

During an active period:

1. **Source-roster balance check daily** — flag if any electoral camp's coverage > 60% of cluster volume.
2. **Per-cluster automated fact-check spike** — bypass NobuAI summary caching; force re-run with extra hallucination check (Wave ZZZZ).
3. **Sponsored-content lockout** — `posts.is_sponsored=true` posts hidden from /breaking + /latest during the period.
4. **Per-candidate quotation tracking** — surfaces "Candidate X quoted N times in election-period clusters vs Y for Candidate Z" on transparency dashboard.
5. **Election-period banner** at top of /comparatif/{id} for election-tagged clusters: "Dossier en période électorale — équilibre éditorial surveillé."

## Editor cadence

- Daily 09:00 UTC: Lucy + per-region editor review previous-day balance report.
- Per-incident: ad-hoc spike on viral story.
- Post-election: 14-day retro per `docs/GRIMBANEWS_LAUNCH_RETROSPECTIVE_TEMPLATE.md`.

## Cross-references

Master plan: S1481. Sister: `docs/GRIMBANEWS_SPONSORED_CONTENT_DETECTOR_PLAN.md`, `docs/GRIMBANEWS_HALLUCINATION_DETECTOR_PLAN.md`.
