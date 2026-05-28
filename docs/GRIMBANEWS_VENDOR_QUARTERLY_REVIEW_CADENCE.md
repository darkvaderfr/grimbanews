# GrimbaNews — Vendor Quarterly Review Cadence

**Status:** plan v0
**Owner:** Sara Chen (CISO) + per-vendor relationship owner
**Walks:** Mythos S1878 (vendor quarterly review cadence) deferred → partial
**Gating dependency:** Vendor register (Wave LLL), tier classification (SUB-50), attestations (SUB-51), incident clauses (SUB-51).

## Why this exists

Vendor onboarding is necessary but not sufficient. Risk drifts: vendor adds a sub-processor, an attestation expires, an incident is disclosed in the news. A quarterly review forces a recurring touch-point where each critical/high vendor is re-evaluated against current criteria.

## Per-quarter agenda

### Critical-tier (every quarter)

For each critical vendor:
- Per-vendor attestation status (current vs expired).
- Per-vendor open incidents (any disclosed since last review).
- Per-vendor sub-processor changes (any additions, locations, scope).
- Per-vendor contractual exposure (renewal date, termination triggers).
- Per-vendor performance issues raised by relationship owner.
- Per-vendor news scan (acquisitions, breaches, layoffs affecting the team we work with).

### High-tier (every 2 quarters)

Lighter version of the above: attestation + incidents + news scan only.

### Medium-tier (annually)

Self-attest questionnaire renewal cycle (per SUB-50).

### Low-tier (per-incident only)

No standing review.

## Per-review output

A short markdown record per vendor, filed under `/admin/grimba/vendor-reviews/YYYY-Q#/`:

```
# Vendor Quarterly Review — <Vendor> — YYYY-Q#

- Tier: critical|high
- Attestation: current (expires YYYY-MM-DD) | expired
- Open incidents disclosed since last review: 0 | list
- Sub-processor changes: none | list with dates
- Renewal date: YYYY-MM-DD
- Owner-reported performance issues: none | list
- News scan flags: none | list
- Decision: continue | escalate | replace
- Action items: list with owner + due
```

## Per-meeting cadence

- Q1 review: first week of February (post-EOY close).
- Q2 review: first week of May.
- Q3 review: first week of August.
- Q4 review: first week of November.

CISO chairs. Each relationship owner attends for their vendors. ~30 min per critical vendor, ~10 min per high-tier vendor (when in-scope).

## Per-escalation routing

- "Escalate" decisions → exec council meeting.
- "Replace" decisions → exec council vote (per SUB-50 risk-tier impact).

## Cross-references

Master plan: S1878. Sister: `docs/GRIMBANEWS_VENDOR_RISK_TIER_CLASSIFICATION.md` (SUB-50), `docs/GRIMBANEWS_VENDOR_SOC2_ISO_REPORT_COLLECTION.md` (SUB-51), `docs/GRIMBANEWS_VENDOR_INCIDENT_NOTIFICATION_CLAUSES.md` (SUB-51), `docs/GRIMBANEWS_VENDOR_TERMINATION_DATA_RETURN.md` (SUB-51).
