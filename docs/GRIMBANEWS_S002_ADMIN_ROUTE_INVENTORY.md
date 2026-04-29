# GrimbaNews S002 Admin Route Inventory

**Sprint:** S002  
**Outcome:** admin route inventory  
**Status:** complete  
**Date:** 2026-04-29  
**Verification:** `php artisan route:list --path=admin/grimba --json`, source reads under `platform/themes/echo/functions/grimba-admin-*.php`

S002 breaks down the GrimbaNews admin route surface identified in S001. The goal is to give G5 Admin Readiness, G7 Security Readiness, and later browser QA a precise map of admin modules, actions, ownership files, and route-level risk.

## Summary

| Item | Result |
|---|---:|
| Total `admin/grimba` routes | 52 |
| Admin modules | 11 |
| Route implementation style | Laravel closures |
| Shared middleware | `web`, `core`, `auth` |
| Route name prefix | `grimba.` |
| Admin prefix source | `BaseHelper::getAdminPrefix()` |

All Grimba admin routes are authenticated by Botble admin auth. None of the custom Grimba admin route groups declare granular per-module permissions yet.

## Module Inventory

| Module | Routes | File | Primary purpose |
|---|---:|---|---|
| Preview API | 3 | `platform/themes/echo/functions/grimba-post-preview.php` | JSON source/cluster preview and cluster suggestion for post editor. |
| Cockpit | 3 | `platform/themes/echo/functions/grimba-admin-cockpit.php` | Operational dashboard, NobuAI summary trigger, bounded runbook command trigger. |
| Cookies | 2 | `platform/themes/echo/functions/grimba-admin-cookies.php` | Cookie banner settings. |
| Coverage map | 1 | `platform/themes/echo/functions/grimba-admin-clusters.php` | Cluster coverage gap map. |
| News sources | 8 | `platform/themes/echo/functions/grimba-admin-sources.php` | Source list, create, edit, delete, triage, quick classify. |
| NewsAPI | 5 | `platform/themes/echo/functions/grimba-admin-newsapi.php` | Provider settings, test, manual fetch, publish NewsAPI drafts. |
| RSS drafts | 4 | `platform/themes/echo/functions/grimba-admin-rss-drafts.php` | RSS draft queue, bulk publish, bulk delete, single publish. |
| RSS feeds | 9 | `platform/themes/echo/functions/grimba-admin-rss-feeds.php` | RSS feed CRUD, toggle, poll one, poll all. |
| Story clusters | 9 | `platform/themes/echo/functions/grimba-admin-clusters.php` | Cluster CRUD, attach/detach posts, NobuAI summary generation. |
| Subscribers | 4 | `platform/themes/echo/functions/grimba-admin-subscribers.php` | Newsletter subscriber list, export, delete, unsubscribe toggle. |
| Translation/provider settings | 4 | `platform/themes/echo/functions/grimba-admin-translation.php` | Translation keys/models, ingest auto-publish flag, translation test, NobuAI test. |

## Route Groups

### Preview API

| Method | URI | Route name | Notes |
|---|---|---|---|
| GET | `admin/grimba/api/preview/source/{id}` | `grimba.api.preview.source` | Returns source metadata for the post editor. |
| GET | `admin/grimba/api/preview/cluster/{id}` | `grimba.api.preview.cluster` | Returns cluster metadata and bias counts. |
| GET | `admin/grimba/api/preview/cluster-suggest` | `grimba.api.preview.cluster-suggest` | Suggests likely cluster from title input. |

### Cockpit

| Method | URI | Route name | Notes |
|---|---|---|---|
| GET | `admin/grimba/cockpit` | `grimba.cockpit` | Aggregates publication, ingest, translation, NobuAI, source, newsletter, and automation status. |
| POST | `admin/grimba/cockpit/nobuai-summaries` | `grimba.cockpit.nobuai-summaries` | Calls `grimba:nobuai-summaries` with a max limit of 5. |
| POST | `admin/grimba/cockpit/runbook` | `grimba.cockpit.runbook` | Allows bounded commands: health, one RSS poll, NobuAI health, FR/EN translation, NewsAPI fetch. |

### Cookies

