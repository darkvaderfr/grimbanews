# GrimbaNews — SOC 2 Audit Scope Definition

**Status:** plan v0
**Owner:** Sara Chen (CISO) + audit firm (when engaged)
**Walks:** Mythos S1812 (SOC 2 audit scope definition) deferred → partial
**Gating dependency:** S1811 audit firm engaged.

## Audit scope (Type I baseline)

### Trust Service Criteria included
- **Security:** mandatory baseline.
- **Availability:** for SaaS / customer-facing systems.
- **Confidentiality:** if customer data classified.
- **Processing Integrity:** for B2B API + transactional flows.
- **Privacy:** if customer PII processed.

### Systems in scope
- GrimbaNews production VPS + DB.
- /api/middle-ground.json + /api/middle-ground.atom endpoints.
- Newsletter delivery pipeline (Wave SUB-25).
- B2B API tier (Wave AABB) — when launched.
- Admin authentication + access control.

### Systems out of scope (rationale documented)
- Marketing site analytics.
- Public RSS feeds (no customer data).
- Third-party hosted services (HubSpot, Mailgun) — relied upon, not audited.

## Per-criteria evidence requirements

Per Wave SUB-35 evidence vault: per-control evidence collected continuously.

## Per-scope-change documentation

- Per-quarter scope review.
- Per-change rationale logged.
- Per-change audit-firm coordination.

## Cross-references

Master plan: S1812. Sister: `docs/GRIMBANEWS_SOC2_CONTROL_MAP.md` (Wave LLL), `docs/GRIMBANEWS_SOC2_AUDIT_FIRM_ENGAGEMENT.md`.
