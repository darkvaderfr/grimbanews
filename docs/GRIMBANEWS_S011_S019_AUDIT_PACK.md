# S011–S019 — Audit Pack (Content, Source, Cluster, Translation, NobuAI, Provider, Ad, Cookie, Cache)

**Generated:** 2026-05-19
**Method:** live DB queries via `php artisan tinker` against the working snapshot at /Users/vb/GrimbaNews.

Bundled as one doc instead of nine because each audit is a small query result + one-line interpretation. Splitting would create more noise than signal.

---

## S011 — Content volume audit

| Metric | Value |
|---|---|
| Posts published | 4,578 |
| Posts drafted | 0 |
| FR posts | 2,004 |
| EN posts | 2,535 |
| Unclassified (NULL language) posts | 38 (~0.8%) |

**Verdict:** ✓ healthy floor — well above the launch threshold (BACKFILL-CAT-1 target was 500 per category). Mix slightly favors EN globally but FR is dominant on the home/breaking/latest rails after S-LSAT-06 locale filter. Unclassified pool tiny (~0.8%) — far below 5% release-blocker threshold; will trickle further via daily backfill cron.

## S012 — Source volume audit

| Metric | Value |
|---|---|
| Total news sources | 688 |
| Top source by post count | Libération (226) |
| Second | The Guardian (168) |
| Third | BBC (158) |
| Fourth | France 24 (153) |
| Fifth | Le Monde (150) |

**Verdict:** ✓ 688 sources; long tail expected. Top 5 dominate but ratio reasonable for an early-stage product. Source diversity passes for S214 (source diversity target).

## S013 — Cluster quality audit

| Metric | Value |
|---|---|
| Distinct clusters (story_cluster_id NOT NULL) | 704 |
| Single-language clusters (dossiers) | 649 (post-S-LANG-11 backfill) |
| Multi-source clusters | tracked per cluster; observed range 1-12 sources per dossier |

**Verdict:** ✓ 704 clusters viable for /dossiers and /comparatif/{id}. S-LANG-11 dossier `primary_language` denorm backfilled 649 dossiers (340 FR / 300 EN / 9 unknown).

## S014 — Translation storage audit

| Metric | Value |
|---|---|
| `grimba_post_translations` rows | 6 |
| `summary_nobuai` non-null | 145 |
| `summary_nobuai_locale` populated | 145 (S-LANG-08 backfill) |

**Verdict:** ⚠ low translation volume — only 6 explicit cross-locale rows. Reader sorting (`GrimbaTranslationPresenter::orderForTargetLocale`) and NULL-rank-3 fallback compensate. To grow this, ship the `grimba:translate-by-rule` (S-LSAT-10) operator surface so Vader can set thresholds (e.g., translate any article hitting 500 views).

## S015 — NobuAI storage audit

| Metric | Value |
|---|---|
| Posts with `summary_nobuai` populated | 145 (~3.2% coverage) |
| Posts with `summary_nobuai_locale` populated | 145 (matches above; S-LANG-08 atomicity) |

**Verdict:** ⚠ coverage is intentionally low — NobuAI summaries are queued opportunistically, not on every published post. Operator-controlled regeneration via cockpit covers the rest. S281 (stale insight refresh) closes this gap when operator can specify a refresh cadence.

## S016 — Provider setting audit

NobuAI provider configuration lives in admin settings (admin-only — never user-visible per CLAUDE.md NobuAI brand purity rule).

| Provider | Configured | Health |
|---|---|---|
| Anthropic | yes | `GrimbaProviderCredits` accounting active |
| OpenAI | yes | secondary fallback |
| Gemini | partial | |
| OpenRouter | optional |  |

**Verdict:** ✓ multiple providers via vault. Redaction confirmed via `tests/Unit/GrimbaProviderCreditsTest.php`. Provider names never leak to readers (Wave OOOO brand purity scanner + `tests/Feature/GrimbaNobuAiBrandPurityTest.php` enforce).

## S017 — Ad setting audit

| Setting | Value |
|---|---|
| Ad provider | `grimba_ads_provider` — Botble settings table |
| Ad slot locations | `grimba_home_top`, `grimba_home_mid`, `grimba_home_native`, `grimba_post_inline`, `grimba_post_sidebar`, `grimba_search_top`, `grimba_source_top` |
| AdSense client ID | configured via admin |
| Consent gating | `partials/cookie-consent.blade.php` |
| Subscriber suppression | partial — entitlement gating planned for S884 |
| CLS reserved space | planned for S874 (open) |

**Verdict:** ⚠ wired but soft. Subscriber-ad-free flag, CLS reservation, and revenue dashboard remain open.

## S018 — Cookie audit

| Cookie | Purpose | Encryption | Same-site |
|---|---|---|---|
| `XSRF-TOKEN` | CSRF protection | Laravel default (encrypted) | lax |
| `botble_session` | session ID | Laravel default (encrypted, httpOnly) | lax |
| `grimba_theme` | dark/light pref | not encrypted (display-only) | lax |
| `grimba_lang` | locale override | not encrypted (display-only) | lax |
| `grimba_region` | edition (Afrique vs International) | not encrypted (display-only) | lax |
| `grimba_consent` | cookie consent state | not encrypted (display-only) | lax |
| `grimba_vault` | saved-article IDs | not encrypted (display-only) | lax |
| `grimba_bias_convention` | FR/US color convention | localStorage (not cookie) | n/a |

**Verdict:** ✓ session + CSRF use Laravel's encryption + httpOnly. Display-only cookies kept unencrypted for client-side JS access (theme/lang/region). No PII in cookies.

## S019 — Cache audit

| Cache | Driver | Used by |
|---|---|---|
| `config:cache` | file | route + view compilation |
| Laravel route cache | file | `php artisan route:cache` |
| Laravel view cache | file | Blade compilation |
| Application cache | `database` (default) per `.env` | `GrimbaProviderCredits`, language detector results, etc. |
| HTTP response cache | per-route via response()->header() | Wave RRRRRRR /feed.xml (`public, max-age=600, s-maxage=1800`), Wave AAAAAAAA /sitemap-grimba.xml (`public, max-age=3600, s-maxage=21600`) |
| Browser/CDN cache | per-asset | static assets shipped from `/public` |

**Verdict:** ✓ multi-layer caching in place. Editorial public pages (`/a-propos` etc.) intentionally NOT public-cached (Wave YYYYYYY revert) because chrome layout renders a per-session csrf-token meta. Future plan: strip csrf-token meta from grimba-chrome → then editorial public-cache becomes safe.

---

## Closes

- S011 content volume audit
- S012 source volume audit
- S013 cluster quality audit
- S014 translation storage audit
- S015 NobuAI storage audit
- S016 provider setting audit
- S017 ad setting audit
- S018 cookie audit
- S019 cache audit

**Bundled total: 9 sprints closed via single audit pack.**
