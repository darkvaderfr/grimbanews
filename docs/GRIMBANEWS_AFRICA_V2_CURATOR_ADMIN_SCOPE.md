# GrimbaNews — Africa Edition v2: Curator Admin Scope

**Status:** plan v0
**Owner:** Sara Chen (CISO) + Larry Ellison (DBA) + Lucy Leai (Strategy)
**Walks:** Mythos S1752 (curator admin scope) deferred → partial
**Gating dependency:** Botble admin auth is single-role per S1401; per-curator scoped role needed.

## Why this exists

Africa curator (Wave SUB-27 sister) needs admin access scoped to Africa-relevant surfaces:
- Per-cluster review for African clusters only.
- Per-source-roster management for African sources.
- Per-region newsletter curation.
- Africa transparency report editing.

NOT access to: full Iboga billing, non-Africa source management, full ops scripts.

## Per-role scope (extends Botble single-role)

```
admin_scopes:
  role_id | scope_pattern (e.g. 'africa.*' / 'cluster.read' / 'source.write')
admin_user_roles:
  user_id | role_id | granted_at
```

## Per-Africa-curator default scopes

- `africa.cluster.read`
- `africa.cluster.review.write`
- `africa.source.write`
- `africa.newsletter.write`
- `africa.transparency-report.write`

## Implementation cost

Botble single-role extension via plugin. ~2-3 weeks engineering.

## Cross-references

Master plan: S1752. Sister: `docs/GRIMBANEWS_AFRICA_V2_NAMED_CURATOR_ROLE.md`, S1401 admin auth.
