# GrimbaNews — Middle Ground Signal API Reference

**Status:** v1 shipped 2026-05-26 (Wave NNNN, `560249ed`)
**Owner:** Lisa Nguyen (backend) on contract + Liam Smith (PM) on schema versioning + Lucy Leai (Strategy) on attribution license
**Endpoint:** `GET /api/middle-ground.json`
**Methodology:** `/methodologie#juste-milieu`

## Why this exists

The Middle Ground signal — clusters where the left and right camp cover a story in equal proportions, distinct from blindspots (one-sided) — is a unique editorial signal GrimbaNews surfaces nightly. This API turns the signal into an open dataset for researchers, journalists, and downstream applications.

## Request

```
GET /api/middle-ground.json[?limit=N]
```

Query parameters:

| Param | Type | Default | Notes |
|---|---|---|---|
| `limit` | int (1-200) | 50 | Number of clusters to return. Clamped to [1, 200]. |

## Response

```json
{
  "generated_at": "2026-05-26T19:13:36+00:00",
  "count": 3,
  "limit": 3,
  "classifier_cadence": "daily-0335-utc",
  "classifier_command": "grimba:reclassify-clusters",
  "methodology_url": "https://grimbanews.com/methodologie#juste-milieu",
  "rows": [
    {
      "cluster_id": 1040,
      "topic": "Story headline as classified by the cluster",
      "left_count": 1,
      "center_count": 0,
      "right_count": 1,
      "tagged_at": "2026-05-26 14:19:30",
      "days_since_tagged": 0,
      "dossier_url": "https://grimbanews.com/comparatif/1040"
    }
  ]
}
```

### Top-level fields

- `generated_at` (string, ISO-8601 UTC) — when the response was rendered. Cached for 15 minutes browser / 30 minutes CDN, so this can lag the wall clock by up to 30 min on hot endpoints.
- `count` (int) — number of rows in this response.
- `limit` (int) — the limit honored (after clamp).
- `classifier_cadence` (string) — `"daily-0335-utc"` for v1. Future enhancements may expose `"hourly"` or `"on-event"`.
- `classifier_command` (string) — Artisan command that produced the rows. Operator-side debugging aid.
- `methodology_url` (string) — deep-link to the methodology §3 bis explainer (Wave OOO).
- `rows` (array) — see below.

### Row fields

- `cluster_id` (int) — `story_clusters.id`. Stable identifier across runs.
- `topic` (string) — cluster topic as stored. Human-readable; FR by default unless the cluster is locale-flagged.
- `left_count` (int) — number of left-classified articles in the cluster.
- `center_count` (int) — center.
- `right_count` (int) — right.
- `tagged_at` (string, MySQL datetime) — when the reclassifier persisted the `mg_` tag.
- `days_since_tagged` (int, nullable) — convenience: days between `tagged_at` and `now()`. Null if `tagged_at` is missing.
- `dossier_url` (string) — absolute URL to the cluster's `/comparatif/{id}` page where the LCR articles can be compared side-by-side.

## Headers

- `Content-Type: application/json`
- `Cache-Control: public, max-age=900, s-maxage=1800` — 15 min browser cache, 30 min CDN cache.
- `Access-Control-Allow-Origin: *` — open CORS so any frontend can pull.
- `Access-Control-Allow-Methods: GET` — read-only.

## Rate limits + fair use

V1 has no rate limit beyond standard Cloudflare / nginx defaults. If you anticipate sustained > 60 req/min, contact `data@grimbanews.com` (placeholder; production email subject to operator onboarding) for a coordinated pull schedule.

## Attribution

The Middle Ground signal is freely reusable under attribution. We ask for a link back to `grimbanews.com` when republishing the dataset or derivative analyses. We do not require commercial-use approval; we do request a courtesy notice for large-scale (>10k cluster) downstream uses.

## Versioning

V1 ships unversioned. A future v2 (when added) will live at `/api/v2/middle-ground.json`. V1 stays available for at least 12 months past any v2 introduction.

## Future enhancements (not v1)

- `?since=<iso>` query: only clusters tagged after a date.
- `?country=<iso>` query: filter by per-cluster majority country.
- `?topic_contains=<keyword>` query: search by topic.
- Per-row `articles[]` expansion: include the actual L/C/R article headlines.
- Atom feed parity (`/api/middle-ground.atom`).
- Schema.org `Dataset` self-description endpoint.

## Cross-references

- Endpoint route: `platform/themes/echo/routes/web.php` (Wave NNNN block)
- Methodology: `platform/themes/echo/views/methodology.blade.php` §6 bis (Wave OOOO)
- Classifier: `app/Console/Commands/GrimbaReclassifyClusters.php`
- Resolver: `app/Support/GrimbaClusterBias.php`
- Tag schema: `story_clusters.review_action` with `mg_<L>_<C>_<R>` prefix
- Lock test: `tests/Feature/GrimbaLaunchReadinessTest.php::test_api_middle_ground_json_returns_valid_data_product`
- Iboga roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`

## Mythos enrichment

This doc + the Wave NNNN code drop together enhance the **S1181 Public API v2 design** row (already partial as of Wave DDDD). With this v1 endpoint live + this docs surface, S1181 is now closer to evidenced — a public-readable signal endpoint exists, is documented, and is locked behind 29 assertions. The remaining v2 work (OAuth keys, rate-limiter, per-key analytics) stays partial pending the surrogate-doc work in Wave DDDD.
