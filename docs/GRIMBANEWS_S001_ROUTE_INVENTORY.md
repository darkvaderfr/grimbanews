# GrimbaNews S001 Route Inventory

**Sprint:** S001  
**Outcome:** route inventory  
**Status:** complete  
**Date:** 2026-04-29  
**Verification:** `php artisan route:list`, `php artisan route:list --except-vendor`, `php artisan route:list --path=admin/grimba --json`

S001 inventories the registered Laravel route surface so later admin route, public surface, scheduler, QA, security, and release-gate work can start from a known map.

## Route Counts

| Scope | Count | Verification |
|---|---:|---|
| All registered routes | 557 | `php artisan route:list` |
| Non-vendor routes | 312 | `php artisan route:list --except-vendor` |
| Grimba admin routes | 52 | `php artisan route:list --path=admin/grimba --json` |
| Public API routes | 42 | `php artisan route:list --path=api/v1` |
| Vault routes | 4 | `php artisan route:list --path=coffre` |

## Route Sources

| Source | Role |
|---|---|
| `routes/web.php` | Root route file, currently empty except PHP open tag. |
| `routes/console.php` | Scheduler and console route definitions; not counted in HTTP routes. |
| `platform/themes/echo/routes/web.php` | Primary Grimba public route file and theme route registration point. |
| `platform/themes/echo/functions/grimba-admin-cockpit.php` | Admin cockpit and cockpit actions. |
| `platform/themes/echo/functions/grimba-admin-rss-feeds.php` | Admin RSS feed CRUD and poll actions. |
| `platform/themes/echo/functions/grimba-admin-rss-drafts.php` | Admin RSS draft review and publish actions. |
| `platform/themes/echo/functions/grimba-admin-newsapi.php` | Admin NewsAPI settings, tests, run, and publish actions. |
| `platform/themes/echo/functions/grimba-admin-sources.php` | Admin source CRUD, triage, and quick classification actions. |
| `platform/themes/echo/functions/grimba-admin-clusters.php` | Admin story cluster CRUD, attach/detach, NobuAI summary, and coverage map routes. |
| `platform/themes/echo/functions/grimba-admin-translation.php` | Admin translation/provider settings and test actions. |
| `platform/themes/echo/functions/grimba-admin-cookies.php` | Admin cookie settings. |
| `platform/themes/echo/functions/grimba-admin-subscribers.php` | Admin subscriber list, export, delete, and toggle routes. |
| `platform/themes/echo/functions/grimba-post-preview.php` | Admin preview API routes for cluster/source/post form assistance. |
| Botble packages/plugins | CMS, auth, blog, pages, ads, analytics, media, language, translation, newsletter, contact, social login, comments, installer, and API routes. |

## Grimba Public Routes

| Method | URI | Route name | Purpose |
|---|---|---|---|
| GET | `/` | `public.index` | Theme homepage. |
| GET | `/comparatif` | `public.comparison.index` | Story comparison index. |
| GET | `/comparatif/{clusterId}` | `public.comparison` | Single cluster comparison view. |
| GET | `/feed.xml` | `public.feed` | Canonical Grimba RSS feed. |
| GET | `/feed` | `public.feed.alt` | Dev-server-safe feed alias. |
| GET | `/search` | `public.grimba-search` | Grimba search with source, owner, date, and bias facets. |
| GET | `/article/{slug}` | `public.grimba-article` | Blog story/article alias. |
| GET | `/command-palette.json` | `public.command-palette.index` | Public command palette data. |
| POST | `/translate/set` | `public.translate.set` | Legacy no-op translation endpoint. |
| POST | `/lang/set` | `public.lang.set` | Language cookie endpoint. |
| POST | `/region/set` | `public.region.set` | Region cookie endpoint. |
| POST | `/onboarding/complete` | `public.onboarding.complete` | Topic onboarding cookie endpoint. |
| POST | `/topics/follow` | `public.topics.follow` | Followed-topic cookie endpoint. |
| GET | `/pour-vous` | `public.for-you` | Personalized reader feed. |
| GET | `/pour-vous/export.csv` | `public.for-you.export` | Cookie-only reading history export. |
| POST | `/newsletter/subscribe` | `public.newsletter.subscribe` | Newsletter signup with reader-bias capture. |
| GET | `/methodologie` | `public.methodology` | Bias/source methodology page. |
| GET | `/account` | `public.account` | Reader account landing override. |
| GET | `/local` | `public.local` | Local news page. |
| POST | `/local/set` | `public.local.set` | Manual local location cookie endpoint. |
| GET | `/proprietaires` | `public.owners` | Media ownership map. |
| GET | `/sources` | `public.sources` | Source directory. |
| GET | `/sources/{slug}` | `public.source` | Source profile page. |
| GET | `/coffre` | `public.coffre` | Saved-for-later vault. |
| GET | `/coffre/partager` | `public.coffre.share` | Vault share page. |
| GET | `/coffre/depuis-lien` | `public.coffre.import` | Vault import page. |
| GET | `/coffre/export.csv` | `public.coffre.export` | Cookie-only vault CSV export. |
| GET | `/angles-morts` | `public.blindspot` | Blindspot page. |
| POST | `/cookie-consent/{action}` | `public.cookie-consent` | Cookie consent capture. |
| GET | `/img-proxy` | `public.img-proxy` | Constrained logo image proxy. |
| GET | `/og/home.png`, `/og/home` | `public.og.home`, `public.og.home.alt` | Homepage Open Graph image. |
| GET | `/og/post/{id}.png`, `/og/post/{id}` | `public.og.post`, `public.og.post.alt` | Post Open Graph image. |
| GET | `/og/story/{id}.png`, `/og/story/{id}` | `public.og.story`, `public.og.story.alt` | Story Open Graph image. |
| GET | `/og/{surface}.png`, `/og/{surface}` | `public.og.surface`, `public.og.surface.alt` | Local/coffre Open Graph images. |
| GET | `/og/placeholder/{id}.svg`, `/og/placeholder/{id}` | `public.og.placeholder`, `public.og.placeholder.alt` | Editorial image placeholder. |
| GET | `/ajax/categories/{categoryId}/posts` | `public.ajax.posts-by-category` | Category post AJAX. |
| GET | `/ajax/shortcode-blog-posts` | `public.ajax.shortcode-blog-posts` | Blog posts shortcode AJAX. |
| GET | `/ajax/shortcode-blog-categories` | `public.ajax.shortcode-blog-categories` | Blog categories shortcode AJAX. |
| GET | `/ajax/widget-blog-posts` | `public.ajax.widget-blog-posts` | Blog posts widget AJAX. |
| GET | `/ajax/widget-blog-categories` | `public.ajax.widget-blog-categories` | Blog categories widget AJAX. |
| GET | `/ajax/widget-breaking-news` | `public.ajax.widget-breaking-news` | Breaking news widget AJAX. |
| GET | `/ajax/menu-sidebar` | `public.ajax.menu-sidebar` | Sidebar menu AJAX. |

