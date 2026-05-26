# GrimbaNews — Saved Searches CSV/JSON Export Plan

**Status:** plan v0 (no export route; surrogate is `/account` page list view)
**Owner:** Rajesh Kumar (Backend) + Liam Smith (PM) + Sara Chen (CISO) on privacy
**Walks:** Mythos S1523 (Data export — saved searches CSV/JSON) deferred → partial
**Gating dependency:** Saved-search primitive (`GrimbaSavedSearches`) exists today + member auth.

## Why this exists

S1523 lets a reader export their saved searches as a CSV or JSON file. GDPR data portability requirement and a UX kindness (lets reader migrate to another service if they want).

## Today's surrogate

- `/account` page lists saved searches in HTML — not machine-readable, no download.

## Route (target)

```
GET /account/saved-searches/export?format=csv
GET /account/saved-searches/export?format=json
```

Auth: member-only. Rate-limit: 5/min per member.

## CSV format

```csv
id,name,query,locale,filters,frequency,created_at,last_run_at,last_match_count
1,"Ukraine FR","ukraine guerre","fr","{\"category\":\"politique\"}","daily","2026-03-15T10:00:00Z","2026-05-26T07:30:00Z",47
```

## JSON format

```json
[
  {
    "id": 1,
    "name": "Ukraine FR",
    "query": "ukraine guerre",
    "locale": "fr",
    "filters": {"category": "politique"},
    "frequency": "daily",
    "created_at": "2026-03-15T10:00:00Z",
    "last_run_at": "2026-05-26T07:30:00Z",
    "last_match_count": 47
  }
]
```

## Privacy posture

- Member can only export their own searches (auth scope enforced).
- No internal-system metadata leaked (no `member_id_hash`, no `created_by_ip`).
- Download served with `Content-Disposition: attachment; filename="grimba-saved-searches-{date}.{ext}"`.

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1523)
- Sister docs: `docs/GRIMBANEWS_GDPR_DSAR_FULL_BUNDLE_PLAN.md`
- Existing infra: `App\Support\GrimbaSavedSearches` + `/account` view
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