| Method | URI | Route name | Notes |
|---|---|---|---|
| GET | `admin/grimba/cookies` | `grimba.cookies.index` | Displays cookie-banner settings. |
| POST | `admin/grimba/cookies` | `grimba.cookies.save` | Writes `grimba_cookie_*` settings. |

### News Sources

| Method | URI | Route name | Notes |
|---|---|---|---|
| GET | `admin/grimba/news-sources` | `grimba.news-sources.index` | Searchable source list. |
| GET | `admin/grimba/news-sources/triage` | `grimba.news-sources.triage` | Unknown-bias triage queue with article counts. |
| POST | `admin/grimba/news-sources/{id}/quick-classify` | `grimba.news-sources.quick-classify` | AJAX classification update. |
| GET | `admin/grimba/news-sources/create` | `grimba.news-sources.create` | Create form. |
| POST | `admin/grimba/news-sources` | `grimba.news-sources.store` | Validated source creation. |
| GET | `admin/grimba/news-sources/{id}/edit` | `grimba.news-sources.edit` | Edit form. |
| PUT | `admin/grimba/news-sources/{id}` | `grimba.news-sources.update` | Validated source update. |
| DELETE | `admin/grimba/news-sources/{id}` | `grimba.news-sources.destroy` | Deletes a source row. |

### NewsAPI

| Method | URI | Route name | Notes |
|---|---|---|---|
| GET | `admin/grimba/newsapi` | `grimba.newsapi.index` | Displays NewsAPI settings, budgets, recent runs, and draft guardrails. |
| POST | `admin/grimba/newsapi` | `grimba.newsapi.save` | Writes key, query, country, category, budget, and active settings. |
| POST | `admin/grimba/newsapi/test` | `grimba.newsapi.test` | Performs a low-cost top-headlines probe. |
| POST | `admin/grimba/newsapi/run` | `grimba.newsapi.run` | Calls `grimba:fetch-newsapi`. |
| POST | `admin/grimba/newsapi/publish-drafts` | `grimba.newsapi.publish-drafts` | Publishes selected NewsAPI drafts through guardrails. |

### RSS Feeds And Drafts

| Method | URI | Route name | Notes |
|---|---|---|---|
| GET | `admin/grimba/rss-feeds` | `grimba.rss-feeds.index` | Feed health dashboard. |
| GET | `admin/grimba/rss-feeds/create` | `grimba.rss-feeds.create` | Create form. |
| POST | `admin/grimba/rss-feeds` | `grimba.rss-feeds.store` | Validated feed creation. |
| GET | `admin/grimba/rss-feeds/{id}/edit` | `grimba.rss-feeds.edit` | Edit form. |
| PUT | `admin/grimba/rss-feeds/{id}` | `grimba.rss-feeds.update` | Validated feed update. |
| DELETE | `admin/grimba/rss-feeds/{id}` | `grimba.rss-feeds.destroy` | Deletes feed and related feed items. |
| POST | `admin/grimba/rss-feeds/{id}/toggle` | `grimba.rss-feeds.toggle` | Toggles feed active flag. |
| POST | `admin/grimba/rss-feeds/{id}/poll-now` | `grimba.rss-feeds.poll-now` | Polls one feed synchronously. |
| POST | `admin/grimba/rss-feeds/poll-all` | `grimba.rss-feeds.poll-all` | Polls all feeds synchronously. |
| GET | `admin/grimba/rss-drafts` | `grimba.rss-drafts.index` | Draft review queue with source/bias filters. |
| POST | `admin/grimba/rss-drafts/publish` | `grimba.rss-drafts.publish` | Bulk publish selected RSS drafts through guardrails. |
| POST | `admin/grimba/rss-drafts/delete` | `grimba.rss-drafts.delete` | Bulk delete selected RSS drafts and null feed-item post links. |
| POST | `admin/grimba/rss-drafts/{id}/publish` | `grimba.rss-drafts.publish-one` | Single draft publish through guardrails. |

### Story Clusters And Coverage

