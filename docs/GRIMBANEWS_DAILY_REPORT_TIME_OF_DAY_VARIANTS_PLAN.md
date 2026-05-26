# GrimbaNews — Daily Report Time-of-Day Variants Plan

**Status:** plan v0 (single daily cadence today; no morning / lunch / evening variants)
**Owner:** Henry Walker (Content) + Rajesh Kumar (Backend) + Olivia Davis (Marketing Strategist)
**Walks:** Mythos S1584 (Time-of-day variants) deferred → partial
**Gating dependency:** Daily report v2 base (S1581+) + per-reader timezone capture.

## Why this exists

S1584 sends different versions of the daily digest at different times — morning, lunch, evening. Today single send at server time.

## Today's surrogate

- Vault digest + saved-search digest send weekly at fixed server hours.

## Variant spec

| Variant | Time (member local) | Content | Length |
|---|---|---|---|
| Matin | 07:00 | Overnight + breaking | 5-7 items |
| Déjeuner | 12:30 | Followed-cluster updates | 3-5 items |
| Soir | 19:00 | Recap + tomorrow preview | 8-10 items |

## Preferences

- Opt into one variant at signup; can subscribe to multiple.
- Per-variant unsubscribe independent.
- Max 3 emails/member/day.

## Timezone

- Capture via `Intl.DateTimeFormat()` at signup.
- Send-time queries `member_timezone_local_hour = :variant_hour`.

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1584)
- Sister docs: `docs/GRIMBANEWS_FOLLOWED_CLUSTERS_PLAN.md`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
