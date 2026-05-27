# GrimbaNews — B2B API Partner Changelog Plan

**Status:** plan v0
**Owner:** Michael O'Connor (Tech Writer) + Rajesh Kumar (Backend) + Liam Smith (PM)
**Walks:** Mythos S1239 (B2B API v1 changelog) deferred → partial
**Gating dependency:** /api/v2 endpoint set (S1241-S1245 deferred) + public docs surface.

## Why this exists

`docs/CHANGELOG.md` is repo-internal. Partners need a stable, public-facing changelog they can subscribe to via RSS to track API contract drift before it breaks their integrations.

## v1 design

- New public surface: `/dev/changelog` (Blade view + markdown source in repo).
- Each entry: ISO date, semver tag, sections (Added / Changed / Deprecated / Removed / Fixed / Security).
- RSS feed at `/dev/changelog.rss`.
- 30-day deprecation policy minimum before any field removal.
- Breaking changes require X-Grimba-Api-Version header negotiation (not yet shipped).

## Section template

```
## v1.3.0 — 2026-05-27

### Added
- `cluster.bias_mix.confidence` numeric field (range 0..1)

### Deprecated
- `article.lang_code` → use `article.locale` (will remove 2026-08-27)
```

## Workflow

- Engineering PR that touches `routes/api.php` or response shapes requires a changelog entry in same PR.
- CI check: PR labeled `api` must modify `docs/dev/changelog.md`.
- Tech writer review before merge.

## Cross-references

Master plan: S1239. Sister: S1240 (SDK skeleton), S1259 (status comms), S1260 (ops playbook), S1241-S1245 (endpoint set).
