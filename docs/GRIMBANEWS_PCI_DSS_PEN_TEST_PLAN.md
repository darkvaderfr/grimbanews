# GrimbaNews — PCI DSS Annual Penetration Test Plan

**Status:** plan v0
**Owner:** Sara Chen (CISO) + Jacob Lee (DevOps) + external pen-test vendor
**Walks:** Mythos S1846 (PCI DSS annual penetration test) deferred → partial
**Gating dependency:** Same as S1842; broader scope sits in Wave LLL bug-bounty + Wave LLL vulnerability scan runbook.

## Pen-test scope

For SAQ-A scope:
- External-facing surface penetration test (annually).
- API endpoint testing.
- Web app pen test (OWASP Top 10).
- Network perimeter test.

Per PCI DSS 11.3: annual + per-significant-change re-test.

## Pen-test vendor selection

- **Bishop Fox:** premier, ~$30-80k/engagement.
- **Trustwave SpiderLabs:** mid-tier, ~$20-50k.
- **Cobalt.io:** crowdsourced, ~$15-30k/engagement.
- **NCC Group:** mid-large enterprise, ~$25-60k.

## Per-engagement scope

- T-0: scope kickoff + rules of engagement.
- T-2 weeks: pen-test execution.
- T+1 week: per-finding report.
- T+30 days: per-finding remediation.
- T+45 days: re-test if Critical/High findings.

## Per-finding response

- Critical: 7 days remediation.
- High: 30 days.
- Medium: per-quarter.
- Low: per-year.

## Bug bounty integration

Continuous bug-bounty (per Wave LLL `docs/GRIMBANEWS_PRIVACY_BUG_BOUNTY_SCOPE.md`) catches issues between annual pen-tests.

## Cross-references

Master plan: S1846. Sister: `docs/GRIMBANEWS_PCI_DSS_ASV_SCAN_PLAN.md`, `docs/GRIMBANEWS_PRIVACY_BUG_BOUNTY_SCOPE.md` (Wave LLL).
