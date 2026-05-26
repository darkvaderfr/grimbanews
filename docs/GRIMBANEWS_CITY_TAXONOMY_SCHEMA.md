# GrimbaNews — City Taxonomy Schema

**Status:** schema v0 (no local_cities table; news_sources.city slot is per-source)
**Owner:** Larry Ellison (VP DBA) on schema + Lucy Leai (Strategy) on city pool curation + Liam Smith (PM) on rollout
**Walks:** Mythos S1611 (city taxonomy schema) deferred → partial
**Gating dependency:** Per-city editorial briefs (operator-side) + per-city seed list. Schema design itself is operator-side.

## Why this exists

S1611 was honest-deferred: "no local_cities table." That's true. Today `/local` resolves cities via keyword scan against `posts.name + posts.description` filtered by `news_sources.country` (per S1603 partial). This works for ad-hoc city matches but doesn't support per-city dedicated landing pages, per-city editorial briefs, per-city sponsorship targeting (S1609 deferred), or per-city analytics. This document defines the schema so the moment Lucy + Larry agree, migration is straight forward.

## Today's surrogate

- `news_sources.city` slot exists at the **source level** via `App\Services\GrimbaSourceClassifier` (some sources tagged with a city, most NULL).
- `/local` route at `platform/themes/echo/routes/web.php:1538-1594` filters posts by `news_sources.country = cc` + city-keyword LIKE against name/description (max 36 results).
- Geolocation via `App\Services\GrimbaGeoLocator::locate()` cascades ip-api.com → ipapi.co.
- Per-city cookie persistence per S1605 complete.

**No `local_cities` table.**

## Proposed schema

```sql
CREATE TABLE local_cities (
  id BIGINT PRIMARY KEY AUTOINCREMENT,
  slug VARCHAR(64) NOT NULL,                  -- 'paris', 'fort-de-france', 'abidjan'
  display_name VARCHAR(128) NOT NULL,         -- 'Paris', 'Fort-de-France', 'Abidjan'
  alt_names JSON DEFAULT '[]',                -- ['fdf'] for city-shorthand matching
  country_code CHAR(2) NOT NULL,              -- ISO 3166-1 alpha-2
  region_code VARCHAR(8) NULL,                -- ISO 3166-2 (state / department / region)
  latitude DECIMAL(10, 7) NULL,
  longitude DECIMAL(10, 7) NULL,
  timezone VARCHAR(64) NULL,                  -- IANA timezone, e.g., 'Europe/Paris'
  population BIGINT NULL,                     -- for ranking / surface decisions
  editorial_priority TINYINT DEFAULT 5,       -- 1 (highest) to 9, default 5
  is_active BOOLEAN DEFAULT TRUE,
  language_primary CHAR(2) NULL,              -- 'fr' / 'en' / etc.
  language_secondary CHAR(2) NULL,            -- for multilingual cities
  brief_path VARCHAR(255) NULL,               -- docs/editorial-briefs/cities/{slug}.md
  source_pool_query JSON DEFAULT '{}',        -- {city_keywords: ['Paris','Île-de-France'], source_ids: [...]}
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  UNIQUE (slug, country_code),
  INDEX (country_code, editorial_priority),
  INDEX (is_active, country_code)
);

CREATE TABLE local_city_source_pin (
  id BIGINT PRIMARY KEY AUTOINCREMENT,
  local_city_id BIGINT NOT NULL,
  news_source_id BIGINT NOT NULL,
  priority TINYINT DEFAULT 5,
  notes VARCHAR(255) NULL,
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  FOREIGN KEY (local_city_id) REFERENCES local_cities(id) ON DELETE CASCADE,
  FOREIGN KEY (news_source_id) REFERENCES news_sources(id) ON DELETE CASCADE,
  UNIQUE (local_city_id, news_source_id)
);
```

## Seed pool (Phase 1 — operator-curated)

Lucy + Steve pick **~50 launch cities** across the regions GrimbaNews actively covers. Initial bucket:

