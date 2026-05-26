# GrimbaNews — Per-Locale Ad Consent Rules

**Status:** design v0 (single FR+EN bilingual consent banner today; per-locale rules deferred)
**Owner:** Maya Patel (Security & Compliance) on regulator-mapping + Sara Chen (CISO) on data-flow review + Nina Patel (Lead Frontend) on banner wiring
**Walks:** Mythos S1146 (per-locale ad consent rules) deferred → partial
**Gating dependency:** Counsel-defined per-jurisdiction consent matrix + per-locale catalog rollout. Consent legal review is regulator-specific.

## Why this exists

S1146 honest-deferred as "single FR+EN bilingual consent banner today." Current banner covers GDPR baseline. Per-locale rules differ:
- DE: TTDSG + GDPR — stricter cookie-banner copy
- IT: Garante per la protezione dei dati personali — additional opt-out language
- ES: AEPD — Spanish-language consent
- PT-BR: LGPD — Brazilian framework
- AR / Middle East: per-country (UAE has PDPL; KSA has PDPL 2021)
- JA: APPI — Japanese framework
- HI / India: DPDPA 2023
- KO: PIPA

This doc captures the per-jurisdiction matrix.

## Per-locale consent obligations

| Locale | Jurisdiction(s) | Consent framework | Key requirements |
|---|---|---|---|
| FR | France + EU | GDPR + CNIL | Granular consent per purpose; opt-out as easy as opt-in; banner language matches site |
| EN | UK + global | UK GDPR + PECR | Same as FR; ICO guidance on dark patterns |
| ES | Spain + EU | GDPR + AEPD | Catalog includes consent banner in ES |
| PT-BR | Brazil | LGPD | Brazilian-Portuguese consent banner |
| DE | Germany + DACH | GDPR + TTDSG | Stricter cookie-banner rules; ad cookies need explicit opt-in (no implied consent) |
| IT | Italy + EU | GDPR + Garante | IT consent banner |
| AR | UAE + KSA + global | UAE PDPL + KSA PDPL | Locale-specific; less mature than GDPR but consent baseline applies |
| JA | Japan | APPI | Opt-out + transparency requirements; consent baseline applies |
| ZH | China + global | PIPL (if serving mainland) | If site reaches mainland readers, PIPL applies; data-localization considerations |
| KO | South Korea | PIPA | Consent + purpose limitation requirements |
| RU | Russia | 152-FZ (if serving RU readers) | Data-localization rules; site may need Russian-resident hosting if served to RU IPs |
| HE | Israel | Israeli Privacy Protection Law | Consent baseline applies |
| HI | India | DPDPA 2023 | New framework; consent + purpose limitation; expected in force 2026 |
| SW | KE + TZ + UG + EA | per-country DPAs | KE Data Protection Act 2019; TZ has framework; consent baseline applies |

## Banner implementation per locale

Single banner component (`resources/views/partials/grimba-cookie-consent.blade.php`) parameterized on:
- `locale` — banner copy language
- `regulator_text_key` — per-locale regulator name in banner ("CNIL", "AEPD", "Garante", "TTDSG")
- `consent_modes` — granular per-purpose (necessary, functional, analytics, advertising) with per-jurisdiction defaults

## Per-purpose default state per jurisdiction

- EU (FR, EN-UK, ES, DE, IT): analytics + advertising default OFF (opt-in only)
- Non-EU (EN-US, AR, JA, KO, IN, EA): analytics default ON / advertising default OFF (legitimate-interest claim possible for analytics in some jurisdictions; defer to counsel)
- ZH-mainland: PIPL strict; treat like EU
- RU: 152-FZ; data-localization separate question
- BR: LGPD; treat like EU

## Acceptance gates

1. Banner copy in active locale (no FR / EN fallback for content surfaces)
2. Granular consent toggles per purpose
3. Operator can pre-configure per-locale default state per `config/grimba_consent.php`
4. Maya Patel + counsel sign-off per locale before launch

## Things deliberately NOT in this design

- **Universal banner copy** — translated per locale; not a one-size-fits-all CCPA-style banner
- **Per-region IP-based geofencing for banner variants** — single locale-driven; rely on browser locale, not IP geo
- **Per-locale Acelle / GA / AdSense suppression on opt-out** — already shipped at FR/EN level via `GrimbaConsent` helper; extends to all locales automatically

## Cross-references

- Master plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1146 row)
- Sister docs: `docs/GRIMBANEWS_CONSENT_LOG_DESIGN.md`, `docs/GRIMBANEWS_GDPR_ROPA.md`, `docs/GRIMBANEWS_LEGAL_PAGES_LOCALIZATION_MATRIX.md`
- Existing infrastructure: `resources/views/partials/grimba-cookie-consent.blade.php`, `App\Support\GrimbaConsent`, `config/grimba_ads.php`