## Grimba Admin Routes

All Grimba admin routes are registered under `admin/grimba`, use `web`, `core`, and `Illuminate\Auth\Middleware\Authenticate`, and are named with the `grimba.` prefix.

| Method | URI | Route name |
|---|---|---|
| GET | `admin/grimba/api/preview/cluster-suggest` | `grimba.api.preview.cluster-suggest` |
| GET | `admin/grimba/api/preview/cluster/{id}` | `grimba.api.preview.cluster` |
| GET | `admin/grimba/api/preview/source/{id}` | `grimba.api.preview.source` |
| GET | `admin/grimba/cockpit` | `grimba.cockpit` |
| POST | `admin/grimba/cockpit/nobuai-summaries` | `grimba.cockpit.nobuai-summaries` |
| POST | `admin/grimba/cockpit/runbook` | `grimba.cockpit.runbook` |
| GET | `admin/grimba/cookies` | `grimba.cookies.index` |
| POST | `admin/grimba/cookies` | `grimba.cookies.save` |
| GET | `admin/grimba/coverage-map` | `grimba.coverage-map.index` |
| GET | `admin/grimba/news-sources` | `grimba.news-sources.index` |
| POST | `admin/grimba/news-sources` | `grimba.news-sources.store` |
| GET | `admin/grimba/news-sources/create` | `grimba.news-sources.create` |
| GET | `admin/grimba/news-sources/triage` | `grimba.news-sources.triage` |
| GET | `admin/grimba/news-sources/{id}/edit` | `grimba.news-sources.edit` |
| PUT | `admin/grimba/news-sources/{id}` | `grimba.news-sources.update` |
| DELETE | `admin/grimba/news-sources/{id}` | `grimba.news-sources.destroy` |
| POST | `admin/grimba/news-sources/{id}/quick-classify` | `grimba.news-sources.quick-classify` |
| GET | `admin/grimba/newsapi` | `grimba.newsapi.index` |
| POST | `admin/grimba/newsapi` | `grimba.newsapi.save` |
| POST | `admin/grimba/newsapi/test` | `grimba.newsapi.test` |
| POST | `admin/grimba/newsapi/run` | `grimba.newsapi.run` |
| POST | `admin/grimba/newsapi/publish-drafts` | `grimba.newsapi.publish-drafts` |
| GET | `admin/grimba/rss-drafts` | `grimba.rss-drafts.index` |
| POST | `admin/grimba/rss-drafts/publish` | `grimba.rss-drafts.publish` |
| POST | `admin/grimba/rss-drafts/delete` | `grimba.rss-drafts.delete` |
| POST | `admin/grimba/rss-drafts/{id}/publish` | `grimba.rss-drafts.publish-one` |
| GET | `admin/grimba/rss-feeds` | `grimba.rss-feeds.index` |
| POST | `admin/grimba/rss-feeds` | `grimba.rss-feeds.store` |
| GET | `admin/grimba/rss-feeds/create` | `grimba.rss-feeds.create` |
| GET | `admin/grimba/rss-feeds/{id}/edit` | `grimba.rss-feeds.edit` |
| PUT | `admin/grimba/rss-feeds/{id}` | `grimba.rss-feeds.update` |
| DELETE | `admin/grimba/rss-feeds/{id}` | `grimba.rss-feeds.destroy` |
| POST | `admin/grimba/rss-feeds/{id}/poll-now` | `grimba.rss-feeds.poll-now` |
| POST | `admin/grimba/rss-feeds/{id}/toggle` | `grimba.rss-feeds.toggle` |
| POST | `admin/grimba/rss-feeds/poll-all` | `grimba.rss-feeds.poll-all` |
| GET | `admin/grimba/story-clusters` | `grimba.story-clusters.index` |
| POST | `admin/grimba/story-clusters` | `grimba.story-clusters.store` |
| GET | `admin/grimba/story-clusters/create` | `grimba.story-clusters.create` |
| GET | `admin/grimba/story-clusters/{id}/edit` | `grimba.story-clusters.edit` |
| PUT | `admin/grimba/story-clusters/{id}` | `grimba.story-clusters.update` |
| DELETE | `admin/grimba/story-clusters/{id}` | `grimba.story-clusters.destroy` |
| POST | `admin/grimba/story-clusters/{id}/attach` | `grimba.story-clusters.attach` |
| POST | `admin/grimba/story-clusters/{id}/detach` | `grimba.story-clusters.detach` |
| POST | `admin/grimba/story-clusters/{id}/nobuai-summary` | `grimba.story-clusters.nobuai-summary` |
| GET | `admin/grimba/subscribers` | `grimba.subscribers.index` |
| GET | `admin/grimba/subscribers/export.csv` | `grimba.subscribers.export` |
| DELETE | `admin/grimba/subscribers/{id}` | `grimba.subscribers.destroy` |
| POST | `admin/grimba/subscribers/{id}/toggle` | `grimba.subscribers.toggle` |
| GET | `admin/grimba/translation` | `grimba.translation.index` |
| POST | `admin/grimba/translation` | `grimba.translation.save` |
| POST | `admin/grimba/translation/test` | `grimba.translation.test` |
| POST | `admin/grimba/translation/nobuai-test` | `grimba.translation.nobuai-test` |