| Region | Cities |
|---|---|
| France métropolitaine | Paris, Lyon, Marseille, Bordeaux, Toulouse, Lille, Nantes, Strasbourg, Rennes, Nice |
| France DOM-TOM (per `docs/GRIMBANEWS_DOM_TOM_SOURCE_ROSTER.md`) | Fort-de-France, Pointe-à-Pitre, Saint-Denis (974), Cayenne, Mamoudzou |
| Africa francophone | Abidjan, Dakar, Yaoundé, Kinshasa, Casablanca, Tunis, Alger, Bamako, Ouagadougou, Libreville |
| Africa anglophone | Lagos, Johannesburg, Nairobi, Cape Town, Accra, Cairo, Addis Ababa |
| Europe FR/EN | Brussels, Geneva, London, Dublin, Amsterdam |
| North America FR | Montréal, Québec, Ottawa |
| North America EN | New York, Washington, Los Angeles, San Francisco, Chicago, Boston, Toronto, Vancouver |
| Caribbean | Port-au-Prince, Kingston, Bridgetown, Georgetown |

Each row needs: slug, display_name, country_code, region_code, lat/lon, timezone, primary language. Population pulled from public dataset (geonames.org).

## Per-city editorial brief

Each high-priority city (`editorial_priority` ≤ 3) gets a brief at `docs/editorial-briefs/cities/{slug}.md`:

```markdown
# {City} Editorial Brief

**Purpose:** Why we cover this city. Reader value proposition.
**Source pool:** Pinned sources (cross-ref local_city_source_pin).
**City-keyword set:** Names variants, neighborhoods, landmarks.
**Cadence:** Daily / weekly / on-event.
**Per-event playbooks:** e.g., "When a typhoon hits Réunion, here's our coverage."
**Quality bar:** Minimum sources before publishing a city-specific cluster.
**Sponsorship eligibility:** Per S1609 deferred — flag if city is open for sponsor.
```

## Migration plan

1. **Schema migration** — `database/migrations/{date}_create_local_cities_table.php`.
2. **Seeder** — `database/seeders/LocalCitiesSeeder.php` populates Phase 1 ~50 cities. Idempotent on `slug+country_code`.
3. **Backfill command** — `php artisan grimba:backfill-city-pins --apply` walks existing `news_sources.city` slot + auto-creates `local_city_source_pin` rows.
4. **Existing `/local` route refactor** — replace ad-hoc keyword scan with `local_cities.source_pool_query` resolution; falls back to ad-hoc when no city row exists.
5. **Per-city landing page** — gates on S1608 (deferred); `/local/{country-code}/{city-slug}` once admin UI ships.

## Admin UI (S1612 dependency)

- `/admin/grimba/local-cities` — paginated list, filters by country / active / priority.
- Per-city editor: all schema fields + pin-source picker (`local_city_source_pin` rows).
- Bulk-import CSV (per Phase 2 cities).
- Per-city activity peek: 7-day post count + per-source breakdown.

## Privacy posture

- City taxonomy contains **no reader PII** — just geographic metadata.
- Geolocation cookie (`grimba_local_city`, `grimba_local_country`, `grimba_local_cc`) per S1605 is the only reader-side data; not joined to city taxonomy on the server.
- IP-based geolocation per S1602 fires only when cookies empty; raw IP never lands on disk.

## Engineering effort estimate

- Schema + migration: 0.5 sprint.
- Seeder (Phase 1 ~50 cities): 1 sprint.
- Backfill command: 1 sprint.
- `/local` route refactor + admin UI (S1612 ship): 3 sprints.
- Per-city landing page (S1608 ship): 2 sprints.
- Tests + per-city verification: 2 sprints.
- **Full ship: ~8-9 sprints.**

## Cross-references

- Master plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1611; sister S1612-S1620, related S1601-S1610)
- Sister docs: `docs/GRIMBANEWS_DOM_TOM_SOURCE_ROSTER.md`, `docs/GRIMBANEWS_GDPR_ROPA.md`
- Existing geolocator: `app/Services/GrimbaGeoLocator.php`
- Existing source classifier: `app/Services/GrimbaSourceClassifier.php`
- Existing routes: `platform/themes/echo/routes/web.php:1538-1610`
- Region helper: `app/Ground/Regions.php`
- Iboga roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
