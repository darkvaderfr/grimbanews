# GrimbaNews — Methodology: Cluster-Merge Algorithm Extraction

**Status:** plan v0 (algorithm lives in `GrimbaRssPoller::findOrFormCluster()`; not yet OSS-ready)
**Owner:** Rajesh Kumar (Backend) on code extraction + Michael O'Connor (Technical Writer) on rubric documentation + Vader on license-clear
**Walks:** Mythos S2047 (Methodology repo — cluster-merge algorithm) deferred → partial
**Gating dependency:** OSS license (S2042) + scope decision (S2041) + internal-notes scrub.

## Why this exists

S2047 extracts the cluster-merge algorithm for OSS publication. Currently the logic is tightly coupled to Laravel ORM + posts schema; needs schema-neutral form.

## Today's surrogate

- `app/Services/GrimbaRssPoller::findOrFormCluster()` — internal, Laravel-coupled.

## Extraction targets

1. **Algorithm pseudocode** (language-agnostic):
```
function find_or_form_cluster(post):
    candidates = posts published within 72h
                  with same coarse-topic
                  and high title-similarity (>0.6 Jaccard / cosine)
                  and matching canonical URL OR similar URL slug
    if best_candidate.score > 0.8:
        return best_candidate.cluster_id
    else:
        return new_cluster_id()
```

2. **Title-similarity rubric** — Jaccard vs Cosine vs Levenshtein justification.
3. **Time-window choice** — why 72h, with rationale for tuning.
4. **Canonical-URL handling** — when same URL is republished by aggregator.
5. **Locale-aware merging** — multilingual cluster handling.

## Documentation deliverables

- `cluster-merge.md` — rubric explanation (CC-BY).
- `cluster-merge.py` — reference implementation (Apache 2.0).
- `cluster-merge-tests/` — public test fixtures.
- `cluster-merge-decisions/` — ADR-style decision records.

## Internal-notes scrub

- Remove operator-specific config (specific source IDs, internal metric thresholds).
- Replace with parameterized defaults + tuning guide.

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S2047)
- Sister docs: `docs/GRIMBANEWS_METHODOLOGY_OSS_LICENSE_SELECTION.md`, `docs/GRIMBANEWS_METHODOLOGY_DEDUP_RULES_EXTRACTION.md`
- Existing infra: `App\Services\GrimbaRssPoller`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
