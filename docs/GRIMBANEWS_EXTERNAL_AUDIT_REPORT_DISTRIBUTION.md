# GrimbaNews — External Audit Report Distribution (Customers / Prospects)

**Status:** plan v0
**Owner:** Sara Chen (CISO) + Liam Smith (PM, customer success) + Michael O'Connor (Legal)
**Walks:** Mythos S1898 (external-audit report distribution) deferred → partial
**Gating dependency:** Report receipt + storage (Wave SUB-57 sister, S1897); enterprise B2B motion (S1991 planned) for prospect requests.

## Why this exists

SOC 2 / ISO reports unlock enterprise procurement. But they're confidential — customers expect to see them, prospects must request them under NDA, and competitors mustn't get them. We need a controlled distribution process that scales without burning CISO time per request.

## Per-requester classification

| Requester type | Path | NDA required | Approval |
|---|---|---|---|
| Active enterprise customer | Self-serve via trust center after signing customer-NDA | Yes (embedded in MSA) | Auto on MSA presence |
| Prospect (active deal) | Request via /trust/request-report | Yes (separate mutual NDA) | Sales + CISO sign-off |
| Prospect (early-stage) | Public summary only | No (summary is public) | n/a |
| Auditor / regulator | Direct CISO channel | Per regulator rules | CISO |
| Press / analyst | Public summary + redacted briefing only | No (briefing prepared) | CISO |
| Competitor / unknown | Decline | n/a | CISO |

## Per-request workflow

1. Per-request submitted via /trust/request-report (or sales channel).
2. Per-request triaged within 1 business day: classify requester.
3. Per-request NDA generated + sent (if required).
4. Per-request NDA countersigned + filed.
5. Per-request report delivered via time-limited download link (24-72 hours).
6. Per-request download logged (who, when, IP, NDA reference).
7. Per-request follow-up: did the requester find what they needed?

## Per-public-summary content

A short publicly-readable summary at /trust:
- Per-summary attestation type + period.
- Per-summary issuing firm name (note: firm name only, not provider — NobuAI branding rules don't apply to auditor identification).
- Per-summary unqualified opinion notation (yes / no exceptions).
- Per-summary scope at a high level.
- Per-summary how to request the full report.

## Per-volume metric

Tracked on `/admin/grimba/audit-report-requests`:
- Per-month request count by requester type.
- Per-month NDA-to-download conversion rate.
- Per-quarter sales-attributed wins enabled by report access.

## Per-leak-incident response

If we suspect a report leak:
- Per-leak access log audited for the time period.
- Per-leak distributed-to list reviewed.
- Per-leak Legal + CISO assess whether to demand return / destruction.
- Per-leak future requests from suspected leaker frozen pending review.

## Cross-references

Master plan: S1898. Sister: `docs/GRIMBANEWS_EXTERNAL_AUDIT_REPORT_RECEIPT.md` (S1897), `docs/GRIMBANEWS_EXTERNAL_AUDIT_REMEDIATION.md` (S1896), trust-center surfaces (planned S1900-band).
