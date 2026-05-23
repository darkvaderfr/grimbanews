# GrimbaNews — GDPR Record of Processing Activities (RoPA, Article 30)

**Status:** RoPA v0 (current processing enumerated; counsel review pending)
**Owner:** Sara Chen (CISO) — co-signed Lucy Leai (Strategy) for editorial-data processing
**Walks:** Mythos S1851 (GDPR processing-activities register / Article 30) deferred → partial
**Gating dependency:** Retained EU counsel review (Article 30 records are formally signed by the controller's representative). This document is the v0 record that counsel will iterate on, not a finished signed copy.

## Why this exists

S1851 was honest-deferred as "no RoPA document; operator-side Sara-Chen / counsel pickup." The Article 30 record is largely an enumeration exercise — list each processing activity, its purpose, categories of data, categories of recipients, retention, transfer arrangements. Counsel review polishes language; the enumeration itself we can do today from the existing codebase.

## Controller information

- **Controller:** Iboga Ventures (operator: Vader) — GrimbaNews product line.
- **Establishment:** France (operator's stated jurisdiction; legal entity to be confirmed by counsel for Article 27 representative requirements).
- **DPO:** Not designated today (S1857 — large-scale special-category-data threshold not met; news aggregation is not health / finance / biometric).

## Processing activity catalogue

### Activity 1 — Reader visit (anonymous browsing)

| Field | Value |
|---|---|
| Purpose | Serving news content; enabling personalization |
| Legal basis | Art. 6(1)(f) legitimate interest |
| Categories of data | IP address (hashed per `GrimbaVaultEvents::ipHash()`), browser headers, language preference cookie (`grimba_lang`), region edition cookie (`grimba_region_edition`), For-You recent-history cookie (`grimba_for_you_recent`) |
| Categories of data subjects | Site visitors |
| Recipients | None external (cookies stay in browser; server logs stay in `storage/logs/`) |
| Retention | Server logs: 90 days. Cookies: 1 year (or until visitor clears). |
| Transfer outside EEA | None (VPS may be EEA-located per vendor #9 in `docs/GRIMBANEWS_VENDOR_REGISTER.md`) |
| Technical / org measures | HSTS + secure cookies + `GrimbaSecurityHeaders` |
| Code surface | `app/Http/Middleware/GrimbaLocaleEnforce.php`, `app/Support/GrimbaForYou.php`, `platform/themes/echo/partials/cookie-consent.blade.php` |

### Activity 2 — Member account

| Field | Value |
|---|---|
| Purpose | Persistent vault, saved searches, member-only features |
| Legal basis | Art. 6(1)(b) contract |
| Categories of data | Email, password hash (bcrypt), display name, vault item IDs, saved-search definitions |
| Categories of data subjects | Registered members |
| Recipients | None external |
| Retention | Until member-initiated deletion (S1859 erasure workflow) or 3 years of inactivity |
| Transfer outside EEA | None |
| Technical / org measures | Botble member-auth + bcrypt + HTTPS-only cookies |
| Code surface | `Botble\Member` package; `app/Support/GrimbaVault.php`; `app/Support/GrimbaSavedSearches.php` |

### Activity 3 — Subscriber (newsletter / vault digest)

| Field | Value |
|---|---|
| Purpose | Weekly digest delivery |
| Legal basis | Art. 6(1)(a) consent (double opt-in) |
| Categories of data | Email, language preference, region preference, opt-in timestamp |
| Categories of data subjects | Opted-in subscribers |
| Recipients | LeafRelay (vendor #7) for transactional email delivery |
| Retention | Until unsubscribe (one-click footer link) |
| Transfer outside EEA | None (LeafRelay = Iboga-owned VPS) |
| Technical / org measures | Double opt-in; one-click unsubscribe; DKIM-signed |
| Code surface | `app/Mail/GrimbaVaultDigestMail.php`; `app/Console/Commands/GrimbaSendVaultDigests.php`; `subscribers` table |

### Activity 4 — Vault analytics

| Field | Value |
|---|---|
| Purpose | Operator insight on saves / unsaves; identify high-value content |
| Legal basis | Art. 6(1)(f) legitimate interest |
| Categories of data | Event type (save/unsave), post ID, timestamp, ip_hash (HMAC-SHA256), member ID if logged in |
| Categories of data subjects | Site visitors + members |
| Recipients | None external |
| Retention | 90 days in live table; archived weekly per `GrimbaArchiveVaultEvents` |
| Transfer outside EEA | None |
| Technical / org measures | Privacy-by-design: ip_hash not raw IP; archived to gzipped JSON; archive retention per S2227 |
| Code surface | `app/Support/GrimbaVaultEvents.php`; `app/Console/Commands/GrimbaArchiveVaultEvents.php` |

### Activity 5 — Contact form

| Field | Value |
|---|---|
| Purpose | Reader inquiry intake; reply via email |
| Legal basis | Art. 6(1)(a) consent + Art. 6(1)(f) legitimate interest (replying) |
| Categories of data | Name (optional), email, message body, ip_hash |
| Categories of data subjects | Anyone who submits |
| Recipients | Operator email inbox via LeafRelay |
| Retention | 1 year then operator-purge |
| Transfer outside EEA | None |
| Technical / org measures | Per-IP rate limit (`AdvertiserLeadController` pattern); HTTPS-only |
| Code surface | `App\Http\Controllers\GrimbaContactController`; `app/Mail/GrimbaContactReplyMail.php` |

### Activity 6 — Translation / NobuAI summarization

| Field | Value |
|---|---|
| Purpose | Multilingual reader access; story-cluster summaries |
| Legal basis | Art. 6(1)(f) legitimate interest |
| Categories of data | **Article content only** — no reader PII transmitted to providers |
| Categories of data subjects | None (source articles are public; reader is anonymous to provider) |
| Recipients | OpenRouter, LibreTranslate, NobuAI proxy chain (vendors #4-#6 per register) |
| Retention | Provider-side per their privacy policies; we cache translations locally indefinitely |
| Transfer outside EEA | Possible — depends on provider routing; DPA to collect per S1873 |
| Technical / org measures | Article-content-only payload; no reader cookies / IPs forwarded; failover order ranks privacy-friendly providers first |
| Code surface | `app/Services/GrimbaTranslator.php`; `app/Services/GrimbaNobuAi.php` |

### Activity 7 — Advertiser lead intake

| Field | Value |
|---|---|
| Purpose | B2B sales lead capture |
| Legal basis | Art. 6(1)(b) pre-contract step + Art. 6(1)(a) consent |
| Categories of data | Company name, contact name, email, message, region, ip_hash |
| Categories of data subjects | Advertiser prospects |
| Recipients | Operator sales mailbox (region-routed per `grimba_advertiser_leads_sales_mailbox` setting) |
| Retention | 2 years (B2B sales-cycle norm) |
| Transfer outside EEA | None |
| Technical / org measures | Per-IP rate limit (`RateLimiter::attempt`) |
| Code surface | `App\Http\Controllers\AdvertiserLeadController` |

## Data-subject rights (Articles 15-22)

- **Right of access (Art. 15)** — operator-side DSAR intake via `/contact` form, manual fulfilment (S1858 — formal workflow deferred).
- **Right to rectification (Art. 16)** — members self-edit via `/account`; non-member data is request-via-contact.
- **Right to erasure (Art. 17)** — member self-delete (partial — full erasure workflow per S1859 deferred); contact-form requests handled manually.
- **Right to restriction (Art. 18)** — operator-side, manual today.
- **Right to data portability (Art. 20)** — `coffre/export.csv` for member vault export.
- **Right to object (Art. 21)** — opt-out of vault analytics via cookie clearance; subscriber unsubscribe one-click.
- **Automated decision-making (Art. 22)** — N/A: For-You ranking is profile-based suggestion, not legally-binding decision.

## DPIA priorities (Article 35)

Per S1852-S1856, full DPIAs are deferred. Priority order when retained counsel engages:

1. Activity 6 (translation / NobuAI) — high data volume + provider transfers.
2. Activity 1 (anonymous browsing) — large scale; profile-relevant via For-You.
3. Activity 4 (vault analytics) — partly addressed by ip_hash; needs formal DPIA.

## Transfer mechanisms

- All current EEA → non-EEA transfers (translation providers per Activity 6) must rely on Article 46 Standard Contractual Clauses or adequacy decision. To be confirmed per-vendor at S1873 DPA collection.

## Change log

- **v0 (2026-05-22)** — initial enumeration. Wave RRRRRRRRRR.

## Cross-references

- Master plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1851 row; dependencies for S1852-S1860)
- Vendor register: `docs/GRIMBANEWS_VENDOR_REGISTER.md`
- ISMS scope: `docs/GRIMBANEWS_ISMS_SCOPE.md`
- Consent log design: `docs/GRIMBANEWS_CONSENT_LOG_DESIGN.md`
- Existing privacy primitive: `app/Support/GrimbaVaultEvents.php::ipHash()`
- Cookie consent surface: `platform/themes/echo/partials/cookie-consent.blade.php`
- Standards reference: GDPR Articles 5, 6, 13-22, 30, 32-34
