# GrimbaNews — University Press Release Source Roster

**Status:** plan v0
**Owner:** Lucy Leai (Strategy) + Lisa Nguyen (data) + science editor TBD
**Walks:** Mythos S2189 (Science v2 university press-release roster) deferred → partial
**Gating dependency:** Feed-URL research + license review per source.

## Why this exists

University press releases break science stories before mainstream press. EurekAlert! + AlphaGalileo aggregate them. Ingesting these gives GrimbaNews science-first-mover advantage.

## Tier-1 aggregators

- **EurekAlert!** (AAAS — Reuters of science press releases)
  - feed: `eurekalert.org/rss/breakingnews.xml`
  - license: editorial reuse with attribution
- **AlphaGalileo** (Europe-focused science press)
  - feed: `alphagalileo.org/en-gb/About/AlphaGalileo/RSS`
  - license: similar
- **Newswise** (US science + medical)
- **ScienceDaily** (aggregator with NLP framing — secondary, more curated)

## Per-source schema

```
[
    'name' => 'EurekAlert',
    'website' => 'https://www.eurekalert.org',
    'feed_url' => 'https://www.eurekalert.org/rss/breakingnews.xml',
    'editorial_category' => 'sciences',
    'credibility_score' => 88,  // press-release tier
    'factuality_score' => 88,
    'ownership_type' => 'public',  // AAAS is nonprofit
    'license_notes' => 'editorial reuse OK with attribution',
]
```

## Editorial guardrails

- Press releases are publisher-funded marketing AS WELL AS source-of-record. Flag accordingly.
- Per-cluster, prefer downstream peer-reviewed coverage over press-release-only when both exist.
- Per-press-release: surface "Communiqué de presse universitaire" badge.

## Cross-references

Master plan: S2189. Sister: `docs/GRIMBANEWS_PEER_REVIEWED_JOURNAL_COVERAGE_PLAN.md`, `docs/GRIMBANEWS_PREPRINT_SERVER_INGEST_PLAN.md`.
