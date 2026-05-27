# GrimbaNews — Web Push Opt-In Surrogate Plan

**Sprint ID:** S1301
**Status:** deferred → partial via surrogate documentation
**Master plan row:** `docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1301-s1310 — Web push opt-in`
**Walk wave:** CCCC

## Gating dependency

Web push opt-in needs:

- VAPID keys (one-time generated; stored in `webpush_public_key` / `webpush_private_key` settings)
- A push subscriber table (`endpoint`, `p256dh`, `auth`, `user_agent`, `created_at`)
- Service worker push-event listener (current `public/grimba-sw.js` does not register one)
- An opt-in UX (post-consent prompt, not first-load)

## Surrogate-now infra

- **`public/grimba-sw.js`** — service worker already registered for offline + cache; one event listener away from push-ready
- **`docs/GRIMBANEWS_COOKIE_CONSENT_PER_REGION.md`** — consent pattern that the push opt-in should follow
- **Newsletter subscription** — the email channel today fulfills "alert me about new content"

## Honest framing

Cheap to ship technically (1 week incl. UX). Opt-in UX has high anti-pattern risk (Chrome shamefile of news sites that abuse push); deliberately deferred to make sure the prompt cadence is reader-respectful.

## Owners

- **Product:** Liam Smith — opt-in UX policy (when to prompt, how often)
- **Backend:** Rajesh Kumar — VAPID + subscription table
- **Frontend:** Nina Patel — service worker push listener
- **Audit panel:** Zen / Echo / Mnemo at PR time

## Cross-references

- Sprint plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1301 row)
- Web push server (VAPID): S1302
- Cookie consent: `docs/GRIMBANEWS_COOKIE_CONSENT_PER_REGION.md`
- Roster: `/Users/vb/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
