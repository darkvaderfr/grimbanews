# S007 — Public Surface Inventory

**Generated:** 2026-05-19
**Method:** `php artisan route:list` filtered to exclude admin/debugbar/telescope/horizon/sanctum/_ignition.
**Count:** 191 public routes (across reader UI, public API v1, member account UI, AJAX helpers, RSS feeds, public XML endpoints).

## Categories

| Category | Examples | Approximate count |
|---|---|---|
| Reader UI (FR canonical) | `/`, `/breaking`, `/latest`, `/dossiers`, `/comparatif/{id}`, `/sources`, `/sources/{slug}`, `/search`, `/local`, `/coffre`, `/coffre/export.csv`, `/coffre-share`, `/pour-vous`, `/angles-morts`, `/blog/{slug}`, `/article/{slug}`, `/methodologie`, `/comprendre-le-barometre`, `/a-propos`, `/faq`, `/advertise`, `/contact`, `/conditions`, `/confidentialite`, `/cookie-policy`, `/team`, `/carrieres`, `/proprietaires`, `/proprietaires/{slug}` | ~36 |
| Editorial editions (FR + EN aliases) | `/afrique`, `/amerique`, etc. | ~12 |
| RSS feeds | `/feed.xml`, `/feed`, `/feed.breaking.xml`, `/feed.latest.xml` | 4 |
| Public XML endpoints | `/sitemap.xml`, `/sitemap-grimba.xml` (Wave AAAAAAAA dynamic), `/pages.xml`, `/blog-posts-*-page-*.xml`, `/ads.txt`, `/robots.txt` (static), `/.well-known/security.txt` (Wave NNNNNNN), `/health` (Wave RRRRR JSON uptime endpoint) | ~10 |
| Image proxy | `/img-proxy` (Wave SSSSS SSRF guard + Wave QQQQQQQ lock test) | 1 |
| Public API v1 | `/api/v1/ads`, `/api/v1/categories`, `/api/v1/categories/{slug}`, `/api/v1/device-tokens`, `/api/v1/languages`, `/api/v1/languages/current`, `/api/v1/logout`, `/api/v1/me`, `/api/v1/notifications`, `/api/v1/notifications/stats`, `/api/v1/pages`, `/api/v1/pages/{id}`, `/api/v1/posts`, `/api/v1/posts/{slug}`, `/api/v1/posts/filters`, `/api/v1/search`, `/api/v1/tags`, `/api/v1/tags/{slug}` | ~30 |
| Member account UI (auth-gated) | `/account`, `/account/dashboard`, `/account/posts/*`, `/account/settings`, `/account/tables/*`, `/account/saved-searches/{id}` | ~12 |
| AJAX helpers | `/ajax/announcements`, `/ajax/categories/{categoryId}/posts`, `/ajax/members/activity-logs`, `/ajax/members/tags/all`, `/ajax/menu-sidebar`, `/ajax/newsletter/popup`, `/ajax/shortcode-blog-categories`, `/ajax/shortcode-blog-posts`, `/ajax/widget-blog-categories`, `/ajax/widget-blog-posts`, `/ajax/widget-breaking-news` | 11 |
| Sponsor/Lead | `/advertise/leads` (POST capture, S-ADS-12) | 1 |
| Click trackers | `/ads-click/{key}` | 1 |
| OG image generator | `/og/page`, `/og/home.png`, `/og/post/{id}.png`, `/og/story/{cluster_id}.png` | ~4 |
| Misc | `/cookie-consent`, `/csrf-cookie`, etc. | ~5 |

## SEO-relevant surface posture (recapped)

After the 2026-05-18 → 2026-05-19 SEO waves (RRRRRR–WWWWWWW + AAAAAAAA + BBBBBBBB):

- **Canonical URL on every public reader surface** via `partials/seo-meta-config.blade.php`; pagination canonicals self-reference (Wave BBBBBBBB)
- **`<meta name="robots">`** on every reader surface — `noindex, follow` on personalized (`/search`, `/coffre`, `/account`, `/pour-vous`, `/local`) and 404 (Wave WWWWWWW)
- **3+ JSON-LD blocks** on every reader surface (NewsArticle on /blog/{slug}, CollectionPage on /breaking + /latest + /dossiers + /angles-morts, AboutPage on /a-propos + /comprendre-le-barometre, TechArticle on /methodologie, FAQPage on /faq, Service on /advertise, SearchResultsPage on /search) — all JSON-LD passes parse-validity lock + has no literal `</script>` (Wave XXXXXXX + YYYYYYY)
- **Per-surface OG image** — /og/home.png for listings, /og/post/{id}.png for articles, /og/story/{cluster_id}.png for clusters (Wave LLLLLL)
- **Twitter card** — single `summary_large_image` + manual twitter:image emission post-`Theme::header()` (Wave GGGGGG dedupe)
- **Locale alternation** — fr_FR ↔ en_US via og:locale + og:locale:alternate (Wave IIIIII)
- **hreflang** — `fr / en / x-default` (S-LANG-06)
- **404 page** — Theme::set('grimba_is_404') triggers no-canonical + noindex (Wave WWWWWWW + ZZZZZZZ single-owner clear)

## Security posture

- HSTS on HTTPS, X-Content-Type-Options: nosniff, X-Frame-Options: SAMEORIGIN, Referrer-Policy: strict-origin-when-cross-origin, hardened CSP, Permissions-Policy via `app/Http/Middleware/GrimbaSecurityHeaders.php` (Wave ZZZZZ + TTTTTTT lock test)
- security.txt at `.well-known/` per RFC 9116 (Wave NNNNNNN + PPPPPPP lock test)
- robots.txt Disallows /admin /coffre /account /member /vendor /storage/framework /core /cache; deliberately keeps /search /pour-vous /local /feed.xml CRAWLABLE so noindex meta is honored (Wave VVVVVVV)

## Closes

- S007 (public surface inventory)
