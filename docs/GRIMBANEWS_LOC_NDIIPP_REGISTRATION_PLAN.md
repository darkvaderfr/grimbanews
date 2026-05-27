# GrimbaNews — Library of Congress NDIIPP Registration Plan

**Status:** plan v0
**Owner:** Lucy Leai (Strategy) + Jacob Lee (DevOps)
**Walks:** Mythos S2225 (Library of Congress NDIIPP registration) deferred → partial
**Gating dependency:** Operator-side outreach + English-language presence.

## Why this exists

LoC's NDIIPP (National Digital Information Infrastructure and Preservation Program) coordinates preservation of digital publications. Registration ensures GrimbaNews English-language content is preserved in US national-scale archives.

## Application path

- Submit GrimbaNews to LoC's Web Archives selection.
- LoC selectors review per-archive nominations.
- Per-acceptance: per-quarter snapshot scheduled.

## Per-quarter snapshot

- LoC's Heritrix crawler hits GrimbaNews per-quarter.
- Per-snapshot: WARC bundle archived.
- LoC public-facing surface: `webarchive.loc.gov/all/*/grimbanews.com/*`.

## Cost

Free (LoC operational cost; participating publishers contribute via per-snapshot crawl).

## Cross-references

Master plan: S2225. Sister: `docs/GRIMBANEWS_WAYBACK_MACHINE_PARTNERSHIP_PLAN.md`, `docs/GRIMBANEWS_BNF_BANQ_LEGAL_DEPOSIT_PLAN.md`, `docs/GRIMBANEWS_IIPC_MEMBERSHIP_PLAN.md`.
