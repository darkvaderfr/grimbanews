# GrimbaNews — OSS Code of Conduct Decision

**Status:** plan v0 (no public repo today; CoC choice pre-staged)
**Owner:** Sophia Martinez (HR & Culture Lead) on community-norms framing + Lucy Leai on tone + future community manager (TBD hire)
**Walks:** Mythos S2081 (Community — code of conduct Contributor Covenant 2.1) deferred → partial
**Gating dependency:** OSS repo provisioned (S2043) + community-manager hire.

## Why this exists

S2081 picks the CoC. Default industry choice is Contributor Covenant 2.1. Discussion below on alternatives + adaptations for the GrimbaNews community.

## Candidate CoCs

| CoC | Pros | Cons | Adoption |
|---|---|---|---|
| Contributor Covenant 2.1 | Industry default, well-vetted, enforcement template | Slightly corporate tone | ~50k repos |
| Citizen Code of Conduct | More community-tone | Less enforcement guidance | smaller adoption |
| Mozilla Community Participation Guidelines | Strong enforcement | More restrictive on speech | Mozilla-aligned projects |
| Custom adaptation | Tailored | Maintenance burden + legal review | uncommon |

## Recommendation

**Contributor Covenant 2.1** with the following clarifications:

1. Editorial-bias debate is welcome; personal attacks are not.
2. Coordinated brigading of issues or PRs = violation (cross-ref S1593).
3. Enforcement contact: `conduct@grimbanews.com` (must be provisioned — depends on S2024 alias capacity).
4. Decisions logged to internal `coc_enforcement_actions` table (private; aggregate to annual report S2001).

## Enforcement ladder

- L1: private clarification (no action)
- L2: warning (private)
- L3: temporary ban (1-30 days)
- L4: permanent ban + public notice

## Per-locale community handling

- CoC translated to FR + EN + (per S2055 OSS i18n) more locales as community grows.
- Enforcement available in reporter's locale.

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S2081)
- Sister docs: `docs/GRIMBANEWS_METHODOLOGY_OSS_LICENSE_SELECTION.md`, `docs/GRIMBANEWS_OSS_I18N_CONTRIBUTION_FLOW_PLAN.md`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
