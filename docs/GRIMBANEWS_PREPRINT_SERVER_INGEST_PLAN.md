# GrimbaNews — Preprint Server Ingest (arXiv / bioRxiv / medRxiv)

**Status:** plan v0
**Owner:** Rajesh Kumar (backend) + Lisa Nguyen (data) + science editor TBD
**Walks:** Mythos S2186 (Science v2 preprint-server integration) deferred → partial
**Gating dependency:** Ingest adapter — preprint servers are not RSS-native; need API integration.

## Why this exists

Preprints drive 50%+ of science journalism today but are noisy (no peer review). Capturing them gives GrimbaNews early-warning on emerging science stories WHILE properly flagging "preprint = not peer-reviewed."

## v1 adapter

Per preprint server:

- **arXiv:** OAI-PMH harvesting via `arxiv.org/oai2`. Daily batch.
- **bioRxiv:** REST API at `api.biorxiv.org/details/biorxiv/`. Daily batch.
- **medRxiv:** REST API at `api.biorxiv.org/details/medrxiv/`. Daily batch.

`grimba:fetch-preprints --server={arxiv|biorxiv|medrxiv}` Artisan command.

## Schema (new column on posts)

```
posts.is_preprint BOOLEAN DEFAULT FALSE
posts.preprint_doi VARCHAR(255) NULL
posts.preprint_version INT NULL
```

## Reader UX

- Per-preprint article card carries amber "Preprint — non peer-reviewed" badge.
- Methodology cross-link explaining preprint vs peer-reviewed.
- When peer-reviewed version published, soft-redirect to peer-reviewed cluster.

## Per-discipline filter

Reader can opt-in to per-discipline preprint feed (CS, physics, biology, medicine, etc.) in `/account/preferences`.

## Volume management

arXiv ships ~5000 preprints/day across all disciplines. Filter to per-cluster-relevance via topic-similarity to existing GrimbaNews clusters. Avoid drowning corpus.

## Cross-references

Master plan: S2186. Sister: `docs/GRIMBANEWS_VECTOR_EMBEDDINGS_STORE_PLAN.md` (for topic-similarity filtering), Wave UUUU science-band docs.
