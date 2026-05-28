# GrimbaNews — PCI DSS Quarterly ASV Scan

**Status:** plan v0
**Owner:** Sara Chen (CISO) + Jacob Lee (DevOps)
**Walks:** Mythos S1845 (PCI DSS quarterly ASV scan) deferred → partial
**Gating dependency:** SAQ-A requires quarterly ASV (Approved Scanning Vendor) scan even for outsourced card processing.

## ASV scan scope

Per SAQ-A v4: external-facing systems (web servers, network perimeter) scanned by PCI-approved vendor quarterly.

## ASV vendor options

- **Qualys PCI:** $300-1500/year per IP. Premier choice.
- **Tenable.io PCI:** similar range. Strong reporting.
- **SecurityMetrics:** SMB-focused, ~$300-800/year.
- **Trustwave:** larger enterprise.

## Per-quarter cadence

- Quarter start: ASV scan initiated.
- Per-finding: Sara + Jacob remediate within 30 days.
- Per-rescan: confirm finding resolved.
- Per-quarter compliant report submitted to acquirer.

## Per-finding response SLA

- Critical: 7 days.
- High: 30 days.
- Medium: per-policy.
- Low: per-policy.

## Per-quarter audit trail

Per-scan report + per-finding remediation evidence archived in PCI evidence vault (per Wave SUB-35 SOC 2 vault pattern).

## Cross-references

Master plan: S1845. Sister: `docs/GRIMBANEWS_PCI_DSS_SAQ_SELECTION.md`, `docs/GRIMBANEWS_VULNERABILITY_SCAN_RUNBOOK.md` (Wave LLL).
