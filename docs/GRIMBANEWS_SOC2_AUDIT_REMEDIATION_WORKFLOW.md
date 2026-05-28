# GrimbaNews — SOC 2 Audit Remediation Workflow

**Status:** plan v0
**Owner:** Sara Chen (CISO) + per-control owner + audit firm
**Walks:** Mythos S1818 (SOC 2 audit remediation) deferred → partial
**Gating dependency:** S1817 audit findings logged.

## Per-finding remediation procedure

1. Finding logged in /admin/soc2-findings with severity + owner.
2. Per-finding remediation plan drafted within 7 days.
3. Per-finding evidence-of-remediation collected.
4. Per-finding audit-firm verification.
5. Per-finding marked resolved.

## Per-severity timeline

- Critical: 7 days to remediate + verify.
- High: 30 days.
- Medium: 60 days.
- Low: 90 days.

## Per-week status standup

Sara + per-control-owner weekly call:
- Per-finding status update.
- Per-finding blocker discussion.
- Per-week roll-up to Vader.

## Per-finding documentation in evidence vault

Per Wave SUB-35 vault: per-finding remediation linked to per-control evidence updates.

## Per-finding closure

- Final status documented.
- Per-finding owner sign-off.
- Audit-firm verification log.

## Cross-references

Master plan: S1818. Sister: `docs/GRIMBANEWS_SOC2_AUDIT_FINDINGS_RESPONSE.md`, `docs/GRIMBANEWS_SOC2_EVIDENCE_VAULT_SETUP.md`.
