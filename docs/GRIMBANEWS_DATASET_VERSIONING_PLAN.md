# GrimbaNews — Dataset Versioning Plan

**Status:** plan v0
**Owner:** Lisa Nguyen (data) + Larry Ellison (DBA)
**Walks:** Mythos S1689 (dataset versioning) deferred → partial
**Gating dependency:** Current CSV exports are point-in-time; `dataset_versions` table needs migration approval.

## Why this exists

Public datasets (API + CSV exports) evolve over time as the corpus grows + ledger updates. Researchers + downstream consumers need stable per-version snapshots for reproducibility.

## Schema (gates on Vader migration approval)

```
dataset_versions:
  id | dataset_slug (e.g. 'middle-ground') | version_label (semver: 1.2.3)
   | snapshot_at | row_count | sha256 | snapshot_url
```

## Per-version cadence

- Monthly snapshot: `dataset/v{N}/middle-ground-{YYYY-MM}.json`.
- Per-version SHA256 published for integrity verification.
- Per-version retention: last 24 months hot + indefinite cold.

## Per-version changelog

`docs/datasets/{slug}/CHANGELOG.md` per dataset:
- v1.2.3 (YYYY-MM-DD): schema additions / removals + breaking notes.

## Per-version reader UX

- `/api/middle-ground/v1.2.3.json` for version-pinned access.
- Default `/api/middle-ground.json` is latest.
- Per-version OG card + Schema.org Dataset block.

## Cross-references

Master plan: S1689. Sister: `docs/GRIMBANEWS_MIDDLE_GROUND_API_REFERENCE.md`, `docs/GRIMBANEWS_DATASET_CSV_SCHEMA.md` (Wave LLL).
