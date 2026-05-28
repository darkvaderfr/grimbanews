# GrimbaNews — Vendor Incident-Notification Clauses

**Status:** plan v0
**Owner:** Sara Chen (CISO) + Michael O'Connor (Legal)
**Walks:** Mythos S1876 (vendor incident-notification clauses) deferred → partial
**Gating dependency:** Vendor SOC 2 / ISO collection (Wave SUB-51 sister); Master Services Agreement template.

## Why this exists

When a vendor that touches GrimbaNews data has a security incident (breach, ransomware, sub-processor failure), we need contractual obligation to be told within a window short enough for our own GDPR / SOC 2 obligations to be met. GDPR Art. 33 requires a controller to notify the supervisory authority within 72 hours — we cannot do that if our processor takes 14 days to tell us.

## Required clause text (per-tier minimums)

### Critical-tier vendors (SLA: ≤ 24 hours)

```
Vendor shall notify GrimbaNews's Chief Information Security Officer in writing
(security@grimbanews.com) of any actual or reasonably suspected Security
Incident affecting GrimbaNews data, systems, or services within twenty-four
(24) hours of Vendor's first awareness. Notification shall include: nature of
the incident, data categories affected, approximate number of records or data
subjects, current containment status, and a designated incident contact.
```

### High-tier vendors (SLA: ≤ 48 hours)

Same body, 48-hour window.

### Medium-tier vendors (SLA: ≤ 72 hours)

Same body, 72-hour window (matches GDPR Art. 33 outer bound).

### Low-tier vendors

Best-effort notification (no SLA), via standard support channel.

## Per-incident vendor obligations

Beyond notification:
- Per-incident root cause analysis delivered within 30 days.
- Per-incident cooperation with our investigation (logs, timeline, access).
- Per-incident remediation evidence (patch deployed, key rotated, etc.).
- Per-incident regulatory cooperation if joint controllership applies.

## Per-vendor MSA insertion checklist

1. Per-vendor MSA / DPA reviewed for existing incident clause.
2. Per-vendor missing clause → addendum proposed using template above.
3. Per-vendor signed addendum → stored alongside attestation in /admin/grimba/vendor-attestations.
4. Per-vendor refusal → escalate to CISO for risk-acceptance decision OR vendor replacement.

## Per-quarter audit

- Per-quarter spot-check 3 random vendors for clause presence + signed addendum on file.
- Per-quarter missing → flag for legal follow-up.

## Cross-references

Master plan: S1876. Sister: `docs/GRIMBANEWS_VENDOR_SOC2_ISO_REPORT_COLLECTION.md`, `docs/GRIMBANEWS_VENDOR_REGISTER.md` (Wave LLL), `docs/GRIMBANEWS_INCIDENT_RESPONSE_PLAN.md`.
