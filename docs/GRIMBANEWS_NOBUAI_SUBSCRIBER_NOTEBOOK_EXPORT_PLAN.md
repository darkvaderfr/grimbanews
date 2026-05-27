# GrimbaNews — NobuAI → Subscriber Notebook Export

**Status:** plan v0
**Owner:** Liam Smith (PM) + Nina Patel (Lead FE) + Steve Jobs (CPO)
**Walks:** Mythos S1098 (NobuAI export to subscriber notebook) deferred → partial
**Gating dependency:** Notebook UI does not exist; gates on `docs/GRIMBANEWS_READER_NOTEBOOK_DESIGN.md` (Wave KKKK partial).

## Why this exists

Premium subscribers (per Wave AABB premium-tier plan) use NobuAI features (summary, insight, multi-step research, counterargument). Today, these are read-once. A subscriber notebook lets them save + organize + revisit + export their NobuAI history.

## v1 design

Each NobuAI-generated artifact (summary, insight, research-brief step, counterargument) gets a "Sauvegarder dans mon carnet" button. Click adds to subscriber's notebook.

Notebook UI at `/account/notebook`:
- Per-artifact: title, type, source-cluster, generated-at, full content
- Folders (gates on `docs/GRIMBANEWS_BOOKMARK_FOLDERS_PLAN.md` Wave KKKK)
- Tags (gates on `docs/GRIMBANEWS_BOOKMARK_TAGS_PLAN.md` Wave KKKK)
- Search-within-notebook (gates on `docs/GRIMBANEWS_BOOKMARK_SEARCH_WITHIN_SAVED_PLAN.md` Wave KKKK)

## Export formats

- JSON (full structured data)
- Markdown (human-readable)
- PDF (formatted, gates on PDF generator)
- Per-cluster bundle (notebook entries for a single cluster as a bound document)

## Schema (gates on Vader migration approval)

```
member_notebook:
  id | member_id | artifact_type (summary|insight|research|counter) | source_cluster_id
   | source_post_id | content_json | folder_id | tags | created_at
```

## Privacy

- Notebook private by default.
- Per-entry share-link can be generated (read-only, expires in 30 days).
- DSAR export (Wave KKKK) includes notebook entries.

## Cross-references

Master plan: S1098. Sister: Wave KKKK bookmark + notebook design docs.
