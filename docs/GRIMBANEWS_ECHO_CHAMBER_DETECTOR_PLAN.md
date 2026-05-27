# GrimbaNews — Echo-Chamber Detector Plan

**Status:** plan v0
**Owner:** David Chen (Data) + Lucy Leai (Strategy) + Maya Patel (Compliance)
**Walks:** Mythos S1538 (echo-chamber detector) deferred → partial
**Gating dependency:** per-member read log (by design does not exist server-side at v1 — privacy default).

## Why this exists

Per Grimba's bias-balance commitment, a reader stuck in an echo-chamber violates the editorial mission. Detection is the first step toward a gentle counter-recommendation surface.

## v1 design constraint

Per privacy default, GrimbaNews does **not** retain per-member-per-article read logs server-side. Detection must therefore run client-side or on a deliberate opt-in basis.

## v1.0 (client-side)

- Browser-side reading-pattern tracker (no server emission) stored in IndexedDB.
- Computes bias-distribution of last 50 reads.
- Renders client-only "Votre équilibre de lecture" widget on `/coffre`.
- No data ever leaves the device.

## v1.1 (opt-in server-side)

- Reader explicitly opts in via `/coffre/parametres/diversite`.
- Server records anonymized cluster bias-signal per read (no article id retained beyond 30d).
- Server-side echo-chamber score surfaced to reader; never to advertisers or editorial.

## Anti-patterns

- No editorial team-side dashboard of per-reader echo-chamber score.
- No automatic feed-rebalance without reader opt-in.
- No "your reading is too biased" guilt language.

## Cross-references

Master plan: S1538. Sister: S1539 (fairness audit), S1540 (launch retro), S1296 "Équilibré" badge (which uses cluster-level not per-article signal). Memory: `feedback_nobuai_model_branding.md`.
