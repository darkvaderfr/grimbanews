# GrimbaNews — Transparency Report: Open-Data Export Plan

**Status:** plan v0
**Owner:** Benjamin Lee (Data Eng) + Michael O'Connor (Tech Writer) + Maya Patel (Compliance)
**Walks:** Mythos S2013 (annual transparency report — open-data download bundle CSV / JSON) deferred → partial
**Gating dependency:** annual report shipped + privacy-cleared export tooling + storage host.

## Why this exists

The transparency report is more useful as a downloadable dataset (researchers, academics, journalists can re-analyze) than as a PDF alone. Surrogate today is `coffre/export.csv` subscriber export, which is reader-data only.

## v1 export contents

| File | Content |
|---|---|
| `bias-distribution-{year}.csv` | per-month per-bias-bucket count |
| `source-roster-{year}.csv` | sources active during year + factuality/credibility/ownership tier |
| `cluster-counts-{year}.csv` | per-day cluster created/merged/orphaned counts |
| `ingest-throughput-{year}.csv` | per-day articles ingested |
| `corrections-{year}.csv` | per-month corrections issued (anonymized) |
| `ad-decisions-{year}.csv` | per-category ad accept/reject counts (S2007) |
| `methodology-changes-{year}.json` | structured change log (S2010) |
| `manifest.json` | bundle index + license + checksum |
| `README.md` | usage notes + citation guide |

## Distribution

- ZIP bundle published at `/transparency/{year}/data.zip`.
- Mirrored to Zenodo if S2053 DOI registration ships.
- CC-BY-SA-4.0 license on bundle contents.

## Privacy

- Zero per-reader data in bundle.
- Source attribution preserved; per-article body never included.
- Maya Patel review-sign-off before each annual publish.

## Cross-references

Master plan: S2013. Sister: S2001 (full report), S2007 (ad rejections), S2010 (methodology change log), S2053 (DOI).
