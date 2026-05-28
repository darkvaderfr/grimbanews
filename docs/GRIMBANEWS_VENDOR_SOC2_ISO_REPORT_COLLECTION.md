# GrimbaNews — Vendor SOC 2 / ISO 27001 Report Collection Plan

**Status:** plan v0
**Owner:** Sara Chen (CISO) + per-vendor relationship owner
**Walks:** Mythos S1875 (vendor SOC 2 / ISO 27001 report collection) deferred → partial
**Gating dependency:** Vendor risk-tier classification (Wave SUB-50) — critical + high tiers require attestation.

## Why this exists

Vendor risk-tier classification (Wave SUB-50) requires critical-tier vendors to have a current SOC 2 Type II or ISO 27001 certificate. We need a formal intake + storage + expiration-tracking process so the CISO can answer "are all critical vendors covered?" without manual spelunking.

## Per-tier requirement

| Tier | Required attestation | Renewal cadence | Storage location |
|---|---|---|---|
| Critical | SOC 2 Type II (12-month window) OR ISO 27001 (current cert) | Annual | Encrypted /admin/grimba/vendor-attestations |
| High | SOC 2 Type I (acceptable) OR ISO 27001 | Bi-annual | Same |
| Medium | Self-attested security questionnaire (no third-party report required) | Tri-annual | Same |
| Low | None required | n/a | n/a |

## Collection workflow

1. Per-vendor request sent during onboarding (Wave SUB-50 sister surface).
2. Per-vendor PDF uploaded by relationship owner.
3. Per-attestation metadata captured: vendor, type, issuer, effective_date, expiration_date, scope.
4. Per-expiration auto-alert 90 / 30 / 7 days before expiry.
5. Per-expired-attestation → vendor flagged for renewal or replacement.

## Schema (pending Vader migration approval)

```
vendor_attestations:
  id | vendor_id | attestation_type (soc2_type1|soc2_type2|iso27001|self_attest)
   | issuer (e.g., "Schellman", "BSI") | effective_date | expiration_date
   | scope_description | pdf_storage_path | uploaded_by | uploaded_at
```

## Per-attestation review checklist

- Scope covers the service we consume (not a sibling product).
- Trust services criteria include security at minimum.
- Auditor opinion is "unqualified" (or qualifications reviewed + accepted).
- Sub-service organizations reviewed (carve-out vs inclusive).

## Cross-references

Master plan: S1875. Sister: `docs/GRIMBANEWS_VENDOR_RISK_TIER_CLASSIFICATION.md` (Wave SUB-50), `docs/GRIMBANEWS_VENDOR_SECURITY_QUESTIONNAIRE_INTAKE.md` (Wave SUB-50).
