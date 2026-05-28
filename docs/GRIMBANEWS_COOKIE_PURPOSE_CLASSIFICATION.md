# GrimbaNews — Cookie Purpose Classification

**Status:** plan v0
**Owner:** Sara Chen (CISO) + Nina Patel (Lead FE) + counsel
**Walks:** Mythos S1862 (cookie purpose classification) deferred → partial
**Gating dependency:** S1861; current consent banner is binary accept/reject without per-category granularity.

## Cookie categories per EDPB

Per EDPB Cookie Guidelines + ePrivacy:

### Strictly necessary (no consent required)
- Session cookies (auth, CSRF token).
- Cookie consent state itself.
- `grimba_locale` (preferred locale).
- `grimba_region` (preferred edition).
- `grimba_consent_v` (consent version tracking).

### Functional (consent recommended; opt-out OK)
- `grimba_dark_mode` (theme preference).
- `grimba_read` (read article IDs for pour-vous).
- `grimba_coffre_local` (saved articles for non-logged-in).

### Analytics (consent required)
- Server-side analytics: no cookies; aggregate logs only.
- Currently no third-party analytics (no Google Analytics).
- Future: per-page-view server beacon (no cookie).

### Advertising (consent required)
- Currently no per-reader ad targeting.
- Future: per-ad-network cookies require explicit opt-in.

## Per-category cookie inventory

Per-cookie per-category labeled in code via comment:
```php
// Cookie category: strictly-necessary
Cookie::queue('grimba_locale', $locale, 365 * 24 * 60);
```

## Cookie banner v2 design

Per-category granular toggle:
- Strictly necessary: always on (greyed checkbox).
- Functional: opt-in (checked default per reader friction-vs-compliance trade-off).
- Analytics: opt-in (unchecked default; explicit accept required).
- Advertising: opt-in (unchecked default; explicit accept required).

## Per-toggle storage

Per-category consent state stored in `grimba_consent_v` cookie (encoded JSON: `{strict:1, func:1, analytics:0, ads:0}`).

## Cross-references

Master plan: S1862. Sister: `docs/GRIMBANEWS_PER_LOCALE_AD_CONSENT_RULES.md` (Wave WWW), `docs/GRIMBANEWS_CONSENT_LOG_DESIGN.md` (Wave LLL).
