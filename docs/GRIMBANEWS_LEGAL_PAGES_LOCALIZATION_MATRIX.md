# GrimbaNews — Legal Pages Localization Matrix

**Status:** matrix v0 (per-locale × per-page coverage tracker)
**Owner:** Sara Chen (CISO) + Lucy Leai (Strategy) — counsel review per jurisdiction
**Walks:** Mythos S1147 (per-locale legal pages) deferred → partial
**Gating dependency:** Each per-locale variant needs counsel review for the relevant jurisdiction. Matrix itself is operator-side and identifies which counsel engagement is required per locale.

## Why this exists

S1147 was honest-deferred as "FR+EN today; per-locale variants need counsel + catalogs." The matrix itself — what pages exist, what locales need them, what counsel review applies — is operator-side enumeration. Shipping the matrix turns S1147 from "deferred no plan" to "deferred but plan known" — that's the partial walk.

## Current state (2026-05-22)

| Page | FR | EN | ES | PT-BR | DE | IT | AR | JA | ZH | KO | RU | HE | HI | SW |
|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|
| `/mentions-legales` (Legal Notice) | shipped | shipped | deferred | deferred | deferred | deferred | deferred | deferred | deferred | deferred | deferred | deferred | deferred | deferred |
| `/politique-de-confidentialite` (Privacy Policy) | shipped | shipped | deferred | deferred | deferred | deferred | deferred | deferred | deferred | deferred | deferred | deferred | deferred | deferred |
| `/cgu` (Terms of Use) | shipped | shipped | deferred | deferred | deferred | deferred | deferred | deferred | deferred | deferred | deferred | deferred | deferred | deferred |
| `/politique-cookies` (Cookie Policy) | partial (cookie banner) | partial (cookie banner) | deferred | deferred | deferred | deferred | deferred | deferred | deferred | deferred | deferred | deferred | deferred | deferred |
| `/charte-editoriale` (Editorial Charter) | deferred | deferred | deferred | deferred | deferred | deferred | deferred | deferred | deferred | deferred | deferred | deferred | deferred | deferred |
| `/corrections` (Corrections policy) | deferred per S2006 | deferred | deferred | deferred | deferred | deferred | deferred | deferred | deferred | deferred | deferred | deferred | deferred | deferred |
| `/vos-droits` (Reader Rights) | deferred per S2036 | deferred | deferred | deferred | deferred | deferred | deferred | deferred | deferred | deferred | deferred | deferred | deferred | deferred |

(All non-FR/EN columns are gated on the i18n catalog band per S1101-S1140.)

## Per-jurisdiction counsel requirement

Per-locale ≠ per-jurisdiction. One catalog can serve multiple jurisdictions; one jurisdiction may need policy tweaks within a shared catalog.

| Jurisdiction(s) | Reader-locale | Counsel needed | Key concerns |
|---|---|---|---|
| France (FR-FR) | FR | French press counsel | CNIL guidance, loi Informatique et Libertés, Loi pour la confiance dans l'économie numérique |
| Belgium / Switzerland / Quebec (FR-BE/CH/QC) | FR | Local counsel each | BE: GDPR + DPA Belgium; CH: nLPD; QC: Law 25 |
| EU broadly (EN as second language for EU readers) | EN | EU counsel | GDPR + ePrivacy + DSA + DMA |
| UK (EN-GB) | EN | UK counsel | UK GDPR + DPA 2018 + PECR |
| US (EN-US) | EN | US counsel | CCPA + state laws (Virginia, Colorado, Connecticut, etc.); Section 230 attribution language |
| Spain / Latin-America (ES) | ES (S1101+) | ES + LATAM counsel | RGPD + LOPDGDD; per-country LATAM (LGPD-style) |
| Brazil (PT-BR) | PT-BR (S1111+) | BR counsel | LGPD |
| Germany / Austria / CH-DE (DE) | DE (S1121+) | DE/AT counsel | GDPR + BDSG + TTDSG |
| Italy (IT) | IT (S1131+) | IT counsel | GDPR + Codice Privacy |
| MENA (AR) | AR (S1132+) | Regional counsel | Per-country; Saudi PDPL + UAE PDPL + Bahrain etc. |
| Other locales | per the wide set | Local counsel | per-jurisdiction |

