# GrimbaNews — External Audit Report Receipt

**Status:** plan v0
**Owner:** Sara Chen (CISO) + Michael O'Connor (Legal)
**Walks:** Mythos S1897 (external-audit report receipt) deferred → partial
**Gating dependency:** Final report issued by auditor after S1895 management-response close.

## Why this exists

The signed final report is a regulated document. Receipt + storage + access controls need to be deliberate so that (a) we never lose the original, (b) we never let it leak to non-NDA parties, (c) we can prove version integrity if customers ask "is this the real report?"

## Per-receipt workflow

1. Auditor issues signed final PDF (with engagement partner signature + firm letterhead).
2. CISO acknowledges receipt within 1 business day.
3. CISO verifies signature + scope + period match the engagement letter.
4. Per-report SHA-256 hash recorded + filed in `/admin/grimba/external-audit/YYYY/report-integrity.txt`.
5. Per-report stored in encrypted archive with restricted access.
6. Per-report metadata logged: auditor, type (SOC 2 Type II / ISO 27001), period, issued_date, expiration_date.

## Per-storage policy

Two copies:
- Primary: encrypted archive at `/admin/grimba/external-audit/YYYY/`. CISO + CEO read; CISO write.
- Backup: encrypted offline storage (rotated quarterly).

No copies on engineering laptops. No copies in chat tools. Distribution exclusively via the controlled customer process (per S1898 wave-next).

## Per-integrity check

Quarterly:
- Per-report SHA-256 re-computed + compared to stored hash.
- Per-report hash mismatch → escalate immediately (potential tampering).
- Per-report annual rotation review (does the report cover the current customer-facing claim window?).

## Per-version-control

If auditor reissues (correction, scope clarification):
- Per-revision new SHA-256 captured.
- Per-revision side-by-side diff documented.
- Per-revision communication to all distributed-to customers.
- Per-revision superseded version retained but marked as such.

## Per-access-log

Every read of the stored report (CISO, CEO, prep for customer distribution, audit committee review) logged with actor + timestamp + purpose. Quarterly review by Audit Committee Chair.

## Cross-references

Master plan: S1897. Sister: `docs/GRIMBANEWS_EXTERNAL_AUDIT_REMEDIATION.md` (S1896), `docs/GRIMBANEWS_EXTERNAL_AUDIT_REPORT_DISTRIBUTION.md` (S1898 next), `docs/GRIMBANEWS_EXTERNAL_AUDIT_FINDINGS_RESPONSE.md` (SUB-56, S1895).
