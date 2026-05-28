# GrimbaNews — Cookie Lifetime Audit

**Status:** plan v0
**Owner:** Sara Chen (CISO) + Nina Patel (Lead FE)
**Walks:** Mythos S1863 (cookie lifetime audit) deferred → partial
**Gating dependency:** Cookie purpose classification (Wave SUB-48 sister).

## Per-cookie lifetime policy

Per EDPB best practice + ePrivacy: cookies should have minimum-necessary lifetime.

## Per-category lifetime defaults

- **Strictly necessary:** session-only (deleted on browser close) OR 1 year max.
- **Functional:** 6 months default; 1 year max with rationale.
- **Analytics:** 13 months (Google Analytics convention); justify if longer.
- **Advertising:** 13 months default; per-vendor varies.

## Per-cookie audit table

```
| Cookie | Category | Current TTL | Recommended | Status |
|---|---|---|---|---|
| grimba_locale | strict | 1 year | 1 year | OK |
| grimba_region | strict | 1 year | 1 year | OK |
| grimba_consent_v | strict | 13 months | 13 months | OK (EDPB recommendation) |
| grimba_dark_mode | functional | 6 months | 6 months | OK |
| grimba_read | functional | 90 days | 90 days | OK |
| grimba_coffre_local | functional | 1 year | 6 months | REVIEW |
| Botble session | strict | session | session | OK |
| Botble CSRF | strict | session | session | OK |
| Botble laravel_session | strict | 2 hours | 2 hours | OK |
```

## Per-cookie quarterly review

Per-quarter: Sara + Nina review per-cookie lifetimes vs policy.

## Per-cookie automated check

Per-CI: scan PHP for `Cookie::queue` / `Cookie::make` calls + flag overruns vs policy table.

## Cross-references

Master plan: S1863. Sister: `docs/GRIMBANEWS_COOKIE_PURPOSE_CLASSIFICATION.md`.
