# GrimbaNews — International Edition v2: Curator Admin Scope

**Status:** plan v0
**Owner:** Sara Chen (CISO) + Larry Ellison (DBA) + Lucy Leai (Strategy)
**Walks:** Mythos S1763 (International curator admin scope) deferred → partial
**Gating dependency:** Same shape as S1752 Africa curator scope; admin_scopes table extension.

## Per-International-curator default scopes

- `international.cluster.read`
- `international.cluster.review.write`
- `international.source.write`
- `international.newsletter.write`
- `international.transparency-report.write`

## Cross-region collaboration

International curator + Africa curator + per-region editors form weekly editorial-coordination call:
- Per-cross-region cluster discussion.
- Per-international-impact event coordination.
- Per-major-event role distribution.

## Implementation cost

Same as Wave SUB-27 Africa curator admin scope: ~2-3 weeks engineering. One implementation covers both regions.

## Cross-references

Master plan: S1763. Sister: `docs/GRIMBANEWS_AFRICA_V2_CURATOR_ADMIN_SCOPE.md`, `docs/GRIMBANEWS_INTERNATIONAL_V2_NAMED_CURATOR_ROLE.md`.
