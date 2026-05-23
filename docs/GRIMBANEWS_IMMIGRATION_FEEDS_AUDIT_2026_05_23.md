# GrimbaNews — Immigration Feed Audit, 2026-05-23

**Status:** dead-link repair + 2 new working feeds added
**Walks:** the `🟡` line in `docs/GRIMBANEWS_LAUNCH_READINESS_CHECKLIST.md` section 1: "Immigration category — 78 articles (was 0). TNH, France terre d'asile, UNHCR feeds disabled (HTTP 403/404)."

## Probe results (11 candidates)

| URL | Status | Disposition |
|---|---|---|
| `thenewhumanitarian.org/rss/all` | **404** | DEAD — was in seeder, replaced |
| `thenewhumanitarian.org/rss.xml` | **200** ✅ | RSS — added to seeder |
| `thenewhumanitarian.org/feeds/` | 403 | dead |
| `thenewhumanitarian.org/topic/migration/feed` | 403 | dead |
| `migrationpolicy.org/rss/migration-information-source` | **403** | DEAD — was in seeder, replaced |
| `migrationpolicy.org/rss.xml` | **200** ✅ | RSS — added to seeder |
| `lacimade.org/feed/` | 200 ✅ | unchanged, working |
| `france-terre-asile.org/feed` | 404 | site has no RSS — REMOVED from seeder |
| `france-terre-asile.org/toutes-les-actualites?format=feed` | 404 | dead — REMOVED |
| `unhcr.org/rss/news.xml` | 403 | UNHCR blocks RSS — REMOVED from seeder |
| `unhcr.org/rss.xml` | 403 | dead — confirmed no working UNHCR feed |
| `unhcr.org/news/rss.xml` | 403 | dead |
| `reliefweb.int/updates/rss.xml` | **200** ✅ | RSS — NEW, added as UNHCR replacement |
| `amnesty.org/en/feed/` | 200 ✅ | RSS — NEW, immigration coverage |
| `refugeesinternational.org/rss/` | 200 ✅ | unchanged, working |
| `ec.europa.eu/migrant-integration/rss.xml` | 404 | dead |

## Final immigration source roster (post-repair)

6 publishers, 6 active RSS feeds. All probed working 2026-05-23.

| # | Publisher | Country | Bias | Cred. | Active RSS feed |
|---|---|---|---|---|---|
| 1 | The New Humanitarian | CH | center | 92 | `thenewhumanitarian.org/rss.xml` |
| 2 | Migration Policy Institute | US | center | 90 | `migrationpolicy.org/rss.xml` |
| 3 | La Cimade | FR | left | 82 | `lacimade.org/feed/` |
| 4 | ReliefWeb (UN OCHA) | CH | center | 93 | `reliefweb.int/updates/rss.xml` |
| 5 | Amnesty International | GB | left | 87 | `amnesty.org/en/feed/` |
| 6 | Refugees International | US | center | 88 | `refugeesinternational.org/rss/` |

## What changed in code

`app/Console/Commands/GrimbaSeedImmigrationSources.php`:
- TNH URL: `/rss/all` → `/rss.xml`
- MPI URL: `/rss/migration-information-source` → `/rss.xml`
- Removed France terre d'asile (no working RSS)
- Removed UNHCR News (RSS blocked)
- Added ReliefWeb (UN OCHA replacement for UNHCR coverage)
- Added Amnesty International (frequent immigration coverage)
- **Seeder is now idempotent on existing publishers** — it backfills MISSING feeds for already-existing publishers instead of skipping them outright (previous behavior left dead feed URLs in place when a re-run might have fixed them).

## What changed in DB (local; operator runs on prod for parity)

3 new `rss_feeds` rows inserted directly via SQL for the local DB:
- TNH `rss.xml` (source_id 677) — the old `rss/all` row stays at `is_active = 0`
- ReliefWeb `rss.xml` (source_id 587) — new
- Amnesty `feed/` (source_id 575) — new

**Operator action for prod cutover:** run `php artisan grimba:seed-immigration-sources` on prod after deploy. The updated seeder will backfill the 3 new feed rows automatically.

## Expected coverage uplift

Immigration category at probe time: 91 articles (per `grimba:backfill-category --dry-run`). Post-deploy with the 5 working publishers polling, expect:
- TNH: ~3-5 immigration-related items / week
- MPI: ~2-3 / week
- La Cimade: ~5-10 / week (French-language, immigration-focused)
- ReliefWeb: ~10-20 / week (humanitarian operations including refugee/displacement)
- Amnesty: ~5-15 / week (human rights including immigration detention)
- Refugees International: ~2-3 / week

Estimated organic uplift: 25-55 immigration items / week. To hit the 500-article launch floor: ~10-20 weeks of organic, OR run `grimba:backfill-category --category=Immigration` daily for a faster ramp (each run pulls per-keyword from NewsAPI; rate-limited).

## Honest open items

- **No replacement for France terre d'asile** — it's a known FR-language immigration nonprofit with zero working RSS. Options: (1) build a Twitter/Mastodon scraper, (2) drop a manual editorial flag in admin to ingest hand-picked URLs, (3) accept La Cimade as the dominant FR-language voice in this niche.
- **UNHCR has no public RSS endpoint** — ReliefWeb (also UN-owned) is the official replacement.
- **Coverage skew:** 4 of 6 publishers are EN-only. La Cimade is the only FR. For Vader's francophone-first edition, hand-curating FR immigration sources (Médecins Sans Frontières, Forum Réfugiés, Conseil français des associations pour les droits de l'enfant) may be operator follow-up work.