## Page-by-page coverage map

### `/mentions-legales` (Legal Notice / Impressum)

- **France:** publisher identity (Director of Publication), hosting provider, ISSN if applicable.
- **DE/AT:** Impressum (TMG § 5) — strict format.
- **EU broadly:** publisher contact under E-Commerce Directive.

Counsel must validate per-jurisdiction format. Reusable across locales sharing a jurisdiction (FR mentions-legales covers FR-FR, FR-BE separately).

### `/politique-de-confidentialite` (Privacy Policy)

- Aligned with `docs/GRIMBANEWS_GDPR_ROPA.md` activity catalogue.
- Per-jurisdiction variants for: data-protection authority contact (CNIL vs ICO vs CCPA AG), cross-border-transfer mechanisms, retention periods if local law differs.

### `/cgu` (Terms of Use)

- Liability limits per-jurisdiction (some EU member states cap liability differently).
- Choice-of-law + jurisdiction clauses.
- US-specific: Section 230 safe-harbor invocation, DMCA designated-agent registration (separate doc).

### `/politique-cookies` (Cookie Policy)

- Per `docs/GRIMBANEWS_CONSENT_LOG_DESIGN.md` schema once shipped.
- Enumerate each cookie (categories per S1862).
- Per-jurisdiction consent regime (EU ePrivacy = opt-in; US = opt-out).

### `/charte-editoriale` (Editorial Charter)

- Editorial independence statement.
- Conflict-of-interest policy (per-staff).
- Sources / fact-checking / corrections methodology.
- Gates on S2148 IFCN signatory pursuit for global alignment.

### `/corrections` (Corrections Policy)

- Gates on S2006 corrections primitive shipping.
- Per-locale once corrections workflow lands.

### `/vos-droits` (Reader Rights Education)

- Gates on S2036 ombudsman education page.
- Per-jurisdiction tailored — reader rights vary by GDPR vs CCPA vs LGPD scope.

## Ship cadence

Recommended order when catalogs ship and counsel engages:

1. **EN expansion review** — current EN pages are FR-translated; UK counsel + US counsel each should review the EN versions for their respective jurisdiction tailoring before launching paid features.
2. **ES + PT-BR** — large reader population growth potential per Afrique-International editorial pivot (Lusophone Africa + ES LATAM).
3. **DE** — German privacy bar is high; counsel review must happen before any DE-locale launch.
4. **IT** — easier (close to FR template).
5. **AR/JA/ZH/KO/RU/HE/HI/SW** — long-tail; sequence per S1132-S1139 catalog priority.

## Activation checklist (per locale)

For each locale activation:

1. Confirm catalog shipped (`lang/{locale}.json` exists per S1101+).
2. Engage per-jurisdiction counsel from the table above.
3. Translate/adapt each page in the matrix (FR base → target).
4. Counsel sign-off.
5. Wire route handlers (current pages live at fixed paths; per-locale would resolve via locale middleware).
6. Update this matrix row from `deferred` → `shipped`.
7. Update `docs/GRIMBANEWS_GDPR_ROPA.md` if new transfer mechanism added.

## Cross-references

- Master plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1147 row; dependencies for S1110/S1120/S1130/S1140 locale-launch readiness)
- GDPR record: `docs/GRIMBANEWS_GDPR_ROPA.md`
- Consent log design: `docs/GRIMBANEWS_CONSENT_LOG_DESIGN.md`
- Locale middleware: `app/Http/Middleware/GrimbaLocaleEnforce.php`
- Cookie banner partial: `platform/themes/echo/partials/cookie-consent.blade.php`
- i18n catalogs: `lang/fr.json` (555 keys), `lang/en.json` (506 keys); future `lang/{locale}.json`
