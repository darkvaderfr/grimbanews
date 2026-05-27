# GrimbaNews — Investigations Companion Data Publication

**Status:** plan v0
**Owner:** Lisa Nguyen (data) + Lucy Leai (Strategy)
**Walks:** Mythos S2211 (Long-form investigations companion data publication) deferred → partial
**Gating dependency:** Overlaps S2013 (transparency-data export); this doc captures investigation-specific data publish workflow.

## Why this exists

Per-investigation underlying data should be reader-accessible: rebuilds trust + enables academic verification. Editorial-leak risk for sensitive data managed via redaction policy.

## v1 design

Per-investigation, alongside the article:
- Raw-data CSV / Parquet (PII-redacted)
- Analysis notebook (Jupyter)
- Per-row source citation
- Per-table dictionary
- License: CC-BY-4.0 (attribution required)

## Redaction policy

- PII (names, addresses, contact info): redacted to placeholder.
- Confidential business info: redacted unless public-record.
- Source-protection: any identifiable source detail redacted.
- Counsel review of redaction list before publish.

## Hosting

- Companion data lives under `/investigations/{slug}/data/`
- Per-data-file SHA256 published for integrity verification
- Zenodo DOI assigned for academic citation (optional per investigation)

## Cross-references

Master plan: S2211. Sister: `docs/GRIMBANEWS_INVESTIGATIONS_DATA_PIPELINE_PLAN.md`, S2013 transparency-data export.
