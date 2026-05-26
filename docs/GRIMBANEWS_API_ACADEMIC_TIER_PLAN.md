# GrimbaNews — Academic API Tier Plan

**Status:** plan v0 (no /api/v2 base; no API-key model; no academic tier)
**Owner:** Lucy Leai (Strategy) + Jacob Lee (DevOps) on infra + Larry Ellison (VP DBA) on rate limiting + Ray Dalio (CFO) on price
**Walks:** Mythos S1691 (API /api/v2 base) + S1692 (OAuth client / API key) + S1700 (academic-tier launch playbook) deferred → partial
**Gating dependency:** Sanctum / Passport install + dataset license decision (per `docs/GRIMBANEWS_DATASET_CSV_SCHEMA.md` + `docs/GRIMBANEWS_OSS_METHODOLOGY_SCOPE.md`) + counsel review of academic-use license. Plan itself is operator-side.

## Why this exists

S1691, S1692, S1700 all share a root: GrimbaNews has **no public API today**. Per S1181-S1190 (deferred), there's no Sanctum / Passport install. Researcher access via CSV exports (per `docs/GRIMBANEWS_DATASET_CSV_SCHEMA.md`) handles bulk-export use cases but not query-on-demand for academic pipelines. This document defines the academic-tier scope so when the API decision is made, build is straight.

## Today's surrogate

- **CSV exports** at `/datasets/*` (per `docs/GRIMBANEWS_DATASET_CSV_SCHEMA.md`) — bulk download.
- **No query API** for live `posts` / `news_sources` / `story_clusters`.
- **Per-IP rate limit** on advertiser-lead endpoint (per `AdvertiserLeadController` 5/10min) — proves the pattern.
- **No `embed_tokens` table** — same pattern would apply to API keys.

## Proposed `/api/v2` base

Route base: `/api/v2/`.

Versioning: `/v2/` means **stable contract** (deprecate v1 if we ship it; today no v1).

| Endpoint | Auth | Returns | Rate (academic tier) |
|---|---|---|---|
| `GET /api/v2/posts` | key | paginated posts (filterable: region, category, source) | 1000/hour |
| `GET /api/v2/posts/{id}` | key | single post | 5000/hour |
| `GET /api/v2/clusters` | key | paginated clusters | 1000/hour |
| `GET /api/v2/clusters/{id}` | key | single cluster + bias breakdown | 5000/hour |
| `GET /api/v2/sources` | key | source registry (mirror of `/datasets/sources.csv`) | 100/hour (low churn) |
| `GET /api/v2/search` | key | semantic + lexical search (gates on `docs/GRIMBANEWS_SEARCH_V2_LAUNCH_PLAYBOOK.md`) | 500/hour |
| `GET /api/v2/bias-distribution/{cluster-id}` | key | bias breakdown for cluster | 5000/hour |

All responses JSON; pagination via `?page=N&per_page=N` (default 25, max 100).

## Schema (API key model)

```sql
CREATE TABLE api_keys (
  id BIGINT PRIMARY KEY AUTOINCREMENT,
  member_id BIGINT NOT NULL,                -- FK members.id
  key_hash CHAR(64) NOT NULL UNIQUE,        -- sha256 of full key; raw key never stored
  key_prefix VARCHAR(8) NOT NULL,           -- 'gn_acad_xxxxx' for UI display
  tier ENUM('public','academic','commercial') DEFAULT 'public',
  name VARCHAR(128) NULL,                   -- human label "MIT lab project"
  rate_limit_per_hour INT DEFAULT 100,
  scopes JSON DEFAULT '[]',                 -- ['posts:read','clusters:read','search:read']
  is_active BOOLEAN DEFAULT TRUE,
  expires_at TIMESTAMP NULL,
  last_used_at TIMESTAMP NULL,
  use_count BIGINT DEFAULT 0,
  citation_required BOOLEAN DEFAULT TRUE,
  citation_text VARCHAR(255) NULL,          -- "MIT NLP Lab study, project XYZ"
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  INDEX (key_hash),
  INDEX (member_id, is_active),
  INDEX (tier, is_active)
);

CREATE TABLE api_key_use_log (
  id BIGINT PRIMARY KEY AUTOINCREMENT,
  api_key_id BIGINT NOT NULL,
  endpoint VARCHAR(255) NOT NULL,
  status_code SMALLINT NOT NULL,
  duration_ms INT NULL,
  timestamp TIMESTAMP NOT NULL,
  INDEX (api_key_id, timestamp),
  INDEX (timestamp)
);
```

## Academic-tier signup (S1693)

`/api/access/apply` (form):

