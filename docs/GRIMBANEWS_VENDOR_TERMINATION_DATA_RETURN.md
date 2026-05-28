# GrimbaNews — Vendor Termination + Data-Return Clauses

**Status:** plan v0
**Owner:** Sara Chen (CISO) + Michael O'Connor (Legal) + per-vendor relationship owner
**Walks:** Mythos S1877 (vendor termination + data-return clauses) deferred → partial
**Gating dependency:** Master Services Agreement template; Wave SUB-51 sister attestation collection in place.

## Why this exists

When a vendor relationship ends (either side, any reason), we need contractual rights to (a) get our data back in a usable format and (b) verify vendor destruction of residual copies. Without this, vendor failure or acquisition can strand GrimbaNews data permanently or leave it on someone else's infrastructure long after termination.

## Required clause text (all tiers receiving GrimbaNews data)

```
Upon termination or expiration of this Agreement, Vendor shall, at GrimbaNews's
written direction within thirty (30) days:

(a) RETURN: Provide all GrimbaNews Customer Data in a machine-readable,
    industry-standard format (CSV, JSON, or vendor-native export with documented
    schema). Delivery via secure channel agreed by both parties.

(b) DESTROY: Securely delete all GrimbaNews Customer Data from Vendor's
    production systems, backups, and any sub-processor systems, within sixty
    (60) days of the return-or-delete instruction. Vendor shall provide written
    Certificate of Destruction signed by an authorized Vendor representative.

(c) RETAIN: Vendor may retain anonymized, aggregated data and data required
    for legal compliance (tax records, litigation hold, regulatory inquiries),
    provided such retention is documented and minimized.

Vendor's transition assistance during the wind-down period (up to ninety
(90) days) shall be billed at then-current rates.
```

## Per-tier transition support

| Tier | Transition window | Migration help | Penalty for non-compliance |
|---|---|---|---|
| Critical | 90 days | Vendor-led with named PM | Liquidated damages clause |
| High | 60 days | Vendor cooperation, our PM | Material breach |
| Medium | 30 days | Self-service export | Material breach |
| Low | 30 days | Self-service export | Standard remedies |

## Per-termination playbook

1. Per-termination notice triggers internal data-inventory query: "what does this vendor hold?"
2. Per-vendor RETURN request issued in writing on Day 0.
3. Per-vendor export received + verified within 30 days.
4. Per-vendor DESTROY request issued on Day 31.
5. Per-vendor Certificate of Destruction received by Day 90.
6. Per-vendor record archived in vendor register (Wave LLL).

## Per-vendor MSA insertion checklist

1. Per-vendor MSA reviewed for existing termination clause.
2. Per-vendor missing or weaker clause → addendum proposed.
3. Per-vendor signed addendum → stored with attestation.
4. Per-vendor refusal → CISO risk-acceptance OR replacement.

## Cross-references

Master plan: S1877. Sister: `docs/GRIMBANEWS_VENDOR_INCIDENT_NOTIFICATION_CLAUSES.md`, `docs/GRIMBANEWS_VENDOR_SOC2_ISO_REPORT_COLLECTION.md`, `docs/GRIMBANEWS_VENDOR_REGISTER.md` (Wave LLL).
