# GrimbaNews — SOC 2 Evidence Vault Setup

**Status:** plan v0
**Owner:** Sara Chen (CISO) + Jacob Lee (DevOps)
**Walks:** Mythos S1809 (SOC 2 evidence vault setup) deferred → partial
**Gating dependency:** SOC 2 audit firm engaged + automated evidence-collection budget.

## Why this exists

SOC 2 Type I requires per-control evidence collected over 3+ months. Manual collection error-prone + time-intensive. Automated vault (Drata, Vanta, Secureframe) streamlines.

## v1 vault scope

- **Access control evidence:** per-user provisioning + revocation logs.
- **Backup evidence:** per-backup verify + restore logs.
- **Encryption evidence:** per-disk + per-database encryption-at-rest verification.
- **Change management:** per-PR review + approval logs.
- **Incident response:** per-incident timeline + post-mortem archive.
- **Vulnerability management:** per-CVE patching evidence.
- **Vendor management:** per-vendor DPA + security review docs.
- **Per-control mapping:** to SOC 2 trust criteria (security + availability + processing integrity + confidentiality + privacy).

## Tool selection (operator-side decision)

- **Drata:** ~$15K/year. AICPA-certified collector.
- **Vanta:** ~$10K/year. Most popular among SMB.
- **Secureframe:** ~$12K/year. Mid-market positioning.

Sara + Ray review per-vendor at engagement.

## Per-evidence cadence

- Daily auto-collection per integrated system.
- Per-month Sara review + signoff.
- Per-quarter audit-ready snapshot.

## Cross-references

Master plan: S1809. Sister: `docs/GRIMBANEWS_SOC2_CONTROL_MAP.md` (Wave LLL), `docs/GRIMBANEWS_RELEASE_SMOKE_EVIDENCE_2026_05_12.md`.
