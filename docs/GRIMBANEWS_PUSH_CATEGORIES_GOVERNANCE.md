# GrimbaNews — Push Notification Categories Governance

**Status:** plan v0 (no push infra; per-saved-search opt-in via `saved_searches.active` is partial surrogate)
**Owner:** Liam Smith (PM) defines categories + Sara Chen (CISO) on opt-in posture + Steve Jobs (CPO) signs UX + David Chen on category-level analytics
**Walks:** Mythos S1175 (App push categories) deferred → partial
**Gating dependency:** Push infra (S1154) + native shell (S1152/S1153) + onboarding flow (S1170)

## Why this exists

S1175 keeps reader push tolerance high by giving granular opt-in. Send everything to everyone and retention dies in 2 weeks (industry-standard finding). Categorize first, ship later.

## Today's surrogate

- **`saved_searches.active`** — per-saved-search opt-in for digest delivery. Conceptually the same primitive but email cadence not real-time.

## Categories (v1 — narrow + clear)

| Category | Default | Description | Trigger |
|---|---|---|---|
| `breaking` | ON (post opt-in) | Major events that interrupt regular news flow | `posts.is_breaking = 1` + first-cluster-only filter |
| `cluster-update` | OFF | Important update to a story you've saved | post added to a coffre-saved cluster |
| `daily-highlights` | OFF | Top 3 stories of the day, 8am local | daily cron at 8am per locale |
| `saved-search` | OFF (per-search opt-in) | New result for a saved search | existing `saved_searches.active` |
| `local` | OFF | Big story in your country | scoped by `prefs_country` |
| `correction-issued` | ON for articles in coffre | Editorial correction on saved article | gates on corrections primitive (S1433) |

## Anti-pattern guards (hard rules — Sara Chen sign-off)

- **Never send marketing push.** No "Try Reader+ free!" notifications.
- **Never send re-engagement spam.** "We miss you" prohibited.
- **Never send sponsor / ad push.** Hard rule, no exceptions.
- **Per-category opt-in always.** No "we'll send everything by default".
- **Locale-respected** — body always in reader's locale.

## Frequency caps (per category, daily)

| Category | Max/day | Quiet hours |
|---|---|---|
| breaking | 5 | 23:00-07:00 local |
| cluster-update | 3 | 23:00-07:00 local |
| daily-highlights | 1 | NA (fixed 08:00 send) |
| saved-search | 3 | 23:00-07:00 local |
| local | 2 | 23:00-07:00 local |
| correction-issued | unbounded but per-article 1x | NA |

## UI surface

- `/account/notifications` web page (parallel to native settings).
- Native: `Settings → Notifications` (system-style toggles).
- Pre-onboarding: only `breaking` shown (skinny choice → higher accept rate).
- Post-onboarding: all 6 categories with descriptions.

## Cross-references

- Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1175)
- Sister docs: `docs/GRIMBANEWS_MOBILE_PUSH_INFRA_SCOPE.md`, `docs/GRIMBANEWS_PUSH_FREQUENCY_CAPS_DESIGN.md`, `docs/GRIMBANEWS_PUSH_OPTIN_ONBOARDING.md`, `docs/GRIMBANEWS_PUSH_CATEGORY_PREFERENCES_DESIGN.md`
- Roster: `~/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
