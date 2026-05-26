# GrimbaNews — Methodology: Dedup Rules Extraction

**Status:** plan v0 (rules live in `GrimbaArticleDedupe`; not yet OSS-extracted)
**Owner:** Rajesh Kumar (Backend) on extraction + Michael O'Connor on doc + Vader on license-clear
**Walks:** Mythos S2048 (Methodology repo — dedup rules) deferred → partial
**Gating dependency:** OSS license (S2042) + cluster-merge extraction (S2047) — sister algorithm.

## Why this exists

S2048 publishes the dedup rule set. Dedup decisions (which articles are "the same" vs distinct coverage) directly impact cluster quality and the public-facing dossier.

## Today's surrogate

- `app/Services/GrimbaArticleDedupe` (when finalized) + canonical URL + title similarity.

## Extraction targets

1. **Canonical URL rule** — strip tracking params, normalize trailing slash.
2. **Title similarity threshold** — 0.85 cosine similarity defaults.
3. **Body fingerprint (sim-hash)** — for syndicated reprints with different titles.
4. **Source-of-truth selection** — when 2 sources publish "same" article, which wins as canonical:
   - Earliest `published_at`
   - Highest `news_sources.factuality_score`
   - First-seen in our pipeline (tiebreak)
5. **Edge cases:**
   - Wire-service syndication (AP, AFP, Reuters)
   - Translation of same article into different locale
   - Original vs aggregator-version

## Documentation deliverables

- `dedup-rules.md` — rubric with worked examples (CC-BY).
- `dedup-rules.py` — reference implementation (Apache 2.0).
- `dedup-fixtures/` — public corpus of dedup pairs with expected outcomes.
- ADRs for non-obvious rules (translation handling, etc.).

## Internal-notes scrub

- Remove operator-specific source-priority lists.
- Replace with parameterized config + tuning guide.

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S2048)
- Sister docs: `docs/GRIMBANEWS_METHODOLOGY_CLUSTER_MERGE_EXTRACTION.md`, `docs/GRIMBANEWS_METHODOLOGY_OSS_LICENSE_SELECTION.md`
- Existing infra: `GrimbaArticleDedupe`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
