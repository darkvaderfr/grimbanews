# S008 — Admin Surface Inventory

**Generated:** 2026-05-19
**Method:** `php artisan route:list` filtered to admin/*.
**Count:** 407 admin routes (across cockpit, content management, ingest config, NobuAI vault, translation, ads, sources, members, media, settings, debugbar, etc.).

## Categories

| Category | Examples | Approximate count |
|---|---|---|
| GrimbaNews cockpit | `/admin/grimba/cockpit`, `/admin/grimba/translation-map` (S-LANG-10), `/admin/grimba/translation-rules` (S-LSAT-13/15), `/admin/grimba/coverage-map` (S-LANG-13), `/admin/grimba/ads`, `/admin/grimba/ads-leads`, `/admin/grimba/newsdata-io` (S-NDI-11/12/13/14) | ~20 |
| Botble blog | `/admin/posts`, `/admin/categories`, `/admin/tags`, `/admin/posts/edit/{id}`, etc. | ~60 |
| Botble core (auth, ACL, settings, dashboards) | `/admin/profile`, `/admin/dashboard`, `/admin/users`, `/admin/users/{id}/edit`, `/admin/setting/*`, `/admin/system/*`, etc. | ~100 |
| Member admin | `/admin/members`, `/admin/members/{id}/edit`, members tables | ~30 |
| NewsAPI / RSS / source admin | `/admin/grimba/news-api`, `/admin/grimba/rss-feeds`, `/admin/grimba/sources`, `/admin/grimba/clusters` | ~40 |
| Media management | `/admin/media`, `/admin/media/popup`, `/admin/media/files/*`, `/admin/media/folders/*` | ~30 |
| Notifications + alerts | `/admin/notifications`, `/admin/notifications/{id}` | ~10 |
| Ads / sponsors | `/admin/grimba/ads`, `/admin/grimba/advertiser-leads` (S-ADS-12) | ~10 |
| Audit logs | `/admin/system/audit-log`, `/admin/system/activity-logs` | ~5 |
| Marketplace / plugins / settings | `/admin/plugins`, `/admin/settings/general/*`, `/admin/settings/website-tracking/*`, etc. | ~80 |
| Debug + telemetry | debugbar routes (disabled in `AppServiceProvider::disableDebugbarOnAdmin()` for stable admin UI) | ~10 |

## Auth posture

- All `/admin/*` routes are gated by Botble's admin guard. Anonymous requests hit the login redirect.
- `GrimbaAdminRootRedirect` middleware ensures `/admin` lands on the Botble dashboard rather than 404.
- Provider keys + member credentials never leave the admin surface — never user-visible per CLAUDE.md NobuAI brand purity rule.
- CSRF token required on every POST/PUT/DELETE; encryption defaults via Laravel.
- Robots.txt Disallows /admin (Wave VVVVVVV) — server-side auth is still the real gate; this is a crawler hint.

## Cockpit-board surfaces (Wave CCCC and prior)

`resources/views/grimba-admin/cockpit.blade.php` aggregates:
- Automation run ledger (S162/S164/S612)
- Ingest provenance per provider (RSS, NewsAPI, manual)
- Translation map link
- Source health/quarantine summary
- NobuAI provider credit accounting (`GrimbaProviderCredits`)
- Health badge: `grimba:health --fail-on-risk` last result

## Closes

- S008 (admin surface inventory)
