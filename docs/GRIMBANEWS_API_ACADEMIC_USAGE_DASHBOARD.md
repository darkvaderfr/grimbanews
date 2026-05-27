# GrimbaNews — API Academic-Tier Usage Dashboard

**Status:** plan v0
**Owner:** Lisa Nguyen (data) + Lucy Leai (Strategy)
**Walks:** Mythos S1695 (academic-tier usage dashboard) deferred → partial
**Gating dependency:** S1691 (academic tier launch) + S1692 (academic-tier auth).

## Why this exists

Academic-tier API users (researchers, students, professors) need usage visibility for their own grant reporting + reproducibility documentation. Free-tier no analytics; academic tier deserves them.

## Per-academic-user dashboard

`/api/academic/{user}/usage`:
- Per-month API calls made.
- Per-endpoint breakdown.
- Per-dataset version queried.
- Per-month cluster IDs retrieved.

## Per-grant tracking

Optional `?grant=ANR-XX-XXXX` query param tags calls with grant ID. Per-grant aggregation in dashboard for grant-report exports.

## Per-academic-user export

Monthly CSV export per-academic-user: useful for thesis methodology sections.

## Schema (gates on Vader migration approval)

```
academic_users:
  id | name | email | institution | affiliation | created_at | verified_at
academic_api_calls:
  user_id | endpoint | called_at | status_code | grant_tag (nullable)
```

## Cross-references

Master plan: S1695. Sister: `docs/GRIMBANEWS_B2B_PER_CUSTOMER_DASHBOARD_PLAN.md`, `docs/GRIMBANEWS_API_ACADEMIC_TIER_PLAN.md` (Wave LLL).
