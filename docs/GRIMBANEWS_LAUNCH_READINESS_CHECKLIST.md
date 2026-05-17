# GrimbaNews — Launch Readiness Checklist

**Author:** Mythos / Claude · **Date:** 2026-05-17 · **Status:** pre-launch (T-7 days target)

This is the durable launch-go/no-go checklist for GrimbaNews. Every line is either ✅ shipped, 🟡 partial (operator action queued), or ⬜ blocker. Re-run at each session opener.

---

## 1. Editorial substrate

| Item | Status | Notes |
|---|---|---|
| 3,461 posts ingested across 643 sources | ✅ | Live count via `select count(*) from posts where status='published'` |
| Origin-language tagged on 99% of posts | ✅ | After Wave J: 36 NULL out of 3,461 = 1.04% — within Vader's tolerance |
| 649 dossiers with primary_language denorm | ✅ | Wave AA: 340 FR · 300 EN · 9 unknown |
| Editorial categories ≥500 articles each | 🟡 | 2 of 14 OK (À la une 748, Géopolitique 538). 12 categories need backfill via `php artisan grimba:backfill-category`. Run when launch-ready. |
| Immigration category — 0 articles | 🟡 | Specifically blocking the Immigration nav surface. Same backfill command. |
| Bias / factuality / ownership classified on sources | ✅ | Coverage by `news_sources.bias_rating` / `factuality_score` / `ownership_type` |
| Story clusters formed via `GrimbaRssPoller::findOrFormCluster()` | ✅ | 649 dossiers active |
| NobuAI summaries (145 posts, all FR) | 🟡 | Coverage is thin (~4% of posts). Run `php artisan grimba:generate-nobuai-summaries` to widen before launch. |

## 2. Reader UX

| Item | Status | Notes |
|---|---|---|
| Cinematic glass design language | ✅ | Steve-approved across all surfaces |
| Sticky responsive header (with Dossiers + active state) | ✅ | Waves L + M |
| 3-column responsive grid on all listing pages | ✅ | Waves M + V — verified live across /blog, /tag, /sources, /angles-morts, /dossiers, /search |
| Mobile bottom nav | ✅ | Coffre + Dossiers slots active |
| Info-pills on home (8 instances) | ✅ | Phase 1 P0s closed |
| Info-pills on dossier page (9 instances) | ✅ | Phase 2 P0s closed |
| Info-pills on listing pages (5 Phase 4 surfaces) | ✅ | Wave BB: breaking, latest, category top-sources, for-you, comparison-index |
| Glass-pill buttons site-wide | ✅ | Wave F |
| Light-mode contrast audit | ✅ | Wave G — kicker bumped, hero fallback fixed, coverage-legend scoped |
| Iframe / double-doc fix on breaking / latest / advertise | ✅ | Wave I |
| Editorial ribbon signature on every reader card | ✅ | Sprint 9 utility + wave M extensions |

## 3. Language tagging system

| Item | Status | Notes |
|---|---|---|
| `GrimbaLanguageDetector` (TLD + n-gram, 26 unit tests) | ✅ | S-LANG-02 |
| Universal `Post::saving` hook | ✅ | S-LANG-03 — covers all 5 ingest writers |
| `grimba:backfill-language` daily cron at 03:15 UTC | ✅ | S-LANG-04 |
| Reader-side NULL-posts rank-3 rule | ✅ | S-LANG-05 |
| `?lang=fr` / `?lang=en` + hreflang alternates | ✅ | S-LANG-06 |
| `<html lang>` + `lang=""` audit | ✅ | S-LANG-07 |
| `posts.summary_nobuai_locale` writer | ✅ | S-LANG-08 — migrated + writer guarded |
| `translated_summary` on join table | ✅ | S-LANG-09 — `GrimbaTranslatePending` writes it |
| Translation work-map admin UI | ✅ | S-LANG-10 + S-LANG-13 |
| Dossier `primary_language` denorm + daily recompute | ✅ | S-LANG-11 + S-LANG-12 |
| Unclassified-language reader badge | ✅ | S-LANG-14 (amber pill, deep-link to methodology) |
| Atomicity test (4 invariants) | ✅ | S-LANG-15 |
| Operator handoff doc | ✅ | S-LANG-16 |
| **All 16/16 S-LANG sprints closed** | ✅ |  |

## 4. Provider integration

| Item | Status | Notes |
|---|---|---|
| RSS poller (`GrimbaRssPoller`) | ✅ | Live |
| NewsAPI fetcher | ✅ | Live |
| Webz.io fetcher | ✅ | Live |
| Mediastack fetcher | ✅ | Live |
| GDELT + Google News live lane | ✅ | Live |
| newsdata.io provider (S-NDI-01..14) | ✅ | Foundation + admin UI shipped Sprint 30. **Waiting on operator-provided API key** before `--provider=newsdata-io` produces real fetches. |
| Cron schedule registered | ✅ | `routes/console.php` |
| Automation monitor cockpit | ✅ | `/admin/grimba/cockpit` |

