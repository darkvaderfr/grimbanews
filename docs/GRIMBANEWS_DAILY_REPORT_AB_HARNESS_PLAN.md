# GrimbaNews — Daily Report A/B Harness Plan

**Status:** plan v0
**Owner:** David Chen (Data Science) + Liam Smith (PM)
**Walks:** Mythos S1387 (daily report A/B subject / cover) deferred → partial
**Gating dependency:** S1073 generic A/B engine (still deferred) + S1382 baseline daily report + S1389 analytics dashboard.

## Why this exists

Without an A/B harness, subject-line and hero-image hypotheses sit untested forever. The harness scopes to one experiment dimension (subject) at v1 to keep statistics interpretable.

## v1 design

- One active experiment per send.
- Two-arm split (50/50).
- Arm assignment deterministic via `hash(member_id, experiment_slug) % 2` — same reader stays in the same arm across days for one experiment.
- Metric stack: open rate (primary), click rate (secondary), unsub rate (guardrail — auto-stop if ≥ 1.5% in losing arm).
- Min sample 5,000 per arm before declaring winner.
- Frequentist two-sample proportion test; flag wins at p < 0.05.

## Schema

```sql
CREATE TABLE daily_report_experiments (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  slug VARCHAR(64) NOT NULL UNIQUE,
  dimension ENUM('subject', 'hero_cluster', 'send_time') NOT NULL,
  arm_a JSON NOT NULL,
  arm_b JSON NOT NULL,
  status ENUM('draft', 'running', 'concluded') DEFAULT 'draft',
  started_at TIMESTAMP NULL,
  concluded_at TIMESTAMP NULL,
  winner ENUM('a', 'b', 'tie') NULL,
  notes TEXT NULL
);
```

## Anti-patterns

- No multivariate at v1 (sample sizes too small).
- No silently-shipped winners — every experiment writes to a public-facing methodology log.
- No subject-line spam variants (anti-pattern: emoji-loaded clickbait).

## Cross-references

Master plan: S1387. Sister: S1073 (general A/B engine), S1382 (daily report), S1389 (analytics), S1588/S1589 (subject + send-time variants).