- Researcher name + affiliation.
- Project description (free-text).
- Expected use volume.
- Citation format proposed.
- Acknowledgement of academic-use license (CC BY-NC 4.0 per `docs/GRIMBANEWS_DATASET_CSV_SCHEMA.md`).

Manual review by Lucy Leai (per dataset-license owner) within 5 business days.

On approval: key generated, emailed to researcher. Key prefix shows tier.

## Academic-tier rate limit (S1694)

Higher than public tier; lower than commercial:

| Tier | Rate (per hour) | Daily cap | Concurrent connections |
|---|---|---|---|
| Public (anonymous) | 60 | 1000 | 2 |
| Academic | 5000 | 50000 | 5 |
| Commercial | per-contract | per-contract | per-contract |

Rate-limit enforced via Laravel `RateLimiter::for('api-v2')` per `api_key_id` (or IP-hash for public tier). Mirror the AdvertiserLeadController pattern.

## Academic-tier usage dashboard (S1695)

`/admin/grimba/api-keys/{id}/usage`:

- Per-day request volume.
- Per-endpoint breakdown.
- Status-code histogram.
- p95 / p99 latency.
- Rate-limit hits.

Self-service for researcher at `/api/access/dashboard` (auth-required).

## Academic-tier API docs (S1696)

Live at `/api/docs` (gates on OpenAPI spec generation).

**Recommended:** generate OpenAPI 3.1 spec from route definitions via `darkaonline/l5-swagger` package. Render with ReDoc or Swagger UI.

Sections:
- Authentication.
- Rate limits.
- Endpoint reference.
- Pagination.
- Errors.
- Citation requirement.
- Code samples (PHP, Python, R — relevant for academic).

## Citation requirement (S1697)

- Citation acknowledged at signup.
- Citation text stored on `api_keys.citation_text`.
- Periodic (annual) email reminder of citation requirement to active researchers.
- Published research using the API may apply for **"verified academic" tier** with higher limits — gates on paper submission to operator review.

## Renewal cadence (S1699)

- **Annual renewal** required.
- Email 60 + 30 + 7 days before expiry.
- Auto-disable on expiry; re-apply same flow.
- Renewal asks for updated project description + recent publications.

## License

Per `docs/GRIMBANEWS_DATASET_CSV_SCHEMA.md` — CC BY-NC 4.0 default for academic; commercial separate.

API responses MUST include:
- License header: `X-GrimbaNews-License: CC-BY-NC-4.0`.
- Citation header: `X-GrimbaNews-Citation: Use of this API requires citation: ...`.
- Data freshness header: `X-GrimbaNews-Data-Freshness: 2026-05-26T08:00:00Z`.

## Privacy posture

- **No reader PII** in API responses (only public post + cluster + source metadata).
- **API key in `Authorization: Bearer ...` header** — never in URL query (avoids leaking via logs).
- **API key hashed at rest** — never stored raw.
- **Per-key usage log retention:** 90 days (post-90 aggregated to per-day counts).
- **GDPR data-export** honors per-key usage on researcher request.

## Engineering effort estimate

- Sanctum install + setup: 1 sprint.
- API-key schema + admin: 2 sprints.
- Each endpoint (per S1691 table = 7 endpoints): ~1 sprint each = 7 sprints.
- Rate-limit middleware: 1 sprint.
- Usage dashboard (admin + self-service): 3 sprints.
- API docs (OpenAPI + ReDoc): 2 sprints.
- Application + review flow: 2 sprints.
- Renewal cadence + email reminders: 1 sprint.
- Tests + sample-fixture verification: 2 sprints.
- **Full ship: ~20 sprints once dataset license decision lands.**

## Launch playbook (S1700)

1. Phase 1: Sanctum + key model + 3 read-only endpoints (`posts`, `clusters`, `sources`). Internal-test only.
2. Phase 2: Application form + Lucy review + first 5 academic keys issued.
3. Phase 3: 4-week observe — rate-limit + error rates.
4. Phase 4: Remaining endpoints + API docs public.
5. Phase 5: Commercial tier opens (separate quote process).

## Cross-references

- Master plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1691-S1700, S1181-S1190 sister)
- Sister docs: `docs/GRIMBANEWS_DATASET_CSV_SCHEMA.md`, `docs/GRIMBANEWS_OSS_METHODOLOGY_SCOPE.md`, `docs/GRIMBANEWS_NEWSLETTER_MONETIZATION_SCOPE.md`, `docs/GRIMBANEWS_VENDOR_REGISTER.md`, `docs/GRIMBANEWS_GDPR_ROPA.md`, `docs/GRIMBANEWS_SEARCH_V2_LAUNCH_PLAYBOOK.md`
- Existing rate-limit pattern: `app/Http/Controllers/AdvertiserLeadController.php`
- Iboga roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
