# GrimbaNews — Per-Reader Monthly Bias-Balance Retrospective

**Status:** plan v0
**Owner:** Lucy Leai (Strategy) + Steve Jobs (CPO) + Liam Smith (PM)
**Walks:** Mythos S1673 (per-reader monthly bias-balance retro) deferred → partial
**Gating dependency:** Per-reader reading-time analytics (Wave AAHH).

## Why this exists

Reader-side accountability for filter-bubble effects. Per-month "your bias mix this month" reflects to reader so they can adjust if they want exposure to other camps.

## v1 design

Per-reader monthly email + `/account/balance` dashboard view:

```
Votre balance ce mois (octobre 2026):
- Articles lus: 42
- Temps de lecture: 3h 12min

Mix par biais:
■■■■■■■■■■ 45% gauche
■■■■■■ 28% centre
■■■■■■ 27% droite

Comparé à votre mois précédent: +5% droite, -3% gauche

Recommandé pour équilibrer: ces 5 dossiers du camp opposé...
```

## Anti-pattern guardrails

- Not framed as judgment ("you read too left").
- Framed as opportunity ("here's the other side if you want it").
- Opt-out toggle in `/account/preferences`.
- No public reader-bias-mix exposure.

## Schema

No new schema — uses existing reading-events + bias_rating join.

## Cross-references

Master plan: S1673. Sister: `docs/GRIMBANEWS_PER_USER_READING_TIME_ANALYTICS_PLAN.md`, `docs/GRIMBANEWS_ML_FEED_FAIRNESS_AUDIT_PLAN.md`.
