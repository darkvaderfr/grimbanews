# GrimbaNews — Privacy Bug Bounty Scope

**Status:** plan v0 (program not launched)
**Owner:** Sara Chen (CISO) + counsel
**Walks:** Mythos S1083 (privacy bug bounty) deferred → partial
**Gating dependency:** Counsel review of safe-harbor terms + bounty budget.

## In-scope surfaces

- `*.grimbanews.com` web + API endpoints
- Mobile app (when launched)
- Email surfaces (newsletters, transactional)
- `/api/middle-ground.json` + `.atom`

## In-scope categories (researcher reports)

- Authentication / authorization bypass
- IDOR (insecure direct object reference)
- SQL / NoSQL injection
- XSS (reflected, stored, DOM)
- SSRF (already lock-tested per `tests/Feature/GrimbaLaunchReadinessTest.php::test_img_proxy_rejects_ssrf_targets`)
- CSRF in non-GET endpoints
- Per-reader data leak via cache poisoning
- Per-reader PII leak via log scraping
- Cookie-consent bypass
- Per-source license / DRM bypass

## Out-of-scope

- Social-engineering attacks against staff
- Physical attacks
- DoS / volumetric attacks (use rate-limit policies instead)
- Third-party services (Cloudflare, AWS, Stripe — report to those vendors)
- Self-XSS without further impact

## Reward tiers

- Critical (auth bypass, mass-PII leak): $500-2000
- High (per-user PII leak, stored XSS in admin): $200-500
- Medium (CSRF, less-impactful XSS): $50-200
- Low (CSP-bypass without impact, header weakness): swag + acknowledgment

## Safe harbor

Researchers acting in good faith per the disclosure policy below are protected from legal action per CFAA / GDPR safe-harbor norms.

## Disclosure policy

- Email: `security@grimbanews.com` (gates on operator-side mailbox provisioning)
- Acknowledge within 72 hours
- Disclose within 90 days of fix, or by mutual agreement
- Researcher credited in `/security` page (opt-in)

## Cross-references

Master plan: S1083. Sister: `docs/GRIMBANEWS_VULNERABILITY_SCAN_RUNBOOK.md`, `docs/GRIMBANEWS_INCIDENT_RESPONSE_RUNBOOK.md`.
