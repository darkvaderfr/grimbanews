# GrimbaNews — Mobile App Local Edition Scope

**Status:** plan v0 (web `/local` is the WebView surrogate today)
**Owner:** Liam Smith (PM) scopes the surface + Steve Jobs (CPO) on permission UX + Nina Patel (Lead FE) on geolocation plugin + Sara Chen (CISO) on geolocation posture
**Walks:** Mythos S1167 (App local edition) deferred → partial
**Gating dependency:** Native shell + city taxonomy (per `GRIMBANEWS_CITY_TAXONOMY_SCHEMA.md` — already scoped) + Capacitor `Geolocation` plugin opt-in

## Why this exists

S1167 wraps the web local edition with optional native geolocation. Today readers manually pick country; native can offer "use my location" once to seed the picker.

## Today's surrogate

- **`/local`** — country-picker + per-country rail.
- **DOM-TOM source roster** at `docs/GRIMBANEWS_DOM_TOM_SOURCE_ROSTER.md`.
- **No geolocation** — manual select only.

## Native enhancements

| Enhancement | Implementation |
|---|---|
| "Use my location" one-time prompt | `Geolocation.getCurrentPosition()` once on first `/local` visit |
| Permission denial graceful | Falls back to existing country picker |
| Reverse-geocode | Use existing `GrimbaCityTaxonomy` (per S1611 city taxonomy doc) — no external geocoder vendor needed |
| Persisted preference | `Preferences` API stores `local_country_iso` |
| Permission revocation guard | Recheck on each foreground; refresh banner if revoked |

## Privacy posture (Sara Chen)

- Geolocation requested ONLY on `/local` page (not splash).
- Coarse precision only (country / region level — `enableHighAccuracy: false`).
- Coordinates **never** sent to server — reverse-geocoded client-side.
- Result is country ISO code only; no lat/lng persisted.
- Permission rationale shown BEFORE prompt: "GrimbaNews can show news from your country. Your exact location stays on your phone."

## Disabled-by-default rail

- The "Use my location" CTA is opt-in.
- Default `/local` experience identical to web — country picker.

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1167)
- Sister docs: `docs/GRIMBANEWS_CITY_TAXONOMY_SCHEMA.md`, `docs/GRIMBANEWS_DOM_TOM_SOURCE_ROSTER.md`, `docs/GRIMBANEWS_GDPR_ROPA.md`
- Existing route: `/local`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
