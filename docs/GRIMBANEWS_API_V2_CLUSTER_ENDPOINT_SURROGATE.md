# GrimbaNews — API v2 Cluster Endpoint Surrogate Plan

**Sprint ID:** S1243
**Status:** deferred → partial via surrogate documentation
**Master plan row:** `docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1241-s1250 — Cluster endpoint`
**Walk wave:** CCCC

## Gating dependency

A `/api/v2/clusters` endpoint needs:

- v2 contract wrapper (S1239)
- Cluster public-projection mapper (members count, bias mix, dossier voices, confidence)
- Field-selection contract honored (large clusters can ship 50+ members)
- Cursor pagination (gates on S1244-class consistency)
- Billing meter (gates on S1254)

The cluster engine already produces this exact projection for `/story/{slug}` server-rendered pages.

## Surrogate-now infra

- **`app/Models/StoryCluster`** — fully populated; ready model
- **`app/Support/GrimbaDossierVoices`** — produces per-cluster voice mix from member articles
- **`app/Support/GrimbaClusterBias`** — produces per-cluster bias-mix payload
- **`/story/{slug}` HTML** — server-renders the full projection a B2B partner would consume
- **`grimba:nobuai-summaries` cmd** — produces the same `summary_nobuai` field every 30min that an API would surface

## Honest framing

Highest-leverage B2B endpoint after sources. A trust-and-safety vendor or research lab gets immediate value from `/clusters?fresh=24h&min_sources=3`. Same gating as S1241/S1242 — needs the v2 wrapper + meter, not new business logic.

## Owners

- **Product:** Liam Smith — endpoint scope + paginator decision
- **Backend:** Rajesh Kumar — `ClustersController@index` + presenter
- **Data:** David Chen — confidence-field contract
- **Platform:** Hannah Kim — cursor pagination + rate ladder
- **Audit panel:** Zen / Echo / Mnemo at PR time

## Cross-references

- Sprint plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1243 row)
- Cluster engine OSS scope: S2071-S2080 (deferred)
- Roster: `/Users/vb/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
