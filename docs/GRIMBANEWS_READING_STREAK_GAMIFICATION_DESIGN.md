# GrimbaNews — Reading Streak Gamification (Steve cinematic)

**Status:** plan v0
**Owner:** Steve Jobs (CPO) + Nina Patel (Lead FE) + Liam Smith (PM)
**Walks:** Mythos S1444 (reading streak gamification) deferred → partial
**Gating dependency:** Per-reader analytics (gates on Wave LLL analytics warehouse plan).

## Why this exists

Reader-engagement loop. Daily-active-reader retention is the key product KPI; streaks materially boost it (Duolingo, Wordle, NYT Games).

## v1 design (cinematic — Steve standard)

- Per-reader streak counter visible on `/account/dashboard`.
- Hero card on dashboard: large day-count, day-of-week dots showing past 7 days.
- Streak preserved when reader visits + reads ≥ 1 article per UTC-day.
- Streak resets at midnight UTC if no article-read.
- "Streak freeze" perk: premium subscribers (Wave AACC) get 1 freeze per week.

## UX (Steve cinematic = mandatory)

- Onboarding: "Vous avez démarré une série de lecture. Revenez demain pour la garder en vie."
- Day-3, Day-7, Day-30, Day-100, Day-365 milestone celebrations.
- Day-30+: unlock a "Lecteur curieux" badge.
- Day-100+: unlock "Membre engagé" badge.
- Day-365+: "Lecteur fondateur" — featured in /transparence page.

## Schema (gates on Vader migration approval)

```
member_streaks:
  member_id PK | current_streak | longest_streak | last_read_at | streak_freezes_remaining
member_streak_milestones:
  member_id | milestone (3|7|30|100|365) | reached_at
```

## Anti-pattern guardrails

- No "lose your streak" panic notifications (badly-implemented streak UX causes anxiety per Duolingo retros).
- Streak is shown as celebration, not pressure.
- Premium streak freeze removes the panic vector.
- Opt-out toggle in `/account/preferences`.

## Cross-references

Master plan: S1444. Sister: `docs/GRIMBANEWS_PER_AUTHOR_TRUST_BADGE_PROGRESSION.md`, `docs/GRIMBANEWS_AB_PERSONALIZATION_FLEET_DESIGN.md`.
