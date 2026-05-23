# GrimbaNews — Vendor Risk Register

**Status:** register v1 (vendors enumerated, formal DPAs deferred)
**Owner:** Sara Chen (CISO) + Ray Dalio (CFO) on cost
**Walks:** Mythos S1806 (SOC 2 vendor-risk evidence) + S1871 (vendor inventory consolidated form) deferred → partial
**Gating dependency:** No formal DPA-collection program; no quarterly vendor review cadence. This document is the **single source-of-truth register** that DPA collection (S1873) and quarterly review (S1878) will iterate against.

## Why this exists

S1806 + S1871 both pointed at the same gap: vendor *list* exists in `.env.example` + provider vault, but no consolidated **risk register** doc enumerates each vendor with its data classes, tier, criticality, and DPA status. This doc closes that gap with the current vendor list.

## Register

| # | Vendor | What we use it for | Data classes touched | Risk tier | DPA status | Subprocessor list location | Notes |
|---|---|---|---|---|---|---|---|
| 1 | Botble CMS | Platform / CMS framework | All site data (single-tenant DB) | Critical | N/A (self-hosted code, not SaaS) | n/a | CodeCanyon-licensed fork per `feedback_codecanyon_license_vader_call.md` |
| 2 | newsdata.io | News API ingestion | None (we send queries, receive headlines + URLs — no reader PII transmitted) | High | Not collected | https://newsdata.io/legal | Daily-cap counter via `GrimbaProviderCredits` per `app/Support/GrimbaProviderCredits.php`; per S891 admin pack |
| 3 | NewsAPI (newsapi.org) | News API fallback | Same as #2 | High | Not collected | https://newsapi.org/terms | Same as #2 |
| 4 | OpenRouter | LLM translation provider (NobuTranslator driver) | Article content (no reader PII) | High | Not collected | https://openrouter.ai/privacy | Per-driver chain in `app/Services/GrimbaTranslator.php` |
| 5 | LibreTranslate (hosted instance) | LLM translation provider fallback | Article content (no reader PII) | Medium | Self-hosted possible | https://libretranslate.com/ | Driver in `GrimbaTranslator` |
| 6 | NobuAI proxy (internal) | LLM provider routing (rebranded chain) | Article content (no reader PII) | High | Internal Iboga | n/a | `GrimbaNobuAi::CHAIN` 8 providers — proxied through NobuAI brand per `feedback_nobuai_model_branding.md` |
| 7 | Acelle Mail (LeafRelay) | Transactional email (newsletter digest, contact replies) | Subscriber email addresses, vault-digest content | High | Internal Iboga | n/a | Per `app/Mail/GrimbaVaultDigestMail.php`; LeafRelay is our own VPS |
| 8 | Namecheap | DNS + domain registrar | Domain only | Low | Standard ToS | https://www.namecheap.com/legal/general/privacy-policy/ | |
| 9 | VPS provider (Hetzner / per Iboga hosting policy) | Compute / disk for grimbanews.com | All site data on disk (SQLite live DB + backups) | Critical | Provider DPA available, not formally signed | https://www.hetzner.com/legal/ | Backups via `app/Support/GrimbaDatabaseBackups.php` |
| 10 | GitHub (darkvaderfr private mirror) | Source control + CI/CD potential | Source code (no reader data) | High | Standard ToS | https://docs.github.com/en/site-policy | Per `feedback_darkvaderfr_git_mandatory.md` |
| 11 | Apple Developer (planned) | iOS app distribution per S1153 | None until shell ships | Medium | Pending | n/a | Deferred per S1153 |
| 12 | Google Play (planned) | Android app distribution per S1153 | None until shell ships | Medium | Pending | n/a | Deferred per S1153 |
| 13 | Stripe / Google AdSense (planned) | Payment processing / ads per S853 + monetization S1211 | Cardholder data (Stripe) — would trigger PCI DSS scope per S1841 | Critical | Pending | n/a | Not integrated today |
| 14 | Cloudflare / Fastly / Better Stack (planned) | CDN + uptime monitoring per S1017/S1018/S1911 | None (cache layer) / uptime telemetry | Medium | Pending | n/a | Not provisioned today |
| 15 | Sentry (planned) | Error tracking per S1013 | Scrubbed exception data, no PII (per `docs/GRIMBANEWS_SENTRY_INTEGRATION_PLAN.md` PII rules) | Medium | Pending | https://sentry.io/legal/dpa/ | Not provisioned today |
| 16 | PagerDuty / Better Stack (planned) | Paging per S1014/S1016 | Operator contact details only | Low | Pending | n/a | Not provisioned today |

## Risk tier definitions

- **Critical** — outage of vendor causes GrimbaNews outage or data loss.
- **High** — outage degrades a load-bearing surface (ingest, translation, email delivery).
- **Medium** — outage degrades a feature without blocking publication.
- **Low** — outage is invisible to readers.

## Data-class rules

- **No reader PII** transmits to any external translation / NobuAI provider — only article content (already public).
- **Subscriber emails** go only to vendor #7 (LeafRelay/Acelle, Iboga-owned VPS).
- **Cardholder data** is currently impossible — zero `stripe|paypal|braintree|adyen` integration in `app/` per S1841 PCI DSS scope statement.
- **Member PII** (account email, login hash) is on vendor #9 (VPS disk SQLite) only.

## DPA-collection plan (S1873)

Priority order for DPA collection (when Sara Chen + counsel engage):

1. Vendor #9 (VPS provider) — critical, holds all data on disk.
2. Vendor #2 + #3 (newsdata.io, NewsAPI) — high, vendor lock-in if not collected.
3. Vendor #4 (OpenRouter) — high, content processing.
4. Vendor #13 (Stripe) — at integration time, will trigger PCI DSS scope per S1841.
5. Vendor #15 (Sentry) — at activation time per `docs/GRIMBANEWS_SENTRY_INTEGRATION_PLAN.md` checklist step 8.

## Quarterly review cadence (S1878)

- Quarterly review at end of Q1/Q2/Q3/Q4.
- Sara Chen owns review; Ray Dalio reviews cost; Jacob Lee reviews technical-fit.
- Each vendor reviewed against: cost vs value, criticality unchanged, alternatives evaluated, DPA refresh, security-event log.
- Output: updated register + delta log appended below.

## Change log

- **2026-05-22** — v1 register created from existing `.env.example` keys + Mythos S1806/S1871 scope. (Wave RRRRRRRRRR)

## Cross-references

- Master plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1806, S1871 rows; S1872-S1878 dependencies)
- Provider keys: `.env.example`
- Provider credits accounting: `app/Support/GrimbaProviderCredits.php`
- NobuAI chain: `app/Services/GrimbaNobuAi.php::CHAIN`
- Translator chain: `app/Services/GrimbaTranslator.php`
- Iboga hosting policy: `~/.claude/projects/-Users-vb-kaizen/memory/feedback_hosting_policy.md`
- CodeCanyon license policy: `~/.claude/projects/-Users-vb-kaizen/memory/feedback_codecanyon_license_vader_call.md`
- NobuAI branding policy: `~/.claude/projects/-Users-vb-kaizen/memory/feedback_nobuai_model_branding.md`
- Sister surrogates: `docs/GRIMBANEWS_SOC2_CONTROL_MAP.md`, `docs/GRIMBANEWS_GDPR_ROPA.md`, `docs/GRIMBANEWS_PCI_DSS_SCOPE_STATEMENT.md`
