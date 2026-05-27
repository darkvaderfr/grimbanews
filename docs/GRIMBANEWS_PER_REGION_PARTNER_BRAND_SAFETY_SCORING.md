# GrimbaNews — Per-Region Partner Brand-Safety Scoring

**Status:** plan v0
**Owner:** Lucy Leai (Strategy) + Sara Chen (CISO)
**Walks:** Mythos S1665 (per-region partner brand-safety scoring) deferred → partial
**Gating dependency:** Partner program active + ad-network onboarding.

## Why this exists

Ad partners (programmatic networks, brand-direct) need per-cluster brand-safety scoring: "is this dossier safe to monetize next to?" Standard categories: violence, hate, adult, election-period (per Wave AAEE), controversy.

## v1 design

Per-cluster brand-safety score 0-100 computed by:
1. Keyword-match against per-category lists
2. NobuAI judge for ambiguous cases (Wave UUUU)
3. Override by editor for known-edge clusters

Score exposed via:
- `/api/clusters/{id}/brand-safety.json` (B2B Pro+ tier per AABB)
- Editorial dashboard

## Categories

- Violence: 0-100
- Hate/discrimination: 0-100
- Adult content: 0-100
- Election period: bool (per Wave AAEE)
- Controversial figure: bool (operator-curated)
- Disinformation-adjacent: bool

## Per-partner thresholds

Each ad partner configures their tolerance:
- Tier-1 brands: only clusters with all scores < 20
- Tier-2 brands: all < 50
- Tier-3: all < 80
- Permissive: < 100 (everything except above 90)

## Cross-references

Master plan: S1665. Sister: `docs/GRIMBANEWS_ELECTION_PERIOD_EDITORIAL_GUARDRAILS.md`, `docs/GRIMBANEWS_SPONSORED_CONTENT_DETECTOR_PLAN.md`.
