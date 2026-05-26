# GrimbaNews — OSS i18n Contribution Flow Plan

**Status:** plan v0 (no Crowdin / Weblate provisioned; per-locale catalog work done in-tree per S1101+ deferrals)
**Owner:** Nina Patel (Lead Frontend) on tooling + Henry Walker (Content) on translator-pool + Sophia Martinez on community coord
**Walks:** Mythos S2094 (Community — i18n translation contribution flow) deferred → partial
**Gating dependency:** OSS repo provisioned (S2043) + locale-catalog work ongoing.

## Why this exists

S2094 streamlines per-locale catalog contributions from the community. Today every catalog entry is in-tree PHP arrays — high friction for non-developer translators. Industry standard: Crowdin / Weblate / Transifex.

## Today's surrogate

- Per-locale catalog files in repo (per S1101+ partial docs).
- All contributions via PR (developer-only path).

## Tool comparison

| Tool | Pros | Cons | Fit |
|---|---|---|---|
| Crowdin | Mature, free for OSS, github integration | Vendor lock-in risk | Default for community-driven |
| Weblate | Self-hostable, libre | Requires our infra | Maximum control |
| Transifex | Mature | Less generous free tier | Less ideal for OSS |
| In-tree only (today) | Zero new infra | High friction | Acceptable while community is small |

## Recommendation

**Crowdin** initially (free OSS tier), with self-hosted Weblate as v2 once community size justifies infra cost.

## Workflow (target)

```
1. New string added in source locale (FR) by editor
2. Crowdin syncs string to all configured locales as "untranslated"
3. Community translator picks up via Crowdin UI
4. Per-string review by language captain (volunteer)
5. Crowdin pushes approved translations back to repo via PR
6. CI runs i18n-validate, merges if green
```

## Quality controls

- Per-locale "language captain" role (volunteer per-locale lead).
- 2-eye review (translator + captain) before merge.
- Auto-validate (no missing placeholders, length sanity, HTML tag preservation).

## Translator recognition

- CONTRIBUTORS.md auto-updated with per-locale translator credits.
- Annual community shout-out (per S2087 deferred).

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S2094)
- Sister docs: `docs/GRIMBANEWS_OSS_CODE_OF_CONDUCT_DECISION.md`, `docs/GRIMBANEWS_METHODOLOGY_OSS_LICENSE_SELECTION.md`, per-locale catalog docs (S1101+ band)
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