## 5. NobuAI brand purity

| Item | Status | Notes |
|---|---|---|
| Zero provider names on reader-facing surfaces | ✅ | Wave CC sweep — clean |
| Lang JSON files clean | ✅ | Wave CC |
| Admin pages may name providers (per CLAUDE.md exception) | ✅ | `/admin/grimba/newsapi`, `/admin/grimba/newsdataio` correctly name upstream |
| Defensive regex strips provider names from rendered content | ✅ | `post.blade.php:426` |

## 6. SEO

| Item | Status | Notes |
|---|---|---|
| `<link rel="alternate" hreflang>` on every reader page | ✅ | S-LANG-06 |
| Canonical via Botble SeoHelper | ✅ | Single canonical per page; no duplicates |
| JSON-LD `inLanguage` correct (omits when NULL) | ✅ | S-LANG-06 |
| OG tags + Twitter cards | ✅ | Existing |
| RSS feeds: main, breaking, latest, per-source | ✅ | Existing + Phase D-09 |
| Sitemap | ✅ | Theme::partials.sitemap + dynamic |
| `<html lang>` consistent across 3 layouts | ✅ | S-LANG-07 |

## 7. Ops + infra

| Item | Status | Notes |
|---|---|---|
| Daily cron jobs registered | ✅ | `routes/console.php` includes 14 jobs |
| Automation monitor table | ✅ | `grimba_automation_monitor_entries` |
| Cockpit page (`/admin/grimba/cockpit`) | ✅ | Live |
| Backup verify daily | ✅ | `grimba:verify-backups --min=1` at 03:05 UTC |
| Image proxy GC | ✅ | `grimba:prune-img-proxy-cache` |
| Release evidence retention | ✅ | `grimba:prune-release-evidence` |
| Rate-limit middleware | ✅ | `GrimbaRateLimiter` |
| CSP headers | ✅ | `GrimbaSecurityHeaders` middleware |
| HTTP→HTTPS redirect | ⬜ | **Operator: ensure VPS Nginx redirects 80 → 443 with HSTS.** |
| DNS + TLS for grimbanews.com → 209.74.88.135 | ⬜ | **Operator: confirm certs valid + A record live before launch.** |

## 8. Testing

| Item | Status | Notes |
|---|---|---|
| Feature + Unit suite | ✅ | 270 OK · 22 incomplete (documented legacy debt) |
| Atomicity invariants | ✅ | S-LANG-15 — 4 assertions |
| Language detector | ✅ | 26 unit fixtures |
| Schedule cron registry | ✅ | `AutomationScheduleTest` covers all 14 jobs |

## 9. Pre-launch operator actions (final)

```bash
# A. Fill the 12 thin editorial categories (will use NewsAPI quota)
php artisan grimba:backfill-category

# B. After (A), gate thin chips
setting('grimba_chip_min_articles', 500)
# or via admin: /admin/settings → grimba_chip_min_articles = 500

# C. Generate NobuAI summaries across remaining 3,316 posts
php artisan grimba:generate-nobuai-summaries

# D. Run translate-pending to fill EN/FR translations across the archive
php artisan grimba:translate-pending

# E. Verify schedule
php artisan schedule:list | grep -E "grimba_|lang_|dossier_"

# F. Operator-only: provision newsdata.io API key, set GRIMBA_NEWSDATAIO_ACTIVE=true, restart workers
```

## 10. Open architect plans (post-launch)

- `docs/GRIMBANEWS_ADVERTISER_CULTURE_FRESHNESS_PLAN.md` — 135 sprints, advertiser dashboard + reader culture + freshness pipeline
- `docs/GRIMBANEWS_LANGUAGE_SPRINT_PLAN.md` — 245-sprint translation overhaul (Phases A–H), broader than the 16-sprint tagging foundation
- `docs/GRIMBANEWS_INFO_PILL_ROLLOUT_PLAN.md` — 41 surfaces total; ~22 wired; remainder are Phase 3/4 cleanup
- `docs/GRIMBANEWS_NEWSDATAIO_INTEGRATION_PLAN.md` — Sprints S-NDI-15/16/17/18 (dedupe extras, cross-provider title-similarity guard, integration test, budget E2E)
- `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` — master tracker

## Net status: **launch-ready conditional on Section 9 operator actions A→F**

The codebase, design language, language tagging, ops cockpit, and brand purity are all green. The 4 remaining 🟡 items are content-population and DNS, not code work. Once operator runs the section-9 commands, GrimbaNews is shippable for the soft-launch window.
