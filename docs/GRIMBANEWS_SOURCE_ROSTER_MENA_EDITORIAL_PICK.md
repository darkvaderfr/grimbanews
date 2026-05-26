# GrimbaNews — MENA Source Roster Editorial Pick

**Status:** plan v0 (no MENA sources seeded today)
**Owner:** Lucy Leai (Strategy) + native AR + HE + FA editor TBD
**Walks:** Mythos S1023 (source roster MENA) deferred → partial
**Gating dependency:** Native-speaker editor cleared per language + counsel review of redistribution license per source (state-owned outlets have country-specific licenses).

## Tier-1 sources per country

- **Egypt:** Al-Ahram (state), Al-Masry Al-Youm (private/center), Mada Masr (independent/left), Daily News Egypt (English/center).
- **Morocco:** Le Matin (state), L'Économiste (private/center-right), TelQuel (private/center), Hespress (private/center).
- **Tunisia:** La Presse (public), Tunisie Numérique (private/center), Le Temps (private/center-right).
- **Algeria:** El Watan (private/center-left), Liberté (private/center-right), Algérie Patriotique (private/right).
- **Israel:** Haaretz (left), Yedioth Ahronoth (center), Israel Hayom (right/pro-Netanyahu), Times of Israel (English/center).
- **UAE:** The National (state-friendly/center), Khaleej Times (private/center).
- **Saudi Arabia:** Arab News (state-friendly), Asharq Al-Awsat (pan-Arab/private/center-right).
- **Iran:** IRNA (state), Tehran Times (state), Iran International (independent/diaspora/critical).

## Per-source seeder schema

Mirror EU east + LATAM. RTL languages (AR + HE + FA) require `direction='rtl'` flag — wires via `docs/GRIMBANEWS_RTL_SUPPORT_PLAN.md` Wave WWW.

## Onboarding cadence

1. Native editor signs off bias + factuality.
2. Counsel reviews redistribution license per source.
3. RSS 7-day validation.
4. `grimba:classify-sources --source-id={id}`.
5. `grimba:poll-feeds --source-id={id}` 14 days.

## Editorial sensitivity flags

- Israel/Palestine coverage: maintain editorial-style guide language (per `docs/GRIMBANEWS_EDITORIAL_STYLE_GUIDE.md` Wave LLL).
- State-owned media: clearly tag `ownership_type='state'`.
- Iranian content under sanctions: counsel review on inbound/outbound republication.

## Cross-references

Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1023).
Sister: `docs/GRIMBANEWS_RTL_SUPPORT_PLAN.md`, `docs/GRIMBANEWS_EDITORIAL_STYLE_GUIDE.md`.
