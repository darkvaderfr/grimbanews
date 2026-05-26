# GrimbaNews — Per-Topic Launch Playbook

**Status:** plan v0
**Owner:** Lucy Leai (Strategy) + Steve Jobs (CPO) + Liam Smith (PM) + per-topic editor
**Walks:** Mythos S1040 (per-topic launch playbook) deferred → partial
**Gating dependency:** v2 taxonomy (S1031) + per-topic editor hired + first per-topic brief signed off.

## Phase 0 — pre-launch (operator)

1. v2 taxonomy migrated (`grimba:remap-categories --from-v1-to-v2`).
2. Per-topic editorial brief signed off (`docs/editorial-briefs/<slug>.md`).
3. Source-roster pass: confirm ≥ 3 sources per camp (L/C/R) per topic.
4. Reader feedback channel ready (`/contact` already ships).
5. Methodology cross-link wired (`/methodologie#topic-<slug>` anchor added).

## Phase 1 — silent soft-launch (operator + first 30 days)

1. New v2 sub-bucket goes live on `/categorie/{slug}` route via Botble.
2. No external promotion; reader discovery only via search + cross-links.
3. Editor reviews per-cluster bias placements weekly.
4. KPIs tracked: read time, scroll depth, share rate, bounce.
5. Cluster bias-distribution monitored — flag if any side > 70% over 30-day window.

## Phase 2 — external announcement

1. Newsletter dedicated topic launch (per `docs/GRIMBANEWS_PER_REGION_DAILY_DIGEST_CADENCE.md`).
2. Methodology page topic section published.
3. Social-card per-topic OG (mirror per `/og/juste-milieu.png` pattern).
4. Partner-newsroom outreach for related topic coverage.

## Phase 3 — quarterly review

1. Editor + Lucy + Steve quarterly retro.
2. Per-topic bias-balance drift report.
3. Reader-signal review (KPIs vs. baseline).
4. Source-roster adjustments (add/remove).

## Failure conditions

- < 5 cluster-worthy stories per week → topic is over-narrow; merge with parent v1 bucket.
- Bias imbalance > 70% one side over 60 days → source-roster gap; add counter-balancing sources.
- Reader bounce > 80% → editorial scope misaligned with audience; brief revision.

## Cross-references

Master plan: `docs/GRIMBANEWS_PREPROD_1000_SPRINT_MASTER_PLAN.md` (S1040).
Sister: `docs/GRIMBANEWS_TOPIC_TAXONOMY_V2_DESIGN.md`, `docs/GRIMBANEWS_PER_TOPIC_EDITORIAL_BRIEF_TEMPLATE.md`.