## API Surface

`api/v1` currently exposes 42 routes from Botble/plugin modules:

- Ads: `GET /api/v1/ads`.
- Blog: posts, categories, tags, filters, and search.
- Pages: page index and show.
- Contact: `POST /api/v1/contacts`.
- Language: language list and current language.
- Auth/profile: register, login, logout, password reset, email check, social auth, profile, avatar, password, settings.
- Notifications and device tokens.

No Grimba-specific `api/v1/grimba/*` namespace exists yet.

## Catch-All And Ordering Notes

- Botble theme catch-alls are registered late: `{key}.{extension}`, `{prefix}/{slug?}`, and `{slug?}`.
- Grimba public routes in `platform/themes/echo/routes/web.php` must stay before `Theme::routes()` so explicit pages like `/sources`, `/search`, `/coffre`, and `/angles-morts` win over catch-alls.
- `/article/{slug}` delegates to Botble's public blog view and is constrained to `[A-Za-z0-9\-_]+`.
- Public AJAX routes are guarded by `RequiresJsonRequestMiddleware`.

## Risks And Follow-Ups

- Many Grimba routes are closures. If production uses `php artisan route:cache`, these routes will need controller extraction first.
- Install routes are present in the route table. S007/S081 security and environment audits should confirm install middleware fully blocks them in pre-prod/prod.
- The legacy `/translate/set` endpoint intentionally returns a no-op response after the earlier translation feature removal. The S301-S350 translation lane should decide whether to replace or remove this compatibility route.
- Public cookie endpoints set long-lived cookies. S007/S145/S071 privacy and cookie-policy work should verify consent, encryption exceptions, SameSite, Secure, and retention behavior.
- Grimba admin routes are authenticated, but the next admin-route inventory sprint should verify permission granularity, CSRF coverage, destructive-action confirmations, and provider-secret redaction.

## Closeout

Sprint: S001  
Outcome: route inventory complete  
Files: `docs/GRIMBANEWS_S001_ROUTE_INVENTORY.md`, `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md`  
Verification: `php artisan route:list`; `php artisan route:list --except-vendor`; `php artisan route:list --path=admin/grimba --json`; route-source reads with `rg` and `sed`  
Risks: closure route cache incompatibility, install-route exposure audit needed, cookie/security follow-ups needed  
Next: S002 admin route inventory  
Commit: recorded in sprint handoff after push
