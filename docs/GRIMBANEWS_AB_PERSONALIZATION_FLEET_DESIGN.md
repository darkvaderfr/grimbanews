# GrimbaNews — A/B + Personalization Fleet Design

**Status:** plan v0 (current personalization is rule-based per-reader; A/B test surface deferred)
**Owner:** Steve Jobs (CPO) + Liam Smith (PM) + Lisa Nguyen (data)
**Walks:** Mythos S1050 (breaking-news A/B tests) deferred → partial
**Gating dependency:** A/B harness shipped (`docs/GRIMBANEWS_AB_HARNESS_DESIGN.md` Wave LLL).

## Test surface

Reader-side cohort assignment (cookie-pinned, 30-day duration):
- `grimba_cohort` cookie: random hex assigned on first visit.
- Cohort → bucket mapping in server-side config table `ab_experiments`.

## Experiments to ship

1. **Breaking-news threshold** — current threshold 70/100; test 65 / 75.
2. **Middle Ground rail placement** — current "below blindspot rail"; test "above" or "carousel-rotation with other rails".
3. **Cluster card density** — current 3-col on desktop; test 4-col.
4. **Newsletter subscription nudge** — current "after 3 visits"; test 1-visit or 5-visit.
5. **Per-region home greeting** — current generic; test "Bonjour, votre région X" greeting.

## Metrics per experiment

- Primary: click-through to dossier pages.
- Secondary: session depth, share rate, newsletter conversion.
- Guardrail: bounce rate (no experiment should raise bounce > 5%).

## Statistical rigor

- Minimum sample: 1000 readers per arm.
- Run for ≥ 14 days.
- Two-tailed p < 0.05.
- Lucy + Steve review before declaring winner.

## Schema (gates on migration)

```
ab_experiments:
  id | slug | status (draft|running|ended) | started_at | ended_at | winning_arm
ab_assignments:
  experiment_id | cohort_id | arm | first_assigned_at
```

## Cross-references

Master plan: S1050. Sister: `docs/GRIMBANEWS_AB_HARNESS_DESIGN.md`, `docs/GRIMBANEWS_PERSONALIZATION_V2_LAUNCH_PLAYBOOK.md` (Wave LLL).
