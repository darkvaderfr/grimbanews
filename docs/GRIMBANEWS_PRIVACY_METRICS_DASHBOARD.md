# GrimbaNews — Privacy-Program Metrics Dashboard

**Status:** plan v0
**Owner:** Sara Chen (CISO) + Lisa Nguyen (data)
**Walks:** Mythos S1869 (privacy-program metrics dashboard) deferred → partial
**Gating dependency:** Consent log + DSAR register + per-category consent toggles.

## Dashboard metrics

`/admin/grimba/privacy-metrics`:

### Consent metrics
- Per-banner-impression: shown count.
- Per-banner-accept rate (per-category).
- Per-banner-reject rate.
- Per-banner-customize rate.
- Per-locale opt-in rate variance.

### DSAR metrics
- Per-quarter DSAR request count.
- Per-quarter DSAR SLA compliance %.
- Per-quarter DSAR-type breakdown (access / erasure / portability / etc.).
- Per-quarter DSAR-resolution-time median.

### Breach / incident metrics
- Per-quarter privacy-incident count.
- Per-quarter breach-notification SLA compliance.
- Per-incident affected-data-subject count.

### Vendor metrics
- Per-quarter vendor DPA + SCC coverage %.
- Per-quarter per-vendor security-attestation freshness.

### Per-reader-rights metrics
- Per-quarter `/vos-droits` page-views.
- Per-quarter DSAR-conversion (page-view → submission).

## Per-quarter Sara + counsel review

- Per-metric trend.
- Per-pattern surfacing (e.g. spike in DSAR after marketing campaign).

## Public-facing privacy transparency report

Annual aggregated metrics published in `/transparence/privacy` (per Wave LLL transparency report scope).

## Cross-references

Master plan: S1869. Sister: `docs/GRIMBANEWS_CONSENT_LOG_DESIGN.md` (Wave LLL), `docs/GRIMBANEWS_GDPR_DSAR_WORKFLOW.md`.
