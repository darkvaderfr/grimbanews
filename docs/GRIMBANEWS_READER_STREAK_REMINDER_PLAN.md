# GrimbaNews — Reader Streak Reminder Email Plan

**Status:** plan v0
**Owner:** Liam Smith (PM) + Olivia Davis (Marketing — lifecycle email) + Henry Walker (Content)
**Walks:** Mythos S1292 (streak email reminder) deferred → partial
**Gating dependency:** S1291 streak counter ships first + opt-in flag on `members.notification_streak`.

## Why this exists

A streak counter (S1291) without a quiet reminder loses 60-70% of casual readers. The reminder is the cheapest retention surface we ship — a single email at a fixed local-time window if the reader is at risk of breaking their streak.

## v1 design

- Daily cron at 19:00 local-time per reader timezone (`members.timezone` already on schema).
- Eligibility:
  - `streak_days >= 3` (don't bother sub-3 streaks).
  - No visit recorded today.
  - `notification_streak = true`.
  - Last reminder ≥ 22h ago (debounce).
- Template: cinematic compact card, single CTA "Lire l'édition du jour".
- One-click unsubscribe (legal + reader respect).

## Frequency caps

- Max 5 reminders / 7-day window per reader.
- Hard stop on streak break (don't re-engage with reminder; switch to dormant-recovery flow per S1394).

## Copy guardrails (per global NobuAI brand)

- No provider names ("Drafted by NobuAI" if AI-assisted subject).
- No streak-loss anxiety language.
- Locale-aware: FR + EN at v1; ES/PT/DE catalogs (S1101+) before any other locale ships this surface.

## Surrogate today

- None. Vault digest + saved-search digest (both weekly) are too low-cadence to keep a daily streak alive.

## Cross-references

Master plan: S1292. Sister: S1291 (counter), S1296 (badges), S1394 (reader product v2 retention), S1281+ (newsletter v2 send infra). Memory: `feedback_nobuai_model_branding.md`.
