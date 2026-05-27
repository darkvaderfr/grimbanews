# GrimbaNews — Per-Cluster Reader Notes Design

**Status:** plan v0
**Owner:** Alex Morgan (UI/UX) + Liam Smith (PM) + Nina Patel (Lead FE)
**Walks:** Mythos S1377 (per-cluster reader notes) deferred → partial
**Gating dependency:** S1376 notebook schema.

## Why this exists

Clusters are the canonical unit of GrimbaNews — a story is a cluster of articles across sources. Reader notes attached to a cluster (not an article) let a reader build a narrative of how the story evolves over days/weeks, with bias-mix observations preserved alongside their own takes.

## UX

- Cluster page (`/groupes/{slug}`) gains a side rail: "Mes notes sur ce sujet".
- Empty state: "Aucune note. Ajouter votre première observation ?".
- Note composer: small markdown textarea + tag picker (free-form, max 5).
- Notes always associate to the **cluster**, never to a single article (avoids note-orphaning when admin merges/splits clusters).

## Data flow

- Notes stored in `reader_notebook_entries` with `entry_type = 'cluster-note'`, `ref_kind = 'cluster'`, `ref_id = cluster.id`.
- A reader's first note on any cluster auto-creates a default notebook ("Notes rapides") if none exists.
- Re-association on cluster merge: jobs/grimba:cluster-merge writes a meta event so notes follow the surviving cluster id.

## Surrogate today

- Vault save on individual articles (no per-cluster grouping).
- Saved searches (query-level, not cluster-level).

## Cross-references

Master plan: S1377. Sister: S1376 (notebook), S1547 (annotation export), S1378-S1380 (reader product v2 set).
