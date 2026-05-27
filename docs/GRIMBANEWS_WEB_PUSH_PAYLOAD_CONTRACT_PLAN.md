# GrimbaNews — Web Push Payload Contract Surrogate Plan

**Sprint ID:** S1303
**Status:** deferred → partial via surrogate documentation
**Master plan row:** `docs/GRIMBANEWS_MYTHOS_S1201_S1400_NOBUAI_B2B_MONETIZATION_EVIDENCE.md#s1301-s1310 — Web push payload contract`
**Walk wave:** CCCC

## Gating dependency

Payload contract needs:

- VAPID server (S1302)
- A shape covering: title, body, icon, badge, url, tag, renotify, requireInteraction, actions
- Per-locale templating (FR/EN/ES/etc.)
- 4kb payload-size budget (push spec hard cap)

## Surrogate-now infra

- **`docs/GRIMBANEWS_PER_REGION_DAILY_DIGEST_CADENCE.md`** — per-locale send timing pattern
- **`app/Support/GrimbaTranslationPresenter`** — per-locale string presenter
- **OG image generation** — per-cluster OG images already produced; same icon would serve push notifications

## Honest framing

A 1-day contract doc + a `PushPayload` value object once S1302 ships. The harder work is the per-locale copy budget (4kb after JSON encoding leaves ~150 utf-8 chars for body in non-Latin scripts).

## Owners

- **Product:** Liam Smith — contract scope
- **i18n:** Nina Patel — per-locale length budget
- **Backend:** Rajesh Kumar — value object + templating
- **Audit panel:** Zen / Echo / Mnemo at PR time

## Cross-references

- Sprint plan ledger: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1303 row)
- Web push server: `docs/GRIMBANEWS_WEB_PUSH_VAPID_SERVER_PLAN.md`
- Roster: `/Users/vb/.claude/projects/-Users-vb-kaizen/memory/project_iboga_full_roster.md`
