# GrimbaNews — Warehouse Source Dwell-Time Capture Design (Per-Source Citation Graph Surrogate)

**Sprint ID:** S1734
**Status:** deferred → partial via surrogate documentation
**Master plan row:** `docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1731-s1740 — Warehouse — source dwell-time capture`
**Walk wave:** BBBB

## Gating dependency

Per-source dwell-time capture needs:

- Client-side beacon (heartbeat every Nms while page visible)
- A `dwell_events` table (`post_id`, `source_id`, `member_id|cookie_hash`, `dwell_ms`, `visibility_state`)
- Visibility API integration (pause when tab hidden, resume when foreground)
- Page-Visibility-aware aggregator
- DPIA review (S1855, deferred) — dwell time is per-reader behavioral data
- Per-category consent (S1862, deferred) — likely "analytics" tier
- Warehouse pipeline (S1731-S1740 band, partial)

## Surrogate-now infra

- **`grimba_read` cookie** — coarse per-article presence (read / not-read), no dwell duration
- **`news_sources.factuality_score`** — upstream per-source signal
- **`docs/GRIMBANEWS_CROSS_SOURCE_CITATION_GRAPH_PLAN.md`** — sibling per-source-citation walk (different focus, same data layer)

## Honest framing

Dwell time is privacy-sensitive. The cookie-based per-article presence is sufficient for "did this article reach a reader" and avoids the privacy footprint of millisecond-level tracking.

## Owners

- **Data Eng:** Benjamin Lee — warehouse pipeline + aggregator
- **Frontend:** Nina Patel — beacon implementation
- **Privacy:** Sara Chen + Maya Patel — DPIA + consent
- **Backend:** Rajesh Kumar — beacon endpoint
- **Audit panel:** Zen / Echo / Mnemo at PR time

## Cross-references

- Sprint plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1734 row)
- Warehouse band anchor: `docs/GRIMBANEWS_MYTHOS_S1601_S1800_LOCAL_TOOLS_DATA_EVIDENCE.md#s1731-s1740`
- Cross-source citation graph sibling: `docs/GRIMBANEWS_CROSS_SOURCE_CITATION_GRAPH_PLAN.md`
- DPIA / consent gates: `docs/GRIMBANEWS_MYTHOS_S1801_S2000_COMPLIANCE_INFRA_EVIDENCE.md#s1851-s1870`
- Roster: `/Users/vb/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