| Method | URI | Route name | Notes |
|---|---|---|---|
| GET | `admin/grimba/story-clusters` | `grimba.story-clusters.index` | Cluster list with bias spread. |
| GET | `admin/grimba/coverage-map` | `grimba.coverage-map.index` | Coverage gap/one-sided cluster map. |
| GET | `admin/grimba/story-clusters/create` | `grimba.story-clusters.create` | Create form. |
| POST | `admin/grimba/story-clusters` | `grimba.story-clusters.store` | Validated cluster creation. |
| GET | `admin/grimba/story-clusters/{id}/edit` | `grimba.story-clusters.edit` | Edit form and attached article list. |
| PUT | `admin/grimba/story-clusters/{id}` | `grimba.story-clusters.update` | Validated cluster update. |
| DELETE | `admin/grimba/story-clusters/{id}` | `grimba.story-clusters.destroy` | Detaches posts, then deletes cluster. |
| POST | `admin/grimba/story-clusters/{id}/attach` | `grimba.story-clusters.attach` | Attaches post to cluster. |
| POST | `admin/grimba/story-clusters/{id}/detach` | `grimba.story-clusters.detach` | Detaches post from cluster. |
| POST | `admin/grimba/story-clusters/{id}/nobuai-summary` | `grimba.story-clusters.nobuai-summary` | Generates NobuAI insight for clusters with at least two published posts. |

### Subscribers

| Method | URI | Route name | Notes |
|---|---|---|---|
| GET | `admin/grimba/subscribers` | `grimba.subscribers.index` | Search/filter subscriber list. |
| GET | `admin/grimba/subscribers/export.csv` | `grimba.subscribers.export` | CSV export including email, bias counts, created date, unsubscribe date, and IP address. |
| POST | `admin/grimba/subscribers/{id}/toggle` | `grimba.subscribers.toggle` | Toggle unsubscribe timestamp. |
| DELETE | `admin/grimba/subscribers/{id}` | `grimba.subscribers.destroy` | Delete subscriber row. |

### Translation And NobuAI Provider Settings

| Method | URI | Route name | Notes |
|---|---|---|---|
| GET | `admin/grimba/translation` | `grimba.translation.index` | Displays translation/NobuAI provider settings, model overrides, failures, and auto-publish flag. |
| POST | `admin/grimba/translation` | `grimba.translation.save` | Writes provider keys, pinned driver, model overrides, and `grimba_ingest_auto_publish`. |
| POST | `admin/grimba/translation/test` | `grimba.translation.test` | Translation provider test. |
| POST | `admin/grimba/translation/nobuai-test` | `grimba.translation.nobuai-test` | NobuAI completion test. |

## Security And QA Findings

- Authentication is present on every custom Grimba admin route through `auth`.
- CSRF middleware is present through the `web` middleware group for POST, PUT, and DELETE routes.
- Granular permissions are not declared in the Grimba route groups. Any authenticated admin who can reach the dashboard may be able to perform high-impact Grimba actions unless Botble menu visibility or upstream policies restrict access elsewhere.
- Most destructive routes use POST or DELETE and redirect back, but the inventory did not verify confirmation UI, audit logging, or authorization by role.
- Provider-key routes write settings from admin forms. The next security pass must verify values are masked in views, redacted from logs, and protected from accidental public exposure.
- `subscribers/export.csv` includes `ip_address`. This needs privacy/legal signoff and possibly a reduced export mode before launch.
- `newsapi/test`, `newsapi/run`, RSS poll actions, NobuAI summary generation, translation tests, and cockpit runbook actions can trigger network calls or expensive provider work. They need browser QA, timeout expectations, and rate/cost guard checks.
- All custom admin routes are closure-backed, so production route caching remains blocked unless these routes are moved into controllers.

## Handoff

Sprint: S002  
Outcome: admin route inventory complete  
Files: `docs/GRIMBANEWS_S002_ADMIN_ROUTE_INVENTORY.md`, `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md`  
Verification: `php artisan route:list --path=admin/grimba --json`; source reads for cockpit, RSS, NewsAPI, sources, clusters, translation, cookies, subscribers, preview APIs  
Risks: no granular Grimba admin permissions found, route cache blocked by closures, subscriber export includes IPs, provider-triggering actions need timeout/cost QA  
Next: S003 command inventory  
Commit: recorded in sprint handoff after push
