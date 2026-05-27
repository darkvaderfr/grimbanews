# GrimbaNews — API Academic Citation Requirement

**Status:** plan v0
**Owner:** Lucy Leai (Strategy) + Michael O'Connor (Technical Writer)
**Walks:** Mythos S1697 (academic citation requirement) deferred → partial
**Gating dependency:** S1693 academic-tier license terms.

## Why this exists

Academic license requires citation when GrimbaNews data informs published research. Standard for open-data projects.

## Required citation format

### BibTeX
```bibtex
@misc{grimbanews_dataset_YYYY,
  author = {{GrimbaNews}},
  title = {GrimbaNews Middle Ground Signal Dataset},
  year = {YYYY},
  version = {N.N.N},
  url = {https://grimbanews.com/api/middle-ground.json},
  doi = {ZenodoDOIPlaceholder},
}
```

### APA
GrimbaNews. (YYYY). *GrimbaNews Middle Ground Signal Dataset* (Version N.N.N) [Dataset]. Iboga Ventures. https://grimbanews.com/api/middle-ground.json

### Chicago
GrimbaNews. *GrimbaNews Middle Ground Signal Dataset.* Version N.N.N. Paris: Iboga Ventures, YYYY.

## License linkage

- Free open access for academic use.
- CC-BY-4.0 (per Wave SSSS Schema.org Dataset block).
- Attribution required in publication; non-attribution use = license violation.

## Citation tracking

Per-citation in academic literature surfaces via:
- Google Scholar alerts on "GrimbaNews".
- Zenodo DOI tracking.
- Per-quarter Lucy review of new citations.

## Per-citation reciprocity

Cited authors receive:
- Optional GrimbaNews "Academic Citer" badge on /transparence page.
- Per-paper publish notification when paper goes open-access.

## Cross-references

Master plan: S1697. Sister: `docs/GRIMBANEWS_API_ACADEMIC_TIER_DOCS.md`, `docs/GRIMBANEWS_MIDDLE_GROUND_API_REFERENCE.md`.
