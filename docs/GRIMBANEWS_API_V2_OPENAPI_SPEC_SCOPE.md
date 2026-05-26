# GrimbaNews — API v2 OpenAPI Spec Scope

**Status:** plan v0 (no spec file; gates on routes being defined)
**Owner:** Rajesh Kumar (Backend) on annotations + Michael O'Connor on hand-curated examples + Liam Smith on coverage
**Walks:** Mythos S1250 (API v2 OpenAPI spec) deferred → partial
**Gating dependency:** All endpoint routes defined (S1181) + reusable response components

## Why this exists

S1250 is the machine-readable contract that drives:
1. Auto-generated docs site (per `GRIMBANEWS_PARTNER_DOCS_PLAN.md`).
2. SDK generation (per S1240 deferred).
3. Partner-side codegen (curl → typed clients).
4. Contract testing in CI.

Without it, docs go stale + SDK generation is impossible.

## Today's surrogate

- **No spec file.**
- **RSS feeds** — XML schemas exist externally (RSS 2.0 / Atom 1.0 docs widely available).

## Spec version + format

- OpenAPI 3.1.
- YAML format (smaller, human-readable).
- Stored at `app/Http/Resources/api-v2.openapi.yaml` in repo.
- Generated artifact served at `/api/v2/openapi.yaml` + `/api/v2/openapi.json`.

## Generation approach

| Approach | Pros | Cons |
|---|---|---|
| Hand-written | full control, examples curated | tedious, drift risk |
| Annotation-driven (`darkaonline/l5-swagger`) | tied to code | annotations clutter controllers |
| Code-first (`zircote/swagger-php`) | similar | similar |
| Spec-first | docs lead implementation | implementation drift |

**Recommendation:** Spec-first authored by Michael O'Connor + Rajesh Kumar — single source of truth, controllers validate against spec in CI.

## Coverage requirements

- 100% of `/api/v2/*` routes.
- Every response status documented (200, 400, 401, 403, 404, 429, 500).
- Every field has description + example.
- Per-tier scopes annotated via security schemes.
- Per-endpoint rate-limit annotated via extension `x-ratelimit`.

## Reusable components

```yaml
components:
  schemas:
    Post:
      type: object
      properties:
        id: { type: integer, example: 12345 }
        slug: { type: string, example: "climate-summit-2026" }
        title: { type: string, example: "Climate Summit Opens..." }
        ...
    Cluster: ...
    Source: ...
    Error:
      type: object
      properties:
        error: { type: string }
        message: { type: string, nullable: true }

  responses:
    Unauthorized: ...
    Forbidden: ...
    RateLimited: ...
    NotFound: ...

  parameters:
    PageParam: ...
    PerPageParam: ...
    FieldsParam: ...
    FilterParam: ...
    SortParam: ...

  securitySchemes:
    bearerAuth:
      type: http
      scheme: bearer
```

## CI validation

- Spectral lint runs on `api-v2.openapi.yaml` per PR.
- Per-endpoint integration test asserts route exists and returns documented status codes.

## Versioning

- Spec versioned alongside API: `info.version: "2.0.0"`.
- Bumps with non-breaking changes (2.0.1, 2.1.0).
- Major version bump = `v3` endpoint base.

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1250)
- Sister docs: `docs/GRIMBANEWS_PUBLIC_API_V2_DESIGN.md`, `docs/GRIMBANEWS_PARTNER_DOCS_PLAN.md`, `docs/GRIMBANEWS_API_FIELD_SELECTION_CONTRACT.md`, `docs/GRIMBANEWS_API_FILTER_SORT_CONTRACT.md`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
