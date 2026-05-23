# GrimbaNews — Bug Bounty Program Scope (v0 draft)

**Status:** scope v0 (program design ready; vendor account + payout funding pending)
**Owner:** Sara Chen (CISO) + Ray Dalio (CFO) on payout budget
**Walks:** Mythos S2091 (community — bug-bounty program) deferred → partial
**Gating dependency:** HackerOne / YesWeHack / Bugcrowd account + payout budget approval. Scope itself is operator-side.

## Why this exists

S2091 was honest-deferred as "needs HackerOne / YesWeHack account + scope." Writing the scope is the operator-side prerequisite — once written, vendor activation is a checkout exercise. This doc is the scope.

## Program goal

Invite security researchers to find vulnerabilities in GrimbaNews before bad actors do; reward responsibly disclosed findings; build a public trust signal that security is taken seriously.

## Vendor shortlist

| Vendor | Free tier | Pros | Cons | Decision |
|---|---|---|---|---|
| **HackerOne** | Free for VDP (no payouts); paid for bug bounty | Industry standard; integrates with Slack/Jira; researcher pool | $5k+/mo for paid bounty | Primary candidate for paid tier |
| **YesWeHack** | Free for VDP | EU-headquartered (data residency); strong in FR-speaking market | Smaller researcher pool than H1 | Primary candidate for VDP-only start |
| **Bugcrowd** | Free for VDP | Comparable to H1 | US-headquartered | Backup |
| **Disclose.io safe-harbor self-hosted** | Free | No vendor lock | We handle intake | Backup for purely VDP, no-bounty path |

**Recommendation phased:**

- Phase 1: **Vulnerability Disclosure Policy (VDP)** on YesWeHack (EU data residency, free). Read-only — no payouts.
- Phase 2 (after first 6 months + Ray budget approval): upgrade to **paid bug bounty** on YesWeHack or HackerOne.

## Program scope

### In-scope assets

- `*.grimbanews.com` (all subdomains).
- `app.grimbanews.com` and `admin.grimbanews.com` if/when those subdomains stand up.
- Mobile apps if/when shipped per S1161/S1162 (not today).
- API endpoints if/when v2 ships per S1181 (not today).

### Out-of-scope assets

- Botble CMS upstream code (not our codebase; report to Botble vendor per `docs/GRIMBANEWS_VENDOR_REGISTER.md` vendor #1).
- Third-party vendor surfaces (newsdata.io, OpenRouter, LeafRelay, etc. — report to those vendors per their disclosure policies).
- Sister Iboga products (LeafRelay, Incognito, NobuTrust, etc. — separate VDPs).
- Social-engineering attacks against staff.
- Physical attacks against VPS provider.
- Denial-of-service attacks (load testing requires explicit pre-coordination).

### In-scope vulnerability classes (paid bounty when active)

| Severity | CVSS range | Bounty (Phase 2 paid tier — proposal) | Examples |
|---|---|---|---|
| Critical | 9.0-10.0 | $1,000-5,000 | RCE, auth bypass to admin, full PII exfil |
| High | 7.0-8.9 | $500-1,000 | Stored XSS in admin context, SQLi reading sensitive tables, server-side request forgery |
| Medium | 4.0-6.9 | $100-500 | Reflected XSS, CSRF on state-changing actions, IDOR with limited blast radius |
| Low | 0.1-3.9 | $0-100 | Open redirect, missing security headers, info disclosure |
| Informational | N/A | Acknowledgement only | Hall of fame entry; security.txt name listing |

Phase 1 (VDP-only): acknowledgement + hall-of-fame, no money.

### Excluded categories (won't pay even if reported)

- Self-XSS requiring social engineering of the victim.
- Best-practice complaints without exploit chain (e.g. "you should use SameSite=Strict" without exploit).
- Findings in third-party libraries we use (file with library vendor; we ack relay).
- Findings on `security.txt` itself or other purely informational files.
- DoS / volumetric attacks.
- Automated scanner output without analysis.

## Safe-harbor clause

Researchers acting in good faith under this program will not be subject to legal action by Iboga Ventures or its representatives, provided they:

- Do not access, modify, or destroy data beyond what's needed to demonstrate the vulnerability.
- Do not disrupt service.
- Disclose privately via the vendor platform / `security@grimbanews.com` first.
- Wait for fix-and-public-disclosure agreement (default 90 days max).
- Do not extort.

The full safe-harbor wording follows the **Disclose.io safe-harbor template v2.0** (industry standard).

## Disclosure timeline

- **Day 0:** report received.
- **Day 1:** ack from Sara Chen.
- **Day 7:** triage complete; severity assigned.
- **Day 30:** fix shipped (target).
- **Day 60:** fix shipped (hard SLA for High/Critical).
- **Day 90:** coordinated public disclosure (researcher + Iboga jointly publish).

## Intake surface

Today (pre-vendor):

- `mailto:security@grimbanews.com` per `public/.well-known/security.txt` (per S995 / `tests/Feature/GrimbaLaunchReadinessTest`).
- PGP key published at `public/.well-known/pgp-key.asc` (to ship — currently missing — small follow-on sprint).

Once vendor ships:

- Vendor platform becomes primary intake.
- `security@grimbanews.com` remains as fallback.

## SECURITY.md (S2092 surrogate consolidation)

S2092 is currently `partial` (security.txt shipped, SECURITY.md deferred). When the OSS methodology repo ships per `docs/GRIMBANEWS_OSS_METHODOLOGY_SCOPE.md` Phase 1, the SECURITY.md will live in each OSS repo and link back to this scope doc.

## Activation checklist

Phase 1 (free VDP):

1. Sara Chen creates YesWeHack VDP profile for GrimbaNews.
2. Upload this scope as the program policy.
3. Update `public/.well-known/security.txt` with vendor-program URL.
4. Add SECURITY.md to OSS repos (when they ship).
5. Update `docs/GRIMBANEWS_VENDOR_REGISTER.md` with YesWeHack row.

Phase 2 (paid bounty, post-budget):

6. Ray approves payout fund.
7. Migrate or expand to paid program on YesWeHack or HackerOne.
8. Publish bounty schedule (table above).
9. Update `docs/GRIMBANEWS_GDPR_ROPA.md` if researcher PII is processed by vendor.

## Cross-references

- Master plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S2091 row)
- Existing security.txt: `public/.well-known/security.txt` (locked by `tests/Feature/GrimbaLaunchReadinessTest`)
- Sister OSS scope: `docs/GRIMBANEWS_OSS_METHODOLOGY_SCOPE.md`
- Sister compliance docs: `docs/GRIMBANEWS_SOC2_CONTROL_MAP.md` (CC7.1 vulnerability detection), `docs/GRIMBANEWS_VENDOR_REGISTER.md`
- IR runbook (intake routes here for breach-class): `docs/GRIMBANEWS_INCIDENT_RESPONSE_RUNBOOK.md`
- Standards reference: ISO/IEC 29147 (vulnerability disclosure), Disclose.io safe-harbor template v2.0
