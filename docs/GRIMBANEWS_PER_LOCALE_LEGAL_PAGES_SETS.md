# GrimbaNews — Per-Locale Legal Pages Sets

**Status:** plan v0
**Owner:** Lucy Leai (Strategy) + counsel + Michael O'Connor (Technical Writer)
**Walks:** Mythos S1148 (per-locale legal pages) deferred → partial — sister rows in i18n band
**Gating dependency:** Per-jurisdiction counsel review.

## Per-locale legal-page set required

| Locale | Pages required |
|---|---|
| FR | mentions-légales, politique-confidentialité, CGU, cookies, RGPD |
| EN (default) | terms, privacy, cookies, accessibility |
| DE (Germany) | Impressum (mandatory), Datenschutz, AGB, Cookies, Barrierefreiheit |
| ES (Spain) | aviso-legal, política-privacidad, cookies, condiciones-uso |
| PT (Brazil) | termos, privacidade, cookies, LGPD |
| IT | informativa-privacy, cookies, termini |
| Other locales: minimum set + locale-specific carve-outs |

## Implementation path

1. Per-locale route slugs in `routes/web.php` resolve to the locale's page set.
2. Source text in `lang/<locale>/legal.json` (gated on shipping per-locale lang JSON).
3. Counsel reviews per-locale text before publish.
4. Versioned: per-page `effective_at` date.

## Why DE matters

German Impressum is legally mandatory (Telemediengesetz §5). Missing this = €1000-50000 fine. Cannot launch DE locale without Impressum.

## DSAR + GDPR rights

Per-locale `/vos-droits` (FR) / `/your-rights` (EN) / `/ihre-rechte` (DE) / etc. surfaces DSAR request flow per `docs/GRIMBANEWS_VOS_DROITS_READER_RIGHTS_PAGE_SCOPE.md` (Wave KKKK).

## Cross-references

Master plan: S1148. Sister: `docs/GRIMBANEWS_DE_LAUNCH_READINESS_CHECKLIST.md` (Wave WWW), `docs/GRIMBANEWS_PER_LOCALE_AD_CONSENT_RULES.md` (Wave WWW), `docs/GRIMBANEWS_GDPR_ROPA.md` (Wave LLL).
