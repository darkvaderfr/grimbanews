# GrimbaNews — Per-Region Election Cycle Calendar

**Status:** plan v0
**Owner:** Lucy Leai (Strategy) + per-region editor + Sara Chen (CISO)
**Walks:** Mythos S1595 (per-region election-cycle calendar) deferred → partial — gating doc for `docs/GRIMBANEWS_ELECTION_PERIOD_EDITORIAL_GUARDRAILS.md`
**Gating dependency:** Operator-curated calendar of upcoming national + sub-national elections.

## Why this exists

Election-period editorial guardrails (Wave AAEE) need a source-of-truth calendar of which countries are in election windows. Calendar drives auto-application of guardrails.

## Calendar entries (per active region)

```
election_periods (table):
  id | country (ISO-2) | election_type | period_start | period_end | candidates_json | guardrail_level
```

Operator pre-populates upcoming elections (12-month rolling window).

## Per-region election types

- **France:** présidentielle, législatives, européennes, municipales, départementales, régionales.
- **Brésil:** presidenciais, legislativas, governadores, prefeitos.
- **DE:** Bundestagswahl, Landtagswahl, Europawahl, Kommunalwahl.
- **ES:** generales, autonómicas, municipales, europeas.
- **US (if covered):** presidential, congressional, gubernatorial, midterm.
- **Per-African-country:** per-presidentielle + legislative + local cycles.

## Per-region guardrail-level

| Country | Guardrail level |
|---|---|
| France | enforced (legal "égalité des temps de parole" applies to broadcast; we apply advisory-strict cluster-balance) |
| Brazil | enforced (TRE balance rules) |
| Germany | enforced (impartiality norms) |
| Other | advisory (balance reported but not gated) |

## Auto-application

When a country enters `period_start`:
- Add per-region banner on /region/{country} surfaces.
- Activate per-cluster source-balance check daily.
- Activate sponsored-content lockout per Wave AAEE.

## Editor cadence

- Lucy + per-region editor: monthly review of upcoming-12-month elections.
- Per-election kickoff: editor meeting 30 days before period start.

## Cross-references

Master plan: S1595. Sister: `docs/GRIMBANEWS_ELECTION_PERIOD_EDITORIAL_GUARDRAILS.md`, `docs/GRIMBANEWS_PER_REGION_HOMEPAGE_HERO_LOCALIZATION.md`.
